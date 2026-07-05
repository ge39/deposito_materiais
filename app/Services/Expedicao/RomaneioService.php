<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
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

            if (empty($entregasIds)) {
                throw ValidationException::withMessages([
                    'entregas' => 'Selecione pelo menos uma entrega para criar o romaneio.',
                ]);
            }

            $entregas = Entrega::with(['itens'])
                ->whereIn('id', $entregasIds)
                ->lockForUpdate()
                ->get();

            if ($entregas->count() !== count($entregasIds)) {
                throw ValidationException::withMessages([
                    'entregas' => 'Uma ou mais entregas selecionadas não foram encontradas.',
                ]);
            }

            foreach ($entregas as $entrega) {
                if (! in_array($entrega->status, ['Aguardando_separacao'], true)) {
                    throw ValidationException::withMessages([
                        'entregas' => "A entrega #{$entrega->id} não está disponível para romaneio. Status atual: {$entrega->status}",
                    ]);
                }

                if ($entrega->itens->isEmpty()) {
                    throw ValidationException::withMessages([
                        'entregas' => "A entrega #{$entrega->id} não possui itens para expedição.",
                    ]);
                }
            }

           $romaneio = Romaneio::create([
                'entrega_id' => $entregas->first()->id,
                'codigo_romaneio' => $this->gerarCodigoRomaneio(),
                'data_emissao' => now(),
                'status' => 'Gerado',
                'motorista_id' => $dados['motorista_id'] ?? null,
                'veiculo_id' => $dados['veiculo_id'] ?? null,
                'observacao' => $dados['observacao'] ?? null,
            ]);

            foreach ($entregas as $entrega) {
                foreach ($entrega->itens as $entregaItem) {
                    RomaneioItem::create([
                        'romaneio_id' => $romaneio->id,
                        'entrega_item_id' => $entregaItem->id,
                        'quantidade_prevista' => $entregaItem->quantidade_prevista ?? 0,
                        'quantidade_carregada' => 0,
                        'status' => 'Pendente',
                    ]);
                }

                $entrega->update([
                    'status' => 'Separando',
                ]);
            }

            return $romaneio->load([
                'motorista',
                'veiculo',
                'itens.entregaItem.entrega',
            ]);
        });
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

                if ($item->entregaItem && $item->entregaItem->entrega) {
                    $item->entregaItem->entrega->update([
                        'status' => 'Faturado',
                    ]);
                }
            }
        });
    }

    private function gerarCodigoRomaneio(): string
    {
        $ultimoId = (int) Romaneio::max('id') + 1;

        return 'ROM-' . now()->format('Ymd') . '-' . str_pad($ultimoId, 5, '0', STR_PAD_LEFT);
    }
}