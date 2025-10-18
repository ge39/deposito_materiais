<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\Venda;
use App\Models\Funcionario;
use App\Models\Frota;
use Illuminate\Http\Request;

class EntregaController extends Controller
{
    public function index()
    {
        $entregas = Entrega::with(['venda','frota','funcionario'])->get();
        return view('entregas.index', compact('entregas'));
    }

    public function create()
    {
        $vendas = Venda::all();
        $frotas = Frota::all();
        $funcionarios = Funcionario::where('funcao','motorista')->get();
        return view('entregas.create', compact('vendas','frotas','funcionarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'frota_id' => 'required|exists:frotas,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data_entrega' => 'nullable|date',
            'endereco_entrega' => 'nullable|string|max:255',
            'status' => 'required|in:pendente,em_transito,entregue,cancelada',
        ]);

        Entrega::create($data);
        return redirect()->route('entregas.index')->with('success', 'Entrega criada com sucesso.');
    }

    public function show(Entrega $entrega)
    {
        return view('entregas.show', compact('entrega'));
    }

    public function edit(Entrega $entrega)
    {
        $vendas = Venda::all();
        $frotas = Frota::all();
        $funcionarios = Funcionario::where('funcao','motorista')->get();
        return view('entregas.edit', compact('entrega','vendas','frotas','funcionarios'));
    }

    public function update(Request $request, Entrega $entrega)
    {
        $data = $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'frota_id' => 'required|exists:frotas,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data_entrega' => 'nullable|date',
            'endereco_entrega' => 'nullable|string|max:255',
            'status' => 'required|in:pendente,em_transito,entregue,cancelada',
        ]);

        $entrega->update($data);
        return redirect()->route('entregas.index')->with('success', 'Entrega atualizada com sucesso.');
    }

    public function destroy(Entrega $entrega)
    {
        $entrega->delete();
        return redirect()->route('entregas.index')->with('success', 'Entrega removida com sucesso.');
    }
}
