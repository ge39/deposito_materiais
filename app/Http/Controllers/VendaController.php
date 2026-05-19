<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

use App\Models\Venda;
Use App\Models\Empresa;
use Mguimaraes\Pix\Payload;
use App\Models\Cliente;
use App\Models\Caixa;

use App\Services\CreditoService;


class VendaController extends Controller
{
    /**
     * Store da venda (PDV)
     * Responsabilidade TOTAL do backend
     */

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
                // 'cliente_id'     => $request->input('cliente_id'),
                'cliente_id'     => $clienteId,
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
 
    public function finalizar(Request $request, CreditoService $creditoService) { 
        DB::beginTransaction(); 
        try { 
            // 🔥 Se vier vazio, zero ou o ID 6 da Venda Balcão, define como NULL para o banco 
            $clienteId = !empty($request->cliente_id) && (int)$request->cliente_id > 0 && (int)$request->cliente_id !== 6 ? (int)$request->cliente_id : null; 

            // 1️⃣ Criação da Venda (Agora o MySQL vai aceitar o NULL sem dar o erro 1048) 
            $dadosVenda = [ 
                'cliente_id' => $clienteId, 
                'funcionario_id' => $request->funcionario_id, 
                'caixa_id' => $request->caixa_id, 
                'status' => 'aberta', 
                'total' => 0 
            ]; 

            $dataVendaOriginal = $request->dataVenda ?? now();
            if (\Schema::hasColumn('vendas', 'data_venda')) { 
                $dadosVenda['data_venda'] = $dataVendaOriginal; 
            } 
            $venda = Venda::create($dadosVenda); 

            // 2️⃣ Inserção dos Itens do Carrinho com Algoritmo FIFO Corrigido (Priorizando produto_id)
           // 2️⃣ Inserção dos Itens do Carrinho com Sanitização Antimeta e Algoritmo FIFO Corrigido
            $itens = $request->input('itens', []); 
            $valorVenda = 0; 

            foreach ($itens as $item) { 
                // 🧼 HELPER DE SANITIZAÇÃO DECIMAL
                $limparNumero = function($valor) { 
                    if (is_numeric($valor)) return (float) $valor; 
                    $stringLimpa = preg_replace('/[^\d,.]/', '', $valor); 
                    if (strpos($stringLimpa, ',') !== false && strpos($stringLimpa, '.') !== false) { 
                        $stringLimpa = str_replace(',', '', $stringLimpa); 
                    } else { 
                        $stringLimpa = str_replace(',', '.', $stringLimpa); 
                    } 
                    return (float) $stringLimpa; 
                }; 

                // Aplica a sanitização nos dados brutos vindos do front-end
                $quantidadeNecessaria = $limparNumero($item['quantidade'] ?? $item['qtd'] ?? 1); 
                
                // 🔥 CORREÇÃO CRÍTICA: Adicionado 'valor_unitario' para capturar o preço real do seu print
                $precoUnitario        = $limparNumero($item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? $item['valor'] ?? 0); 
                $desconto             = $limparNumero($item['desconto'] ?? 0.00); 

                // Mapeamento do ID do produto
                $produtoId = $item['produto_id'] ?? $item['id'] ?? null; 

                if (!$produtoId) { 
                    DB::rollBack(); 
                    return response()->json(['success' => false, 'erro' => 'Identificador do produto inválido no carrinho.'], 422); 
                } 

                // Calcula o subtotal e alimenta o acumulador geral de forma garantida no PHP
                $subtotalLocal = ($quantidadeNecessaria * $precoUnitario) - $desconto; 
                $valorVenda += $subtotalLocal; 

                // Busca os lotes ativos com saldo deste produto por ordem FIFO 
                $lotesDisponiveis = DB::table('lotes') 
                    ->where('produto_id', $produtoId) 
                    ->where('quantidade_disponivel', '>', 0) 
                    ->orderBy('created_at', 'asc') 
                    ->lockForUpdate() 
                    ->get(); 

                if ($lotesDisponiveis->isEmpty()) { 
                    // Fallback: Grava com as propriedades resolvidas e lote_id nulo 
                    $venda->itens()->create([ 
                        'venda_id'       => $venda->id, 
                        'produto_id'     => $produtoId, 
                        'lote_id'        => null, 
                        'quantidade'     => (int) $quantidadeNecessaria, 
                        'preco_unitario' => $precoUnitario, 
                        'desconto'       => $desconto 
                    ]); 
                } else { 
                    // Distribui e fragmenta a quantidade nos lotes respeitando o FIFO 
                    foreach ($lotesDisponiveis as $lote) { 
                        if ($quantidadeNecessaria <= 0) break; 
                        $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel); 

                        // Gravação Completa de todas as colunas 
                        $venda->itens()->create([ 
                            'venda_id'       => $venda->id, 
                            'produto_id'     => $produtoId, 
                            'lote_id'        => $lote->id, 
                            'quantidade'     => (int) $qtdConsumir, 
                            'preco_unitario' => $precoUnitario, 
                            'desconto'       => $desconto 
                        ]); 

                        // Atualiza o saldo do lote consumido no banco de dados 
                        DB::table('lotes') 
                            ->where('id', $lote->id) 
                            ->decrement('quantidade_disponivel', $qtdConsumir); 

                        $quantidadeNecessaria -= $qtdConsumir; 
                    } 

                    // Fallback de segurança complementar se a venda superar os lotes 
                    if ($quantidadeNecessaria > 0) { 
                        $venda->itens()->create([ 
                            'venda_id'       => $venda->id, 
                            'produto_id'     => $produtoId, 
                            'lote_id'        => null, 
                            'quantidade'     => (int) $quantidadeNecessaria, 
                            'preco_unitario' => $precoUnitario, 
                            'desconto'       => $desconto 
                        ]); 
                    } 
                } 
            }

            // 3️⃣ CONTROLE DE FLUXO: ISOLAMENTO TOTAL DA VENDA BALCÃO 
            $pagamentosEnviados = collect($request->input('pagamentos', []))->keyBy('forma'); 
            $usaCarteira = $pagamentosEnviados->has('carteira'); 
            $formasPermitidas = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito']; 

            // 🔴 SE FOR NULL (Venda Balcão): Barra a carteira direto sem consultar services 
            if (is_null($clienteId)) { 
                if ($usaCarteira) { 
                    DB::rollBack(); 
                    return response()->json(['success' => false, 'erro' => 'Cliente balcão não pode utilizar pagamento via carteira/crediário.'], 422); 
                } 
            } else { 
                // 🟢 SE FOR CLIENTE CADASTRADO: Executa as validações normais de crédito 
                $cliente = Cliente::with(['creditoAtivo', 'ultimaMovimentacao'])->find($clienteId); 
                if ($cliente) { 
                    if ($usaCarteira) { 
                        if (!$cliente->creditoAtivo) { 
                            DB::rollBack(); 
                            return response()->json(['success' => false, 'erro' => 'Este cliente não possui crediário ativo no sistema.'], 422); 
                        } 
                        $validacao = $creditoService->validarCredito($cliente, $valorVenda, $request->input('pagamentos', [])); 
                        if (!$validacao['aprovado']) { 
                            DB::rollBack(); 
                            return response()->json(['success' => false, 'erro' => $validacao['mensagem']], 422); 
                        } 
                    } 
                    $formasPermitidas = $creditoService->formasPermitidas($cliente, $valorVenda); 
                } 
            } 

            // 4️⃣ Processamento das Formas de Pagamento 
            $formasPossiveis = ['dinheiro', 'cartao_credito', 'cartao_debito', 'carteira', 'pix']; 
            $totalPagamentos = 0; 
            foreach ($formasPossiveis as $forma) { 
                $valor = isset($pagamentosEnviados[$forma]) ? (float) $pagamentosEnviados[$forma]['valor'] : 0; 
                if ($valor <= 0) continue; 

                if (!in_array($forma, $formasPermitidas)) { 
                    DB::rollBack(); 
                    return response()->json(['success' => false, 'erro' => "A forma de pagamento '{$forma}' não está disponível ou autorizada para esta venda"], 422); 
                } 

                $pagamentoSalvo = $venda->pagamentos()->create([ 
                    'user_id' => auth()->id() ?? $venda->funcionario_id ?? $request->funcionario_id, 
                    'caixa_id' => $venda->caixa_id, 
                    'forma_pagamento' => $forma, 
                    'valor' => $valor, 
                    'status' => 'confirmado', 
                ]); 

                if ($forma === 'carteira' && !is_null($clienteId)) { 
                    app(ContaCorrenteService::class)->registrarMovimentacao($pagamentoSalvo); 
                } 
                $totalPagamentos += $valor; 
            } 

            // 5️⃣ Validação Matemática de Suficiência 
            if (($valorVenda - $totalPagamentos) > 0.01) { 
                DB::rollBack(); 
                return response()->json([ 
                    'success' => false, 
                    'erro' => "Pagamento insuficiente. Restante: R$ " . number_format(($valorVenda - $totalPagamentos), 2, ',', '.') 
                ], 422); 
            } 

            // 6️⃣ Consolida o encerramento da venda no banco de dados 
            $dadosUpdate = [ 
                'status' => 'finalizada', 
                'total' => $valorVenda, 
            ];
            if (\Schema::hasColumn('vendas', 'data_venda')) { 
                $dadosUpdate['data_venda'] = $dataVendaOriginal; 
            }
            $venda->update($dadosUpdate); 

            DB::commit(); 

           // 7️⃣ Verificação de regras de Sangria de Caixa
            $caixa = Caixa::find($venda->caixa_id);
            if ($caixa) {
                $verificacao = $caixa->verificarSangria();
                if (!empty($verificacao['bloquearPDV']) || !empty($verificacao['avisarSangria'])) {
                    return response()->json([
                        'success' => true,
                        'redirect_sangria' => true,
                        'url' => route('caixa.sangria.form', $caixa->id),
                        'cupom_url' => route('venda.cupom', $venda->id) // 🔥 Corrigido para 'venda.cupom'
                    ]);
                }
            }

            // 🔥 RETORNO ATUALIZADO: Envia a URL do cupom gerada dinamicamente
            return response()->json([
                'success' => true,
                'total' => $valorVenda,
                'message' => 'Venda finalizada com sucesso',
                'venda_id' => $venda->id,
                'cupom_url' => route('venda.cupom', $venda->id) // 🔥 Rota gerada pelo Laravel
            ]);

            } catch (\Exception $e) { 
                DB::rollBack(); 
                return response()->json([ 
                    'success' => false, 
                    'erro' => 'Erro interno ao processar transação: ' . $e->getMessage() . ' na linha ' . $e->getLine() 
                ], 500); 
        } 
    }

  // dados da empresa e exibe a tela que imprime cupom das vendas
    // dados da empresa e exibe a tela que imprime cupom das vendas
    public function cupom($id) 
    { 
        // 1. Carrega a venda com os relacionamentos originais
        $venda = Venda::with([
            'cliente', 
            'itens.produto', 
            'pagamentos', 
            'funcionario'
        ])->findOrFail($id); 

        // 2. Busca a empresa ativa
        $empresa = Empresa::where('ativo', 1)->first();

        // 3. Cálculos básicos de Totais
        $descontoTotal = $venda->itens->sum('desconto'); 
        $pagoEmDinheiro = $venda->pagamentos->where('forma_pagamento', 'dinheiro')->sum('valor'); 
        $totalPagoGeral = $venda->pagamentos->sum('valor'); 
        $troco = $totalPagoGeral > $venda->total ? ($totalPagoGeral - $venda->total) : 0;

        // 4. PROTEÇÃO DE CAMINHO: Verifica qual pasta realmente existe no seu projeto
        if (view()->exists('vendas.cupom')) {
            $caminhoView = 'vendas.cupom'; // Se sua pasta for "vendas" (Plural)
        } else {
            $caminhoView = 'venda.cupom';  // Se sua pasta for "venda" (Singular)
        }

        // 5. Retorna a view correta com os dados
        return view($caminhoView, compact( 
            'venda', 
            'empresa', 
            'descontoTotal', 
            'pagoEmDinheiro', 
            'troco' 
        )); 
    }

    

}
