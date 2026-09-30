<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Bloqueio de acesso: apenas admin e gerente
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!in_array($user->nivel_acesso, ['admin', 'gerente'])) {
                abort(403, 'Acesso negado!');
            }
            return $next($request);
        });
    }

    // Listar clientes ativos com busca inteligente
    // public function index(Request $request)
    // {

    //     $clientes = Cliente::orderBy('nome')->paginate(15);

    //     return view('clientes.index', compact('clientes'));

    // }

    // public function index(Request $request)
    // {
    //     $clientes = Cliente::with('credito')
    //         ->orderBy('nome')
    //         ->paginate(15);

    //     return view('clientes.index', compact('clientes'));
    // }

    public function index(Request $request)
{
    $clientes = Cliente::query()
        ->with('credito')
        ->where('ativo', 1)
        ->orderBy('nome')
        ->paginate(15)
        ->withQueryString();

    return view(
        'clientes.index',
        compact('clientes')
    );
}

    public function buscar(Request $request)
    {
        $dadosValidados = $request->validate(
            [
                'busca' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'tipo' => [
                    'nullable',
                    'in:fisica,juridica',
                ],
            ],
            [
                'busca.string' =>
                    'O termo informado para busca é inválido.',

                'busca.max' =>
                    'O termo de busca pode possuir no máximo 255 caracteres.',

                'tipo.in' =>
                    'O tipo de cliente informado é inválido.',
            ]
        );

        $busca = trim(
            (string) (
                $dadosValidados['busca']
                ?? ''
            )
        );

        $tipo = $dadosValidados['tipo'] ?? null;

        $documentoNumerico = preg_replace(
            '/\D/',
            '',
            $busca
        );

        $clientes = Cliente::query()
            ->with('credito')
            ->where('ativo', 1)
            ->when(
                $busca !== '',
                function ($query) use (
                    $busca,
                    $documentoNumerico
                ) {
                    $query->where(
                        function ($subQuery) use (
                            $busca,
                            $documentoNumerico
                        ) {
                            $subQuery
                                ->where(
                                    'nome',
                                    'like',
                                    '%' . $busca . '%'
                                )
                                ->orWhere(
                                    'cpf_cnpj',
                                    'like',
                                    '%' . $busca . '%'
                                );

                            if ($documentoNumerico !== '') {
                                $subQuery->orWhereRaw(
                                    "
                                        REPLACE(
                                            REPLACE(
                                                REPLACE(
                                                    REPLACE(
                                                        cpf_cnpj,
                                                        '.',
                                                        ''
                                                    ),
                                                    '-',
                                                    ''
                                                ),
                                                '/',
                                                ''
                                            ),
                                            ' ',
                                            ''
                                        ) LIKE ?
                                    ",
                                    [
                                        '%' . $documentoNumerico . '%',
                                    ]
                                );
                            }
                        }
                    );
                }
            )
            ->when(
                ! empty($tipo),
                function ($query) use ($tipo) {
                    $query->where(
                        'tipo',
                        $tipo
                    );
                }
            )
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view(
            'clientes.index',
            compact('clientes')
        );
    }

    // Cliente credito
    // public function buscar (Request $request){
    //      $credito = \App\Models\ClienteCredito::where('cliente_id', $cliente->id)
    //         ->latest('id')
    //         ->first();

    //      $clientes = Cliente::query()
    //         ->where('ativo', 1)
    //         ->when($request->busca, function($query, $busca) {
    //             $query->where('nome', 'like', "%{$busca}%")
    //                   ->orWhere('cpf_cnpj', 'like', "%{$busca}%");
    //         })
    //         ->when($request->tipo, function($query, $tipo) {
    //             $query->where('tipo', $tipo);
    //         })
    //         ->orderBy('nome')
    //         ->paginate(15);

    //     return view('clientes.index', compact('clientes'));
    // }

    // Listar clientes inativos
    public function inativos()
    {
        $clientes = Cliente::where('ativo', 0)->paginate(15);
        return view('clientes.inativos', compact('clientes'));
    }

    // Formulário de criação
    public function create()
    {
        return view('clientes.create');
    }

    // Salvar novo cliente
    public function store(Request $request)
    {
        $this->normalizarEndereco($request);

        $data = $request->validate(
            [
                'nome' => 'required|string|max:255',
                'tipo' => 'required|in:fisica,juridica',
                'cpf_cnpj' => 'required|string|max:20',
                'rg_ie' => 'nullable|string|max:50',
                'orgao_emissor' => 'nullable|string|max:50',
                'data_emissao' => 'nullable|date',
                'data_nascimento' => 'nullable|date',
                'sexo' => 'nullable|in:masculino,feminino,outro',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'cep' => [
                    'nullable',
                    'regex:/^\d{5}-\d{3}$/',
                ],
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:10',
                'bairro' => 'nullable|string|max:255',
                'cidade' => 'nullable|string|max:255',
                'estado' => [
                    'nullable',
                    'string',
                    'size:2',
                    'regex:/^[A-Z]{2}$/',
                ],
                // 'limite_credito' => 'nullable|numeric',
                'observacoes' => 'nullable|string',
                'ativo' => 'nullable|boolean',
            ],
            [
                'cep.regex' =>
                    'O CEP deve possuir exatamente oito números no formato 00000-000.',
                'estado.size' =>
                    'O estado deve ser informado com a sigla de duas letras.',
                'estado.regex' =>
                    'O estado deve conter somente a sigla de duas letras.',
            ]
        );

        $data['ativo'] = $request->has('ativo') ? 1 : 0;

        Cliente::create($data);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente criado com sucesso.');
    }

    // Formulário de edição
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    // Atualizar cliente
    public function update(Request $request, Cliente $cliente)
    {
        $this->normalizarEndereco($request);

        $data = $request->validate(
            [
                'nome' => 'required|string|max:255',
                'tipo' => 'required|in:fisica,juridica',
                'cpf_cnpj' => 'required|string|max:20',
                'rg_ie' => 'nullable|string|max:50',
                'orgao_emissor' => 'nullable|string|max:50',
                'data_emissao' => 'nullable|date',
                'data_nascimento' => 'nullable|date',
                'sexo' => 'nullable|in:masculino,feminino,outro',
                'telefone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'cep' => [
                    'nullable',
                    'regex:/^\d{5}-\d{3}$/',
                ],
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:10',
                'bairro' => 'nullable|string|max:255',
                'cidade' => 'nullable|string|max:255',
                'estado' => [
                    'nullable',
                    'string',
                    'size:2',
                    'regex:/^[A-Z]{2}$/',
                ],
                // 'limite_credito' => 'nullable|numeric',
                'observacoes' => 'nullable|string',
                'ativo' => 'nullable|boolean',
            ],
            [
                'cep.regex' =>
                    'O CEP deve possuir exatamente oito números no formato 00000-000.',
                'estado.size' =>
                    'O estado deve ser informado com a sigla de duas letras.',
                'estado.regex' =>
                    'O estado deve conter somente a sigla de duas letras.',
            ]
        );

        $data['ativo'] = $request->has('ativo') ? 1 : 0;

        $cliente->update($data);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    private function normalizarEndereco(Request $request): void
    {
        $cepNumerico = preg_replace(
            '/\D+/',
            '',
            (string) $request->input('cep', '')
        );

        $cep = null;

        if ($cepNumerico !== '') {
            $cep = strlen($cepNumerico) === 8
                ? substr($cepNumerico, 0, 5)
                    . '-'
                    . substr($cepNumerico, 5, 3)
                : $cepNumerico;
        }

        $estado = $this->normalizarTexto(
            $request->input('estado')
        );

        $request->merge([
            'cep' => $cep,
            'endereco' => $this->normalizarTexto(
                $request->input('endereco')
            ),
            'numero' => $this->normalizarTexto(
                $request->input('numero')
            ),
            'bairro' => $this->normalizarTexto(
                $request->input('bairro')
            ),
            'cidade' => $this->normalizarTexto(
                $request->input('cidade')
            ),
            'estado' => $estado !== null
                ? strtoupper($estado)
                : null,
        ]);
    }

    private function normalizarTexto(mixed $valor): ?string
    {
        $texto = trim((string) ($valor ?? ''));

        if ($texto === '') {
            return null;
        }

        $textoNormalizado = preg_replace(
            '/\s+/u',
            ' ',
            $texto
        );

        return $textoNormalizado !== null
            ? $textoNormalizado
            : $texto;
    }


    public function show(Cliente $cliente)
    {
        $saldo = app(\App\Services\ContaCorrenteService::class)
            ->saldoAtual($cliente->id);

        $credito = \App\Models\ClienteCredito::where('cliente_id', $cliente->id)
            ->latest('id')
            ->first();

        $movimentacoes = \App\Models\ClienteContaCorrente::where('cliente_id', $cliente->id)
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('clientes.show', compact(
            'cliente',
            'saldo',
            'credito',
            'movimentacoes'
        ));
    }

    // Ativar cliente
    public function ativar(Cliente $cliente)
    {

       $cliente->ativo = '1';
        $cliente->save();
        return redirect()->route('clientes.inativos')->with('success', 'Cliente ativado.');
    }

    // Desativar cliente
    public function desativar(Cliente $cliente)
    {
        $cliente->ativo = 0;
        $cliente->save();
        return redirect()->route('clientes.index')->with('success', 'Cliente desativado.');
    }

    public function saldo($id)
    {
        $cliente = \App\Models\Cliente::findOrFail($id);

        $saldo = \App\Models\ClienteContaCorrente::where('cliente_id', $id)
            ->sum(\DB::raw("
                CASE
                    WHEN tipo = 'credito' THEN valor
                    WHEN tipo = 'debito' THEN -valor
                END
            "));

        return response()->json([
            'nome' => $cliente->nome,
            'saldo' => (float) $saldo
        ]);
    }

}
