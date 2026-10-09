<?php

namespace App\Services\BI;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class Bi07ScoreService
{
    /**
     * Score gerencial interno (0 a 10).
     *
     * Componentes:
     * - Situacao dos vencimentos: 0 a 4
     * - Historico registrado de pagamentos: 0 a 2
     * - Utilizacao do limite: 0 a 1.5
     * - Historico de compras: 0 a 1.5
     *
     * Nao equivale a score de bureau de credito.
     * Nao concede nem bloqueia credito automaticamente.
     */
    public function calcular(): array
    {
        $hoje = CarbonImmutable::today()->toDateString();

        // Resumo de credito ja existente no ERP.
        $creditos = DB::table('vw_cliente_credito_resumo')
            ->get()
            ->keyBy('cliente_id');

        // Obrigações de Carteira ainda pendentes.
        $pendencias = DB::table('pagamentos_venda as p')
            ->join('vendas as v', 'v.id', '=', 'p.venda_id')
            ->where('p.forma_pagamento', 'carteira')
            ->where('p.status', 'Pendente')
            ->where('v.status', '<>', 'cancelada')
            ->whereNotNull('v.cliente_id')
            ->select('v.cliente_id')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(p.valor),0) as total_aberto')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN p.data_vencimento < ? THEN p.valor ELSE 0 END),0) as total_vencido',
                [$hoje]
            )
            ->selectRaw(
                'MAX(CASE WHEN p.data_vencimento < ? THEN DATEDIFF(?,p.data_vencimento) ELSE 0 END) as maior_atraso',
                [$hoje, $hoje]
            )
            ->groupBy('v.cliente_id')
            ->get()
            ->keyBy('cliente_id');

        // Pagamentos efetivamente registrados como credito
        // de origem pagamento na conta corrente.
        // Historicos ausentes nao sao inventados.
        $pagamentos = DB::table('cliente_conta_correntes')
            ->where('tipo', 'credito')
            ->where('origem', 'pagamento')
            ->select('cliente_id')
            ->selectRaw('COUNT(*) as quantidade')
            ->groupBy('cliente_id')
            ->get()
            ->keyBy('cliente_id');

        $compras = DB::table('vendas')
            ->where('status', 'finalizada')
            ->whereNotNull('cliente_id')
            ->select('cliente_id')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('MIN(data_venda) as primeira_compra')
            ->groupBy('cliente_id')
            ->get()
            ->keyBy('cliente_id');

        $ids = $creditos->keys()
            ->merge($pendencias->keys())
            ->merge($pagamentos->keys())
            ->merge($compras->keys())
            ->unique();

        $resultado = [];

        foreach ($ids as $id) {
            $credito = $creditos->get($id);
            $pendencia = $pendencias->get($id);
            $pagamento = $pagamentos->get($id);
            $compra = $compras->get($id);

            $qtdCompras = (int) ($compra->quantidade ?? 0);
            $qtdPagamentos = (int) ($pagamento->quantidade ?? 0);
            $qtdPendencias = (int) ($pendencia->quantidade ?? 0);

            // Sem limite cadastrado, nao calcular um
            // score financeiro completo e artificial.
            $elegivel = $credito !== null
                && (
                    $qtdCompras >= 2
                    || $qtdPagamentos > 0
                    || $qtdPendencias > 0
                );

            if (!$elegivel) {
                $resultado[$id] = [
                    'score' => null,
                    'classe' => 'secondary',
                    'situacao' => 'Nao avaliado',
                    'motivo' => 'Historico ou cadastro de credito insuficiente',
                ];
                continue;
            }

            $aberto = max(0, (float) ($pendencia->total_aberto ?? 0));
            $vencido = max(0, (float) ($pendencia->total_vencido ?? 0));
            $dias = max(0, (int) ($pendencia->maior_atraso ?? 0));

            // 1. SITUACAO DOS VENCIMENTOS - 4 pontos
            // Penaliza atrasos ativos, sobretudo antigos.
            if ($vencido <= 0) {
                $pontosAtraso = 4.0;
            } elseif ($dias <= 7) {
                $pontosAtraso = 3.0;
            } elseif ($dias <= 30) {
                $pontosAtraso = 2.0;
            } elseif ($dias <= 60) {
                $pontosAtraso = 1.0;
            } else {
                $pontosAtraso = 0.0;
            }

            // Penalizacao adicional proporcional ao valor
            // vencido no saldo em aberto.
            if ($aberto > 0 && $vencido / $aberto >= 0.75) {
                $pontosAtraso = max(0, $pontosAtraso - 0.5);
            }

            // 2. HISTORICO DE PAGAMENTOS - 2 pontos
            // Mede evidencias de pagamentos registrados,
            // NAO afirma pontualidade historica sem prova.
            if ($qtdPagamentos >= 5) {
                $pontosPagamento = 2.0;
            } elseif ($qtdPagamentos >= 3) {
                $pontosPagamento = 1.5;
            } elseif ($qtdPagamentos >= 1) {
                $pontosPagamento = 1.0;
            } else {
                $pontosPagamento = 0.0;
            }

            // 3. UTILIZACAO DO CREDITO - 1.5 ponto
            $limite = max(0, (float) ($credito->limite_credito ?? 0));
            $utilizado = max(0, (float) ($credito->total_usado ?? 0));

            if ($limite <= 0) {
                // Falta informacao para esta componente.
                $resultado[$id] = [
                    'score' => null,
                    'classe' => 'secondary',
                    'situacao' => 'Nao avaliado',
                    'motivo' => 'Limite de credito nao informado ou zerado',
                ];
                continue;
            }

            $ocupacao = $utilizado / $limite;

            if ($ocupacao <= 0.50) {
                $pontosCredito = 1.5;
            } elseif ($ocupacao <= 0.80) {
                $pontosCredito = 1.0;
            } elseif ($ocupacao <= 1.0) {
                $pontosCredito = 0.5;
            } else {
                $pontosCredito = 0.0;
            }

            // 4. RELACIONAMENTO - 1.5 ponto
            // Regularidade aproximada por historico
            // disponivel, nao por julgamento subjetivo.
            $mesesRelacionamento = 0;

            if ($compra && $compra->primeira_compra) {
                $mesesRelacionamento = max(
                    0,
                    (int) CarbonImmutable::parse($compra->primeira_compra)
                        ->diffInMonths(CarbonImmutable::today())
                );
            }

            if ($qtdCompras >= 10 && $mesesRelacionamento >= 6) {
                $pontosRelacionamento = 1.5;
            } elseif ($qtdCompras >= 5 && $mesesRelacionamento >= 3) {
                $pontosRelacionamento = 1.0;
            } elseif ($qtdCompras >= 2) {
                $pontosRelacionamento = 0.5;
            } else {
                $pontosRelacionamento = 0.0;
            }

            $score = round(
                min(10, max(0,
                    $pontosAtraso
                    + $pontosPagamento
                    + $pontosCredito
                    + $pontosRelacionamento
                )),
                1
            );

            if ($score >= 8) {
                $classe = 'success';
                $situacao = 'Excelente';
            } elseif ($score >= 6) {
                $classe = 'info';
                $situacao = 'Bom';
            } elseif ($score >= 4) {
                $classe = 'warning';
                $situacao = 'Atencao';
            } else {
                $classe = 'danger';
                $situacao = 'Alto risco';
            }

            $resultado[$id] = [
                'score' => $score,
                'classe' => $classe,
                'situacao' => $situacao,
                'motivo' => 'Indicador gerencial calculado a partir dos dados financeiros registrados',
            ];
        }

        return $resultado;
    }
}