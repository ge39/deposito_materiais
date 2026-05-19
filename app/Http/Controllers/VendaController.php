<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // 🔥 Garanta que essa linha esteja no topo do arquivo
use App\Models\Venda;
use App\Models\Empresa;
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

   public function store(Request $request, CreditoService $creditoService)
    {
        // 1️⃣ Resgata e trata o dado bruto do cliente antes da validação para evitar quebras
        $clienteIdRaw = $request->input('cliente_id');
        
        if (empty($clienteIdRaw) || $clienteIdRaw == '6' || strtoupper($clienteIdRaw) === 'VENDA BALCAO') {
            // Se for balcão ou vazio, remove do request para passar na regra 'nullable' da validação
            $request->merge(['cliente_id' => null]);
        }

        // 2️⃣ Validação Original Ajustada
        $request->validate([
            'cliente_id'             => 'nullable|exists:clientes,id', 
            'funcionario_id'         => 'required|exists:users,id',
            'caixa_id'               => 'required|exists:caixas,id',
            'dataVenda'              => 'required|date',
            'endereco'               => 'nullable|string|max:255',
            'itens'                  => 'required|array|min:1',
            'itens.*.produto_id'     => 'required|exists:produtos,id',
            'itens.*.quantidade'     => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 2️⃣ Fallback cliente "VENDA BALCÃO" (ID 6 do seu banco de dados)
            $clienteIdRaw = $request->input('cliente_id');
    
            // Se o front-end mandar vazio, nulo ou o texto "VENDA BALCAO", o PHP força o ID 6 real do seu banco
            if (empty($clienteIdRaw) || $clienteIdRaw == '' || strtoupper($clienteIdRaw) === 'VENDA BALCAO') {
                $clienteId = 6; // 🔥 Força o ID real do seu banco de dados para a Venda Balcão!
            } else {
                $clienteId = (int) $clienteIdRaw; // Se for um cliente cadastrado (ID 2, 3...), usa o ID dele
            }

            // 4️⃣ Cria a venda usando a sua fórmula matemática original limpa
            $totalVenda = collect($request->input('itens', []))
                            ->sum(fn($i) => $i['quantidade'] * $i['valor_unitario']);
            
            $venda = Venda::create([
                'cliente_id'     => $clienteId,
                'funcionario_id' => $request->input('funcionario_id'),
                'caixa_id'       => $request->input('caixa_id'),
                'data_venda'     => $request->input('dataVenda'),
                'endereco'       => $request->input('endereco'),
                'total'          => $totalVenda,
                'status'         => 'finalizada' // Garante o status correto do ciclo
            ]);

            // 5️⃣ Persiste itens da venda e realiza baixa real das quantidades nos lotes (FIFO)
            foreach ($request->input('itens', []) as $item) {
                $produtoId            = $item['produto_id'];
                $quantidadeNecessaria = floatval($item['quantidade']);
                $precoUnitario        = floatval($item['valor_unitario']);

                // Busca os lotes ativos com estoque por ordem cronológica (FIFO)
                $lotesDisponiveis = DB::table('lotes')
                    ->where('produto_id', $produtoId)
                    ->where('quantidade_disponivel', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                if ($lotesDisponiveis->isEmpty()) {
                    // Fallback: Se não houver lotes cadastrados, grava com lote_id nulo
                    $venda->itens()->create([
                        'produto_id'     => $produtoId,
                        'lote_id'        => null,
                        'quantidade'     => $quantidadeNecessaria,
                        'preco_unitario' => $precoUnitario,
                    ]);
                } else {
                    // Distribui a quantidade vendida nos lotes disponíveis
                    foreach ($lotesDisponiveis as $lote) {
                        if ($quantidadeNecessaria <= 0) break;
                        $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel);

                        $venda->itens()->create([
                            'produto_id'     => $produtoId,
                            'lote_id'        => $lote->id,
                            'quantidade'     => $qtdConsumir,
                            'preco_unitario' => $precoUnitario,
                        ]);

                        // 📉 Dá a baixa real e imediata diminuindo o saldo do lote no banco
                        DB::table('lotes')
                            ->where('id', $lote->id)
                            ->decrement('quantidade_disponivel', $qtdConsumir);

                        $quantidadeNecessaria -= $qtdConsumir;
                    }

                    // Se houver estouro de estoque (Venda acima do limite físico dos lotes)
                    if ($quantidadeNecessaria > 0) {
                        $venda->itens()->create([
                            'produto_id'     => $produtoId,
                            'lote_id'        => null,
                            'quantidade'     => $quantidadeNecessaria,
                            'preco_unitario' => $precoUnitario,
                        ]);
                    }
                }
            }

            DB::commit();

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
 
    // public function finalizar(Request $request, CreditoService $creditoService) { 
    //     DB::beginTransaction(); 
    //     try { 
    //         // 🔥 Se vier vazio, zero ou o ID 6 da Venda Balcão, define como NULL para o banco 
    //         $clienteId = !empty($request->cliente_id) && (int)$request->cliente_id > 0 && (int)$request->cliente_id !== 6 ? (int)$request->cliente_id : null; 

    //         // 1️⃣ Criação da Venda (Agora o MySQL vai aceitar o NULL sem dar o erro 1048) 
    //         $dadosVenda = [ 
    //             'cliente_id' => $clienteId, 
    //             'funcionario_id' => $request->funcionario_id, 
    //             'caixa_id' => $request->caixa_id, 
    //             'status' => 'aberta', 
    //             'total' => 0 
    //         ]; 

    //         $dataVendaOriginal = $request->dataVenda ?? now();
    //         if (\Schema::hasColumn('vendas', 'data_venda')) { 
    //             $dadosVenda['data_venda'] = $dataVendaOriginal; 
    //         } 
    //         $venda = Venda::create($dadosVenda); 

    //         // 2️⃣ Inserção dos Itens do Carrinho com Algoritmo FIFO Corrigido (Priorizando produto_id)
    //         // 2️⃣ Inserção dos Itens do Carrinho com Sanitização Antimeta e Algoritmo FIFO Corrigido
    //         $itens = $request->input('itens', []); 
    //         $valorVenda = 0; 

    //         foreach ($itens as $item) { 
    //             // 🧼 HELPER DE SANITIZAÇÃO DECIMAL
    //             $limparNumero = function($valor) { 
    //                 if (is_numeric($valor)) return (float) $valor; 
    //                 $stringLimpa = preg_replace('/[^\d,.]/', '', $valor); 
    //                 if (strpos($stringLimpa, ',') !== false && strpos($stringLimpa, '.') !== false) { 
    //                     $stringLimpa = str_replace(',', '', $stringLimpa); 
    //                 } else { 
    //                     $stringLimpa = str_replace(',', '.', $stringLimpa); 
    //                 } 
    //                 return (float) $stringLimpa; 
    //             }; 

    //             // Aplica a sanitização nos dados brutos vindos do front-end
    //             $quantidadeNecessaria = $limparNumero($item['quantidade'] ?? $item['qtd'] ?? 1); 
                
    //             // 🔥 CORREÇÃO CRÍTICA: Adicionado 'valor_unitario' para capturar o preço real do seu print
    //             $precoUnitario        = $limparNumero($item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? $item['valor'] ?? 0); 
    //             $desconto             = $limparNumero($item['desconto'] ?? 0.00); 

    //             // Mapeamento do ID do produto
    //             $produtoId = $item['produto_id'] ?? $item['id'] ?? null; 

    //             if (!$produtoId) { 
    //                 DB::rollBack(); 
    //                 return response()->json(['success' => false, 'erro' => 'Identificador do produto inválido no carrinho.'], 422); 
    //             } 

    //             // Calcula o subtotal e alimenta o acumulador geral de forma garantida no PHP
    //             $subtotalLocal = ($quantidadeNecessaria * $precoUnitario) - $desconto; 
    //             $valorVenda += $subtotalLocal; 

    //             // Busca os lotes ativos com saldo deste produto por ordem FIFO 
    //             $lotesDisponiveis = DB::table('lotes') 
    //                 ->where('produto_id', $produtoId) 
    //                 ->where('quantidade_disponivel', '>', 0) 
    //                 ->orderBy('created_at', 'asc') 
    //                 ->lockForUpdate() 
    //                 ->get(); 

    //             if ($lotesDisponiveis->isEmpty()) { 
    //                 // Fallback: Grava com as propriedades resolvidas e lote_id nulo 
    //                 $venda->itens()->create([ 
    //                     'venda_id'       => $venda->id, 
    //                     'produto_id'     => $produtoId, 
    //                     'lote_id'        => null, 
    //                     'quantidade'     => (int) $quantidadeNecessaria, 
    //                     'preco_unitario' => $precoUnitario, 
    //                     'desconto'       => $desconto 
    //                 ]); 
    //             } else { 
    //                 // Distribui e fragmenta a quantidade nos lotes respeitando o FIFO 
    //                 foreach ($lotesDisponiveis as $lote) { 
    //                     if ($quantidadeNecessaria <= 0) break; 
    //                     $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel); 

    //                     // Gravação Completa de todas as colunas 
    //                     $venda->itens()->create([ 
    //                         'venda_id'       => $venda->id, 
    //                         'produto_id'     => $produtoId, 
    //                         'lote_id'        => $lote->id, 
    //                         'quantidade'     => (int) $qtdConsumir, 
    //                         'preco_unitario' => $precoUnitario, 
    //                         'desconto'       => $desconto 
    //                     ]);

    //                     // 📉 Realiza a baixa do estoque físico na tabela de lotes
    //                     DB::table('lotes')
    //                         ->where('id', $lote->id)
    //                         ->decrement('quantidade_disponivel', $qtdConsumir);

    //                     $quantidadeNecessaria -= $qtdConsumir;
    //                 }

    //                 // Se passar por todos os lotes e ainda restar quantidade necessária (Estoque insuficiente/negativo)
    //                 if ($quantidadeNecessaria > 0) {
    //                     $venda->itens()->create([ 
    //                         'venda_id'       => $venda->id, 
    //                         'produto_id'     => $produtoId, 
    //                         'lote_id'        => null, 
    //                         'quantidade'     => (int) $quantidadeNecessaria, 
    //                         'preco_unitario' => $precoUnitario, 
    //                         'desconto'       => $desconto 
    //                     ]);
    //                 }
    //             } 
    //         } 

    //         // 3️⃣ CONTROLE DE FLUXO: ISOLAMENTO TOTAL DA VENDA BALCÃO 
    //         $pagamentosEnviados = collect($request->input('pagamentos', []))->keyBy('forma'); 
    //         $usaCarteira = $pagamentosEnviados->has('carteira'); 
    //         $formasPermitidas = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito']; 

    //         // 🔴 SE FOR NULL (Venda Balcão): Barra a carteira direto sem consultar services 
    //         if (is_null($clienteId)) { 
    //             if ($usaCarteira) { 
    //                 DB::rollBack(); 
    //                 return response()->json(['success' => false, 'erro' => 'Cliente balcão não pode utilizar pagamento via carteira/crediário.'], 422); 
    //             } 
    //         } else { 
    //             // 🟢 SE FOR CLIENTE CADASTRADO: Executa as validações normais de crédito 
    //             $cliente = Cliente::with(['creditoAtivo', 'ultimaMovimentacao'])->find($clienteId); 
    //             if ($cliente) { 
    //                 if ($usaCarteira) { 
    //                     if (!$cliente->creditoAtivo) { 
    //                         DB::rollBack(); 
    //                         return response()->json(['success' => false, 'erro' => 'Este cliente não possui crediário ativo no sistema.'], 422); 
    //                     } 
    //                     $validacao = $creditoService->validarCredito($cliente, $valorVenda, $request->input('pagamentos', [])); 
    //                     if (!$validacao['aprovado']) { 
    //                         DB::rollBack(); 
    //                         return response()->json(['success' => false, 'erro' => $validacao['mensagem']], 422); 
    //                     } 
    //                 } 
    //                 $formasPermitidas = $creditoService->formasPermitidas($cliente, $valorVenda); 
    //             } 
    //         } 

    //         // 4️⃣ Processamento das Formas de Pagamento 
    //         $formasPossiveis = ['dinheiro', 'cartao_credito', 'cartao_debito', 'carteira', 'pix']; 
    //         $totalPagamentos = 0; 
    //         foreach ($formasPossiveis as $forma) { 
    //             $valor = isset($pagamentosEnviados[$forma]) ? (float) $pagamentosEnviados[$forma]['valor'] : 0; 
    //             if ($valor <= 0) continue; 

    //             if (!in_array($forma, $formasPermitidas)) { 
    //                 DB::rollBack(); 
    //                 return response()->json(['success' => false, 'erro' => "A forma de pagamento '{$forma}' não está disponível ou autorizada para esta venda"], 422); 
    //             } 

    //             $pagamentoSalvo = $venda->pagamentos()->create([ 
    //                 'user_id' => auth()->id() ?? $venda->funcionario_id ?? $request->funcionario_id, 
    //                 'caixa_id' => $venda->caixa_id, 
    //                 'forma_pagamento' => $forma, 
    //                 'valor' => $valor, 
    //                 'status' => 'confirmado', 
    //             ]); 

    //             if ($forma === 'carteira' && !is_null($clienteId)) { 
    //                 app(\App\Services\ContaCorrenteService::class)->registrarMovimentacao($pagamentoSalvo); 
    //             } 
    //             $totalPagamentos += $valor; 
    //         } 

    //         // 5️⃣ Validação Matemática de Suficiência 
    //         if (($valorVenda - $totalPagamentos) > 0.01) { 
    //             DB::rollBack(); 
    //             return response()->json([ 
    //                 'success' => false, 
    //                 'erro' => "Pagamento insuficiente. Restante: R$ " . number_format(($valorVenda - $totalPagamentos), 2, ',', '.') 
    //             ], 422); 
    //         } 

    //         // 6️⃣ Consolida o encerramento da venda no banco de dados 
    //         $dadosUpdate = [ 
    //             'status' => 'finalizada', 
    //             'total' => $valorVenda, 
    //         ];
    //         if (\Schema::hasColumn('vendas', 'data_venda')) { 
    //             $dadosUpdate['data_venda'] = $dataVendaOriginal; 
    //         }
    //         $venda->update($dadosUpdate); 

    //         DB::commit(); 

    //        // 7️⃣ Verificação de regras de Sangria de Caixa
    //         $caixa = Caixa::find($venda->caixa_id);
    //         if ($caixa) {
    //             $verificacao = $caixa->verificarSangria();
    //             if (!empty($verificacao['bloquearPDV']) || !empty($verificacao['avisarSangria'])) {
    //                 return response()->json([
    //                     'success' => true,
    //                     'redirect_sangria' => true,
    //                     'url' => route('caixa.sangria.form', $caixa->id),
    //                     'cupom_url' => route('venda.cupom', $venda->id) 
    //                 ]);
    //             }
    //         }

    //         // 🔥 RETORNO ATUALIZADO: Envia a URL do cupom gerada dinamicamente
    //         return response()->json([
    //             'success' => true,
    //             'total' => $valorVenda,
    //             'message' => 'Venda finalizada com sucesso',
    //             'venda_id' => $venda->id,
    //             'cupom_url' => route('venda.cupom', $venda->id) 
    //         ]);

    //         } catch (\Exception $e) { 
    //             DB::rollBack(); 
    //             return response()->json([ 
    //                 'success' => false, 
    //                 'erro' => 'Erro interno ao processar transação: ' . $e->getMessage() . ' na linha ' . $e->getLine() 
    //             ], 500); 
    //     } 
    // }

    public function finalizar(Request $request, CreditoService $creditoService) 
    { 
        DB::beginTransaction(); 
        try { 
            // 🔥 Define NULL para Venda Balcão (ID 6 ou vazio) evitando erro 1048 no MySQL
            $clienteId = !empty($request->cliente_id) && (int)$request->cliente_id > 0 && (int)$request->cliente_id !== 6 
                ? (int)$request->cliente_id 
                : null; 

            // 1️⃣ Criação Inicial da Venda (Status aberta e total zerado temporariamente)
            $dadosVenda = [ 
                'cliente_id'     => $clienteId, 
                'funcionario_id' => $request->funcionario_id, 
                'caixa_id'       => $request->caixa_id, 
                'status'         => 'aberta', 
                'total'          => 0 
            ]; 

            $dataVendaOriginal = $request->dataVenda ?? now();
            if (\Schema::hasColumn('vendas', 'data_venda')) { 
                $dadosVenda['data_venda'] = $dataVendaOriginal; 
            } 
            $venda = Venda::create($dadosVenda); 

            // 2️⃣ Processamento dos Itens do Carrinho (Algoritmo FIFO)
            $itens = $request->input('itens', []); 
            $valorVenda = 0; 

            // Helper isolado para limpar e sanitizar valores numéricos recebidos
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

            foreach ($itens as $item) { 
                $quantidadeNecessaria = $limparNumero($item['quantidade'] ?? $item['qtd'] ?? 1); 
                $precoUnitario        = $limparNumero($item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? $item['valor'] ?? 0); 
                $desconto             = $limparNumero($item['desconto'] ?? 0.00); 
                $produtoId            = $item['produto_id'] ?? $item['id'] ?? null; 

                if (!$produtoId) { 
                    DB::rollBack(); 
                    return response()->json(['success' => false, 'erro' => 'Identificador do produto inválido no carrinho.'], 422); 
                } 

                // 🔥 PROTEÇÃO: Se o preço veio zerado do front-end, busca o valor original na tabela de produtos
                if ($precoUnitario <= 0) {
                    $produtoBanco = DB::table('produtos')->where('id', $produtoId)->first();
                    $precoUnitario = $produtoBanco ? (float) $produtoBanco->preco_venda : 0.00;
                }

                // Calcula o subtotal deste item e acumula no total geral da venda
                $subtotalLocal = ($quantidadeNecessaria * $precoUnitario) - $desconto; 
                $valorVenda += $subtotalLocal; 

                // Busca os lotes ativos com saldo do produto ordenados por data (FIFO)
                $lotesDisponiveis = DB::table('lotes') 
                    ->where('produto_id', $produtoId) 
                    ->where('quantidade_disponivel', '>', 0) 
                    ->orderBy('created_at', 'asc') 
                    ->lockForUpdate() 
                    ->get(); 

                if ($lotesDisponiveis->isEmpty()) { 
                    // Fallback: Se não houver lotes cadastrados, grava o item sem lote_id
                    $venda->itens()->create([ 
                        'venda_id'       => $venda->id, 
                        'produto_id'     => $produtoId, 
                        'lote_id'        => null, 
                        'quantidade'     => (int) $quantidadeNecessaria, 
                        'preco_unitario' => $precoUnitario, 
                        'desconto'       => $desconto 
                    ]); 
                } else { 
                    // Fragmenta e consome as quantidades dos lotes disponíveis
                    foreach ($lotesDisponiveis as $lote) { 
                        if ($quantidadeNecessaria <= 0) break; 
                        $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel); 

                        $venda->itens()->create([ 
                            'venda_id'       => $venda->id, 
                            'produto_id'     => $produtoId, 
                            'lote_id'        => $lote->id, 
                            'quantidade'     => (int) $qtdConsumir, 
                            'preco_unitario' => $precoUnitario, 
                            'desconto'       => $desconto 
                        ]);

                        // 📉 Dá baixa automática na quantidade disponível do lote
                        DB::table('lotes')
                            ->where('id', $lote->id)
                            ->decrement('quantidade_disponivel', $qtdConsumir);

                        $quantidadeNecessaria -= $qtdConsumir;
                    }

                    // Se o estoque dos lotes acabar e ainda restar quantidade vendida (Estoque Negativo)
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

            // 🔥 CORREÇÃO CRÍTICA: Atualiza o campo total na tabela 'vendas' com a soma de todos os itens
            $venda->update(['total' => $valorVenda]);

            // 3️⃣ Controle de Fluxo e Regras de Pagamento / Carteira
            $pagamentosEnviados = collect($request->input('pagamentos', []))->keyBy('forma'); 
            $usaCarteira = $pagamentosEnviados->has('carteira'); 

            // Bloqueio de uso de carteira para Venda Balcão (Cliente nulo)
            if (is_null($clienteId)) { 
                if ($usaCarteira) { 
                    DB::rollBack(); 
                    return response()->json(['success' => false, 'erro' => 'Cliente balcão não pode utilizar pagamento via carteira/crediário.'], 422); 
                } 
            } else { 
                // Validações para Clientes Cadastrados
                $cliente = Cliente::with(['creditoAtivo', 'ultimaMovimentacao'])->find($clienteId); 
                
                if ($cliente) { 
                    if ($usaCarteira) { 
                        if (!$cliente->creditoAtivo || $cliente->creditoAtivo->status !== 'Ativo') {
                            DB::rollBack();
                            return response()->json(['success' => false, 'erro' => 'O cliente não possui cadastro de crédito ativo.'], 422);
                        }

                        $saldoDisponivel = (float) $cliente->creditoAtivo->saldo;
                        $valorCarteira = $limparNumero($pagamentosEnviados->get('carteira')['valor'] ?? 0);

                        if ($saldoDisponivel < $valorCarteira) {
                            DB::rollBack();
                            return response()->json(['success' => false, 'erro' => 'Saldo em carteira insuficiente para esta transação.'], 422);
                        }

                        // Processa o débito na carteira utilizando o seu Service dedicado
                        $creditoService->debitar($cliente, $valorCarteira, "Venda ID: {$venda->id}");
                    }
                } else {
                    DB::rollBack();
                    return response()->json(['success' => false, 'erro' => 'Cliente informado não foi encontrado.'], 422);
                }
            } 

            // 4️⃣ Finalização da Venda
            $venda->update(['status' => 'finalizada']);
            
            DB::commit(); 
            return response()->json(['success' => true, 'venda_id' => $venda->id, 'total' => $valorVenda]);

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return response()->json(['success' => false, 'erro' => 'Erro interno ao finalizar venda: ' . $e->getMessage()], 500); 
        } 
    }

    // dados da empresa e exibe a tela que imprime cupom das vendas
    public function cupom($id) 
    { 
        // 1. Carrega a venda com os relacionamentos originais
        $venda = Venda::with([
            'cliente', 
            'itens.produto.unidadeMedida', // Carrega relacionamento de medidas para evitar zeros na impressão
            'itens.lote',                  // Carrega relacionamento de lotes para evitar zeros na impressão
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
            $caminhoView = 'vendas.cupom'; 
        } else {
            $caminhoView = 'venda.cupom';  
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
