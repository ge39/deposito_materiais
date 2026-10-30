<?php

namespace App\Services\Estoque;

use Illuminate\Support\Facades\DB;

class ReposicaoPromessasService
{
    /**
     * ESTDIV02_PROD_V3
     *
     * Reposicao das promessas de entrega.
     * Somente leitura. Nao movimenta estoque.
     *
     * FIFO pelo orcamento mais antigo.
     * Nao desconta reservas historicas dos lotes.
     * Considera os compromissos pre-rota uma unica vez.
     */
    public function calcular(): array
    {
        return DB::transaction(function (): array {

            $alvos = [
                'Aguardando_estoque',
                'Aguardando_separacao',
            ];

            // Outros compromissos ainda em preparacao.
            // Disputam o mesmo saldo pelo FIFO.
            $concorrentes = [
                'Pendente_pagamento',
                'Aguardando_faturamento',
                'Material_separado',
                'Separacao_conferida',
                'Material_carregado',
                'Saida_conferida',
                'Liberado',
                'Em_preparacao',
                'Pronta_para_carregamento',
                'Carregada',
                'Liberada',
                'Reagendada',
            ];

            // Entregas em rota, no destino ou encerradas
            // nao consomem novamente estoque para separacao.
            $linhas = DB::table('entrega_itens as ei')
                ->join(
                    'entregas as e',
                    'e.id',
                    '=',
                    'ei.entrega_id'
                )
                ->join(
                    'orcamentos as o',
                    'o.id',
                    '=',
                    'e.orcamento_id'
                )
                ->join(
                    'item_orcamentos as io',
                    'io.id',
                    '=',
                    'ei.item_orcamento_id'
                )
                ->join(
                    'produtos as p',
                    'p.id',
                    '=',
                    'io.produto_id'
                )
                ->where('o.tipo_entrega', 'entrega')
                ->whereIn(
                    'e.status',
                    array_merge($alvos, $concorrentes)
                )
                ->whereRaw(
                    "LOWER(TRIM(COALESCE(o.status,'')))
                     NOT IN ('cancelado','expirado','vencido')"
                )
                ->where('ei.quantidade_prevista', '>', 0)
                ->select([
                    'ei.id as entrega_item_id',
                    'ei.entrega_id',
                    'ei.item_orcamento_id',
                    'ei.quantidade_prevista',
                    'ei.quantidade_entregue',
                    'e.status as entrega_status',
                    'o.id as orcamento_id',
                    'o.data_orcamento',
                    'io.quantidade_solicitada as total_item_orcamento',
                    'io.produto_id',
                    'p.nome as produto',
                ])
                ->orderBy('o.data_orcamento')
                ->orderBy('o.id')
                ->orderBy('e.id')
                ->orderBy('ei.id')
                ->get();

            if ($linhas->isEmpty()) {
                return [];
            }

            // Saldo operacional registrado nos lotes ativos.
            // quantidade = historico de compra, nao saldo.
            $ids = $linhas
                ->pluck('produto_id')
                ->unique()
                ->values()
                ->all();

            $saldos = DB::table('lotes')
                ->where('status', 1)
                ->whereIn('produto_id', $ids)
                ->select('produto_id')
                ->selectRaw(
                    'COALESCE(SUM(GREATEST(0,
                     COALESCE(quantidade_disponivel,0))),0)
                     AS saldo'
                )
                ->groupBy('produto_id')
                ->pluck('saldo', 'produto_id');

            $produtos = [];
            $usadoPorItem = [];

            // FIFO global: todas as demandas anteriores
            // consomem primeiro o saldo disponivel.
            foreach ($linhas as $linha) {

                $pid = (int) $linha->produto_id;
                $itemId = (int) $linha->item_orcamento_id;

                $ehAlvo = in_array(
                    $linha->entrega_status,
                    $alvos,
                    true
                );

                $original = max(
                    0.0,
                    (float) $linha->quantidade_prevista -
                    (float) ($linha->quantidade_entregue ?? 0)
                );

                if ($original < 0.0005) {
                    continue;
                }

                // Evita contabilizar entregas fracionadas
                // acima do total do item do orcamento.
                $limite = max(
                    0.0,
                    (float) $linha->total_item_orcamento -
                    ($usadoPorItem[$itemId] ?? 0.0)
                );

                $necessario = min($original, $limite);

                $usadoPorItem[$itemId] =
                    ($usadoPorItem[$itemId] ?? 0.0) +
                    $necessario;

                if (!isset($produtos[$pid])) {

                    $saldo = max(
                        0.0,
                        (float) ($saldos[$pid] ?? 0)
                    );

                    $produtos[$pid] = [
                        'produto_id' => $pid,
                        'produto' => $linha->produto,
                        'solicitado' => 0.0,
                        'coberto' => 0.0,
                        'a_repor' => 0.0,
                        'saldo_liquido' => $saldo,
                        'reservado_outras' => 0.0,
                        'disponivel_para_promessas' => $saldo,
                        'avisos' => [],
                        'detalhes' => [],
                        '_livre' => $saldo,
                        '_alvo' => false,
                    ];
                }

                $p = &$produtos[$pid];

                if ($original > $necessario + 0.0005) {
                    $p['avisos'][] =
                        'Entregas excedem o item do orcamento: '
                        . 'revisar fracionamento';
                }

                // Uma unidade de saldo cobre apenas
                // uma unidade de demanda.
                $coberto = min(
                    $necessario,
                    $p['_livre']
                );

                $p['_livre'] = max(
                    0.0,
                    $p['_livre'] - $coberto
                );

                if ($ehAlvo) {

                    $p['_alvo'] = true;
                    $p['solicitado'] += $necessario;
                    $p['coberto'] += $coberto;
                    $p['a_repor'] += max(
                        0.0,
                        $necessario - $coberto
                    );

                    $p['detalhes'][] = [
                        'orcamento_id' =>
                            (int) $linha->orcamento_id,
                        'entrega_id' =>
                            (int) $linha->entrega_id,
                        'status' => $linha->entrega_status,
                        'solicitado' => round($necessario, 3),
                        'coberto' => round($coberto, 3),
                        'a_repor' => round(
                            max(0.0, $necessario - $coberto),
                            3
                        ),
                    ];

                } else {

                    // Nao e a reserva historica do lote.
                    // E a demanda de outra promessa ativa.
                    $p['reservado_outras'] += $necessario;
                }

                unset($p);
            }

            $resultado = [];

            foreach ($produtos as $p) {

                if (!$p['_alvo']) {
                    continue;
                }

                foreach ([
                    'solicitado',
                    'coberto',
                    'a_repor',
                    'saldo_liquido',
                    'reservado_outras',
                    'disponivel_para_promessas'
                ] as $campo) {
                    $p[$campo] = round($p[$campo], 3);
                }

                $p['avisos'] = array_values(
                    array_unique($p['avisos'])
                );

                unset($p['_alvo'], $p['_livre']);

                $resultado[] = $p;
            }

            usort(
                $resultado,
                fn ($a, $b) =>
                    ($b['a_repor'] <=> $a['a_repor'])
                    ?: ($a['produto_id'] <=> $b['produto_id'])
            );

            return $resultado;
        });
    }
}