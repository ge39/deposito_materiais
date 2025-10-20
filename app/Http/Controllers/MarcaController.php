<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marca;

class MarcaController extends Controller
{
    // LISTAR MARCAS ATIVAS
    public function index()
    {
        $marcas = Marca::where('ativo', true)->orderBy('nome')->paginate(15);
        return view('marcas.index', compact('marcas'));
    }

    // FORMULÁRIO DE CRIAÇÃO
    public function create()
    {
        return view('marcas.create');
    }

    // SALVAR NOVA MARCA
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        Marca::create($validated);

        return redirect()->route('marcas.index')->with('success', 'Marca cadastrada com sucesso!');
    }

    // FORMULÁRIO DE EDIÇÃO
    public function edit(Marca $marca)
    {
        return view('marcas.edit', compact('marca'));
    }

    // ATUALIZAR MARCA
    public function update(Request $request, Marca $marca)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        $marca->update($validated);

        return redirect()->route('marcas.index')->with('success', 'Marca atualizada com sucesso!');
    }

    // DESATIVAR MARCA
    public function destroy(Marca $marca)
    {
        $marca->ativo = false;
        $marca->save();

        return redirect()->route('marcas.index')->with('success', 'Marca desativada com sucesso!');
    }

    // LISTAR MARCAS DESATIVADAS
    public function inativos()
    {
        $marcas = Marca::where('ativo', false)->orderBy('nome')->paginate(15);
        return view('marcas.inativos', compact('marcas'));
    }

    // REATIVAR MARCA
    public function reativar(Marca $marca)
    {
        $marca->ativo = true;
        $marca->save();

        return redirect()->route('marcas.inativos')->with('success', 'Marca reativada com sucesso!');
    }
}
