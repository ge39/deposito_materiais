<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Venda;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\DB;

class CreditoService
{
    /**
     * Verifica se cliente pode usar carteira, Venda à vista continua funcionando.
     */
    public function podeUsarCarteira(Cliente $cliente): bool
    {
        if (!$cliente->ativo) return false;
        if ($cliente->bloqueado_credito) return false;
        if ($cliente->limite_credito <= 0) return false;

        // 🔥 NOVA REGRA
        $saldoConta = app(ContaCorrenteService::class)->saldoAtual($cliente->id);

        if ($saldoConta < 0) return false;

        return true;
    }
    /**
     * Calcula saldo devedor atual do cliente
     */

    public function saldoDevedor(Cliente $cliente): float
    {
        return PagamentoVenda::whereHas('venda', function ($query) use ($cliente) {
                $query->where('cliente_id', $cliente->id)
                    ->where('status', '!=', 'cancelada');
            })
            ->where('forma_pagamento', 'credito')
            ->where('status', 'pendente')
            ->sum('valor');
    }

    /**
     * Verifica se há limite disponível
     */
   public function temLimiteDisponivel(Cliente $cliente, float $valorNovaVenda): bool
    {
        // saldo devedor (pendente)
        $saldoDevedor = $this->saldoDevedor($cliente);

        // limite total
        $limite = $cliente->limite_credito;

        // crédito disponível
        $disponivel = $limite - $saldoDevedor;

        return $valorNovaVenda <= $disponivel;
    }

    /**
     * Método principal de validação
     */

    public function validarCredito(Cliente $cliente, float $valorNovaVenda, array $pagamentos = []): array
    {
        // Se houver pagamento em carteira, valida permissão
        // $valorCarteira = isset($pagamentos['carteira']) ? (float) $pagamentos['carteira']['valor'] : 0;
        $valorCarteira = $pagamentos['carteira']['valor'] ?? 0;

        if ($valorCarteira > 0 && !$this->podeUsarCarteira($cliente)) {
            return [
                'aprovado' => false,
                'mensagem' => 'Cliente possui débito em aberto. Carteira bloqueada.'
            ];
        }
        
        if (in_array(strtoupper($cliente->tipo_cliente), ['BALCAO'])) {
            // Remove carteira dos pagamentos ou bloqueia caso tente usar
            unset($pagamentos['carteira']);
        }

        if ($valorCarteira > 0 && !$this->podeUsarCarteira($cliente)) {
            return [
                'aprovado' => false,
                'mensagem' => 'Cliente não possui permissão para usar carteira.'
            ];
        }

        // Valida limite apenas se houver valor em carteira
        if ($valorCarteira > 0 && !$this->temLimiteDisponivel($cliente, $valorNovaVenda)) {
            return [
                'aprovado' => false,
                'mensagem' => 'Limite de crédito insuficiente.'
            ];
        }

        return [
            'aprovado' => true,
            'mensagem' => 'Crédito aprovado.',
            'saldo_atual' => $this->saldoDevedor($cliente),
            'limite' => $cliente->limite_credito
        ];
    }

    public function atualizarStatusCliente(Cliente $cliente): void
    {
        $saldo = $this->saldoDevedor($cliente);

        if ($saldo >= $cliente->limite_credito && $cliente->limite_credito > 0) {
            $cliente->ativo = 0; // bloqueia
        } else {
            $cliente->ativo = 1; // libera
        }

        $cliente->save();
    }

    public function possuiPagamentoEmAtraso(Cliente $cliente): bool
    {
        return PagamentoVenda::whereHas('venda', function ($query) use ($cliente) {
                $query->where('cliente_id', $cliente->id);
            })
            ->where('forma_pagamento', 'carteira')
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', now())
            ->exists();
    }

    public function ajustarScore(Cliente $cliente, int $variacao, string $motivo)
    {
        $scoreAnterior = $cliente->score_credito;

        $cliente->score_credito += $variacao;
        $cliente->score_credito = max(0, min(100, $cliente->score_credito));
        $cliente->save();

        $this->registrarHistorico(
            $cliente,
            'ajuste_score',
            $motivo,
            $scoreAnterior,
            $cliente->score_credito
        );
    }

    /**
     * Retorna as formas de pagamento permitidas para o cliente
     */
    public function formasPermitidas(Cliente $cliente): array
    {
        $formas = [
            'dinheiro',
            'pix',
            'cartao_credito',
            'cartao_debito'
        ];

        // Se cliente puder usar carteira, adiciona
        if ($this->podeUsarCarteira($cliente)) {
            $formas[] = 'carteira';
        }

        return $formas;
    }
        
}