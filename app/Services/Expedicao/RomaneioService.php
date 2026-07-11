<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\EntregaItem;
use App\Models\Romaneio;
use App\Models\RomaneioItem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RomaneioService
{
    public function criarRomaneio(array $dados): Romaneio
    {
        return DB::transaction(function () use ($dados) {
            $entregasIds = collect($dados['entregas'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $entregaItensIds = collect($dados['entrega_itens'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            /*
             * Compatibilidade com a nova tela de montagem, caso ela envie:
             *
             * entrega_id
             * itens[x][entrega_item_id]
             * itens[x][quantidade]
             */
            $itensComQuantidade = collect($dados['itens'] ?? [])
                ->filter(function ($item) {
                    return is_array($item)
                        && isset($item['entrega_item_id'])
                        && (int) $item['entrega_item_id'] > 0
                        && (float) ($item['quantidade'] ?? 0) > 0;
                })
                ->map(function ($item) {
                    return [
                        'entrega_item_id' => (int) $item['entrega_item_id'],
                        'quantidade' => round(
                            (float) ($item['quantidade'] ?? 0),
                            2
                        ),
                    ];
                })
                ->values();

            if (
                isset($dados['entrega_id']) &&
                (int) $dados['entrega_id'] > 0
            ) {
                $entregasIds->push((int) $dados['entrega_id']);
                $entregasIds = $entregasIds
                    ->unique()
                    ->values();
            }

            if ($itensComQuantidade->isNotEmpty()) {
                $entregaItensIds = $entregaItensIds
                    ->merge(
                        $itensComQuantidade->pluck(
                            'entrega_item_id'
                        )
                    )
                    ->unique()
                    ->values();
            }

            if (
                $entregasIds->isEmpty() &&
                $entregaItensIds->isEmpty()
            ) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Selecione pelo menos uma entrega ou item de entrega para criar o romaneio.',
                ]);
            }

            $entregaItens = $this->buscarItensParaRomaneio(
                $entregasIds->all(),
                $entregaItensIds->all()
            );

            if ($entregaItens->isEmpty()) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'Nenhum item disponível foi encontrado para criação do romaneio.',
                ]);
            }

            $this->validarEntregasDosItens(
                $entregaItens
            );

            $itensPreparados = $this->prepararItensDoRomaneio(
                $entregaItens,
                $itensComQuantidade
            );

            if ($itensPreparados->isEmpty()) {
                throw ValidationException::withMessages([
                    'itens' =>
                        'Os itens selecionados não possuem saldo disponível para criação do romaneio.',
                ]);
            }

            $entregaPrincipal = $itensPreparados
                ->first()['entrega_item']
                ->entrega;

            if (! $entregaPrincipal) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        'Não foi possível identificar a entrega dos itens selecionados.',
                ]);
            }

            $romaneio = Romaneio::create([
                'entrega_id' => $entregaPrincipal->id,
                'codigo_romaneio' =>
                    $this->gerarCodigoRomaneio(),
                'data_emissao' => now(),
                'status' => 'Gerado',
                'motorista_id' =>
                    $dados['motorista_id'] ?? null,
                'veiculo_id' =>
                    $dados['veiculo_id'] ?? null,
                'observacao' =>
                    $dados['observacao'] ?? null,
                'criado_por' => Auth::id(),
            ]);

            foreach ($itensPreparados as $itemPreparado) {
                RomaneioItem::create([
                    'romaneio_id' => $romaneio->id,
                    'entrega_item_id' =>
                        $itemPreparado['entrega_item']->id,
                    'quantidade_prevista' =>
                        $itemPreparado['quantidade'],
                    'quantidade_carregada' => 0,
                    'status' => 'Pendente',
                ]);
            }

            $entregasAfetadas = $itensPreparados
                ->pluck('entrega_item')
                ->pluck('entrega')
                ->filter()
                ->unique('id');

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
                'entrega.venda',
                'itens.entregaItem.entrega.cliente',
                'itens.entregaItem.produto',
                'itens.entregaItem.vendaItem.produto',
                'itens.entregaItem.itemOrcamento.produto',
            ]);
        });
    }

    private function buscarItensParaRomaneio(
        array $entregasIds,
        array $entregaItensIds
    ): EloquentCollection {
        $entregasIds = collect($entregasIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $entregaItensIds = collect($entregaItensIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $query = EntregaItem::query()
            ->with([
                'entrega',
                'produto',
                'vendaItem.produto',
                'itemOrcamento.produto',
            ])
            ->whereNotIn('status', [
                'Cancelado',
                'cancelado',
                'Entregue',
                'entregue',
                'Devolvido',
                'devolvido',
            ]);

        if (
            $entregasIds->isNotEmpty() &&
            $entregaItensIds->isNotEmpty()
        ) {
            $query->where(function ($query) use (
                $entregasIds,
                $entregaItensIds
            ) {
                $query
                    ->whereIn(
                        'entrega_id',
                        $entregasIds->all()
                    )
                    ->orWhereIn(
                        'id',
                        $entregaItensIds->all()
                    );
            });
        } elseif ($entregaItensIds->isNotEmpty()) {
            $query->whereIn(
                'id',
                $entregaItensIds->all()
            );
        } else {
            $query->whereIn(
                'entrega_id',
                $entregasIds->all()
            );
        }

        return $query
            ->lockForUpdate()
            ->get();
    }

    private function validarEntregasDosItens(
        Collection $entregaItens
    ): void {
        $entregas = $entregaItens
            ->pluck('entrega')
            ->filter()
            ->unique('id');

        if ($entregas->isEmpty()) {
            throw ValidationException::withMessages([
                'entrega' =>
                    'Os itens selecionados não possuem uma entrega válida.',
            ]);
        }

        foreach ($entregas as $entrega) {
            $statusNormalizado = strtolower(
                trim((string) $entrega->status)
            );

            if (! in_array($statusNormalizado, [
                'aguardando_separacao',
                'separando',
            ], true)) {
                throw ValidationException::withMessages([
                    'entrega' =>
                        "A entrega #{$entrega->id} não está disponível para romaneio. " .
                        "Status atual: {$entrega->status}.",
                ]);
            }
        }
    }

    private function prepararItensDoRomaneio(
        Collection $entregaItens,
        Collection $itensComQuantidade
    ): Collection {
        $quantidadesInformadas = $itensComQuantidade
            ->keyBy('entrega_item_id');

        $entregaItensIds = $entregaItens
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $quantidadesJaAlocadas = RomaneioItem::query()
            ->whereIn(
                'entrega_item_id',
                $entregaItensIds->all()
            )
            ->whereHas(
                'romaneio',
                function ($query) {
                    $query->where(
                        'status',
                        '!=',
                        'Cancelado'
                    );
                }
            )
            ->selectRaw(
                'entrega_item_id, SUM(quantidade_prevista) AS total_alocado'
            )
            ->groupBy('entrega_item_id')
            ->lockForUpdate()
            ->pluck(
                'total_alocado',
                'entrega_item_id'
            );

        return $entregaItens
            ->map(function (
                EntregaItem $entregaItem
            ) use (
                $quantidadesInformadas,
                $quantidadesJaAlocadas
            ) {
                $quantidadePrevista = round(
                    (float) (
                        $entregaItem->quantidade_prevista
                        ?? 0
                    ),
                    2
                );

                $quantidadeJaAlocada = round(
                    (float) (
                        $quantidadesJaAlocadas[
                            $entregaItem->id
                        ] ?? 0
                    ),
                    2
                );

                $saldoDisponivel = round(
                    max(
                        0,
                        $quantidadePrevista -
                        $quantidadeJaAlocada
                    ),
                    2
                );

                if ($saldoDisponivel <= 0) {
                    return null;
                }

                $quantidadeInformada =
                    $quantidadesInformadas->get(
                        $entregaItem->id
                    );

                /*
                 * Na tela antiga, que envia apenas IDs,
                 * será utilizado todo o saldo disponível.
                 *
                 * Na nova tela de montagem, será utilizada
                 * a quantidade digitada pelo operador.
                 */
                $quantidadeRomaneio =
                    $quantidadeInformada
                        ? round(
                            (float) $quantidadeInformada[
                                'quantidade'
                            ],
                            2
                        )
                        : $saldoDisponivel;

                if ($quantidadeRomaneio <= 0) {
                    return null;
                }

                if (
                    $quantidadeRomaneio >
                    $saldoDisponivel
                ) {
                    throw ValidationException::withMessages([
                        'itens' =>
                            "A quantidade informada para o item #{$entregaItem->id} " .
                            'excede o saldo disponível de ' .
                            number_format(
                                $saldoDisponivel,
                                2,
                                ',',
                                '.'
                            ) .
                            '.',
                    ]);
                }

                return [
                    'entrega_item' => $entregaItem,
                    'quantidade' =>
                        $quantidadeRomaneio,
                    'quantidade_prevista' =>
                        $quantidadePrevista,
                    'quantidade_ja_alocada' =>
                        $quantidadeJaAlocada,
                    'saldo_disponivel' =>
                        $saldoDisponivel,
                ];
            })
            ->filter()
            ->values();
    }

    public function cancelar(
        Romaneio $romaneio,
        string $motivo
    ): void {
        DB::transaction(function () use (
            $romaneio,
            $motivo
        ) {
            if (in_array($romaneio->status, [
                'Carregado',
                'Em Rota',
                'Finalizado',
                'Cancelado',
            ], true)) {
                throw ValidationException::withMessages([
                    'romaneio' =>
                        'Este romaneio não pode mais ser cancelado.',
                ]);
            }

            $romaneio->load([
                'itens.entregaItem.entrega',
            ]);

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

            $entregasAfetadas = $romaneio
                ->itens
                ->pluck('entregaItem.entrega')
                ->filter()
                ->unique('id');

            foreach ($entregasAfetadas as $entrega) {
                $possuiOutroRomaneioAtivo =
                    RomaneioItem::query()
                        ->whereHas(
                            'romaneio',
                            function ($query) use (
                                $romaneio
                            ) {
                                $query
                                    ->where(
                                        'id',
                                        '!=',
                                        $romaneio->id
                                    )
                                    ->where(
                                        'status',
                                        '!=',
                                        'Cancelado'
                                    );
                            }
                        )
                        ->whereHas(
                            'entregaItem',
                            function ($query) use (
                                $entrega
                            ) {
                                $query->where(
                                    'entrega_id',
                                    $entrega->id
                                );
                            }
                        )
                        ->exists();

                if (! $possuiOutroRomaneioAtivo) {
                    $entrega->update([
                        'status' =>
                            'Aguardando_separacao',
                    ]);
                }
            }
        });
    }

    private function gerarCodigoRomaneio(): string
    {
        $ultimoId = (int) Romaneio::max('id') + 1;

        return 'ROM-' .
            now()->format('Ymd') .
            '-' .
            str_pad(
                $ultimoId,
                5,
                '0',
                STR_PAD_LEFT
            );
    }
}