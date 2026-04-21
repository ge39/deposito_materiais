<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\ItemOrcamento;
use App\Models\Orcamento;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    
    public static bool $ignorarObserver = false;

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
    // private function distribuir(ItemOrcamento $item, int $produtoId, float $quantidade): void
    // {
    //     $this->limparDistribuicao($item);
            
    //     $restante = $quantidade;

    //     $lotes = $this->buscarLotesDisponiveis($produtoId);

    //     foreach ($lotes as $lote) {

    //         if ($restante <= 0) break;

    //         $disponivel = $this->disponivel($lote);

    //         if ($disponivel <= 0) continue;

    //         $qtd = min($restante, $disponivel);

    //         // 🔒 reserva no lote
    //         $this->reservarLote($lote, $qtd);

    //         // 🔗 registra vínculo
    //         $this->registrarLote($item, $lote, $qtd);

    //         $restante -= $qtd;
    //     }

    //     // 🔥 sempre recalcula após mexer nos lotes
    //     $this->recalcularItem($item);
    // }

    private function distribuir(ItemOrcamento $item, int $produtoId, float $quantidade): void
    {
        $this->limparDistribuicao($item);

        $restante = $quantidade;

        $lotes = $this->buscarLotesDisponiveis($produtoId);

        foreach ($lotes as $lote) {

            if ($restante <= 0) break;

            $disponivel = $this->disponivel($lote);

            if ($disponivel <= 0) continue;

            $qtd = min($restante, $disponivel);

            $this->reservarLote($lote, $qtd);

            $this->registrarLote($item, $lote, $qtd);

            $restante -= $qtd;
        }

        $this->recalcularItem($item);
    }

    private function limparDistribuicao(ItemOrcamento $item): void
    {
        // remove vínculos antigos
        DB::table('item_orcamento_lotes')
            ->where('item_orcamento_id', $item->id)
            ->delete();

        // zera reservas nos lotes relacionados
        Lote::whereIn('id', function ($q) use ($item) {
            $q->select('lote_id')
                ->from('item_orcamento_lotes')
                ->where('item_orcamento_id', $item->id);
        })->decrement('quantidade_reservada', 0); // safe no-op base
    }
    /**
     * UPSERT ITEM x LOTE
     */
    // private function registrarLote(ItemOrcamento $item, Lote $lote, float $qtd): void
    // {
    //     $registro = DB::table('item_orcamento_lotes')
    //         ->where('item_orcamento_id', $item->id)
    //         ->where('lote_id', $lote->id)
    //         ->lockForUpdate()
    //         ->first();

    //     if ($registro) {
    //         DB::table('item_orcamento_lotes')
    //             ->where('id', $registro->id)
    //             ->update([
    //                 'quantidade_reservada' => DB::raw("quantidade_reservada + {$qtd}"),
    //                 'updated_at' => now(),
    //             ]);
    //     } else {
    //         DB::table('item_orcamento_lotes')->insert([
    //             'item_orcamento_id' => $item->id,
    //             'lote_id' => $lote->id,
    //             'quantidade_reservada' => $qtd,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }
    // }
    private function registrarLote(ItemOrcamento $item, Lote $lote, float $qtd): void
    {
        // 🔒 1. VALIDAÇÃO DE INTEGRIDADE
        // Garante que o lote pertence ao mesmo produto do item do orçamento
        // Evita inconsistência de dados (ex: reservar lote de outro produto)
        if ($lote->produto_id !== $item->produto_id) {
            throw new \Exception(
                "Lote {$lote->id} não pertence ao produto {$item->produto_id}"
            );
        }

        // 🚀 2. UPSERT ATÔMICO (INSERT + UPDATE)
        // Esse comando faz duas coisas automaticamente:
        //
        // ✔ Se NÃO existir (item_orcamento_id + lote_id)
        //     → INSERE um novo registro
        //
        // ✔ Se JÁ existir
        //     → SOMA a quantidade_reservada (não sobrescreve)
        //
        // Isso evita:
        // - duplicidade de registros
        // - perda de dados
        // - problemas de concorrência (race condition)
        //
        // ⚠️ IMPORTANTE:
        // Para funcionar corretamente, é obrigatório ter índice único:
        // UNIQUE (item_orcamento_id, lote_id)
        DB::statement("
            INSERT INTO item_orcamento_lotes 
            (item_orcamento_id, lote_id, quantidade_reservada, quantidade_atendida, created_at, updated_at)
            VALUES (?, ?, ?, 0, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                -- 🔥 Soma a nova quantidade com a já existente
                quantidade_reservada = quantidade_reservada + VALUES(quantidade_reservada),

                -- 🕒 Atualiza timestamp
                updated_at = NOW()
        ", [
            $item->id,   // ID do item do orçamento
            $lote->id,   // ID do lote
            $qtd         // Quantidade a ser reservada
        ]);
    }

    /**
     * LOTES DISPONÍVEIS (FIFO)
     */
    private function buscarLotesDisponiveis(int $produtoId)
    {
        return Lote::where('produto_id', $produtoId)
            ->where('status', 1)
            ->whereRaw('(quantidade - quantidade_reservada) > 0')
            ->orderBy('created_at') // FIFO
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
    // private function reservarLote(Lote $lote, float $qtd): void
    // {
    //     if ($qtd > $this->disponivel($lote)) {
    //         throw new \Exception("Estoque insuficiente no lote {$lote->id}");
    //     }

    //     $lote->increment('quantidade_reservada', $qtd);
    // }

    private function reservarLote(Lote $lote, float $qtd): void
    {
        if ($qtd > $this->disponivel($lote)) {
            throw new \Exception("Estoque insuficiente no lote {$lote->id}");
        }

        DB::table('lotes')
            ->where('id', $lote->id)
            ->increment('quantidade_reservada', $qtd);
    }
    
    
    /**
     * CANCELAMENTO DE RESERVA
     */
    // public function cancelarReserva(ItemOrcamento $item): void
    // {
    //     DB::transaction(function () use ($item) {

    //         $vinculos = DB::table('item_orcamento_lotes')
    //             ->where('item_orcamento_id', $item->id)
    //             ->lockForUpdate()
    //             ->get();

    //         foreach ($vinculos as $v) {

    //             $lote = Lote::lockForUpdate()->find($v->lote_id);

    //             if ($lote) {
    //                 $lote->decrement('quantidade_reservada', $v->quantidade_reservada);
    //             }
    //         }

    //         DB::table('item_orcamento_lotes')
    //             ->where('item_orcamento_id', $item->id)
    //             ->delete();

    //         // 🔥 recalcula corretamente
    //         $this->recalcularItem($item);

    //         $this->atenderPendentesPorProduto($item->produto_id);
    //         $this->atualizarStatusOrcamento([$item->orcamento_id]);
    //     });
    // }
    public function cancelarReserva(ItemOrcamento $item): void
    {
        DB::transaction(function () use ($item) {

            // 🔒 pega vínculos atuais
            $vinculos = DB::table('item_orcamento_lotes')
                ->where('item_orcamento_id', $item->id)
                ->lockForUpdate()
                ->get();

            // 🔄 devolve estoque (SEM observer)
            foreach ($vinculos as $v) {
                DB::table('lotes')
                    ->where('id', $v->lote_id)
                    ->decrement('quantidade_reservada', $v->quantidade_reservada);
            }

            // 🧹 remove vínculos
            DB::table('item_orcamento_lotes')
                ->where('item_orcamento_id', $item->id)
                ->delete();

            // 🔥 recalcula item (zera atendido corretamente)
            $this->recalcularItem($item);
        });
    }

    /**
     * RESERVA DIRETA
     */
    // public function reservar(int $itemId, int $produtoId, float $quantidade): void
    // {
    //     DB::transaction(function () use ($itemId, $produtoId, $quantidade) {

    //         $item = ItemOrcamento::lockForUpdate()->findOrFail($itemId);

    //         $this->distribuir($item, $produtoId, $quantidade);

    //         $this->atualizarStatusOrcamento([$item->orcamento_id]);
    //     });
    // }

    public function recalcularReservar(int $itemId, int $produtoId, float $quantidade)
    {
        DB::transaction(function () use ($itemId, $produtoId, $quantidade) {

            $item = ItemOrcamento::lockForUpdate()->findOrFail($itemId);

            // 🔥 PASSO 1: limpa tudo
            $this->cancelarReserva($item);

            // 🔥 PASSO 2: redistribui do zero
            $this->distribuir($item, $produtoId, $quantidade);

            // 🔥 PASSO 3: recalcula
            $this->recalcularItem($item);

            // 🔥 PASSO 4: atende fila (UMA VEZ)
            $this->atenderPendentesPorProduto($produtoId);

            $this->atualizarStatusOrcamento([$item->orcamento_id]);
        });
    }

    /**
     * 🔥 RECALCULO (FONTE DA VERDADE)
     */
    private function recalcularItem(ItemOrcamento $item): void
    {
        $quantidadeAtendida = DB::table('item_orcamento_lotes')
            ->where('item_orcamento_id', $item->id)
            ->sum('quantidade_reservada');

        $item->quantidade_atendida = $quantidadeAtendida;
        $item->quantidade_pendente =
            $item->quantidade_solicitada - $quantidadeAtendida;

        $this->atualizarStatusItem($item);

        $item->save();
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
                'status' => $temPendente
                    ? 'Aguardando Estoque'
                    : 'Aguardando Aprovacao'
            ]);
        }
    }
}