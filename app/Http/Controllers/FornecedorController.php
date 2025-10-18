<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    // Listar todos os fornecedores
    public function index()
    {
        $fornecedores = Fornecedor::all();
        return view('fornecedores.index', compact('fornecedores'));
    }

    // Formulário de criação
    public function create()
    {
        return view('fornecedores.create');
    }

    // Salvar novo fornecedor
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string',
        ]);
        Fornecedor::create($request->all());
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor criado com sucesso!');
    }

    // Mostrar detalhes de um fornecedor
    public function show(Fornecedor $fornecedore)
    {
        // Padronizando variável para view
        $fornecedor = $fornecedore;
        return view('fornecedores.show', compact('fornecedor'));
    }

    // Formulário de edição
    public function edit(Fornecedor $fornecedore)
    {
        // Padronizando variável para view
        $fornecedor = $fornecedore;
        return view('fornecedores.edit', compact('fornecedor'));
    }

    // Atualizar fornecedor
    public function update(Request $request, Fornecedor $fornecedore)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'endereco' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string',
        ]);

        $fornecedor = $fornecedore;
        $fornecedor->update($request->all());

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    // Deletar fornecedor
    public function destroy(Fornecedor $fornecedore)
    {
        $fornecedor = $fornecedore;
        $fornecedor->delete();

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor deletado com sucesso!');
    }
}
