<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use App\Services\BI\BiProdutosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BiProdutosController extends Controller
{
    public function __construct(
        private readonly BiProdutosService $service
    ) {
    }

    public function index(
        Request $request
    ): View {
        $this->autorizar();

        $filtros =
            $this->validarFiltros(
                $request
            );

        return view(
            'bi.produtos.index',
            [
                'dados' =>
                    $this->service
                        ->painel(
                            $filtros
                        ),

                'categorias' =>
                    $this->service
                        ->categorias(),

                'fornecedores' =>
                    $this->service
                        ->fornecedores(),

                'marcas' =>
                    $this->service
                        ->marcas(),

                'filtros' =>
                    $filtros,
            ]
        );
    }

    public function ativos(
        Request $request
    ): View {
        $filtros =
            $this->validarFiltros(
                $request
            );

        return $this->detalhe(
            'Produtos ativos',
            'Produtos ativos conforme os filtros selecionados.',
            $this->service
                ->produtosAtivos(
                    $filtros
                ),
            $filtros
        );
    }

    public function abaixoMinimo(
        Request $request
    ): View {
        $filtros =
            $this->validarFiltros(
                $request
            );

        return $this->detalhe(
            'Produtos abaixo do mínimo',
            'Produtos com estoque disponível positivo e abaixo ou igual ao mínimo.',
            $this->service
                ->abaixoMinimo(
                    $filtros
                ),
            $filtros
        );
    }

    public function valorEstoque(
        Request $request
    ): View {
        $filtros =
            $this->validarFiltros(
                $request
            );

        return $this->detalhe(
            'Composição do valor do estoque',
            'Composição financeira calculada pelos saldos disponíveis dos lotes.',
            $this->service
                ->composicaoEstoque(
                    $filtros
                ),
            $filtros
        );
    }

    public function estoqueParado(
        Request $request
    ): View {
        $filtros =
            $this->validarFiltros(
                $request
            );

        return $this->detalhe(
            'Estoque parado',
            'Produtos com estoque disponível positivo e nenhuma venda finalizada no período.',
            $this->service
                ->estoqueParadoCompleto(
                    $filtros
                ),
            $filtros
        );
    }

    public function estoqueZero(
        Request $request
    ): View {
        $this->autorizar();

        $filtros =
            $this->validarFiltros(
                $request
            );

        return view(
            'bi.produtos.estoque-zero',
            [
                'produtos' =>
                    $this->service
                        ->estoqueZeroPaginado(
                            $filtros
                        ),

                'categorias' =>
                    $this->service
                        ->categorias(),

                'filtros' =>
                    $filtros,
            ]
        );
    }

    public function estoqueZeroPdf(
        Request $request
    ) {
        $this->autorizar();

        $filtros =
            $this->validarFiltros(
                $request
            );

        $produtos =
            $this->service
                ->estoqueZeroCompleto(
                    $filtros
                );

        return Pdf::loadView(
            'bi.produtos.estoque-zero-pdf',
            [
                'produtos' =>
                    $produtos,

                'filtros' =>
                    $filtros,

                'geradoEm' =>
                    now(),
            ]
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download(
                'bi-produtos-estoque-zero-'
                . now()->format('Ymd-His')
                . '.pdf'
            );
    }

    public function estoqueZeroPlanilha(
        Request $request
    ): StreamedResponse {
        $this->autorizar();

        $filtros =
            $this->validarFiltros(
                $request
            );

        $produtos =
            $this->service
                ->estoqueZeroCompleto(
                    $filtros
                );

        $arquivo =
            'bi-produtos-estoque-zero-'
            . now()->format('Ymd-His')
            . '.csv';

        return response()->streamDownload(
            function () use ($produtos) {
                $saida =
                    fopen(
                        'php://output',
                        'w'
                    );

                fwrite(
                    $saida,
                    "\xEF\xBB\xBF"
                );

                fputcsv(
                    $saida,
                    [
                        'ID',
                        'Produto',
                        'Categoria',
                        'Estoque total',
                        'Reservado',
                        'Disponivel',
                        'Estoque minimo',
                        'Ultima venda',
                        'Dias sem venda',
                        'Promocao',
                        'Situacao',
                    ],
                    ';'
                );

                foreach (
                    $produtos as $produto
                ) {
                    fputcsv(
                        $saida,
                        [
                            $produto->id,
                            $produto->nome,
                            $produto->categoria
                                ?? '',
                            number_format(
                                (float) $produto
                                    ->estoque_total,
                                3,
                                ',',
                                '.'
                            ),
                            number_format(
                                (float) $produto
                                    ->estoque_reservado,
                                3,
                                ',',
                                '.'
                            ),
                            number_format(
                                (float) $produto
                                    ->estoque_disponivel,
                                3,
                                ',',
                                '.'
                            ),
                            number_format(
                                (float) $produto
                                    ->estoque_minimo,
                                3,
                                ',',
                                '.'
                            ),
                            $produto->ultima_venda
                                ? CarbonImmutable::parse(
                                    $produto->ultima_venda
                                )->format(
                                    'd/m/Y'
                                )
                                : 'Nunca',
                            $produto->dias_sem_venda
                                ?? 'Nunca vendeu',
                            (int) $produto
                                ->em_promocao
                                === 1
                                    ? 'Sim'
                                    : 'Nao',
                            $produto->situacao,
                        ],
                        ';'
                    );
                }

                fclose(
                    $saida
                );
            },
            $arquivo,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    private function detalhe(
        string $titulo,
        string $subtitulo,
        $produtos,
        array $filtros
    ): View {
        $this->autorizar();

        return view(
            'bi.produtos.detalhe',
            [
                'titulo' =>
                    $titulo,

                'subtitulo' =>
                    $subtitulo,

                'produtos' =>
                    $produtos,

                'filtros' =>
                    $filtros,
            ]
        );
    }

    private function validarFiltros(
        Request $request
    ): array {
        $dados =
            $request->validate(
                [
                    'data_inicio' => [
                        'nullable',
                        'date_format:Y-m-d',
                    ],

                    'data_fim' => [
                        'nullable',
                        'date_format:Y-m-d',
                    ],

                    'q' => [
                        'nullable',
                        'string',
                        'max:150',
                    ],

                    'categoria_id' => [
                        'nullable',
                        'integer',
                        'exists:categorias,id',
                    ],

                    'fornecedor_id' => [
                        'nullable',
                        'integer',
                        'exists:fornecedores,id',
                    ],

                    'marca_id' => [
                        'nullable',
                        'integer',
                        'exists:marcas,id',
                    ],

                    'promocao' => [
                        'nullable',
                        'in:0,1',
                    ],

                    'ordenacao' => [
                        'nullable',
                        'in:nome,ultima_venda,estoque_minimo,vendido',
                    ],
                ]
            );

        $fim =
            ! empty(
                $dados['data_fim']
            )
                ? CarbonImmutable::parse(
                    $dados['data_fim']
                )
                : CarbonImmutable::today();

        $inicio =
            ! empty(
                $dados['data_inicio']
            )
                ? CarbonImmutable::parse(
                    $dados['data_inicio']
                )
                : $fim->subDays(89);

        abort_if(
            $inicio->gt($fim),
            422,
            'A data inicial não pode ser posterior à data final.'
        );

        $dados['data_inicio'] =
            $inicio->format(
                'Y-m-d'
            );

        $dados['data_fim'] =
            $fim->format(
                'Y-m-d'
            );

        return $dados;
    }

    private function autorizar(): void
    {
        abort_unless(
            auth()->check()
            &&
            in_array(
                auth()->user()->nivel_acesso,
                [
                    'admin',
                    'gerente',
                ],
                true
            ),
            403
        );
    }
}