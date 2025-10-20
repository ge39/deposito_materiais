<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    // Listar clientes ativos
    public function index()
    {
        $clientes = Cliente::where('ativo', 1)->paginate(15);
        return view('clientes.index', compact('clientes'));
    }

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
        $data = $request->validate([
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
            'cep' => 'nullable|string|max:20',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:50',
            'limite_credito' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $data['ativo'] = $request->has('ativo') ? 1 : 0;

        Cliente::create($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente criado com sucesso.');
    }

    // Formulário de edição
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    // Atualizar cliente
    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
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
            'cep' => 'nullable|string|max:20',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:50',
            'limite_credito' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $data['ativo'] = $request->has('ativo') ? 1 : 0;

        $cliente->update($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso.');
    }
    public function show($id)
    {
        // Buscar cliente pelo ID, ou falhar
        $cliente = Cliente::findOrFail($id);

        // Retorna a view com os dados do cliente
        return view('clientes.show', compact('cliente'));
    }


    // Ativar cliente
    public function ativar(Cliente $cliente)
    {
        $cliente->ativo = 1;
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
}
