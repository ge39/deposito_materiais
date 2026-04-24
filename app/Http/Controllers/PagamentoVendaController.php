<?php

namespace App\Http\Controllers;

use App\Models\PagamentoVenda;
use Illuminate\Http\Request;

class PagamentoVendaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'forma_pagamento' => 'required|string',
            'valor' => 'required|numeric|min:0.01'
        ]);

        PagamentoVenda::create([
            ...$request->all(),
            'user_id' => auth()->id(),
            'status'  => 'confirmado'
        ]);

        return back()->with('success', 'Pagamento registrado.');
    }
}
