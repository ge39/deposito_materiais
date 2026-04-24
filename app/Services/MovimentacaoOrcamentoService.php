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
        $antes,
        $depois,
        ?string $descricao = null,
        string $origem = 'sistema',
        ?int $userId = null
    ): void {

        MovimentacaoOrcamento::create([
        'lote_id' => $loteId,
        'orcamento_id' => $orcamentoId,
        'item_orcamento_id' => $itemId,
        'user_id' => $userId ?? auth()->id(),
        'tipo' => $tipo,
        'descricao' => $descricao,
        'quantidade_antes' => $antes,
        'quantidade_depois' => $depois,
        'origem' => $origem,
        ]);
    }
}