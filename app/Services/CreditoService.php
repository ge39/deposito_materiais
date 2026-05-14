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
        return DB::transaction(function () use ($cliente) {
            if (!$cliente->ativo || $cliente->ativo === 'inativo') return false;

            // 🔥 CORREÇÃO: Busca as informações da relação correta 'creditoAtivo'
            $credito = $cliente->creditoAtivo;
            if (!$credito) return false;

            // Se o status no banco for explicitamente bloqueado, barra na hora
            if ($credito->status === 'bloqueado') return false;
            if ((float)$credito->limite_credito <= 0) return false;

            // Consulta o saldo atual real na conta corrente
            $saldo = app(ContaCorrenteService::class)->saldoAtual($cliente->id);

            // 🔥 CORREÇÃO REQUISITO: Se o saldo for menor ou igual a zero, o input deve ser bloqueado
            return $saldo > 0;
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
            $credito = $cliente->creditoAtivo;
            if (!$credito || $credito->status === 'bloqueado') return false;

            $limiteCredito = (float)($credito->limite_credito ?? 0);
            $devedor = $this->saldoDevedor($cliente);

            return ($devedor + $valorNovaVenda) <= $limiteCredito;
        });
    }

    /**
     * Validação completa de crédito + carteira (REGRA PRINCIPAL DO PDV)
     */
    public function validarCredito(Cliente $cliente, float $valorNovaVenda, array $pagamentos = []): array
    {
        return DB::transaction(function () use ($cliente, $valorNovaVenda, $pagamentos) {
            $valorCarteira = $pagamentos['carteira']['valor'] ?? 0;

            if (strtoupper($cliente->tipo_cliente) === 'BALCAO' && $valorCarteira > 0) {
                return [
                    'aprovado' => false,
                    'mensagem' => 'Cliente balcão não pode usar carteira.'
                ];
            }

            // 🔥 CORREÇÃO: Valida o status e saldo do cliente antes de checar limites amplos
            $credito = $cliente->creditoAtivo;
            $saldo = app(ContaCorrenteService::class)->saldoAtual($cliente->id);

            if (optional($credito)->status === 'bloqueado' || $saldo <= 0) {
                return [
                    'aprovado' => false,
                    'mensagem' => 'O crediário/carteira deste cliente encontra-se bloqueado ou sem saldo.'
                ];
            }

            // Valida limite de crédito geral
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
        });
    }

    /**
     * Atualiza status do cliente baseado no risco financeiro
     */
    public function atualizarStatusCliente(Cliente $cliente): void
    {
        DB::transaction(function () use ($cliente) {
            $credito = $cliente->creditoAtivo;
            if (!$credito) return;

            $limiteCredito = (float)($credito->limite_credito ?? 0);
            $devedor = $this->saldoDevedor($cliente);

            // Se estourar o limite, desativa o status do crédito
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

            // 🔥 CORREÇÃO: Mapeia do relacionamento correto 'creditoAtivo' unificado no seu PDV
            $status = $cliente->creditoAtivo->status ?? null;
            $creditoAtivo = $status === 'ativo';

            // 💰 CARTEIRA (Só adiciona se o crédito estiver ativo E passar na regra de saldo > 0)
            if ($creditoAtivo && $this->podeUsarCarteira($cliente)) {
                $formas[] = 'carteira';
            }

            // 🧾 CRÉDITO (FIADO)
            $limiteCredito = (float)($cliente->creditoAtivo->limite_credito ?? 0);
            if ($creditoAtivo && $limiteCredito > 0) {
                if ($this->temLimiteDisponivel($cliente, $valorVenda)) {
                    $formas[] = 'credito';
                }
            }

            return $formas;
        });
    }
}
