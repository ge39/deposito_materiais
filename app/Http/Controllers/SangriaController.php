<?php

namespace App\Http\Controllers;

use App\Models\Sangria;
use App\Models\Caixa;
use App\Models\Empresa;
use App\Models\MovimentacaoCaixa;
use App\Models\SangriaConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // <-- ADICIONE ISSO
use Illuminate\Validation\ValidationException; // <-- ADICIONE ISSO

class SangriaController extends Controller
{
    /**
     * Exibe o formulário de sangria para um caixa.
     */
  
    public function criarForm(Caixa $caixa)
    {
        // Centraliza regras de sangria no model
        $verificacao = $caixa->verificarSangria();

        $ultimaSangria = Sangria::where('caixa_id', $caixa->id)
            ->latest('id')
            ->first();

        /// Buscar a empresa ativa correspondente ao caixa
       // Pega a configuração de sangria cuja empresa esteja ativa
        $configSangria = SangriaConfig::with(['empresa' => function($query) {
            $query->where('ativo', 1);
        }])->first();

        // $configSangria = \App\Models\SangriaConfig::where('empresa_id', $caixa->empresa_id)->first();

        // Verifica se veio alguma config com empresa ativa
        if (!$configSangria || !$configSangria->empresa) {
            abort(404, 'Nenhuma configuração de sangria encontrada para empresa ativa.');
        }

        // Agora você tem:
        // $configSangria->valor_limite
        // $configSangria->empresa->nome, $configSangria->empresa->id, etc.
        $valorLimite = $configSangria->valor_limite;
        $empresa = $configSangria->empresa;

        return response()
        ->view('pdv.sangria_form', [
            'caixa' => $caixa,
            'saldoAtual' => $verificacao['saldoAtual'],
            'codigo_operacao' => $ultimaSangria->codigo_operacao ?? 0,
            'limiteSangria'        => $verificacao['limiteSangria'], // 🎯 ENVIADO R$ 200,00 CORRETAMENTE AQUI
            'limiteBloqueio' => $verificacao['limiteBloqueio'],
            'avisarSangria' => $verificacao['avisarSangria'],
            'bloquearPDV' => $verificacao['bloquearPDV'],
            'ultimaSangria' => $ultimaSangria,
            'configSangria', // 👈 FALTAVA ISSO
            'valorSugeridoSangria' => max(
                0,
                $verificacao['saldoAtual'] - $valorLimite
            ),
        ])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
    }
      
    // public function registrar(Request $request, Caixa $caixa)
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
    //             'tipo'              => 'Saida_manual',
    //             'forma_pagamento'   => 'Sangria',
    //             'origem_id'         => $sangria->id,
    //             'valor'             => $valor,
    //             'data_movimentacao' => now(),
    //             'observacao'        => 'Sangria realizada manualmente'
    //         ]);
    //     });
        
        

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Sangria registrada com sucesso.',
    //         'redirect' => route('caixa.sangria.form', $caixa->id),
    //     ]);
    // }

    // public function registrar(Request $request, Caixa $caixa)
    // {
    //     $request->validate([
    //         'valor' => 'required|numeric|min:0.01',
    //         'motivo' => 'required|string'
    //     ]);

    //     // Guarda a instância criada para pegar o ID gerado pelo banco
    //     $sangria = DB::transaction(function () use ($request, $caixa) {
    //         $valor = (float) $request->valor;
    //         $saldoAntes = $caixa->saldoDinheiroAtual();

    //         if ($valor > $saldoAntes) {
    //             throw ValidationException::withMessages([
    //                 'valor' => 'Valor maior que o saldo disponível.'
    //             ]);
    //         }

    //         $saldoDepois = $saldoAntes - $valor;

    //         $sangriaCriada = Sangria::create([
    //             'empresa_id' => $caixa->empresa_id,
    //             'caixa_id' => $caixa->id,
    //             'user_id' => auth()->id(),
    //             'codigo_operacao' => 'SNG-' . $caixa->id . '-' . now()->format('YmdHis'),
    //             'numero_pdv' => $caixa->id,
    //             'valor' => $valor,
    //             'saldo_antes' => $saldoAntes,
    //             'saldo_depois' => $saldoDepois,
    //             'motivo' => $request->motivo,
    //             'impresso' => 0,
    //         ]);

    //         MovimentacaoCaixa::create([
    //             'caixa_id' => $caixa->id,
    //             'user_id' => auth()->id(),
    //             'tipo' => 'Saida_manual',
    //             'forma_pagamento' => 'Sangria',
    //             'origem_id' => $sangriaCriada->id,
    //             'valor' => $valor,
    //             'data_movimentacao' => now(),
    //             'observacao' => 'Sangria realizada manualmente'
    //         ]);

    //         return $sangriaCriada; // Retorna o objeto para fora do bloco transacional
    //     ]);
    

    //     // Retorna as chaves lidas pelo JavaScript para remontar a tela síncrona
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Sangria registrada com sucesso.',
    //         'sangria_id' => $sangria->id, // 👈 ENVIADO PARA EXIBIR O BOTÃO IMPRIMIR
    //         'saldo_atual' => $caixa->saldoDinheiroAtual(), // 👈 ENVIADO PARA RECALCULAR O VALOR EM TELA
    //     ]);
    // }

    public function registrar(Request $request, Caixa $caixa) 
    {
        $request->validate([
            'valor'  => 'required|numeric|min:0.01',
            'motivo' => 'required|string'
        ]);

        try {
            // Executa a transação e captura a instância gerada no banco de dados
            $sangria = DB::transaction(function () use ($request, $caixa) {
                $valor = round((float) $request->valor, 2); // Garante precisão de centavos
                $saldoAntes = round((float) $caixa->saldoDinheiroAtual(), 2);

                if ($valor > $saldoAntes) {
                    throw ValidationException::withMessages([
                        'valor' => 'Valor maior que o saldo disponível em caixa.'
                    ]);
                }

                $saldoDepois = round($saldoAntes - $valor, 2);

                // 1️⃣ Cria o registro na tabela sangrias
                $sangriaCriada = Sangria::create([
                    'empresa_id'      => $caixa->empresa_id,
                    'caixa_id'        => $caixa->id,
                    'user_id'         => auth()->id(),
                    'codigo_operacao' => 'SNG-' . $caixa->id . '-' . now()->format('YmdHis'),
                    'numero_pdv'      => $caixa->id,
                    'valor'           => $valor,
                    'saldo_antes'     => $saldoAntes,
                    'saldo_depois'    => $saldoDepois,
                    'motivo'          => $request->motivo,
                    'impresso'        => 0,
                ]);

                // 2️⃣ Cria o histórico na tabela movimentacoes_caixa
                MovimentacaoCaixa::create([
                    'caixa_id'          => $caixa->id,
                    'user_id'           => auth()->id(),
                    'tipo'              => 'Saida_manual',
                    'forma_pagamento'   => 'Sangria',
                    'origem_id'         => $sangriaCriada->id,
                    'valor'             => $valor,
                    'data_movimentacao' => now(),
                    'observacao'        => 'Sangria realizada manualmente'
                ]);

                return $sangriaCriada; 
            });

            // 3️⃣ Retorna a resposta JSON limpa lida pelo seu AJAX do Blade
            return response()->json([
                'success'     => true,
                'message'     => 'Sangria registrada com sucesso.',
                'sangria_id'  => $sangria->id, // Mapeia o ID para montar a URL de impressão
                'saldo_atual' => round((float) $caixa->saldoDinheiroAtual(), 2), // Devolve o novo saldo exato
            ]);

        } catch (ValidationException $e) {
            // Repassa o erro de validação de saldo de forma nativa para o AJAX ler o campo com erro
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Captura falhas inesperadas de banco de dados
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar a operação: ' . $e->getMessage()
            ], 500);
        }
    }
   

    /**
     * Exibe a impressão da sangria.
     */
    public function imprimir(Sangria $sangria)
    {
        return view('pdv.sangria_impressao', compact('sangria'));
    }
}