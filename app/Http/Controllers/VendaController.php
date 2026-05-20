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
    public function finalizar(Request $request, CreditoService $creditoService) { 
        DB::beginTransaction(); 
        try { 
            // 🔥 A SUA IDÉIA DO FILTRO INVERSO DO CLIENTE:
            $clienteIdRaw = $request->input('cliente_id'); 
            if (empty($clienteIdRaw) || $clienteIdRaw == '' || strtoupper($clienteIdRaw) === 'VENDA BALCAO') {
                $clienteBalcao = DB::table('clientes')
                                    ->where('nome', 'LIKE', '%VENDA BALCAO%')
                                    ->where('ativo', 1)
                                    ->first();
                $clienteId = $clienteBalcao ? $clienteBalcao->id : 6; // Fallback seguro ID 6
            } else {
                $clienteId = (int) $clienteIdRaw;
            }

            // 1️⃣ Criação da Venda (Capturando o caixa_id 310 correto)
            $caixaId = (int) $request->input('caixa_id');

            $dadosVenda = [ 
                'cliente_id'     => $clienteId, 
                'funcionario_id' => $request->input('funcionario_id'), 
                'caixa_id'       => $caixaId, 
                'status'         => 'aberta', 
                'total'          => 0 
            ]; 

            $dataVendaOriginal = $request->dataVenda ?? now();
            if (\Schema::hasColumn('vendas', 'data_venda')) { 
                $dadosVenda['data_venda'] = $dataVendaOriginal; 
            } 
            $venda = Venda::create($dadosVenda); 

            // 2️⃣ Inserção dos Itens do Carrinho com Algoritmo FIFO (Sua lógica original)
            $itens = $request->input('itens', []); 
            $valorVenda = 0; 

            // Helper de Sanitização Decimal
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

                // Se o preço vier zerado, busca o preço padrão do cadastro para não zerar a venda
                if ($precoUnitario <= 0) {
                    $produtoBanco = DB::table('produtos')->where('id', $produtoId)->first();
                    $precoUnitario = $produtoBanco ? (float) $produtoBanco->preco_venda : 0.00;
                }

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
                    $venda->itens()->create([ 
                        'venda_id'       => $venda->id, 
                        'produto_id'     => $produtoId, 
                        'lote_id'        => null, 
                        'quantidade'     => (int) $quantidadeNecessaria, 
                        'preco_unitario' => $precoUnitario, 
                        'desconto'       => $desconto 
                    ]); 
                } else { 
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

                        // 📉 REALIZA A BAIXA DE ESTOQUE NO LOTE CORRESPONDENTE
                        DB::table('lotes')
                            ->where('id', $lote->id)
                            ->decrement('quantidade_disponivel', $qtdConsumir);

                        $quantidadeNecessaria -= $qtdConsumir;
                    }

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

        // ========================================================
        // 🔥 CORREÇÃO TOTAL DA VENDA: Atualiza com o valor real acumulado
        // ========================================================
        $venda->update(['total' => $valorVenda]);

        // ========================================================
        // 🔥 AJUSTE DEFINITIVO: PERSISTÊNCIA NA TABELA movimentacoes_caixa
        // ========================================================
        // Formas que o seu banco e a sua View esperam receber como texto
        $formasPagamentoPermitidas = ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'carteira'];

        foreach ($formasPagamentoPermitidas as $forma) {
            // Captura o valor tratando strings, vírgulas ou pontos vindos do FormData do JS
            $valorRaw = $request->input($forma, 0);
            $valorPago = is_numeric($valorRaw) ? floatval($valorRaw) : floatval(str_replace(',', '.', $valorRaw));
            
            if ($valorPago > 0) {
                DB::table('movimentacoes_caixa')->insert([
                    'caixa_id'          => $caixaId,
                    'auditoria_id'      => null,
                    'user_id'           => (int) $venda->funcionario_id, 
                    'tipo'              => 'venda', 
                    'forma_pagamento'   => $forma,  // Mantido como string padrão conforme seu banco varchar(25)
                    'valor'             => $valorPago,
                    'valor_auditado'    => 0.00,
                    'bandeira'          => null,
                    'origem_id'         => (int) $venda->id, // 👈 CORRIGIDO: Agora aponta para o ID da VENDA real!
                    'observacao'        => "Venda PDV #" . $venda->id,
                    'data_movimentacao' => now(), 
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }


        // 3️⃣ CONTROLE DE FLUXO: VALIDAÇÕES DE PAGAMENTO EM CARTEIRA
        // (O restante do seu código de validação de Carteira e o DB::commit() continuam exatamente iguais abaixo...)


            // 3️⃣ CONTROLE DE FLUXO: VALIDAÇÕES DE PAGAMENTO EM CARTEIRA
            $pagamentosEnviados = collect($request->input('pagamentos', []))->keyBy('forma'); 
            $usaCarteira = $pagamentosEnviados->has('carteira') || floatval($request->input('carteira', 0)) > 0; 

            if (is_null($venda->cliente_id) || $venda->cliente_id == 6) { 
                if ($usaCarteira) { 
                    DB::rollBack(); 
                    return response()->json(['success' => false, 'erro' => 'Cliente balcão não pode utilizar pagamento via carteira/crediário.'], 422); 
                } 
            } else { 
                // Validações para Clientes Cadastrados
                $cliente = Cliente::with(['creditoAtivo', 'ultimaMovimentacao'])->find($venda->cliente_id); 
                if ($cliente && $usaCarteira) { 
                    if (!$cliente->creditoAtivo || $cliente->creditoAtivo->status !== 'Ativo') {
                        DB::rollBack();
                        return response()->json(['success' => false, 'erro' => 'O cliente não possui cadastro de crédito ativo.'], 422);
                    }

                    $saldoDisponivel = (float) $cliente->creditoAtivo->saldo;
                    $valorCarteira = floatval($request->input('carteira', 0));

                    if ($saldoDisponivel < $valorCarteira) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'erro' => 'Saldo em carteira insuficiente para esta transação.'], 422);
                    }

                    // Processa o débito na carteira do cliente
                    $creditoService->debitar($cliente, $valorCarteira, "Venda ID: {$venda->id}");
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

    // public function finalizar(Request $request, CreditoService $creditoService) { 
    //     DB::beginTransaction(); 
    //     try { 
    //         // 1️⃣ Mapeamento e Filtro Inverso do Cliente (Venda Balcão)
    //         $clienteIdRaw = $request->input('cliente_id'); 
    //         if (empty($clienteIdRaw) || $clienteIdRaw == '' || strtoupper($clienteIdRaw) === 'VENDA BALCAO') {
    //             $clienteBalcao = DB::table('clientes')
    //                                 ->where('nome', 'LIKE', '%VENDA BALCAO%')
    //                                 ->where('ativo', 1)
    //                                 ->first();
    //             $clienteId = $clienteBalcao ? $clienteBalcao->id : 6; 
    //         } else {
    //             $clienteId = (int) $clienteIdRaw;
    //         }

    //         $caixaId = (int) $request->input('caixa_id');
    //         $funcionarioId = (int) $request->input('funcionario_id');

    //         // 2️⃣ Criação Inicial do Cabeçalho da Venda
    //         $dadosVenda = [ 
    //             'cliente_id'     => $clienteId, 
    //             'funcionario_id' => $funcionarioId, 
    //             'caixa_id'       => $caixaId, 
    //             'status'         => 'finalizada', 
    //             'total'          => 0 
    //         ]; 

    //         if (\Schema::hasColumn('vendas', 'data_venda')) { 
    //             $dadosVenda['data_venda'] = $request->input('dataVenda') ?? now(); 
    //         } 
    //         $venda = Venda::create($dadosVenda); 

    //         // 3️⃣ Processamento dos Itens do Carrinho (Algoritmo FIFO)
    //         $itens = $request->input('itens', []); 
    //         $valorVenda = 0; 

    //         $limparNumero = function($valor) { 
    //             if (is_numeric($valor)) return (float) $valor; 
    //             $stringLimpa = preg_replace('/[^\d,.]/', '', $valor); 
    //             if (strpos($stringLimpa, ',') !== false && strpos($stringLimpa, '.') !== false) { 
    //                 $stringLimpa = str_replace(',', '', $stringLimpa); 
    //             } else { 
    //                 $stringLimpa = str_replace(',', '.', $stringLimpa); 
    //             } 
    //             return (float) $stringLimpa; 
    //         }; 

    //         foreach ($itens as $item) { 
    //             $quantidadeNecessaria = $limparNumero($item['quantidade'] ?? $item['qtd'] ?? 1); 
    //             $precoUnitario        = $limparNumero($item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? 0); 
    //             $desconto             = $limparNumero($item['desconto'] ?? 0.00); 
    //             $produtoId            = $item['produto_id'] ?? $item['id'] ?? null; 

    //             if (!$produtoId) { 
    //                 DB::rollBack(); 
    //                 return response()->json(['success' => false, 'erro' => 'Identificador do produto inválido.'], 422); 
    //             } 

    //             if ($precoUnitario <= 0) {
    //                 $produtoBanco = DB::table('produtos')->where('id', $produtoId)->first();
    //                 $precoUnitario = $produtoBanco ? (float) $produtoBanco->preco_venda : 0.00;
    //             }

    //             $subtotalLocal = ($quantidadeNecessaria * $precoUnitario) - $desconto; 
    //             $valorVenda += $subtotalLocal; 

    //             $lotesDisponiveis = DB::table('lotes') 
    //                 ->where('produto_id', $produtoId) 
    //                 ->where('quantidade_disponivel', '>', 0) 
    //                 ->orderBy('created_at', 'asc') 
    //                 ->lockForUpdate() 
    //                 ->get(); 

    //             if ($lotesDisponiveis->isEmpty()) { 
    //                 $venda->itens()->create([ 
    //                     'produto_id'     => $produtoId, 
    //                     'lote_id'        => null, 
    //                     'quantidade'     => $quantidadeNecessaria, 
    //                     'preco_unitario' => $precoUnitario, 
    //                     'desconto'       => $desconto 
    //                 ]); 
    //             } else { 
    //                 foreach ($lotesDisponiveis as $lote) { 
    //                     if ($quantidadeNecessaria <= 0) break; 
    //                     $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel); 

    //                     $venda->itens()->create([ 
    //                         'produto_id'     => $produtoId, 
    //                         'lote_id'        => $lote->id, 
    //                         'quantidade'     => $qtdConsumir, 
    //                         'preco_unitario' => $precoUnitario, 
    //                         'desconto'       => $desconto 
    //                     ]);

    //                     DB::table('lotes')->where('id', $lote->id)->decrement('quantidade_disponivel', $qtdConsumir);
    //                     $quantidadeNecessaria -= $qtdConsumir;
    //                 }

    //                 if ($quantidadeNecessaria > 0) {
    //                     $venda->itens()->create([ 
    //                         'produto_id'     => $produtoId, 
    //                         'lote_id'        => null, 
    //                         'quantidade'     => $quantidadeNecessaria, 
    //                         'preco_unitario' => $precoUnitario, 
    //                         'desconto'       => $desconto 
    //                     ]);
    //                 }
    //             } 
    //         } 

    //         // Atualiza o valor real finalizado da venda
    //         $venda->update(['total' => $valorVenda]);

    //         // ========================================================
    //         // 🔥 CORREÇÃO SINCRO: CAPTURA EXATA DO ARRAY 'pagamentos' DO JS
    //         // ========================================================
    //         $pagamentosEnviados = collect($request->input('pagamentos', []))->keyBy('forma');
    //         $formasPermitidas = ['dinheiro', 'cartao_credito', 'cartao_debito', 'pix', 'carteira'];
    //         $valorCarteira = 0.00;

    //         foreach ($formasPermitidas as $forma) {
    //             $valorPago = 0.00;

    //             // Busca pelo índice 'forma' mapeado do seu código JavaScript
    //             if ($pagamentosEnviados->has($forma)) {
    //                 $valorPago = $limparNumero($pagamentosEnviados->get($forma)['valor'] ?? 0);
    //             }

    //             if ($forma === 'carteira') {
    //                 $valorCarteira = $valorPago;
    //             }

    //             if ($valorPago > 0) {
    //                 // Criação via Model respeitando as travas de validação
    //                 \App\Models\MovimentacaoCaixa::create([
    //                     'caixa_id'          => $caixaId,
    //                     'auditoria_id'      => null,
    //                     'user_id'           => $funcionarioId, 
    //                     'tipo'              => 'venda', 
    //                     'forma_pagamento'   => $forma,  
    //                     'valor'             => $valorPago,
    //                     'valor_auditado'    => null, 
    //                     'bandeira'          => null,
    //                     'origem_id'         => (int) $venda->id, 
    //                     'observacao'        => "Venda PDV #" . $venda->id,
    //                     'data_movimentacao' => now(), 
    //                 ]);
    //             }
    //         }

    //         // 4️⃣ Executa a verificação de crediário/carteira se necessário
    //         if ($valorCarteira > 0) {
    //             $creditoService->lancarDebitoCliente($clienteId, $valorCarteira, $venda->id);
    //         }

    //         // 5️⃣ INTEGRAÇÃO COM AS REGRAS DE SANGRIA E REDIRECIONAMENTO DO SEU MODEL
    //         $caixa = Caixa::findOrFail($caixaId);
    //         $dadosSangria = $caixa->verificarSangria(); // Chama o método do seu model!

    //         DB::commit(); 

    //         // Resposta JSON estruturada perfeitamente para satisfazer o seu Fetch API
    //         return response()->json([
    //             'success'          => true,
    //             'message'          => 'Venda processada com sucesso!',
    //             'venda_id'         => $venda->id,
    //             'cupom_url'        => route('vendas.cupom', $venda->id), // Altere se o nome da sua rota de cupom for diferente
    //             'redirect_sangria' => $dadosSangria['bloquearPDV'],
    //             'url'              => route('sangrias.lancar', ['caixa' => $caixaId]) // Rota para onde o operador deve ir sangrar
    //         ], 200);

    //     } catch (\Exception $e) { 
    //         DB::rollBack(); 
    //         return response()->json([
    //             'success' => false, 
    //             'erro'    => 'Erro interno no servidor: ' . $e->getMessage()
    //         ], 500); 
    //     } 
    // }



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
