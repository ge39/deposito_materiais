<?php
namespace App\Services;
use App\Models\MovimentacaoOrcamento;

class MovimentacaoOrcamentoService
{
    public function registrar(
        int $loteId,
        int $orcamentoId,
        ?int $itemId,
        string $tipo,
        float $antes,
        float $depois,
        string $descricao = null,
        string $origem = 'sistema'
    ): void {

        MovimentacaoOrcamento::create([
            'lote_id' => $loteId,
            'orcamento_id' => $orcamentoId,
            'item_orcamento_id' => $itemId,
            'user_id' => auth()->id(),
            'tipo' => $tipo,
            'descricao' => $descricao,
            'quantidade_antes' => $antes,
            'quantidade_depois' => $depois,
            'origem' => $origem,
        ]);
    }
}