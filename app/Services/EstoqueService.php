<?php
namespace App\Services;
use App\Models\Lote;
use App\Models\ItemOrcamento;
use App\Models\Orcamento;
use App\Services\MovimentacaoOrcamentoService;
use App\Enums\TipoMovimentacao;
use App\Enums\OrigemMovimentacao;
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
    //     // $this->limparDistribuicao($item);

    //     $restante = $quantidade;

    //     $lotes = $this->buscarLotesDisponiveis($produtoId);

    //     foreach ($lotes as $lote) {

    //         if ($restante <= 0) break;

    //         $disponivel = $this->disponivel($lote);

    //         if ($disponivel <= 0) continue;

    //         $qtd = min($restante, $disponivel);

    //         $this->reservarLote($lote, $qtd);

    //         $this->registrarLote($item, $lote, $qtd);

    //         $restante -= $qtd;
    //     }

    //     $this->recalcularItem($item);
    // }

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

    //         $movService = app(MovimentacaoOrcamentoService::class);

    //         $vinculos = DB::table('item_orcamento_lotes')
    //             ->where('item_orcamento_id', $item->id)
    //             ->lockForUpdate()
    //             ->get();

    //         foreach ($vinculos as $v) {

    //             $lote = DB::table('lotes')
    //                 ->where('id', $v->lote_id)
    //                 ->lockForUpdate()
    //                 ->first();

    //             $antes = $lote->quantidade_reservada;
    //             $depois = $antes - $v->quantidade_reservada;

    //             DB::table('lotes')
    //                 ->where('id', $v->lote_id)
    //                 ->decrement('quantidade_reservada', $v->quantidade_reservada);

    //             // 🔥 REGISTRA MOVIMENTAÇÃO
    //             $movService->registrar(
    //                 $v->lote_id,
    //                 $item->orcamento_id,
    //                 $item->id,
    //                 TipoMovimentacao::CANCELAMENTO,
    //                 $antes,
    //                 $depois,
    //                 'Liberação de reserva',
    //                 OrigemMovimentacao::SISTEMA
    //             );
    //         }

    //         DB::table('item_orcamento_lotes')
    //             ->where('item_orcamento_id', $item->id)
    //             ->delete();

    //         $this->recalcularItem($item);
    //     });
    // }

    public function cancelarReserva(ItemOrcamento $item): void
    {
        DB::transaction(function () use ($item) {

            $movService = app(MovimentacaoOrcamentoService::class);

            $vinculos = DB::table('item_orcamento_lotes')
                ->where('item_orcamento_id', $item->id)
                ->lockForUpdate()
                ->get();

            foreach ($vinculos as $v) {

                $lote = DB::table('lotes')
                    ->where('id', $v->lote_id)
                    ->lockForUpdate()
                    ->first();

                if (!$lote) continue;

                $antes = $lote->quantidade_reservada;

                // 🔹 atualiza estoque
                DB::table('lotes')
                    ->where('id', $v->lote_id)
                    ->decrement('quantidade_reservada', $v->quantidade_reservada);

                // 🔹 pega valor atualizado
                $loteAtualizado = DB::table('lotes')
                    ->where('id', $v->lote_id)
                    ->first();

                $depois = max(0, $loteAtualizado->quantidade_reservada);

                // 🔥 REGISTRA MOVIMENTAÇÃO
                $movService->registrar(
                    $v->lote_id,
                    $item->orcamento_id,
                    $item->id,
                    TipoMovimentacao::CANCELAMENTO,
                    $antes,
                    $depois,
                    'Liberação de reserva',
                    OrigemMovimentacao::SISTEMA
                );
            }

            DB::table('item_orcamento_lotes')
                ->where('item_orcamento_id', $item->id)
                ->delete();

            $this->recalcularItem($item);
        });
    }

    /**
     * RESERVA DIRETA
     */

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

            $orcamento = Orcamento::find($id);

            // 🚨 REGRA DE OURO: nunca mexer em cancelados
            if (!$orcamento || $orcamento->status === 'Cancelado') {
                continue;
            }

            $temPendente = ItemOrcamento::where('orcamento_id', $id)
                ->where('quantidade_pendente', '>', 0)
                ->exists();

            $orcamento->update([
                'status' => $temPendente
                    ? 'Aguardando Estoque'
                    : 'Aguardando Aprovacao'
            ]);
        }
    }



    //  public function reprocessarProduto(int $produtoId)
    // {
    //     DB::transaction(function () use ($produtoId) {

    //         // 1. pega todos itens pendentes do produto
    //         $itens = ItemOrcamento::where('produto_id', $produtoId)
    //             ->whereRaw('(quantidade_solicitada - quantidade_atendida) > 0')
    //             ->orderBy('orcamento_id')
    //             ->lockForUpdate()
    //             ->get();

    //         foreach ($itens as $item) {

    //             // 2. tenta redistribuir com FIFO
    //             $this->distribuir(
    //                 $item,
    //                 $produtoId,
    //                 $item->quantidade_solicitada - $item->quantidade_atendida
    //             );

    //             // 3. recalcula fonte da verdade
    //             $this->recalcularItem($item);
    //         }

    //         // 4. atualiza status dos orçamentos afetados
    //         $this->atualizarStatusOrcamento(
    //             $itens->pluck('orcamento_id')->unique()->toArray()
    //         );
    //     });
    // }
}