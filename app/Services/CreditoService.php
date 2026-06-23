<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteContaCorrente;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CreditoService
{
    /**
     * Verifica se cliente pode usar carteira (saldo real maior que zero e ativo)
     */
    public function podeUsarCarteira(Cliente $cliente): bool
    {
        // 🚀 REMOVIDO DB::transaction: Consultas de leitura não devem travar o banco
        if (!$cliente->ativo || $cliente->ativo === 'inativo') return false;

        $credito = $cliente->creditoAtivo;
        if (!$credito) return false;

        if ($credito->status === 'bloqueado') return false;
        if ((float)$credito->limite_credito <= 0) return false;

        $saldo = app(ContaCorrenteService::class)->saldoAtual($cliente->id);

        return $saldo > 0;
    }

    /**
     * Calcula o total de crédito já utilizado (fiado)
     */
    public function saldoDevedor(Cliente $cliente): float
    {
        // 🚀 OTIMIZAÇÃO CRÍTICA: Substituído 'whereHas' por 'join' para ganho massivo de performance
        return (float) PagamentoVenda::join('vendas', 'pagamentos_venda.venda_id', '=', 'vendas.id')
            ->where('vendas.cliente_id', $cliente->id)
            ->where('vendas.status', '!=', 'cancelada')
            ->where('pagamentos_venda.forma_pagamento', 'carteira')
            ->where('pagamentos_venda.status', 'pendente')
            ->sum('pagamentos_venda.valor');
    }

    /**
     * Valida limite de crédito antes da venda (CONSISTENTE)
     */
    public function temLimiteDisponivel(Cliente $cliente, float $valorNovaVenda): bool
    {
        $credito = $cliente->creditoAtivo;
        if (!$credito || $credito->status === 'bloqueado') return false;

        $limiteCredito = (float)($credito->limite_credito ?? 0);
        $devedor = $this->saldoDevedor($cliente);

        return ($devedor + $valorNovaVenda) <= $limiteCredito;
    }

    /**
     * Validação completa de crédito + carteira (REGRA PRINCIPAL DO PDV)
     */
    public function validarCredito(Cliente $cliente, float $valorNovaVenda, array $pagamentos = []): array
    {
        $valorCarteira = $pagamentos['carteira']['valor'] ?? 0;

        if (strtoupper($cliente->tipo_cliente) === 'BALCAO' && $valorCarteira > 0) {
            return [
                'aprovado' => false,
                'mensagem' => 'Cliente balcão não pode usar carteira.'
            ];
        }

        $credito = $cliente->creditoAtivo;
        $saldo = app(ContaCorrenteService::class)->saldoAtual($cliente->id);

        if (optional($credito)->status === 'bloqueado' || $saldo <= 0) {
            return [
                'aprovado' => false,
                'mensagem' => 'O crediário/carteira deste cliente encontra-se bloqueado ou sem saldo.'
            ];
        }

        if (!$this->temLimiteDisponivel($cliente, $valorNovaVenda)) {
            return [
                'aprovado' => false,
                'mensagem' => 'Limite de crédito insuficiente.'
            ];
        }

        return [
            'aprovado' => true,
            'saldo_atual' => $this->saldoDevedor($cliente),
            'limite' => (float)($credito->limite_credito ?? 0)
        ];
    }

    /**
     * Atualiza status do cliente baseado no risco financeiro
     */
    // public function atualizarStatusCliente(Cliente $cliente): void
    // {
    //     // 🔒 MANTIDO DB::transaction apenas aqui, pois envolve uma operação de ESCRITA (save)
    //     DB::transaction(function () use ($cliente) {
    //         $credito = $cliente->creditoAtivo;
    //         if (!$credito) return;

    //         $limiteCredito = (float)($credito->limite_credito ?? 0);
    //         $devedor = $this->saldoDevedor($cliente);

    //         if ($devedor >= $limiteCredito && $limiteCredito > 0) {
    //             $credito->status = 'bloqueado';
    //             $credito->save();
    //         }
    //     });
    // }

    public function atualizarStatusCliente(Cliente $cliente): void
    {
        DB::transaction(function () use ($cliente) {

            $credito = $cliente->creditoAtivo;
            if (!$credito) return;

            $limiteCredito = (float) ($credito->limite_credito ?? 0);

            // 🔎 saldo da conta corrente (ajuste o relacionamento conforme seu model)
            $saldoConta = (float) (
                $cliente->contaCorrente?->saldo_apos ?? 0
            );

            // 🔎 saldo devedor atual
            $devedor = (float) $this->saldoDevedor($cliente);

            /**
             * REGRA FINAL:
             * bloqueia apenas se:
             * saldo da conta corrente >= limite de crédito
             * E ainda houver risco devedor
             */
            if (
                $limiteCredito > 0 &&
                $saldoConta >= $limiteCredito &&
                $devedor >= $limiteCredito
            ) {
                $credito->status = 'bloqueado';
                $credito->save();
            }
        });
    }

    /**
     * Verifica atraso de pagamento (FIADO)
     */
    public function possuiPagamentoEmAtraso(Cliente $cliente): bool
    {
        // 🚀 OTIMIZAÇÃO CRÍTICA: Substituído 'whereHas' por 'join' para evitar travamento em tabelas grandes
        return PagamentoVenda::join('vendas', 'pagamentos_venda.venda_id', '=', 'vendas.id')
            ->where('vendas.cliente_id', $cliente->id)
            ->where('pagamentos_venda.forma_pagamento', 'carteira')
            ->where('pagamentos_venda.status', 'pendente')
            ->whereDate('pagamentos_venda.data_vencimento', '<', now())
            ->exists();
    }

    /**
     * Retorna formas de pagamento permitidas no PDV (REGRA CENTRAL)
     */
    public function formasPermitidas(Cliente $cliente, float $valorVenda = 0): array
    {
        $formas = [
            'dinheiro',
            'pix',
            'cartao_credito',
            'cartao_debito'
        ];

        $status = $cliente->creditoAtivo->status ?? null;
        $creditoAtivo = $status === 'ativo';

        if ($creditoAtivo && $this->podeUsarCarteira($cliente)) {
            $formas[] = 'carteira';
        }

       // No final do método formasPermitidas:
        $limiteCredito = (float)($cliente->creditoAtivo->limite_credito ?? 0);
        if ($creditoAtivo && $limiteCredito > 0) {
            if ($this->temLimiteDisponivel($cliente, $valorVenda)) {
                // 🔥 Removido 'credito' e mantido apenas 'carteira' para bater com o Controller e o Banco
                // Se você já adicionou 'carteira' na verificação anterior, pode apenas ignorar este bloco
            }
        }
        return $formas;
    }
   
   /**
     * Processa débito da carteira do cliente
     */
    public function adicionarDebito(
        int $clienteId,
        float $valor,
        ?int $vendaId = null
    ): bool {

        if ($valor <= 0) {
            return false;
        }

        $cliente = Cliente::find($clienteId);

        if (!$cliente) {
            throw new \Exception('Cliente não encontrado.');
        }

        DB::transaction(function () use (
            $cliente,
            $valor,
            $vendaId
        ) {

            $saldoAtual = app(ContaCorrenteService::class)
                ->saldoAtual($cliente->id);

            if ($saldoAtual < $valor) {
                throw new \Exception('Saldo insuficiente na carteira.');
            }

            $novoSaldo = $saldoAtual - $valor;

            ClienteContaCorrente::create([
                'cliente_id' => $cliente->id,
                'venda_id' => $vendaId,
                'tipo' => 'debito',
                'origem' => 'venda',
                'valor' => $valor,
                'saldo_apos' => $novoSaldo,
                'descricao' => 'Pagamento via carteira'
            ]);

            Cache::forget("cliente_saldo_{$cliente->id}");

            $this->atualizarStatusCliente($cliente);
        });

        return true;
    }

    public function aumentarLimiteCredito(Cliente $cliente, float $novoLimite): void
    {
        $credito = $cliente->creditoAtivo; // Seu relacionamento existente
        if (!$credito) {
            throw new \Exception('O cliente não possui registro de crédito configurado.');
        }

        $limiteAnterior = (float)($credito->limite_credito ?? 0);
        if ($novoLimite <= $limiteAnterior) {
            throw new \Exception('O novo limite deve ser estritamente maior que o limite de crédito atual.');
        }

        // Calcula a diferença que será injetada como fôlego na conta corrente
        $diferenca = $novoLimite - $limiteAnterior;

        // 1. Atualiza o teto na tabela de configurações de crédito
        $credito->update([
            'limite_credito' => $novoLimite,
            // Se estava bloqueado por falta de limite, o aumento restabelece o status ativo
            'status'         => $credito->status === 'bloqueado' ? 'ativo' : $credito->status
        ]);

        // Atualiza as flags na tabela de clientes caso estivesse marcado como bloqueado
        if ($cliente->bloqueado_credito == 1) {
            $cliente->update([
                'bloqueado_credito'     => 0,
                'data_bloqueio_credito' => null,
                'ativo'                 => 'ativo'
            ]);
        }

        // 2. 🔒 SEGURANÇA MÁXIMA: Aplica o lockForUpdate para travar a conta corrente contra compras simultâneas no PDV
        $ultimaMovimentacao = ClienteContaCorrente::where('cliente_id', $cliente->id)
            ->orderByDesc('id')
            ->lockForUpdate() 
            ->first();

        $saldoAtual = $ultimaMovimentacao !== null ? (float)$ultimaMovimentacao->saldo_apos : $limiteAnterior;
        $novoSaldoCC = $saldoAtual + $diferenca;

        // Registra a transação de ajuste na conta corrente
        ClienteContaCorrente::create([
            'cliente_id' => $cliente->id,
            'tipo'       => 'credito',
            'origem'     => 'ajuste',
            'valor'      => $diferenca,
            'saldo_apos' => $novoSaldoCC,
            'descricao'  => "Aumento de limite de crédito de R$ " . number_format($limiteAnterior, 2, ',', '.') . " para R$ " . number_format($novoLimite, 2, ',', '.')
        ]);

        // 3. Registra o evento na tabela de histórico de crédito
        DB::table('cliente_historico_creditos')->insert([
            'cliente_id'    => $cliente->id,
            'tipo_evento'   => 'ajuste_score', // Enum disponível na sua tabela
            'descricao'     => "Aumento de limite homologado. Novo teto: R$ " . number_format($novoLimite, 2, ',', '.'),
            'score_anterior'=> $cliente->score_credito, // Mapeando campos da sua tabela
            'score_novo'    => $cliente->score_credito,
            'created_at'    => now()
        ]);

        // 🔥 Invalida o cache de saldo imediatamente para atualizar o PDV e os painéis
        \Illuminate\Support\Facades\Cache::forget("cliente_saldo_{$cliente->id}");
    }

    
    /**
     * Varre o cliente específico e aplica o bloqueio caso ele possua faturas vencidas.
     * Perfeito para ser chamado de forma individual ou dentro de um Command/Job diário.
     */
    public function verificarEBloquearPorAtraso(Cliente $cliente): bool
    {
        // Reutiliza o seu método otimizado com join para checar inadimplência
        if ($this->possuiPagamentoEmAtraso($cliente)) {
            
            $credito = $cliente->creditoAtivo;
            if ($credito && $credito->status !== 'bloqueado') {
                
                DB::transaction(function () use ($cliente, $credito) {
                    // 1. Modifica o status da configuração de crédito
                    $credito->update(['status' => 'bloqueado']);

                    // 2. Modifica o status na tabela master de clientes
                    $cliente->update([
                        'bloqueado_credito'     => 1,
                        'data_bloqueio_credito' => now(),
                        'ativo'                 => 'bloqueado_credito' // Conforme enums da sua tabela
                    ]);

                    // 3. Registra o evento específico na tabela de históricos
                    DB::table('cliente_historico_creditos')->insert([
                        'cliente_id'    => $cliente->id,
                        'tipo_evento'   => 'bloqueio_atraso', // Alinhado ao enum mapeado
                        'descricao'     => 'Bloqueio administrativo aplicado devido a parcelas/vendas em atraso na carteira.',
                        'score_anterior'=> $cliente->score_credito,
                        'score_novo'    => $cliente->score_credito,
                        'created_at'    => now()
                    ]);

                    // 🔥 Invalida o cache para travar o PDV na hora
                    \Illuminate\Support\Facades\Cache::forget("cliente_saldo_{$cliente->id}");
                });

                return true;
            }
        }
        return false;
    }

    /**
     * Método auxiliar para bloqueios originados por estouro de saldo/estorno.
     */
    public function bloquearPorLimiteEstourado(Cliente $cliente, string $motivo): void
    {
        $credito = $cliente->creditoAtivo;
        if ($credito && $credito->status !== 'bloqueado') {
            $credito->update(['status' => 'bloqueado']);
            
            $cliente->update([
                'bloqueado_credito'     => 1,
                'data_bloqueio_credito' => now(),
                'ativo'                 => 'bloqueado_credito'
            ]);

            DB::table('cliente_historico_creditos')->insert([
                'cliente_id'    => $cliente->id,
                'tipo_evento'   => 'bloqueio_limite', // Enum correspondente
                'descricao'     => $motivo,
                'score_anterior'=> $cliente->score_credito,
                'score_novo'    => $cliente->score_credito,
                'created_at'    => now()
            ]);
        }
    }


}
