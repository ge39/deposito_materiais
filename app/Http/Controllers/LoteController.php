<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Produto;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoteController extends Controller
{
    public function index($produto_id)
    {
        $produto = Produto::with('lotes')->findOrFail($produto_id);
        return view('lotes.index', compact('produto'));
    }

    public function create($produto_id)
    {
        $produto = Produto::findOrFail($produto_id);
        return view('lotes.create', compact('produto'));
    }

    public function store(Request $request, $produto_id)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1',
            'preco_compra' => 'nullable|numeric|min:0',
            'data_compra' => 'nullable|date',
            'validade' => 'nullable|date|after_or_equal:data_compra',
        ]);

        $produto = Produto::findOrFail($produto_id);

        $lote = new Lote($request->all());
        $lote->produto_id = $produto->id;

        // Define validade padrão caso não informada
        if (!$lote->validade) {
            $lote->validade = Carbon::parse($lote->data_compra ?? now())->addMonths(3);
        }

        $lote->save();

        return redirect()->route('lotes.index', $produto->id)
            ->with('success', 'Lote adicionado com sucesso!');
    }

}
