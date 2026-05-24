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

    public function finalizar(Request $request, CreditoService $creditoService) 
    { 
        // Helper de Sanitização Decimal
        $limparNumero = function($valor) { 
            if (is_numeric($valor)) return (float) $valor; 
            $stringLimpa = preg_replace('/[^\d,.]/', '', $valor); 
            return (float) (strpos($stringLimpa, ',') !== false && strpos($stringLimpa, '.') !== false 
                ? str_replace(',', '', $stringLimpa) 
                : str_replace(',', '.', $stringLimpa));
        };

        DB::beginTransaction(); 
        try { 
            // 1️⃣ Define Cliente e Caixa
            $clienteId = $this->buscarOuDefinirCliente($request->input('cliente_id'));
            $caixaId = (int) $request->input('caixa_id');
            // 🔥 DECLARAÇÃO DA VARIÁVEL CORRETA (Com o "$" e o nome exato)
            $funcionarioId = auth()->id() ?? $request->input('funcionario_id') ?? $request->input('funciona_id') ?? 00;

            // 2️⃣ Instancia a Venda
            $dadosVenda = [ 
                'cliente_id'     => $clienteId, 
                'funcionario_id' => $funcionarioId, // 🔥 Correção do mapeamento da coluna do banco
                'caixa_id'       => $caixaId, 
                'status'         => 'aberta', 
                'total'          => 0,
                'data_venda'     => $request->dataVenda ?? now()
            ]; 
            $venda = Venda::create($dadosVenda); 

            // 3️⃣ Executa Itens e FIFO (Filho)
            $valorTotal = $this->processarItensVenda($venda, $request->input('itens', []), $limparNumero);
            $venda->update(['total' => $valorTotal]);

            // 4️⃣ Executa os Pagamentos (Filho)
            $userId = auth()->id() ?? $request->input('user_id', 1);
            $pagamentos = $this->pagamentos_venda($venda->id, $request, $userId, $limparNumero);

            // 5️⃣ Executa Caixa e Crediário (Filho)
            $this->finalizarFluxoFinanceiro($venda, $pagamentos, $caixaId, $creditoService);

            // 6️⃣ Fecha a venda
            $venda->update(['status' => 'finalizada']);

            DB::commit(); 
            return response()->json(['success' => true, 'venda_id' => $venda->id], 200);

        } catch (\Exception $e) { 
            DB::rollBack(); 
            return response()->json(['success' => false, 'erro' => $e->getMessage()], 422); 
        } 
    }

        /**
     * Define ou localiza o cliente correto para a venda.
     */
    private function buscarOuDefinirCliente($clienteIdRaw): int
    {
        if (empty($clienteIdRaw) || strtoupper($clienteIdRaw) === 'VENDA BALCAO') {
            $clienteBalcao = DB::table('clientes')
                                ->where('nome', 'LIKE', '%VENDA BALCAO%')
                                ->where('ativo', 1)
                                ->first();
            return $clienteBalcao ? $clienteBalcao->id : 6;
        }
        return (int) $clienteIdRaw;
    }

        /**
     * Processa a baixa de estoque dos itens utilizando o algoritmo FIFO.
     */
    // private function processarItensVenda(Venda $venda, array $itens, $limparNumero): float
    // {
    //     $valorTotalVenda = 0;

    //     foreach ($itens as $item) {
    //         $quantidadeNecessaria = $limparNumero($item['quantidade'] ?? $item['qtd'] ?? 1);
    //         $precoUnitario        = $limparNumero($item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? $item['valor'] ?? 0);
    //         $desconto             = $limparNumero($item['desconto'] ?? 0.00);
    //         $produtoId            = $item['produto_id'] ?? $item['id'] ?? null;

    //         if (!$produtoId) {
    //             throw new \InvalidArgumentException('Identificador do produto inválido no carrinho.');
    //         }

    //         if ($precoUnitario <= 0) {
    //             $produtoBanco = DB::table('produtos')->where('id', $produtoId)->first();
    //             $precoUnitario = $produtoBanco ? (float) $produtoBanco->preco_venda : 0.00;
    //         }

    //         $valorTotalVenda += ($quantidadeNecessaria * $precoUnitario) - $desconto;

    //         // 🚀 OTIMIZAÇÃO CRÍTICA: Removido lockForUpdate() para impedir o travamento em cascata entre os caixas
    //         $lotes = DB::table('lotes')
    //             ->where('produto_id', $produtoId)
    //             ->where('quantidade_disponivel', '>', 0)
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         if ($lotes->isEmpty()) {
    //             $venda->itens()->create([
    //                 'produto_id'     => $produtoId,
    //                 'lote_id'        => null,
    //                 'quantidade'     => $quantidadeNecessaria,
    //                 'preco_unitario' => $precoUnitario,
    //                 'desconto'       => $desconto
    //             ]);
    //         } else {
    //             foreach ($lotes as $lote) {
    //                 if ($quantidadeNecessaria <= 0) break;
    //                 $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel);

    //                 $venda->itens()->create([
    //                     'produto_id'     => $produtoId,
    //                     'lote_id'        => $lote->id,
    //                     'quantidade'     => $qtdConsumir,
    //                     'preco_unitario' => $precoUnitario,
    //                     'desconto'       => $desconto
    //                 ]);

    //                 // O decrement opera de forma isolada e segura a nível de banco
    //                 DB::table('lotes')->where('id', $lote->id)->decrement('quantidade_disponivel', $qtdConsumir);
    //                 $quantidadeNecessaria -= $qtdConsumir;
    //             }

    //             if ($quantidadeNecessaria > 0) {
    //                 $venda->itens()->create([
    //                     'produto_id'     => $produtoId,
    //                     'lote_id'        => null,
    //                     'quantidade'     => $quantidadeNecessaria,
    //                     'preco_unitario' => $precoUnitario,
    //                     'desconto'       => $desconto
    //                 ]);
    //             }
    //         }
    //     }

    //     return $valorTotalVenda;
    // }

        /**
     * Processa a baixa de estoque dos itens utilizando o algoritmo FIFO.
     * Protegido contra concorrência multi-lote via Lock Cirúrgico de milissegundos.
     */
    private function processarItensVenda(Venda $venda, array $itens, $limparNumero): float
    {
        $valorTotalVenda = 0;

        foreach ($itens as $item) {
            $quantidadeNecessaria = $limparNumero($item['quantidade'] ?? $item['qtd'] ?? 1);
            $precoUnitario        = $limparNumero($item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? $item['valor'] ?? 0);
            $desconto             = $limparNumero($item['desconto'] ?? 0.00);
            $produtoId            = $item['produto_id'] ?? $item['id'] ?? null;

            if (!$produtoId) {
                throw new \InvalidArgumentException('Identificador do produto inválido no carrinho.');
            }

            if ($precoUnitario <= 0) {
                $produtoBanco = DB::table('produtos')->where('id', $produtoId)->first();
                $precoUnitario = $produtoBanco ? (float) $produtoBanco->preco_venda : 0.00;
            }

            $valorTotalVenda += ($quantidadeNecessaria * $precoUnitario) - $desconto;

            // 🔒 LOCK CIRÚRGICO: Trava apenas as linhas dos lotes ativos deste produto.
            // Como a venda agora é gravada em um único passo no controller, esse lock dura apenas
            // alguns milissegundos, garantindo o FIFO perfeito sem congelar as outras frentes de caixa.
            $lotes = DB::table('lotes')
                ->where('produto_id', $produtoId)
                ->where('quantidade_disponivel', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate() // 🔥 Reativado de forma otimizada para consistência multi-lote
                ->get();

            if ($lotes->isEmpty()) {
                $venda->itens()->create([
                    'produto_id'     => $produtoId,
                    'lote_id'        => null,
                    'quantidade'     => $quantidadeNecessaria,
                    'preco_unitario' => $precoUnitario,
                    'desconto'       => $desconto
                ]);
            } else {
                foreach ($lotes as $lote) {
                    if ($quantidadeNecessaria <= 0) break;
                    
                    // 🧠 Garante o cálculo exato baseado no valor atualizado e travado pelo lock
                    $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel);

                    $venda->itens()->create([
                        'produto_id'     => $produtoId,
                        'lote_id'        => $lote->id,
                        'quantidade'     => $qtdConsumir,
                        'preco_unitario' => $precoUnitario,
                        'desconto'       => $desconto
                    ]);

                    // Subtração segura e isolada
                    DB::table('lotes')
                        ->where('id', $lote->id)
                        ->decrement('quantidade_disponivel', $qtdConsumir);
                        
                    $quantidadeNecessaria -= $qtdConsumir;
                }

                // Fallback: Se a venda estourar o estoque físico dos lotes cadastrados
                if ($quantidadeNecessaria > 0) {
                    $venda->itens()->create([
                        'produto_id'     => $produtoId,
                        'lote_id'        => null,
                        'quantidade'     => $quantidadeNecessaria,
                        'preco_unitario' => $precoUnitario,
                        'desconto'       => $desconto
                    ]);
                }
            }
        }

        return $valorTotalVenda;
    }


    /**
     * Registra os pagamentos de forma isolada.
     */
    // private function pagamentos_venda(int $vendaId, Request $request, int $userId, $limparNumero): array
    // {
    //     // Alinhado 100% com as opções válidas do seu enum do banco de dados
    //     $formasPermitidas = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'carteira', 'boleto', 'outros'];
    //     $pagamentosProcessados = [];

    //     foreach ($formasPermitidas as $forma) {
    //         $valorPago = $limparNumero($request->input($forma, 0));

    //         if ($valorPago > 0) {
    //             // 🚀 Mantido o nome correto e confirmado da sua tabela: pagamentos_venda
    //             DB::table('pagamentos_venda')->insert([
    //                 'user_id'          => $userId,
    //                 'venda_id'         => $vendaId,
    //                 'forma_pagamento'  => $forma,
    //                 'bandeira'         => $request->input("bandeira_{$forma}") ?: null,
    //                 'valor'            => $valorPago,
    //                 'troco'            => $forma === 'dinheiro' ? $limparNumero($request->input("troco_{$forma}", 0)) : 0,
    //                 'parcelas'         => $request->input("parcelas_{$forma}") ? (int)$request->input("parcelas_{$forma}") : null,
    //                 'status'           => $forma === 'carteira' ? 'pendente' : 'confirmado',
    //                 'created_at'       => now(),
    //                 'updated_at'       => now(),
    //                 'data_vencimento'  => $request->input("vencimento_{$forma}") ?: now()->format('Y-m-d')
    //             ]);

    //             $pagamentosProcessados[$forma] = $valorPago;
    //         }
    //     }

    //     return $pagamentosProcessados;
    // }

        /**
     * Registra os pagamentos de forma isolada.
     */
    private function pagamentos_venda(int $vendaId, Request $request, int $userId, $limparNumero): array
    {
        // Alinhado 100% com as opções válidas do seu enum do banco de dados
        $formasPermitidas = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'carteira', 'boleto', 'outros'];
        $pagamentosProcessados = [];

        // 🎯 AJUSTE SEGURO: Busca o valor total real da venda que foi gravado na tabela vendas
        $totalVenda = DB::table('vendas')->where('id', $vendaId)->value('total') ?? 0;

        // 1. Calcula primeiro a soma de todas as OUTRAS formas de pagamento (exceto dinheiro)
        $somaOutrasFormas = 0;
        foreach ($formasPermitidas as $forma) {
            if ($forma !== 'dinheiro') {
                $somaOutrasFormas += (float)$limparNumero($request->input($forma, 0));
            }
        }

        // 2. Descobre o valor máximo líquido que o dinheiro deve registrar para quitar a venda
        $restanteParaDinheiro = $totalVenda - $somaOutrasFormas;
        if ($restanteParaDinheiro < 0) {
            $restanteParaDinheiro = 0;
        }

        // 3. Processa os inputs e faz a divisão exata entre VALOR LÍQUIDO e TROCO
        foreach ($formasPermitidas as $forma) {
            $valorDigitado = (float)$limparNumero($request->input($forma, 0));

            if ($valorDigitado > 0) {
                $valorFinalBanco = $valorDigitado;
                $trocoLinha = 0;

                // --- SEPARAÇÃO MATEMÁTICA DO TROCO ---
                if ($forma === 'dinheiro' && $valorDigitado > $restanteParaDinheiro) {
                    $valorFinalBanco = $restanteParaDinheiro; // Salva o líquido necessário (ex: 350.00)
                    $trocoLinha = $valorDigitado - $restanteParaDinheiro; // Salva o troco real (ex: 50.00)
                }
                // ------------------------------------

                // Se o valor líquido e o troco zerarem por algum motivo, ignora o insert
                if ($valorFinalBanco <= 0 && $trocoLinha <= 0) {
                    continue;
                }

                DB::table('pagamentos_venda')->insert([
                    'user_id'          => $userId,
                    'venda_id'         => $vendaId,
                    'forma_pagamento'  => $forma,
                    'bandeira'         => $request->input("bandeira_{$forma}") ?: null,
                    'valor'            => $valorFinalBanco, // 🎯 SALVA O LÍQUIDO PERFEITO
                    'troco'            => $trocoLinha,      // 🎯 SALVA O TROCO REAL
                    'parcelas'         => $request->input("parcelas_{$forma}") ? (int)$request->input("parcelas_{$forma}") : null,
                    'status'           => $forma === 'carteira' ? 'pendente' : 'confirmado',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                    'data_vencimento'  => $request->input("vencimento_{$forma}") ?: now()->format('Y-m-d')
                ]);

                // Passa o valor líquido para a função finalizarFluxoFinanceiro salvar correto no caixa
                $pagamentosProcessados[$forma] = $valorFinalBanco;
            }
        }

        return $pagamentosProcessados;
    }


    /**
     * Registra o fluxo de caixa e aciona regras de carteira externa.
     */
    private function finalizarFluxoFinanceiro(Venda $venda, array $pagamentos, int $caixaId, CreditoService $creditoService): void
    {
        // 🚀 CORREÇÃO: Captura com segurança o ID do usuário logado localmente para evitar variáveis indefinidas
        $userIdAtual = auth()->id() ?? $venda->funcionario_id ?? 1;

        foreach ($pagamentos as $forma => $valor) {
            if ($caixaId > 0) {
                DB::table('movimentacoes_caixa')->insert([
                    'user_id'         => $userIdAtual,
                    'caixa_id'         => $caixaId,
                    'tipo'             => 'venda',
                    'forma_pagamento'  => $forma,
                    'valor'            => $valor,
                    'observacao'       => "Venda PDV#{$venda->id}",
                    'created_at'       => now(),
                    'updated_at'       => now()
                ]);
            }

            if ($forma === 'carteira' && $venda->cliente_id) {
                $creditoService->adicionarDebito($venda->cliente_id, $valor, $venda->id);
            }
        }
    }

       // dados da empresa e exibe a tela que imprime cupom das vendas
    // public function cupom($id) 
    // { 
    //     // 1. Carrega a venda com os relacionamentos originais
    //     $venda = Venda::with([
    //         'cliente', 
    //         'itens.produto.unidadeMedida', // Carrega relacionamento de medidas para evitar zeros na impressão
    //         'itens.lote',                  // Carrega relacionamento de lotes para evitar zeros na impressão
    //         'funcionario'
    //     ])->findOrFail($id); 

    //     // 🔥 CORREÇÃO: Busca os pagamentos direto na tabela real 'pagamentos_venda' usando o Query Builder
    //     // Isso garante o carregamento idêntico ao describe do seu banco, evitando erros de relacionamento vazio.
    //     $pagamentosDaVenda = \Illuminate\Support\Facades\DB::table('pagamentos_venda')
    //         ->where('venda_id', $id)
    //         ->get();

    //     // 2. Busca a empresa ativa (Mantendo seu padrão)
    //     $empresa = \App\Models\Empresa::where('ativo', 1)->first();

    //     // 3. Cálculos básicos de Totais baseados na coleção de pagamentos real do banco
    //     $descontoTotal  = $venda->itens->sum('desconto'); 
    //     $pagoEmDinheiro = $pagamentosDaVenda->where('forma_pagamento', 'dinheiro')->sum('valor'); 
    //     $totalPagoGeral = $pagamentosDaVenda->sum('valor'); 
    //     $troco          = $totalPagoGeral > $venda->total ? ($totalPagoGeral - $venda->total) : 0;

    //     // Injeta os pagamentos na propriedade do model para a View rodar em loops normais sem quebrar
    //     $venda->setRelation('pagamentos', $pagamentosDaVenda);

    //     // 4. PROTEÇÃO DE CAMINHO: Verifica qual pasta realmente existe no seu projeto
    //     if (view()->exists('vendas.cupom')) {
    //         $caminhoView = 'vendas.cupom'; 
    //     } else {
    //         $caminhoView = 'venda.cupom';  
    //     }

    //     // 5. Retorna a view correta com os dados
    //     return view($caminhoView, compact( 
    //         'venda', 
    //         'empresa', 
    //         'descontoTotal', 
    //         'pagoEmDinheiro', 
    //         'troco' 
    //     )); 
    // }

        public function cupom($id) 
    { 
        // 1. Carrega a venda com os relacionamentos originais
        $venda = Venda::with([
            'cliente', 
            'itens.produto.unidadeMedida', 
            'itens.lote',                  
            'funcionario'
        ])->findOrFail($id); 

        // Busca as movimentações de pagamento salvas no banco
        $pagamentosDaVenda = \Illuminate\Support\Facades\DB::table('pagamentos_venda')
            ->where('venda_id', $id)
            ->get();

        // 2. Busca a empresa ativa
        $empresa = \App\Models\Empresa::where('ativo', 1)->first();

        // 3. Cálculos básicos de Totais
        $descontoTotal = $venda->itens->sum('desconto'); 
        
        // 🎯 INTERVENÇÃO CIRÚRGICA: Recupera a linha do dinheiro
        $pagamentoDinheiro = $pagamentosDaVenda->where('forma_pagamento', 'dinheiro')->first();
        
        // Lê as duas colunas separadas do banco
        $valorLiquido = $pagamentoDinheiro ? (float)$pagamentoDinheiro->valor : 0; // Ex: 350.00
        $troco        = $pagamentoDinheiro ? (float)$pagamentoDinheiro->troco : 0; // Ex: 150.00

        // 🔥 AQUI ESTÁ O SEGREDO: Soma o troco de volta ao valor apenas para exibição no cupom!
        // A variável $pagoEmDinheiro vai para a View valendo R$ 500,00 (350 + 150)
        $pagoEmDinheiro = $valorLiquido + $troco; 

        // Injeta os pagamentos na propriedade do model para a View
        $venda->setRelation('pagamentos', $pagamentosDaVenda);

        // Proteção de caminho nativa do seu código
        if (view()->exists('vendas.cupom')) {
            $caminhoView = 'vendas.cupom'; 
        } else {
            $caminhoView = 'venda.cupom';  
        }

        // 5. Retorna a view com as variáveis prontas
        return view($caminhoView, compact( 
            'venda', 
            'empresa', 
            'descontoTotal', 
            'pagoEmDinheiro', 
            'troco' 
        )); 
    }



}
