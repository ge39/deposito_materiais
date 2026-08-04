<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnidadeMedida;

class UnidadeMedidaController extends Controller
{
    public function index()
    {
        $unidades = UnidadeMedida::where('ativo', true)->orderBy('nome')->paginate(15);
        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('unidades.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:50',
            'sigla' => 'required|string|max:10',
            'ativo' => 'boolean',
        ]);

        UnidadeMedida::create($validated);

        return redirect()->route('unidades.index')->with('success', 'Unidade de Medida cadastrada com sucesso!');
    }

    public function edit(UnidadeMedida $unidade)
    {
        return view('unidades.edit', compact('unidade'));
    }

    public function update(Request $request, UnidadeMedida $unidade)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:50',
            'sigla' => 'required|string|max:10',
            'ativo' => 'boolean',
        ]);

        $unidade->update($validated);

        return redirect()->route('unidades.index')->with('success', 'Unidade de Medida atualizada com sucesso!');
    }

    public function destroy(UnidadeMedida $unidade)
    {
        $unidade->ativo = false;
        $unidade->save();

        return redirect()->route('unidades.index')->with('success', 'Unidade de Medida desativada com sucesso!');
    }

    public function inativos()
    {
        $unidades = UnidadeMedida::where('ativo', false)->orderBy('nome')->paginate(15);
        return view('unidades.inativos', compact('unidades'));
    }

    public function reativar(UnidadeMedida $unidade)
    {
        $unidade->ativo = true;
        $unidade->save();

        return redirect()->route('unidades.inativos')->with('success', 'Unidade de Medida reativada com sucesso!');
    }
}
