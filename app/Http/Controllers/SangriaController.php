<?php

namespace App\Http\Controllers;

use App\Models\Sangria;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // <-- ADICIONE ISSO
use Illuminate\Validation\ValidationException; // <-- ADICIONE ISSO

class SangriaController extends Controller
{
    /**
     * Exibe o formulário de sangria para um caixa.
     */
    // public function criarForm(Caixa $caixa)
    // {
    //     // Pega os dados de sangria e bloqueio do caixa
    //     $verificacao = $caixa->verificarSangria();
    //      $saldoAtual = $caixa->saldoDinheiroAtual();
    //     $limiteSangria = 1000; // exemplo
    //     $bloquearPDV = false;  // sua regra

    //     $ultimaSangria = Sangria::where('caixa_id', $caixa->id)
    //         ->latest()
    //         ->first();

    //     return view('pdv.sangria_form', [
    //         'caixa' => $caixa,
    //         'saldoAtual' => $verificacao['saldoAtual'],
    //         'limiteSangria' => $verificacao['limiteSangria'],
    //         'limiteBloqueio' => $verificacao['limiteBloqueio'],
    //         'avisarSangria' => $verificacao['avisarSangria'],
    //         'bloquearPDV' => $verificacao['bloquearPDV'],
    //         'ultimaSangria' => $ultimaSangria,
    //         'valorSugeridoSangria' => max(0, $verificacao['saldoAtual'] - $verificacao['limiteSangria']),
    //     ]);
    // }
    public function criarForm(Caixa $caixa)
    {
        // Centraliza regras de sangria no model
        $verificacao = $caixa->verificarSangria();

        $ultimaSangria = Sangria::where('caixa_id', $caixa->id)
            ->latest('id') // mais performático que created_at
            ->first();

        return response()
            ->view('pdv.sangria_form', [
                'caixa' => $caixa,
                'saldoAtual' => $verificacao['saldoAtual'],
                'codigo_operacao' => $ultimaSangria->codigo_operacao ?? 0,
                'limiteSangria' => $verificacao['limiteSangria'],
                'limiteBloqueio' => $verificacao['limiteBloqueio'],
                'avisarSangria' => $verificacao['avisarSangria'],
                'bloquearPDV' => $verificacao['bloquearPDV'],
                'ultimaSangria' => $ultimaSangria,
                'valorSugeridoSangria' => max(
                    0,
                    $verificacao['saldoAtual'] - $verificacao['limiteSangria']
                ),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
      
    //  public function registrar(Request $request, Caixa $caixa)
    // {
    //     $request->validate([
    //         'valor'  => 'required|numeric|min:0.01',
    //         'motivo' => 'required|string'
    //     ]);

    //     DB::transaction(function () use ($request, $caixa) {

    //         $valor = (float) $request->valor;
    //         $saldoAntes = $caixa->saldoDinheiroAtual();

    //         if ($valor > $saldoAntes) {
    //             throw ValidationException::withMessages([
    //                 'valor' => 'Valor maior que o saldo disponível.'
    //             ]);
    //         }

    //         $saldoDepois = $saldoAntes - $valor;

    //         $sangria = Sangria::create([
    //             'empresa_id'   => $caixa->empresa_id,
    //             'caixa_id'     => $caixa->id,
    //             'user_id'      => auth()->id(),
    //             'codigo_operacao' => 'SNG-' . $caixa->id . '-' . now()->format('YmdHis'),
    //             'numero_pdv'   => $caixa->id,
    //             'valor'        => $valor,
    //             'saldo_antes'  => $saldoAntes,
    //             'saldo_depois' => $saldoDepois,
    //             'motivo'       => $request->motivo,
    //             'impresso'     => 0,
    //         ]);

    //         MovimentacaoCaixa::create([
    //             'caixa_id'          => $caixa->id,
    //             'user_id'           => auth()->id(),
    //             'tipo'              => 'sangria',
    //             'forma_pagamento'   => 'Sangria',
    //             'origem_id'         => $sangria->id,
    //             'valor'             => $valor,
    //             'data_movimentacao' => now(),
    //             'observacao'        => 'Sangria realizada'
    //         ]);
    //     });
        
        

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Sangria registrada com sucesso.',
    //         'redirect' => route('caixa.sangria.form', $caixa->id),
    //     ]);
    // }
        
    public function registrar(Request $request, Caixa $caixa)
    {
        $request->validate([
            'valor'  => 'required|numeric|min:0.01',
            'motivo' => 'required|string'
        ]);

        DB::transaction(function () use ($request, $caixa) {

            $valor = (float) $request->valor;
            $saldoAntes = $caixa->saldoDinheiroAtual();

            if ($valor > $saldoAntes) {
                throw ValidationException::withMessages([
                    'valor' => 'Valor maior que o saldo disponível.'
                ]);
            }

            $saldoDepois = $saldoAntes - $valor;

            $sangria = Sangria::create([
                'empresa_id'   => $caixa->empresa_id,
                'caixa_id'     => $caixa->id,
                'user_id'      => auth()->id(),
                'codigo_operacao' => 'SNG-' . $caixa->id . '-' . Str::uuid(),
                'numero_pdv'   => $caixa->id,
                'valor'        => $valor,
                'saldo_antes'  => $saldoAntes,
                'saldo_depois' => $saldoDepois,
                'motivo'       => $request->motivo,
                'impresso'     => 0,
            ]);

            MovimentacaoCaixa::create([
                'caixa_id'          => $caixa->id,
                'user_id'           => auth()->id(),
                'tipo'              => 'saida_manual',
                'forma_pagamento'   => 'Sangria',
                'origem_id'         => $sangria->id,
                'valor'             => $valor,
                'data_movimentacao' => now(),
                'observacao'        => 'Sangria realizada'
            ]);
        });

        return redirect()
            ->route('caixa.sangria.form', $caixa->id)
            ->with('success', 'Sangria registrada com sucesso.');
    }
    /**
     * Exibe a impressão da sangria.
     */
    public function imprimir(Sangria $sangria)
    {
        return view('pdv.sangria_impressao', compact('sangria'));
    }
    }