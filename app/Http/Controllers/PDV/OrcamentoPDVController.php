<?php

namespace App\Http\Controllers\PDV;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;

class OrcamentoPDVController extends Controller
{
    /**
     * Buscar orçamento aprovado para uso no PDV
     */
    public function buscar($codigo)
    {
        $orcamento = Orcamento::with([
                'cliente',
                'itens.produto'
            ])
            ->where('codigo_orcamento', $codigo)
            ->where('status', 'Aprovado')
            ->where('ativo', 1)
            ->first();

        if (!$orcamento) {
            return response()->json([
                'success' => false,
                'message' => 'Orçamento não encontrado ou não aprovado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'orcamento' => [
                'id'       => $orcamento->id,
                'codigo'   => $orcamento->codigo_orcamento,
                'data'     => $orcamento->data_orcamento,
                'validade' => $orcamento->validade,
                'total'    => $orcamento->total,
                'cliente'  => [
                    'id'   => $orcamento->cliente->id,
                    'nome' => $orcamento->cliente->nome,
                ],
                'itens' => $orcamento->itens->map(function ($item) {
                    return [
                        'produto_id' => $item->produto->id,
                        'descricao'  => $item->produto->nome,
                        'quantidade' => $item->quantidade,
                        'preco_unit' => $item->preco_unitario,
                        'subtotal'   => $item->subtotal,
                    ];
                }),
            ]
        ]);
    }
}
