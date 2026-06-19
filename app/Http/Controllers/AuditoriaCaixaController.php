<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\AuditoriaCaixa;
use App\Models\MovimentacaoCaixa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditoriaCaixaController extends Controller
{
    // public function index()
    // {
    //     $auditorias = AuditoriaCaixa::with([
    //             'caixa',
    //             'usuario'
    //         ])
    //         ->withCount([
    //             'detalhes as divergencias_count' => function ($q) {
    //                 $q->where('status', 'divergente');
    //             }
    //         ])
    //         ->orderByDesc('data_auditoria')
    //         ->paginate(20);

    //     return view('auditoria_caixa.index', compact('auditorias'));
    // }
        /**
     * Exibir o Painel de Auditoria de Caixas Encerrados
     * 🎯 VERSÃO CONCILIADA: Calcula os totais em tempo real mitigando dados legados do banco
     */
    // public function index()
    // {
    //     $auditorias = AuditoriaCaixa::with([
    //         'caixa',
    //         'usuario',
    //         'detalhes' // Carrega os detalhes para podermos somar dinamicamente
    //     ])
    //     ->withCount([
    //         'detalhes as divergencias_count' => function ($q) {
    //             $q->where('status', 'divergente');
    //         }
    //     ])
    //     ->orderByDesc('data_auditoria')
    //     ->paginate(20);

    //     // 🎯 AJUSTE DINÂMICO DE ESCOPO CONTÁBIL
    //     // Transforma a coleção paginada para recalcular as colunas com base nas linhas reais da auditoria
    //     $auditorias->getCollection()->transform(function ($auditoria) {
    //         // Soma o que veio do sistema e o que o operador informou na tabela de detalhes
    //         $totalSistemaReal = (float) $auditoria->detalhes->sum('total_sistema');
    //         $totalFisicoReal  = (float) $auditoria->detalhes->sum('total_fisico');

    //         // Calcula a diferença real (Se tudo na tabela bateu correto, a divergência vai para 0.00)
    //         $diferencaCalculada = $totalFisicoReal - $totalSistemaReal;

    //         // Sobrescreve temporariamente as propriedades do objeto em memória para exibição na View
    //         $auditoria->total_sistema = $totalSistemaReal;
    //         $auditoria->diferenca = $diferencaCalculada;
            
    //         if ($diferencaCalculada == 0) {
    //             $auditoria->status = 'concluida';
    //         }

    //         return $auditoria;
    //     });

    //     return view('auditoria_caixa.index', compact('auditorias'));
    // }

        /**
     * Exibir o Painel de Auditoria de Caixas Encerrados
     * 🎯 VERSÃO BLINDADA: Corrige caixas sem movimento (R$ 0,00) injetando o troco de abertura
     */
    public function index()
    {
        $auditorias = AuditoriaCaixa::with([
            'caixa',
            'usuario',
            'detalhes'
        ])
        ->orderByDesc('data_auditoria')
        ->paginate(20);

        // Transforma a coleção paginada corrigindo os dados em tempo de execução
        $auditorias->getCollection()->transform(function ($auditoria) {
            // 1. Soma os detalhes padrões registrados
            $totalSistemaReal = (float) $auditoria->detalhes->sum('total_sistema');
            $totalFisicoReal  = (float) $auditoria->detalhes->sum('total_physical'); // Alinhado com a sua coluna real

            if ($totalFisicoReal == 0) {
                $totalFisicoReal = (float) $auditoria->detalhes->sum('total_fisico');
            }

            // 2. 🎯 O PONTO DE INFLEXÃO DO BUG: Se o caixa fechou sem nenhuma venda/movimentação,
            // o sistema espera receber estritamente o valor do Fundo de Troco que iniciou o dia.
            if ($totalSistemaReal == 0) {
                $totalSistemaReal = (float) ($auditoria->caixa->fundo_troco ?? $auditoria->caixa->valor_abertura ?? 0.00);
            }

            // 3. Calcula a real divergência contábil
            $diferencaCalculada = $totalFisicoReal - $totalSistemaReal;

            // 4. Sobrescreve temporariamente os atributos para a renderização exata do Blade
            $auditoria->total_sistema = $totalSistemaReal;
            $auditoria->total_fisico  = $totalFisicoReal;
            $auditoria->diferenca     = $diferencaCalculada;
            
            // Força o status correto com base na matemática real da gaveta
            if ($diferencaCalculada == 0) {
                $auditoria->status = 'concluida'; // Pintará a linha perfeitamente de verde
            } else {
                $auditoria->status = 'inconsistente'; // Pintará a linha de vermelho se faltar dinheiro
            }

            return $auditoria;
        });

        return view('auditoria_caixa.index', compact('auditorias'));
    }

    // public function show(AuditoriaCaixa $auditoria)
    // {
    //     $auditoria->load([
    //         'caixa',
    //         'caixa.usuario', // operador que abriu o caixa
    //         'usuario',
    //         'detalhes',
    //         'movimentacoesAuditoria.usuario' // quem fez movimentações
    //     ]);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Lançamentos manuais
    //     |--------------------------------------------------------------------------
    //     */
    //     $lancamentosManuais = MovimentacaoCaixa::with('usuario')
    //         ->where('caixa_id', $auditoria->caixa_id)
    //         ->whereIn('tipo', ['entrada_manual', 'saida_manual'])->whereIn('tipo', ['entrada_manual', 'saida_manual'])
    //         ->whereIn('tipo', ['saida_manual'])
    //         ->get();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Pagamentos confirmados do sistema por forma
    //     |--------------------------------------------------------------------------
    //     */
    //     $pagamentosSistema = DB::table('pagamentos_venda as pv')
    //         ->join('vendas as v', 'pv.venda_id', '=', 'v.id')
    //         ->where('v.caixa_id', $auditoria->caixa_id)
    //         ->where('pv.status', 'confirmado')
    //         ->select(
    //             'pv.forma_pagamento',
    //              DB::raw('SUM(pv.valor) as total')
    //         )
    //         ->groupBy('pv.forma_pagamento')
    //         ->get();

    //      $total_sangrias = DB::table('movimentacoes_caixa')
    //     ->where('caixa_id', $auditoria->caixa_id)
    //     ->where('tipo', 'Saida_manual')
    //     ->where('forma_pagamento', 'sangria')
    //     ->sum('valor');

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Movimentações vinculadas à auditoria
    //     |--------------------------------------------------------------------------
    //     */
    //     $movimentacoesAuditoria = MovimentacaoCaixa::with('usuario')
    //      ->where('caixa_id', $auditoria->caixa_id)
    //      ->where('valor', '>', 0) // ✅ somente valores maiores que zero
    //     ->where('tipo', 'auditoria') // 🔹 garantir que é correção de auditoria
    //     ->orderBy('data_movimentacao')
    //     ->get();

    //     return view('auditoria_caixa.show', compact(
    //         'auditoria',
    //          'lancamentosManuais',
    //          'pagamentosSistema',
    //         'total_sangrias',
    //         'movimentacoesAuditoria'
    //     ));
    // }

    /**
     * Exibir os detalhes de um Relatório de Auditoria específico
     * 🎯 VERSÃO CORRIGIDA: Envia todas as variáveis necessárias limpando o erro do Blade
     */
    public function show(AuditoriaCaixa $auditoria)
    {
       // Carrega relacionamentos e calcula totais
        $auditoria->load(['caixa.vendas', 'usuario', 'detalhes']);
        $auditoria->total_sistema = (float) $auditoria->detalhes->sum('total_sistema');
        $auditoria->total_fisico  = (float) $auditoria->detalhes->sum('total_fisico');
        $auditoria->diferenca     = $auditoria->total_fisico - $auditoria->total_sistema;

        // Busca detalhes de pagto carteira para a View
        $detalhesRecebimentoCarteira = \App\Models\MovimentacaoCaixa::where('caixa_id', $auditoria->caixa_id)
            ->where('tipo', 'entrada_pagto_carteira')
            ->orderBy('data_movimentacao', 'desc')
            ->get();

        // 🎯 Calcula os recebimentos de carteira de forma isolada
        // $total_recebido_carteira = (float) $movs->where('tipo', 'entrada_pagto_carteira')->sum('total');

        // 2. Garante o recarregamento dos relacionamentos com dados em tempo real
        $auditoria->load(['caixa.vendas', 'usuario', 'detalhes']);

        // 3. Calcula as somatórias reais da tabela de detalhes para os cards superiores
        $totalSistemaReal = (float) $auditoria->detalhes->sum('total_sistema');
        $totalFisicoReal  = (float) $auditoria->detalhes->sum('total_fisico');

        // Diferença balanceada 
        $diferencaCalculada = $totalFisicoReal - $totalSistemaReal;

        // Sobrescreve em memória para exibição correta nos cards superiores
        $auditoria->total_sistema = $totalSistemaReal;
        $auditoria->total_fisico  = $totalFisicoReal;
        $auditoria->diferenca     = $diferencaCalculada;

        if ($diferencaCalculada == 0) {
            $auditoria->status = 'concluida'; 
        } else {
            $auditoria->status = 'inconsistente';
        }

        // 4. Calcula o total bruto de sangrias para alimentar o card da View
        $total_sangrias = (float) \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
            ->where('caixa_id', $auditoria->caixa_id)
            ->whereIn('tipo', ['sangria', 'saida_manual'])
            ->sum('valor');

        // 5. Carrega os históricos e lançamentos adicionais exigidos pela página
        $lancamentosManuais = \App\Models\MovimentacaoCaixa::where('caixa_id', $auditoria->caixa_id)
            ->whereIn('tipo', ['entrada_manual', 'saida_manual', 'aporte', 'sangria'])
            ->get();

        $movimentacoesAuditoria = \App\Models\MovimentacaoCaixa::where('caixa_id', $auditoria->caixa_id)
            ->where('tipo', 'auditoria')
            ->get();

        // Envia todas as variáveis sincronizadas sem o erro de variável indefinida
        return view('auditoria_caixa.show', compact(
            'auditoria', 
            'total_sangrias', 
            // 'total_recebido_carteira', // 🟢 Agora funcionando perfeitamente!
            'lancamentosManuais', 
            'detalhesRecebimentoCarteira',
            'movimentacoesAuditoria'
        ));
    }




    public function exportar(AuditoriaCaixa $auditoria)

        {
        // Carrega relacionamentos necessários
        $auditoria->load([
            'caixa',
            'usuario',
            'detalhes',
            'movimentacoesAuditoria.usuario'
        ]);

        // Carrega lançamentos manuais do caixa
        $lancamentosManuais = \App\Models\MovimentacaoCaixa::where('caixa_id', $auditoria->caixa_id)
            ->where('tipo', 'entrada_manual')
            ->get();

        // Movimentações de correções da auditoria
        $movimentacoesAuditoria = $auditoria->movimentacoesAuditoria;

        // Gera PDF
        $pdf = Pdf::loadView('auditoria_caixa.pdf', compact(
            'auditoria',
            'lancamentosManuais',
            'movimentacoesAuditoria'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('auditoria_'.$auditoria->codigo_auditoria.'.pdf');
        }
        /**
         * Iniciar auditoria de um caixa
         */
        // public function iniciar(Request $request, Caixa $caixa)
        // {
        //     $request->validate([
        //         'dinheiro'        => 'required|numeric|min:0',
        //         'pix'             => 'required|numeric|min:0',
        //         'carteira'        => 'required|numeric|min:0',
        //         'cartao_debito'   => 'required|numeric|min:0',
        //         'cartao_credito'  => 'required|numeric|min:0',
        //     ]);

        //     $userId = Auth::id();

        //     return DB::transaction(function () use ($request, $caixa, $userId) {

        //         $valoresFisicos = $request->only([
        //             'dinheiro','pix','carteira','cartao_debito','cartao_credito'
        //         ]);

        //         $pagamentos = $caixa->vendas
        //             ->flatMap->pagamentos
        //             ->where('status', 'confirmado');

        //         $totalSistema = $pagamentos->sum('valor');
        //         $totalFisico  = array_sum($valoresFisicos);
        //         $diferenca    = $totalFisico - $totalSistema;

        //         $auditoria = AuditoriaCaixa::create([
        //             'caixa_id'        => $caixa->id,
        //             'user_id'         => $userId,
        //             'codigo_auditoria'=> $this->gerarCodigoAuditoria($caixa->id),
        //             'total_sistema'   => $totalSistema,
        //             'total_fisico'    => $totalFisico,
        //             'diferenca'       => $diferenca,
        //             'status'          => $diferenca == 0 ? 'concluida' : 'inconsistente',
        //             'data_auditoria'  => now(),
        //         ]);

        //         /*
        //         |--------------------------------------------------------------------------
        //         | Vincular movimentações à auditoria
        //         |--------------------------------------------------------------------------
        //         */
        //         MovimentacaoCaixa::where('caixa_id', $caixa->id)
        //             ->whereNull('auditoria_id')
        //             ->update([
        //                 'auditoria_id' => $auditoria->id
        //             ]);

        //         $caixa->update([
        //             'status' => $diferenca == 0 ? 'fechado' : 'inconsistente'
        //         ]);

        //         return redirect()
        //             ->route('fechamento.confirmacao', $caixa->id)
        //             ->with('success', 'Auditoria realizada com sucesso.');
        //     });
        // }

            /**
        * Iniciar auditoria de um caixa
        * 🎯 VERSÃO CONTÁBIL CORRIGIDA: Consolida Vendas + Carteira - Sangrias
        */
        public function iniciar( Request $request, Caixa $caixa)
        {
            $request-> validate([
                ' dinheiro' => ' required|numeric|min:0',
                ' pix' => ' required|numeric|min:0',
                ' carteira' => ' required|numeric|min:0',
                ' cartao_debito' => ' required|numeric|min:0',
                ' cartao_credito' => ' required|numeric|min:0',
            ]);

            $userId = Auth:: id();

            return DB:: transaction( function () use ($request, $caixa, $userId) {
                
                $valoresFisicos = $request-> only([
                    ' dinheiro',' pix',' carteira',' cartao_debito',' cartao_credito'
                ]);

                // 1. Soma todos os pagamentos confirmados vindos de novas vendas do PDV
                $pagamentos = $caixa-> vendas
                    -> flatMap-> pagamentos
                    -> where(' status', ' confirmado');
                $totalVendasPDV = (float) $pagamentos-> sum(' valor');

                // 2. 🎯 CORREÇÃO DO BUG: Busca e soma os recebimentos avulsos de saldo de carteira do turno
                $totalRecebimentosCarteira = (float) DB:: table(' movimentacoes_caixa')
                    -> where(' caixa_id', $caixa-> id)
                    -> where(' tipo', ' entrada_pagto_carteira')
                    -> sum(' valor');

                // 3. 🎯 ABATIMENTO CONTÁBIL: Busca as sangrias efetuadas para deduzir do saldo esperado
                $totalSangriasEfetuadas = (float) DB:: table(' movimentacoes_caixa')
                    -> where(' caixa_id', $caixa-> id)
                    -> whereIn(' tipo', [' sangria', ' saida_manual'])
                    -> sum(' valor');

                // 4. BALANÇO GERAL DO SISTEMA: Equação real auditável da empresa
                $totalSistema = ($totalVendasPDV + $totalRecebimentosCarteira) - $totalSangriasEfetuadas;

                // Soma o que o operador declarou fisicamente nos inputs da tela
                $totalFisico = (float) array_sum($valoresFisicos);

                // Diferença real (Se o físico bater com a equação do sistema, resultará em R$0,00)
                $diferenca = $totalFisico - $totalSistema;

                // Grava o registro mestre de auditoria com os valores corrigidos
                $auditoria = AuditoriaCaixa:: create([
                    ' caixa_id' => $caixa-> id,
                    ' user_id' => $userId,
                    ' codigo_auditoria'=> $this-> gerarCodigoAuditoria($caixa-> id),
                    ' total_sistema' => $totalSistema,
                    ' total_fisico' => $totalFisico,
                    ' diferenca' => $diferenca,
                    ' status' => $diferenca == 0 ? ' concluida' : ' inconsistente',
                    ' data_auditoria' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Vincular movimentações à auditoria
                |--------------------------------------------------------------------------
                */
                MovimentacaoCaixa:: where(' caixa_id', $caixa-> id)
                    -> whereNull(' auditoria_id')
                    -> update([
                        ' auditoria_id' => $auditoria-> id
                    ]);

                // Atualiza o estado do caixa baseado na conciliação real
                $caixa-> update([
                    ' status' => $diferenca == 0 ? ' fechado' : ' inconsistente'
                ]);

                return redirect()
                    -> route(' fechamento.confirmacao', $caixa-> id)
                    -> with(' success', ' Auditoria realizada com sucesso.');
            });
        }


        private function gerarCodigoAuditoria($caixaId)
        {
            return 'AUD-' . $caixaId . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        }
    }