<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\ContaCorrenteService;
use App\Services\CreditoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ClienteCreditoController extends Controller
{
    protected $contaCorrenteService;
    protected $creditoService;

    public function __construct(
        ContaCorrenteService $contaCorrenteService,
        CreditoService $creditoService
    ) {
        $this->contaCorrenteService = $contaCorrenteService;
        $this->creditoService = $creditoService;
    }

    /**
     * POST /api/clientes/{id}/credito/pagar
     * Cenário de amortização ou pagamento total do saldo utilizado.
     */
    //    public function pagarCredito(Request $request, $id)
    // {
    //     $request->validate([
    //         'valor'        => 'required|numeric|min:0.01',
    //         'meio_captura' => 'required|string|in:dinheiro,pix,debito'
    //     ]);

    //     $cliente = Cliente::findOrFail($id);

    //     try {
    //         $novoSaldo = DB::transaction(function () use ($request, $cliente) {
                
    //             // 1. Executa a entrada de saldo na Conta Corrente (Chama seu Service)
    //             $saldoCalculado = $this->contaCorrenteService->adicionarCredito(
    //                 $cliente->id,
    //                 (float) $request->valor
    //             );

    //             // 2. 🔥 GRAVAÇÃO COMPLEMENTAR DO HISTÓRICO (Garante a submissão para a auditoria)
    //             DB::table('cliente_historico_creditos')->insert([
    //                 'cliente_id'     => $cliente->id,
    //                 'tipo_evento'    => 'desbloqueio', // Tipo padrão mapeado na sua migration
    //                 'descricao'      => "Recebimento de pagamento via " . strtoupper($request->meio_captura) . " no valor de R$ " . number_format($request->valor, 2, ',', '.'),
    //                 'score_anterior' => $cliente->score_credito, // Respeitando a estrutura da sua tabela
    //                 'score_novo'     => $cliente->score_credito,
    //                 'created_at'     => now()
    //             ]);

    //             // Opcional: Se tiver tabela de movimentacao de caixa do operador, ela entra aqui também.

    //             return $saldoCalculado;
    //         });

    //         // Devolve o JSON exato que o seu FETCH JavaScript está esperando ler lá na linha 25 (data.dados.saldo_disponivel)
    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Pagamento processado com sucesso!',
    //             'dados'   => [
    //                 'saldo_disponivel' => $novoSaldo
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }


    /**
     * POST /api/clientes/{id}/credito/aumentar-limite
     * Cenário de upgrade de limite de crédito.
     */
       /**
     * POST /clientes/{id}/credito/aumentar-limite
     * Cenário de upgrade de limite de crédito com auditoria garantida.
     */
    public function aumentarLimite(Request $request, $id)
    {
        $request->validate([
            'novo_limite' => 'required|numeric|min:0.01',
        ]);

        $cliente = Cliente::with('creditoAtivo')->findOrFail($id);

        try {
            // Abre a transação e força o retorno dos dados
            DB::transaction(function () use ($request, $cliente) {
                
                // 1. Executa a regra matemática de limites e saldo na conta corrente
                $this->creditoService->aumentarLimiteCredito(
                    $cliente,
                    (float) $request->novo_limite
                );

                // 2. 🔥 SUBMISSÃO GARANTIDA DO HISTÓRICO (Evita falhas do Service)
                // Usamos o enum 'ajuste_score' que está estritamente mapeado na sua migration
                DB::table('cliente_historico_creditos')->insert([
                    'cliente_id'     => $cliente->id,
                    'tipo_evento'    => 'ajuste_score', 
                    'descricao'      => "Aumento de limite de crédito homologado via painel para R$ " . number_format($request->novo_limite, 2, ',', '.'),
                    'score_anterior' => $cliente->score_credito,
                    'score_novo'     => $cliente->score_credito,
                    'created_at'     => now()
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Limite de crédito atualizado com sucesso e registrado no histórico!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }


    /**
     * POST /api/credito/movimentacoes/{id}/estornar
     * Processa a transação reversa de estorno na conta corrente
     */
    public function estornar(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'nullable|string|max:255'
        ]);

        try {
            $novoSaldo = $this->contaCorrenteService->estornarTransacao(
                $id,
                $request->input('motivo', 'Estorno solicitado pelo operador.')
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Lançamento estornado com sucesso!',
                'dados'   => [
                    'saldo_disponivel' => $novoSaldo
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

     /**
     * POST /clientes/{id}/credito/pagar
     * Processa o pagamento, atualiza a conta corrente vinculando à venda, reativa se total e alimenta o caixa.
     */
    // public function pagarCredito(Request $request, $id)
    // {
    //     $request->validate([
    //         'valor'        => 'required|numeric|min:0.01',
    //         'meio_captura' => 'required|string|in:dinheiro,pix,debito',
    //         'venda_id'     => 'nullable|integer' // 🔥 Adicionada a validação do campo venda_id
    //     ]);

    //     $usuarioLogado = auth()->user();
    //     if (!$usuarioLogado) {
    //         return response()->json(['status' => 'error', 'message' => 'Sessão expirada.'], 401);
    //     }

    //     try {
    //         $saldoFinal = DB::transaction(function () use ($request, $id, $usuarioLogado) {
                
    //             // 1. 🔒 Localiza e trava a sessão de caixa ativa do operador logado
    //             $caixaAtivo = DB::table('caixas')
    //                 ->where('user_id', $usuarioLogado->id) 
    //                 ->where('status', 'aberto')            
    //                 ->lockForUpdate()
    //                 ->first();

    //             if (!$caixaAtivo) {
    //                 throw new \Exception('Operação Negada: O operador não possui uma sessão de caixa aberta para este turno.');
    //             }

    //             // 2. Captura as configurações de crédito do cliente
    //             $creditoConfig = DB::table('cliente_creditos')->where('cliente_id', $id)->first();
    //             $limiteCredito = $creditoConfig ? (float) $creditoConfig->limite_credito : 500.00;

    //             // 3. 🔒 Trava e busca o último registro do cliente na Conta Corrente
    //             $ultimaMovimentacao = DB::table('cliente_conta_correntes')
    //                 ->where('cliente_id', $id)
    //                 ->orderByDesc('id')
    //                 ->lockForUpdate()
    //                 ->first();

    //             $saldoAtual = $ultimaMovimentacao !== null ? (float) $ultimaMovimentacao->saldo_apos : $limiteCredito;
    //             $novoSaldoCC = $saldoAtual + (float) $request->valor;

    //             // 4. 🔥 INSERE O PAGAMENTO NA TABELA VINCULANDO O VENDA_ID RECEBIDO
    //             $ccId = DB::table('cliente_conta_correntes')->insertGetId([
    //                 'cliente_id'         => $id,
    //                 'venda_id'           => $request->input('venda_id'), // 🔥 AGORA SALVA O VENDA_ID DO COMPORTAMENTO DO PDV
    //                 'pagamento_venda_id' => null, 
    //                 'tipo'               => 'credito', 
    //                 'origem'             => 'pagamento', 
    //                 'valor'              => (float) $request->valor,
    //                 'saldo_apos'         => $novoSaldoCC, 
    //                 'descricao'          => "Recebimento de pagamento / amortização de carteira via " . strtoupper($request->meio_captura),
    //                 'created_at'         => now(),
    //                 'updated_at'         => now()
    //             ]);

    //             // 5. REGRA DE DESBLOQUEIO AUTOMÁTICO
    //             if (round($novoSaldoCC, 2) >= round($limiteCredito, 2)) {
    //                 DB::table('cliente_creditos')->where('cliente_id', $id)->update([
    //                     'status' => 'ativo',
    //                     'updated_at' => now()
    //                 ]);

    //                 DB::table('clientes')->where('id', $id)->update([
    //                     'bloqueado_credito'     => 0,
    //                     'data_bloqueio_credito' => null,
    //                     'ativo'                 => '1', 
    //                     'updated_at'            => now()
    //                 ]);
    //             }

    //             // 6. REGISTRA A AUDITORIA CONTÁBIL
    //             DB::table('cliente_historico_creditos')->insert([
    //                 'cliente_id'     => $id,
    //                 'tipo_evento'    => 'desbloqueio',
    //                 'descricao'      => "Quitação de saldo devedor via " . strtoupper($request->meio_captura) . " no valor de R$ " . number_format($request->valor, 2, ',', '.') . ". Vinculado à Venda ID #" . $request->input('venda_id'),
    //                 'score_anterior' => DB::table('clientes')->where('id', $id)->value('score_credito') ?? 100,
    //                 'score_novo'     => DB::table('clientes')->where('id', $id)->value('score_credito') ?? 100,
    //                 'created_at'     => now()
    //             ]);

    //             // 7. REGISTRA NO FLUXO DE CAIXA DO PDV
    //             DB::table('movimentacoes_caixa')->insert([
    //                 'caixa_id'          => $caixaAtivo->id,
    //                 'user_id'           => $usuarioLogado->id, 
    //                 'tipo'              => 'entrada', 
    //                 'forma_pagamento'   => $request->meio_captura, 
    //                 'valor'             => (float) $request->valor,
    //                 'valor_auditado'    => 0.00,
    //                 'origem_id'         => $ccId, 
    //                 'observacao'        => "Recebimento de saldo de carteira. Cliente ID #{$id}", 
    //                 'data_movimentacao' => now(),
    //                 'created_at'        => now(),
    //                 'updated_at'        => now()
    //             ]);

    //             \Illuminate\Support\Facades\Cache::forget("cliente_saldo_{$id}");

    //             return $novoSaldoCC;
    //         });

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Pagamento vinculado à venda com sucesso!',
    //             'dados'   => [
    //                 'saldo_disponivel' => $saldoFinal
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    public function pagarCredito(Request $request, $id)
{
    $request->validate([
        'valor'        => 'required|numeric|min:0.01',
        'meio_captura' => 'required|string|in:dinheiro,pix,debito',
        'venda_id'     => 'nullable|integer' 
    ]);

    $usuarioLogado = auth()->user();
    if (!$usuarioLogado) {
        return response()->json(['status' => 'error', 'message' => 'Sessão expirada.'], 401);
    }

    try {
        // Altera o retorno da transação para entregar um array com os dois dados essenciais
        $retornoTransacao = DB::transaction(function () use ($request, $id, $usuarioLogado) {
            
            // 1. 🔒 Localiza e trava a sessão de caixa ativa do operador logado
            $caixaAtivo = DB::table('caixas')
                ->where('user_id', $usuarioLogado->id) 
                ->where('status', 'aberto')            
                ->lockForUpdate()
                ->first();

            if (!$caixaAtivo) {
                throw new \Exception('Operação Negada: O operador não possui uma sessão de caixa aberta para este turno.');
            }

            // 2. Captura as configurações de crédito do cliente
            $creditoConfig = DB::table('cliente_creditos')->where('cliente_id', $id)->first();
            $limiteCredito = $creditoConfig ? (float) $creditoConfig->limite_credito : 500.00;

            // 3. 🔒 Trava e busca o último registro do cliente na Conta Corrente
            $ultimaMovimentacao = DB::table('cliente_conta_correntes')
                ->where('cliente_id', $id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $saldoAtual = $ultimaMovimentacao !== null ? (float) $ultimaMovimentacao->saldo_apos : $limiteCredito;
            $novoSaldoCC = $saldoAtual + (float) $request->valor;

            // 4. 🔥 INSERE O PAGAMENTO NA TABELA VINCULANDO O VENDA_ID RECEBIDO
            $ccId = DB::table('cliente_conta_correntes')->insertGetId([
                'cliente_id'         => $id,
                'venda_id'           => $request->input('venda_id'), 
                'pagamento_venda_id' => null, 
                'tipo'               => 'credito', 
                'origem'             => 'pagamento', 
                'valor'              => (float) $request->valor,
                'saldo_apos'         => $novoSaldoCC, 
                'descricao'          => "Recebimento de pagamento / amortização de carteira via " . strtoupper($request->meio_captura),
                'created_at'         => now(),
                'updated_at'         => now()
            ]);

            // 5. REGRA DE DESBLOQUEIO AUTOMÁTICO
            if (round($novoSaldoCC, 2) >= round($limiteCredito, 2)) {
                DB::table('cliente_creditos')->where('cliente_id', $id)->update([
                    'status' => 'ativo',
                    'updated_at' => now()
                ]);

                DB::table('clientes')->where('id', $id)->update([
                    'bloqueado_credito'     => 0,
                    'data_bloqueio_credito' => null,
                    'ativo'                 => '1', 
                    'updated_at'            => now()
                ]);
            }

            // 6. REGISTRA A AUDITORIA CONTÁBIL
            DB::table('cliente_historico_creditos')->insert([
                'cliente_id'     => $id,
                'tipo_evento'    => 'desbloqueio',
                'descricao'      => "Quitação de saldo devedor via " . strtoupper($request->meio_captura) . " no valor de R$ " . number_format($request->valor, 2, ',', '.') . ". Vinculado à Venda ID #" . $request->input('venda_id'),
                'score_anterior' => DB::table('clientes')->where('id', $id)->value('score_credito') ?? 100,
                'score_novo'     => DB::table('clientes')->where('id', $id)->value('score_credito') ?? 100,
                'created_at'     => now()
            ]);

            // 7. REGISTRA NO FLUXO DE CAIXA DO PDV
            DB::table('movimentacoes_caixa')->insert([
                'caixa_id'          => $caixaAtivo->id,
                'user_id'           => $usuarioLogado->id, 
                'tipo'              => 'entrada', 
                'forma_pagamento'   => $request->meio_captura, 
                'valor'             => (float) $request->valor,
                'valor_auditado'    => 0.00,
                'origem_id'         => $ccId, 
                'observacao'        => "Recebimento de saldo de carteira. Cliente ID #{$id}", 
                'data_movimentacao' => now(),
                'created_at'        => now(),
                'updated_at'        => now()
                ]);

                \Illuminate\Support\Facades\Cache::forget("cliente_saldo_{$id}");

                // Retorna os dois dados capturados dentro do escopo seguro do banco
                return [
                    'pagamento_id'     => $ccId,
                    'saldo_disponivel' => $novoSaldoCC
                ];
            });

            // 💎 RETORNO JSON COMPLETAMENTE CORRIGIDO (DADOS COMPATÍVEIS COM MÚLTIPLOS PDVS)
            return response()->json([
                'status'  => 'success',
                'message' => 'Pagamento vinculado à venda com sucesso!',
                'dados'   => [
                    'pagamento_id'     => $retornoTransacao['pagamento_id'], // ➔ Agora o ID é entregue com sucesso!
                    'saldo_disponivel' => $retornoTransacao['saldo_disponivel']
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }


      /**
     * Busca os dados de um pagamento específico de carteira no banco.
     */
//    public function obterPagamento(\Illuminate\Http\Request $request)
// {
//     // 1. Lê e valida o ID recebido do JavaScript
//     $pagamentoId = (int) $request->input('pagamento_id');

//     if ($pagamentoId <= 0) {
//         return response()->json([
//             'success' => false, 
//             'erro'    => 'Código do pagamento inválido para a busca.'
//         ], 422);
//     }

//     // 🧠 CONSULTA DIRETA NO BANCO DE DADOS (Unificando a movimentação com os dados do cliente)
//     // Ajuste o nome da tabela 'clientes' caso no seu banco ela tenha outro nome (ex: users)
//     $pagamento = \Illuminate\Support\Facades\DB::table('cliente_conta_correntes')
//         ->join('clientes', 'clientes.id', '=', 'cliente_conta_correntes.cliente_id')
//         ->where('cliente_comment_correntes.id', $pagamentoId) // Garante a busca pela chave primária correta
//         ->select(
//             'cliente_conta_correntes.id',
//             'cliente_conta_correntes.valor',
//             'cliente_conta_correntes.saldo_apos',
//             'cliente_conta_correntes.origem',
//             'cliente_conta_correntes.created_at',
//             'clientes.nome as cliente_nome' // Já traz o nome do cliente mastigado pro JS
//         )
//         ->first();

//     // 2. Se o registro sumiu ou não foi gerado a tempo, aborta com segurança
//     if (!$pagamento) {
//         return response()->json([
//             'success' => false, 
//             'erro'    => 'Pagamento não localizado no banco de dados.'
//         ], 404);
//     }

//     // 3. RETORNO ESTRUTURADO (Entrega exatamente o objeto que o JavaScript precisa mapear)
//     return response()->json([
//         'success' => true,
//         'dados'   => [
    //             'valor'            => (float) $pagamento->valor,
//             'id'               => $pagamento->id,
//             'cliente'          => $pagamento->cliente_nome,
//             'saldo_disponivel' => (float) $pagamento->saldo_apos,
//             'meio'             => $pagamento->origem, // Entrega o tipo de captura original
//             'data_hora'        => date('d/m/Y H:i:s', strtotime($pagamento->created_at))
// }
//         ]
//     ]);

    public function exibirComprovante($id)
    {
        // 🧠 CONSULTA DIRETA NO BANCO DE DADOS: Busca os dados reais gravados na linha informada
        $pagamento = \Illuminate\Support\Facades\DB::table('cliente_conta_correntes')
            ->join('clientes', 'clientes.id', '=', 'cliente_conta_correntes.cliente_id')
            ->where('cliente_conta_correntes.id', (int) $id)
            ->select(
                'cliente_conta_correntes.valor',
                'cliente_conta_correntes.saldo_apos',
                'cliente_conta_correntes.origem',
                'cliente_conta_correntes.created_at',
                'clientes.nome as cliente_nome'
            )
            ->first();

        // Se por acaso o ID não existir no banco, exibe um aviso 404 seguro
        if (!$pagamento) {
            abort(404, 'Comprovante não localizado no sistema.');
        }

        // Formata os dados para exibição limpa no papel A4 da sua Epson L3150
        $cliente   = $pagamento->cliente_nome;
        $valor     = number_format($pagamento->valor, 2, ',', '.');
        $meio      = strtoupper($pagamento->origem ?: 'DINHEIRO');
        $saldo     = number_format($pagamento->saldo_apos, 2, ',', '.');
        $dataHora  = date('d/m/Y H:i:s', strtotime($pagamento->created_at));

        // Retorna o HTML estruturado em duas vias para folha A4 comum
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <title>Comprovante de Pagamento - Carteira</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; font-size: 14px; color: #000; background: #fff; }
                .via { border: 2px dashed #000; padding: 30px; margin-bottom: 50px; background: #fff; }
                .txt-center { text-align: center; }
                .titulo { font-size: 22px; font-weight: bold; margin-bottom: 5px; }
                hr { border: 0; border-top: 1px solid #000; margin: 15px 0; }
                .linha-dados { display: flex; justify-content: space-between; margin-bottom: 8px; }
                .valor-destaque { font-size: 20px; font-weight: bold; margin: 15px 0; }
                .bloco-assinatura { margin-top: 50px; text-align: center; }
                @media print { .quebra-pagina { page-break-after: always; break-after: page; height: 0; } }
            </style>
        </head>
        <body>

            <!-- ================= VIA 1: CLIENTE ================= -->
            <div class='via'>
                <div class='txt-center titulo'>DEPÓSITO DE MATERIAIS</div>
                <div class='txt-center'><b>COMPROVANTE DE PAGAMENTO DE CARTEIRA</b></div>
                <div class='txt-center'>--- VIA DO CLIENTE ---</div>
                <hr>
                <div class='linha-dados'><span><b>Data/Hora:</b> $dataHora</span> <span><b>Meio de Pgto:</b> $meio</span></div>
                <div><b>Cliente:</b> $cliente</div>
                <hr>
                <div class='valor-destaque'>VALOR PAGO: R$ $valor</div>
                <div><b>Saldo Restante na Carteira:</b> R$ $saldo</div>
                <hr>
                <div class='bloco-assinatura'>______________________________________________________<br>Assinatura do Cliente</div>
            </div>

            <div class='quebra-pagina'></div>

            <!-- ================= VIA 2: CAIXA ================= -->
            <div class='via'>
                <div class='txt-center titulo'>DEPÓSITO DE MATERIAIS</div>
                <div class='txt-center'><b>COMPROVANTE DE PAGAMENTO DE CARTEIRA</b></div>
                <div class='txt-center'>--- VIA DO CAIXA ---</div>
                <hr>
                <div class='linha-dados'><span><b>Data/Hora:</b> $dataHora</span> <span><b>Meio de Pgto:</b> $meio</span></div>
                <div><b>Cliente:</b> $cliente</div>
                <hr>
                <div class='valor-destaque'>VALOR PAGO: R$ $valor</div>
                <div><b>Saldo Restante na Carteira:</b> R$ $saldo</div>
                <hr>
                <div class='bloco-assinatura'>______________________________________________________<br>Controle Interno do Caixa</div>
            </div>

                <!-- Final do seu HTML das duas vias -->
            <script>
                window.onload = function() { 
                    // 1. Dispara o painel de impressão da Epson normalmente
                    window.print(); 
                    
                    // 2. 🛡️ REGRA DE OURO DO PDV: Escuta quando o operador FECHA o painel da impressora
                    // Não importa se ele clicou em IMPRIMIR ou em CANCELAR.
                    window.addEventListener('afterprint', function() {
                        // Dá um pequeno respiro visual e fecha a aba de forma segura
                        // setTimeout(function() { 
                        //     window.close(); 
                        // }, 20000);
                    });
                };
            </script>
        </body>
        </html>
        ";
    }



}
