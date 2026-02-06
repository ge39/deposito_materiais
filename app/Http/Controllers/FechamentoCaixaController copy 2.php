<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caixa;
use App\Services\CaixaService;
use App\Models\MovimentacaoCaixa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
 use App\ViewModels\FechamentoCaixaViewModel;

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
    // public function index($caixaId)
    // {
    //     $caixa = Caixa::with(['movimentacoes','vendas.pagamentos'])->findOrFail($caixaId);

    //     $totalEntradas = CaixaService::totalEntradas($caixaId);
    //     $totalSaidas   = CaixaService::totalSaidas($caixaId);

    //     $teveMovimento =
    //         bccomp($totalEntradas, 0, 2) !== 0 ||
    //         bccomp($totalSaidas, 0, 2) !== 0;

    //     $totaisPorForma = CaixaService::totaisPorForma($caixaId);
    //     $totalGeralSistema = array_sum($totaisPorForma);

    //     return view('fechamento_caixa.index', compact(
    //         'caixa',
    //         'totalEntradas',
    //         'totalSaidas',
    //         'teveMovimento',
    //         'totaisPorForma',
    //         'totalGeralSistema'
    //     ));
    // }

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

    // public function fechar(Request $request, $caixaId)
    //     {
    //         $caixa = Caixa::with(['vendas.pagamentos'])->findOrFail($caixaId);

    //     $semMovimento = !$caixa->possuiVendas();

    //     if ($semMovimento) {
    //         return $this->fecharSemMovimentoAuditoria($request, $caixa);
    //     }
        
    //     $request->validate([
    //         'dinheiro'       => 'required|numeric|min:0',
    //         'pix'            => 'required|numeric|min:0',
    //         'carteira'       => 'required|numeric|min:0',
    //         'cartao_debito'  => 'required|numeric|min:0',
    //         'cartao_credito' => 'required|numeric|min:0',
    //         // Bandeiras opcionais
    //         'bandeira_visa'       => 'nullable|numeric|min:0',
    //         'bandeira_mastercard' => 'nullable|numeric|min:0',
    //         'bandeira_elo'        => 'nullable|numeric|min:0',
    //         'bandeira_amex'       => 'nullable|numeric|min:0',
    //         'bandeira_hipercard'  => 'nullable|numeric|min:0',
    //     ]);

    //     $valoresFisicos = $request->only([
    //         'dinheiro','pix','carteira','cartao_debito','cartao_credito'
    //     ]);

    //     $bandeiras = $request->only([
    //         'bandeira_visa','bandeira_mastercard','bandeira_elo','bandeira_amex','bandeira_hipercard'
    //     ]);

    //     DB::transaction(function () use ($caixaId, $valoresFisicos, $bandeiras) {

    //         $caixa = Caixa::with(['vendas.pagamentos'])->lockForUpdate()->findOrFail($caixaId);

    //         if (!$caixa->estaAberto()) {
    //             throw new \Exception("Caixa já foi fechado por outro operador.");
    //         }

    //         $userId = Auth::id();
    //         $divergencias = [];

    //         /** ============================
    //          * 1️⃣ Calcular total do sistema por forma
    //          * ============================ */
    //         $formas = ['dinheiro','pix','carteira','cartao_debito','cartao_credito'];

    //         $totaisSistema = [];
    //         foreach ($formas as $f) {
    //             $totaisSistema[$f] = $caixa->vendas->flatMap->pagamentos
    //                 ->where('forma_pagamento', $f)
    //                 ->where('status','confirmado')
    //                 ->sum('valor');
    //         }

    //         /** ============================
    //          * 2️⃣ Registrar divergências (sem incluir fundo de troco)
    //          * ============================ */
    //         foreach ($formas as $f) {
    //             $valorFisico = (float) ($valoresFisicos[$f] ?? 0);

    //             // Para divergência, subtrai apenas vendas contadas, não o fundo de troco
    //             $dif = $valorFisico - $totaisSistema[$f];

    //             if ($dif != 0) {
    //                 MovimentacaoCaixa::create([
    //                     'caixa_id' => $caixa->id,
    //                     'user_id' => $userId,
    //                     'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
    //                     'valor' => abs($dif),
    //                     'forma_pagamento' => $f,
    //                     'observacao' => "[AJUSTE MANUAL] Forma: $f",
    //                     'data_movimentacao' => now(),
    //                 ]);

    //                 $divergencias[$f] = $dif;
    //             }
    //         }

    //         /** ============================
    //          * 3️⃣ Registrar bandeiras (somente se informado)
    //          * ============================ */
    //         foreach ($bandeiras as $campo => $valorFisico) {
    //             $valorFisico = (float) $valorFisico;
    //             if ($valorFisico <= 0) continue;

    //             $bandeira = str_replace('bandeira_', '', $campo);

    //             // MovimentacaoCaixa::create([
    //             //     'caixa_id' => $caixa->id,
    //             //     'user_id' => $userId,
    //             //     'tipo' => 'entrada_manual',
    //             //     'valor' => $valorFisico,
    //             //     'forma_pagamento' => 'cartao',
    //             //     'bandeira' => $bandeira,
    //             //     'observacao' => "[MANUAL] Bandeira $bandeira",
    //             //     'data_movimentacao' => now(),
    //             // ]);

    //             // Divergência por bandeira
    //             $valorSistema = $caixa->vendas->flatMap->pagamentos
    //                 ->where('forma_pagamento', 'cartao')
    //                 ->where('bandeira', ucfirst($bandeira))
    //                 ->where('status','confirmado')
    //                 ->sum('valor');

    //             $dif = $valorFisico - $valorSistema;
    //             if ($dif != 0) {
    //                 MovimentacaoCaixa::create([
    //                     'caixa_id' => $caixa->id,
    //                     'user_id' => $userId,
    //                     'tipo' => $dif > 0 ? 'entrada_manual' : 'saida_manual',
    //                     'valor' => abs($dif),
    //                     'forma_pagamento' => 'cartao',
    //                     'bandeira' => $bandeira,
    //                     'observacao' => "[AJUSTE MANUAL] Bandeira $bandeira",
    //                     'data_movimentacao' => now(),
    //                 ]);

    //                 $divergencias['bandeiras'][$bandeira] = $dif;
    //             }
    //         }

    //         /** ============================
    //          * 4️⃣ Movimentação consolidada de fechamento
    //          * ============================ */
    //         $totalFechamento = array_sum($valoresFisicos) + array_sum($bandeiras) + $caixa->fundo_troco;

    //         MovimentacaoCaixa::create([
    //             'caixa_id' => $caixa->id,
    //             'user_id' => $userId,
    //             'tipo' => 'fechamento',
    //             'valor' => $totalFechamento,
    //             'observacao' => !empty($divergencias) ? json_encode($divergencias) : null,
    //             'data_movimentacao' => now(),
    //         ]);

    //         /** ============================
    //          * 5️⃣ Atualiza status do caixa
    //          * ============================ */
    //         $caixa->update([
    //             'valor_fechamento' => $totalFechamento,
    //             'status' => empty($divergencias) ? 'fechado' : 'inconsistente',
    //             'data_fechamento' => now(),
    //             'fechado_por' => $userId,
    //             'observacao_divergencia' => !empty($divergencias) ? json_encode($divergencias) : null,
    //         ]);

    //     });

    //     return redirect()->route('fechamento.lista')
    //         ->with('success', 'Caixa fechado com sucesso.');
    // }
    public function fechar(Request $request, $caixaId)
    {
        $caixa = Caixa::with(['vendas.pagamentos'])->findOrFail($caixaId);

        if (!$caixa->possuiVendas()) {
            return $this->fecharSemMovimentoAuditoria($request, $caixa);
        }

        $this->validarRequestFechamento($request);

        $valoresFisicos = $this->extrairValoresFisicos($request);
        $bandeiras      = $this->extrairBandeiras($request);

        DB::transaction(function () use ($caixaId, $valoresFisicos, $bandeiras) {

            $caixa = Caixa::with(['vendas.pagamentos'])
                ->lockForUpdate()
                ->findOrFail($caixaId);

            $this->validarCaixaAberto($caixa);

            $userId = Auth::id();

            $totaisSistema = $this->calcularTotaisSistema($caixa);
            $divergencias  = [];

            $this->registrarDivergenciasPorForma(
                $caixa,
                $userId,
                $valoresFisicos,
                $totaisSistema,
                $divergencias
            );

            $this->auditarBandeiras(
                $caixa,
                $userId,
                $bandeiras,
                $divergencias
            );

            $totalFechamento = $this->calcularTotalFechamento(
                $valoresFisicos,
                $bandeiras,
                $caixa
            );

            $this->registrarFechamento(
                $caixa,
                $userId,
                $totalFechamento,
                $divergencias
            );

            $this->atualizarStatusCaixa(
                $caixa,
                $userId,
                $totalFechamento,
                $divergencias
            );
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

 //********************************************************* */
    // public function corrigirDivergencias($caixaId)
    // {
    //     $caixa = Caixa::with(['movimentacoes', 'vendas.pagamentos'])->findOrFail($caixaId);
    //     $totaisPorForma = CaixaService::totaisPorForma($caixaId); // retorna valores sistema
    //     $divergencias = CaixaService::calcularDivergencias($caixaId); // retorna array forma => diferença
    //     $total_entradas = $caixa->movimentacoes->whereIn('tipo',['abertura','entrada_manual'])->sum('valor');
    //     $total_saidas = $caixa->movimentacoes->whereIn('tipo',['saida_manual','cancelamento_venda'])->sum('valor');
    //     $totalGeralSistema = array_sum($totaisPorForma);
    //     $divergencia = array_sum(array_map('abs', $divergencias));

    //     return view('fechamento_caixa.corrigir_divergencias', compact(
    //         'caixa','totaisPorForma','divergencias','total_entradas','total_saidas','totalGeralSistema','divergencia'
    //     ));
    // }
    public function corrigirDivergencias(Request $request, $caixaId)
    {
        $request->validate([
            'ajustes' => 'required|array',
            'ajustes.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $caixaId) {

            foreach ($request->ajustes as $forma => $valorAjustado) {

                MovimentacaoCaixa::where('caixa_id', $caixaId)
                    ->where('forma_pagamento', $forma)
                    ->whereIn('tipo', ['entrada_manual', 'saida_manual'])
                    ->where('observacao', 'like', '[AJUSTE%]')
                    ->update([
                        'valor' => $valorAjustado,
                    ]);
            }
        });

        return back()->with('success', 'Divergências corrigidas com sucesso.');
    }
 
    // public function ajustarDivergencias(Request $request, $caixaId)
    // {
    //     $request->validate([
    //         'formas'    => 'required|array|min:1',
    //         'formas.*'  => 'required|string',
    //     ]);

    //     DB::transaction(function () use ($request, $caixaId) {

    //         $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

    //         if (!$caixa->estaAberto() && $caixa->status !== 'inconsistente') {
    //             throw new \Exception('Caixa já foi fechado ou não está inconsistente.');
    //         }

    //         $userId = auth()->id();
    //         $totaisPorForma = CaixaService::totaisPorForma($caixaId);

    //         $valorTotalFechamento = 0;

    //         foreach ($request->formas as $forma => $valorCorrigidoBr) {

    //             $valorCorrigido = $this->parseValorBR($valorCorrigidoBr);
    //             $valorSistema   = (float) ($totaisPorForma[$forma] ?? 0);

    //             if (bccomp((string)$valorCorrigido, (string)$valorSistema, 2) !== 0) {
    //                 throw ValidationException::withMessages([
    //                     "formas.$forma" =>
    //                         "O valor corrigido de {$forma} deve ser exatamente R$ "
    //                         . number_format($valorSistema, 2, ',', '.')
    //                 ]);
    //             }

    //             $valorTotalFechamento += $valorCorrigido;
    //         }

    //         $valorTotalFechamento += $caixa->fundo_troco;

    //         MovimentacaoCaixa::create([
    //             'caixa_id' => $caixa->id,
    //             'user_id'  => $userId,
    //             'tipo'     => 'fechamento',
    //             'valor'    => $valorTotalFechamento,
    //             'data_movimentacao' => now(),
    //         ]);

    //         $caixa->update([
    //             'valor_fechamento' => $valorTotalFechamento,
    //             'status'           => 'fechado',
    //             'data_fechamento'  => now(),
    //             'fechado_por'      => $userId,
    //         ]);
    //     });

    //     return redirect()
    //         ->route('fechamento.lista')
    //         ->with('success', 'Divergências corrigidas e caixa fechado com sucesso.');
    // }

    public function ajustarDivergencias(Request $request, $caixaId)
    {
        $request->validate([
            'formas'    => 'required|array|min:1',
            'formas.*'  => 'required|string',
        ]);

        DB::transaction(function () use ($request, $caixaId) {

            $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

            if ($caixa->status !== 'inconsistente') {
                throw new \Exception('Caixa não está inconsistente para auditoria.');
            }

            $userId = auth()->id();
            $totaisPorForma = CaixaService::totaisPorForma($caixaId);

            $valorAuditado = 0;

            foreach ($request->formas as $forma => $valorCorrigidoBr) {

                $valorCorrigido = $this->parseValorBR($valorCorrigidoBr);
                $valorSistema   = (float) ($totaisPorForma[$forma] ?? 0);

                if (bccomp((string)$valorCorrigido, (string)$valorSistema, 2) !== 0) {
                    throw ValidationException::withMessages([
                        "formas.$forma" =>
                            "O valor corrigido de {$forma} deve ser exatamente R$ "
                            . number_format($valorSistema, 2, ',', '.')
                    ]);
                }

                $valorAuditado += $valorCorrigido;
            }

            // Soma fundo de troco ao valor auditado final
            $valorAuditado += $caixa->fundo_troco;

            /**
             * 🔴 AQUI ESTÁ O AJUSTE REAL
             * Nenhuma nova movimentação é criada
             * Apenas atualiza o caixa
             */
            $caixa->update([
                'valor_auditado' => $valorAuditado,
                'status'         => 'fechado',
                'data_fechamento'=> now(),
                'fechado_por'    => $userId,
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

    // public function atualizarValoresAjustados(Request $request, $caixaId)
    // {
    //     DB::transaction(function () use ($request, $caixaId) {

    //         foreach ($request->formas as $forma => $valorAjustado) {

    //             DB::table('movimentacoes_caixa')
    //                 ->where('caixa_id', $caixaId)
    //                 ->where('tipo', 'entrada_manual')
    //                 ->where('forma_pagamento', $forma)
    //                 ->update([
    //                     'valor' => (float) str_replace(',', '.', str_replace('.', '', $valorAjustado)),
    //                     'updated_at' => now(),
    //                 ]);
    //         }

    //     });

    //     return back()->with('success', 'Valores corrigidos no banco.');
    // }

    public function ajustar(Request $request, $caixaId)
    {
        $request->validate([
            'formas' => 'required|array'
        ]);

        DB::transaction(function () use ($request, $caixaId) {

            foreach ($request->formas as $forma => $valorCorrigido) {

                // Converte 1.234,56 → 1234.56
                $valorCorrigido = str_replace('.', '', $valorCorrigido);
                $valorCorrigido = str_replace(',', '.', $valorCorrigido);

                MovimentacaoCaixa::where('caixa_id', $caixaId)
                    ->where('tipo', 'entrada_manual')
                    ->whereNull('bandeira')
                    ->where('forma_pagamento', $forma)
                    ->update([
                        'valor' => $valorCorrigido,
                        'updated_at' => now(),
                        'observacao' => DB::raw(
                            "CONCAT('[AJUSTE AUDITORIA] ', IFNULL(observacao,''))"
                        )
                    ]);
            }
        });

        return back()->with('success', 'Valores ajustados com sucesso.');
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