<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteContaCorrente;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ContaCorrenteService
{
    /**
     * Registra uma movimentação de débito na carteira de forma segura.
     */
    public function registrarMovimentacao(PagamentoVenda $pagamento): void
    {
        if ($pagamento->forma_pagamento !== 'carteira') {
            return;
        }

        // 🚀 OTIMIZAÇÃO: Evita queries redundantes puxando o cliente diretamente da relação já carregada
        $venda = $pagamento->venda;
        $cliente = $venda ? $venda->cliente : null;

        if (!$cliente) {
            throw new \Exception('Cliente não encontrado para registrar movimentação.');
        }

        DB::transaction(function () use ($cliente, $pagamento) {
            // 🔒 Mantido o lock protetivo, mas agora executado em milissegundos após o commit da venda
            $ultimaMovimentacao = ClienteContaCorrente::where('cliente_id', $cliente->id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            // Garante o carregamento do crédito ativo de forma leve se for o primeiro uso
            if ($ultimaMovimentacao === null) {
                $cliente->loadMissing('creditoAtivo');
            }

            $limiteCredito = (float)(optional($cliente->creditoAtivo)->limite_credito ?? 0);
            $saldoAtual = $ultimaMovimentacao !== null ? (float)$ultimaMovimentacao->saldo_apos : $limiteCredito;

            if ($saldoAtual < (float)$pagamento->valor) {
                throw new \Exception('Saldo insuficiente na carteira para processar este pagamento.');
            }

            $novoSaldo = $saldoAtual - (float)$pagamento->valor;

            ClienteContaCorrente::create([
                'cliente_id'         => $cliente->id,
                'venda_id'           => $pagamento->venda_id,
                'pagamento_venda_id' => $pagamento->id,
                'tipo'               => 'debito',
                'origem'             => 'venda',
                'valor'              => $pagamento->valor,
                'saldo_apos'         => $novoSaldo,
                'descricao'          => 'Pagamento via carteira'
            ]);

            // 🔥 CORREÇÃO: Limpa a chave exata do cache usando a mesma string do método saldoAtual
            Cache::forget("cliente_saldo_{$cliente->id}");
        });
    }

        /**
     * Processa débito da carteira do cliente
     */
    public function adicionarDebito(PagamentoVenda $pagamento): bool
    {
        if ($pagamento->forma_pagamento !== 'carteira') {
            return false;
        }

        app(ContaCorrenteService::class)
            ->registrarMovimentacao($pagamento);

        // atualiza status financeiro do cliente
        $cliente = optional($pagamento->venda)->cliente;

        if ($cliente) {
            $this->atualizarStatusCliente($cliente);
        }

        return true;
    }

    /**
     * Retorna o saldo atual real do cliente (Sincronizado com as regras do PDV)
     */
    public function saldoAtual(int $clienteId): float
    {
        // 🚀 OTIMIZAÇÃO: 10 segundos de cache são suficientes para proteger o PDV de cliques duplos 
        // sem causar risco de leituras desatualizadas de saldo.
        return (float) Cache::remember("cliente_saldo_{$clienteId}", 10, function () use ($clienteId) {
            
            $ultimoSaldoRaw = ClienteContaCorrente::where('cliente_id', $clienteId)
                ->orderByDesc('id')
                ->value('saldo_apos');

            if ($ultimoSaldoRaw !== null) {
                return (float)$ultimoSaldoRaw;
            }

            $cliente = Cliente::with('creditoAtivo')->find($clienteId);
            
            return (float)(optional($cliente->creditoAtivo)->limite_credito ?? 0);
        });
    }
    
    /**
     * Processa a transação de entrada (Pagamento/Amortização) na conta corrente.
     */
    public function adicionarCredito(int $clienteId, float $valor, ?int $pagamentoVendaId = null): float
    {
        if ($valor <= 0) {
            throw new \Exception('O valor do pagamento deve ser maior que zero.');
        }

        $cliente = Cliente::with('creditoAtivo')->find($clienteId);
        if (!$cliente) {
            throw new \Exception('Cliente não encontrado.');
        }

        // 🔒 Mantém o exato padrão de lock protetivo que você usa no débito
        $ultimaMovimentacao = ClienteContaCorrente::where('cliente_id', $cliente->id)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $limiteCredito = (float)(optional($cliente->creditoAtivo)->limite_credito ?? 0);
        $saldoAtual = $ultimaMovimentacao !== null ? (float)$ultimaMovimentacao->saldo_apos : $limiteCredito;

        // Cenário de pagamento: o dinheiro entra SOMANDO ao saldo disponível
        $novoSaldo = $saldoAtual + $valor;

        // Registra a transação financeira bruta
        ClienteContaCorrente::create([
            'cliente_id'         => $cliente->id,
            'pagamento_venda_id' => $pagamentoVendaId,
            'tipo'               => 'credito',
            'origem'             => 'pagamento',
            'valor'              => $valor,
            'saldo_apos'         => $novoSaldo,
            'descricao'          => 'Recebimento de pagamento / amortização de carteira'
        ]);

        // 🔥 Limpa a chave exata do cache do PDV que você implementou
        \Illuminate\Support\Facades\Cache::forget("cliente_saldo_{$cliente->id}");

       // ... (código anterior do método adicionarCredito até o ClienteContaCorrente::create)

        // 🔥 Limpa o cache para sincronizar o PDV instantaneamente
        \Illuminate\Support\Facades\Cache::forget("cliente_saldo_{$cliente->id}");

        $credito = $cliente->creditoAtivo;

        // Só tentamos desbloquear se o saldo atualizado voltou a ficar POSITIVO (> 0)
        if ($credito && $novoSaldo > 0 && $credito->status === 'bloqueado') {
            
            // Instancia o CreditoService para reutilizar a verificação com join de parcelas vencidas
            $creditoService = app(CreditoService::class);
            
            // 🚨 REGRA CRÍTICA: Mesmo com saldo em conta corrente, se ele ainda tiver faturas atrasadas, NÃO DESBLOQUEIA.
            if (!$creditoService->possuiPagamentoEmAtraso($cliente)) {
                
                $credito->update(['status' => 'ativo']);

                $cliente->update([
                    'bloqueado_credito'     => 0,
                    'data_bloqueio_credito' => null,
                    'ativo'                 => 'ativo'
                ]);

                DB::table('cliente_historico_creditos')->insert([
                    'cliente_id'  => $cliente->id,
                    'tipo_evento' => 'desbloqueio',
                    'descricao'   => 'Desbloqueio automático gerado por liquidação total de saldo devedor e parcelas em atraso.',
                    'created_at'  => now()
                ]);
            }
        }

        return $novoSaldo;


        return $novoSaldo;
    }

    /**
     * Executa o estorno de uma movimentação financeira anterior de forma segura.
     */
        public function estornarTransacao(int $movimentacaoId, string $motivo = 'Estorno solicitado pelo operador.'): float
    {
        // 1. Evita o duplo estorno verificando se essa movimentação já foi a origem de um estorno anterior
        $jaEstornado = ClienteContaCorrente::where('origem', 'estorno')
            ->where('descricao', 'like', "Estorno do lançamento ID #{$movimentacaoId}.%")
            ->exists();

        if ($jaEstornado) {
            throw new \Exception('Esta transação já foi estornada anteriormente e não pode ser duplicada.');
        }

        // Localiza a movimentação original que será estornada
        $movOriginal = ClienteContaCorrente::findOrFail($movimentacaoId);
        $clienteId = $movOriginal->cliente_id;

        // 🔒 Mantém o padrão de lock protetivo para recalcular o saldo apos
        $ultimaMovimentacao = ClienteContaCorrente::where('cliente_id', $clienteId)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if (!$ultimaMovimentacao) {
            throw new \Exception('Nenhuma movimentação localizada para recalcular o saldo.');
        }

        $saldoAtual = (float) $ultimaMovimentacao->saldo_apos;
        $valorEstorno = (float) $movOriginal->valor;

        // Se a original era DÉBITO (venda), o estorno DEVOLVE o saldo (soma).
        // Se a original era CRÉDITO (pagamento), o estorno REMOVE o saldo (subtrai).
        if ($movOriginal->tipo === 'debito') {
            $novoSaldo = $saldoAtual + $valorEstorno;
            $tipoNovo = 'credito';

            // Cancela a venda vinculada se ela existir
            if ($movOriginal->venda_id) {
                $venda = \App\Models\Venda::find($movOriginal->venda_id);
                if ($venda) {
                    try {
                        // Isso vai disparar o 'booted()' do seu model Venda e validar o caixa
                        $venda->update(['status' => 'cancelada']);
                    } catch (\Exception $e) {
                        // Interrompe e avisa se o caixa estiver fechado, preservando o saldo anterior
                        throw new \Exception("Falha ao estornar venda: " . $e->getMessage());
                    }
                }
            }
        } else {
            $novoSaldo = $saldoAtual - $valorEstorno;
            $tipoNovo = 'debito';
        }

        // Registra a contrapartida de estorno na conta corrente
        $novoEstorno = ClienteContaCorrente::create([
            'cliente_id'         => $clienteId,
            'venda_id'           => $movOriginal->venda_id,
            'pagamento_venda_id' => $movOriginal->pagamento_venda_id,
            'tipo'               => $tipoNovo,
            'origem'             => 'estorno',
            'valor'              => $valorEstorno,
            'saldo_apos'         => $novoSaldo,
            'descricao'          => "Estorno do lançamento ID #{$movOriginal->id}. Motivo: {$motivo}"
        ]);

        // 🔥 Limpa o cache imediatamente para o PDV atualizar
        \Illuminate\Support\Facades\Cache::forget("cliente_saldo_{$clienteId}");

        // Se o estorno jogou o saldo para baixo de zero, aplica o bloqueio preventivo
        $cliente = Cliente::with('creditoAtivo')->find($clienteId);
        if ($cliente && $novoSaldo <= 0) {
            app(CreditoService::class)->bloquearPorLimiteEstourado($cliente, "Bloqueio aplicado via estorno financeiro de ID #{$novoEstorno->id}.");
        }

        return $novoSaldo;
    }
}
