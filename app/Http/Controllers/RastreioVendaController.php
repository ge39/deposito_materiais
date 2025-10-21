<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Lote;
use App\Models\Venda;
use App\Models\VendaItem;

class RastreioVendaController extends Controller
{
    public function index()
    {
        $clientes = Cliente::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $lotes = Lote::orderBy('id')->get();
        $vendas = Venda::with('cliente')->orderBy('id')->get();

        $itens = collect(); // coleção vazia inicialmente

        return view('rastreio.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }

    public function buscar(Request $request)
    {
        $clientes = Cliente::orderBy('nome')->get();
        $produtos = Produto::orderBy('nome')->get();
        $lotes = Lote::orderBy('id')->get();
        $vendas = Venda::with('cliente')->orderBy('id')->get();

        $itensQuery = VendaItem::query();

        // Se selecionou uma venda específica
        if ($request->filled('venda_id')) {
            $itensQuery->where('venda_id', $request->venda_id);
        } else {
            // Caso não selecione uma venda, aplicar outros filtros
            if ($request->filled('cliente_id')) {
                $vendasCliente = Venda::where('cliente_id', $request->cliente_id)->pluck('id');
                $itensQuery->whereIn('venda_id', $vendasCliente);
            }

            if ($request->filled('produto_id')) {
                $itensQuery->where('produto_id', $request->produto_id);
            }

            if ($request->filled('lote_id')) {
                $itensQuery->where('lote_id', $request->lote_id);
            }
        }

        $itens = $itensQuery->get();

        return view('rastreio.index', compact('clientes', 'produtos', 'lotes', 'vendas', 'itens'));
    }
}
