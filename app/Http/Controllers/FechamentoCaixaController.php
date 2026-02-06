<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Services\CaixaService;
use App\Models\MovimentacaoCaixa;
use App\Models\PagamentoVenda;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FechamentoCaixaController extends Controller
{
    /**
     * Mostra a view de fechamento/auditoria
     */

    public function index($caixaId)
    {
        $caixa = Caixa::with('movimentacoes')->findOrFail($caixaId);

        $total_esperado = CaixaService::total_esperado($caixaId);

        $total_entradas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['abertura', 'entrada_manual'])
            ->sum('valor');

        $total_saidas = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])
        ->sum('valor');

        /** ============================
         * INICIALIZAÇÃO SEGURA
         * ============================ */
        $divergencia = 0;
        $totaisPorForma = [];
        $totalGeralSistema = 0;

        /** ============================
         * TOTAIS DO SISTEMA POR FORMA
         * ============================ */
        if ($caixa->vendas->count()) {
            foreach ($caixa->vendas as $venda) {
                foreach ($venda->pagamentos as $pag) {
                    if ($pag->status !== 'confirmado') {
                        continue;
                    }

                    $forma = $pag->forma_pagamento;
                    $totaisPorForma[$forma] = ($totaisPorForma[$forma] ?? 0) + $pag->valor;
                }
            }

            $totalGeralSistema = array_sum($totaisPorForma);
        }

            return view('fechamento_caixa.index', compact(
                'caixa',
                'total_entradas',
                'total_saidas',
                'total_esperado',
                'divergencia',
                'totaisPorForma',
                'totalGeralSistema'
            ));
    }
   
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

    
    //  public function fechar(Request $request, $caixaId)
    // {
    //     $request->validate([
    //         'dinheiro' => 'required|numeric|min:0',
    //         'pix' => 'required|numeric|min:0',
    //         'carteira' => 'required|numeric|min:0',
    //         'cartao_debito' => 'required|numeric|min:0',
    //         'cartao_credito' => 'required|numeric|min:0',
    //     ]);

    //     $valoresFisicos = $request->only(['dinheiro','pix','carteira','cartao_debito','cartao_credito']);

    //     DB::transaction(function () use ($caixaId, $valoresFisicos, $request) {

    //         // 1️⃣ Bloqueio pessimista para concorrência
    //         $caixa = Caixa::with(['vendas.pagamentos'])->lockForUpdate()->findOrFail($caixaId);

    //         if (!$caixa->estaAberto()) {
    //             throw new \Exception("Caixa já foi fechado por outro operador.");
    //         }

    //         // 2️⃣ Totais por forma de pagamento do sistema
    //         $formas = ['dinheiro','pix','carteira','cartao_debito','cartao_credito'];
    //         $totaisSistema = [];
    //         foreach ($formas as $f) {
    //             $totaisSistema[$f] = $caixa->vendas->flatMap->pagamentos
    //                 ->where('forma_pagamento',$f)
    //                 ->where('status','confirmado')
    //                 ->sum('valor');
    //         }

    //         // 3️⃣ Calcula divergências
    //         $divergencias = [];
    //         foreach ($formas as $f) {
    //             $dif = $valoresFisicos[$f] - ($totaisSistema[$f] ?? 0);
    //             if ($dif != 0) {
    //                 MovimentacaoCaixa::create([
    //                     'caixa_id' => $caixa->id,
    //                     'user_id' => auth()->id(),
    //                     'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
    //                     'valor' => abs($dif),
    //                     'origem_id' => null,
    //                     'observacao' => "Ajuste manual no fechamento: $f",
    //                 ]);
    //                 $divergencias[$f] = $dif;
    //             }
    //         }

    //         // 4️⃣ Movimentação de fechamento
    //         MovimentacaoCaixa::create([
    //             'caixa_id' => $caixa->id,
    //             'user_id' => auth()->id(),
    //             'tipo' => 'fechamento',
    //             'valor' => array_sum($valoresFisicos),
    //             'valor_auditado' => 0,
    //             'origem_id' => null,
    //             'observacao' => !empty($divergencias) ? json_encode($divergencias) : null,
    //         ]);

    //         // 5️⃣ Atualiza caixa
    //         $caixa->update([
    //             'valor_fechamento' => array_sum($valoresFisicos),
    //             'status' => empty($divergencias) ? 'fechado' : 'inconsistente',
    //             'data_fechamento' => now(),
    //             'fechado_por' => auth()->id(),
    //             'observacao_divergencia' => !empty($divergencias) ? json_encode($divergencias) : null,
    //         ]);

    //     });

    //     return redirect()->route('fechamento.lista')
    //         ->with('success', 'Caixa fechado com sucesso.');
    // }

    // public function fechar(Request $request, $caixaId)
    // {
    //     $request->validate([
    //         'dinheiro'        => 'required|numeric|min:0',
    //         'pix'             => 'required|numeric|min:0',
    //         'carteira'        => 'required|numeric|min:0',
    //         'cartao_debito'   => 'required|numeric|min:0',
    //         'cartao_credito'  => 'required|numeric|min:0',
    //     ]);

    //     $valoresFisicos = $request->only([
    //         'dinheiro',
    //         'pix',
    //         'carteira',
    //         'cartao_debito',
    //         'cartao_credito'
    //     ]);

    //     DB::transaction(function () use ($caixaId, $valoresFisicos) {

    //         $caixa = Caixa::with(['vendas.pagamentos'])
    //             ->lockForUpdate()
    //             ->findOrFail($caixaId);

    //         if (!$caixa->estaAberto()) {
    //             throw new \Exception('Caixa já fechado.');
    //         }

    //         $userId = auth()->id();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 1️⃣ Totais do sistema (somente vendas)
    //         |--------------------------------------------------------------------------
    //         */
    //         $totaisSistema = [];
    //         foreach ($valoresFisicos as $forma => $_) {
    //             $totaisSistema[$forma] = $caixa->vendas
    //                 ->flatMap->pagamentos
    //                 ->where('forma_pagamento', $forma)
    //                 ->where('status', 'confirmado')
    //                 ->sum('valor');
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 2️⃣ Lançamento dos valores informados pelo usuário
    //         |--------------------------------------------------------------------------
    //         */
    //         foreach ($valoresFisicos as $forma => $valor) {
    //             if ($valor <= 0) {
    //                 continue;
    //             }

    //             MovimentacaoCaixa::create([
    //                 'caixa_id'        => $caixa->id,
    //                 'user_id'         => $userId,
    //                 'tipo'            => 'entrada_manual',
    //                 'forma_pagamento' => $forma,
    //                 'valor'           => $valor,
    //                 'origem_id'       => null,
    //                 'observacao'      => 'Valor informado no fechamento',
    //             ]);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 3️⃣ Ajustes de divergência (somente diferença)
    //         |--------------------------------------------------------------------------
    //         */
    //         $divergencias = [];

    //         // foreach ($valoresFisicos as $forma => $valorInformado) {
    //         //     $valorSistema = $totaisSistema[$forma] ?? 0;
    //         //     $dif = $valorInformado - $valorSistema;

    //         //     if ($dif == 0) {
    //         //         continue;
    //         //     }

    //         //     MovimentacaoCaixa::create([
    //         //         'caixa_id'        => $caixa->id,
    //         //         'user_id'         => $userId,
    //         //         'tipo'            => $dif > 0 ? 'entrada_manual' : 'saida_manual',
    //         //         'forma_pagamento' => $forma,
    //         //         'valor'           => abs($dif),
    //         //         'origem_id'       => null,
    //         //         'observacao'      => "Ajuste manual no fechamento: {$forma}",
    //         //     ]);

    //         //     $divergencias[$forma] = $dif;
    //         // }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 4️⃣ Fechamento (SEM fundo de caixa)
    //         |--------------------------------------------------------------------------
    //         */
    //         // $totalSistema   = array_sum($totaisSistema);
    //         // $totalInformado = array_sum($valoresFisicos);

    //         // MovimentacaoCaixa::create([
    //         //     'caixa_id'       => $caixa->id,
    //         //     'user_id'        => $userId,
    //         //     'tipo'           => 'fechamento',
    //         //     'valor'          => $totalSistema,
    //         //     'valor_auditado' => $totalSistema,
    //         //     'origem_id'      => null,
    //         //     'observacao'     => !empty($divergencias)
    //         //         ? json_encode($divergencias)
    //         //         : 'Fechamento sem divergências',
    //         // ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 5️⃣ Atualiza status do caixa
    //         |--------------------------------------------------------------------------
    //         */
    //         $totalSistema   = array_sum($totaisSistema);
    //         $caixa->update([
    //             'valor_fechamento'       => $totalSistema,
    //             'status'                 => empty($divergencias) ? 'fechado' : 'inconsistente',
    //             'data_fechamento'        => now(),
    //             'fechado_por'            => $userId,
    //             // 'observacao_divergencia' => !empty($divergencias)
    //             //     ? json_encode($divergencias)
    //             //     : null,
    //             'observacao' => !empty($divergencias) ? json_encode($divergencias) : null,
    //         ]);
    //     });

    //     return redirect()
    //         ->route('fechamento.lista')
    //         ->with('success', 'Caixa fechado com sucesso.');
    // }

        public function fechar(Request $request, $caixaId)
{
    $request->validate([
        'dinheiro'        => 'required|numeric|min:0',
        'pix'             => 'required|numeric|min:0',
        'carteira'        => 'required|numeric|min:0',
        'cartao_debito'   => 'required|numeric|min:0',
        'cartao_credito'  => 'required|numeric|min:0',
    ]);

    $valoresFisicos = $request->only([
        'dinheiro',
        'pix',
        'carteira',
        'cartao_debito',
        'cartao_credito'
    ]);

    DB::transaction(function () use ($caixaId, $valoresFisicos) {

        $caixa = Caixa::with(['vendas.pagamentos'])
            ->lockForUpdate()
            ->findOrFail($caixaId);

        if (!$caixa->estaAberto()) {
            throw new \Exception('Caixa já fechado.');
        }

        $userId = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Totais do sistema (somente vendas confirmadas)
        |--------------------------------------------------------------------------
        */
        $totaisSistema = [];

        foreach ($valoresFisicos as $forma => $_) {
            $totaisSistema[$forma] = $caixa->vendas
                ->flatMap->pagamentos
                ->where('forma_pagamento', $forma)
                ->where('status', 'confirmado')
                ->sum('valor');
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Lançamento dos valores informados (SEM DUPLICAR)
        |--------------------------------------------------------------------------
        */
        foreach ($valoresFisicos as $forma => $valor) {

            if ($valor <= 0) {
                continue;
            }

            // 🔒 impede relançamento da mesma forma no fechamento
            $jaLancado = MovimentacaoCaixa::where('caixa_id', $caixa->id)
                ->where('tipo', 'entrada_manual')
                ->where('forma_pagamento', $forma)
                ->where('observacao', 'Valor informado no fechamento')
                ->exists();

            if ($jaLancado) {
                continue;
            }

            MovimentacaoCaixa::create([
                'caixa_id'        => $caixa->id,
                'user_id'         => $userId,
                'tipo'            => 'entrada_manual',
                'forma_pagamento' => $forma,
                'valor'           => $valor, // 🔴 exatamente o valor digitado
                'origem_id'       => null,
                'observacao'      => 'Valor informado no fechamento',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Divergências (APENAS CÁLCULO – SEM MOVIMENTAÇÃO)
        |--------------------------------------------------------------------------
        */
        $divergencias = [];

        foreach ($valoresFisicos as $forma => $valorInformado) {
            $valorSistema = $totaisSistema[$forma] ?? 0;
            $dif = $valorInformado - $valorSistema;

            if ($dif != 0) {
                $divergencias[$forma] = $dif;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Atualiza status do caixa
        |--------------------------------------------------------------------------
        */
        $totalSistema = array_sum($totaisSistema);

        $caixa->update([
            'valor_fechamento' => $totalSistema,
            'status'           => empty($divergencias) ? 'fechado' : 'inconsistente',
            'data_fechamento'  => now(),
            'fechado_por'      => $userId,
            'observacao'       => !empty($divergencias)
                ? json_encode($divergencias)
                : null,
        ]);
    });

    return redirect()
        ->route('fechamento.lista')
        ->with('success', 'Caixa fechado com sucesso.');
}

       
    private function fecharSemMovimentoAuditoria(Request $request, Caixa $caixa)
    {
        $request->validate([
            'motivo_fechamento' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $caixa) {

            if (!$caixa->estaAberto()) {
                throw new \Exception('Caixa já foi fechado.');
            }

            $userId = Auth::id();

            // Auditoria mínima do fechamento
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'user_id' => $userId,
                'tipo' => 'fechamento_sem_movimento',
                'valor' => $caixa->fundo_troco,
                'observacao' => $request->motivo_fechamento,
                'data_movimentacao' => now(),
            ]);

            $caixa->update([
                'valor_fechamento' => $caixa->fundo_troco,
                'status' => 'fechado_sem_movimento',
                'data_fechamento' => now(),
                'fechado_por' => $userId,
                'observacao_divergencia' => $request->motivo_fechamento,
            ]);
        });

        return redirect()
            ->route('fechamento.lista')
            ->with('success', 'Caixa fechado sem movimentação.');
    }

    //VALIDAÇÕES
    private function validarRequestFechamento(Request $request): void
    {
        $request->validate([
            'dinheiro'       => 'required|numeric|min:0',
            'pix'            => 'required|numeric|min:0',
            'carteira'       => 'required|numeric|min:0',
            'cartao_debito'  => 'required|numeric|min:0',
            'cartao_credito' => 'required|numeric|min:0',
            'bandeira_visa'       => 'nullable|numeric|min:0',
            'bandeira_mastercard' => 'nullable|numeric|min:0',
            'bandeira_elo'        => 'nullable|numeric|min:0',
            'bandeira_amex'       => 'nullable|numeric|min:0',
            'bandeira_hipercard'  => 'nullable|numeric|min:0',
        ]);
    }

    private function validarCaixaAberto(Caixa $caixa): void
    {
        if (!$caixa->estaAberto()) {
            throw new \Exception('Caixa já foi fechado por outro operador.');
        }
    }
    //extração de dados
    private function extrairValoresFisicos(Request $request): array
    {
        return $request->only([
            'dinheiro','pix','carteira','cartao_debito','cartao_credito'
        ]);
    }

    private function extrairBandeiras(Request $request): array
    {
        return $request->only([
            'bandeira_visa','bandeira_mastercard','bandeira_elo',
            'bandeira_amex','bandeira_hipercard'
        ]);
    }
    //4️⃣ Auditorias e cálculos
    private function calcularTotaisSistema(Caixa $caixa): array
    {
        $formas = ['dinheiro','pix','carteira','cartao_debito','cartao_credito'];
        $totais = [];

        foreach ($formas as $forma) {
            $totais[$forma] = $caixa->vendas->flatMap->pagamentos
                ->where('forma_pagamento', $forma)
                ->where('status', 'confirmado')
                ->sum('valor');
        }

        return $totais;
    }

    private function registrarDivergenciasPorForma(
        Caixa $caixa,
        int $userId,
        array $valoresFisicos,
        array $totaisSistema,
        array &$divergencias
        ): void {
            foreach ($totaisSistema as $forma => $valorSistema) {

                $valorFisico = (float) ($valoresFisicos[$forma] ?? 0);
                $dif = $valorFisico - $valorSistema;

                if ($dif == 0) {
                    continue;
                }

                MovimentacaoCaixa::create([
                    'caixa_id' => $caixa->id,
                    'user_id' => $userId,
                    'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
                    'valor' => abs($dif),
                    'forma_pagamento' => $forma,
                    'observacao' => "[AJUSTE MANUAL] Forma: $forma",
                    'data_movimentacao' => now(),
                ]);

                $divergencias[$forma] = $dif;
            }
    }

    private function auditarBandeiras(
        Caixa $caixa,
        int $userId,
        array $bandeiras,
        array &$divergencias
        ): void {
        foreach ($bandeiras as $campo => $valorFisico) {

            $valorFisico = (float) $valorFisico;
            if ($valorFisico <= 0) {
                continue;
            }

            $bandeira = str_replace('bandeira_', '', $campo);

            $valorSistema = $caixa->vendas->flatMap->pagamentos
                ->where('forma_pagamento', 'cartao')
                ->where('bandeira', ucfirst($bandeira))
                ->where('status', 'confirmado')
                ->sum('valor');

            $dif = $valorFisico - $valorSistema;

            if ($dif == 0) {
                continue;
            }

            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'user_id' => $userId,
                'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
                'valor' => abs($dif),
                'forma_pagamento' => 'cartao',
                'bandeira' => $bandeira,
                'observacao' => "[AJUSTE MANUAL] Bandeira $bandeira",
                'data_movimentacao' => now(),
            ]);

            $divergencias['bandeiras'][$bandeira] = $dif;
        }
    }

    //5️⃣ Fechamento e persistência

    private function calcularTotalFechamento(
        array $valoresFisicos,
        array $bandeiras,
        Caixa $caixa
        ): float {
            return array_sum($valoresFisicos)
                + array_sum($bandeiras)
                + $caixa->fundo_troco;
    }

    private function registrarFechamento(
        Caixa $caixa,
        int $userId,
        float $totalFechamento,
        array $divergencias
        ): void {
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'user_id' => $userId,
                'tipo' => 'fechamento',
                'valor' => $totalFechamento,
                'valor_auditado' => 0,
                'observacao' => !empty($divergencias)
                    ? json_encode($divergencias)
                    : null,
                'data_movimentacao' => now(),
            ]);
    }

    private function atualizarStatusCaixa(
        Caixa $caixa,
        int $userId,
        float $totalFechamento,
        array $divergencias
        ): void {
        $caixa->update([
            'valor_fechamento' => $totalFechamento,
            'status' => empty($divergencias) ? 'fechado' : 'inconsistente',
            'data_fechamento' => now(),
            'fechado_por' => $userId,
            'observacao_divergencia' => !empty($divergencias)
                ? json_encode($divergencias)
                : null,
        ]);
    }

    
//    public function ajustarDivergencias(Request $request, int $caixaId)
//     {
//         $request->validate([
//             'formas'   => 'required|array|min:1',
//             'formas.*' => 'required',
//         ]);

//         DB::transaction(function () use ($request, $caixaId) {

//             $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

//             if ($caixa->status !== 'inconsistente') {
//                 throw new \Exception('Caixa não está inconsistente para auditoria.');
//             }

//             $userId = auth()->id();

//             // Soma FINAL auditada vinda da view
//             $totalAuditado = 0;

//             foreach ($request->formas as $forma => $valorStr) {
//                 $totalAuditado += $this->parseValorBR($valorStr);
//             }

//             // 🔒 ÚNICO LUGAR ONDE valor_auditado É PERMITIDO
//             MovimentacaoCaixa::where('caixa_id', $caixa->id)
//                 ->where('tipo', 'fechamento')
//                 ->latest('id')
//                 ->update([
//                     'valor_auditado' => $totalAuditado,
//                     'observacao'     => 'Auditoria concluída por forma de pagamento',
//                     'user_id'        => $userId,
//                     'updated_at'     => now(),
//                 ]);

//             // Fecha o caixa
//             $caixa->update([
//                 'status'          => 'fechado',
//                 'data_fechamento' => now(),
//                 'fechado_por'     => $userId,
//             ]);
//         });

//         return redirect()
//             ->route('fechamento.lista')
//             ->with('success', 'Auditoria concluída e caixa fechado com sucesso.');
//     }
    public function ajustarDivergencias(Request $request, int $caixaId)
    {
        $request->validate([
            'formas'   => 'required|array|min:1',
            'formas.*' => 'required',
        ]);
        
        DB::transaction(function () use ($request, $caixaId) {

            // Antes de criar o fechamento, calcule o total das vendas do sistema:
            $totalVendas = PagamentoVenda::where('caixa_id', $caixaId)
            ->where('status', 'confirmado')
            ->sum('valor'); 

            $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

            if ($caixa->status !== 'inconsistente') {
                throw new \Exception('Caixa não está inconsistente para auditoria.');
            }

            $userId = auth()->id();
            $totalAuditado = 0;

            // foreach ($request->formas as $forma => $valorStr) {

            //     $valorInformado = $this->parseValorBR($valorStr);

            //     // Total do sistema por forma
            //     $valorSistema = PagamentoVenda::where('caixa_id', $caixa->id)
            //         ->where('forma_pagamento', $forma)
            //         ->where('status', 'confirmado')
            //         ->sum('valor');

            //     $diferenca = $valorInformado - $valorSistema;

            //     // Se houver divergência, gera movimentação
            //     if ($diferenca != 0) {
            //         MovimentacaoCaixa::create([
            //             'caixa_id'        => $caixa->id,
            //             'user_id'         => $userId,
            //             'tipo'            => $diferenca > 0 ? 'entrada_manual' : 'saida_manual',
            //             'forma_pagamento' => $forma,
            //             'valor'           => abs($diferenca),
            //             'observacao'      => "[AJUSTE MANUAL] Forma: {$forma}",
            //         ]);
            //     }

            //     $totalAuditado += $valorInformado;
            //     $valorAuditadoOriginal = $valorInformado;
            // }

            foreach ($request->formas as $forma => $valorStr) {

                $valorAuditado = $this->parseValorBR($valorStr);

                // valor real do sistema (vendas)
                $valorSistema = PagamentoVenda::where('caixa_id', $caixa->id)
                    ->where('forma_pagamento', $forma)
                    ->where('status', 'confirmado')
                    ->sum('valor');

                // 🔹 grava auditoria POR FORMA
                MovimentacaoCaixa::updateOrCreate(
                    [
                        'caixa_id'        => $caixa->id,
                        'tipo'            => 'auditoria',
                        'forma_pagamento' => $forma,
                    ],
                    [
                        'user_id'         => $userId,
                        'valor'           => $valorSistema,     // valor do sistema
                        'valor_auditado'  => $valorAuditado,    // valor corrigido
                        'observacao'      => 'Auditoria por forma de pagamento',
                        'data_movimentacao'=> now(),
                    ]
                );

                // 🔹 se houver diferença, gera ajuste contábil
                $diferenca = $valorAuditado - $valorSistema;

                if ($diferenca != 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id'        => $caixa->id,
                        'user_id'         => $userId,
                        'tipo'            => $diferenca > 0 ? 'entrada_manual' : 'saida_manual',
                        'forma_pagamento' => $forma,
                        'valor'           => abs($diferenca),
                        'observacao'      => "[AJUSTE AUDITORIA] Forma: {$forma}",
                        'data_movimentacao'=> now(),
                    ]);
                }
            }

            // 🔒 SOMENTE O FECHAMENTO RECEBE valor_auditado
            MovimentacaoCaixa::where('caixa_id', $caixa->id)
                ->where('tipo', 'fechamento')
                ->latest('id')
                ->update([
                    // 'valor_auditado' => $totalAuditado,
                    'valor_auditado' => $totalVendas,               // ✅ OBRIGATÓRIO
                    'observacao'     => 'Auditoria concluída por forma de pagamento',
                    'user_id'        => $userId,
                    'updated_at'     => now(),
                ]);

            $caixa->update([
                'status'          => 'fechado',
                'data_fechamento' => now(),
                'fechado_por'     => $userId,
            ]);
        });

        return redirect()
            ->route('fechamento.lista')
            ->with('success', 'Auditoria concluída e caixa fechado com sucesso.');
    }



    public function divergencias($caixaId)
    {
        $caixa = Caixa::with(['movimentacoes','vendas.pagamentos'])->findOrFail($caixaId);

        $totaisPorForma = CaixaService::totaisPorForma($caixaId);
        $divergencias = CaixaService::calcularDivergencias($caixaId);

        // Total entradas e saídas do caixa
        $total_entradas = $caixa->movimentacoes
            ->whereIn('tipo',['abertura','entrada_manual'])
            ->sum('valor');

        $total_saidas = $caixa->movimentacoes
            ->whereIn('tipo',['saida_manual','cancelamento_venda'])
            ->sum('valor');

        $totalGeralSistema = array_sum($totaisPorForma);

        // Divergência total absoluta
        $divergencia = array_sum(array_map('abs', $divergencias));

        return view('fechamento_caixa.corrigir_divergencias', compact(
            'caixa',
            'totaisPorForma',
            'divergencias',
            'total_entradas',
            'total_saidas',
            'totalGeralSistema',
            'divergencia'
        ));
    }

    private function parseValorBR($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.00;
        }

        // Remove tudo que não seja número, ponto, vírgula ou sinal
        $valor = trim($valor);
        $valor = preg_replace('/[^\d.,-]/', '', $valor);

        // Trata negativo apenas no início
        $negativo = false;
        if (str_starts_with($valor, '-')) {
            $negativo = true;
            $valor = substr($valor, 1);
        }

        // Se houver vírgula, assume padrão BR
        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        $numero = round((float) $valor, 2);

        return $negativo ? -$numero : $numero;
    }

   // chama o modal Fechamento pelo caixa F10
   public function fechamento($caixaId)
    {
        $caixa = Caixa::with('vendas.pagamentos')
            ->where('id', $caixaId)
            ->where('status', 'aberto')
            ->firstOrFail();

        return view('fechamento_caixa.fechamento', compact('caixa'));
    }

    //FAZ A AUDITORIA DO FECHAMENTO
    public function auditar(Request $request, $id)
    {
        $movimentacao = MovimentacaoCaixa::findOrFail($id);

        if ($movimentacao->tipo !== 'fechamento') {
            return redirect()->back()
                ->withErrors('Somente o fechamento de caixa pode ser auditado.');
        }

        $request->validate([
            'valor_auditado' => ['required', 'numeric', 'min:0'],
        ]);

        $movimentacao->update([
            'valor_auditado' => $request->valor_auditado,
            'user_id' => auth()->id(),
            'observacao' => 'Correção de divergência',
        ]);

        return back()->with('success', 'Auditoria registrada com sucesso.');
    }

    
}