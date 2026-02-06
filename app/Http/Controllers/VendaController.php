<?php

namespace App\Http\Controllers;
use Illuminate\Validation\ValidationException;
use App\Models\Venda;
use App\Models\ItemVenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    /**
     * Store da venda (PDV)
     * Responsabilidade TOTAL do backend
     */


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Cria a venda
            $venda = Venda::create([
                'cliente_id'      => $request->input('cliente_id'),
                'funcionario_id'  => $request->input('funcionario_id'),
                'caixa_id'        => $request->input('caixa_id'),
                'data_venda'      => $request->input('dataVenda'),
                'endereco'        => $request->input('endereco'),
                'total'           => collect($request->input('itens', []))
                                        ->sum(fn($i) => $i['quantidade'] * $i['valor_unitario'])
            ]);

            // 2️⃣ Persiste itens da venda
            foreach ($request->input('itens', []) as $item) {
                $venda->itens()->create([
                    'produto_id'    => $item['produto_id'],
                    'lote_id'       => $item['lote_id'] ?? null,
                    'quantidade'    => $item['quantidade'],
                    'preco_unitario'=> $item['valor_unitario']
                ]);
            }

            // 🔹 Commit da transação
            DB::commit();

            // 3️⃣ Retorna sucesso + venda_id para o JS chamar /venda/finalizar/{id}
            return response()->json([
                'success'  => true,
                'message'  => 'Venda criada com sucesso',
                'venda_id' => $venda->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //pagamentos de venda (pagamentos_venda)
   public function finalizar(Request $request, Venda $venda)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Todas as formas de pagamento possíveis
            $formasPossiveis = ['dinheiro', 'cartao_credito', 'cartao_debito', 'carteira', 'pix'];

            // 2️⃣ Total da venda
            $totalVenda = $venda->itens->sum(fn($i) => $i->quantidade * $i->preco_unitario);

            // 3️⃣ Pagamentos enviados pelo front (se houver)
            $pagamentosEnviados = collect($request->input('pagamentos', []))
                ->keyBy('forma'); // indexa por forma

            $totalPagamentos = 0;

            // 4️⃣ Cria todos os registros de pagamento
            foreach ($formasPossiveis as $forma) {
                $valor = isset($pagamentosEnviados[$forma]) ? (float) $pagamentosEnviados[$forma]['valor'] : 0;

                $totalPagamentos += $valor;

                $venda->pagamentos()->create([
                    'user_id' => auth()->id(),
                    'caixa_id' => $venda->caixa_id,
                    'forma_pagamento' => $forma,
                    'valor' => $valor,
                    'status' => 'confirmado',
                ]);
            }

            // 5️⃣ Validação: pagamentos insuficientes
            if ($totalPagamentos < $totalVenda) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'erro' => "Pagamento insuficiente. Total da venda: R$ {$totalVenda}, total pago: R$ {$totalPagamentos}"
                ], 422);
            }

            // 6️⃣ Atualiza status da venda
            $venda->update([
                'status' => 'finalizada',
                'total' => $totalVenda,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'total' => $totalVenda,
                'message' => 'Venda finalizada com sucesso',
                'venda_id' => $venda->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'erro' => $e->getMessage()
            ], 500);
        }
    }

    // public function finalizar(Request $request, Venda $venda)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // 1️⃣ Todas as formas de pagamento possíveis
    //         $formasPossiveis = ['dinheiro', 'cartao_credito', 'cartao_debito', 'carteira', 'pix'];

    //         // 2️⃣ Total da venda
    //         $totalVenda = $venda->itens->sum(fn($i) => $i->quantidade * $i->preco_unitario);

    //         // 3️⃣ Pagamentos enviados pelo front, indexados por forma
    //         $pagamentosEnviados = collect($request->input('pagamentos', []))
    //             ->keyBy('forma');

    //         // 4️⃣ Cria uma coleção com todas as formas, preenchendo 0 se não houver valor
    //         $pagamentosParaRegistrar = collect($formasPossiveis)->map(function ($forma) use ($pagamentosEnviados) {
    //             return [
    //                 'forma_pagamento' => $forma,
    //                 'valor' => isset($pagamentosEnviados[$forma]) ? (float) $pagamentosEnviados[$forma]['valor'] : 0,
    //             ];
    //         });

    //         // 5️⃣ Soma os valores
    //         $totalPagamentos = $pagamentosParaRegistrar->sum('valor');

    //         // 6️⃣ Validação: pagamento insuficiente
    //         if ($totalPagamentos < $totalVenda) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'erro' => "Pagamento insuficiente. Total da venda: R$ {$totalVenda}, total pago: R$ {$totalPagamentos}"
    //             ], 422);
    //         }

    //         // 7️⃣ Persiste todos os pagamentos
    //         $pagamentosParaRegistrar->each(function ($pag) use ($venda) {
    //             $venda->pagamentos()->create([
    //                 'user_id' => auth()->id(),
    //                 'caixa_id' => $venda->caixa_id,
    //                 'forma_pagamento' => $pag['forma_pagamento'],
    //                 'valor' => $pag['valor'],
    //                 'status' => 'confirmado',
    //             ]);
    //         });

    //         // 8️⃣ Atualiza status da venda
    //         $venda->update([
    //             'status' => 'finalizada',
    //             'total' => $totalVenda,
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'total' => $totalVenda,
    //             'message' => 'Venda finalizada com sucesso',
    //             'venda_id' => $venda->id
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'erro' => $e->getMessage()
    //         ], 500);
    //     }
    // }



}
