<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditoriaCaixa;
use App\Models\AuditoriaDetalhe;
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

     public function fechar(Request $request, Caixa $caixa)
    {
        if (!$caixa->estaAberto()) {
            return back()->withErrors('Caixa já está fechado.');
        }

        if (!$caixa->possuiVendas()) {
            return $this->fecharSemMovimento($request, $caixa);
        }

        return $this->fecharMovimentoComAuditoria($request, $caixa);
           

    
    }

    //rota padrao para mostrar a confirmação do fechamento, seja com ou sem movimento
   public function confirmacao(Caixa $caixa)
    {
        if ($caixa->status === 'inconsistente') {
            return view('fechamento_caixa.confirmacao_inconsistente', compact('caixa'));
        }

        return view('fechamento_caixa.confirmacao', compact('caixa'));
    }


 
    private function fecharSemMovimento(Request $request, Caixa $caixa)
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
                'forma_pagamento' => 'fechamento',
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
            ->route('fechamento.confirmacao', $caixa->id)
            ->with('success', 'Caixa fechado sem movimentação.');
    }
    

  
    // public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    // {
    //     // Validação dos valores informados pelo operador
    //     $request->validate([
    //         'dinheiro'       => 'required|numeric|min:0',
    //         'pix'            => 'required|numeric|min:0',
    //         'carteira'       => 'required|numeric|min:0',
    //         'cartao_debito'  => 'required|numeric|min:0',
    //         'cartao_credito' => 'required|numeric|min:0',
    //     ]);

    //     $userId = Auth::id();

    //     // Valores físicos informados pelo operador
    //     $valoresFisicos = $request->only([
    //         'dinheiro','pix','carteira','cartao_debito','cartao_credito'
    //     ]);

    //     DB::transaction(function () use ($caixa, $userId, $valoresFisicos) {

    //         $caixa->lockForUpdate();

    //         /** ===============================
    //          * 1️⃣ Entradas manuais (operador)
    //          * =============================== */
    //         foreach ($valoresFisicos as $forma => $valor) {
    //             MovimentacaoCaixa::create([
    //                 'caixa_id'        => $caixa->id,
    //                 'user_id'         => $userId,
    //                 'tipo'            => 'entrada_manual',
    //                 'forma_pagamento' => $forma,
    //                 'valor'           => $valor,
    //                 'observacao'      => 'Valor físico informado no fechamento',
    //                 'data_movimentacao' => now(),
    //             ]);
    //         }

    //         /** ===============================
    //          * 2️⃣ Totais do sistema por forma
    //          * =============================== */
    //         $totalPorForma = $caixa->vendas
    //             ->flatMap->pagamentos
    //             ->where('status','confirmado')
    //             ->groupBy('forma_pagamento')
    //             ->map->sum('valor')
    //             ->toArray();

    //         /** ===============================
    //          * 3️⃣ Determinar status do caixa
    //          * =============================== */
    //         $status = 'fechado'; // assume que bate tudo
    //         foreach ($valoresFisicos as $forma => $valorFisico) {
    //             $totalSistemaForma = $totalPorForma[$forma] ?? 0;

    //             if (floatval($valorFisico) !== floatval($totalSistemaForma)) {
    //                 $status = 'inconsistente';
    //                 break; // basta uma divergência
    //             }
    //         }

    //         /** ===============================
    //          * 4️⃣ Registro do fechamento
    //          * =============================== */
    //         MovimentacaoCaixa::create([
    //             'caixa_id'       => $caixa->id,
    //             'user_id'        => $userId,
    //             'tipo'           => 'fechamento',
    //             'valor'          => array_sum($totalPorForma), // total do sistema
    //             'valor_auditado' => array_sum($valoresFisicos), // total físico
    //             'observacao'     => 'Fechamento realizado pelo operador',
    //             'data_movimentacao' => now(),
    //         ]);

    //         /** ===============================
    //          * 5️⃣ Atualização do caixa
    //          * =============================== */
    //         $caixa->update([
    //             'status'           => $status,
    //             'valor_fechamento' => array_sum($valoresFisicos),
    //             'data_fechamento'  => now(),
    //             'fechado_por'      => $userId,
    //         ]);
    //     });

    //     return redirect()
    //         ->route('fechamento.confirmacao', ['caixa' => $caixa->id])
    //         ->with('success', 'Caixa fechado com auditoria.');
    // }

    public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    {
        $request->validate([
            'dinheiro'        => 'required|numeric|min:0',
            'pix'             => 'required|numeric|min:0',
            'carteira'        => 'required|numeric|min:0',
            'cartao_debito'   => 'required|numeric|min:0',
            'cartao_credito'  => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();

        return DB::transaction(function () use ($request, $caixa, $userId) {

            $formas = [
                'dinheiro',
                'pix',
                'carteira',
                'cartao_debito',
                'cartao_credito'
            ];

            $valoresFisicos = $request->only($formas);

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ Buscar pagamentos confirmados do sistema
            |--------------------------------------------------------------------------
            */
            $pagamentos = $caixa->vendas
                ->flatMap->pagamentos
                ->where('status','confirmado');

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ Criar Auditoria principal (temporária)
            |--------------------------------------------------------------------------
            */
            $auditoria = AuditoriaCaixa::create([
                'caixa_id'        => $caixa->id,
                'user_id'         => $userId,
                'codigo_auditoria'=> 'AUD-' . $caixa->id . '-' . now()->format('YmdHis'),
                'total_sistema'   => 0,
                'total_fisico'    => 0,
                'diferenca'       => 0,
                'status'          => 'concluida',
                'data_auditoria'  => now(),
            ]);

            $totalSistemaGeral = 0;
            $totalFisicoGeral  = 0;
            $existeDivergencia = false;

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ Auditoria detalhada por forma
            |--------------------------------------------------------------------------
            */
            foreach ($formas as $forma) {

                $totalSistema = $pagamentos
                    ->where('forma_pagamento', $forma)
                    ->sum('valor');

                $totalFisico = $valoresFisicos[$forma];
                $diferenca   = $totalFisico - $totalSistema;

                if (abs($diferenca) > 0.01) {
                    $status = 'divergente';
                    $existeDivergencia = true;
                } else {
                    $status = 'correto';
                }

                AuditoriaDetalhe::create([
                    'auditoria_id'  => $auditoria->id,
                    'forma_pagamento'=> $forma,
                    'total_sistema' => $totalSistema,
                    'total_fisico'  => $totalFisico,
                    'diferenca'     => $diferenca,
                    'status'        => $status,
                ]);

                $totalSistemaGeral += $totalSistema;
                $totalFisicoGeral  += $totalFisico;
            }

            /*
            |--------------------------------------------------------------------------
            | 4️⃣ Atualizar auditoria principal
            |--------------------------------------------------------------------------
            */
            $auditoria->update([
                'total_sistema' => $totalSistemaGeral,
                'total_fisico'  => $totalFisicoGeral,
                'diferenca'     => $totalFisicoGeral - $totalSistemaGeral,
                'status'        => $existeDivergencia ? 'inconsistente' : 'concluida',
            ]);

            /*
            |--------------------------------------------------------------------------
            | 5️⃣ Atualizar caixa
            |--------------------------------------------------------------------------
            */
            $caixa->update([
                'status'           => $existeDivergencia ? 'inconsistente' : 'fechado',
                'valor_fechamento' => $totalFisicoGeral,
                'data_fechamento'  => now(),
                'fechado_por'      => $userId,
            ]);

            return redirect()
                ->route('fechamento.confirmacao', $caixa->id)
                ->with('success', $existeDivergencia
                    ? 'Caixa fechado com divergências.'
                    : 'Caixa fechado sem divergências.'
                );
        });
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

            // if ($caixa->status !== 'inconsistente') {
            //     throw new \Exception('Caixa não está inconsistente para auditoria.');
            // }

            $userId = auth()->id();
            $totalAuditado = 0;

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

         // Busca o caixa no banco
        $caixa = Caixa::findOrFail($caixaId);

       return redirect()->route('fechamento.auditoria', $caixa->id)
        ->with('auditoria_sucesso', 'A divergência do Caixa #' . $caixa->id . ' foi corrigida pela auditoria.');

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
   
    //CORRIGE AS DIVERGÊNCIAS APÓS A AUDITORIA FISCAL e imprime mensagem na tela
   public function auditoria($caixa)
{
    $caixa = Caixa::findOrFail($caixa);

    $movimentacoes = MovimentacaoCaixa::where('caixa_id', $caixa->id)
                        ->where('tipo', 'fechamento')
                        ->get();

    return view('fechamento_caixa.confirmacao_auditoria', compact('caixa', 'movimentacoes'));

}

    
}