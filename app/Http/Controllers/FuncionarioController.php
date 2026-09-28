<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FuncionarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if (! in_array($user->nivel_acesso, ['admin', 'gerente'], true)) {
                abort(403, 'Acesso negado!');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $funcionarios = Funcionario::ativos()
            ->orderBy('nome')
            ->paginate(15);

        return view('funcionarios.index', compact('funcionarios'));
    }

    public function search(Request $request)
    {
        $termo = trim((string) $request->input('q', ''));

        $funcionarios = Funcionario::ativos()
            ->when($termo !== '', function ($query) use ($termo) {
                $query->where(function ($busca) use ($termo) {
                    $busca->where('nome', 'like', "%{$termo}%")
                        ->orWhere('cpf', 'like', "%{$termo}%")
                        ->orWhere('email', 'like', "%{$termo}%");
                });
            })
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('funcionarios.index', [
            'funcionarios' => $funcionarios,
            'mensagem' => $funcionarios->isEmpty()
                ? 'Nenhum funcionário encontrado para o termo pesquisado.'
                : null,
        ]);
    }

    public function create()
    {
        return view('funcionarios.create');
    }

    public function store(Request $request)
    {
        $this->normalizarEntrada($request);

        $dados = $request->validate(
            $this->regrasValidacao(),
            $this->mensagensValidacao()
        );

        $dados = $this->prepararDadosPersistencia($request, $dados);

        Funcionario::create($dados);

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    public function edit(Funcionario $funcionario)
    {
        return view('funcionarios.edit', compact('funcionario'));
    }

    public function update(Request $request, Funcionario $funcionario)
    {
        $this->normalizarEntrada($request);

        $dados = $request->validate(
            $this->regrasValidacao($funcionario),
            $this->mensagensValidacao()
        );

        $dados = $this->prepararDadosPersistencia($request, $dados);

        $funcionario->fill($dados);
        $funcionario->save();

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function desativa(Funcionario $funcionario)
    {
        $funcionario->ativo = false;
        $funcionario->rastreamento_habilitado = false;
        $funcionario->save();

        return redirect()
            ->route('funcionarios.index')
            ->with('success', 'Funcionário desativado com sucesso!');
    }

    public function show($id)
    {
        $funcionario = Funcionario::findOrFail($id);

        return view('funcionarios.show', compact('funcionario'));
    }

    public function buscarPorCPF($cpf)
    {
        $cpf = preg_replace('/\D/', '', (string) $cpf);

        $funcionario = Funcionario::whereRaw(
            "REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?",
            [$cpf]
        )->first();

        if ($funcionario) {
            return response()->json([
                'success' => true,
                'data' => $funcionario,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Funcionário não encontrado.',
        ]);
    }

    private function normalizarEntrada(Request $request): void
    {
        $estado = strtoupper(
            trim((string) $request->input('estado', ''))
        );

        $request->merge([
            'cpf' => preg_replace(
                '/\D/',
                '',
                (string) $request->input('cpf', '')
            ),
            'estado' => $estado !== '' ? $estado : null,
        ]);
    }

    private function regrasValidacao(
        ?Funcionario $funcionario = null
    ): array {
        return [
            'cpf' => [
                'required',
                'digits:11',
                Rule::unique('funcionarios', 'cpf')
                    ->ignore($funcionario?->id),
            ],
            'nome' => ['required', 'string', 'max:255'],
            'funcao' => [
                'required',
                Rule::in(Funcionario::FUNCOES),
            ],
            'telefone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'cep' => ['nullable', 'string', 'max:12'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:12'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'size:2'],
            'observacoes' => ['nullable', 'string', 'max:250'],
            'data_admissao' => ['nullable', 'date'],
            'ativo' => ['nullable', 'boolean'],
            'rastreamento_habilitado' => ['nullable', 'boolean'],
        ];
    }

    private function mensagensValidacao(): array
    {
        return [
            'cpf.digits' => 'O CPF deve conter exatamente 11 números.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'funcao.in' => 'A função selecionada não é válida.',
            'rastreamento_habilitado.boolean' =>
                'A opção de rastreamento informada não é válida.',
        ];
    }

    private function prepararDadosPersistencia(
        Request $request,
        array $dados
    ): array {
        $dados['ativo'] = $request->boolean('ativo');

        $dados['rastreamento_habilitado'] =
            $dados['funcao'] === 'motorista'
            && $request->boolean('rastreamento_habilitado');

        return $dados;
    }
}