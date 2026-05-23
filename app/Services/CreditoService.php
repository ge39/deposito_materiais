<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;

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
    public function atualizarStatusCliente(Cliente $cliente): void
    {
        // 🔒 MANTIDO DB::transaction apenas aqui, pois envolve uma operação de ESCRITA (save)
        DB::transaction(function () use ($cliente) {
            $credito = $cliente->creditoAtivo;
            if (!$credito) return;

            $limiteCredito = (float)($credito->limite_credito ?? 0);
            $devedor = $this->saldoDevedor($cliente);

            if ($devedor >= $limiteCredito && $limiteCredito > 0) {
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
}
