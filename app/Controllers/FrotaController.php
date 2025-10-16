<?php

namespace App\Http\Controllers;

use App\Models\Frota;
use Illuminate\Http\Request;

class FrotaController extends Controller
{
    public function index()
    {
        $frotas = Frota::all();
        return view('frotas.index', compact('frotas'));
    }

    public function create()
    {
        return view('frotas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'veiculo' => 'required|string|max:255',
            'placa' => 'nullable|string|max:20',
            'capacidade' => 'nullable|integer',
        ]);

        Frota::create($data);
        return redirect()->route('frotas.index')->with('success', 'Frota criada com sucesso.');
    }

    public function show(Frota $frota)
    {
        return view('frotas.show', compact('frota'));
    }

    public function edit(Frota $frota)
    {
        return view('frotas.edit', compact('frota'));
    }

    public function update(Request $request, Frota $frota)
    {
        $data = $request->validate([
            'veiculo' => 'required|string|max:255',
            'placa' => 'nullable|string|max:20',
            'capacidade' => 'nullable|integer',
        ]);

        $frota->update($data);
        return redirect()->route('frotas.index')->with('success', 'Frota atualizada com sucesso.');
    }

    public function destroy(Frota $frota)
    {
        $frota->delete();
        return redirect()->route('frotas.index')->with('success', 'Frota removida com sucesso.');
    }
}
