<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            if (! in_array(auth()->user()->nivel_acesso, ['admin', 'gerente'])) {
                abort(403, 'Acesso negado!');
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $busca = trim((string) $request->input('busca'));
        $status = (string) $request->input('status', 'todos');

        $registros = Categoria::query()
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($subquery) use ($busca) {
                    $subquery
                        ->where('nome', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%");
                });
            })
            ->when(
                $status === 'ativos',
                fn ($query) => $query->where('ativo', '1')
            )
            ->when(
                $status === 'inativos',
                fn ($query) => $query->where(function ($subquery) {
                    $subquery
                        ->whereNull('ativo')
                        ->orWhere('ativo', '!=', '1');
                })
            )
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('cadastros-auxiliares.index', [
            'registros' => $registros,
            'routePrefix' => 'categorias',
            'titulo' => 'Categorias',
            'singular' => 'Categoria',
            'subtitulo' => 'Organize os produtos por categoria comercial.',
            'icone' => 'bi-tags',
            'possuiSigla' => false,
            'possuiDescricao' => true,
            'totais' => $this->totais(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $nomeNormalizado = preg_replace(
            '/\s+/u',
            ' ',
            trim((string) $request->input('nome'))
        );

        $request->merge([
            'nome' => $nomeNormalizado,
        ]);

        $dados = $request->validate(
            [
                'nome' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categorias', 'nome'),
                ],

                'descricao' => [
                    'nullable',
                    'string',
                ],

                'ativo' => [
                    'nullable',
                    Rule::in(['0', '1']),
                ],
            ],
            [
                'nome.required' =>
                    'Informe o nome da categoria.',

                'nome.unique' =>
                    'Já existe uma categoria cadastrada com esse nome.',
            ]
        );

        $categoria = new Categoria();
        $categoria->nome = $dados['nome'];

        $categoria->descricao =
            filled($dados['descricao'] ?? null)
                ? trim($dados['descricao'])
                : null;

        $categoria->ativo =
            $request->input('ativo', '0') === '1'
                ? '1'
                : '0';

        $categoria->save();

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Categoria cadastrada com sucesso!'
            );
    }

    public function update(
        Request $request,
        Categoria $categoria
    ): RedirectResponse {
        $nomeNormalizado = preg_replace(
            '/\s+/u',
            ' ',
            trim((string) $request->input('nome'))
        );

        $request->merge([
            'nome' => $nomeNormalizado,
        ]);

        $dados = $request->validate(
            [
                'nome' => [
                    'required',
                    'string',
                    'max:255',

                    Rule::unique(
                        'categorias',
                        'nome'
                    )->ignore($categoria->id),
                ],

                'descricao' => [
                    'nullable',
                    'string',
                ],

                'ativo' => [
                    'nullable',
                    Rule::in(['0', '1']),
                ],
            ],
            [
                'nome.required' =>
                    'Informe o nome da categoria.',

                'nome.unique' =>
                    'Já existe uma categoria cadastrada com esse nome.',
            ]
        );

        $categoria->nome = $dados['nome'];

        $categoria->descricao =
            filled($dados['descricao'] ?? null)
                ? trim($dados['descricao'])
                : null;

        $categoria->ativo =
            $request->input('ativo', '0') === '1'
                ? '1'
                : '0';

        $categoria->save();

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Categoria atualizada com sucesso!'
            );
    }

    public function edit(Categoria $categoria): View
    {
        return view('cadastros-auxiliares.edit', [
            'registro' => $categoria,
            'routePrefix' => 'categorias',
            'titulo' => 'Editar Categoria',
            'singular' => 'Categoria',
            'icone' => 'bi-tags',
            'possuiSigla' => false,
            'possuiDescricao' => true,
        ]);
    }

    // public function update(
    //     Request $request,
    //     Categoria $categoria
    // ): RedirectResponse {
    //     $dados = $request->validate([
    //         'nome' => [
    //             'required',
    //             'string',
    //             'max:255',
    //         ],
    //         'descricao' => [
    //             'nullable',
    //             'string',
    //         ],
    //         'ativo' => [
    //             'nullable',
    //             Rule::in(['0', '1']),
    //         ],
    //     ]);

    //     $categoria->nome = trim($dados['nome']);
    //     $categoria->descricao = filled($dados['descricao'] ?? null)
    //         ? trim($dados['descricao'])
    //         : null;
    //     $categoria->ativo = $request->input('ativo', '0') === '1'
    //         ? '1'
    //         : '0';
    //     $categoria->save();

    //     return redirect()
    //         ->route('categorias.index')
    //         ->with('success', 'Categoria atualizada com sucesso!');
    // }

    public function alternarStatus(
        Categoria $categoria
    ): RedirectResponse {
        $categoria->ativo = (string) $categoria->ativo === '1'
            ? '0'
            : '1';

        $categoria->save();

        return redirect()
            ->route('categorias.index')
            ->with(
                'success',
                'Status da categoria atualizado com sucesso!'
            );
    }

    private function totais(): array
    {
        return [
            'total' => Categoria::count(),

            'ativos' => Categoria::where(
                'ativo',
                '1'
            )->count(),

            'inativos' => Categoria::where(
                function ($query) {
                    $query
                        ->whereNull('ativo')
                        ->orWhere('ativo', '!=', '1');
                }
            )->count(),
        ];
    }
}