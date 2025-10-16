<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo_cliente' => 'nullable|in:pf,pj',
            'cpf_cnpj' => 'nullable|string|max:20',
            'rg_ie' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'endereco' => 'nullable|string|max:255',
            'limite_credito' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
        ]);

        Cliente::create($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente criado com sucesso.');
    }

    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo_cliente' => 'nullable|in:pf,pj',
            'cpf_cnpj' => 'nullable|string|max:20',
            'rg_ie' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'endereco' => 'nullable|string|max:255',
            'limite_credito' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
        ]);

        $cliente->update($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido com sucesso.');
    }
}
