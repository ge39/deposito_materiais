<?php

namespace App\Services\Expedicao;

use App\Models\Empresa;
use App\Models\Entrega;
use App\Models\Romaneio;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class EntregaInteligenteService
{
    private array $metadadosRota = [];

    private array $matrizValhalla = [];

    private bool $valhallaDisponivel = false;

    private ?string $valhallaErro = null;

    private const RAIO_PROXIMIDADE_KM = 0.1;

    private const STATUS_ENTREGAS_EFETIVAS = [
        'Entregue',
        'Entregue_finalizada_com_ocorrencia',
    ];

    private const STATUS_ENCERRADOS = [
        'Entregue',
        'Entregue_parcial',
        'Entregue_finalizada_com_ocorrencia',
        'Nao_entregue',
        'Recusada',
        'Devolvida',
        'Cancelada',
    ];

    private const STATUS_APTOS_PARA_ROTA = [
        'Aguardando_separacao',
        'Em_preparacao',
        'Pronta_para_carregamento',
        'Carregada',
        'Liberada',
        'Em_rota',
        'No_destino',
    ];

    /**
     * A Entrega Inteligente acompanha o pátio e a operação externa ativa.
     * Planejamento, separação, entregas encerradas e retorno permanecem
     * em seus respectivos módulos.
     */
    private const STATUS_ENTREGA_INTELIGENTE = [
        'Pronta_para_carregamento',
        'Carregada',
        'Liberada',
        'Em_rota',
        'No_destino',
    ];

    public function montarDashboard(
        CarbonInterface $dataReferencia
    ): array {
        $referencia = CarbonImmutable::instance(
            $dataReferencia
        )->startOfDay();

        $inicioJanela = $referencia->subDays(7);
        $fimJanela = $referencia->addDays(7);

        $empresaAtiva = $this->consultarEmpresaAtiva();
        $empresaNormalizada = $this->normalizarEmpresaAtiva(
            $empresaAtiva
        );

        $entregas = $this->consultarEntregas(
            $inicioJanela,
            $fimJanela
        );

        $romaneiosAtivos = $this
            ->consultarRomaneiosAtivos($entregas);

        $this->prepararMatrizValhalla(
            $empresaNormalizada,
            $entregas
        );

        $entregas = $this->aplicarOrdemInteligente(
            $entregas,
            $romaneiosAtivos,
            $empresaNormalizada,
            $referencia
        );

        $linhas = $entregas
            ->map(function (Entrega $entrega) use (
                $referencia,
                $romaneiosAtivos
            ): array {
                return $this->normalizarEntrega(
                    $entrega,
                    $referencia,
                    $romaneiosAtivos->get(
                        $entrega->id
                    )
                );
            })
            ->sortBy([
                ['data_chave', 'asc'],
                ['periodo_ordem', 'asc'],
                ['codigo', 'asc'],
            ])
            ->values();

        $linhas = $this->aplicarOrdemGlobalMapa(
            $linhas,
            $empresaNormalizada
        );

        $ultimosSete = $linhas->filter(
            fn (array $linha): bool =>
                $linha['data_chave'] >= $inicioJanela->toDateString()
                && $linha['data_chave'] < $referencia->toDateString()
        );

        $entregasHoje = $linhas
            ->where(
                'data_chave',
                $referencia->toDateString()
            )
            ->values();

        $proximosSete = $linhas->filter(
            fn (array $linha): bool =>
                $linha['data_chave'] > $referencia->toDateString()
                && $linha['data_chave'] <= $fimJanela->toDateString()
        );

        $oportunidades = $this
            ->detectarOportunidadesAntecipacao(
                $linhas,
                $referencia
            );

        $agenda = $this->montarAgenda(
            $linhas,
            $referencia
        );

        return [
            'empresaAtiva' => $empresaNormalizada,
            'dataReferencia' => $referencia,
            'inicioJanela' => $inicioJanela,
            'fimJanela' => $fimJanela,
            'roteirizador' => [
                'motor' => 'Valhalla',
                'disponivel' => $this->valhallaDisponivel,
                'pontos_matriz' => count($this->matrizValhalla),
                'fallback_geografico' => ! $this->valhallaDisponivel,
                'erro' => $this->valhallaErro,
            ],
            'resumo' => [
                'ultimos_sete' =>
                    $this->resumir($ultimosSete),

                'hoje' =>
                    $this->resumir($entregasHoje),

                'proximos_sete' =>
                    $this->resumir($proximosSete),

                'oportunidades' =>
                    $oportunidades->count(),
            ],
            'agenda' => $agenda,
            'entregasHoje' => $entregasHoje,
            'oportunidades' => $oportunidades,
            'indicadores' => $this->montarIndicadores(
                $linhas
            ),
            'rankingClientes' => $this->montarRankingClientes(
                $linhas
            ),
            'distribuicaoPeriodos' =>
                $this->montarDistribuicaoPeriodos($linhas),
            'distribuicaoStatus' =>
                $this->montarDistribuicaoStatus($linhas),
            'concentracaoLocalidades' =>
                $this->montarConcentracaoLocalidades($linhas),
            'graficoDiario' => $this->montarGraficoDiario(
                $agenda
            ),
            'alertasOperacionais' =>
                $this->montarAlertasOperacionais(
                    $linhas,
                    $oportunidades
                ),
            'entregasAtrasadas' => $linhas
                ->where('atrasada', true)
                ->values(),
            'entregasDetalhadas' => $linhas,
        ];
    }

    public function detectarOportunidadesAntecipacao(
        Collection $linhas,
        CarbonInterface $dataReferencia
    ): Collection {
        $referencia = CarbonImmutable::instance(
            $dataReferencia
        )->startOfDay();

        $hoje = $linhas
            ->filter(
                fn (array $linha): bool =>
                    $linha['data_chave'] === $referencia->toDateString()
                    && $linha['apta_para_rota']
                    && $linha['endereco_chave'] !== ''
            )
            ->groupBy('endereco_chave');

        if ($hoje->isEmpty()) {
            return collect();
        }

        return $linhas
            ->filter(function (array $linha) use ($referencia): bool {
                if (
                    ! $linha['apta_para_rota']
                    || $linha['endereco_chave'] === ''
                ) {
                    return false;
                }

                return in_array(
                    $linha['data_chave'],
                    [
                        $referencia->addDay()->toDateString(),
                        $referencia->addDays(2)->toDateString(),
                    ],
                    true
                );
            })
            ->filter(
                fn (array $linha): bool =>
                    $hoje->has($linha['endereco_chave'])
            )
            ->map(function (array $linha) use (
                $hoje,
                $referencia
            ): array {
                $dataFutura = CarbonImmutable::parse(
                    $linha['data_chave']
                );

                return [
                    'entrega_futura' => $linha,
                    'entregas_hoje' => $hoje
                        ->get($linha['endereco_chave'])
                        ->values(),
                    'dias_antecipacao' =>
                        $referencia->diffInDays($dataFutura),
                    'endereco' => $linha['endereco'],
                ];
            })
            ->sortBy([
                ['dias_antecipacao', 'asc'],
                ['entrega_futura.periodo_ordem', 'asc'],
                ['entrega_futura.codigo', 'asc'],
            ])
            ->values();
    }

    private function consultarEmpresaAtiva(): ?Empresa
    {
        return Empresa::query()
            ->where('ativo', 1)
            ->orderBy('id')
            ->first();
    }

    private function normalizarEmpresaAtiva(
        ?Empresa $empresa
    ): array {
        $nome = trim(
            (string) (
                $empresa?->nome
                ?? config('logistica.deposito.nome')
                ?? config('app.name')
                ?? 'Empresa'
            )
        );

        $endereco = $empresa
            ? $this->montarEnderecoEmpresa($empresa)
            : '';

        if ($endereco === '') {
            $endereco = trim(
                (string) config(
                    'logistica.deposito.endereco',
                    'Endereço não configurado'
                )
            );
        }

        $latitude = $this->normalizarCoordenada(
            $empresa?->latitude,
            -90,
            90
        ) ?? $this->normalizarCoordenada(
            config(
                'logistica.deposito.latitude',
                config('openstreetmap.center.lat')
            ),
            -90,
            90
        );

        $longitude = $this->normalizarCoordenada(
            $empresa?->longitude,
            -180,
            180
        ) ?? $this->normalizarCoordenada(
            config(
                'logistica.deposito.longitude',
                config('openstreetmap.center.lng')
            ),
            -180,
            180
        );

        return [
            'id' => $empresa?->id,
            'nome' => $nome !== ''
                ? $nome
                : 'Empresa',
            'endereco' => $endereco,
            'telefone' => $empresa?->telefone,
            'email' => $empresa?->email,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => (int) config(
                'logistica.deposito.zoom',
                16
            ),
            'raio_patio_metros' => (int) config(
                'logistica.deposito.raio_patio_metros',
                60
            ),
        ];
    }

    private function montarEnderecoEmpresa(Empresa $empresa): string
    {
        $logradouro = trim(
            implode(
                ', ',
                array_filter([
                    trim((string) $empresa->endereco),
                    trim((string) $empresa->numero),
                ])
            )
        );

        $cidadeEstado = trim(
            implode(
                ' - ',
                array_filter([
                    trim((string) $empresa->cidade),
                    trim((string) $empresa->estado),
                ])
            )
        );

        $partes = array_filter([
            $logradouro,
            trim((string) $empresa->complemento),
            trim((string) $empresa->bairro),
            $cidadeEstado,
            trim((string) $empresa->cep) !== ''
                ? 'CEP ' . trim((string) $empresa->cep)
                : null,
        ]);

        return implode(', ', $partes);
    }

    private function consultarEntregas(
        CarbonInterface $inicio,
        CarbonInterface $fim
    ): Collection {
        return Entrega::query()
            ->with([
                'orcamento.cliente',
                'venda.cliente',
                'veiculo',
                'motorista',
                'itens.vendaItem.produto.categoria',
                'itens.itemOrcamento.produto.categoria',
            ])
            ->where(
                'tipo_entrega',
                'entrega'
            )
            ->whereIn(
                'status',
                self::STATUS_ENTREGA_INTELIGENTE
            )
            ->where(function (Builder $query) use (
                $inicio,
                $fim
            ): void {
                $query
                    ->whereBetween(
                        'data_prevista_entrega',
                        [
                            $inicio->toDateString(),
                            $fim->toDateString(),
                        ]
                    )
                    ->orWhere(function (Builder $query) use (
                        $inicio,
                        $fim
                    ): void {
                        $query
                            ->whereNull(
                                'data_prevista_entrega'
                            )
                            ->whereBetween(
                                'data_prevista',
                                [
                                    $inicio->toDateString(),
                                    $fim->toDateString(),
                                ]
                            );
                    });
            })
            ->orderByRaw(
                'COALESCE(data_prevista_entrega, data_prevista) ASC'
            )
            ->orderBy('periodo_entrega')
            ->orderBy('id')
            ->get();
    }

    private function consultarRomaneiosAtivos(
        Collection $entregas
    ): Collection {
        if ($entregas->isEmpty()) {
            return collect();
        }

        return Romaneio::query()
            ->with([
                'veiculo',
                'motorista',
                'veiculoExecutante',
                'motoristaExecutante',
            ])
            ->whereIn(
                'entrega_id',
                $entregas->pluck('id')
            )
            ->where(
                'status',
                '!=',
                'Cancelado'
            )
            ->orderBy('id')
            ->get()
            ->keyBy('entrega_id');
    }

    private function prepararMatrizValhalla(
        array $empresa,
        Collection $entregas
    ): void {
        $this->matrizValhalla = [];
        $this->valhallaDisponivel = false;
        $this->valhallaErro = null;

        $latitudeEmpresa = $empresa['latitude'] ?? null;
        $longitudeEmpresa = $empresa['longitude'] ?? null;

        if ($latitudeEmpresa === null || $longitudeEmpresa === null) {
            $this->valhallaErro = 'Coordenadas da empresa não configuradas.';

            return;
        }

        $pontos = collect([
            $this->chaveCoordenada(
                (float) $latitudeEmpresa,
                (float) $longitudeEmpresa
            ) => [
                'lat' => (float) $latitudeEmpresa,
                'lon' => (float) $longitudeEmpresa,
            ],
        ]);

        foreach ($entregas as $entrega) {
            $latitude = $this->normalizarCoordenada(
                $entrega->latitude_entrega,
                -90,
                90
            );
            $longitude = $this->normalizarCoordenada(
                $entrega->longitude_entrega,
                -180,
                180
            );

            if (
                ! (bool) $entrega->coordenada_confirmada
                || $latitude === null
                || $longitude === null
            ) {
                continue;
            }

            $pontos->put(
                $this->chaveCoordenada($latitude, $longitude),
                [
                    'lat' => $latitude,
                    'lon' => $longitude,
                ]
            );
        }

        $limite = max(
            2,
            (int) config(
                'services.valhalla.max_matrix_locations',
                50
            )
        );
        $pontos = $pontos->take($limite);

        if ($pontos->count() < 2) {
            $this->valhallaErro = 'Não há entregas com coordenadas confirmadas.';

            return;
        }

        $chaves = $pontos->keys()->values();
        $localizacoes = $pontos->values()->all();
        $url = rtrim(
            (string) config(
                'services.valhalla.url',
                'http://127.0.0.1:8002'
            ),
            '/'
        );

        try {
            $resposta = Http::acceptJson()
                ->asJson()
                ->connectTimeout(2)
                ->timeout(
                    (int) config(
                        'services.valhalla.timeout',
                        30
                    )
                )
                ->post($url . '/sources_to_targets', [
                    'sources' => $localizacoes,
                    'targets' => $localizacoes,
                    'costing' => 'auto',
                    'units' => 'kilometers',
                ]);

            if (! $resposta->successful()) {
                $this->valhallaErro = 'Valhalla respondeu HTTP '
                    . $resposta->status()
                    . '.';

                return;
            }

            $linhas = $resposta->json('sources_to_targets');

            if (! is_array($linhas)) {
                $this->valhallaErro = 'Matriz retornada em formato inválido.';

                return;
            }

            foreach ($linhas as $indiceOrigem => $destinos) {
                $chaveOrigem = $chaves->get($indiceOrigem);

                if ($chaveOrigem === null || ! is_array($destinos)) {
                    continue;
                }

                foreach ($destinos as $indiceDestino => $trecho) {
                    if (! is_array($trecho)) {
                        continue;
                    }

                    $indiceDestinoReal = isset($trecho['to_index'])
                            ? (int) $trecho['to_index']
                            : $indiceDestino;
                    $chaveDestino = $chaves->get(
                        $indiceDestinoReal
                    );
                    $distancia = $trecho['distance'] ?? null;

                    if (
                        $chaveDestino === null
                        || ! is_numeric($distancia)
                    ) {
                        continue;
                    }

                    $this->matrizValhalla[$chaveOrigem][
                        $chaveDestino
                    ] = [
                        'distancia_km' => (float) $distancia,
                        'tempo_segundos' => isset($trecho['time'])
                            && is_numeric($trecho['time'])
                                ? (float) $trecho['time']
                                : null,
                    ];
                }
            }

            $this->valhallaDisponivel = $this->matrizValhalla !== [];

            if (! $this->valhallaDisponivel) {
                $this->valhallaErro = 'A matriz não contém rotas utilizáveis.';
            }
        } catch (Throwable $exception) {
            $this->valhallaErro = Str::limit(
                $exception->getMessage(),
                180
            );
        }
    }

    private function chaveCoordenada(
        float $latitude,
        float $longitude
    ): string {
        return number_format($latitude, 7, '.', '')
            . ':'
            . number_format($longitude, 7, '.', '');
    }

    private function trechoValhalla(
        float $latitudeOrigem,
        float $longitudeOrigem,
        float $latitudeDestino,
        float $longitudeDestino
    ): ?array {
        $chaveOrigem = $this->chaveCoordenada(
            $latitudeOrigem,
            $longitudeOrigem
        );
        $chaveDestino = $this->chaveCoordenada(
            $latitudeDestino,
            $longitudeDestino
        );

        return $this->matrizValhalla[$chaveOrigem][$chaveDestino]
            ?? null;
    }

    private function aplicarOrdemInteligente(
        Collection $entregas,
        Collection $romaneios,
        array $empresa,
        CarbonInterface $referencia
    ): Collection {
        $latitudeEmpresa = $empresa['latitude'] ?? null;
        $longitudeEmpresa = $empresa['longitude'] ?? null;

        if (
            $entregas->isEmpty()
            || $latitudeEmpresa === null
            || $longitudeEmpresa === null
        ) {
            return $entregas;
        }

        $concentracoes = $this->calcularConcentracoesRegionais(
            $entregas
        );

        $grupos = $entregas->groupBy(
            function (Entrega $entrega) use ($romaneios): string {
                $romaneio = $romaneios->get($entrega->id);
                $veiculoId = $romaneio?->veiculo_executante_id
                    ?? $romaneio?->veiculo_id
                    ?? $entrega->veiculo_id;
                $motoristaId = $romaneio?->motorista_executante_id
                    ?? $romaneio?->motorista_id
                    ?? $entrega->motorista_id;

                if (! $veiculoId && ! $motoristaId) {
                    return 'sem-equipe-' . $entrega->id;
                }

                return 'veiculo-' . ($veiculoId ?: 0)
                    . '-motorista-' . ($motoristaId ?: 0);
            }
        );

        foreach ($grupos as $grupo) {
            $this->ordenarGrupoInteligente(
                $grupo->values(),
                $romaneios,
                (float) $latitudeEmpresa,
                (float) $longitudeEmpresa,
                $referencia,
                $concentracoes
            );
        }

        return $entregas;
    }

    private function ordenarGrupoInteligente(
        Collection $grupo,
        Collection $romaneios,
        float $latitudeInicial,
        float $longitudeInicial,
        CarbonInterface $referencia,
        array $concentracoes
    ): void {
        $rotaIniciada = $grupo->contains(
            function (Entrega $entrega) use ($romaneios): bool {
                $romaneio = $romaneios->get($entrega->id);

                return in_array(
                    $entrega->status,
                    ['Em_rota', 'No_destino'],
                    true
                ) || $romaneio?->data_saida !== null;
            }
        );

        if ($rotaIniciada) {
            $this->preservarOrdemEmExecucao(
                $grupo,
                $concentracoes,
                $latitudeInicial,
                $longitudeInicial
            );

            return;
        }

        $primeira = $grupo->first();
        $romaneioPrimeira = $primeira
            ? $romaneios->get($primeira->id)
            : null;
        $veiculo = $romaneioPrimeira?->veiculoExecutante
            ?? $romaneioPrimeira?->veiculo
            ?? $primeira?->veiculo;

        $pendentes = $grupo
            ->mapWithKeys(function (Entrega $entrega) use (
                $romaneios,
                $veiculo,
                $referencia,
                $concentracoes
            ): array {
                return [
                    $entrega->id => $this->dadosRoteirizacao(
                        $entrega,
                        $romaneios->get($entrega->id),
                        $veiculo,
                        $referencia,
                        $concentracoes[$entrega->id] ?? []
                    ),
                ];
            });

        $uso = [
            'kg' => 0.0,
            'm3' => 0.0,
            'unidades' => 0.0,
        ];
        $latitudeAtual = $latitudeInicial;
        $longitudeAtual = $longitudeInicial;
        $cepAtual = null;
        $bairroAtual = null;
        $ordem = 1;

        while ($pendentes->isNotEmpty()) {
            $candidatos = $pendentes
                ->map(function (array $dados) use (
                    $uso,
                    $veiculo,
                    $latitudeAtual,
                    $longitudeAtual,
                    $cepAtual,
                    $bairroAtual
                ): array {
                    $dados['cabe_capacidade'] =
                        $this->cabeNaCapacidade(
                            $dados,
                            $uso,
                            $veiculo
                        );
                    $dados['mesmo_cep'] = $cepAtual !== null
                        && $dados['cep'] === $cepAtual;
                    $dados['mesmo_bairro'] = $bairroAtual !== null
                        && $dados['bairro_chave'] === $bairroAtual;
                    $distanciaGeografica =
                        $dados['coordenada_valida']
                            ? $this->distanciaKm(
                            $latitudeAtual,
                            $longitudeAtual,
                            $dados['latitude'],
                            $dados['longitude']
                            )
                            : 999999;
                    $trechoValhalla = $dados['coordenada_valida']
                        ? $this->trechoValhalla(
                            $latitudeAtual,
                            $longitudeAtual,
                            $dados['latitude'],
                            $dados['longitude']
                        )
                        : null;
                    $dados['distancia_atual'] =
                        $trechoValhalla['distancia_km']
                            ?? $distanciaGeografica;
                    $dados['tempo_atual_segundos'] =
                        $trechoValhalla['tempo_segundos'] ?? null;
                    $dados['fonte_distancia'] = $trechoValhalla
                        ? 'valhalla'
                        : 'geografica';
                    $dados['proxima_100m'] =
                        $distanciaGeografica
                            <= self::RAIO_PROXIMIDADE_KM;

                    return $dados;
                })
                ->sort(function (array $a, array $b): int {
                    return [
                        $a['atrasada'] ? 0 : 1,
                        -$a['dias_atraso'],
                        $a['data_chave'],
                        $a['periodo_ordem'],
                        $a['proxima_100m'] ? 0 : 1,
                        $a['mesmo_cep'] ? 0 : 1,
                        $a['mesmo_bairro'] ? 0 : 1,
                        -$a['concentracao_regional'],
                        round($a['distancia_atual'], 4),
                        $a['restricoes'] === [] ? 0 : 1,
                        $a['cabe_capacidade'] ? 0 : 1,
                        $a['entrega']->id,
                    ] <=> [
                        $b['atrasada'] ? 0 : 1,
                        -$b['dias_atraso'],
                        $b['data_chave'],
                        $b['periodo_ordem'],
                        $b['proxima_100m'] ? 0 : 1,
                        $b['mesmo_cep'] ? 0 : 1,
                        $b['mesmo_bairro'] ? 0 : 1,
                        -$b['concentracao_regional'],
                        round($b['distancia_atual'], 4),
                        $b['restricoes'] === [] ? 0 : 1,
                        $b['cabe_capacidade'] ? 0 : 1,
                        $b['entrega']->id,
                    ];
                });

            $selecionada = $candidatos->first();

            if (! $selecionada) {
                break;
            }

            $entrega = $selecionada['entrega'];
            $restricoes = $selecionada['restricoes'];

            if (! $selecionada['cabe_capacidade']) {
                $restricoes[] = 'Capacidade acumulada excedida';
            }

            $this->atribuirOrdemRota(
                $entrega,
                $ordem,
                $referencia
            );

            $this->metadadosRota[$entrega->id] = [
                'ordem_inteligente' => $ordem,
                'distancia_anterior_km' => round(
                    $selecionada['distancia_atual'],
                    2
                ),
                'tempo_anterior_segundos' =>
                    $selecionada['tempo_atual_segundos'],
                'fonte_distancia' =>
                    $selecionada['fonte_distancia'],
                'peso_estimado_kg' => round(
                    $selecionada['kg'],
                    2
                ),
                'volume_estimado_m3' => round(
                    $selecionada['m3'],
                    3
                ),
                'concentracao_regional' =>
                    $selecionada['concentracao_regional'],
                'concentracao_bairro' =>
                    $selecionada['concentracao_bairro'],
                'concentracao_cep' =>
                    $selecionada['concentracao_cep'],
                'concentracao_100m' =>
                    $selecionada['concentracao_100m'],
                'restricoes_rota' => array_values(
                    array_unique($restricoes)
                ),
                'rota_preservada' => false,
            ];

            $uso['kg'] += $selecionada['kg'];
            $uso['m3'] += $selecionada['m3'];
            $uso['unidades'] += $selecionada['unidades'];
            if ($selecionada['coordenada_valida']) {
                $latitudeAtual = $selecionada['latitude'];
                $longitudeAtual = $selecionada['longitude'];
            }
            $cepAtual = $selecionada['cep'];
            $bairroAtual = $selecionada['bairro_chave'];
            $pendentes->forget($entrega->id);
            $ordem++;
        }
    }

    private function preservarOrdemEmExecucao(
        Collection $grupo,
        array $concentracoes,
        float $latitudeInicial,
        float $longitudeInicial
    ): void {
        $maiorOrdem = (int) $grupo->max('ordem_rota');
        $latitudeAtual = $latitudeInicial;
        $longitudeAtual = $longitudeInicial;

        foreach (
            $grupo->sortBy(
                fn (Entrega $entrega): int =>
                    $entrega->ordem_rota ?? PHP_INT_MAX
            ) as $entrega
        ) {
            $concentracao = $concentracoes[$entrega->id] ?? [];
            $latitudeEntrega = $this->normalizarCoordenada(
                $entrega->latitude_entrega,
                -90,
                90
            );
            $longitudeEntrega = $this->normalizarCoordenada(
                $entrega->longitude_entrega,
                -180,
                180
            );
            $coordenadaValida =
                (bool) $entrega->coordenada_confirmada
                && $latitudeEntrega !== null
                && $longitudeEntrega !== null;
            $trechoValhalla = $coordenadaValida
                ? $this->trechoValhalla(
                    $latitudeAtual,
                    $longitudeAtual,
                    $latitudeEntrega,
                    $longitudeEntrega
                )
                : null;
            $distanciaAnterior = $coordenadaValida
                ? round(
                    $trechoValhalla['distancia_km']
                        ?? $this->distanciaKm(
                            $latitudeAtual,
                            $longitudeAtual,
                            $latitudeEntrega,
                            $longitudeEntrega
                        ),
                    2
                )
                : null;

            if ($entrega->ordem_rota === null) {
                $maiorOrdem++;
                $entrega->ordem_rota = $maiorOrdem;
            }

            $this->metadadosRota[$entrega->id] = [
                'ordem_inteligente' => (int) $entrega->ordem_rota,
                'distancia_anterior_km' => $distanciaAnterior,
                'tempo_anterior_segundos' =>
                    $trechoValhalla['tempo_segundos'] ?? null,
                'fonte_distancia' => $trechoValhalla
                    ? 'valhalla'
                    : 'geografica',
                'peso_estimado_kg' => null,
                'volume_estimado_m3' => null,
                'concentracao_regional' => (int) (
                    $concentracao['regional'] ?? 1
                ),
                'concentracao_bairro' => (int) (
                    $concentracao['bairro'] ?? 1
                ),
                'concentracao_cep' => (int) (
                    $concentracao['cep'] ?? 1
                ),
                'concentracao_100m' => (int) (
                    $concentracao['proximidade_100m'] ?? 1
                ),
                'restricoes_rota' => [],
                'rota_preservada' => true,
            ];

            if ($coordenadaValida) {
                $latitudeAtual = $latitudeEntrega;
                $longitudeAtual = $longitudeEntrega;
            }
        }
    }

    private function dadosRoteirizacao(
        Entrega $entrega,
        ?Romaneio $romaneio,
        $veiculo,
        CarbonInterface $referencia,
        array $concentracao
    ): array {
        $data = CarbonImmutable::parse(
            $entrega->data_prevista_entrega
                ?? $entrega->data_prevista
        )->startOfDay();
        $latitudeNormalizada = $this->normalizarCoordenada(
            $entrega->latitude_entrega,
            -90,
            90
        );
        $longitudeNormalizada = $this->normalizarCoordenada(
            $entrega->longitude_entrega,
            -180,
            180
        );
        $coordenadaValida =
            (bool) $entrega->coordenada_confirmada
            && $latitudeNormalizada !== null
            && $longitudeNormalizada !== null;
        $latitude = $latitudeNormalizada ?? 0.0;
        $longitude = $longitudeNormalizada ?? 0.0;
        $itens = collect($entrega->itens);
        $kg = 0.0;
        $m3 = 0.0;
        $unidades = 0.0;
        $restricoes = [];

        foreach ($itens as $item) {
            $quantidade = (float) ($item->quantidade_prevista ?? 0);
            $produto = $item->produto
                ?? $item->vendaItem?->produto
                ?? $item->itemOrcamento?->produto;

            $unidades += $quantidade;
            $kg += $quantidade * (float) ($produto?->peso ?? 0);
            $m3 += $quantidade * $this->volumeProdutoM3($produto);

            $restricaoProduto = $this->validarProdutoVeiculo(
                $produto,
                $veiculo
            );

            if ($restricaoProduto !== null) {
                $restricoes[] = $restricaoProduto;
            }
        }

        $enderecoNormalizado = $this->normalizarTextoRota(
            (string) $entrega->endereco_entrega
        );

        if (
            $veiculo
            && (bool) ($veiculo->restricao_zona_central ?? false)
            && str_contains($enderecoNormalizado, 'centro')
        ) {
            $restricoes[] = 'Veículo com restrição de zona central';
        }

        if (! $coordenadaValida) {
            $restricoes[] = 'Coordenada da entrega não confirmada';
        }

        $diasAtraso = $data->lessThan($referencia)
            ? (int) $data->diffInDays($referencia, true)
            : 0;
        $localidade = $this->resolverLocalidade(
            (string) $entrega->endereco_entrega
        );

        return [
            'entrega' => $entrega,
            'romaneio' => $romaneio,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'coordenada_valida' => $coordenadaValida,
            'cep' => $this->extrairCepRota(
                (string) $entrega->endereco_entrega
            ),
            'bairro_chave' => $this->normalizarTextoRota(
                (string) ($localidade['bairro'] ?? '')
            ),
            'data_chave' => $data->toDateString(),
            'periodo_ordem' => $this->ordemPeriodo(
                $entrega->periodo_entrega
            ),
            'atrasada' => $diasAtraso > 0,
            'dias_atraso' => $diasAtraso,
            'kg' => $kg,
            'm3' => $m3,
            'unidades' => $unidades,
            'restricoes' => array_values(
                array_unique($restricoes)
            ),
            'concentracao_regional' => (int) (
                $concentracao['regional'] ?? 1
            ),
            'concentracao_bairro' => (int) (
                $concentracao['bairro'] ?? 1
            ),
            'concentracao_cep' => (int) (
                $concentracao['cep'] ?? 1
            ),
            'concentracao_100m' => (int) (
                $concentracao['proximidade_100m'] ?? 1
            ),
        ];
    }

    private function calcularConcentracoesRegionais(
        Collection $entregas
    ): array {
        $dados = $entregas->map(function (Entrega $entrega): array {
            $data = CarbonImmutable::parse(
                $entrega->data_prevista_entrega
                    ?? $entrega->data_prevista
            )->toDateString();
            $endereco = (string) $entrega->endereco_entrega;
            $localidade = $this->resolverLocalidade($endereco);
            $latitude = $this->normalizarCoordenada(
                $entrega->latitude_entrega,
                -90,
                90
            );
            $longitude = $this->normalizarCoordenada(
                $entrega->longitude_entrega,
                -180,
                180
            );

            return [
                'id' => (int) $entrega->id,
                'data' => $data,
                'periodo' => $this->ordemPeriodo(
                    $entrega->periodo_entrega
                ),
                'bairro' => $this->normalizarTextoRota(
                    (string) ($localidade['bairro'] ?? '')
                ),
                'cep' => $this->extrairCepRota($endereco),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'coordenada_valida' =>
                    (bool) $entrega->coordenada_confirmada
                    && $latitude !== null
                    && $longitude !== null,
            ];
        })->values();

        return $dados->mapWithKeys(function (array $entrega) use (
            $dados
        ): array {
            $compativeis = $dados->filter(
                fn (array $candidata): bool =>
                    $candidata['data'] === $entrega['data']
                    && $candidata['periodo'] === $entrega['periodo']
            );

            $bairro = $entrega['bairro'] !== ''
                ? $compativeis->where('bairro', $entrega['bairro'])->count()
                : 1;
            $cep = $entrega['cep'] !== ''
                ? $compativeis->where('cep', $entrega['cep'])->count()
                : 1;
            $proximidade = $entrega['coordenada_valida']
                ? $compativeis->filter(function (array $candidata) use (
                    $entrega
                ): bool {
                    return $candidata['coordenada_valida']
                        && $this->distanciaKm(
                            $entrega['latitude'],
                            $entrega['longitude'],
                            $candidata['latitude'],
                            $candidata['longitude']
                        ) <= self::RAIO_PROXIMIDADE_KM;
                })->count()
                : 1;

            return [
                $entrega['id'] => [
                    'bairro' => max(1, $bairro),
                    'cep' => max(1, $cep),
                    'proximidade_100m' => max(1, $proximidade),
                    'regional' => max($bairro, $cep, $proximidade, 1),
                ],
            ];
        })->all();
    }

    private function atribuirOrdemRota(
        Entrega $entrega,
        int $ordem,
        CarbonInterface $referencia
    ): void {
        $entrega->ordem_rota = $ordem;

        $hoje = CarbonImmutable::today(
            (string) config('app.timezone')
        );

        if (
            ! CarbonImmutable::instance($referencia)
                ->startOfDay()
                ->equalTo($hoje)
            || ! in_array(
                $entrega->status,
                ['Carregada', 'Liberada'],
                true
            )
        ) {
            return;
        }

        if ((int) $entrega->getOriginal('ordem_rota') !== $ordem) {
            $entrega->updateQuietly([
                'ordem_rota' => $ordem,
            ]);
        }
    }

    private function cabeNaCapacidade(
        array $dados,
        array $uso,
        $veiculo
    ): bool {
        if (! $veiculo) {
            return true;
        }

        $limites = [
            'kg' => (float) ($veiculo->capacidade_kg ?? 0),
            'm3' => (float) ($veiculo->capacidade_m3 ?? 0),
            'unidades' => (float) (
                $veiculo->capacidade_unidades ?? 0
            ),
        ];

        foreach ($limites as $campo => $limite) {
            if (
                $limite > 0
                && ($uso[$campo] + $dados[$campo]) > $limite
            ) {
                return false;
            }
        }

        return true;
    }

    private function validarProdutoVeiculo(
        $produto,
        $veiculo
    ): ?string {
        if (! $produto || ! $veiculo) {
            return null;
        }

        $descricao = $this->normalizarTextoRota(
            trim(
                (string) ($produto->nome ?? '')
                . ' '
                . (string) ($produto->categoria?->nome ?? '')
            )
        );

        $regras = [
            'aceita_areia_pedra' => ['areia', 'pedra', 'brita'],
            'aceita_blocos_tijolos' => ['bloco', 'tijolo'],
            'aceita_cimento_argamassa' => ['cimento', 'argamassa'],
            'aceita_tintas_quimicos' => ['tinta', 'quimico', 'solvente'],
            'aceita_telhas' => ['telha'],
            'aceita_madeiras' => ['madeira', 'madeiramento'],
        ];

        foreach ($regras as $campo => $palavras) {
            foreach ($palavras as $palavra) {
                if (
                    str_contains($descricao, $palavra)
                    && ! (bool) ($veiculo->{$campo} ?? false)
                ) {
                    return 'Veículo incompatível com '
                        . ($produto->nome ?? 'produto');
                }
            }
        }

        return null;
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

    private function extrairCepRota(string $endereco): ?string
    {
        if (preg_match('/\b\d{5}-?\d{3}\b/', $endereco, $resultado)) {
            return preg_replace('/\D/', '', $resultado[0]);
        }

        return null;
    }

    private function normalizarTextoRota(string $texto): string
    {
        return Str::lower(
            Str::ascii(trim($texto))
        );
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

    private function normalizarEntrega(
        Entrega $entrega,
        CarbonInterface $dataReferencia,
        ?Romaneio $romaneio
    ): array {
        $dataPlanejada = CarbonImmutable::parse(
            $entrega->data_prevista_entrega
                ?? $entrega->data_prevista
        )->startOfDay();

        $cliente = $entrega->orcamento?->cliente
            ?? $entrega->venda?->cliente
            ?? null;

        $endereco = trim(
            (string) (
                $entrega->endereco_entrega
                ?? ''
            )
        );

        $itens = collect(
            $entrega->itens
        );

        $quantidadePrevista = $itens->sum(
            fn ($item): float =>
                (float) ($item->quantidade_prevista ?? 0)
        );

        $quantidadeEntregue = $itens->sum(
            fn ($item): float =>
                (float) ($item->quantidade_entregue ?? 0)
        );

        $encerrada = in_array(
            $entrega->status,
            self::STATUS_ENCERRADOS,
            true
        );

        $entregaEfetiva = in_array(
            $entrega->status,
            self::STATUS_ENTREGAS_EFETIVAS,
            true
        );

        $dataAtual = CarbonImmutable::today(
            (string) config('app.timezone')
        );

        $atrasada = ! $encerrada
            && $dataPlanejada->lessThan($dataAtual);

        $diasAtraso = $atrasada
            ? (int) $dataPlanejada->diffInDays(
                $dataAtual,
                true
            )
            : 0;

        $aptaParaRota = in_array(
            $entrega->status,
            self::STATUS_APTOS_PARA_ROTA,
            true
        );

        $localidade = $this->resolverLocalidade(
            $endereco
        );

        $produtos = $itens
            ->map(function ($item): ?string {
                $produto = $item->produto
                    ?? $item->vendaItem?->produto
                    ?? $item->itemOrcamento?->produto
                    ?? null;

                return $produto?->nome
                    ?? $produto?->descricao
                    ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $produtosResumo = $produtos
            ->take(3)
            ->implode(', ');

        if ($produtos->count() > 3) {
            $produtosResumo .= ' +'
                . ($produtos->count() - 3);
        }

        $veiculo = $romaneio?->veiculoExecutante
            ?? $romaneio?->veiculo
            ?? $entrega->veiculo
            ?? null;

        $motorista = $romaneio?->motoristaExecutante
            ?? $romaneio?->motorista
            ?? $entrega->motorista
            ?? null;

        $latitudeEntrega = $this->normalizarCoordenada(
            $entrega->latitude_entrega,
            -90,
            90
        );

        $longitudeEntrega = $this->normalizarCoordenada(
            $entrega->longitude_entrega,
            -180,
            180
        );

        $coordenadaConfirmada =
            (bool) $entrega->coordenada_confirmada
            && $latitudeEntrega !== null
            && $longitudeEntrega !== null;

        $metadadosRota = $this->metadadosRota[$entrega->id]
            ?? [];
        $fusoHorario = (string) config('app.timezone');
        $liberadoEm = $romaneio?->liberado_em
            ? $romaneio->liberado_em
                ->timezone($fusoHorario)
                ->format('d/m/Y H:i')
            : null;
        $saidaEm = $romaneio?->data_saida
            ? $romaneio->data_saida
                ->timezone($fusoHorario)
                ->format('d/m/Y H:i')
            : null;

        return [
            'id' => (int) $entrega->id,
            'codigo' => $entrega->codigo_entrega
                ?: 'ENT-' . $entrega->id,
            'data_chave' => $dataPlanejada->toDateString(),
            'ordem_rota' => $entrega->ordem_rota !== null
                ? (int) $entrega->ordem_rota
                : null,
            'ordem_inteligente' => $metadadosRota[
                'ordem_inteligente'
            ] ?? $entrega->ordem_rota,
            'data_formatada' => $dataPlanejada->format('d/m/Y'),
            'dia_semana' => $this->diaSemana($dataPlanejada),
            'periodo' => $entrega->periodo_entrega
                ?: 'Não informado',
            'periodo_rotulo' => $this->rotuloPeriodo(
                $entrega->periodo_entrega
            ),
            'periodo_ordem' => $this->ordemPeriodo(
                $entrega->periodo_entrega
            ),
            'cliente' => $cliente?->nome
                ?? $cliente?->razao_social
                ?? $entrega->responsavel_recebimento
                ?? 'Cliente não informado',
            'telefone' => trim(
                (string) $entrega->telefone_recebimento
            ) !== ''
                ? trim(
                    (string) $entrega->telefone_recebimento
                )
                : 'Não informado',
            'responsavel_recebimento' => trim(
                (string) ($entrega->responsavel_recebimento ?? '')
            ) !== ''
                ? trim((string) $entrega->responsavel_recebimento)
                : 'Não informado',
            'observacao_entrega' => trim(
                (string) ($entrega->observacao_entrega ?? '')
            ) !== ''
                ? trim((string) $entrega->observacao_entrega)
                : null,
            'endereco' => $endereco !== ''
                ? $endereco
                : 'Endereço não informado',
            'endereco_chave' => $this->normalizarEndereco(
                $endereco
            ),
            'latitude_entrega' => $latitudeEntrega,
            'longitude_entrega' => $longitudeEntrega,
            'coordenada_confirmada' =>
                $coordenadaConfirmada,
            'status' => $entrega->status,
            'status_rotulo' => $this->rotuloStatus(
                $entrega->status
            ),
            'encerrada' => $encerrada,
            'entrega_efetiva' => $entregaEfetiva,
            'atrasada' => $atrasada,
            'dias_atraso' => $diasAtraso,
            'apta_para_rota' => $aptaParaRota,
            'categoria_operacional' =>
                $this->categoriaOperacional(
                    $entrega->status,
                    $entregaEfetiva,
                    $atrasada,
                    $aptaParaRota
                ),
            'total_itens' => $itens->count(),
            'quantidade_prevista' => $quantidadePrevista,
            'quantidade_entregue' => $quantidadeEntregue,
            'produtos' => $produtosResumo !== ''
                ? $produtosResumo
                : 'Não informado',
            'bairro' => $localidade['bairro'],
            'cidade' => $localidade['cidade'],
            'localidade' => $localidade['rotulo'],
            'veiculo_id' => $veiculo?->id,
            'veiculo' => $veiculo?->placa
                ?? 'Não definido',
            'veiculo_modelo' => $veiculo?->modelo,
            'veiculo_tipo' => $veiculo?->tipo_veiculo
                ?? $veiculo?->tipo,
            'veiculo_carroceria' =>
                $veiculo?->tipo_carroceria,
            'veiculo_possui_munck' => (bool) (
                $veiculo?->possui_munck ?? false
            ),
            'veiculo_carroceria_aberta' => (bool) (
                $veiculo?->possui_carroceria_aberta
                ?? false
            ),
            'veiculo_carroceria_fechada' => (bool) (
                $veiculo?->possui_carroceria_fechada
                ?? false
            ),
            'motorista_id' => $motorista?->id,
            'motorista' => $motorista?->nome
                ?? $motorista?->name
                ?? 'Não definido',
            'romaneio_id' => $romaneio?->id,
            'romaneio_codigo' =>
                $romaneio?->codigo_romaneio,
            'romaneio_status' =>
                $romaneio?->status,
            'liberado_em' => $liberadoEm,
            'saida_em' => $saidaEm,
            'distancia_anterior_km' => $metadadosRota[
                'distancia_anterior_km'
            ] ?? null,
            'tempo_anterior_segundos' => $metadadosRota[
                'tempo_anterior_segundos'
            ] ?? null,
            'tempo_anterior_minutos' => isset(
                $metadadosRota['tempo_anterior_segundos']
            )
                ? round(
                    (float) $metadadosRota[
                        'tempo_anterior_segundos'
                    ] / 60,
                    1
                )
                : null,
            'fonte_distancia' => $metadadosRota[
                'fonte_distancia'
            ] ?? 'geografica',
            'peso_estimado_kg' => $metadadosRota[
                'peso_estimado_kg'
            ] ?? null,
            'volume_estimado_m3' => $metadadosRota[
                'volume_estimado_m3'
            ] ?? null,
            'concentracao_regional' => (int) (
                $metadadosRota['concentracao_regional'] ?? 1
            ),
            'concentracao_bairro' => (int) (
                $metadadosRota['concentracao_bairro'] ?? 1
            ),
            'concentracao_cep' => (int) (
                $metadadosRota['concentracao_cep'] ?? 1
            ),
            'concentracao_100m' => (int) (
                $metadadosRota['concentracao_100m'] ?? 1
            ),
            'restricoes_rota' => $metadadosRota[
                'restricoes_rota'
            ] ?? [],
            'rota_preservada' => (bool) (
                $metadadosRota['rota_preservada'] ?? false
            ),
        ];
    }

    private function montarAgenda(
        Collection $linhas,
        CarbonInterface $dataReferencia
    ): Collection {
        $referencia = CarbonImmutable::instance(
            $dataReferencia
        )->startOfDay();

        return collect(range(-7, 7))
            ->map(function (int $deslocamento) use (
                $linhas,
                $referencia
            ): array {
                $data = $referencia->addDays(
                    $deslocamento
                );

                $entregasDia = $linhas
                    ->where(
                        'data_chave',
                        $data->toDateString()
                    )
                    ->values();

                return [
                    'data_chave' => $data->toDateString(),
                    'data_formatada' => $data->format('d/m/Y'),
                    'dia_semana' => $this->diaSemana($data),
                    'deslocamento' => $deslocamento,
                    'periodos' => [
                        'manha' => $entregasDia
                            ->where('periodo', 'manha')
                            ->count(),
                        'tarde' => $entregasDia
                            ->where('periodo', 'tarde')
                            ->count(),
                        'comercial' => $entregasDia
                            ->where('periodo', 'comercial')
                            ->count(),
                    ],
                    'resumo' => $this->resumir(
                        $entregasDia
                    ),
                ];
            });
    }

    private function aplicarOrdemGlobalMapa(
        Collection $linhas,
        array $empresa
    ): Collection {
        if ($linhas->isEmpty()) {
            return $linhas;
        }

        $latitudeAtual = $empresa['latitude'] ?? null;
        $longitudeAtual = $empresa['longitude'] ?? null;
        $cepAtual = null;
        $bairroAtual = null;
        $ordem = 1;

        $pendentes = $linhas->mapWithKeys(
            function (array $linha): array {
                $linha['cep_mapa'] = $this->extrairCepRota(
                    (string) ($linha['endereco'] ?? '')
                );
                $linha['bairro_mapa'] = $this->normalizarTextoRota(
                    (string) ($linha['bairro'] ?? '')
                );
                $linha['rota_mapa'] = implode(':', [
                    (int) ($linha['veiculo_id'] ?? 0),
                    (int) ($linha['motorista_id'] ?? 0),
                ]);

                return [(int) $linha['id'] => $linha];
            }
        );

        $ordenadas = collect();

        while ($pendentes->isNotEmpty()) {
            $menoresOrdensPorRota = $pendentes
                ->filter(
                    fn (array $linha): bool =>
                        (bool) ($linha['rota_preservada'] ?? false)
                        && ($linha['ordem_rota'] ?? null) !== null
                )
                ->groupBy('rota_mapa')
                ->map(
                    fn (Collection $rota): int =>
                        (int) $rota->min('ordem_rota')
                );

            $candidatos = $pendentes
                ->map(function (array $linha) use (
                    $latitudeAtual,
                    $longitudeAtual,
                    $cepAtual,
                    $bairroAtual,
                    $menoresOrdensPorRota
                ): array {
                    $coordenadaValida =
                        (bool) ($linha['coordenada_confirmada'] ?? false)
                        && ($linha['latitude_entrega'] ?? null) !== null
                        && ($linha['longitude_entrega'] ?? null) !== null;

                    $distanciaGeografica = $coordenadaValida
                        && $latitudeAtual !== null
                        && $longitudeAtual !== null
                            ? $this->distanciaKm(
                                (float) $latitudeAtual,
                                (float) $longitudeAtual,
                                (float) $linha['latitude_entrega'],
                                (float) $linha['longitude_entrega']
                            )
                            : 999999.0;
                    $trechoValhalla = $coordenadaValida
                        && $latitudeAtual !== null
                        && $longitudeAtual !== null
                            ? $this->trechoValhalla(
                                (float) $latitudeAtual,
                                (float) $longitudeAtual,
                                (float) $linha['latitude_entrega'],
                                (float) $linha['longitude_entrega']
                            )
                            : null;
                    $distancia = $trechoValhalla['distancia_km']
                        ?? $distanciaGeografica;

                    $menorOrdemRota = $menoresOrdensPorRota->get(
                        $linha['rota_mapa']
                    );

                    $linha['distancia_mapa_km'] = $distancia;
                    $linha['tempo_mapa_segundos'] =
                        $trechoValhalla['tempo_segundos'] ?? null;
                    $linha['tempo_mapa_minutos'] = isset(
                        $trechoValhalla['tempo_segundos']
                    )
                        ? round(
                            (float) $trechoValhalla[
                                'tempo_segundos'
                            ] / 60,
                            1
                        )
                        : null;
                    $linha['fonte_distancia_mapa'] = $trechoValhalla
                        ? 'valhalla'
                        : 'geografica';
                    $linha['proxima_100m_mapa'] =
                        $distanciaGeografica
                            <= self::RAIO_PROXIMIDADE_KM;
                    $linha['mesmo_cep_mapa'] = $cepAtual !== null
                        && $linha['cep_mapa'] !== ''
                        && $linha['cep_mapa'] === $cepAtual;
                    $linha['mesmo_bairro_mapa'] = $bairroAtual !== null
                        && $linha['bairro_mapa'] !== ''
                        && $linha['bairro_mapa'] === $bairroAtual;
                    $linha['bloqueada_ordem_rota'] =
                        $menorOrdemRota !== null
                        && (int) ($linha['ordem_rota'] ?? PHP_INT_MAX)
                            !== (int) $menorOrdemRota;

                    return $linha;
                })
                ->sort(function (array $a, array $b): int {
                    return [
                        $a['bloqueada_ordem_rota'] ? 1 : 0,
                        $a['atrasada'] ? 0 : 1,
                        -(int) ($a['dias_atraso'] ?? 0),
                        $a['data_chave'],
                        (int) $a['periodo_ordem'],
                        $a['proxima_100m_mapa'] ? 0 : 1,
                        $a['mesmo_cep_mapa'] ? 0 : 1,
                        $a['mesmo_bairro_mapa'] ? 0 : 1,
                        -(int) ($a['concentracao_regional'] ?? 1),
                        round((float) $a['distancia_mapa_km'], 4),
                        $a['status'] === 'No_destino' ? 0 : 1,
                        (int) ($a['ordem_rota'] ?? PHP_INT_MAX),
                        (int) $a['id'],
                    ] <=> [
                        $b['bloqueada_ordem_rota'] ? 1 : 0,
                        $b['atrasada'] ? 0 : 1,
                        -(int) ($b['dias_atraso'] ?? 0),
                        $b['data_chave'],
                        (int) $b['periodo_ordem'],
                        $b['proxima_100m_mapa'] ? 0 : 1,
                        $b['mesmo_cep_mapa'] ? 0 : 1,
                        $b['mesmo_bairro_mapa'] ? 0 : 1,
                        -(int) ($b['concentracao_regional'] ?? 1),
                        round((float) $b['distancia_mapa_km'], 4),
                        $b['status'] === 'No_destino' ? 0 : 1,
                        (int) ($b['ordem_rota'] ?? PHP_INT_MAX),
                        (int) $b['id'],
                    ];
                });

            $selecionada = $candidatos->first();

            if (! $selecionada) {
                break;
            }

            $selecionada['ordem_mapa'] = $ordem++;
            $selecionada['distancia_mapa_km'] =
                $selecionada['distancia_mapa_km'] < 999999
                    ? round($selecionada['distancia_mapa_km'], 2)
                    : null;

            if (
                (bool) ($selecionada['coordenada_confirmada'] ?? false)
                && ($selecionada['latitude_entrega'] ?? null) !== null
                && ($selecionada['longitude_entrega'] ?? null) !== null
            ) {
                $latitudeAtual = (float) $selecionada['latitude_entrega'];
                $longitudeAtual = (float) $selecionada['longitude_entrega'];
            }

            $cepAtual = $selecionada['cep_mapa'] !== ''
                ? $selecionada['cep_mapa']
                : null;
            $bairroAtual = $selecionada['bairro_mapa'] !== ''
                ? $selecionada['bairro_mapa']
                : null;

            unset(
                $selecionada['cep_mapa'],
                $selecionada['bairro_mapa'],
                $selecionada['rota_mapa'],
                $selecionada['proxima_100m_mapa'],
                $selecionada['mesmo_cep_mapa'],
                $selecionada['mesmo_bairro_mapa'],
                $selecionada['bloqueada_ordem_rota']
            );

            $ordenadas->push($selecionada);
            $pendentes->forget((int) $selecionada['id']);
        }

        $porId = $ordenadas->keyBy('id');

        return $linhas
            ->map(
                fn (array $linha): array =>
                    $porId->get((int) $linha['id'], $linha)
            )
            ->values();
    }

    private function resumir(Collection $linhas): array
    {
        return [
            'total' => $linhas->count(),
            'concluidas' => $linhas
                ->where('encerrada', true)
                ->count(),
            'efetivas' => $linhas
                ->where('entrega_efetiva', true)
                ->count(),
            'abertas' => $linhas
                ->where('encerrada', false)
                ->count(),
            'atrasadas' => $linhas
                ->where('atrasada', true)
                ->count(),
            'quantidade_prevista' => $linhas->sum(
                'quantidade_prevista'
            ),
        ];
    }

    private function montarIndicadores(Collection $linhas): array
    {
        $total = $linhas->count();
        $carregadas = $linhas->where('status', 'Carregada')->count();
        $liberadas = $linhas->where('status', 'Liberada')->count();
        $emRota = $linhas->where('status', 'Em_rota')->count();
        $noDestino = $linhas->where('status', 'No_destino')->count();
        $atrasadas = $linhas->where('atrasada', true)->count();

        $veiculosAtivos = $linhas
            ->filter(
                fn (array $linha): bool =>
                    ($linha['veiculo'] ?? 'Não definido') !== 'Não definido'
            )
            ->pluck('veiculo')
            ->unique()
            ->count();

        return [
            'total' => $total,
            'carregadas' => $carregadas,
            'liberadas' => $liberadas,
            'em_rota' => $emRota,
            'no_destino' => $noDestino,
            'atrasadas' => $atrasadas,
            'veiculos_ativos' => $veiculosAtivos,
            'quantidade_prevista' => $linhas->sum(
                'quantidade_prevista'
            ),

            // Compatibilidade temporária com componentes antigos da view.
            'concluidas' => 0,
            'em_andamento' => $total,
            'eficiencia' => $this->percentual(
                $noDestino,
                $total
            ),
        ];
    }

    private function montarRankingClientes(
        Collection $linhas
    ): Collection {
        $ranking = $linhas
            ->groupBy('cliente')
            ->map(function (
                Collection $entregas,
                string $cliente
            ): array {
                return [
                    'cliente' => $cliente,
                    'total' => $entregas->count(),
                    'concluidas' => $entregas
                        ->where('entrega_efetiva', true)
                        ->count(),
                    'quantidade_prevista' => $entregas->sum(
                        'quantidade_prevista'
                    ),
                ];
            })
            ->sortByDesc('total')
            ->take(6)
            ->values();

        $maiorTotal = max(
            1,
            (int) $ranking->max('total')
        );

        return $ranking
            ->map(function (array $item) use ($maiorTotal): array {
                $item['percentual_barra'] = $this->percentual(
                    $item['total'],
                    $maiorTotal
                );

                return $item;
            });
    }

    private function montarDistribuicaoPeriodos(
        Collection $linhas
    ): Collection {
        $periodos = collect([
            'manha' => 'Manhã',
            'comercial' => 'Comercial',
            'tarde' => 'Tarde',
            'Não informado' => 'Não informado',
        ]);

        return $periodos
            ->map(function (
                string $rotulo,
                string $periodo
            ) use ($linhas): array {
                $entregas = $linhas
                    ->where('periodo', $periodo);

                return [
                    'chave' => $periodo,
                    'rotulo' => $rotulo,
                    'total' => $entregas->count(),
                    'em_rota' => $entregas
                        ->where('status', 'Em_rota')
                        ->count(),
                    'no_destino' => $entregas
                        ->where('status', 'No_destino')
                        ->count(),
                    'atrasadas' => $entregas
                        ->where('atrasada', true)
                        ->count(),
                ];
            })
            ->filter(
                fn (array $periodo): bool =>
                    $periodo['total'] > 0
            )
            ->values();
    }

    private function montarDistribuicaoStatus(
        Collection $linhas
    ): Collection {
        $categorias = collect([
            'em_rota' => [
                'rotulo' => 'Em rota',
                'cor' => '#fd7e14',
            ],
            'no_destino' => [
                'rotulo' => 'No destino',
                'cor' => '#0d6efd',
            ],
            'atrasadas' => [
                'rotulo' => 'Fora da janela',
                'cor' => '#dc3545',
            ],
        ]);

        $total = max(1, $linhas->count());

        return $categorias
            ->map(function (
                array $configuracao,
                string $categoria
            ) use ($linhas, $total): array {
                $quantidade = match ($categoria) {
                    'em_rota' => $linhas
                        ->where('status', 'Em_rota')
                        ->count(),
                    'no_destino' => $linhas
                        ->where('status', 'No_destino')
                        ->count(),
                    'atrasadas' => $linhas
                        ->where('atrasada', true)
                        ->count(),
                    default => 0,
                };

                return [
                    'chave' => $categoria,
                    'rotulo' => $configuracao['rotulo'],
                    'cor' => $configuracao['cor'],
                    'quantidade' => $quantidade,
                    'percentual' => $this->percentual(
                        $quantidade,
                        $total
                    ),
                ];
            })
            ->values();
    }

    private function montarConcentracaoLocalidades(
        Collection $linhas
    ): Collection {
        $concentracao = $linhas
            ->groupBy('localidade')
            ->map(function (
                Collection $entregas,
                string $localidade
            ): array {
                return [
                    'localidade' => $localidade,
                    'total' => $entregas->count(),
                    'atrasadas' => $entregas
                        ->where('atrasada', true)
                        ->count(),
                    'quantidade_prevista' => $entregas->sum(
                        'quantidade_prevista'
                    ),
                ];
            })
            ->sortByDesc('total')
            ->take(6)
            ->values();

        $maiorTotal = max(
            1,
            (int) $concentracao->max('total')
        );

        return $concentracao
            ->map(function (array $item) use ($maiorTotal): array {
                $item['intensidade'] = $this->percentual(
                    $item['total'],
                    $maiorTotal
                );

                return $item;
            });
    }

    private function montarGraficoDiario(
        Collection $agenda
    ): Collection {
        return $agenda
            ->map(function (array $dia): array {
                $total = $dia['resumo']['total'];
                $concluidas = $dia['resumo']['efetivas'];

                return [
                    'rotulo' => $dia['deslocamento'] === 0
                        ? 'D+0'
                        : 'D'
                            . ($dia['deslocamento'] > 0 ? '+' : '')
                            . $dia['deslocamento'],
                    'data' => $dia['data_formatada'],
                    'total' => $total,
                    'concluidas' => $concluidas,
                    'eficiencia' => $this->percentual(
                        $concluidas,
                        $total
                    ),
                ];
            })
            ->values();
    }

    private function montarAlertasOperacionais(
        Collection $linhas,
        Collection $oportunidades
    ): Collection {
        return collect([
            [
                'tipo' => 'danger',
                'icone' => 'bi-clock-history',
                'quantidade' => $linhas
                    ->where('atrasada', true)
                    ->count(),
                'titulo' => 'Fora da janela prevista',
                'descricao' => 'Entregas ativas com data prevista vencida.',
            ],
            [
                'tipo' => 'warning',
                'icone' => 'bi-card-checklist',
                'quantidade' => $linhas
                    ->whereNull('romaneio_codigo')
                    ->count(),
                'titulo' => 'Sem romaneio',
                'descricao' => 'Inconsistência para entrega em operação externa.',
            ],
            [
                'tipo' => 'warning',
                'icone' => 'bi-truck',
                'quantidade' => $linhas
                    ->where('veiculo', 'Não definido')
                    ->count(),
                'titulo' => 'Sem veículo',
                'descricao' => 'Entrega em rota sem veículo identificado.',
            ],
            [
                'tipo' => 'warning',
                'icone' => 'bi-person-x',
                'quantidade' => $linhas
                    ->where('motorista', 'Não definido')
                    ->count(),
                'titulo' => 'Sem motorista',
                'descricao' => 'Entrega em rota sem motorista identificado.',
            ],
            [
                'tipo' => 'info',
                'icone' => 'bi-geo-alt',
                'quantidade' => $linhas
                    ->where('coordenada_confirmada', false)
                    ->count(),
                'titulo' => 'Sem coordenada confirmada',
                'descricao' => 'Não podem ser posicionadas com precisão no mapa.',
            ],
        ]);
    }

    private function resolverLocalidade(
        string $endereco
    ): array {
        if (trim($endereco) === '') {
            return [
                'bairro' => 'Não identificado',
                'cidade' => 'Não identificada',
                'rotulo' => 'Localidade não identificada',
            ];
        }

        $partes = collect(
            explode(',', $endereco)
        )
            ->map(
                fn (string $parte): string => trim($parte)
            )
            ->filter()
            ->values();

        $indiceCidade = $partes->search(
            fn (string $parte): bool =>
                preg_match(
                    '/\s-\s[A-Z]{2}\b/i',
                    $parte
                ) === 1
        );

        if ($indiceCidade !== false) {
            $cidadeComEstado = $partes->get(
                $indiceCidade
            );

            $cidade = trim(
                preg_replace(
                    '/\s-\s[A-Z]{2}\b.*$/i',
                    '',
                    $cidadeComEstado
                ) ?? $cidadeComEstado
            );

            $bairro = $indiceCidade > 0
                ? $partes->get($indiceCidade - 1)
                : 'Não identificado';

            return [
                'bairro' => $bairro,
                'cidade' => $cidade,
                'rotulo' => $bairro . ' / ' . $cidade,
            ];
        }

        $ultimasPartes = $partes
            ->reject(
                fn (string $parte): bool =>
                    Str::contains(
                        Str::lower($parte),
                        'cep'
                    )
            )
            ->take(-2)
            ->values();

        $bairro = $ultimasPartes->first()
            ?? 'Não identificado';

        $cidade = $ultimasPartes->get(1)
            ?? 'Não identificada';

        return [
            'bairro' => $bairro,
            'cidade' => $cidade,
            'rotulo' => $bairro . ' / ' . $cidade,
        ];
    }

    private function categoriaOperacional(
        ?string $status,
        bool $entregaEfetiva,
        bool $atrasada,
        bool $aptaParaRota
    ): string {
        if ($atrasada) {
            return 'atrasadas';
        }

        return match ($status) {
            'No_destino' => 'no_destino',
            'Em_rota' => 'em_rota',
            default => 'aguardando',
        };
    }

    private function percentual(
        int|float $parte,
        int|float $total
    ): float {
        if ((float) $total <= 0) {
            return 0;
        }

        return round(
            ((float) $parte / (float) $total) * 100,
            1
        );
    }

    private function normalizarCoordenada(
        mixed $valor,
        float $minimo,
        float $maximo
    ): ?float {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }

        $valorNormalizado = str_replace(
            ',',
            '.',
            trim((string) $valor)
        );

        if (! is_numeric($valorNormalizado)) {
            return null;
        }

        $coordenada = (float) $valorNormalizado;

        if (
            ! is_finite($coordenada)
            || $coordenada < $minimo
            || $coordenada > $maximo
        ) {
            return null;
        }

        return $coordenada;
    }

    private function normalizarEndereco(string $endereco): string
    {
        $endereco = Str::lower(
            Str::ascii(
                trim($endereco)
            )
        );

        return preg_replace(
            '/[^a-z0-9]+/',
            '',
            $endereco
        ) ?? '';
    }

    private function rotuloPeriodo(?string $periodo): string
    {
        return match ($periodo) {
            'manha' => 'Manhã',
            'tarde' => 'Tarde',
            'comercial' => 'Comercial',
            default => 'Não informado',
        };
    }

    private function ordemPeriodo(?string $periodo): int
    {
        return match ($periodo) {
            'manha' => 1,
            'comercial' => 2,
            'tarde' => 3,
            default => 4,
        };
    }

    private function rotuloStatus(?string $status): string
    {
        if (empty($status)) {
            return 'Não informado';
        }

        return Str::of($status)
            ->replace('_', ' ')
            ->lower()
            ->ucfirst()
            ->toString();
    }

    private function diaSemana(CarbonInterface $data): string
    {
        return match ((int) $data->format('N')) {
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo',
        };
    }
}