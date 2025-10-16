<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    public function index()
    {
        $fornecedores = Fornecedor::all();
        return view('fornecedores.index', compact('fornecedores'));
    }

    public function create()
    {
        return view('fornecedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'endereco' => 'nullable|string|max:255',
        ]);

        Fornecedor::create($data);
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor criado com sucesso.');
    }

    public function show(Fornecedor $fornecedor)
    {
        return view('fornecedores.show', compact('fornecedor'));
    }

    public function edit(Fornecedor $fornecedor)
    {
        return view('fornecedores.edit', compact('fornecedor'));
    }

    public function update(Request $request, Fornecedor $fornecedor)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'endereco' => 'nullable|string|max:255',
        ]);

        $fornecedor->update($data);
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso.');
    }

    public function destroy(Fornecedor $fornecedor)
    {
        $fornecedor->delete();
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor removido com sucesso.');
    }
}
