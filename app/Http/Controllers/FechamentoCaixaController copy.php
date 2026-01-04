<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use Illuminate\Support\Facades\DB;

class FechamentoCaixaController extends Controller
{
    // Mostra a view de fechamento/auditoria
    // public function index($caixaId)
    // {
    //     $caixa = Caixa::with(['vendas.pagamentos', 'movimentacoes'])->findOrFail($caixaId);

    //     // Totais de entradas e saídas
    //     $total_entradas = $caixa->movimentacoes
    //         ->whereIn('tipo', ['abertura', 'venda', 'entrada_manual'])
    //         ->sum('valor');

    //     $total_saidas = $caixa->movimentacoes
    //         ->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])
    //         ->sum('valor');

    //     $total_esperado = $caixa->valor_abertura + $total_entradas - $total_saidas;
    //     $divergencia = ($caixa->valor_fechamento ?? $total_esperado) - $total_esperado;

    //     // Totais por forma de pagamento do sistema
    //     $totaisPorForma = [];
    //     foreach ($caixa->vendas as $venda) {
    //         foreach ($venda->pagamentos as $pag) {
    //             if ($pag->status !== 'confirmado') continue;
    //             $forma = $pag->forma_pagamento;
    //             if (!isset($totaisPorForma[$forma])) $totaisPorForma[$forma] = 0;
    //             $totaisPorForma[$forma] += $pag->valor;
    //         }
    //     }

    //     return view('fechamento_caixa.index', compact(
    //         'caixa',
    //         'total_entradas',
    //         'total_saidas',
    //         'total_esperado',
    //         'divergencia',
    //         'totaisPorForma'
    //     ));
    // }

    public function index($caixaId)
    {
        // Carrega o caixa com vendas, pagamentos e movimentações
        $caixa = Caixa::with(['vendas.pagamentos', 'movimentacoes'])->findOrFail($caixaId);

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

        // Passa as movimentações também para a view
        return view('fechamento_caixa.index', compact(
            'caixa',
            'total_entradas',
            'total_saidas',
            'total_esperado',
            'divergencia',
            'totaisPorForma'
        ))->with('movimentacoes', $caixa->movimentacoes);
    }

    // Pega caixas abertos ou inconsistentes
   public function listaCaixas()
    {
        $caixas = Caixa::with(['usuario', 'terminal'])
                        ->whereIn('status', ['aberto', 'inconsistente'])
                        ->orderBy('data_abertura', 'asc')
                        ->get();

        return view('fechamento_caixa.listaCaixas', compact('caixas'));
    }

    // Processa o fechamento do caixa
//    public function fechar(Request $request, $caixaId)
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

    // Processa o fechamento do caixa
    public function fechar(Caixa $caixa)
    {
        if ($caixa->status !== 'aberto') {
            return back()->with('error', 'Caixa não está aberto.');
        }

        DB::transaction(function () use ($caixa) {

            // Entradas e saídas reais
            $totalEntradas = $caixa->movimentacoes()
                ->whereIn('tipo', ['abertura', 'venda', 'entrada_manual'])
                ->sum('valor');

            $totalSaidas = $caixa->movimentacoes()
                ->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])
                ->sum('valor');

            $totalEsperado = $caixa->valor_abertura + $totalEntradas - $totalSaidas;

            // Valor físico NÃO é informado na opção A
            $divergencia = 0;

            // Registro de fechamento (evento)
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'user_id'  => auth()->id(),
                'tipo'     => 'fechamento',
                'valor'    => $totalEsperado,
                'observacao' => 'Fechamento automático do caixa',
            ]);

            // Atualiza caixa
            $caixa->update([
                'valor_fechamento' => $totalEsperado,
                'status'           => 'fechado',
                'data_fechamento'  => now(),
                'fechado_por'      => auth()->id(),
            ]);
        });

        return redirect()
            ->route('fechamento.lista')
            ->with('success', 'Caixa fechado com sucesso.');
    }

        public function lancar_valores(Request $request, $caixaId)
        {
            $caixa = Caixa::findOrFail($caixaId);

            // Verifica se o caixa está bloqueado
            if ($caixa->status === 'bloqueado') {
                // Desbloqueia temporariamente
                $caixa->status = 'aberto';
                $caixa->save();
            }

            // Lista de campos que representam valores
            $valores = [
                'dinheiro'        => 'Dinheiro',
                'pix'             => 'Pix',
                'carteira'        => 'Carteira',
                'cartao_debito'   => 'Cartão Débito',
                'cartao_credito'  => 'Cartão Crédito',
            ];

            // Registrar movimentações manuais
            foreach ($valores as $campo => $descricao) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    $caixa->movimentacoes()->create([
                        'tipo'        => 'entrada_manual',
                        'valor'       => $valor,
                        'origem'      => 'Manual',
                        'observacao'  => "Lançamento manual: {$descricao}",
                        'created_at'  => now(),
                    ]);
                }
            }

            // Bandeiras de cartão
            $bandeiras = [
                'bandeira_visa'       => 'Visa',
                'bandeira_mastercard' => 'Mastercard',
                'bandeira_elo'        => 'Elo',
                'bandeira_amex'       => 'Amex',
            ];

            foreach ($bandeiras as $campo => $nome) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    $caixa->movimentacoes()->create([
                        'tipo'        => 'entrada_manual',
                        'valor'       => $valor,
                        'origem'      => 'Manual',
                        'observacao'  => "Lançamento manual: Bandeira {$nome}",
                        'created_at'  => now(),
                    ]);
                }
            }

            return redirect()->route('fechamento.auditar', $caixa->id)
                            ->with('success', 'Movimentações lançadas com sucesso!');
        }
        
        //mostra a view de lançamento de valores manuais
        public function lancarValores(Caixa $caixa)
        {
            return view('fechamento_caixa.lancar_valores', compact('caixa'));
        }


}
