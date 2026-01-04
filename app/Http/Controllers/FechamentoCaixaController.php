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
    public function fechar(Request $request, Caixa $caixa)
    {
        if ($caixa->status !== 'aberto') {
            return redirect()->back()->with('error', 'Este caixa não está aberto.');
        }

        DB::transaction(function () use ($request, $caixa) {
            $userId = auth()->id();

            // Entradas manuais
            $entradas = [
                'entrada_suprimento' => 'Suprimento',
                'entrada_ajuste'     => 'Ajuste Positivo',
                'entrada_devolucao'  => 'Devolução em Dinheiro',
                'entrada_outros'     => 'Outras Entradas',
            ];

            foreach ($entradas as $campo => $descricao) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id'  => $userId,
                        'tipo'     => 'entrada_manual',
                        'valor'    => $valor,
                        'observacao' => $descricao,
                        'data_movimentacao' => now(),
                    ]);
                }
            }

            // Saídas manuais
            $saidas = [
                'saida_sangria' => 'Sangria',
                'saida_despesa' => 'Despesas',
                'saida_ajuste'  => 'Ajuste Negativo',
                'saida_outros'  => 'Outras Saídas',
            ];

            foreach ($saidas as $campo => $descricao) {
                $valor = (float) $request->input($campo, 0);
                if ($valor > 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixa->id,
                        'user_id'  => $userId,
                        'tipo'     => 'saida_manual',
                        'valor'    => $valor,
                        'observacao' => $descricao,
                        'data_movimentacao' => now(),
                    ]);
                }
            }

            // Registro de fechamento
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'user_id'  => $userId,
                'tipo'     => 'fechamento',
                'valor'    => array_sum($request->only(['dinheiro','pix','carteira','cartao_debito','cartao_credito'])),
                'observacao' => 'Fechamento do caixa',
                'data_movimentacao' => now(),
            ]);

            // Atualiza status do caixa
            $caixa->update([
                'status'          => 'fechado',
                'valor_fechamento'=> array_sum($request->only(['dinheiro','pix','carteira','cartao_debito','cartao_credito'])),
                'data_fechamento' => now(),
                'fechado_por'     => $userId,
            ]);
        });

        return redirect()
            ->route('fechamento.lista')
            ->with('success', 'Caixa fechado com sucesso.');
    }
}