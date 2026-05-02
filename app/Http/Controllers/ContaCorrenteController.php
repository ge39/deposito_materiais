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
    
    // public function infoClienteFinanceiro($clienteId)
    // {
    //     $cliente = Cliente::findOrFail($clienteId);

    //     $conta = app(ContaCorrenteService::class);
    //     $credito = app(CreditoService::class);

    //     return response()->json([
    //         'cliente' => $cliente->nome,

    //         // 💰 carteira (saldo real)
    //         'saldo_carteira' => $conta->saldoAtual($cliente->id),

    //         // 🧾 crédito
    //         'limite_credito' => $cliente->limite_credito,
    //         'credito_usado' => $credito->saldoDevedor($cliente),

    //         // 💳 formas permitidas no PDV
    //         'formas_pagamento' => $credito->formasPermitidas($cliente)
    //     ]);
    // }
    // public function infoClienteFinanceiro($clienteId)
    // {
    //     $cliente = Cliente::findOrFail($clienteId);

    //     $conta = app(ContaCorrenteService::class);
    //     $credito = app(CreditoService::class);

    //     $saldoCarteira = $conta->saldoAtual($cliente->id);
    //     $creditoUsado = $credito->saldoDevedor($cliente);
    //     $limiteCredito = $cliente->limite_credito ?? 0;

    //     $limiteDisponivel = max(0, $limiteCredito - $creditoUsado);

    //     return response()->json([
    //         'cliente' => [
    //             'id' => $cliente->id,
    //             'nome' => $cliente->nome,
    //             'tipo' => $cliente->tipo
    //         ],

    //         'financeiro' => [
    //             'carteira' => [
    //                 'saldo' => $saldoCarteira
    //             ],

    //             'credito' => [
    //                 'limite' => $limiteCredito,
    //                 'usado' => $creditoUsado,
    //                 'disponivel' => $limiteDisponivel
    //             ]
    //         ],

    //         'formas_pagamento' => $credito->formasPermitidas($cliente)
    //     ]);
    // }
    //saldo simplificado
    // public function infoClienteFinanceiro($clienteId)
    // {
    //     $cliente = Cliente::with('creditoAtivo')->findOrFail($clienteId);

    //     $conta = app(\App\Services\ContaCorrenteService::class);

    //     $credito = $cliente->creditoAtivo;

    //     // só usa limite se estiver ativo
    //     $limite = ($credito && $credito->status === 'ativo')
    //         ? (float) $credito->limite_credito
    //         : 0;

    //     // 🔥 verifica se existe movimentação
    //     $temMovimento = $cliente->contaCorrente()->exists();

    //     $saldoConta = $conta->saldoAtual($cliente->id);

    //     // 🔥 REGRA CORRETA
    //     if (!$temMovimento) {
    //         $saldoFinal = $limite;
    //     } else {
    //         $saldoFinal = $saldoConta;
    //     }

    //     return response()->json([
    //         'cliente' => $cliente->nome,
    //         'saldo_apos' => $saldoFinal,
    //         'limite_credito' => $limite,
    //     ]);
    // }
    // public function infoClienteFinanceiro($clienteId)
    // {
    //     $cliente = Cliente::with(['creditoAtivo', 'contaCorrente'])->findOrFail($clienteId);

    //     $conta = app(\App\Services\ContaCorrenteService::class);

    //     $credito = $cliente->creditoAtivo;

    //     // só usa limite se estiver ativo
    //     $limite = ($credito && $credito->status === 'ativo')
    //         ? (float) $credito->limite_credito
    //         : 0;

    //     // 🔥 verifica se existe movimentação (mantido)
    //     $temMovimento = $cliente->contaCorrente()->exists();

    //     $saldoConta = $conta->saldoAtual($cliente->id);

    //     // 🔥 REGRA CORRIGIDA
    //     if (!$temMovimento) {
    //         $saldoFinal = $limite;
    //     } else {
    //         $saldoFinal = $limite + $saldoConta;
    //     }

    //     // 🔥 NOVO: verifica se existe conta corrente (independente de movimentação)
    //     $temContaCorrente = !is_null($cliente->contaCorrente);

    //     $saldoConta = $conta->saldoAtual($cliente->id);

    //     // 🔥 REGRA ORIGINAL (mantida)
    //     if (!$temMovimento) {
    //         $saldoFinal = $limite;
    //     } else {
    //         $saldoFinal = $saldoConta;
    //     }

    //     return response()->json([
    //         'cliente' => $cliente->nome,
    //         'saldo_apos' => $saldoFinal,
    //         'limite_credito' => $limite,

    //         // 🔥 NOVOS CAMPOS (não quebra nada existente)
    //         'tem_conta_corrente' => $temContaCorrente,
    //         'tem_movimento' => $temMovimento,

    //         // opcional: mandar objeto completo se precisar no front
    //         'conta_corrente' => $cliente->contaCorrente,
    //     ]);
    // }

    public function infoClienteFinanceiro($clienteId)
    {
        $cliente = Cliente::with(['creditoAtivo', 'contaCorrente'])
            ->findOrFail($clienteId);

        $contaService = app(\App\Services\ContaCorrenteService::class);

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

        return response()->json([
            'cliente' => $cliente->nome,
            'saldo_apos' => $saldoFinal,
            'limite_credito' => $limite,
            'tem_conta_corrente' => $temContaCorrente,
            'tem_movimento' => $temMovimento,
            'conta_corrente' => $cliente->contaCorrente,
        ]);
    }
}