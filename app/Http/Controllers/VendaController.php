<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;

class VendaController extends Controller
{
    public function index()
    {
        $itens = session()->get('pdv', []);
        $total = array_sum(array_column($itens, 'subtotal'));
        return view('pdv.index', compact('itens', 'total'));
    }

    public function buscarProduto(Request $request)
    {
        $term = $request->term;
        $produtos = Produto::where('ativo',1)
                    ->where('nome','like',"%{$term}%")
                    ->orWhere('codigo','like',"%{$term}%")
                    ->get();
        return response()->json($produtos);
    }
 
    public function adicionarProduto(Request $request)
    {
        $produto = Produto::findOrFail($request->produto_id);
        $quantidade = $request->quantidade ?? 1;

        $itens = session()->get('pdv', []);

        if(isset($itens[$produto->id])){
            $itens[$produto->id]['quantidade'] += $quantidade;
            $itens[$produto->id]['subtotal'] = $itens[$produto->id]['quantidade'] * $produto->preco;
        } else {
            $itens[$produto->id] = [
                'nome' => $produto->nome,
                'preco' => $produto->preco,
                'quantidade' => $quantidade,
                'subtotal' => $produto->preco * $quantidade
            ];
        }

        session()->put('pdv', $itens);

        return response()->json([
            'success'=>true,
            'itens'=>$itens,
            'total'=>array_sum(array_column($itens,'subtotal'))
        ]);
    }
    public function store(Request $request)
    {
        // Verifica se existe vale aplicado
        $vale = null;
        $valorDesconto = 0;

        if ($request->filled('vale_codigo')) {
            $vale = \App\Models\ValeCompra::where('codigo', $request->vale_codigo)
                ->where('status', 'ativo')
                ->first();

            if (!$vale) {
                return back()->with('error', 'Código de vale inválido ou expirado.');
            }

            // Verifica se o vale pertence ao mesmo cliente (opcional)
            if ($vale->cliente_id != $request->cliente_id) {
                return back()->with('error', 'Este vale não pertence ao cliente selecionado.');
            }

            $valorDesconto = min($vale->saldo, $request->total_venda);
        }

        // Cria a venda normalmente
        $venda = Venda::create([
            'cliente_id' => $request->cliente_id,
            'data_venda' => now(),
            'valor_total' => $request->total_venda - $valorDesconto,
            'status' => 'concluída',
        ]);

        // Se houver vale, atualiza os dados
        if ($vale) {
            $vale->valor_utilizado += $valorDesconto;

            if ($vale->saldo <= 0.01) {
                $vale->status = 'usado';
                $vale->data_utilizacao = now();
            }

            $vale->save();
        }

        return redirect()->route('vendas.show', $venda->id)
            ->with('success', 'Venda concluída com sucesso' . ($vale ? ' com uso de vale!' : ''));
    }

    public function gerarCupom(Venda $venda)
        {
            $itens = $venda->itens; // relação com VendaItem
            $total = $venda->total;
            $valor_pago = $venda->valor_pago;
            $troco = $venda->troco;
            $forma_pagamento = $venda->forma_pagamento;

            return view('pdv.cupom', compact('venda','itens','total','valor_pago','troco','forma_pagamento'));
        }
        
    public function removerProduto(Request $request)
    {
        $itens = session()->get('pdv', []);
        unset($itens[$request->produto_id]);
        session()->put('pdv', $itens);

        return response()->json([
            'success'=>true,
            'itens'=>$itens,
            'total'=>array_sum(array_column($itens,'subtotal'))
        ]);
    }

    public function finalizarVenda(Request $request)
    {
        $itens = session()->get('pdv', []);
        if(!$itens) return response()->json(['error'=>'Nenhum produto adicionado.'],400);

        $venda = Venda::create([
            'total'=>array_sum(array_column($itens,'subtotal')),
            'user_id'=>auth()->id() ?? null,
            'forma_pagamento'=>$request->forma_pagamento,
            'valor_pago'=>$request->valor_pago,
            'troco'=>($request->valor_pago ?? 0) - array_sum(array_column($itens,'subtotal'))
        ]);

        foreach($itens as $produtoId=>$item){
            VendaItem::create([
                'venda_id'=>$venda->id,
                'produto_id'=>$produtoId,
                'quantidade'=>$item['quantidade'],
                'preco'=>$item['preco'],
                'subtotal'=>$item['subtotal']
            ]);
        }


        session()->forget('pdv');

        return response()->json(['success'=>true,'message'=>'Venda finalizada com sucesso!','troco'=>($request->valor_pago ?? 0) - array_sum(array_column($itens,'subtotal'))]);
    }
}
