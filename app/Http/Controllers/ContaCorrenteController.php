<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteContaCorrente;
use App\Services\ContaCorrenteService;
use App\Services\CreditoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ContaCorrenteController extends Controller
{
    /**
     * Exibe a view do extrato de movimentações do cliente.
     */
    public function show($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        
        $movimentacoes = ClienteContaCorrente::where('cliente_id', $cliente->id)
            ->orderBy('id', 'desc')
            ->paginate(20);

        $saldo = app(ContaCorrenteService::class)->saldoAtual($cliente->id);

        return view('clientes.conta_corrente.show', compact(
            'cliente',
            'movimentacoes',
            'saldo'
        ));
    }

    /**
     * Retorna as informações financeiras consolidadas para o Modal do PDV.
     */
    public function infoClienteFinanceiro($clienteId)
    {
        // Carrega o cliente trazendo a relação configurada no ecossistema
        $cliente = Cliente::with(['creditoAtivo'])->findOrFail($clienteId);
        $contaService = app(ContaCorrenteService::class);

        $credito = $cliente->creditoAtivo;
        
        // 🔥 CORREÇÃO: Captura o status real do banco. Se não houver registro, assume inativo
        $statusBanco = $credito ? $credito->status : 'inativo';
        $limite = $credito ? (float)$credito->limite_credito : 0;

        // 🔥 CORREÇÃO MATEMÁTICA: O Service já entrega o saldo líquido calculado perfeitamente
        $saldoReal = $contaService->saldoAtual($cliente->id);

        // 🔥 REQUISITO: Se o status for bloqueado no banco OU se o saldo for menor ou igual a zero, força bloqueado
        $statusFinal = ($statusBanco === 'bloqueado' || $saldoReal <= 0) ? 'bloqueado' : $statusBanco;

        $creditoUsado = $limite - $saldoReal;

        // Verifica se há alguma linha de histórico na tabela
        $temMovimento = ClienteContaCorrente::where('cliente_id', $cliente->id)->exists();

        // 🔥 CORREÇÃO DE CHAVES: Retorna exatamente o mapeamento que o JavaScript do seu PDV espera ler
        return response()->json([
            'success' => true,
            'cliente' => [
                'id'   => $cliente->id,
                'nome' => $cliente->nome,
            ],
            'saldo'         => $saldoReal,       // Sincronizado com o JS
            'limite'        => $limite,          // Sincronizado com o JS
            'credito_usado' => $creditoUsado > 0 ? $creditoUsado : 0,
            'status'        => $statusFinal,     // Sincronizado com o JS e com a regra de saldo <= 0
            'tem_movimento' => $temMovimento,
            'formas_pagamento' => []             // Mantido para compatibilidade do front
        ]);
    }

    /**
     * 🔥 NOVO MÉTODO: Processa a entrada de dinheiro no caixa para receber/abater o fiado do cliente.
     */
    public function receberPagamentoFiado(Request $request, int $id, ContaCorrenteService $contaCorrenteService)
    {
        $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'forma_pagamento' => 'required|in:dinheiro,pix,cartao_debito,cartao_credito',
            'descricao' => 'nullable|string|max:255'
        ]);

        $valorPago = (float) $request->input('valor');
        $formaInput = $request->input('forma_pagamento');
        $descricaoInput = $request->input('descricao') ?? "Recebimento de conta/fiado via " . strtoupper($formaInput);

        $cliente = Cliente::with(['creditoAtivo'])->find($id);

        if (!$cliente) {
            return response()->json(['success' => false, 'erro' => 'Cliente não encontrado.'], 404);
        }

        DB::beginTransaction();
        try {
            $saldoAtual = $contaCorrenteService->saldoAtual($id);
            $novoSaldo = $saldoAtual + $valorPago;

            // Insere o registro de entrada (crédito)
            DB::table('cliente_conta_correntes')->insert([
                'cliente_id'         => $cliente->id,
                'venda_id'           => null,
                'pagamento_venda_id' => null,
                'tipo'               => 'credito',
                'origem'             => 'recebimento',
                'valor'              => $valorPago,
                'saldo_apos'         => $novoSaldo,
                'descricao'          => $descricaoInput,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // Se o saldo voltou a ficar positivo, remove o bloqueio automático de saldo estourado
            if ($novoSaldo > 0 && optional($cliente->creditoAtivo)->status === 'bloqueado') {
                $cliente->creditoAtivo->status = 'ativo';
                $cliente->creditoAtivo->save();
            }

            DB::commit();

            Cache::forget("cliente_saldo_{$cliente->id}");

            return response()->json([
                'success' => true,
                'mensagem' => 'Pagamento recebido com sucesso!',
                'novo_saldo' => $novoSaldo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'erro' => 'Erro interno ao salvar recebimento: ' . $e->getMessage()
            ], 500);
        }
    }
}
