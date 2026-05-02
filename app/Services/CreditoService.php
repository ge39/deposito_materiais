<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;

class CreditoService
{
    /**
     * Verifica se cliente pode usar carteira (saldo real)
     */
    public function podeUsarCarteira(Cliente $cliente): bool
    {
        return DB::transaction(function () use ($cliente) {

            if (!$cliente->ativo) return false;
            if ($cliente->bloqueado_credito) return false;
            if ($cliente->limite_credito <= 0) return false;

            $saldo = app(ContaCorrenteService::class)
                ->saldoAtual($cliente->id);

            return $saldo >= 0;
        });
    }

    /**
     * Calcula o total de crédito já utilizado (fiado)
     */
    public function saldoDevedor(Cliente $cliente): float
    {
        return (float) PagamentoVenda::whereHas('venda', function ($q) use ($cliente) {
                $q->where('cliente_id', $cliente->id)
                  ->where('status', '!=', 'cancelada');
            })
            ->where('forma_pagamento', 'credito')
            ->where('status', 'pendente')
            ->sum('valor');
    }

    /**
     * Valida limite de crédito antes da venda (CONSISTENTE)
     */
    public function temLimiteDisponivel(Cliente $cliente, float $valorNovaVenda): bool
    {
        return DB::transaction(function () use ($cliente, $valorNovaVenda) {

            $devedor = $this->saldoDevedor($cliente);

            return ($devedor + $valorNovaVenda) <= $cliente->limite_credito;
        });
    }

    /**
     * Validação completa de crédito + carteira (REGRA PRINCIPAL DO PDV)
     */
    public function validarCredito(Cliente $cliente, float $valorNovaVenda, array $pagamentos = []): array
    {
        return DB::transaction(function () use ($cliente, $valorNovaVenda, $pagamentos) {

            $valorCarteira = $pagamentos['carteira']['valor'] ?? 0;

            // 🔒 regra de negócio
            if (strtoupper($cliente->tipo_cliente) === 'BALCAO' && $valorCarteira > 0) {
                return [
                    'aprovado' => false,
                    'mensagem' => 'Cliente balcão não pode usar carteira.'
                ];
            }

            // 🔒 valida limite de crédito
            if (!$this->temLimiteDisponivel($cliente, $valorNovaVenda)) {
                return [
                    'aprovado' => false,
                    'mensagem' => 'Limite de crédito insuficiente.'
                ];
            }

            return [
                'aprovado' => true,
                'saldo_atual' => $this->saldoDevedor($cliente),
                'limite' => $cliente->limite_credito
            ];
        });
    }

    /**
     * Atualiza status do cliente baseado no risco financeiro
     */
    public function atualizarStatusCliente(Cliente $cliente): void
    {
        DB::transaction(function () use ($cliente) {

            $devedor = $this->saldoDevedor($cliente);

            $cliente->ativo = !(
                $devedor >= $cliente->limite_credito &&
                $cliente->limite_credito > 0
            );

            $cliente->save();
        });
    }

    /**
     * Verifica atraso de pagamento (FIADO)
     */
    public function possuiPagamentoEmAtraso(Cliente $cliente): bool
    {
        return PagamentoVenda::whereHas('venda', function ($q) use ($cliente) {
                $q->where('cliente_id', $cliente->id);
            })
            ->where('forma_pagamento', 'credito')
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', now())
            ->exists();
    }

    /**
     * Retorna formas de pagamento permitidas no PDV (REGRA CENTRAL)
     */
    public function formasPermitidas(Cliente $cliente, float $valorVenda = 0): array
    {
        return DB::transaction(function () use ($cliente, $valorVenda) {

            $formas = [
                'dinheiro',
                'pix',
                'cartao_credito',
                'cartao_debito'
            ];

            // 💰 carteira (saldo real)
            if ($this->podeUsarCarteira($cliente)) {

                $saldo = app(ContaCorrenteService::class)
                    ->saldoAtual($cliente->id);

                if ($saldo > 0) {
                    $formas[] = 'carteira';
                }
            }

            // 🧾 crédito (fiado)
            if ($cliente->limite_credito > 0) {

                if ($this->temLimiteDisponivel($cliente, $valorVenda)) {
                    $formas[] = 'credito';
                }
            }

            return $formas;
        });
    }
}