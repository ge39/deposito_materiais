<?php

namespace App\Http\Controllers;

use App\Models\UnidadeMedida;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnidadeMedidaController extends Controller
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

        $registros = UnidadeMedida::query()
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($subquery) use ($busca) {
                    $subquery
                        ->where(
                            'nome',
                            'like',
                            "%{$busca}%"
                        )
                        ->orWhere(
                            'sigla',
                            'like',
                            "%{$busca}%"
                        );
                });
            })
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
            'routePrefix' => 'unidades-medida',
            'titulo' => 'Unidades de Medida',
            'singular' => 'Unidade de Medida',
            'subtitulo' =>
                'Gerencie nomes e siglas utilizados nos produtos.',
            'icone' => 'bi-rulers',
            'possuiSigla' => true,
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

        $siglaNormalizada = Str::upper(
            preg_replace(
                '/\s+/u',
                '',
                trim((string) $request->input('sigla'))
            )
        );

        $request->merge([
            'nome' => $nomeNormalizado,
            'sigla' => $siglaNormalizada,
        ]);

        $dados = $request->validate(
            [
                'nome' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique(
                        'unidades_medida',
                        'nome'
                    ),
                ],

                'sigla' => [
                    'required',
                    'string',
                    'max:10',
                    Rule::unique(
                        'unidades_medida',
                        'sigla'
                    ),
                ],

                'ativo' => [
                    'nullable',
                    Rule::in(['0', '1']),
                ],
            ],
            [
                'nome.required' =>
                    'Informe o nome da unidade de medida.',

                'nome.unique' =>
                    'Já existe uma unidade de medida com esse nome.',

                'sigla.required' =>
                    'Informe a sigla da unidade de medida.',

                'sigla.unique' =>
                    'Já existe uma unidade de medida com essa sigla.',
            ]
        );

        $unidadeMedida = new UnidadeMedida();
        $unidadeMedida->timestamps = false;
        $unidadeMedida->nome = $dados['nome'];
        $unidadeMedida->sigla = $dados['sigla'];

        $unidadeMedida->ativo =
            $request->input('ativo', '0') === '1'
                ? '1'
                : '0';

        $unidadeMedida->save();

        return redirect()
            ->route('unidades-medida.index')
            ->with(
                'success',
                'Unidade de medida cadastrada com sucesso!'
            );
    }

public function update(
    Request $request,
    UnidadeMedida $unidadeMedida
): RedirectResponse {
    $nomeNormalizado = preg_replace(
        '/\s+/u',
        ' ',
        trim((string) $request->input('nome'))
    );

    $siglaNormalizada = Str::upper(
        preg_replace(
            '/\s+/u',
            '',
            trim((string) $request->input('sigla'))
        )
    );

    $request->merge([
        'nome' => $nomeNormalizado,
        'sigla' => $siglaNormalizada,
    ]);

    $dados = $request->validate(
        [
            'nome' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'unidades_medida',
                    'nome'
                )->ignore($unidadeMedida->id),
            ],

            'sigla' => [
                'required',
                'string',
                'max:10',

                Rule::unique(
                    'unidades_medida',
                    'sigla'
                )->ignore($unidadeMedida->id),
            ],

            'ativo' => [
                'nullable',
                Rule::in(['0', '1']),
            ],
        ],
        [
            'nome.required' =>
                'Informe o nome da unidade de medida.',

            'nome.unique' =>
                'Já existe uma unidade de medida com esse nome.',

            'sigla.required' =>
                'Informe a sigla da unidade de medida.',

            'sigla.unique' =>
                'Já existe uma unidade de medida com essa sigla.',
        ]
    );

    $unidadeMedida->timestamps = false;
    $unidadeMedida->nome = $dados['nome'];
    $unidadeMedida->sigla = $dados['sigla'];

    $unidadeMedida->ativo =
        $request->input('ativo', '0') === '1'
            ? '1'
            : '0';

    $unidadeMedida->save();

    return redirect()
        ->route('unidades-medida.index')
        ->with(
            'success',
            'Unidade de medida atualizada com sucesso!'
        );
}

    public function edit(
        UnidadeMedida $unidadeMedida
    ): View {
        return view('cadastros-auxiliares.edit', [
            'registro' => $unidadeMedida,
            'routePrefix' => 'unidades-medida',
            'titulo' => 'Editar Unidade de Medida',
            'singular' => 'Unidade de Medida',
            'icone' => 'bi-rulers',
            'possuiSigla' => true,
            'possuiDescricao' => false,
        ]);
    }

    public function alternarStatus(
        UnidadeMedida $unidadeMedida
    ): RedirectResponse {
        $unidadeMedida->timestamps = false;

        $unidadeMedida->ativo =
            (string) $unidadeMedida->ativo === '1'
                ? '0'
                : '1';

        $unidadeMedida->save();

        return redirect()
            ->route('unidades-medida.index')
            ->with(
                'success',
                'Status da unidade de medida atualizado com sucesso!'
            );
    }

    private function totais(): array
    {
        return [
            'total' => UnidadeMedida::count(),

            'ativos' => UnidadeMedida::where(
                'ativo',
                '1'
            )->count(),

            'inativos' => UnidadeMedida::where(
                function ($query) {
                    $query
                        ->whereNull('ativo')
                        ->orWhere('ativo', '!=', '1');
                }
            )->count(),
        ];
    }
}