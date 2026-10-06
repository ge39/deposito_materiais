<?php

namespace App\Services\BI;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class BiLogisticaService
{
    private const STATUS_ENCERRADOS_PRAZO = [
        'Entregue',
        'Entregue_finalizada_com_ocorrencia',
        'Entregue_parcial',
        'Nao_entregue',
        'Recusada',
        'Devolvida',
        'Cancelada',
    ];

    public function dashboard(array $filtros): array
    {
        $hoje = CarbonImmutable::today();

        $inicio = ! empty($filtros['data_inicio'])
            ? CarbonImmutable::parse($filtros['data_inicio'])
            : $hoje->startOfMonth();

        $fim = ! empty($filtros['data_fim'])
            ? CarbonImmutable::parse($filtros['data_fim'])
            : $hoje;

        if ($fim->lt($inicio)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        $status = trim((string) ($filtros['status'] ?? ''));
        $periodo = trim((string) ($filtros['periodo'] ?? ''));

        $motoristaId = ! empty($filtros['motorista_id'])
            ? (int) $filtros['motorista_id']
            : null;

        $veiculoId = ! empty($filtros['veiculo_id'])
            ? (int) $filtros['veiculo_id']
            : null;

        /*
        |--------------------------------------------------------------------------
        | COORTE PRINCIPAL
        |--------------------------------------------------------------------------
        |
        | Todo o BI parte de entregas.data_prevista.
        | Romaneios, eventos e ocorrencias sao expandidos a partir desta coorte.
        |
        */

        $entregasBase = $this->baseEntregas(
            inicio: $inicio,
            fim: $fim,
            status: $status,
            periodo: $periodo,
            motoristaId: $motoristaId,
            veiculoId: $veiculoId
        );

        $romaneiosBase = $this->baseRomaneios(
            entregasBase: $entregasBase,
            motoristaId: $motoristaId,
            veiculoId: $veiculoId
        );

        $romaneioIds = (clone $romaneiosBase)
            ->select('r.id');

        $ocorrenciasBase = DB::table('romaneio_ocorrencias as ro')
            ->whereIn(
                'ro.romaneio_id',
                (clone $romaneioIds)
            );

        $eventosBase = DB::table('romaneio_eventos as re')
            ->whereIn(
                're.romaneio_id',
                (clone $romaneioIds)
            );

        /*
        |--------------------------------------------------------------------------
        | KPIs
        |--------------------------------------------------------------------------
        */

        $totalEntregas = (int) (clone $entregasBase)->count();

        $entregues = (int) (clone $entregasBase)
            ->where('e.status', 'Entregue')
            ->count();

        $entreguesComOcorrencia = (int) (clone $entregasBase)
            ->where(
                'e.status',
                'Entregue_finalizada_com_ocorrencia'
            )
            ->count();

        $entregasParciais = (int) (clone $entregasBase)
            ->where('e.status', 'Entregue_parcial')
            ->count();

        $naoEntregues = (int) (clone $entregasBase)
            ->where('e.status', 'Nao_entregue')
            ->count();

        $emRota = (int) (clone $entregasBase)
            ->where('e.status', 'Em_rota')
            ->count();

        $atrasadas = (int) (clone $entregasBase)
            ->whereDate(
                'e.data_prevista',
                '<',
                $hoje->toDateString()
            )
            ->whereNotIn(
                'e.status',
                self::STATUS_ENCERRADOS_PRAZO
            )
            ->count();

        $totalOcorrencias = (int) (clone $ocorrenciasBase)
            ->count();

        $totalRomaneios = (int) (clone $romaneiosBase)
            ->count();

        $saidasRegistradas = (int) (clone $eventosBase)
            ->where(
                're.evento',
                'Saída do veículo registrada'
            )
            ->count();

        $motoristasUtilizados = (int) (clone $romaneiosBase)
            ->whereNotNull('r.motorista_id')
            ->distinct()
            ->count('r.motorista_id');

        $veiculosUtilizados = (int) (clone $romaneiosBase)
            ->whereNotNull('r.veiculo_id')
            ->distinct()
            ->count('r.veiculo_id');

        /*
        |--------------------------------------------------------------------------
        | RESUMOS
        |--------------------------------------------------------------------------
        */

        $statusResumo = (clone $entregasBase)
            ->select(
                'e.status',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('e.status')
            ->orderByDesc('total')
            ->get();

        $periodosResumo = (clone $entregasBase)
            ->select(
                'e.periodo_entrega',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('e.periodo_entrega')
            ->orderByDesc('total')
            ->get();

        $ocorrenciasPorCategoria = (clone $ocorrenciasBase)
            ->select(
                'ro.categoria',
                DB::raw('COUNT(*) AS total')
            )
            ->groupBy('ro.categoria')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RANKING MOTORISTAS
        |--------------------------------------------------------------------------
        */

        $rankingMotoristas = (clone $romaneiosBase)
            ->join(
                'entregas as e',
                'e.id',
                '=',
                'r.entrega_id'
            )
            ->leftJoin(
                'funcionarios as f',
                'f.id',
                '=',
                'r.motorista_id'
            )
            ->whereNotNull('r.motorista_id')
            ->select(
                'r.motorista_id',
                'f.nome'
            )
            ->selectRaw(
                'COUNT(DISTINCT r.id) AS romaneios'
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Entregue' THEN r.id END) AS entregues"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Entregue_finalizada_com_ocorrencia' THEN r.id END) AS entregues_com_ocorrencia"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Entregue_parcial' THEN r.id END) AS parciais"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Nao_entregue' THEN r.id END) AS nao_entregues"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Em_rota' THEN r.id END) AS em_rota"
            )
            ->groupBy(
                'r.motorista_id',
                'f.nome'
            )
            ->orderByDesc('romaneios')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RANKING VEICULOS
        |--------------------------------------------------------------------------
        */

        $rankingVeiculos = (clone $romaneiosBase)
            ->join(
                'entregas as e',
                'e.id',
                '=',
                'r.entrega_id'
            )
            ->leftJoin(
                'veiculos as v',
                'v.id',
                '=',
                'r.veiculo_id'
            )
            ->whereNotNull('r.veiculo_id')
            ->select(
                'r.veiculo_id',
                'v.placa',
                'v.modelo'
            )
            ->selectRaw(
                'COUNT(DISTINCT r.id) AS romaneios'
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Entregue' THEN r.id END) AS entregues"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Entregue_finalizada_com_ocorrencia' THEN r.id END) AS entregues_com_ocorrencia"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Entregue_parcial' THEN r.id END) AS parciais"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Nao_entregue' THEN r.id END) AS nao_entregues"
            )
            ->selectRaw(
                "COUNT(DISTINCT CASE WHEN e.status = 'Em_rota' THEN r.id END) AS em_rota"
            )
            ->groupBy(
                'r.veiculo_id',
                'v.placa',
                'v.modelo'
            )
            ->orderByDesc('romaneios')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DETALHES DAS ENTREGAS
        |--------------------------------------------------------------------------
        */

        $entregasDetalhesBase = $this->baseDetalhesEntregas(
            entregasBase: $entregasBase,
            motoristaId: $motoristaId,
            veiculoId: $veiculoId
        );

        $entregasPrevistasDetalhes = (clone $entregasDetalhesBase)
            ->orderBy('e.data_prevista')
            ->orderBy('e.id')
            ->get();

        $entreguesDetalhes = (clone $entregasDetalhesBase)
            ->where('e.status', 'Entregue')
            ->orderByDesc('e.data_realizada')
            ->get();

        $entreguesComOcorrenciaDetalhes = (clone $entregasDetalhesBase)
            ->where(
                'e.status',
                'Entregue_finalizada_com_ocorrencia'
            )
            ->orderByDesc('e.data_realizada')
            ->get();

        $parciaisDetalhes = (clone $entregasDetalhesBase)
            ->where('e.status', 'Entregue_parcial')
            ->orderByDesc('e.data_prevista')
            ->get();

        $naoEntreguesDetalhes = (clone $entregasDetalhesBase)
            ->where('e.status', 'Nao_entregue')
            ->orderByDesc('e.data_prevista')
            ->get();

        $emRotaDetalhes = (clone $entregasDetalhesBase)
            ->where('e.status', 'Em_rota')
            ->orderBy('e.data_prevista')
            ->get();

        $atrasadasDetalhes = (clone $entregasDetalhesBase)
            ->whereDate(
                'e.data_prevista',
                '<',
                $hoje->toDateString()
            )
            ->whereNotIn(
                'e.status',
                self::STATUS_ENCERRADOS_PRAZO
            )
            ->selectRaw(
                'DATEDIFF(CURDATE(), e.data_prevista) AS dias_atraso'
            )
            ->orderByDesc('dias_atraso')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DETALHES ROMANEIOS
        |--------------------------------------------------------------------------
        */

        $romaneiosDetalhes = (clone $romaneiosBase)
            ->join(
                'entregas as e',
                'e.id',
                '=',
                'r.entrega_id'
            )
            ->leftJoin(
                'funcionarios as f',
                'f.id',
                '=',
                'r.motorista_id'
            )
            ->leftJoin(
                'veiculos as v',
                'v.id',
                '=',
                'r.veiculo_id'
            )
            ->select(
                'r.id',
                'r.entrega_id',
                'r.status',
                'r.data_saida',
                'r.data_retorno',
                'e.codigo_entrega',
                'e.data_prevista',
                'f.nome as motorista_nome',
                'v.placa',
                'v.modelo'
            )
            ->orderByDesc('r.id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DETALHES OCORRENCIAS
        |--------------------------------------------------------------------------
        */

        $ocorrenciasDetalhes = (clone $ocorrenciasBase)
            ->join(
                'romaneios as r',
                'r.id',
                '=',
                'ro.romaneio_id'
            )
            ->leftJoin(
                'entregas as e',
                'e.id',
                '=',
                'r.entrega_id'
            )
            ->leftJoin(
                'funcionarios as f',
                'f.id',
                '=',
                'r.motorista_id'
            )
            ->leftJoin(
                'veiculos as v',
                'v.id',
                '=',
                'r.veiculo_id'
            )
            ->select(
                'ro.id',
                'ro.romaneio_id',
                'ro.categoria',
                'ro.tipo',
                'ro.classificacao_inicial',
                'ro.criticidade',
                'ro.status',
                'ro.quantidade_envolvida',
                'ro.descricao',
                'e.codigo_entrega',
                'f.nome as motorista_nome',
                'v.placa'
            )
            ->orderByDesc('ro.id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DETALHES DAS SAIDAS
        |--------------------------------------------------------------------------
        */

        $saidasDetalhes = (clone $eventosBase)
            ->join(
                'romaneios as r',
                'r.id',
                '=',
                're.romaneio_id'
            )
            ->leftJoin(
                'entregas as e',
                'e.id',
                '=',
                'r.entrega_id'
            )
            ->leftJoin(
                'funcionarios as f',
                'f.id',
                '=',
                're.funcionario_id'
            )
            ->leftJoin(
                'veiculos as v',
                'v.id',
                '=',
                'r.veiculo_id'
            )
            ->where(
                're.evento',
                'Saída do veículo registrada'
            )
            ->select(
                're.id',
                're.romaneio_id',
                're.ocorrido_em',
                're.status_anterior',
                're.status_novo',
                're.dados',
                'e.codigo_entrega',
                'f.nome as motorista_nome',
                'v.placa',
                'v.modelo'
            )
            ->orderByDesc('re.ocorrido_em')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | OPCOES DOS FILTROS
        |--------------------------------------------------------------------------
        */

        $statusDisponiveis = DB::table('entregas')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        $motoristas = DB::table('funcionarios')
            ->where('funcao', 'motorista')
            ->select(
                'id',
                'nome'
            )
            ->orderBy('nome')
            ->get();

        $veiculos = DB::table('veiculos')
            ->select(
                'id',
                'placa',
                'modelo'
            )
            ->orderBy('placa')
            ->get();

        return [
            'inicio' => $inicio,
            'fim' => $fim,
            'status' => $status,
            'periodo' => $periodo,
            'motoristaId' => $motoristaId,
            'veiculoId' => $veiculoId,

            'totalEntregas' => $totalEntregas,
            'entregues' => $entregues,
            'entreguesComOcorrencia' => $entreguesComOcorrencia,
            'entregasParciais' => $entregasParciais,
            'naoEntregues' => $naoEntregues,
            'emRota' => $emRota,
            'atrasadas' => $atrasadas,
            'totalOcorrencias' => $totalOcorrencias,
            'totalRomaneios' => $totalRomaneios,
            'saidasRegistradas' => $saidasRegistradas,
            'motoristasUtilizados' => $motoristasUtilizados,
            'veiculosUtilizados' => $veiculosUtilizados,

            'statusResumo' => $statusResumo,
            'periodosResumo' => $periodosResumo,
            'ocorrenciasPorCategoria' => $ocorrenciasPorCategoria,
            'rankingMotoristas' => $rankingMotoristas,
            'rankingVeiculos' => $rankingVeiculos,

            'entregasPrevistasDetalhes' => $entregasPrevistasDetalhes,
            'entreguesDetalhes' => $entreguesDetalhes,
            'entreguesComOcorrenciaDetalhes' => $entreguesComOcorrenciaDetalhes,
            'parciaisDetalhes' => $parciaisDetalhes,
            'naoEntreguesDetalhes' => $naoEntreguesDetalhes,
            'emRotaDetalhes' => $emRotaDetalhes,
            'atrasadasDetalhes' => $atrasadasDetalhes,
            'ocorrenciasDetalhes' => $ocorrenciasDetalhes,
            'romaneiosDetalhes' => $romaneiosDetalhes,
            'saidasDetalhes' => $saidasDetalhes,

            'statusDisponiveis' => $statusDisponiveis,
            'motoristas' => $motoristas,
            'veiculos' => $veiculos,
        ];
    }

    private function baseEntregas(
        CarbonImmutable $inicio,
        CarbonImmutable $fim,
        string $status,
        string $periodo,
        ?int $motoristaId,
        ?int $veiculoId
    ): Builder {
        $query = DB::table('entregas as e')
            ->whereBetween(
                'e.data_prevista',
                [
                    $inicio->toDateString(),
                    $fim->toDateString(),
                ]
            );

        if ($status !== '') {
            $query->where('e.status', $status);
        }

        if ($periodo !== '') {
            $query->where(
                'e.periodo_entrega',
                $periodo
            );
        }

        if ($motoristaId || $veiculoId) {
            $query->whereExists(
                function ($sub) use (
                    $motoristaId,
                    $veiculoId
                ) {
                    $sub->selectRaw('1')
                        ->from('romaneios as rf')
                        ->whereColumn(
                            'rf.entrega_id',
                            'e.id'
                        );

                    if ($motoristaId) {
                        $sub->where(
                            'rf.motorista_id',
                            $motoristaId
                        );
                    }

                    if ($veiculoId) {
                        $sub->where(
                            'rf.veiculo_id',
                            $veiculoId
                        );
                    }
                }
            );
        }

        return $query;
    }

    private function baseRomaneios(
        Builder $entregasBase,
        ?int $motoristaId,
        ?int $veiculoId
    ): Builder {
        $entregaIds = (clone $entregasBase)
            ->select('e.id');

        $query = DB::table('romaneios as r')
            ->whereIn(
                'r.entrega_id',
                $entregaIds
            );

        if ($motoristaId) {
            $query->where(
                'r.motorista_id',
                $motoristaId
            );
        }

        if ($veiculoId) {
            $query->where(
                'r.veiculo_id',
                $veiculoId
            );
        }

        return $query;
    }

    private function baseDetalhesEntregas(
        Builder $entregasBase,
        ?int $motoristaId,
        ?int $veiculoId
    ): Builder {
        $ultimoRomaneio = DB::table('romaneios as ur')
            ->selectRaw(
                'MAX(ur.id) AS id, ur.entrega_id'
            );

        if ($motoristaId) {
            $ultimoRomaneio->where(
                'ur.motorista_id',
                $motoristaId
            );
        }

        if ($veiculoId) {
            $ultimoRomaneio->where(
                'ur.veiculo_id',
                $veiculoId
            );
        }

        $ultimoRomaneio->groupBy(
            'ur.entrega_id'
        );

        return (clone $entregasBase)
            ->leftJoinSub(
                $ultimoRomaneio,
                'urx',
                function ($join) {
                    $join->on(
                        'urx.entrega_id',
                        '=',
                        'e.id'
                    );
                }
            )
            ->leftJoin(
                'romaneios as r',
                'r.id',
                '=',
                'urx.id'
            )
            ->leftJoin(
                'funcionarios as f',
                'f.id',
                '=',
                'r.motorista_id'
            )
            ->leftJoin(
                'veiculos as v',
                'v.id',
                '=',
                'r.veiculo_id'
            )
            ->select(
                'e.id',
                'e.codigo_entrega',
                'e.data_prevista',
                'e.data_prevista_entrega',
                'e.periodo_entrega',
                'e.data_realizada',
                'e.status',
                'e.endereco_entrega',
                'r.id as romaneio_id',
                'r.status as romaneio_status',
                'r.data_saida',
                'r.data_retorno',
                'f.nome as motorista_nome',
                'v.placa',
                'v.modelo'
            );
    }
}