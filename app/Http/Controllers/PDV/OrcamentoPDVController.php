<?php

namespace App\Http\Controllers\PDV;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrcamentoPDVController extends Controller
{
    /**
     * Buscar orçamento aprovado para uso no PDV
     */
    public function buscar($codigo)
    {
      $codigo = trim((string) $codigo);

        // Busca o orçamento trazendo qualquer status, sem bloquear direto no SQL
        $orcamento = Orcamento::with(['cliente', 'itens.produto.unidadeMedida'])
            ->where('codigo_orcamento', $codigo)
            ->first();

        // Se o orçamento REALMENTE não existir no banco de dados
        if (!$orcamento) {
            return response()->json([
                'success' => false,
                'message' => 'Orçamento não encontrado no sistema.',
                'codigo_recebido' => $codigo
            ], 404);
        }

        // Se o orçamento existe, o retorno deve ser de SUCESSO. 
        // O JavaScript vai ler o campo 'status' e decidir qual modal exibir para o operador!
        return response()->json([
            'success' => true,
            'orcamento' => $orcamento
        ], 200);


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
