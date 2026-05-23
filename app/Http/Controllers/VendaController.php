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

            $lotes = DB::table('lotes')
                ->where('produto_id', $produtoId)
                ->where('quantidade_disponivel', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
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
                    $qtdConsumir = min($quantidadeNecessaria, $lote->quantidade_disponivel);

                    $venda->itens()->create([
                        'produto_id'     => $produtoId,
                        'lote_id'        => $lote->id,
                        'quantidade'     => $qtdConsumir,
                        'preco_unitario' => $precoUnitario,
                        'desconto'       => $desconto
                    ]);

                    DB::table('lotes')->where('id', $lote->id)->decrement('quantidade_disponivel', $qtdConsumir);
                    $quantidadeNecessaria -= $qtdConsumir;
                }

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
     * Registra os pagamentos de forma isolada (Sua solicitação principal).
     */
    private function pagamentos_venda(int $vendaId, Request $request, int $userId, $limparNumero): array
    {
        $formasPermitidas = ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'carteira', 'boleto', 'outros'];
        $pagamentosProcessados = [];

        foreach ($formasPermitidas as $forma) {
            $valorPago = $limparNumero($request->input($forma, 0));

            if ($valorPago > 0) {
                DB::table('pagamentos_venda')->insert([
                    'user_id'          => $userId,
                    'venda_id'         => $vendaId,
                    'forma_pagamento'  => $forma,
                    'bandeira'         => $request->input("bandeira_{$forma}") ?: null,
                    'valor'            => $valorPago,
                    'parcelas'         => $request->input("parcelas_{$forma}") ? (int)$request->input("parcelas_{$forma}") : null,
                    'status'           => $forma === 'carteira' ? 'pendente' : 'confirmado',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                    'data_vencimento'  => $request->input("vencimento_{$forma}") ?: now()->format('Y-m-d')
                ]);

                $pagamentosProcessados[$forma] = $valorPago;
            }
        }

        return $pagamentosProcessados;
    }

    /**
     * Registra o fluxo de caixa e aciona regras de carteira externa.Movimentacoes_caixa
     */
    private function finalizarFluxoFinanceiro(Venda $venda, array $pagamentos, int $caixaId, CreditoService $creditoService): void
    {
        foreach ($pagamentos as $forma => $valor) {
            if ($caixaId > 0) {
                DB::table('movimentacoes_caixa')->insert([
                    'user_id'         => $userId ?? auth()->id() ?? 1,
                    'caixa_id'         => $caixaId,
                    // 'venda_id'         => $venda->id,
                    'tipo'             => 'venda',
                    'forma_pagamento'  => $forma,
                    'valor'            => $valor,
                    'observacao'        => "Venda PDV#{$venda->id}",
                    'created_at'       => now(),
                    'updated_at'       => now()
                ]);
            }

            if ($forma === 'carteira' && $venda->cliente_id) {
                $creditoService->adicionarDebito($venda->cliente_id, $valor, $venda->id);
            }
        }
    }
    /**
     * Exibe ou gera o cupom da venda concluída.
     */

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

       // dados da empresa e exibe a tela que imprime cupom das vendas
    public function cupom($id) 
    { 
        // 1. Carrega a venda com os relacionamentos originais
        $venda = Venda::with([
            'cliente', 
            'itens.produto.unidadeMedida', // Carrega relacionamento de medidas para evitar zeros na impressão
            'itens.lote',                  // Carrega relacionamento de lotes para evitar zeros na impressão
            'funcionario'
        ])->findOrFail($id); 

        // 🔥 CORREÇÃO: Busca os pagamentos direto na tabela real 'pagamentos_venda' usando o Query Builder
        // Isso garante o carregamento idêntico ao describe do seu banco, evitando erros de relacionamento vazio.
        $pagamentosDaVenda = \Illuminate\Support\Facades\DB::table('pagamentos_venda')
            ->where('venda_id', $id)
            ->get();

        // 2. Busca a empresa ativa (Mantendo seu padrão)
        $empresa = \App\Models\Empresa::where('ativo', 1)->first();

        // 3. Cálculos básicos de Totais baseados na coleção de pagamentos real do banco
        $descontoTotal  = $venda->itens->sum('desconto'); 
        $pagoEmDinheiro = $pagamentosDaVenda->where('forma_pagamento', 'dinheiro')->sum('valor'); 
        $totalPagoGeral = $pagamentosDaVenda->sum('valor'); 
        $troco          = $totalPagoGeral > $venda->total ? ($totalPagoGeral - $venda->total) : 0;

        // Injeta os pagamentos na propriedade do model para a View rodar em loops normais sem quebrar
        $venda->setRelation('pagamentos', $pagamentosDaVenda);

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
