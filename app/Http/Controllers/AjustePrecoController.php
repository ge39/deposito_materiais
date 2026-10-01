<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Services\AjustePrecoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class AjustePrecoController extends Controller
{
    public function __construct(
        private readonly AjustePrecoService $ajustePrecoService
    ) {
        $this->middleware('auth');

        $this->middleware(
            function ($request, $next) {
                if (! in_array(
                    auth()->user()->nivel_acesso,
                    ['admin', 'gerente'],
                    true
                )) {
                    abort(
                        403,
                        'Acesso negado!'
                    );
                }

                return $next($request);
            }
        );
    }

    public function index(): View
    {
        return view(
            'ajuste-precos.index',
            $this->dadosBaseTela()
        );
    }

    public function simular(
        Request $request
    ): View {
        $dados = $this->validarDados(
            $request
        );

        $simulacao =
            $this->ajustePrecoService
                ->simular($dados);

        return view(
            'ajuste-precos.index',
            array_merge(
                $this->dadosBaseTela(),
                [
                    'simulacao'
                        => $simulacao,
                    'filtros'
                        => $dados,
                ]
            )
        );
    }

    public function aplicar(
        Request $request
    ): RedirectResponse {
        $dados = $this->validarDados(
            $request
        );

        try {
            $resultado =
                $this->ajustePrecoService
                    ->aplicar(
                        $dados,
                        auth()->id(),
                        $request->ip(),
                        $request->userAgent()
                    );

            return redirect()
                ->route(
                    'ajuste-precos.index'
                )
                ->with(
                    'success',
                    sprintf(
                        'Ajuste aplicado com sucesso. '
                        . '%d produto(s) alterado(s). '
                        . 'Operação: %s',
                        $resultado['alterados'],
                        $resultado[
                            'operacao_uuid'
                        ]
                    )
                );
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Não foi possível aplicar o ajuste: '
                    . $e->getMessage()
                );
        }
    }

    private function validarDados(
        Request $request
    ): array {
        return $request->validate([
            'escopo' => [
                'required',
                Rule::in([
                    'categoria',
                    'fornecedor',
                    'produto',
                    'selecionados',
                    'todos',
                ]),
            ],

            'categoria_id' => [
                'nullable',
                'required_if:escopo,categoria',
                'integer',
                'exists:categorias,id',
            ],

            'fornecedor_id' => [
                'nullable',
                'required_if:escopo,fornecedor',
                'integer',
                'exists:fornecedores,id',
            ],

            'produto_id' => [
                'nullable',
                'required_if:escopo,produto',
                'integer',
                'exists:produtos,id',
            ],

            'produto_ids' => [
                'nullable',
                'required_if:escopo,selecionados',
                'array',
                'min:1',
            ],

            'produto_ids.*' => [
                'integer',
                'distinct',
                'exists:produtos,id',
            ],

            'situacao' => [
                'required',
                Rule::in([
                    'ativos',
                    'inativos',
                    'todos',
                ]),
            ],

            'estoque' => [
                'required',
                Rule::in([
                    'com_estoque',
                    'sem_estoque',
                    'todos',
                ]),
            ],

            'campo_preco' => [
                'required',
                Rule::in([
                    'preco_venda',
                    'preco_venda_2',
                    'preco_venda_3',
                ]),
            ],

            'tipo_ajuste' => [
                'required',
                Rule::in([
                    'acrescimo_percentual',
                    'desconto_percentual',
                    'acrescimo_valor',
                    'desconto_valor',
                ]),
            ],

            'valor_ajuste' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);
    }

    private function dadosBaseTela(): array
    {
        return [
            'categorias' =>
                Categoria::query()
                    ->orderBy('nome')
                    ->get(),

            'fornecedores' =>
                Fornecedor::query()
                    ->where('ativo', 1)
                    ->orderBy('nome')
                    ->get(),

            'produtos' =>
                Produto::query()
                    ->where('ativo', 1)
                    ->orderBy('nome')
                    ->get([
                        'id',
                        'nome',
                        'sku',
                        'categoria_id',
                        'fornecedor_id',
                    ]),

            'simulacao' =>
                collect(),

            'filtros' => [
                'escopo'
                    => 'categoria',
                'categoria_id'
                    => null,
                'fornecedor_id'
                    => null,
                'produto_id'
                    => null,
                'produto_ids'
                    => [],
                'situacao'
                    => 'ativos',
                'estoque'
                    => 'com_estoque',
                'campo_preco'
                    => 'preco_venda',
                'tipo_ajuste'
                    => 'acrescimo_percentual',
                'valor_ajuste'
                    => '5.00',
            ],
        ];
    }
}
