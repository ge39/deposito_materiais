<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\Romaneio;
use App\Models\Veiculo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EntregaInteligenteConsolidacaoService
{
    private const RAIO_MAXIMO_KM = 5.0;

    private const STATUS_ENTREGA_EDITAVEIS = [
        'Aguardando_separacao',
        'Em_preparacao',
        'Pronta_para_carregamento',
    ];

    private const STATUS_ROMANEIO_EDITAVEIS = [
        'Gerado',
        'Em_separacao',
        'Separado',
        'Na_doca',
    ];

    public function decidir(array $dados, int $usuarioId): array
    {
        return DB::transaction(function () use (
            $dados,
            $usuarioId
        ): array {
            $origem = $this->carregarEntrega(
                (int) $dados['entrega_origem_id']
            );
            $destino = $this->carregarEntrega(
                (int) $dados['entrega_destino_id']
            );

            if ($origem->is($destino)) {
                throw ValidationException::withMessages([
                    'entrega_destino_id' =>
                        'Selecione duas entregas diferentes.',
                ]);
            }

            $distanciaKm = $this->validarProximidadeEPeriodo(
                $origem,
                $destino
            );
            $decisao = (string) $dados['decisao'];

            if ($decisao === 'manter_separado') {
                $this->registrarHistorico(
                    $origem,
                    $destino,
                    $decisao,
                    $distanciaKm,
                    $usuarioId,
                    null,
                    null,
                    'Operador optou por manter operações separadas.'
                );

                return [
                    'message' =>
                        'Decisão registrada. As operações permanecerão separadas.',
                ];
            }

            if (! ($dados['disponibilidade_confirmada'] ?? false)) {
                throw ValidationException::withMessages([
                    'disponibilidade_confirmada' =>
                        'Confirme a disponibilidade do veículo e do motorista entre os períodos.',
                ]);
            }

            $this->validarOperacaoEditavel($origem);
            $this->validarOperacaoEditavel($destino);

            $romaneioOrigem = $this->romaneioAtivo($origem);
            $romaneioDestino = $this->romaneioAtivo($destino);

            $veiculoId = $romaneioDestino?->veiculo_executante_id
                ?? $romaneioDestino?->veiculo_id
                ?? $destino->veiculo_id;
            $motoristaId = $romaneioDestino?->motorista_executante_id
                ?? $romaneioDestino?->motorista_id
                ?? $destino->motorista_id;

            if (! $veiculoId || ! $motoristaId) {
                throw ValidationException::withMessages([
                    'operacao_destino' =>
                        'A operação escolhida ainda não possui veículo e motorista definidos.',
                ]);
            }

            $veiculo = Veiculo::query()->find($veiculoId);

            if (! $veiculo) {
                throw ValidationException::withMessages([
                    'veiculo' => 'O veículo selecionado não foi localizado.',
                ]);
            }

            $this->validarCompatibilidade(
                $origem,
                $veiculo
            );
            $this->validarCapacidade(
                $origem,
                $destino,
                $veiculo
            );

            $veiculoAnteriorId = $origem->veiculo_id;
            $motoristaAnteriorId = $origem->motorista_id;

            $origem->update([
                'veiculo_id' => $veiculoId,
                'motorista_id' => $motoristaId,
                'ordem_rota' => null,
            ]);

            if ($romaneioOrigem) {
                $romaneioOrigem->update([
                    'veiculo_id' => $veiculoId,
                    'motorista_id' => $motoristaId,
                    'veiculo_executante_id' => $veiculoId,
                    'motorista_executante_id' => $motoristaId,
                    'ordem_execucao' => null,
                ]);
            }

            $this->registrarHistorico(
                $origem,
                $destino,
                $decisao,
                $distanciaKm,
                $usuarioId,
                (int) $veiculoId,
                (int) $motoristaId,
                sprintf(
                    'Equipe anterior: veículo %s, motorista %s.',
                    $veiculoAnteriorId ?: 'não definido',
                    $motoristaAnteriorId ?: 'não definido'
                )
            );

            return [
                'message' =>
                    'Entrega adicionada à mesma operação. A ordem inteligente será recalculada.',
            ];
        }, 3);
    }

    public function paresIgnorados(): array
    {
        if (! Schema::hasTable('entrega_inteligente_decisoes')) {
            return [];
        }

        return DB::table('entrega_inteligente_decisoes')
            ->where('decisao', 'manter_separado')
            ->orderByDesc('id')
            ->get([
                'entrega_origem_id',
                'entrega_destino_id',
            ])
            ->map(function ($decisao): string {
                $ids = [
                    (int) $decisao->entrega_origem_id,
                    (int) $decisao->entrega_destino_id,
                ];
                sort($ids);

                return implode(':', $ids);
            })
            ->unique()
            ->values()
            ->all();
    }

    private function carregarEntrega(int $entregaId): Entrega
    {
        return Entrega::query()
            ->with([
                'itens.vendaItem.produto.categoria',
                'itens.itemOrcamento.produto.categoria',
            ])
            ->lockForUpdate()
            ->findOrFail($entregaId);
    }

    private function romaneioAtivo(Entrega $entrega): ?Romaneio
    {
        return Romaneio::query()
            ->where('entrega_id', $entrega->id)
            ->where('status', '!=', 'Cancelado')
            ->lockForUpdate()
            ->latest('id')
            ->first();
    }

    private function validarProximidadeEPeriodo(
        Entrega $origem,
        Entrega $destino
    ): float {
        $dataOrigem = CarbonImmutable::parse(
            $origem->data_prevista_entrega
                ?? $origem->data_prevista
        )->toDateString();
        $dataDestino = CarbonImmutable::parse(
            $destino->data_prevista_entrega
                ?? $destino->data_prevista
        )->toDateString();

        if ($dataOrigem !== $dataDestino) {
            throw ValidationException::withMessages([
                'data' =>
                    'A consolidação assistida exige entregas na mesma data.',
            ]);
        }

        if (
            trim((string) $origem->periodo_entrega) === ''
            || trim((string) $destino->periodo_entrega) === ''
            || $origem->periodo_entrega === $destino->periodo_entrega
        ) {
            throw ValidationException::withMessages([
                'periodo' =>
                    'As entregas devem possuir períodos diferentes e definidos.',
            ]);
        }

        if (
            $origem->latitude_entrega === null
            || $origem->longitude_entrega === null
            || $destino->latitude_entrega === null
            || $destino->longitude_entrega === null
        ) {
            throw ValidationException::withMessages([
                'coordenadas' =>
                    'As duas entregas precisam ter coordenadas confirmadas.',
            ]);
        }

        $coordenadas = [
            (float) $origem->latitude_entrega,
            (float) $origem->longitude_entrega,
            (float) $destino->latitude_entrega,
            (float) $destino->longitude_entrega,
        ];

        if (
            ! $origem->coordenada_confirmada
            || ! $destino->coordenada_confirmada
            || collect($coordenadas)->contains(
                fn (float $valor): bool => ! is_finite($valor)
            )
        ) {
            throw ValidationException::withMessages([
                'coordenadas' =>
                    'As duas entregas precisam ter coordenadas confirmadas.',
            ]);
        }

        $distanciaKm = $this->distanciaKm(...$coordenadas);

        if ($distanciaKm > self::RAIO_MAXIMO_KM) {
            throw ValidationException::withMessages([
                'distancia' => sprintf(
                    'As entregas estão a %.1f km. O limite para esta sugestão é %.1f km.',
                    $distanciaKm,
                    self::RAIO_MAXIMO_KM
                ),
            ]);
        }

        return $distanciaKm;
    }

    private function validarOperacaoEditavel(Entrega $entrega): void
    {
        if (! in_array(
            $entrega->status,
            self::STATUS_ENTREGA_EDITAVEIS,
            true
        )) {
            throw ValidationException::withMessages([
                'status' =>
                    'A entrega ' . ($entrega->codigo_entrega ?: $entrega->id)
                    . ' já foi carregada, liberada ou iniciada e não pode ser remanejada.',
            ]);
        }

        $romaneio = $this->romaneioAtivo($entrega);

        if (! $romaneio) {
            return;
        }

        if (
            $romaneio->data_saida !== null
            || ! in_array(
                $romaneio->status,
                self::STATUS_ROMANEIO_EDITAVEIS,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'romaneio' =>
                    'O romaneio ' . $romaneio->codigo_romaneio
                    . ' já entrou em carregamento ou execução.',
            ]);
        }
    }

    private function validarCompatibilidade(
        Entrega $origem,
        Veiculo $veiculo
    ): void {
        $regras = [
            'aceita_areia_pedra' => ['areia', 'pedra', 'brita'],
            'aceita_blocos_tijolos' => ['bloco', 'tijolo'],
            'aceita_cimento_argamassa' => ['cimento', 'argamassa'],
            'aceita_tintas_quimicos' => ['tinta', 'quimico', 'solvente'],
            'aceita_telhas' => ['telha'],
            'aceita_madeiras' => ['madeira', 'madeiramento'],
        ];

        foreach ($origem->itens as $item) {
            $produto = $item->produto
                ?? $item->vendaItem?->produto
                ?? $item->itemOrcamento?->produto;

            if (! $produto) {
                continue;
            }

            $descricao = Str::lower(
                Str::ascii(
                    trim(
                        (string) ($produto->nome ?? '')
                        . ' '
                        . (string) ($produto->categoria?->nome ?? '')
                    )
                )
            );

            foreach ($regras as $campo => $palavras) {
                foreach ($palavras as $palavra) {
                    if (
                        str_contains($descricao, $palavra)
                        && ! (bool) ($veiculo->{$campo} ?? false)
                    ) {
                        throw ValidationException::withMessages([
                            'compatibilidade' =>
                                'O veículo não é compatível com '
                                . ($produto->nome ?? 'um dos produtos')
                                . '.',
                        ]);
                    }
                }
            }
        }
    }

    private function validarCapacidade(
        Entrega $origem,
        Entrega $destino,
        Veiculo $veiculo
    ): void {
        $data = CarbonImmutable::parse(
            $destino->data_prevista_entrega
                ?? $destino->data_prevista
        )->toDateString();

        $entregaIds = Romaneio::query()
            ->where('status', '!=', 'Cancelado')
            ->where(function ($query) use ($veiculo): void {
                $query
                    ->where('veiculo_executante_id', $veiculo->id)
                    ->orWhere(function ($query) use ($veiculo): void {
                        $query
                            ->whereNull('veiculo_executante_id')
                            ->where('veiculo_id', $veiculo->id);
                    });
            })
            ->pluck('entrega_id')
            ->push($destino->id)
            ->push($origem->id)
            ->filter()
            ->unique();

        $entregasSemRomaneio = Entrega::query()
            ->where('veiculo_id', $veiculo->id)
            ->whereRaw(
                'COALESCE(data_prevista_entrega, data_prevista) = ?',
                [$data]
            )
            ->pluck('id');

        $entregaIds = $entregaIds
            ->merge($entregasSemRomaneio)
            ->unique();

        $entregas = Entrega::query()
            ->with([
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
            ])
            ->whereIn('id', $entregaIds)
            ->whereRaw(
                'COALESCE(data_prevista_entrega, data_prevista) = ?',
                [$data]
            )
            ->get();

        $uso = $entregas->reduce(
            function (array $total, Entrega $entrega): array {
                $medidas = $this->medidasEntrega($entrega);

                foreach ($total as $campo => $valor) {
                    $total[$campo] = $valor + $medidas[$campo];
                }

                return $total;
            },
            [
                'kg' => 0.0,
                'm3' => 0.0,
                'unidades' => 0.0,
            ]
        );

        $limites = [
            'kg' => (float) ($veiculo->capacidade_kg ?? 0),
            'm3' => (float) ($veiculo->capacidade_m3 ?? 0),
            'unidades' => (float) (
                $veiculo->capacidade_unidades ?? 0
            ),
        ];

        foreach ($limites as $campo => $limite) {
            if ($limite > 0 && $uso[$campo] > $limite) {
                throw ValidationException::withMessages([
                    'capacidade' => sprintf(
                        'A carga consolidada excede a capacidade de %s do veículo (%.2f de %.2f).',
                        $campo,
                        $uso[$campo],
                        $limite
                    ),
                ]);
            }
        }
    }

    private function medidasEntrega(Entrega $entrega): array
    {
        $medidas = [
            'kg' => 0.0,
            'm3' => 0.0,
            'unidades' => 0.0,
        ];

        foreach ($entrega->itens as $item) {
            $quantidade = (float) ($item->quantidade_prevista ?? 0);
            $produto = $item->produto
                ?? $item->vendaItem?->produto
                ?? $item->itemOrcamento?->produto;

            $medidas['unidades'] += $quantidade;
            $medidas['kg'] += $quantidade
                * (float) ($produto?->peso ?? 0);
            $medidas['m3'] += $quantidade
                * $this->volumeProdutoM3($produto);
        }

        return $medidas;
    }

    private function volumeProdutoM3($produto): float
    {
        if (! $produto) {
            return 0.0;
        }

        $largura = (float) ($produto->largura ?? 0);
        $altura = (float) ($produto->altura ?? 0);
        $profundidade = (float) ($produto->profundidade ?? 0);

        if ($largura <= 0 || $altura <= 0 || $profundidade <= 0) {
            return 0.0;
        }

        $divisor = max($largura, $altura, $profundidade) > 10
            ? 1000000
            : 1;

        return ($largura * $altura * $profundidade) / $divisor;
    }

    private function registrarHistorico(
        Entrega $origem,
        Entrega $destino,
        string $decisao,
        float $distanciaKm,
        int $usuarioId,
        ?int $veiculoId,
        ?int $motoristaId,
        ?string $observacao
    ): void {
        DB::table('entrega_inteligente_decisoes')->insert([
            'entrega_origem_id' => $origem->id,
            'entrega_destino_id' => $destino->id,
            'decisao' => $decisao,
            'distancia_km' => round($distanciaKm, 3),
            'veiculo_destino_id' => $veiculoId,
            'motorista_destino_id' => $motoristaId,
            'decidido_por' => $usuarioId,
            'observacao' => $observacao,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function distanciaKm(
        float $latitudeOrigem,
        float $longitudeOrigem,
        float $latitudeDestino,
        float $longitudeDestino
    ): float {
        $raioTerra = 6371;
        $deltaLatitude = deg2rad(
            $latitudeDestino - $latitudeOrigem
        );
        $deltaLongitude = deg2rad(
            $longitudeDestino - $longitudeOrigem
        );
        $a = sin($deltaLatitude / 2) ** 2
            + cos(deg2rad($latitudeOrigem))
            * cos(deg2rad($latitudeDestino))
            * sin($deltaLongitude / 2) ** 2;

        return $raioTerra * 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );
    }
}