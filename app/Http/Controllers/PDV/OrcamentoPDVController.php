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
    $codigo = trim((string) $codigo);

    $orcamento = Orcamento::with(['cliente', 'itens.produto.unidadeMedida'])
        ->where('codigo_orcamento', $codigo)
        ->where('status', 'Aprovado')
        ->first();

    if (!$orcamento) {
        return response()->json([
            'success' => false,
            'message' => 'Orçamento não encontrado ou não aprovado',
            'codigo_recebido' => $codigo
        ], 404);
    }

    // Transforma os itens para incluir preco_unitario e subtotal
    $orcamento->itens->transform(function ($item) {
        return [
            'id' => $item->id,
            'produto_id' => $item->produto_id,
            'quantidade' => $item->quantidade,
            'preco_unitario' => $item->preco_unitario,
            'subtotal' => $item->subtotal,
            'produto' => $item->produto ? [
                'nome' => $item->produto->nome,
                'descricao' => $item->produto->descricao,
                'unidade_medida' => $item->produto->unidadeMedida ? [
                    'sigla' => $item->produto->unidadeMedida->sigla
                ] : null
            ] : null
        ];
    });

    return response()->json([
        'success' => true,
        'orcamento' => $orcamento
    ]);
}






}
