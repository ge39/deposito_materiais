<?php

namespace App\Http\Controllers;

use App\Models\ComissaoRegra;
use App\Models\Funcionario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ComissaoRegraController extends Controller
{
    public function index(): View
    {
        $regras = ComissaoRegra::query()
            ->with([
                'funcionario',
                'categoria',
                'produto',
            ])
            ->orderByDesc('ativo')
            ->orderBy('funcionario_id')
            ->orderBy('escopo')
            ->orderByDesc('id')
            ->paginate(25);

        return view(
            'comissoes.regras.index',
            compact('regras')
        );
    }

    public function create(): View
    {
        return view(
            'comissoes.regras.create',
            $this->dadosFormulario()
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validar($request);

        $this->validarConflito($dados);

        ComissaoRegra::create($dados);

        return redirect()
            ->route('comissoes.regras.index')
            ->with(
                'success',
                'Regra de comissão cadastrada com sucesso.'
            );
    }

    public function edit(ComissaoRegra $regra): View
    {
        return view(
            'comissoes.regras.edit',
            array_merge(
                $this->dadosFormulario(),
                compact('regra')
            )
        );
    }

    public function update(
        Request $request,
        ComissaoRegra $regra
    ): RedirectResponse {
        $dados = $this->validar($request);

        $this->validarConflito(
            $dados,
            $regra->id
        );

        $regra->update($dados);

        return redirect()
            ->route('comissoes.regras.index')
            ->with(
                'success',
                'Regra de comissão atualizada com sucesso.'
            );
    }

    public function destroy(
        ComissaoRegra $regra
    ): RedirectResponse {
        $possuiHistorico = DB::table('comissoes_vendas')
            ->where('regra_comissao_id', $regra->id)
            ->exists();

        if ($possuiHistorico) {
            $regra->update([
                'ativo' => false,
            ]);

            return redirect()
                ->route('comissoes.regras.index')
                ->with(
                    'success',
                    'A regra possui histórico e foi desativada.'
                );
        }

        $regra->delete();

        return redirect()
            ->route('comissoes.regras.index')
            ->with(
                'success',
                'Regra de comissão excluída com sucesso.'
            );
    }

    private function dadosFormulario(): array
    {
        $vendedores = Funcionario::query()
            ->join(
                'users',
                'users.funcionario_id',
                '=',
                'funcionarios.id'
            )
            ->where(
                'users.nivel_acesso',
                'vendedor'
            )
            ->where(
                'users.ativo',
                1
            )
            ->select('funcionarios.*')
            ->distinct()
            ->orderBy('funcionarios.nome')
            ->get();

        $categorias = DB::table('categorias')
            ->orderBy('nome')
            ->get();

        $produtos = DB::table('produtos')
            ->orderBy('nome')
            ->get();

        return compact(
            'vendedores',
            'categorias',
            'produtos'
        );
    }

    private function validar(Request $request): array
    {
        $dados = $request->validate([
            'funcionario_id' => [
                'required',
                'integer',
                'exists:funcionarios,id',
            ],

            'escopo' => [
                'required',
                Rule::in([
                    'geral',
                    'categoria',
                    'produto',
                ]),
            ],

            'categoria_id' => [
                'nullable',
                'integer',
                'exists:categorias,id',
            ],

            'produto_id' => [
                'nullable',
                'integer',
                'exists:produtos,id',
            ],

            'usar_percentual_produto' => [
                'nullable',
                'boolean',
            ],

            'percentual' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'vigencia_inicio' => [
                'nullable',
                'date',
            ],

            'vigencia_fim' => [
                'nullable',
                'date',
                'after_or_equal:vigencia_inicio',
            ],

            'ativo' => [
                'nullable',
                'boolean',
            ],

            'observacao' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $dados['ativo'] =
            $request->boolean('ativo');

        $dados['usar_percentual_produto'] =
            $request->boolean(
                'usar_percentual_produto'
            );

        if ($dados['escopo'] === 'geral') {
            $dados['categoria_id'] = null;
            $dados['produto_id'] = null;
            $dados['usar_percentual_produto'] = false;
        }

        if ($dados['escopo'] === 'categoria') {
            $dados['produto_id'] = null;
            $dados['usar_percentual_produto'] = false;

            if (empty($dados['categoria_id'])) {
                throw ValidationException::withMessages([
                    'categoria_id' =>
                        'Selecione a categoria da regra.',
                ]);
            }
        }

        if ($dados['escopo'] === 'produto') {
            $dados['categoria_id'] = null;

            if (empty($dados['produto_id'])) {
                throw ValidationException::withMessages([
                    'produto_id' =>
                        'Selecione o produto da regra.',
                ]);
            }
        }

        if (
            $dados['escopo'] !== 'produto'
            || !$dados['usar_percentual_produto']
        ) {
            if (
                !isset($dados['percentual'])
                || $dados['percentual'] === ''
            ) {
                throw ValidationException::withMessages([
                    'percentual' =>
                        'Informe o percentual da comissão.',
                ]);
            }
        }

        if (
            $dados['escopo'] === 'produto'
            && $dados['usar_percentual_produto']
        ) {
            $dados['percentual'] = null;
        }

        return $dados;
    }

    private function validarConflito(
        array $dados,
        ?int $ignorarId = null
    ): void {
        if (!$dados['ativo']) {
            return;
        }

        $query = ComissaoRegra::query()
            ->where(
                'funcionario_id',
                $dados['funcionario_id']
            )
            ->where(
                'escopo',
                $dados['escopo']
            )
            ->where('ativo', true);

        if ($ignorarId !== null) {
            $query->whereKeyNot($ignorarId);
        }

        if ($dados['escopo'] === 'categoria') {
            $query->where(
                'categoria_id',
                $dados['categoria_id']
            );
        }

        if ($dados['escopo'] === 'produto') {
            $query->where(
                'produto_id',
                $dados['produto_id']
            );
        }

        $inicio =
            $dados['vigencia_inicio'] ?? null;

        $fim =
            $dados['vigencia_fim'] ?? null;

        $query->where(function ($q) use ($fim) {
            if ($fim === null) {
                $q->whereNull('vigencia_inicio')
                    ->orWhereNotNull('vigencia_inicio');

                return;
            }

            $q->whereNull('vigencia_inicio')
                ->orWhere(
                    'vigencia_inicio',
                    '<=',
                    $fim
                );
        });

        $query->where(function ($q) use ($inicio) {
            if ($inicio === null) {
                $q->whereNull('vigencia_fim')
                    ->orWhereNotNull('vigencia_fim');

                return;
            }

            $q->whereNull('vigencia_fim')
                ->orWhere(
                    'vigencia_fim',
                    '>=',
                    $inicio
                );
        });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'escopo' =>
                    'Já existe uma regra ativa conflitante para este vendedor e período.',
            ]);
        }
    }
}