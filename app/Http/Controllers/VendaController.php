<?php

namespace App\Http\Controllers;
use Illuminate\Validation\ValidationException;
use App\Models\Venda;
Use App\Models\Empresa;
use Mguimaraes\Pix\Payload;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Services\CreditoService;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    /**
     * Store da venda (PDV)
     * Responsabilidade TOTAL do backend
     */


    // public function store(Request $request)
    // {
    
    //     DB::beginTransaction();
    //     try {
    //         // 1️⃣ Cria a venda
    //         $venda = Venda::create([
    //             'cliente_id'      => $request->input('cliente_id'),
    //             'funcionario_id'  => $request->input('funcionario_id'),
    //             'caixa_id'        => $request->input('caixa_id'),
    //             'data_venda'      => $request->input('dataVenda'),
    //             'endereco'        => $request->input('endereco'),
    //             'total'           => collect($request->input('itens', []))
    //                                     ->sum(fn($i) => $i['quantidade'] * $i['valor_unitario'])
    //         ]);

    //         // 2️⃣ Persiste itens da venda
    //         foreach ($request->input('itens', []) as $item) {
    //             $venda->itens()->create([
    //                 'produto_id'    => $item['produto_id'],
    //                 'lote_id'       => $item['lote_id'] ?? null,
    //                 'quantidade'    => $item['quantidade'],
    //                 'preco_unitario'=> $item['valor_unitario']
    //             ]);
    //         }

    //         // 🔹 Commit da transação
    //         DB::commit();

    //         // 3️⃣ Retorna sucesso + venda_id para o JS chamar /venda/finalizar/{id}
    //         return response()->json([
    //             'success'  => true,
    //             'message'  => 'Venda criada com sucesso',
    //             'venda_id' => $venda->id
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        // 1️⃣ Validação
        $request->validate([
            'cliente_id'     => 'nullable|exists:clientes,id', // pode ser null, usamos fallback
            'funcionario_id' => 'required|exists:users,id',
            'caixa_id'       => 'required|exists:caixas,id',
            'dataVenda'      => 'required|date',
            'endereco'       => 'nullable|string|max:255',
            'itens'          => 'required|array|min:1',
            'itens.*.produto_id'     => 'required|exists:produtos,id',
            'itens.*.quantidade'     => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
            'itens.*.lote_id'        => 'nullable|exists:lotes,id',
        ]);

        DB::beginTransaction();
        try {
            // 2️⃣ Fallback cliente "VENDA BALCÃO" caso não seja enviado
            $clienteId = $request->input('cliente_id');
            if (!$clienteId) {
                $clienteBalcao = Cliente::where('nome', 'VENDA BALCAO')
                                        ->where('ativo', 1)
                                        ->firstOrFail();
                $clienteId = $clienteBalcao->id;
            }

            // 3️⃣ Cria a venda
            $totalVenda = collect($request->input('itens', []))
                            ->sum(fn($i) => $i['quantidade'] * $i['valor_unitario']);
            
            $venda = Venda::create([
                'cliente_id'     => $request->input('cliente_id'),
                'funcionario_id' => $request->input('funcionario_id'),
                'caixa_id'       => $request->input('caixa_id'),
                'data_venda'     => $request->input('dataVenda'),
                'endereco'       => $request->input('endereco'),
                'total'          => $totalVenda,
            ]);

            // 4️⃣ Persiste itens da venda
            foreach ($request->input('itens', []) as $item) {
                $venda->itens()->create([
                    'produto_id'     => $item['produto_id'],
                    'lote_id'        => $item['lote_id'] ?? null,
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $item['valor_unitario'],
                ]);
            }

            DB::commit();

            // 5️⃣ Retorna sucesso
            return response()->json([
                'success'  => true,
                'message'  => 'Venda criada com sucesso',
                'venda_id' => $venda->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //pagamentos de venda (pagamentos_venda)
    public function finalizar(Request $request, Venda $venda, CreditoService $creditoService)
    {
        
         // ✅ Pega o cliente direto do objeto Venda
        $cliente = $venda->cliente;

        // ✅ Total da venda calculado pelo backend
        $valorVenda = $venda->itens->sum(fn($i) => $i->quantidade * $i->preco_unitario);

        // ✅ Valida crédito do cliente
        $validacao = $creditoService->validarCredito($cliente, $valorVenda);

        if (!$validacao['aprovado']) {
            return response()->json([
                'success' => false,
                'erro'    => $validacao['mensagem']
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ Todas as formas de pagamento possíveis
            $formasPossiveis = ['dinheiro', 'cartao_credito', 'cartao_debito', 'carteira', 'pix'];

            // 2️⃣ Pagamentos enviados pelo frontend
            $pagamentosEnviados = collect($request->input('pagamentos', []))
                ->keyBy('forma'); // indexa por forma

            $totalPagamentos = 0;
           
            $formasPermitidas = $creditoService->formasPermitidas($cliente);
        // Retorna array como ['dinheiro','cartao_credito','cartao_debito','pix']

        foreach ($formasPossiveis as $forma) {
            $valor = isset($pagamentosEnviados[$forma]) ? (float) $pagamentosEnviados[$forma]['valor'] : 0;

            if (!in_array($forma, $formasPermitidas)) {
                if ($valor > 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'erro'    => "Cliente não possui permissão para usar {$forma}"
                    ], 422);
                }
                $valor = 0; // garante que não cria pagamento indevido
            }

            $totalPagamentos += $valor;

            $venda->pagamentos()->create([
                // 'user_id'         => auth()->id(),
                'user_id' => auth()->id() ?? $venda->funcionario_id,
                'caixa_id'        => $venda->caixa_id,
                'forma_pagamento' => $forma,
                'valor'           => $valor,
                'status'          => 'confirmado',
            ]);
        }
        // 4️⃣ Validação: pagamentos insuficientes
        if ($totalPagamentos < $valorVenda) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'erro'    => "Pagamento insuficiente. Total da venda: R$ {$valorVenda}, total pago: R$ {$totalPagamentos}"
            ], 422);
        }

        // 5️⃣ Atualiza status da venda
        $venda->update([
            'status' => 'finalizada',
            'total'  => $valorVenda,
        ]);

        DB::commit();

        // ✅ Retorna JSON sempre, pronto para o JS
        return response()->json([
            'success'  => true,
            'total'    => $valorVenda,
            'message'  => 'Venda finalizada com sucesso',
            'venda_id' => $venda->id
        ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'erro'    => $e->getMessage()
            ], 500);
        }
    }

    
    //criar qrcode
    // public function gerarPix($venda)
    // {
    //     $payload = new Payload();

    //     $payload->setPixKey('11999999999'); // chave pix da empresa
    //     $payload->setDescription('Venda '.$venda->id);
    //     $payload->setMerchantName('DEPOSITO MATERIAIS');
    //     $payload->setMerchantCity('POA');
    //     $payload->setAmount(number_format($venda->total,2,'.',''));
    //     $payload->setTxid($venda->id);

    //     return $payload->getPayload();
    // }

    //dados da empresa e exibe a tela que imprime cupom das vendas
    public function cupom($id)
    {
        $venda = Venda::with([
            'cliente',
            'itens.produto',
            'pagamentos',
            'funcionario'
        ])->findOrFail($id);

        $empresa = Empresa::where('ativo', 1)->first();

        return view('vendas.cupom', compact('venda','empresa'));
    }
        
    

}
