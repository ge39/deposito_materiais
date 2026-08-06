<?php

namespace App\Services\Expedicao;

use App\Models\Entrega;
use App\Models\Romaneio;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EntregaInteligenteService
{
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

    public function montarDashboard(
        CarbonInterface $dataReferencia
    ): array {
        $referencia = CarbonImmutable::instance(
            $dataReferencia
        )->startOfDay();

        $inicioJanela = $referencia->subDays(7);
        $fimJanela = $referencia->addDays(7);

        $entregas = $this->consultarEntregas(
            $inicioJanela,
            $fimJanela
        );

        $romaneiosAtivos = $this
            ->consultarRomaneiosAtivos($entregas);

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
            'dataReferencia' => $referencia,
            'inicioJanela' => $inicioJanela,
            'fimJanela' => $fimJanela,
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
                'itens.vendaItem.produto',
                'itens.itemOrcamento.produto',
            ])
            ->where(
                'tipo_entrega',
                'entrega'
            )
            ->where(
                'status',
                '!=',
                'Cancelada'
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

        $atrasada = ! $encerrada
            && $dataPlanejada->lessThan($dataReferencia);

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

        $veiculo = $romaneio?->veiculo
            ?? $entrega->veiculo
            ?? null;

        $motorista = $romaneio?->motorista
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

        return [
            'id' => (int) $entrega->id,
            'codigo' => $entrega->codigo_entrega
                ?: 'ENT-' . $entrega->id,
            'data_chave' => $dataPlanejada->toDateString(),
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
            'telefone' => $entrega->telefone_recebimento
                ?? $cliente?->telefone
                ?? $cliente?->celular
                ?? 'Não informado',
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
            'veiculo' => $veiculo?->placa
                ?? 'Não definido',
            'veiculo_modelo' => $veiculo?->modelo,
            'motorista' => $motorista?->nome
                ?? $motorista?->name
                ?? 'Não definido',
            'romaneio_codigo' =>
                $romaneio?->codigo_romaneio,
            'romaneio_status' =>
                $romaneio?->status,
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
        $concluidas = $linhas
            ->where('entrega_efetiva', true)
            ->count();

        return [
            'total' => $total,
            'concluidas' => $concluidas,
            'em_andamento' => $linhas
                ->where('encerrada', false)
                ->where('apta_para_rota', true)
                ->count(),
            'atrasadas' => $linhas
                ->where('atrasada', true)
                ->count(),
            'eficiencia' => $this->percentual(
                $concluidas,
                $total
            ),
            'quantidade_prevista' => $linhas->sum(
                'quantidade_prevista'
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
                    'concluidas' => $entregas
                        ->where('entrega_efetiva', true)
                        ->count(),
                    'em_andamento' => $entregas
                        ->where('encerrada', false)
                        ->where('apta_para_rota', true)
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
            'concluidas' => [
                'rotulo' => 'Concluídas',
                'cor' => '#198754',
            ],
            'em_andamento' => [
                'rotulo' => 'Em andamento',
                'cor' => '#fd7e14',
            ],
            'atrasadas' => [
                'rotulo' => 'Atrasadas',
                'cor' => '#dc3545',
            ],
            'aguardando' => [
                'rotulo' => 'Aguardando operação',
                'cor' => '#0d6efd',
            ],
            'ocorrencias' => [
                'rotulo' => 'Encerradas sem efetivação',
                'cor' => '#6c757d',
            ],
        ]);

        $total = max(
            1,
            $linhas->count()
        );

        return $categorias
            ->map(function (
                array $configuracao,
                string $categoria
            ) use ($linhas, $total): array {
                $quantidade = $linhas
                    ->where(
                        'categoria_operacional',
                        $categoria
                    )
                    ->count();

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
        $abertas = $linhas->where(
            'encerrada',
            false
        );

        return collect([
            [
                'tipo' => 'danger',
                'icone' => 'bi-exclamation-lg',
                'quantidade' => $linhas
                    ->where('atrasada', true)
                    ->count(),
                'titulo' => 'Entregas atrasadas',
                'descricao' => 'Requerem decisão operacional.',
            ],
            [
                'tipo' => 'warning',
                'icone' => 'bi-card-checklist',
                'quantidade' => $abertas
                    ->whereNull('romaneio_codigo')
                    ->count(),
                'titulo' => 'Sem romaneio ativo',
                'descricao' => 'Aguardam montagem operacional.',
            ],
            [
                'tipo' => 'warning',
                'icone' => 'bi-truck',
                'quantidade' => $abertas
                    ->whereNotNull('romaneio_codigo')
                    ->where('veiculo', 'Não definido')
                    ->count(),
                'titulo' => 'Sem veículo definido',
                'descricao' => 'Necessitam planejamento de frota.',
            ],
            [
                'tipo' => 'info',
                'icone' => 'bi-shield-check',
                'quantidade' => $linhas
                    ->where(
                        'romaneio_status',
                        'Aguardando_liberacao'
                    )
                    ->count(),
                'titulo' => 'Aguardando liberação',
                'descricao' => 'Carga conferida para decisão final.',
            ],
            [
                'tipo' => 'primary',
                'icone' => 'bi-lightbulb',
                'quantidade' => $oportunidades->count(),
                'titulo' => 'Possíveis antecipações',
                'descricao' => 'Confirmar previamente com o cliente.',
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
        if ($entregaEfetiva) {
            return 'concluidas';
        }

        if (
            in_array(
                $status,
                [
                    'Nao_entregue',
                    'Entregue_parcial',
                    'Recusada',
                    'Devolvida',
                ],
                true
            )
        ) {
            return 'ocorrencias';
        }

        if ($atrasada) {
            return 'atrasadas';
        }

        if ($aptaParaRota) {
            return 'em_andamento';
        }

        return 'aguardando';
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