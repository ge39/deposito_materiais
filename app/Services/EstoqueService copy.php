<?php
    class EstoqueService
{
    public function atenderPendentes($produtoId)
    {
        DB::transaction(function () use ($produtoId) {

            $pendentes = ItemOrcamento::where('produto_id', $produtoId)
                ->where('quantidade_pendente', '>', 0)
                ->lockForUpdate()
                ->get();

            $lotes = Lote::where('produto_id', $produtoId)
                ->whereRaw('quantidade > quantidade_reservada')
                ->lockForUpdate()
                ->orderBy('created_at')
                ->get();

            $orcamentosAfetados = [];

            foreach ($pendentes as $item) {

                $faltante = $item->quantidade_pendente;

                foreach ($lotes as $lote) {

                    if ($faltante <= 0) break;

                    $disponivel = $lote->quantidade - $lote->quantidade_reservada;

                    if ($disponivel <= 0) continue;

                    $qtd = min($faltante, $disponivel);

                    $lote->quantidade_reservada += $qtd;
                    $lote->save();

                    $item->quantidade_atendida += $qtd;
                    $item->quantidade_pendente -= $qtd;

                    if ($item->quantidade_pendente == 0) {
                        $item->status = 'disponivel';
                    }

                    $item->save();

                    $faltante -= $qtd;
                }

                $orcamentosAfetados[] = $item->orcamento_id;
            }

            foreach (array_unique($orcamentosAfetados) as $orcamentoId) {
                $this->atualizarStatusOrcamento($orcamentoId);
            }
        });
    }

    private function atualizarStatusOrcamento($orcamentoId)
    {
        $orcamento = Orcamento::with('itens')->find($orcamentoId);

        $temPendente = $orcamento->itens
            ->where('quantidade_pendente', '>', 0)
            ->count() > 0;

        $orcamento->status = $temPendente
            ? 'Aguardando Estoque'
            : 'Aguardando Aprovacao';

        $orcamento->save();
    }
}