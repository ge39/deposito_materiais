<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteContaCorrente;
use App\Services\ContaCorrenteService;

class ContaCorrenteController extends Controller
{
    public function show($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);

        $movimentacoes = ClienteContaCorrente::where('cliente_id', $cliente->id)
            ->orderBy('id', 'desc')
            ->paginate(20);

        $saldo = app(ContaCorrenteService::class)
            ->saldoAtual($cliente->id);

        return view('clientes.conta_corrente.show', compact(
            'cliente',
            'movimentacoes',
            'saldo'
        ));
    }
}