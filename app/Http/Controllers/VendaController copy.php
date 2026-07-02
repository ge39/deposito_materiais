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
use App\Models\EstoqueDivergencia;
use App\Services\CreditoService;
use App\Services\Entregas\EntregaService;

class VendaController extends Controller
{
    /**
     * Store da venda (PDV)
     * Responsabilidade TOTAL do backend
     */
    // public function finalizar(Request $request, CreditoService $creditoService, EntregaService $entregaService)
    // {
    //     $limparNumero = function ($valor) {
    //         if (is_numeric($valor)) {
    //             return (float) $valor;
    //         }

    //         $stringLimpa = preg_replace('/[^\d,.]/', '', $valor);

    //         return (float) (
    //             strpos($stringLimpa, ',') !== false && strpos($stringLimpa, '.') !== false
    //                 ? str_replace(',', '', $stringLimpa)
    //                 : str_replace(',', '.', $stringLimpa)
    //         );
    //     };

    //     DB::beginTransaction();

    //     try {

    //         $clienteId = $this->buscarOuDefinirCliente($request->input('cliente_id'));
    //         $caixaId = (int) $request->input('caixa_id');

    //         $funcionarioId = auth()->id()
    //             ?? $request->input('funcionario_id')
    //             ?? $request->input('funciona_id')
    //             ?? 0;
    //         $orcamentoId = $request->input('orcamento_id');
    //         $itensOriginais = $request->input('itens', []);
    //         $itensTratados = [];

    //         foreach ($itensOriginais as $item) {
    //             if (!isset($item['produto_id'])) {
    //                 continue;
    //             }

    //             $precoDefinido = $item['valor_unitario']
    //                 ?? $item['preco_unitario']
    //                 ?? $item['preco']
    //                 ?? 0;

    //             $item['valor_unitario'] = $precoDefinido;
    //             $item['preco_unitario'] = $precoDefinido;
    //             $item['preco'] = $precoDefinido;

    //             $qtdDefinida = $item['quantidade'] ?? $item['qtd'] ?? 1;

    //             $item['quantidade'] = $qtdDefinida;
    //             $item['qtd'] = $qtdDefinida;

    //             $itensTratados[] = $item;
    //         }

    //         $venda = Venda::create([
    //             'orcamento_id'    => $orcamentoId,
    //             'cliente_id'      => $clienteId,
    //             'funcionario_id'  => $funcionarioId,
    //             'caixa_id'        => $caixaId,
    //             'status'          => 'aberta',
    //             'total'           => 0,
    //             'data_venda'      => $request->dataVenda ?? now(),
    //         ]);

    //         $valorTotal = $this->processarItensVenda($venda, $itensTratados, $limparNumero);

    //         $venda->update([
    //             'total' => $valorTotal,
    //         ]);

    //         foreach ($itensTratados as $item) {
    //             if (!isset($item['produto_id'])) {
    //                 continue;
    //             }

    //             $precoFinal = $limparNumero($item['preco_unitario']);

    //             if ($precoFinal <= 0) {
    //                 $precoBase = DB::table('produtos')
    //                     ->where('id', $item['produto_id'])
    //                     ->value('preco_venda');

    //                 $precoFinal = $precoBase ? (float) $precoBase : 0.00;
    //             }

    //             \App\Models\ItemVenda::create([
    //                 'venda_id'       => $venda->id,
    //                 'produto_id'     => $item['produto_id'],
    //                 'lote_id'        => (
    //                     empty($item['lote_id']) || $item['lote_id'] === 'SEM_LOTE'
    //                 ) ? null : (int) $item['lote_id'],
    //                 'quantidade'     => $limparNumero($item['quantidade']),
    //                 'preco_unitario' => $precoFinal,
    //                 'desconto'       => $limparNumero($item['desconto'] ?? 0),
    //             ]);
    //         }

    //         $userId = auth()->id() ?? $request->input('user_id', 1);

    //         $pagamentos = $this->pagamentos_venda(
    //             $venda->id,
    //             $request,
    //             $userId,
    //             $limparNumero
    //         );

    //         $this->finalizarFluxoFinanceiro(
    //             $venda,
    //             $pagamentos,
    //             $caixaId,
    //             $creditoService
    //         );

    //         $venda->update([
    //             'status' => 'finalizada',
    //         ]);

    //         if ($orcamentoId) {
    //             $orcamento = \App\Models\Orcamento::find($orcamentoId);

    //             if ($orcamento) {
    //                 $orcamento->update([
    //                     'status'       => 'Faturado',
    //                     'editando_por' => auth()->id()
    //                         ?? $request->input('funcionario_id')
    //                         ?? 1,
    //                 ]);

    //                 if (($orcamento->tipo_entrega ?? null) === 'entrega') {
    //                     $entregaService->gerarEntregaDaVenda($venda, $orcamento);
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success'  => true,
    //             'venda_id' => $venda->id,
    //         ], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'erro'    => $e->getMessage(),
    //         ], 422);
    //     }
    // }

     public function finalizar(Request $request, CreditoService $creditoService) 
    { 
        // 🕵️‍♂️ CAPTURA DOS DADOS ANTES DE PERSISTIR
        // \Log::info('===== DADOS RECEBIDOS NO BACKEND (PDV) =====');
        // \Log::info('URL Acessada: ' . request()->fullUrl());
        // \Log::info('Payload Completo:', $request->all());
        // \Log::info('Valor de orcamento_id isolado: ' . $request->input('orcamento_id'));
        // \Log::info('============================================');

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
            $funcionarioId = auth()->id() ?? $request->input('funcionario_id') ?? $request->input('funciona_id') ?? 00;

            // 🚀 BALANCEMENTO REFINADO: Normalização e espelhamento completo de chaves para o Estoque
            $itensOriginais = $request->input('itens', []);
            $itensTratados = [];

            foreach ($itensOriginais as $item) {
                if (isset($item['produto_id'])) {
                    // Resolve o preço encontrando qualquer uma das chaves e replica em todas para blindar o estoque
                    $precoDefinido = $item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? 0;
                    
                    $item['valor_unitario'] = $precoDefinido;
                    $item['preco_unitario'] = $precoDefinido;
                    $item['preco']          = $precoDefinido; // 🎯 Alimenta o preço esperado pela baixa de estoque!

                    // Garante consistência na quantidade caso mude o seletor no JS
                    $qtdDefinida = $item['quantidade'] ?? $item['qtd'] ?? 1;
                    $item['quantidade']     = $qtdDefinida;
                    $item['qtd']            = $qtdDefinida;

                    $itensTratados[] = $item;
                }
            }

            // 2️⃣ Instancia a Venda
            $dadosVenda = [ 
                'cliente_id'     => $clienteId, 
                'funcionario_id' => $funcionarioId, 
                'caixa_id'       => $caixaId, 
                'status'         => 'aberta', 
                'total'          => 0,
                'data_venda'     => $request->dataVenda ?? now()
            ]; 
            $venda = Venda::create($dadosVenda); 

            // 3️⃣ Executa Itens, FIFO/Estoque com chaves 100% espelhadas
            $valorTotal = $this->processarItensVenda($venda, $itensTratados, $limparNumero);
            $venda->update(['total' => $valorTotal]);

            // 🚀 PERSISTÊNCIA COMPLETA: Gravação física na tabela item_vendas
            foreach ($itensTratados as $item) {
                if (isset($item['produto_id'])) {
                    $precoFinal = $limparNumero($item['preco_unitario']);
                    
                    if ($precoFinal <= 0) {
                        $precoBase = DB::table('produtos')->where('id', $item['produto_id'])->value('preco_venda');
                        $precoFinal = $precoBase ? (float)$precoBase : 0.00;
                    }

                \App\Models\ItemVenda::create([
                    'venda_id'       => $venda->id,
                    'produto_id'     => $item['produto_id'],
                    'lote_id'        => (
                        empty($item['lote_id']) || $item['lote_id'] === 'SEM_LOTE'
                    ) ? null : (int) $item['lote_id'],
                    'quantidade'     => $limparNumero($item['quantidade']),
                    'preco_unitario' => $precoFinal,
                    'desconto'       => $limparNumero($item['desconto'] ?? 0),
                ]);
                }
            }

            // 4️⃣ Executa os Pagamentos (Filho)
            $userId = auth()->id() ?? $request->input('user_id', 1);
            $pagamentos = $this->pagamentos_venda($venda->id, $request, $userId, $limparNumero);

            // 5️⃣ Executa Caixa e Crediário (Filho)
            $this->finalizarFluxoFinanceiro($venda, $pagamentos, $caixaId, $creditoService);

            // 6️⃣ Fecha a venda
            $venda->update(['status' => 'finalizada']);

            // 🎯 RASTREAMENTO SIMPLIFICADO POR PRODUTO
            $clienteIdLog = $request->input('cliente_id');
            
            $primeiroItem = count($itensTratados) > 0 ? $itensTratados[0] : null;
            $produtoIdLog = $primeiroItem ? $primeiroItem['produto_id'] : null;

            if ($produtoIdLog) {
                $orcamento = \App\Models\Orcamento::where('cliente_id', $clienteIdLog)
                    ->where('status', 'Aprovado')
                    ->whereHas('itens', function($query) use ($produtoIdLog) {
                        $query->where('produto_id', $produtoIdLog);
                    })
                    ->latest()
                    ->first();

                if ($orcamento) {
                    \App\Models\Orcamento::where('id', $orcamento->id)->update([
                        'status'       => 'Faturado',
                        'editando_por' => auth()->id() ?? $request->input('funcionario_id') ?? 1
                    ]);
                }
            }
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
     * Processa a baixa de estoque dos itens, valida teto de descontos manuais
     * baseados no perfil do cliente e implementa FIFO.
     */
    private function processarItensVenda(Venda $venda, array $itens, $limparNumero): float
    {
        $valorTotalVenda = 0;

        $cliente = DB::table('clientes')
            ->where('id', $venda->cliente_id)
            ->first();

        $perfilCliente = $cliente ? $cliente->tipo_cliente : 'markup_1';

        foreach ($itens as $item) {
            $qtd = $limparNumero($item['quantidade'] ?? $item['qtd'] ?? 1);

            $precoOriginal = $item['valor_unitario'] ?? $item['preco_unitario'] ?? $item['preco'] ?? 0;
            $precoCobrado = $limparNumero($precoOriginal);

            $descInformado = $limparNumero($item['desconto'] ?? 0);
            $prodId = $item['produto_id'] ?? $item['id'] ?? null;
            $loteIdInformado = $item['lote_id'] ?? null;

            $produto = DB::table('produtos')
                ->where('id', $prodId)
                ->first();

            if (!$produto) {
                continue;
            }

            // 1. Define preço e desconto máximo baseado no perfil do cliente
            switch ($perfilCliente) {
                case 'markup_2':
                    $maxDesc = $produto->desconto_max_2 ?? 0;
                    break;

                case 'markup_3':
                    $maxDesc = $produto->desconto_max_3 ?? 0;
                    break;

                default:
                    $maxDesc = $produto->desconto_max_1 ?? 0;
                    break;
            }

            // 2. Valida trava de desconto manual
            $totalBruto = $qtd * ($precoCobrado ?: $produto->preco_venda);
            $descPerc = ($totalBruto > 0) ? ($descInformado / $totalBruto) * 100 : 0;

            if ($descPerc > $maxDesc) {
                throw new \Exception("Desconto excede o máximo de {$maxDesc}%");
            }

            // =======================================================================
            // 3. Baixa de estoque por lote com FIFO
            // =======================================================================
            $lotes = [];

            if ($loteIdInformado) {
                $loteEspecifico = DB::table('lotes')
                    ->where('id', $loteIdInformado)
                    ->where('produto_id', $prodId)
                    ->where('quantidade_disponivel', '>', 0)
                    ->lockForUpdate()
                    ->first();

                if ($loteEspecifico) {
                    $lotes[] = $loteEspecifico;
                }
            }

            if (empty($lotes)) {
                $lotes = DB::table('lotes')
                    ->where('produto_id', $prodId)
                    ->where('quantidade_disponivel', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();
            }

            $qtdRestante = $qtd;
            $qtdAtendida = 0;

            foreach ($lotes as $lote) {
                if ($qtdRestante <= 0) {
                    break;
                }

                $consumo = min($lote->quantidade_disponivel, $qtdRestante);
                $novaQtdLote = $lote->quantidade_disponivel - $consumo;

                DB::table('lotes')
                    ->where('id', $lote->id)
                    ->update([
                        'quantidade_disponivel' => $novaQtdLote,
                        'updated_at'            => now()
                    ]);

                $qtdAtendida += $consumo;
                $qtdRestante -= $consumo;
            }

            // 4. Registra divergência quando a venda exceder o estoque virtual
            if ($qtdRestante > 0) {
                DB::table('estoque_divergencias')->insert([
                    'produto_id'              => $prodId,
                    'venda_id'                => $venda->id,
                    'caixa_id'                => $venda->caixa_id,
                    'quantidade_solicitada'   => $qtd,
                    'quantidade_atendida'     => $qtdAtendida,
                    'diferenca'               => $qtdRestante,
                    'tipo'                    => 'venda',
                    'observacao'              => 'Venda PDV com quantidade acima do estoque virtual.',
                    'usuario_id'              => auth()->id(),
                    'created_at'              => now(),
                ]);
            }

            // 5. Atualiza quantidade consolidada do produto sem negativar estoque
            $novoEstoqueGeral = max(0, ($produto->quantidade_estoque ?? 0) - $qtdAtendida);

            DB::table('produtos')
                ->where('id', $prodId)
                ->update([
                    'quantidade_estoque' => $novoEstoqueGeral,
                    'updated_at'         => now()
                ]);

            $valorTotalVenda += ($totalBruto - $descInformado);
        }

        return $valorTotalVenda;
    }

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

    /**
     * Gera os dados do cupom de forma estática e segura para impressão ou reimpressão.
     */
     public function cupom($id) 
    { 
        // 1. Carrega a venda pai com os relacionamentos de primeiro nível
        $venda = Venda::with(['cliente', 'funcionario'])->findOrFail($id); 

        // 🚀 CARREGAMENTO CIRÚRGICO: Busca os itens diretamente partindo de ItemVenda
        // Isso anula qualquer falha de mapeamento ou chave estrangeira invertida do Eloquent
        $itensReais = \App\Models\ItemVenda::with(['produto.unidadeMedida', 'lote'])
            ->where('venda_id', $id)
            ->get();

        // Injeta os itens carregados diretamente na relação da Venda
        $venda->setRelation('itens', $itensReais);

        // Busca o histórico real de pagamentos gravado no banco de dados
        $pagamentosDaVenda = \Illuminate\Support\Facades\DB::table('pagamentos_venda')
            ->where('venda_id', $id)
            ->get();

        // 2. Busca os dados cadastrais da empresa ativa
        $empresa = \App\Models\Empresa::where('ativo', 1)->first();

        // 3. Agrega os subtotais e descontos em memória
        $descontoTotal = $venda->itens->sum('desconto'); 
        
        // 🎯 AJUSTE CIRÚRGICO EXATO: Busca apenas o terminal_id numérico que está dentro da tabela caixas
        // Isso elimina o JOIN com a tabela terminais e mata o erro 1054 de vez
        $terminalId = \Illuminate\Support\Facades\DB::table('caixas')
            ->where('id', $venda->caixa_id)
            ->value('terminal_id') ?? 0;

        // 🎯 RECUPERAÇÃO SEGURA DO TROCO HISTÓRICO
        // Busca o registro de dinheiro se ele existir nessa venda
        $pagamentoDinheiro = $pagamentosDaVenda->where('forma_pagamento', 'dinheiro')->first();
        
        // Se houver dinheiro, extrai o troco gravado na nova coluna. Caso contrário, assume 0
        $troco = $pagamentoDinheiro ? (float)$pagamentoDinheiro->troco : 0;
        
        // O dinheiro contábil líquido gravado no banco
        $valorLiquidoDinheiro = $pagamentoDinheiro ? (float)$pagamentoDinheiro->valor : 0;

        // Soma o troco de volta ao valor apenas na view para imprimir o Bruto entregue (ex: 350 + 150 = 500)
        $pagoEmDinheiro = $valorLiquidoDinheiro + $troco; 

        // Injeta a coleção na venda para o Blade renderizar o loop sem dar novas queries
        $venda->setRelation('pagamentos', $pagamentosDaVenda);

        // 4. Proteção de caminhos para compatibilidade de pastas
        $caminhoView = view()->exists('vendas.cupom') ? 'vendas.cupom' : 'venda.cupom';  

        // 5. Devolve o template com todas as variáveis preenchidas
        return view($caminhoView, compact( 
            'venda', 
            'empresa', 
            'descontoTotal', 
            'pagoEmDinheiro', 
            'troco',
            'terminalId' // 🎯 Passa a variável numérica limpa para a sua View
        )); 
    }

    /**
     * Busca o ID da última venda finalizada no banco para prevenção de quedas de energia.
     */
    public function obterUltimaVendaId(\Illuminate\Http\Request $request)
    {
        // Lê o caixa_id que o formulário do JavaScript vai enviar
        $caixaId = (int) $request->input('caixa_id');

        if ($caixaId <= 0) {
            return response()->json(['success' => false, 'erro' => 'Caixa nao identificado para a busca.']);
        }

        // 🧠 CONSULTA DIRETA NO BANCO DE DADOS: 
        // Pega o ID mais alto (o último) que foi gravado como 'finalizada' neste caixa
        $ultimoId = \Illuminate\Support\Facades\DB::table('vendas')
            ->where('caixa_id', $caixaId)
            ->where('status', 'finalizada')
            ->orderBy('id', 'desc')
            ->value('id');

        if (!$ultimoId) {
            return response()->json(['success' => false, 'erro' => 'Nenhuma venda encontrada para este caixa no banco.']);
        }

        return response()->json([
            'success'  => true, 
            'venda_id' => $ultimoId
        ]);
    }

}
