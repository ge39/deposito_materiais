<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditoriaCaixa;
use App\Models\AuditoriaDetalhe;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use App\Models\PagamentoVenda;
use App\Services\CaixaService;
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
        $total_sangrias = 0;

         $total_sangrias = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('tipo', 'sangria')
        ->where('forma_pagamento', 'sangria')
        ->sum('valor');

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
                'totalGeralSistema',
                'total_sangrias'
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

    public function possuiVendas()
    {
        return $this->vendas()->exists();
    }

     public function fechar(Request $request, Caixa $caixa)
    {
        // dd('entrou no metodo');
        if (!$caixa->estaAberto()) {
            return back()->withErrors('Caixa já está fechado.');
            // dd('passou do estaAberto');
        }

        if (!$caixa->possuiVendas()) {

            return $this->fecharSemMovimento($request, $caixa);
          
        }
        // dd('passou do estaAberto');
        return $this->fecharMovimentoComAuditoria($request, $caixa);
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

    public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    {
        if ($caixa->status !== 'aberto') {
            abort(403, 'Este caixa já foi fechado ou não está disponível para fechamento.');
        }

        $request->validate([
            'dinheiro'       => 'required|numeric|min:0',
            'pix'            => 'required|numeric|min:0',
            'carteira'       => 'required|numeric|min:0',
            'cartao_debito'  => 'required|numeric|min:0',
            'cartao_credito' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();
        $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
        $valoresFisicos = $request->only($formas);

        return DB::transaction(function () use ($request, $caixa, $userId, $formas, $valoresFisicos) {

            $caixa->refresh();

            if ($caixa->status !== 'aberto') {
                throw new \Exception('Este caixa já foi fechado durante o processamento.');
            }

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ TOTAL SISTEMA GERAL
            |--------------------------------------------------------------------------
            */
            $totalSistemaGeral = PagamentoVenda::whereHas('venda', function ($q) use ($caixa) {
                    $q->where('caixa_id', $caixa->id);
                })
                ->where('status', 'confirmado')
                ->sum('valor');

            $totalSistemaGeral += $caixa->fundo_troco;

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ TOTAL FÍSICO
            |--------------------------------------------------------------------------
            */
            $totalFisicoGeral = array_sum($valoresFisicos);

            $diferencaGeral = $totalFisicoGeral - $totalSistemaGeral;

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ CRIAR AUDITORIA PRINCIPAL
            |--------------------------------------------------------------------------
            */
            $codigoAuditoria = 'AUD-' . $caixa->id . '-' . now()->format('YmdHis');

            $auditoria = AuditoriaCaixa::create([
                'caixa_id'         => $caixa->id,
                'user_id'          => $userId,
                'codigo_auditoria' => $codigoAuditoria,
                'total_sistema'    => $totalSistemaGeral,
                'total_fisico'     => $totalFisicoGeral,
                'diferenca'        => $diferencaGeral,
                'status'           => abs($diferencaGeral) > 0.01 ? 'inconsistente' : 'concluida',
                'observacao'       => 'Fechamento com auditoria',
                'data_auditoria'   => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | 4️⃣ AUDITORIA POR FORMA + AJUSTES AUTOMÁTICOS
            |--------------------------------------------------------------------------
            */
            foreach ($formas as $forma) {

                $totalSistemaForma = PagamentoVenda::whereHas('venda', function ($q) use ($caixa) {
                        $q->where('caixa_id', $caixa->id);
                    })
                    ->where('status', 'confirmado')
                    ->where('forma_pagamento', $forma)
                    ->sum('valor');

                if ($forma === 'dinheiro') {
                    $totalSistemaForma += $caixa->fundo_troco;
                }

                $totalFisicoForma = $valoresFisicos[$forma];

                $diferencaForma = $totalFisicoForma - $totalSistemaForma;

                // Salvar detalhe da auditoria
                AuditoriaDetalhe::create([
                    'auditoria_id'    => $auditoria->id,
                    'forma_pagamento' => $forma,
                    'total_sistema'   => $totalSistemaForma,
                    'total_fisico'    => $totalFisicoForma,
                    'diferenca'       => $diferencaForma,
                    'status'          => abs($diferencaForma) > 0.01 ? 'divergente' : 'correto',
                ]);


                /*
                |--------------------------------------------------------------------------
                | 🔹 REGISTRAR VALOR FÍSICO INFORMADO (entrada manual)
                |--------------------------------------------------------------------------
                */
                MovimentacaoCaixa::create([
                    'caixa_id'          => $caixa->id,
                    'auditoria_id'      => $auditoria->id, // ✅ também vinculado
                    'tipo'              => 'entrada_manual',
                    'forma_pagamento'   => $forma,
                    'valor'             => $totalFisicoForma,
                    'observacao'        => 'Valor físico informado no fechamento',
                    'user_id'           => $userId,
                    'data_movimentacao' => now(),
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | 🔹 REGISTRAR SAÍDAS (se houver)
        |--------------------------------------------------------------------------
        */

        if (
            $request->saida_sangria > 0 ||
            $request->saida_despesa > 0 ||
            $request->saida_ajuste  > 0 ||
            $request->saida_outros  > 0
        ) {
            $this->registrarSaidas($request, $caixa, $auditoria);
        }   

            /*
            |--------------------------------------------------------------------------
            | 5️⃣ ATUALIZAR CAIXA
            |--------------------------------------------------------------------------
            */
            $caixa->update([
                'status'           => abs($diferencaGeral) > 0.01 ? 'inconsistente' : 'fechado',
                'valor_fechamento' => $totalFisicoGeral,
                'data_fechamento'  => now(),
                'fechado_por'      => $userId,
            ]);

            return redirect()
                ->route('fechamento.confirmacao', $caixa->id)
                ->with('success',
                    abs($diferencaGeral) > 0.01
                        ? 'Caixa fechado com divergências.'
                        : 'Caixa fechado sem divergências.'
                );
        });
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

    private function registrarSaidas($request, $caixa, $auditoria = null): void
    {
        $map = [
            'saida_sangria' => 'sangria',
            'saida_despesa' => 'despesa',
            'saida_ajuste'  => 'ajuste_negativo',
            'saida_outros'  => 'outras_saidas',
        ];

        foreach ($map as $campo => $tipo) {
            $valor = (float) $request->$campo;

            if ($valor > 0) {
                MovimentacaoCaixa::create([
                    'caixa_id'          => $caixa->id,
                    'auditoria_id'      => $auditoria->id ?? null,
                    'tipo'              => 'saida_manual',
                    'forma_pagamento'   => $tipo,
                    'valor'             => $valor,
                    'observacao'        => ucfirst(str_replace('_', ' ', $tipo)),
                    'user_id'           => auth()->id(),
                    'data_movimentacao' => now(),
                ]);
            }
        }
    }
    public function ajustarDivergencias(Request $request, int $caixaId)
    {
        $request->validate([
            'formas'   => 'required|array|min:1',
            'formas.*' => 'required',
        ]);
        
        DB::transaction(function () use ($request, $caixaId) {

            // Antes de criar o fechamento, calcule o total das vendas do sistema:
           $totalVendas = PagamentoVenda::whereHas('venda', function ($query) use ($caixaId) {
                $query->where('caixa_id', $caixaId);
            })
            ->where('status', 'confirmado')
            ->sum('valor');
 

            $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

            if ($caixa->status !== 'inconsistente') {
                throw new \Exception('Caixa não está inconsistente para auditoria.');
            }

            $userId = auth()->id();
            $totalAuditado = 0;

            foreach ($request->formas as $forma => $valorStr) {

                $valorAuditado = $this->parseValorBR($valorStr);

                // valor real do sistema (vendas)
               $valorSistema = PagamentoVenda::whereHas('venda', function ($query) use ($caixa) {
                    $query->where('caixa_id', $caixa->id);
                })
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

        $totaisInformados = MovimentacaoCaixa::where('caixa_id', $caixa->id)
            ->where('tipo', 'entrada_manual')
            ->selectRaw('forma_pagamento, SUM(valor) as total')
            ->groupBy('forma_pagamento')
            ->pluck('total', 'forma_pagamento')
            ->toArray();
            
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
            'divergencia',
            'totaisInformados' // <-- FALTAVA ISSO
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

     //rota padrao para mostrar a confirmação do fechamento, seja com ou sem movimento
    public function confirmacao(Caixa $caixa)
    {
        if ($caixa->status === 'inconsistente') {
            return view('fechamento_caixa.confirmacao_inconsistente', compact('caixa'));
        }

        return view('fechamento_caixa.confirmacao', compact('caixa'));
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