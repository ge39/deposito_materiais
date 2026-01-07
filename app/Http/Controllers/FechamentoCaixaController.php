<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use Illuminate\Support\Facades\DB;

class FechamentoCaixaController extends Controller
{
    /**
     * Mostra a view de fechamento/auditoria
     */
    public function index($caixaId)
    {
        // Carrega o caixa com vendas, pagamentos e movimentações
        $caixa = Caixa::with([
            'vendas.pagamentos', 
            'movimentacoes.user' // RELACIONAMENTO CORRETO
        ])->findOrFail($caixaId);

        // Totais de entradas e saídas
        $total_entradas = $caixa->movimentacoes
            ->whereIn('tipo', ['abertura', 'venda', 'entrada_manual'])
            ->sum('valor');

        $total_saidas = $caixa->movimentacoes
            ->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])
            ->sum('valor');

        $total_esperado = $caixa->valor_abertura + $total_entradas - $total_saidas;
        $divergencia = ($caixa->valor_fechamento ?? $total_esperado) - $total_esperado;

        // Totais por forma de pagamento do sistema
        $totaisPorForma = [];
        foreach ($caixa->vendas as $venda) {
            foreach ($venda->pagamentos as $pag) {
                if ($pag->status !== 'confirmado') continue;
                $forma = $pag->forma_pagamento;
                if (!isset($totaisPorForma[$forma])) $totaisPorForma[$forma] = 0;
                $totaisPorForma[$forma] += $pag->valor;
            }
        }

        return view('fechamento_caixa.index', compact(
            'caixa',
            'total_entradas',
            'total_saidas',
            'total_esperado',
            'divergencia',
            'totaisPorForma'
        ))->with('movimentacoes', $caixa->movimentacoes);
    }

    /**
     * Lista caixas abertos ou inconsistentes
     */
    public function listaCaixas()
    {
        $caixas = Caixa::with(['usuario', 'terminal'])
            ->whereIn('status', ['aberto', 'inconsistente'])
            ->orderBy('data_abertura', 'asc')
            ->get();

        return view('fechamento_caixa.listaCaixas', compact('caixas'));
    }

    /**
     * Mostra a view para lançamento manual de valores
     */
    public function lancarValores(Caixa $caixa)
    {
        return view('fechamento_caixa.lancar_valores', compact('caixa'));
    }

    /**
     * Lança valores manuais no caixa (entradas e bandeiras)
     */
    public function lancar_valores(Request $request, $caixaId)
    {
        $caixa = Caixa::findOrFail($caixaId);

        // Desbloqueia temporariamente se estiver bloqueado
        if ($caixa->status === 'bloqueado') {
            $caixa->status = 'aberto';
            $caixa->save();
        }

        $userId = auth()->id();

        // Lista de valores por forma de pagamento
        $valores = [
            'dinheiro'        => 'Dinheiro',
            'pix'             => 'Pix',
            'carteira'        => 'Carteira',
            'cartao_debito'   => 'Cartão Débito',
            'cartao_credito'  => 'Cartão Crédito',
        ];

        DB::transaction(function() use ($request, $caixa, $valores, $userId) {
            // Entradas manuais
            foreach ($valores as $campo => $descricao) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id'  => $userId,
                        'tipo'     => 'entrada_manual',
                        'valor'    => $valor,
                        'observacao' => "Lançamento manual: {$descricao}",
                        'data_movimentacao' => now(),
                    ]);
                }
            }

            // Bandeiras de cartão (informativo)
            $bandeiras = [
                'bandeira_visa'       => 'Visa',
                'bandeira_mastercard' => 'Mastercard',
                'bandeira_elo'        => 'Elo',
                'bandeira_amex'       => 'Amex',
                'bandeira_hipercard'  => 'Hipercard',
            ];

            foreach ($bandeiras as $campo => $nome) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id'  => $userId,
                        'tipo'     => 'entrada_manual',
                        'valor'    => $valor,
                        'observacao' => "Lançamento manual: Bandeira {$nome}",
                        'data_movimentacao' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('fechamento.auditar', $caixa->id)
            ->with('success', 'Movimentações lançadas com sucesso!');
    }

    /**
     * Fecha o caixa com registro das movimentações manuais
     */
    // public function fechar(Request $request, Caixa $caixa)
    // {
    //     if ($caixa->status !== 'aberto') {
    //         return redirect()->back()->with('error', 'Este caixa não está aberto.');
    //     }

    //     DB::transaction(function () use ($request, $caixa) {
    //         $userId = auth()->id();

    //         // Entradas manuais
    //         $entradas = [
    //             'entrada_suprimento' => 'Suprimento',
    //             'entrada_ajuste'     => 'Ajuste Positivo',
    //             'entrada_devolucao'  => 'Devolução em Dinheiro',
    //             'entrada_outros'     => 'Outras Entradas',
    //         ];

    //         foreach ($entradas as $campo => $descricao) {
    //             $valor = (float) $request->input($campo, 0);
    //             if ($valor > 0) {
    //                 MovimentacaoCaixa::create([
    //                     'caixa_id' => $caixa->id,
    //                     'user_id'  => $userId,
    //                     'tipo'     => 'entrada_manual',
    //                     'valor'    => $valor,
    //                     'observacao' => $descricao,
    //                     'data_movimentacao' => now(),
    //                 ]);
    //             }
    //         }

    //         // Saídas manuais
    //         $saidas = [
    //             'saida_sangria' => 'Sangria',
    //             'saida_despesa' => 'Despesas',
    //             'saida_ajuste'  => 'Ajuste Negativo',
    //             'saida_outros'  => 'Outras Saídas',
    //         ];

    //         foreach ($saidas as $campo => $descricao) {
    //             $valor = (float) $request->input($campo, 0);
    //             if ($valor > 0) {
    //                 MovimentacaoCaixa::create([
    //                     'caixa_id' => $caixa->id,
    //                     'user_id'  => $userId,
    //                     'tipo'     => 'saida_manual',
    //                     'valor'    => $valor,
    //                     'observacao' => $descricao,
    //                     'data_movimentacao' => now(),
    //                 ]);
    //             }
    //         }

    //         // Registro de fechamento
    //         MovimentacaoCaixa::create([
    //             'caixa_id' => $caixa->id,
    //             'user_id'  => $userId,
    //             'tipo'     => 'fechamento',
    //             'valor'    => array_sum($request->only(['dinheiro','pix','carteira','cartao_debito','cartao_credito'])),
    //             'observacao' => 'Fechamento do caixa',
    //             'data_movimentacao' => now(),
    //         ]);

    //         // Atualiza status do caixa
    //         $caixa->update([
    //             'status'          => 'fechado',
    //             'valor_fechamento'=> array_sum($request->only(['dinheiro','pix','carteira','cartao_debito','cartao_credito'])),
    //             'data_fechamento' => now(),
    //             'fechado_por'     => $userId,
    //         ]);
    //     });

    //     return redirect()
    //         ->route('fechamento.lista')
    //         ->with('success', 'Caixa fechado com sucesso.');
    // }
//     public function fechar(Request $request, $caixaId)
//     {
//         $request->validate([
//             'dinheiro' => 'required|numeric|min:0',
//             'pix' => 'required|numeric|min:0',
//             'carteira' => 'required|numeric|min:0',
//             'cartao_debito' => 'required|numeric|min:0',
//             'cartao_credito' => 'required|numeric|min:0',
//         ]);

//         $valoresFisicos = $request->only(['dinheiro','pix','carteira','cartao_debito','cartao_credito']);

//         DB::transaction(function () use ($caixaId, $valoresFisicos, $request) {

//             // 1️⃣ Bloqueio pessimista para concorrência
//             $caixa = Caixa::with(['vendas.pagamentos'])->lockForUpdate()->findOrFail($caixaId);

//             if (!$caixa->estaAberto()) {
//                 throw new \Exception("Caixa já foi fechado por outro operador.");
//             }

//             // 2️⃣ Totais por forma de pagamento do sistema
//             $formas = ['dinheiro','pix','carteira','cartao_debito','cartao_credito'];
//             $totaisSistema = [];
//             foreach ($formas as $f) {
//                 $totaisSistema[$f] = $caixa->vendas->flatMap->pagamentos
//                     ->where('forma_pagamento',$f)
//                     ->where('status','confirmado')
//                     ->sum('valor');
//             }

//             // 3️⃣ Calcula divergências
//             $divergencias = [];
//             foreach ($formas as $f) {
//                 $dif = $valoresFisicos[$f] - ($totaisSistema[$f] ?? 0);
//                 if ($dif != 0) {
//                     MovimentacaoCaixa::create([
//                         'caixa_id' => $caixa->id,
//                         'user_id' => auth()->id(),
//                         'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
//                         'valor' => abs($dif),
//                         'origem_id' => null,
//                         'observacao' => "Ajuste manual no fechamento: $f",
//                     ]);
//                     $divergencias[$f] = $dif;
//                 }
//             }

//             // 4️⃣ Movimentação de fechamento
//             MovimentacaoCaixa::create([
//                 'caixa_id' => $caixa->id,
//                 'user_id' => auth()->id(),
//                 'tipo' => 'fechamento',
//                 'valor' => array_sum($valoresFisicos),
//                 'origem_id' => null,
//                 'observacao' => !empty($divergencias) ? json_encode($divergencias) : null,
//             ]);

//             // 5️⃣ Atualiza caixa
//             $caixa->update([
//                 'valor_fechamento' => array_sum($valoresFisicos),
//                 'status' => empty($divergencias) ? 'fechado' : 'inconsistente',
//                 'data_fechamento' => now(),
//                 'fechado_por' => auth()->id(),
//                 'observacao_divergencia' => !empty($divergencias) ? json_encode($divergencias) : null,
//             ]);

//         });

//         return redirect()->route('fechamento.lista')
//             ->with('success', 'Caixa fechado com sucesso.');
//     }

    public function fechar(Request $request, $caixaId)
    {
        // Validação dos campos
        $request->validate([
            'dinheiro'       => 'required|numeric|min:0',
            'pix'            => 'required|numeric|min:0',
            'carteira'       => 'required|numeric|min:0',
            'cartao_debito'  => 'required|numeric|min:0',
            'cartao_credito' => 'required|numeric|min:0',
            // Validação opcional para bandeiras
            'bandeira_visa'       => 'nullable|numeric|min:0',
            'bandeira_mastercard' => 'nullable|numeric|min:0',
            'bandeira_elo'        => 'nullable|numeric|min:0',
            'bandeira_amex'       => 'nullable|numeric|min:0',
            'bandeira_hipercard'  => 'nullable|numeric|min:0',
        ]);

        $valoresFisicos = $request->only([
            'dinheiro','pix','carteira','cartao_debito','cartao_credito'
        ]);

        $bandeiras = $request->only([
            'bandeira_visa','bandeira_mastercard','bandeira_elo','bandeira_amex','bandeira_hipercard'
        ]);

        DB::transaction(function () use ($caixaId, $valoresFisicos, $bandeiras) {

            $caixa = Caixa::with(['vendas.pagamentos'])->lockForUpdate()->findOrFail($caixaId);

            if (!$caixa->estaAberto()) {
                throw new \Exception("Caixa já foi fechado por outro operador.");
            }

            $userId = auth()->id();

            // 1️⃣ Registrar cada forma de pagamento como movimentação manual
            $formas = ['dinheiro','pix','carteira','cartao_debito','cartao_credito'];
            $divergencias = [];

            // foreach ($formas as $f) {
            //     $valorFisico = (float) ($valoresFisicos[$f] ?? 0);

            //     // Total do sistema para esta forma
            //     $valorSistema = $caixa->vendas->flatMap->pagamentos
            //         ->where('forma_pagamento', $f)
            //         ->where('status', 'confirmado')
            //         ->sum('valor');

            //     // Criar movimentação para o valor físico informado
            //     if ($valorFisico > 0) {
            //         MovimentacaoCaixa::create([
            //             'caixa_id' => $caixa->id,
            //             'user_id' => $userId,
            //             'tipo' => 'entrada_manual',
            //             'valor' => $valorFisico,
            //             'forma_pagamento' => $f,
            //             'observacao' => "[MANUAL] Forma: $f",
            //             'data_movimentacao' => now(),
            //         ]);
            //     }

            //     // Registrar divergência se houver
            //     $dif = $valorFisico - $valorSistema;
            //     if ($dif != 0) {
            //         MovimentacaoCaixa::create([
            //             'caixa_id' => $caixa->id,
            //             'user_id' => $userId,
            //             'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
            //             'valor' => abs($dif),
            //             'forma_pagamento' => $f,
            //             'observacao' => "[AJUSTE MANUAL] Forma: $f",
            //             'data_movimentacao' => now(),
            //         ]);
            //         $divergencias[$f] = $dif;
            //     }
            // }

            foreach ($formas as $f) {
                $valorFisico = (float) ($valoresFisicos[$f] ?? 0);
                $valorSistema = $caixa->vendas->flatMap->pagamentos
                    ->where('forma_pagamento', $f)
                    ->where('status','confirmado')
                    ->sum('valor');

                $dif = $valorFisico - $valorSistema;
                if ($dif != 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id' => $userId,
                        'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
                        'valor' => abs($dif),
                        'forma_pagamento' => $f,
                        'observacao' => "[AJUSTE MANUAL] Forma: $f",
                        'data_movimentacao' => now(),
                    ]);
                    $divergencias[$f] = $dif;
                }
            }


            // 2️⃣ Registrar cada bandeira de cartão como entrada manual separada
            foreach ($bandeiras as $campo => $valorFisico) {
                $valorFisico = (float) $valorFisico;
                if ($valorFisico <= 0) continue;

                MovimentacaoCaixa::create([
                    'caixa_id' => $caixa->id,
                    'user_id' => $userId,
                    'tipo' => 'entrada_manual',
                    'valor' => $valorFisico,
                    'forma_pagamento' => 'cartao',
                    'bandeira' => str_replace('bandeira_', '', $campo),
                    'observacao' => "[MANUAL] Bandeira " . str_replace('bandeira_', '', $campo),
                    'data_movimentacao' => now(),
                ]);

                // Total do sistema por bandeira (se tiver campo 'bandeira' na tabela pagamentos)
                $valorSistema = $caixa->vendas->flatMap->pagamentos
                    ->where('forma_pagamento', 'cartao')
                    ->where('bandeira', ucfirst(str_replace('bandeira_', '', $campo)))
                    ->where('status','confirmado')
                    ->sum('valor');

                $dif = $valorFisico - $valorSistema;
                if ($dif != 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id' => $userId,
                        'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
                        'valor' => abs($dif),
                        'forma_pagamento' => 'cartao',
                        'bandeira' => str_replace('bandeira_', '', $campo),
                        'observacao' => "[AJUSTE MANUAL] Bandeira " . str_replace('bandeira_', '', $campo),
                        'data_movimentacao' => now(),
                    ]);
                    $divergencias['bandeiras'][str_replace('bandeira_', '', $campo)] = $dif;
                }
            }

            // 3️⃣ Movimentação consolidada de fechamento
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'user_id' => $userId,
                'tipo' => 'fechamento',
                'valor' => array_sum($valoresFisicos) + array_sum($bandeiras),
                'observacao' => !empty($divergencias) ? json_encode($divergencias) : null,
                'data_movimentacao' => now(),
            ]);

            // 4️⃣ Atualiza status do caixa
            $caixa->update([
                'valor_fechamento' => array_sum($valoresFisicos) + array_sum($bandeiras),
                'status' => empty($divergencias) ? 'fechado' : 'inconsistente',
                'data_fechamento' => now(),
                'fechado_por' => $userId,
                'observacao_divergencia' => !empty($divergencias) ? json_encode($divergencias) : null,
            ]);

        });

        return redirect()->route('fechamento.lista')
            ->with('success', 'Caixa fechado com sucesso.');
    }

}