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
       public function pagarCredito(Request $request, $id)
    {
        $request->validate([
            'valor'        => 'required|numeric|min:0.01',
            'meio_captura' => 'required|string|in:dinheiro,pix,debito'
        ]);

        $cliente = Cliente::findOrFail($id);

        try {
            $novoSaldo = DB::transaction(function () use ($request, $cliente) {
                
                // 1. Executa a entrada de saldo na Conta Corrente (Chama seu Service)
                $saldoCalculado = $this->contaCorrenteService->adicionarCredito(
                    $cliente->id,
                    (float) $request->valor
                );

                // 2. 🔥 GRAVAÇÃO COMPLEMENTAR DO HISTÓRICO (Garante a submissão para a auditoria)
                DB::table('cliente_historico_creditos')->insert([
                    'cliente_id'     => $cliente->id,
                    'tipo_evento'    => 'desbloqueio', // Tipo padrão mapeado na sua migration
                    'descricao'      => "Recebimento de pagamento via " . strtoupper($request->meio_captura) . " no valor de R$ " . number_format($request->valor, 2, ',', '.'),
                    'score_anterior' => $cliente->score_credito, // Respeitando a estrutura da sua tabela
                    'score_novo'     => $cliente->score_credito,
                    'created_at'     => now()
                ]);

                // Opcional: Se tiver tabela de movimentacao de caixa do operador, ela entra aqui também.

                return $saldoCalculado;
            });

            // Devolve o JSON exato que o seu FETCH JavaScript está esperando ler lá na linha 25 (data.dados.saldo_disponivel)
            return response()->json([
                'status'  => 'success',
                'message' => 'Pagamento processado com sucesso!',
                'dados'   => [
                    'saldo_disponivel' => $novoSaldo
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
}
