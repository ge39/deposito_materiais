<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Cliente;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class VendaController extends Controller
{
    public function index()
    {
        $vendas = Venda::with(['cliente', 'funcionario'])->get();
        return view('vendas.index', compact('vendas'));
    }

    public function create()
    {
        $clientes = Cliente::all();
        $funcionarios = Funcionario::all();
        return view('vendas.create', compact('clientes', 'funcionarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data_venda' => 'required|date',
            'total' => 'required|numeric',
            'status' => 'required|in:pendente,concluida,cancelada',
        ]);

        Venda::create($data);
        return redirect()->route('vendas.index')->with('success', 'Venda criada com sucesso.');
    }

    public function show(Venda $venda)
    {
        return view('vendas.show', compact('venda'));
    }

    public function edit(Venda $venda)
    {
        $clientes = Cliente::all();
        $funcionarios = Funcionario::all();
        return view('vendas.edit', compact('venda','clientes','funcionarios'));
    }

    public function update(Request $request, Venda $venda)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data_venda' => 'required|date',
            'total' => 'required|numeric',
            'status' => 'required|in:pendente,concluida,cancelada',
        ]);

        $venda->update($data);
        return redirect()->route('vendas.index')->with('success', 'Venda atualizada com sucesso.');
    }

    public function destroy(Venda $venda)
    {
        $venda->delete();
        return redirect()->route('vendas.index')->with('success', 'Venda removida com sucesso.');
    }
}
