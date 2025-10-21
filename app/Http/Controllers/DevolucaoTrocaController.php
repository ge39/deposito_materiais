<?php

namespace App\Http\Controllers;

use App\Models\DevolucaoTroca;
use App\Models\Venda;
use App\Models\Produto;
use Illuminate\Http\Request;

class DevolucaoTrocaController extends Controller
{
    // Listar todas as devoluções/trocas
    public function index()
    {
        $devolucoes = DevolucaoTroca::with(['venda.cliente', 'produto'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('devolucoes.index', compact('devolucoes'));
    }

    // Formulário de criação
    public function create()
    {
        $vendas = Venda::with('cliente')->orderBy('created_at', 'desc')->get();
        return view('devolucoes.create', compact('vendas'));
    }

    // Salvar no banco
    public function store(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:1',
            'tipo' => 'required|in:devolucao,troca',
            'motivo' => 'required|string|max:255',
        ]);

        // Verificar quantidade da venda
        $venda = Venda::findOrFail($request->venda_id);
        $produtoVenda = $venda->itens()->where('produto_id', $request->produto_id)->first();

        if (!$produtoVenda) {
            return back()->withErrors(['produto_id' => 'Produto não pertence a essa venda.'])->withInput();
        }

        if ($request->quantidade > $produtoVenda->quantidade) {
            return back()->withErrors(['quantidade' => 'Quantidade maior que a vendida.'])->withInput();
        }

        DevolucaoTroca::create([
            'venda_id' => $request->venda_id,
            'produto_id' => $request->produto_id,
            'quantidade' => $request->quantidade,
            'tipo' => $request->tipo,
            'motivo' => $request->motivo,
        ]);

        return redirect()->route('devolucoes.index')->with('success', 'Devolução/Troca registrada com sucesso.');
    }

    // Retornar produtos de uma venda (JSON)
    public function produtosDaVenda(Venda $venda)
    {
        $produtos = $venda->itens()->with('produto')->get()->map(function($item) {
            return [
                'id' => $item->produto->id,
                'nome' => $item->produto->nome,
                'quantidade' => $item->quantidade,
            ];
        });

        return response()->json($produtos);
    }
}
