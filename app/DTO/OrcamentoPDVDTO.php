<?php

namespace App\DTO;

use App\Models\Orcamento;

class OrcamentoPDVDTO
{
    public static function fromModel(Orcamento $orcamento): array
    {
return response()->json([
    'success' => true,
    'orcamento' => [
        'id'       => $orcamento->id,
        'codigo'   => $orcamento->codigo_orcamento,
        'data'     => $orcamento->data_orcamento,
        'validade' => $orcamento->validade,
        'total'    => $orcamento->total,

        'cliente' => [
            'id'       => $cliente->id,
            'nome'     => $cliente->nome,
            'cpf_cnpj' => $cliente->cpf_cnpj ?? '',
            'telefone' => $cliente->telefone ?? '',
            'endereco' => [
                'logradouro' => $cliente->endereco ?? '',
                'numero'     => $cliente->numero ?? '',
                'bairro'     => $cliente->bairro ?? '',
                'cidade'     => $cliente->cidade ?? '',
                'estado'     => $cliente->estado ?? '',
                'cep'        => $cliente->cep ?? '',
                'entrega'    => $cliente->endereco_entrega ?? '',
            ],
        ],

        'itens' => $orcamento->itens->map(function ($item) {
            return [
                'produto_id' => $item->produto_id,
                'descricao'  => $item->produto->descricao ?? '',
                'quantidade' => $item->quantidade,
                'preco_unit' => $item->preco_unitario,
                'subtotal'   => $item->subtotal,
            ];
            })->values()
        ];
    }
}