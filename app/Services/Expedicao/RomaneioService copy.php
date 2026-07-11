<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\EntregaItem;
use App\Models\Romaneio;
use App\Models\RomaneioItem;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RomaneioService
{
    public function criarRomaneio(array $dados): Romaneio
    {
        return DB::transaction(function () use ($dados) {
            $entregasIds = $dados['entregas'] ?? [];
            $entregaItensIds = $dados['entrega_itens'] ?? [];

            if (empty($entregasIds) && empty($entregaItensIds)) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Selecione pelo menos uma entrega ou item de entrega para criar o romaneio.',
                ]);
            }

            $entregaItens = $this->buscarItensParaRomaneio($entregasIds, $entregaItensIds);

            if ($entregaItens->isEmpty()) {
                throw ValidationException::withMessages([
                    'itens' => 'Nenhum item disponível para criação do romaneio.',
                ]);
            }

            $entregaPrincipal = $entregaItens->first()->entrega;

            $this->validarEntregasDosItens($entregaItens);

            $romaneio = Romaneio::create([
                'entrega_id' => $entregaPrincipal->id,
                'codigo_romaneio' => $this->gerarCodigoRomaneio(),
                'data_emissao' => now(),
                'status' => 'Gerado',
                'motorista_id' => $dados['motorista_id'] ?? null,
                'veiculo_id' => $dados['veiculo_id'] ?? null,
                'observacao' => $dados['observacao'] ?? null,
                'criado_por' => Auth::id(),
            ]);

            foreach ($entregaItens as $entregaItem) {
                RomaneioItem::create([
                    'romaneio_id' => $romaneio->id,
                    'entrega_item_id' => $entregaItem->id,
                    'quantidade_prevista' => $entregaItem->quantidade_prevista ?? 0,
                    'quantidade_carregada' => 0,
                    'status' => 'Pendente',
                ]);
            }

            $entregasAfetadas = $entregaItens->pluck('entrega')->unique('id');

            foreach ($entregasAfetadas as $entrega) {
                $entrega->update([
                    'status' => 'Separando',
                ]);
            }

            return $romaneio->load([
                'motorista',
                'veiculo',
                'entrega.cliente',
                'entrega.orcamento',
                'itens.entregaItem.produto',
                'itens.entregaItem.entrega',
            ]);
        });
    }

   private function buscarItensParaRomaneio(array $entregasIds, array $entregaItensIds)
    {
        $query = EntregaItem::with([
            'entrega',
            'produto',
            'vendaItem.produto',
            'itemOrcamento.produto',
        ])->lockForUpdate();

        if (!empty($entregaItensIds)) {
            $query->whereIn('id', $entregaItensIds);
        } else {
            $query->whereIn('entrega_id', $entregasIds);
        }

        return $query
            ->whereNotIn('status', ['Entregue', 'Cancelado', 'Devolvido'])
            ->where(function ($q) {
                $q->where('quantidade_prevista', '>', 0)
                ->orWhere('quantidade_entregue', '>', 0);
            })
            ->get();
    }

    private function validarEntregasDosItens($entregaItens): void
    {
        foreach ($entregaItens as $item) {
            if (! $item->entrega) {
                throw ValidationException::withMessages([
                    'itens' => "O item #{$item->id} não possui entrega vinculada.",
                ]);
            }

            if (! in_array($item->entrega->status, ['Aguardando_separacao', 'Separando'], true)) {
                throw ValidationException::withMessages([
                    'entregas' => "A entrega #{$item->entrega->id} não está disponível para romaneio. Status atual: {$item->entrega->status}",
                ]);
            }

            if (in_array($item->status, ['Cancelado', 'Entregue', 'Devolvido'], true)) {
                throw ValidationException::withMessages([
                    'itens' => "O item #{$item->id} não está disponível para romaneio. Status atual: {$item->status}",
                ]);
            }

            // $jaExisteEmRomaneioAtivo = RomaneioItem::where('entrega_item_id', $item->id)
            //     ->whereHas('romaneio', function ($query) {
            //         $query->whereNotIn('status', ['Cancelado']);
            //     })
            //     ->exists();

            // if ($jaExisteEmRomaneioAtivo) {
            //     throw ValidationException::withMessages([
            //         'itens' => "O item #{$item->id} já está vinculado a um romaneio ativo.",
            //     ]);
            // }

            $quantidadeJaComprometida = RomaneioItem::query()
                ->where('entrega_item_id', $item->id)
                ->whereHas('romaneio', function ($query) {
                    $query->where('status', '!=', 'Cancelado');
                })
                ->sum('quantidade_prevista');

            $saldoDisponivel = round(
                (float) $item->quantidade_prevista - (float) $quantidadeJaComprometida,
                2
            );

            if ($saldoDisponivel <= 0) {
                throw ValidationException::withMessages([
                    'itens' => "O item #{$item->id} não possui saldo disponível para inclusão em outro romaneio.",
                ]);
            }

            $quantidadeSolicitada = $saldoDisponivel;

            RomaneioItem::create([
                'romaneio_id' => $romaneio->id,
                'entrega_item_id' => $item->id,
                'quantidade_prevista' => $quantidadeSolicitada,
                'quantidade_carregada' => 0,
                'status' => 'Pendente',
            ]);
        }
    }

    public function cancelar(Romaneio $romaneio, string $motivo): void
    {
        DB::transaction(function () use ($romaneio, $motivo) {
            if (in_array($romaneio->status, ['Carregado', 'Em Rota', 'Finalizado', 'Cancelado'], true)) {
                throw ValidationException::withMessages([
                    'romaneio' => 'Este romaneio não pode mais ser cancelado.',
                ]);
            }

            $romaneio->load('itens.entregaItem.entrega');

            $romaneio->update([
                'status' => 'Cancelado',
                'motivo_cancelamento' => $motivo,
                'cancelado_em' => now(),
                'cancelado_por' => Auth::id(),
            ]);

            foreach ($romaneio->itens as $item) {
                $item->update([
                    'status' => 'Cancelado',
                ]);
            }

            $entregasAfetadas = $romaneio->itens
                ->pluck('entregaItem.entrega')
                ->filter()
                ->unique('id');

            foreach ($entregasAfetadas as $entrega) {
                $entrega->update([
                    'status' => 'Aguardando_separacao',
                ]);
            }
        });
    }

    private function gerarCodigoRomaneio(): string
    {
        $ultimoId = (int) Romaneio::max('id') + 1;

        return 'ROM-' . now()->format('Ymd') . '-' . str_pad($ultimoId, 5, '0', STR_PAD_LEFT);
    }
}