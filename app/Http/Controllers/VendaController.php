<?php

namespace App\Http\Controllers;
use Illuminate\Validation\ValidationException;
use App\Services\CaixaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    /**
     * Store da venda (PDV)
     * Responsabilidade TOTAL do backend
     */
    
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'itens'      => 'required|array|min:1',
            'pagamentos' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {

        // pegar caixa aberto
        $caixa = DB::table('caixas')
            ->where('terminal_id', session('terminal_id'))
            ->whereNull('data_fechamento')
            ->lockForUpdate()
            ->first();

        if (!$caixa) {
            throw ValidationException::withMessages([
                'caixa' => 'Nenhum caixa aberto para este terminal.'
            ]);
        }

        // calcular total da venda
        $totalVenda = 0;
        foreach ($request->itens as $item) {
            $totalVenda += $item['quantidade'] * $item['preco_venda'];
        }

        // criar venda
        $vendaId = DB::table('vendas')->insertGetId([
            'cliente_id'     => $request->cliente_id,
            'caixa_id'       => $caixa->id,
            'terminal_id'    => session('terminal_id'),
            'funcionario_id' => auth()->id(),
            'total'          => $totalVenda,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // registrar movimentação no caixa **após criar a venda**
        foreach ($request->pagamentos as $pagamento) {
            \App\Services\CaixaService::registrarMovimentacaoCaixa([
                'caixa_id'        => $caixa->id,
                'user_id'         => auth()->id(),
                'tipo'            => 'venda',
                'valor'           => $pagamento['valor'],
                'forma_pagamento' => $pagamento['forma_pagamento'] ?? $pagamento['forma'],
                'bandeira'        => $pagamento['bandeira'] ?? null,
                'origem_id'       => $vendaId,
                'observacao'      => 'Venda PDV #' . $vendaId,
            ]);
        }

        // demais passos: inserir itens, atualizar estoque, etc.

        return response()->json([
            'success' => true,
            'venda_id' => $vendaId
        ]);
    });

    }

    public function fecharCaixa(Request $request)
    {
        return DB::transaction(function () use ($request) {

            /** ============================
             * 1. CAIXA ABERTO
             * ============================ */
            $caixa = DB::table('caixas')
                ->where('terminal_id', session('terminal_id'))
                ->whereNull('data_fechamento')
                ->lockForUpdate()
                ->first();

            if (!$caixa) {
                throw ValidationException::withMessages([
                    'caixa' => 'Nenhum caixa aberto para este terminal.'
                ]);
            }

            /** ============================
             * 2. SOMAR MOVIMENTAÇÕES
             * ============================ */
            $entradas = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->whereIn('tipo', ['abertura', 'venda', 'entrada_manual'])
                ->sum('valor');

            $saidas = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])
                ->sum('valor');

            $saldoSistema = $entradas - $saidas;

            /** ============================
             * 3. MOVIMENTAÇÃO DE FECHAMENTO
             * ============================ */
            DB::table('movimentacoes_caixa')->insert([
                'caixa_id'          => $caixa->id,
                'user_id'           => auth()->id(),
                'tipo'              => 'fechamento',
                'valor'             => $saldoSistema,
                'origem_id'         => null,
                'observacao'        => $request->observacao ?? 'Fechamento de caixa',
                'data_movimentacao' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            /** ============================
             * 4. FECHAR CAIXA
             * ============================ */
            DB::table('caixas')
                ->where('id', $caixa->id)
                ->update([
                    'data_fechamento' => now(),
                    'updated_at'      => now(),
                ]);

            /** ============================
             * 5. RETORNO
             * ============================ */
            return response()->json([
                'success' => true,
                'saldo'   => $saldoSistema
            ]);
        });
    }
    // Registrar diferença, se houver (positivo ou negativo)

    public function fecharCaixaComConferencia(Request $request)
    {
        $request->validate([
            'formas'                 => 'required|array|min:1',
            'formas.*.forma'         => 'required|string',
            'formas.*.valor_informado'=> 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {

            /** ============================
             * 1. CAIXA ABERTO
             * ============================ */
            $caixa = DB::table('caixas')
                ->where('terminal_id', session('terminal_id'))
                ->whereNull('data_fechamento')
                ->lockForUpdate()
                ->first();

            if (!$caixa) {
                throw ValidationException::withMessages([
                    'caixa' => 'Nenhum caixa aberto para este terminal.'
                ]);
            }

            /** ============================
             * 2. TOTAL SISTEMA POR FORMA
             * ============================ */
            $pagamentosSistema = DB::table('pagamentos_venda')
                ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
                ->where('caixa_id', $caixa->id)
                ->where('status', 'confirmado')
                ->groupBy('forma_pagamento')
                ->get()
                ->keyBy('forma_pagamento');

            $saldoFinal = 0;

            /** ============================
             * 3. CONFERÊNCIA POR FORMA
             * ============================ */
            foreach ($request->formas as $forma) {

                $formaNome = $forma['forma'];
                $valorInformado = (float) $forma['valor_informado'];
                $valorSistema = (float) ($pagamentosSistema[$formaNome]->total ?? 0);

                $diferenca = $valorInformado - $valorSistema;
                $saldoFinal += $valorInformado;

                DB::table('movimentacoes_caixa')->insert([
                    'caixa_id'          => $caixa->id,
                    'user_id'           => auth()->id(),
                    'tipo'              => 'fechamento',
                    'valor'             => $valorInformado,
                    'origem_id'         => null,
                    'observacao'        => "Fechamento {$formaNome} | Sistema: {$valorSistema} | Diferença: {$diferenca}",
                    'data_movimentacao' => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            /** ============================
             * 4. FECHAR CAIXA
             * ============================ */
            DB::table('caixas')
                ->where('id', $caixa->id)
                ->update([
                    'data_fechamento' => now(),
                    'updated_at'      => now(),
                ]);

            /** ============================
             * 5. RETORNO
             * ============================ */
            return response()->json([
                'success' => true,
                'saldo_final' => $saldoFinal
            ]);
        });
    }

    // Uma movimentação por forma (valor informado)
    // Uma movimentação adicional por divergência, quando existir:
    // tipo = entrada_manual → sobra
    // tipo = saida_manual → falta
    // Nada é recalculado.
    // Auditoria clara e rastreável.

    public function fecharCaixaComDivergencia(Request $request)
    {
        $request->validate([
            'formas'                  => 'required|array|min:1',
            'formas.*.forma'          => 'required|string',
            'formas.*.valor_informado'=> 'required|numeric|min:0',
            'observacao'              => 'nullable|string'
        ]);

        return DB::transaction(function () use ($request) {

            /** ============================
             * 1. CAIXA ABERTO
             * ============================ */
            $caixa = DB::table('caixas')
                ->where('terminal_id', session('terminal_id'))
                ->whereNull('data_fechamento')
                ->lockForUpdate()
                ->first();

            if (!$caixa) {
                throw ValidationException::withMessages([
                    'caixa' => 'Nenhum caixa aberto para este terminal.'
                ]);
            }

            /** ============================
             * 2. TOTAL DO SISTEMA POR FORMA
             * ============================ */
            $sistema = DB::table('pagamentos_venda')
                ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
                ->where('caixa_id', $caixa->id)
                ->where('status', 'confirmado')
                ->groupBy('forma_pagamento')
                ->get()
                ->keyBy('forma_pagamento');

            $saldoFinal = 0;

            /** ============================
             * 3. CONFERÊNCIA + DIVERGÊNCIA
             * ============================ */
            foreach ($request->formas as $forma) {

                $nomeForma = $forma['forma'];
                $valorInformado = (float) $forma['valor_informado'];
                $valorSistema = (float) ($sistema[$nomeForma]->total ?? 0);
                $diferenca = $valorInformado - $valorSistema;

                $saldoFinal += $valorInformado;

                // Registro do valor informado
                DB::table('movimentacoes_caixa')->insert([
                    'caixa_id'          => $caixa->id,
                    'user_id'           => auth()->id(),
                    'tipo'              => 'fechamento',
                    'valor'             => $valorInformado,
                    'observacao'        => "Fechamento {$nomeForma}",
                    'data_movimentacao' => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Registro da divergência (se existir)
                if ($diferenca != 0) {
                    DB::table('movimentacoes_caixa')->insert([
                        'caixa_id'          => $caixa->id,
                        'user_id'           => auth()->id(),
                        'tipo'              => $diferenca > 0 ? 'entrada_manual' : 'saida_manual',
                        'valor'             => abs($diferenca),
                        'observacao'        => "Divergência {$nomeForma} | Sistema: {$valorSistema}",
                        'data_movimentacao' => now(),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }

            /** ============================
             * 4. FECHAR CAIXA
             * ============================ */
            DB::table('caixas')
                ->where('id', $caixa->id)
                ->update([
                    'data_fechamento' => now(),
                    'updated_at'      => now(),
                ]);

            /** ============================
             * 5. RETORNO
             * ============================ */
            return response()->json([
                'success'     => true,
                'saldo_final' => $saldoFinal
            ]);
        });
    }

    public function relatorioCaixa($caixaId)
    {
        /** ============================
         * 1. CAIXA
         * ============================ */
        $caixa = DB::table('caixas')
            ->where('id', $caixaId)
            ->first();

        if (!$caixa) {
            abort(404, 'Caixa não encontrado');
        }

        /** ============================
         * 2. MOVIMENTAÇÕES
         * ============================ */
        $movimentacoes = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->orderBy('data_movimentacao')
            ->get();

        /** ============================
         * 3. TOTAIS POR TIPO
         * ============================ */
        $totaisPorTipo = $movimentacoes->groupBy('tipo')->map(function ($grupo) {
            return $grupo->sum('valor');
        });

        /** ============================
         * 4. PAGAMENTOS POR FORMA
         * ============================ */
        $pagamentosPorForma = DB::table('pagamentos_venda')
            ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
            ->where('caixa_id', $caixaId)
            ->where('status', 'confirmado')
            ->groupBy('forma_pagamento')
            ->get();

        /** ============================
         * 5. SALDO FINAL DO SISTEMA
         * ============================ */
        $entradas = $movimentacoes->whereIn('tipo', [
            'abertura',
            'venda',
            'entrada_manual',
            'fechamento'
        ])->sum('valor');

        $saidas = $movimentacoes->whereIn('tipo', [
            'saida_manual',
            'cancelamento_venda'
        ])->sum('valor');

        $saldoSistema = $entradas - $saidas;

        /** ============================
         * 6. RETORNO
         * ============================ */
        return response()->json([
            'caixa'               => $caixa,
            'totais_por_tipo'     => $totaisPorTipo,
            'pagamentos_por_forma'=> $pagamentosPorForma,
            'saldo_sistema'       => $saldoSistema,
            'movimentacoes'       => $movimentacoes,
        ]);
    }

    public function finalizar(Request $request)
{
    // validação, persistência, etc.

    return response()->json([
        'success' => true,
        'mensagem' => 'Venda finalizada com sucesso!'
    ]);
}


}
