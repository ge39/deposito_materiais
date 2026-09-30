<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Veiculo;
use App\Services\Expedicao\EntregaInteligenteConsolidacaoService;
use App\Services\Expedicao\EntregaInteligenteService;
use App\Services\Traccar\TraccarClient;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EntregaInteligenteController extends Controller
{
    public function __construct(
        private readonly EntregaInteligenteService $entregaInteligenteService,
        private readonly EntregaInteligenteConsolidacaoService $consolidacaoService
    ) {
    }

    public function index(Request $request): View
    {
        $dados = $request->validate([
            'data_referencia' => [
                'nullable',
                'date_format:Y-m-d',
            ],
        ]);

        $dataReferencia = ! empty($dados['data_referencia'])
            ? CarbonImmutable::parse(
                $dados['data_referencia']
            )->startOfDay()
            : CarbonImmutable::today();

        $dashboard = $this
            ->entregaInteligenteService
            ->montarDashboard($dataReferencia);

        $dashboard['paresConsolidacaoIgnorados'] = $this
            ->consolidacaoService
            ->paresIgnorados();

        return view(
            'entregas_inteligentes.index',
            $dashboard
        );
    }

    public function posicoesVeiculos(): JsonResponse
    {
        try {
            $empresa = Empresa::ativa();

            if (! $empresa) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Empresa ativa não encontrada.',
                    'veiculos' => [],
                ], 404);
            }

            $rastreamento = $empresa->rastreamentoConfig;

            if (! $rastreamento || ! $rastreamento->enabled) {
                return response()->json([
                    'ok' => true,
                    'rastreamento_habilitado' => false,
                    'veiculos' => [],
                ]);
            }

            $serverUrl = trim(
                (string) $rastreamento->server_url
            );

            $email = trim(
                (string) $rastreamento->traccar_email
            );

            $password = (string) $rastreamento->traccar_password;

            if (
                $serverUrl === ''
                || $email === ''
                || $password === ''
            ) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Credenciais do Traccar não configuradas.',
                    'veiculos' => [],
                ], 503);
            }

            $client = new TraccarClient(
                $serverUrl,
                $email,
                $password
            );

            /*
             * A API retorna todos os dispositivos acessíveis.
             * O isolamento multiempresa é aplicado pelo groupId
             * configurado para a empresa ativa.
             */
            $devices = collect(
                $client->devices()
            );

            $groupId = $rastreamento->traccar_group_id !== null
                ? (int) $rastreamento->traccar_group_id
                : null;

            if ($groupId !== null) {
                $devices = $devices->filter(
                    fn (array $device): bool =>
                        (int) ($device['groupId'] ?? 0)
                        === $groupId
                );
            }

            /*
             * O vínculo ERP <-> Traccar é exclusivamente pelo
             * uniqueId visível no cadastro do dispositivo.
             */
            $devicesByUniqueId = $devices
                ->filter(
                    fn (array $device): bool =>
                        trim(
                            (string) (
                                $device['uniqueId']
                                ?? ''
                            )
                        ) !== ''
                )
                ->keyBy(
                    fn (array $device): string =>
                        'uid:' . trim(
                            (string) $device['uniqueId']
                        )
                );

            if ($devicesByUniqueId->isEmpty()) {
                return response()->json([
                    'ok' => true,
                    'rastreamento_habilitado' => true,
                    'empresa_id' => (int) $empresa->id,
                    'traccar_group_id' => $groupId,
                    'veiculos' => [],
                ]);
            }

            $uniqueIdsPermitidos = $devices
                ->map(
                    fn (array $device): string =>
                        trim(
                            (string) (
                                $device['uniqueId']
                                ?? ''
                            )
                        )
                )
                ->filter(
                    fn (string $uniqueId): bool =>
                        $uniqueId !== ''
                )
                ->values();

            /*
             * Somente veículos:
             * - da empresa ativa;
             * - ativos no ERP;
             * - com rastreador;
             * - com rastreamento ativo;
             * - com uniqueId válido;
             * - pertencentes ao grupo Traccar da empresa.
             */
            $veiculos = Veiculo::query()
                ->where(
                    'empresa_id',
                    $empresa->id
                )
                ->where('ativo', 1)
                ->where(
                    'possui_rastreador',
                    1
                )
                ->where(
                    'rastreamento_ativo',
                    1
                )
                ->whereNotNull(
                    'traccar_unique_id'
                )
                ->whereIn(
                    'traccar_unique_id',
                    $uniqueIdsPermitidos->all()
                )
                ->get();

            /*
             * position.deviceId usa o ID interno do Traccar.
             * Esse valor é usado somente em memória para relacionar
             * a posição retornada pela API ao dispositivo encontrado
             * pelo uniqueId. Não é campo cadastral do ERP.
             */
            $deviceInternalIds = $devices
                ->map(
                    fn (array $device): int =>
                        (int) ($device['id'] ?? 0)
                )
                ->filter(
                    fn (int $id): bool =>
                        $id > 0
                )
                ->values();

            $positions = collect(
                $client->positions()
            )
                ->filter(
                    fn (array $position): bool =>
                        $deviceInternalIds->contains(
                            (int) (
                                $position['deviceId']
                                ?? 0
                            )
                        )
                )
                ->keyBy(
                    fn (array $position): int =>
                        (int) (
                            $position['deviceId']
                            ?? 0
                        )
                );

            $resultado = $veiculos
                ->map(
                    function (
                        Veiculo $veiculo
                    ) use (
                        $devicesByUniqueId,
                        $positions
                    ): array {
                        $uniqueId = trim(
                            (string) (
                                $veiculo
                                    ->traccar_unique_id
                            )
                        );

                        $device = $devicesByUniqueId
                            ->get(
                                'uid:' . $uniqueId,
                                []
                            );

                        /*
                         * ID interno necessário somente para
                         * localizar position.deviceId na resposta
                         * da API do Traccar.
                         */
                        $internalId = (int) (
                            $device['id']
                            ?? 0
                        );

                        $position = $internalId > 0
                            ? $positions->get(
                                $internalId
                            )
                            : null;

                        $speedKnots = $position !== null
                            ? (float) (
                                $position['speed']
                                ?? 0
                            )
                            : null;

                        return [
                            'veiculo_id' =>
                                (int) $veiculo->id,

                            'placa' =>
                                $veiculo->placa,

                            'modelo' =>
                                $veiculo->modelo,

                            'traccar_unique_id' =>
                                $uniqueId,

                            'device_name' =>
                                $device['name']
                                ?? null,

                            'status' =>
                                $device['status']
                                ?? 'unknown',

                            'last_update' =>
                                $device['lastUpdate']
                                ?? null,

                            'posicao_disponivel' =>
                                $position !== null,

                            'latitude' =>
                                $position !== null
                                    ? (float) (
                                        $position[
                                            'latitude'
                                        ]
                                        ?? 0
                                    )
                                    : null,

                            'longitude' =>
                                $position !== null
                                    ? (float) (
                                        $position[
                                            'longitude'
                                        ]
                                        ?? 0
                                    )
                                    : null,

                            'valid' =>
                                $position !== null
                                    ? (bool) (
                                        $position[
                                            'valid'
                                        ]
                                        ?? false
                                    )
                                    : false,

                            'fix_time' =>
                                $position[
                                    'fixTime'
                                ]
                                ?? null,

                            'speed_kmh' =>
                                $speedKnots !== null
                                    ? round(
                                        $speedKnots
                                        * 1.852,
                                        1
                                    )
                                    : null,

                            'course' =>
                                $position !== null
                                    ? (float) (
                                        $position[
                                            'course'
                                        ]
                                        ?? 0
                                    )
                                    : null,

                            'accuracy' =>
                                $position !== null
                                    ? (float) (
                                        $position[
                                            'accuracy'
                                        ]
                                        ?? 0
                                    )
                                    : null,

                            'motion' =>
                                $position !== null
                                    ? (bool) (
                                        $position[
                                            'attributes'
                                        ][
                                            'motion'
                                        ]
                                        ?? false
                                    )
                                    : false,

                            'battery_level' =>
                                $position !== null
                                && isset(
                                    $position[
                                        'attributes'
                                    ][
                                        'batteryLevel'
                                    ]
                                )
                                    ? (float) (
                                        $position[
                                            'attributes'
                                        ][
                                            'batteryLevel'
                                        ]
                                    )
                                    : null,
                        ];
                    }
                )
                ->values();

            return response()->json([
                'ok' => true,
                'rastreamento_habilitado' => true,
                'empresa_id' =>
                    (int) $empresa->id,
                'traccar_group_id' =>
                    $groupId,
                'veiculos' =>
                    $resultado,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' =>
                    'Não foi possível consultar o rastreamento.',
                'veiculos' => [],
            ], 502);
        }
    }
}
