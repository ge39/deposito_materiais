<?php

namespace App\Http\Controllers\PDV;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrcamentoPDVController extends Controller
{
    /**
     * Busca um orçamento para carregamento no PDV.
     */
    public function buscar($codigo): JsonResponse
    {
        $codigo = trim((string) $codigo);

        $orcamento = Orcamento::with([
            'cliente',
            'itens.produto.unidadeMedida',
        ])
            ->where('codigo_orcamento', $codigo)
            ->first();

        if (! $orcamento) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Orçamento não encontrado no sistema.',
                    'codigo_recebido' => $codigo,
                ],
                404
            );
        }

        $itensIds = $orcamento->itens
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            )
            ->all();

        $reservasPorItem = collect();

        if (! empty($itensIds)) {
            $reservasPorItem = DB::table(
                'item_orcamento_lotes as iol'
            )
                ->join(
                    'lotes as l',
                    'l.id',
                    '=',
                    'iol.lote_id'
                )
                ->whereIn(
                    'iol.item_orcamento_id',
                    $itensIds
                )
                ->where(
                    'iol.quantidade_reservada',
                    '>',
                    0
                )
                ->select([
                    'iol.id as item_orcamento_lote_id',
                    'iol.item_orcamento_id',
                    'iol.lote_id',
                    'iol.quantidade_reservada',
                    'iol.quantidade_atendida',
                    'l.numero_lote',
                ])
                ->orderBy('iol.item_orcamento_id')
                ->orderBy('iol.id')
                ->get()
                ->groupBy('item_orcamento_id');
        }

        $itensPDV = collect();

        foreach ($orcamento->itens as $item) {
            $quantidadeSolicitada = round(
                (float) (
                    $item->quantidade_solicitada
                    ?? $item->quantidade
                    ?? 0
                ),
                3
            );

            $reservas = $reservasPorItem->get(
                $item->id,
                collect()
            );

            /*
             * Item sem lote reservado.
             * Continua sendo enviado ao PDV com lote nulo.
             */
            if ($reservas->isEmpty()) {
                $itensPDV->push(
                    $this->montarItemPDV(
                        $item,
                        null,
                        $quantidadeSolicitada,
                        $quantidadeSolicitada
                    )
                );

                continue;
            }

            $quantidadeRestante = $quantidadeSolicitada;

            /*
             * Cada lote reservado gera uma linha própria no carrinho.
             */
            foreach ($reservas as $reserva) {
                if ($quantidadeRestante <= 0) {
                    break;
                }

                $quantidadeReservada = round(
                    (float) $reserva->quantidade_reservada,
                    3
                );

                $quantidadeDoLote = min(
                    $quantidadeRestante,
                    $quantidadeReservada
                );

                if ($quantidadeDoLote <= 0) {
                    continue;
                }

                $itensPDV->push(
                    $this->montarItemPDV(
                        $item,
                        $reserva,
                        $quantidadeDoLote,
                        $quantidadeSolicitada
                    )
                );

                $quantidadeRestante = round(
                    $quantidadeRestante
                    - $quantidadeDoLote,
                    3
                );
            }

            /*
             * Se parte da quantidade solicitada ainda não possui lote,
             * mantém uma linha sem lote para preservar o faturamento.
             */
            if ($quantidadeRestante > 0) {
                $itensPDV->push(
                    $this->montarItemPDV(
                        $item,
                        null,
                        $quantidadeRestante,
                        $quantidadeSolicitada
                    )
                );
            }
        }

        $dadosOrcamento = $orcamento->toArray();
        $dadosOrcamento['itens'] = $itensPDV->values()->all();

        return response()->json(
            [
                'success' => true,
                'orcamento' => $dadosOrcamento,
            ],
            200
        );
    }

    /**
     * Monta uma linha do orçamento para o carrinho do PDV.
     */
    private function montarItemPDV(
        $item,
        $reserva,
        float $quantidade,
        float $quantidadeOriginal
    ): array {
        $proporcao = $quantidadeOriginal > 0
            ? $quantidade / $quantidadeOriginal
            : 0;

        $subtotalOriginal = (float) (
            $item->subtotal
            ?? 0
        );

        $descontoOriginal = (float) (
            $item->valor_desconto
            ?? 0
        );

        return [
            'id' => $item->id,
            'item_orcamento_id' => $item->id,

            'item_orcamento_lote_id' =>
                $reserva?->item_orcamento_lote_id,

            'produto_id' => $item->produto_id,

            'lote_id' => $reserva
                ? (int) $reserva->lote_id
                : null,

            'numero_lote' => $reserva?->numero_lote,

            'quantidade_solicitada' => round(
                $quantidade,
                3
            ),

            'quantidade' => round(
                $quantidade,
                3
            ),

            'quantidade_atendida' => $reserva
                ? round(
                    (float) $reserva->quantidade_atendida,
                    3
                )
                : 0,

            'preco_unitario' => $item->preco_unitario,
            'preco_liquido' => $item->preco_liquido,

            'valor_desconto' => round(
                $descontoOriginal * $proporcao,
                2
            ),

            'subtotal' => round(
                $subtotalOriginal * $proporcao,
                2
            ),

            'produto' => $item->produto
                ? [
                    'id' => $item->produto->id,
                    'nome' => $item->produto->nome,
                    'descricao' =>
                        $item->produto->descricao,

                    'unidade_medida' =>
                        $item->produto->unidadeMedida
                            ? [
                                'sigla' =>
                                    $item
                                        ->produto
                                        ->unidadeMedida
                                        ->sigla,
                            ]
                            : null,
                ]
                : null,
        ];
    }
}