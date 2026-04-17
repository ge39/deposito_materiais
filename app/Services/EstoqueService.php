<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\ItemOrcamento;
use App\Models\Orcamento;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    /**
     * ENTRADA DE LOTE
     */
    public function entradaLote(Lote $lote): void
    {
        DB::transaction(function () use ($lote) {

            if (is_null($lote->quantidade_reservada)) {
                $lote->quantidade_reservada = 0;
                $lote->save();
            }

            $this->atenderPendentesPorProduto($lote->produto_id);
        });
    }

    /**
     * ATENDER FILA FIFO
     */
    public function atenderPendentesPorProduto(int $produtoId): void
    {
        $itens = ItemOrcamento::query()
            ->join('orcamentos', 'orcamentos.id', '=', 'item_orcamentos.orcamento_id')
            ->where('item_orcamentos.produto_id', $produtoId)
            ->whereRaw('(quantidade_solicitada - quantidade_atendida) > 0')
            ->orderBy('orcamentos.data_orcamento')
            ->lockForUpdate()
            ->select('item_orcamentos.*')
            ->get();

        if ($itens->isEmpty()) return;

        $orcamentosAfetados = [];

        foreach ($itens as $item) {

            $pendente = $item->quantidade_solicitada - $item->quantidade_atendida;

            if ($pendente <= 0) continue;

            $this->distribuir($item, $produtoId, $pendente);

            $orcamentosAfetados[$item->orcamento_id] = true;
        }

        $this->atualizarStatusOrcamento(array_keys($orcamentosAfetados));
    }

    /**
     * DISTRIBUIÇÃO FIFO MULTI-LOTE
     */
    private function distribuir(ItemOrcamento $item, int $produtoId, float $quantidade): void
    {
        $restante = $quantidade;

        $lotes = $this->buscarLotesDisponiveis($produtoId);

        foreach ($lotes as $lote) {

            if ($restante <= 0) break;

            $disponivel = $this->disponivel($lote);

            if ($disponivel <= 0) continue;

            $qtd = min($restante, $disponivel);

            $this->reservarLote($lote, $qtd);
            $this->registrarLote($item, $lote, $qtd);

            $item->quantidade_atendida += $qtd;

            $restante -= $qtd;
        }

        $this->atualizarStatusItem($item);
        $item->save();
    }

    /**
     * UPSERT ITEM x LOTE
     */
    private function registrarLote(ItemOrcamento $item, Lote $lote, float $qtd): void
    {
        $registro = DB::table('item_orcamento_lotes')
            ->where('item_orcamento_id', $item->id)
            ->where('lote_id', $lote->id)
            ->lockForUpdate()
            ->first();

        if ($registro) {
            DB::table('item_orcamento_lotes')
                ->where('id', $registro->id)
                ->update([
                    'quantidade_reservada' => DB::raw("quantidade_reservada + {$qtd}"),
                    'quantidade_atendida' => DB::raw("quantidade_atendida + {$qtd}"),
                ]);
        } else {
            DB::table('item_orcamento_lotes')->insert([
                'item_orcamento_id' => $item->id,
                'lote_id' => $lote->id,
                'quantidade_reservada' => $qtd,
                'quantidade_atendida' => $qtd,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * LOTES DISPONÍVEIS
     */
    private function buscarLotesDisponiveis(int $produtoId)
    {
        return Lote::where('produto_id', $produtoId)
            ->where('status', 1)
            ->whereRaw('(quantidade - quantidade_reservada) > 0')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();
    }

    /**
     * DISPONÍVEL REAL
     */
    private function disponivel(Lote $lote): float
    {
        return $lote->quantidade - $lote->quantidade_reservada;
    }

    /**
     * RESERVA LOTE
     */
    private function reservarLote(Lote $lote, float $qtd): void
    {
        if ($qtd > $this->disponivel($lote)) {
            throw new \Exception("Estoque insuficiente no lote {$lote->id}");
        }

        $lote->increment('quantidade_reservada', $qtd);
    }

     /**
     * CANCELAMENTO DE RESERVA
     */
    public function cancelarReserva(ItemOrcamento $item): void
    {
        $vinculos = DB::table('item_orcamento_lotes')
            ->where('item_orcamento_id', $item->id)
            ->get();

        foreach ($vinculos as $v) {

            $lote = Lote::lockForUpdate()->find($v->lote_id);

            if ($lote) {
                $lote->quantidade_reservada = max(
                    0,
                    $lote->quantidade_reservada - $v->quantidade_reservada
                );
                $lote->save();
            }
        }

        DB::table('item_orcamento_lotes')
            ->where('item_orcamento_id', $item->id)
            ->delete();

        $item->update([
            'quantidade_atendida' => 0,
            'quantidade_pendente' => $item->quantidade_solicitada,
            'status' => 'indisponivel'
        ]);

        $this->atenderPendentesPorProduto($item->produto_id);
        $this->atualizarStatusOrcamento([$item->orcamento_id]);
    }

     public function reservar(int $itemId, int $produtoId, float $quantidade): void
    {
        DB::transaction(function () use ($itemId, $produtoId, $quantidade) {

            $item = ItemOrcamento::lockForUpdate()->findOrFail($itemId);

            $this->distribuir($item, $produtoId, $quantidade);

            $this->atualizarStatusOrcamento([$item->orcamento_id]);
        });
    }

    /**
     * STATUS DO ITEM
     */
    private function atualizarStatusItem(ItemOrcamento $item): void
    {
        $pendente = $item->quantidade_solicitada - $item->quantidade_atendida;

        if ($item->quantidade_atendida <= 0) {
            $item->status = 'indisponivel';
        } elseif ($pendente > 0) {
            $item->status = 'parcial';
        } else {
            $item->status = 'disponivel';
        }
    }

    /**
     * STATUS DO ORÇAMENTO
     */
    private function atualizarStatusOrcamento(array $ids): void
    {
        foreach ($ids as $id) {

            $temPendente = ItemOrcamento::where('orcamento_id', $id)
                ->whereRaw('(quantidade_solicitada - quantidade_atendida) > 0')
                ->exists();

            Orcamento::where('id', $id)->update([
                'status' => $temPendente ? 'Aguardando Estoque' : 'Aguardando Aprovacao'
            ]);
        }
    }
}