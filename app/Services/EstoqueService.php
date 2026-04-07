<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\ItemOrcamento;
use App\Models\Orcamento;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    /**
     * 🔹 Reservar estoque para um novo orçamento
     * Retorna os lotes atendidos e quantidade pendente
     */
    public function reservar(int $produtoId, float $quantidade)
    {
        $resultado = [
            'itens' => [],
            'pendente' => 0
        ];

        $restante = $quantidade;

        $lotes = Lote::where('produto_id', $produtoId)
            ->where('status', 1)
            ->whereRaw('quantidade_disponivel - quantidade_reservada > 0')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($restante <= 0) break;

            $disponivel = $lote->disponivel_real;
            if ($disponivel <= 0) continue;

            $qtd = min($restante, $disponivel);

            $lote->quantidade_reservada += $qtd;
            $lote->save();

            $resultado['itens'][] = [
                'lote_id'   => $lote->id,
                'quantidade'=> $qtd,
                'atendida'  => $qtd,
                'pendente'  => 0
            ];

            $restante -= $qtd;
        }

        if ($restante > 0) {
            // Pendência quando não há estoque suficiente
            $resultado['itens'][] = [
                'lote_id'   => null,
                'quantidade'=> $restante,
                'atendida'  => 0,
                'pendente'  => $restante
            ];
            $resultado['pendente'] = $restante;
        }

        return $resultado;
    }

    /**
     * 🔹 Atender itens pendentes quando um novo lote chega
     */
    public function atenderPendentes(int $produtoId)
    {
        DB::transaction(function () use ($produtoId) {

            // Itens pendentes ou parciais do produto
            $itensPendentes = ItemOrcamento::where('produto_id', $produtoId)
                ->whereIn('status', ['indisponivel', 'parcial'])
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            if ($itensPendentes->isEmpty()) {
                return;
            }

            // Lotes válidos com estoque disponível
            $lotes = Lote::where('produto_id', $produtoId)
                ->where('status', 1)
                ->whereRaw('quantidade_disponivel - quantidade_reservada > 0')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            foreach ($itensPendentes as $item) {
                $restante = $item->quantidade_pendente ?? $item->quantidade;

                foreach ($lotes as $lote) {
                    if ($restante <= 0) break;

                    $disponivel = $lote->disponivel_real;
                    if ($disponivel <= 0) continue;

                    $qtd = min($restante, $disponivel);

                    // Atualiza lote
                    $lote->quantidade_reservada += $qtd;
                    $lote->save();

                    // Atualiza item
                    $item->quantidade_atendida = ($item->quantidade_atendida ?? 0) + $qtd;
                    $item->quantidade_pendente  = max(($item->quantidade - $item->quantidade_atendida), 0);
                    $item->quantidade           = ($item->quantidade ?? 0);

                    // Define previsão de entrega se ainda não existir
                    if (!$item->previsao_entrega) {
                        $item->previsao_entrega = now();
                    }

                    $restante -= $qtd;
                }

                // Atualiza status do item
                if ($item->quantidade_pendente > 0) {
                    $item->status = 'parcial';
                } else {
                    $item->status = 'disponivel';
                }

                $item->save();

                // Atualiza status do orçamento
                $this->atualizarStatusOrcamento($item->orcamento_id);
            }
        });
    }

    /**
     * 🔹 Atualiza status do orçamento com base nos itens
     */
    private function atualizarStatusOrcamento(int $orcamentoId)
    {
        $orcamento = Orcamento::with('itens')->find($orcamentoId);
        if (!$orcamento) return;

        $temPendentes = $orcamento->itens
            ->whereIn('status', ['indisponivel', 'parcial'])
            ->count() > 0;

        $orcamento->status = $temPendentes
            ? 'Aguardando Estoque'
            : 'Aprovado';

        $orcamento->save();
    }
}