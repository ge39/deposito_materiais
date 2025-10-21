<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;

class FuncionarioController extends Controller
{
    // Lista apenas funcionários ativos
    public function index()
    {
        $funcionarios = Funcionario::where('ativo', 1)->get();
        // return view('funcionarios.index', compact('funcionarios'));

         $funcionarios = Funcionario::where('ativo', 1)->paginate(15); // 15 cards por página
        return view('funcionarios.index', compact('funcionarios'));
    }
    // Pesquisa funcionários por nome, CPF ou email
    public function search(Request $request)
    {
        $query = $request->input('q');

        $funcionarios = \App\Models\Funcionario::where('nome', 'like', "%{$query}%")
            ->orWhere('cpf', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->paginate(15);

        if ($funcionarios->isEmpty()) {
            return view('funcionarios.index', [
                'funcionarios' => $funcionarios,
                'mensagem' => 'Nenhum funcionário encontrado para o termo pesquisado.'
            ]);
        }

        return view('funcionarios.index', compact('funcionarios'));
    }

    // Formulário de cadastro
    public function create()
    {
        return view('funcionarios.create');
    }

    // Salva um novo funcionário
    public function store(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string|max:14|unique:funcionarios,cpf',
            'nome' => 'required|string|max:255',
            'funcao' => 'required|string|max:50',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'cep' => 'nullable|string|size:9',
            'endereco' => 'nullable|string',
            'numero' => 'nullable|string',
            'bairro' => 'required|string|max:255',
            'cidade' => 'nullable|string',
            'estado' => 'nullable|string|size:2',
            'observacoes' => 'nullable|string',
            'data_admissao' => 'nullable|date',
            'ativo' => 'nullable|boolean',
        ]);

        Funcionario::create($request->all());

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    // Formulário de edição
    public function edit(Funcionario $funcionario)
    {
        return view('funcionarios.edit', compact('funcionario'));
    }

    // Atualiza um funcionário existente
    public function update(Request $request, Funcionario $funcionario)
    {
        $request->validate([
            'cpf' => 'required|string|max:14|unique:funcionarios,cpf,' . $funcionario->id,
            'nome' => 'required|string|max:255',
            'funcao' => 'required|string|max:50',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'cep' => 'nullable|string|size:9',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
             'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'observacoes' => 'nullable|string',
            'data_admissao' => 'nullable|date',
            'ativo' => 'nullable|boolean',
        ]);

        $funcionario->update($request->all());

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    // Desativa um funcionário (marca como inativo)
    public function desativa(Funcionario $funcionario)
    {
        $funcionario->ativo = 0;
        $funcionario->save();

        return redirect()->route('funcionarios.index')
            ->with('success', 'Funcionário desativado com sucesso!');
    }

    public function show($id)
    {
        $funcionario = Funcionario::findOrFail($id);
        return view('funcionarios.show', compact('funcionario'));
    }

}
