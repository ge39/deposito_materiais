<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarcaController extends Controller
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

        $registros = Marca::query()
            ->when(
                $busca !== '',
                fn ($query) => $query->where(
                    'nome',
                    'like',
                    "%{$busca}%"
                )
            )
            ->when(
                $status === 'ativos',
                fn ($query) => $query->where('ativo', '1')
            )
            ->when(
                $status === 'inativos',
                fn ($query) => $query->where(
                    function ($subquery) {
                        $subquery
                            ->whereNull('ativo')
                            ->orWhere('ativo', '!=', '1');
                    }
                )
            )
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('cadastros-auxiliares.index', [
            'registros' => $registros,
            'routePrefix' => 'marcas',
            'titulo' => 'Marcas',
            'singular' => 'Marca',
            'subtitulo' =>
                'Gerencie as marcas utilizadas no cadastro de produtos.',
            'icone' => 'bi-bookmark-star',
            'possuiSigla' => false,
            'possuiDescricao' => false,
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
                    'max:100',
                    Rule::unique('marcas', 'nome'),
                ],

                'ativo' => [
                    'nullable',
                    Rule::in(['0', '1']),
                ],
            ],
            [
                'nome.required' =>
                    'Informe o nome da marca.',

                'nome.unique' =>
                    'Já existe uma marca cadastrada com esse nome.',
            ]
        );

        $marca = new Marca();
        $marca->timestamps = false;
        $marca->nome = $dados['nome'];

        $marca->ativo =
            $request->input('ativo', '0') === '1'
                ? '1'
                : '0';

        $marca->save();

        return redirect()
            ->route('marcas.index')
            ->with(
                'success',
                'Marca cadastrada com sucesso!'
            );
    }

public function update(
    Request $request,
    Marca $marca
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
                'max:100',

                Rule::unique(
                    'marcas',
                    'nome'
                )->ignore($marca->id),
            ],

            'ativo' => [
                'nullable',
                Rule::in(['0', '1']),
            ],
        ],
        [
            'nome.required' =>
                'Informe o nome da marca.',

            'nome.unique' =>
                'Já existe uma marca cadastrada com esse nome.',
        ]
    );

    $marca->timestamps = false;
    $marca->nome = $dados['nome'];

    $marca->ativo =
        $request->input('ativo', '0') === '1'
            ? '1'
            : '0';

    $marca->save();

    return redirect()
        ->route('marcas.index')
        ->with(
            'success',
            'Marca atualizada com sucesso!'
        );
}

    public function edit(Marca $marca): View
    {
        return view('cadastros-auxiliares.edit', [
            'registro' => $marca,
            'routePrefix' => 'marcas',
            'titulo' => 'Editar Marca',
            'singular' => 'Marca',
            'icone' => 'bi-bookmark-star',
            'possuiSigla' => false,
            'possuiDescricao' => false,
        ]);
    }

    public function alternarStatus(
        Marca $marca
    ): RedirectResponse {
        $marca->timestamps = false;

        $marca->ativo = (string) $marca->ativo === '1'
            ? '0'
            : '1';

        $marca->save();

        return redirect()
            ->route('marcas.index')
            ->with(
                'success',
                'Status da marca atualizado com sucesso!'
            );
    }

    private function totais(): array
    {
        return [
            'total' => Marca::count(),

            'ativos' => Marca::where(
                'ativo',
                '1'
            )->count(),

            'inativos' => Marca::where(
                function ($query) {
                    $query
                        ->whereNull('ativo')
                        ->orWhere('ativo', '!=', '1');
                }
            )->count(),
        ];
    }
}