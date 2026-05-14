<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteContaCorrente;
use App\Services\ContaCorrenteService;
use App\Services\CreditoService;

class ContaCorrenteController extends Controller
{
    public function show($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);

        $movimentacoes = ClienteContaCorrente::where('cliente_id', $cliente->id)
            ->orderBy('id', 'desc')
            ->paginate(20);

        $saldo = app(ContaCorrenteService::class)
            ->saldoAtual($cliente->id);

        return view('clientes.conta_corrente.show', compact(
            'cliente',
            'movimentacoes',
            'saldo'
        ));
    }
    
    public function infoClienteFinanceiro($clienteId)
    {
        $cliente = Cliente::with(['creditoAtivo', 'contaCorrente'])
            ->findOrFail($clienteId);
        $contaService = app(\App\Services\ContaCorrenteService::class);

        $credito = $cliente->credito; // ✔️ primeiro define

        $status = $credito ? $credito->status : 'inativo'; // ✔️ depois usa;

        $limite = ($status === 'ativo')
            ? (float) ($credito->limite_credito ?? 0)
            : 0;

        $credito = $cliente->creditoAtivo;

        // Limite disponível apenas se crédito ativo
        $limite = ($credito && $credito->status === 'ativo')
            ? (float) $credito->limite_credito
            : 0;

        // Saldo da conta corrente (pode retornar null se nunca houve movimentação)
        $saldoConta = $contaService->saldoAtual($cliente->id);

        /**
         * REGRA DE NEGÓCIO:
         * - sem movimentação: saldo = limite_credito
         * - com movimentação: saldo = limite_credito + saldo corrente
         */
        $saldoFinal = is_null($saldoConta)
            ? $limite
            : $limite + (float) $saldoConta;

        // Possui conta corrente vinculada
        $temContaCorrente = $cliente->contaCorrente()->exists();

        // Possui movimentação financeira
        $temMovimento = !is_null($saldoConta);
       
        \Log::info('Saldo conta:', [
            'saldo' => $contaService->saldoAtual($cliente->id)
        ]);

        return response()->json([
            'cliente' => $cliente->nome,
            'saldo_apos' => $saldoFinal,
            'limite_credito' => $limite,
            'tem_conta_corrente' => $temContaCorrente,
            'tem_movimento' => $temMovimento,
            'conta_corrente' => $cliente->contaCorrente,
            // 🔥 AQUI AGORA FUNCIONA
            'status_credito' => $status,

            'tem_conta_corrente' => $cliente->contaCorrente()->exists(),
        ]);
    }

}