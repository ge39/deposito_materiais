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

    // public function index($caixaId)
    // {
    //     $caixa = Caixa::with('movimentacoes')->findOrFail($caixaId);
    
    //     $total_esperado = CaixaService::total_esperado($caixaId);

    //     $total_entradas = DB::table('movimentacoes_caixa')
    //         ->where('caixa_id', $caixaId)
    //         // ->whereIn('tipo', ['abertura', 'entrada_manual', 'venda'])
    //         // ->where('tipo', 'venda')
    //         ->sum('valor');
           

    //     $total_saidas = DB::table('movimentacoes_caixa')
    //     ->where('caixa_id', $caixaId)
    //     ->whereIn('tipo', ['saida_manual', 'cancelamento_venda'])
    //     ->sum('valor');
        

    //     /** ============================
    //      * INICIALIZAÇÃO SEGURA
    //      * ============================ */
    //     $divergencia = 0;
    //     $totaisPorForma = [];
    //     $totalGeralSistema = 0;
    //     $total_sangrias = 0;

    //      $total_sangrias = DB::table('movimentacoes_caixa')
    //     ->where('caixa_id', $caixaId)
    //     ->where('tipo', 'sangria')
    //     ->where('forma_pagamento', 'sangria')
    //     ->sum('valor');

    //     /** ============================
    //      * TOTAIS DO SISTEMA POR FORMA
    //      * ============================ */
    //     if ($caixa->vendas->count()) {
    //         foreach ($caixa->vendas as $venda) {
    //             foreach ($venda->pagamentos as $pag) {
    //                 if ($pag->status !== 'confirmado') {
    //                     continue;
    //                 }

    //                 $forma = $pag->forma_pagamento;
    //                 $totaisPorForma[$forma] = ($totaisPorForma[$forma] ?? 0) + $pag->valor;
    //             }
    //         }

    //         $totalGeralSistema = array_sum($totaisPorForma);
    //     }

    //         return view('fechamento_caixa.index', compact(
    //             'caixa',
    //             'total_entradas',
    //             'total_saidas',
    //             'total_esperado',
    //             'divergencia',
    //             'totaisPorForma',
    //             'totalGeralSistema',
    //             'total_sangrias'
    //         ));
    // }

    public function index($caixaId)
    {
        // 1️⃣ Carrega o caixa garantindo que ele exista
        $caixa = Caixa::findOrFail($caixaId);

        // 2️⃣ TOTAL ENTRADAS: Soma tudo o que injetou dinheiro (Abertura, Vendas do PDV, Aportes/Entradas Manuais)
        $total_entradas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['abertura', 'venda'])
            ->sum('valor');

        // 3️⃣ TOTAL SAÍDAS: Soma retiradas manuais, despesas e cancelamentos
        $total_saidas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa'])
            ->sum('valor');

        // 4️⃣ SANGRIAS: Isola o total de sangrias para dedução ou exibição limpa
        $total_sangrias = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->where('tipo', 'sangria')
            ->sum('valor');

        // 5️⃣ FORMAS DE PAGAMENTO DO SISTEMA: Inicializa a matriz zerada para a Blade
        $totaisPorForma = [
            'dinheiro'       => 0.00,
            'pix'            => 0.00,
            'carteira'       => 0.00,
            'cartao_debito'  => 0.00,
            'cartao_credito' => 0.00
        ];

        // Busca e agrupa os valores de vendas ou lançamentos manuais já consolidados nesta fita
        $movimentosAgrupados = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['venda', 'entrada_manual']) // Lê as vendas do dia E os fechamentos dos caixas antigos
            ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
            ->groupBy('forma_pagamento')
            ->get();

        foreach ($movimentosAgrupados as $mov) {
            $forma = strtolower(trim($mov->forma_pagamento));
            // Remove possíveis variações de escrita (ex: "cartao debito" vira "cartao_debito")
            $forma = str_replace(' ', '_', $forma); 

            if (array_key_exists($forma, $totaisPorForma)) {
                $totaisPorForma[$forma] = (float) $mov->total;
            }
        }

        // 6️⃣ MATEMÁTICA CONSOLIDADA DO SISTEMA
        $totalGeralSistema = array_sum($totaisPorForma);

        // O esperado em dinheiro físico é: Fundo de Troco + Entradas em Dinheiro Vivo - Saídas/Sangrias
        $dinheiroDoSistema = $totaisPorForma['dinheiro'] ?? 0.00;
        $total_esperado = ($caixa->fundo_troco + $dinheiroDoSistema) - ($total_saidas + $total_sangrias);
        
        $divergencia = 0.00;

        // Carrega o histórico completo de movimentações para alimentar a tabela do rodapé da Blade
        $caixa->setRelation('movimentacoes', $caixa->movimentacoes()->orderBy('id', 'asc')->get());

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
        
        // Aceita tanto o status 'aberto' quanto o 'inconsistente' se o operador precisar re-enviar
        if ($caixa->status !== 'aberto' && $caixa->status !== 'inconsistente') {
            return back()->withErrors('Este caixa já está fechado.');
            // dd('passou do estaAberto');
        }

        // 🌟 A CORREÇÃO DE LOGICA: Verifica se existem linhas do tipo 'venda' gravadas na fita do caixa
        $temMovimentoDeVenda = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixa->id)
            ->where('tipo', 'venda')
            ->exists();

        // Se NÃO existirem vendas registradas na fita, segue o fluxo administrativo sem movimento
        if (!$temMovimentoDeVenda) {
            return $this->fecharSemMovimento($request, $caixa);
        }
        
        // dd('passou do estaAberto');
        
        // Se existirem vendas, segue o fluxo correto de auditoria dos valores da gaveta
        return $this->fecharMovimentoComAuditoria($request, $caixa);
    }

    // private function fecharSemMovimento(Request $request, Caixa $caixa)
    // {
    //     // 1️⃣ Valida a justificativa enviada pela Blade
    //     $request->validate([
    //         'motivo_fechamento' => 'required|string|max:255',
    //     ]);

    //     return DB::transaction(function () use ($request, $caixa) {
    //         $caixa->refresh();

    //         if ($caixa->status !== 'aberto') {
    //             throw new \Exception('Este caixa já foi modificado ou fechado por outra sessão.');
    //         }

    //         $userId = auth()->id();
    //         $motivo = $request->input('motivo_fechamento');

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 2️⃣ GRAVAÇÃO DO CABEÇALHO DE AUDITORIA (auditorias_caixa)
    //         |--------------------------------------------------------------------------
    //         */
    //         $codigoAuditoria = 'AUD-SEM-' . $caixa->id . '-' . now()->format('YmdHis');
    //         $totalSistemaGeral = (float) $caixa->fundo_troco; // O sistema espera apenas o troco inicial

    //         $auditoriaId = DB::table('auditorias_caixa')->insertGetId([
    //             'caixa_id'         => $caixa->id,
    //             'user_id'          => $userId,
    //             'codigo_auditoria' => $codigoAuditoria,
    //             'total_sistema'    => $totalSistemaGeral,
    //             'total_fisico'     => $totalSistemaGeral, // O físico também é o próprio troco que sobrou
    //             'diferenca'        => 0.00,               // Sem movimento = divergência zero
    //             'status'           => 'concluida',        // ENUM exato do seu banco
    //             'observacao'       => "Fechamento sem movimento comercial. Motivo: {$motivo}",
    //             'data_auditoria'   => now(),
    //         ]);

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 3️⃣ GRAVAÇÃO DOS DETALHES POR FORMA (auditoria_detalhes)
    //         |--------------------------------------------------------------------------
    //         */
    //         $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];

    //         foreach ($formas as $forma) {
    //             // No caixa sem movimento, o sistema só espera dinheiro do fundo de troco. O resto é zero.
    //             $sistemaForma = ($forma === 'dinheiro') ? (float) $caixa->fundo_troco : 0.00;
    //             $fisicoForma  = $sistemaForma; // Assume o mesmo valor para fechar zerado

    //             DB::table('auditoria_detalhes')->insert([
    //                 'auditoria_id'    => $auditoriaId,
    //                 'forma_pagamento' => $forma,
    //                 'total_sistema'   => $sistemaForma,
    //                 'total_fisico'    => $fisicoForma,
    //                 'diferenca'       => 0.00,
    //                 'status'          => 'correto', // ENUM exato do seu banco
    //                 'created_at'      => now(),
    //                 'updated_at'      => now(),
    //             ]);
    //         }

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 4️⃣ REGISTRO HISTÓRICO NA FITA (movimentacoes_caixa)
    //         |--------------------------------------------------------------------------
    //         */
    //         DB::table('movimentacoes_caixa')->insert([
    //             'caixa_id'          => $caixa->id,
    //             'auditoria_id'      => $auditoriaId, // Vincula à auditoria criada
    //             'user_id'           => $userId,
    //             'tipo'              => 'fechamento_sem_movimento',
    //             'forma_pagamento'   => 'fechamento',
    //             'valor'             => (float) $caixa->fundo_troco,
    //             'valor_auditado'    => 0.00,
    //             'bandeira'          => null,
    //             'origem_id'         => null,
    //             'observacao'        => $motivo,
    //             'data_movimentacao' => now(),
    //             'created_at'        => now(),
    //             'updated_at'        => now(),
    //         ]);

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 5️⃣ ATUALIZAÇÃO DEFINITIVA DO STATUS DO CAIXA
    //         |--------------------------------------------------------------------------
    //         */
    //         $caixa->update([
    //             'valor_fechamento' => (float) $caixa->fundo_troco,
    //             'status'           => 'fechado_sem_movimento',
    //             'data_fechamento'  => now(),
    //             'fechado_por'      => $userId,
    //             'observacao'       => $motivo,
    //         ]);

    //         // Redireciona diretamente para a rota física de confirmação que exibe a tela verde
    //         return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
    //             ->with('success', 'Caixa encerrado administrativamente sem movimento.');
    //     });
    // }
    // public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    // {
    //     // 1️⃣ Validação dos valores conferidos na gaveta do balcão
    //     $request->validate([
    //         'dinheiro'       => 'required|numeric|min:0',
    //         'pix'            => 'required|numeric|min:0',
    //         'carteira'       => 'required|numeric|min:0',
    //         'cartao_debito'  => 'required|numeric|min:0',
    //         'cartao_credito' => 'required|numeric|min:0',
    //     ]);

    //     $userId = auth()->id();
    //     $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
    //     $valoresFisicos = $request->only($formas);

    //     return DB::transaction(function () use ($request, $caixa, $userId, $formas, $valoresFisicos) {
    //         $caixa->refresh();

    //         if ($caixa->status !== 'aberto' && $caixa->status !== 'inconsistente') {
    //             throw new \Exception('Este caixa já foi processado ou fechado por outra sessão.');
    //         }

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 2️⃣ APURAÇÃO DE VALORES (SISTEMA vs FÍSICO)
    //         |--------------------------------------------------------------------------
    //         */
    //         // Soma as vendas reais do PDV por forma de pagamento gravadas na fita
    //         $vendasDoCaixa = DB::table('movimentacoes_caixa')
    //             ->where('caixa_id', $caixa->id)
    //             ->where('tipo', 'venda')
    //             ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
    //             ->groupBy('forma_pagamento')
    //             ->get()
    //             ->keyBy('forma_pagamento');

    //         $totalSistemaGeral = 0.00;
    //         $totalFisicoGeral = array_sum($valoresFisicos);

    //         // Calcula o total projetado geral do sistema (Fundo de troco + vendas em todas as formas)
    //         // Se houver sangrias ou despesas no seu fluxo, subtraia-as do total geral aqui.
    //         $totalSistemaGeral += (float) $caixa->fundo_troco;
    //         foreach ($formas as $f) {
    //             $totalSistemaGeral += (float) ($vendasDoCaixa->get($f)->total ?? 0.00);
    //         }

    //         $diferencaGeral = $totalFisicoGeral - $totalSistemaGeral;
    //         $statusAuditoria = abs($diferencaGeral) <= 0.01 ? 'concluida' : 'inconsistente';

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 3️⃣ GRAVAÇÃO DO CABEÇALHO (auditorias_caixa)
    //         |--------------------------------------------------------------------------
    //         */
    //         $codigoAuditoria = 'AUD-' . $caixa->id . '-' . now()->format('YmdHis');

    //         $auditoriaId = DB::table('auditorias_caixa')->insertGetId([
    //             'caixa_id'         => $caixa->id,
    //             'user_id'          => $userId,
    //             'codigo_auditoria' => $codigoAuditoria,
    //             'total_sistema'    => $totalSistemaGeral,
    //             'total_fisico'     => $totalFisicoGeral,
    //             'diferenca'        => $diferencaGeral,
    //             'status'           => $statusAuditoria, // 'concluida' ou 'inconsistente' conforme o ENUM
    //             'observacao'       => 'Fechamento com auditoria detalhada automatizada',
    //             'data_auditoria'   => now(),
    //         ]);

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 4️⃣ GRAVAÇÃO DOS DETALHES POR FORMA (auditoria_detalhes)
    //         |--------------------------------------------------------------------------
    //         */
    //         foreach ($formas as $forma) {
    //             // Pega o valor que o sistema registrou especificamente para esta forma de pagamento
    //             $sistemaForma = (float) ($vendasDoCaixa->get($forma)->total ?? 0.00);

    //             // Ajuste específico do dinheiro vivo: o caixa herda o fundo de troco inicial
    //             if ($forma === 'dinheiro') {
    //                 $sistemaForma += (float) $caixa->fundo_troco;
    //             }

    //             $fisicoForma = (float) ($valoresFisicos[$forma] ?? 0.00);
    //             $diferencaForma = $fisicoForma - $sistemaForma;
    //             $statusForma = abs($diferencaForma) <= 0.01 ? 'correto' : 'divergente';

    //             DB::table('auditoria_detalhes')->insert([
    //                 'auditoria_id'    => $auditoriaId, // Chave estrangeira obrigatória
    //                 'forma_pagamento' => $forma,
    //                 'total_sistema'   => $sistemaForma,
    //                 'total_fisico'    => $fisicoForma,
    //                 'diferenca'       => $diferencaForma,
    //                 'status'          => $statusForma, // 'correto' ou 'divergente' conforme o ENUM
    //                 'created_at'      => now(),
    //                 'updated_at'      => now(),
    //             ]);

    //             // 5️⃣ REGISTRO HISTÓRICO NA FITA DE MOVIMENTAÇÕES (Mantém o padrão do Caixa 296)
    //             DB::table('movimentacoes_caixa')->insert([
    //                 'caixa_id'          => $caixa->id,
    //                 'auditoria_id'      => $auditoriaId, // Passa a vincular a movimentação à auditoria gerada!
    //                 'user_id'           => $userId,
    //                 'tipo'              => 'entrada_manual',
    //                 'forma_pagamento'   => $forma,
    //                 'valor'             => $fisicoForma,
    //                 'valor_auditado'    => 0.00,
    //                 'bandeira'          => null,
    //                 'origem_id'         => null,
    //                 'observacao'        => "Valor físico informado no fechamento",
    //                 'data_movimentacao' => now(),
    //                 'created_at'        => now(),
    //                 'updated_at'        => now(),
    //             ]);
    //         }

    //         /*

    //         |--------------------------------------------------------------------------
    //         | 6️⃣ ATUALIZAÇÃO DEFINITIVA DO STATUS DO CAIXA
    //         |--------------------------------------------------------------------------
    //         */
    //         $novoStatusCaixa = abs($diferencaGeral) <= 0.01 ? 'fechado' : 'inconsistente';

    //         $caixa->update([
    //             'valor_fechamento' => $totalFisicoGeral,
    //             'status'           => $novoStatusCaixa,
    //             'data_fechamento'  => now(),
    //             'fechado_por'      => $userId,
    //         ]);
    //     });

    //     // 🌟 Redireciona para a rota física que decide se mostra a tela verde ou a de inconsistência
    //     return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
    //         ->with('success', 'Conferência de caixa processada!');
    // }

    private function fecharSemMovimento(Request $request, Caixa $caixa)
    {
        $request->validate(['motivo_fechamento' => 'required|string|max:255']);
        $userId = auth()->id();
        $motivo = $request->input('motivo_fechamento');

        return DB::transaction(function () use ($caixa, $userId, $motivo) {
            $caixa->refresh();
            if ($caixa->status !== 'aberto') { 
                throw new \Exception('Caixa já modificado por outro terminal.'); 
            }

            // 1. Gera cabeçalho da auditoria (Troco esperado == Troco recebido)
            $auditoriaId = $this->criarAuditoriaCabecalho(
                $caixa->id, 
                $userId, 
                (float)$caixa->fundo_troco, 
                (float)$caixa->fundo_troco, 
                "Fechamento administrativo sem movimento: {$motivo}"
            );

            // 2. Prepara matriz zerada (Apenas dinheiro herda o troco)
            $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
            $valoresSistemas = array_fill_keys($formas, 0.00);
            $valoresSistemas['dinheiro'] = (float)$caixa->fundo_troco;
            
            // Executa o método privado compartilhado
            $this->salvarAuditoriaDetalhes($auditoriaId, $formas, $valoresSistemas, $valoresSistemas);

            // 3. Grava histórico na fita do caixa aberto
            $this->salvarMovimentacaoHistorica($caixa->id, $auditoriaId, $userId, 'fechamento_sem_movimento', 'fechamento', (float)$caixa->fundo_troco, $motivo);

            // 4. Atualiza o cabeçalho principal
            $caixa->update([
                'valor_fechamento' => (float)$caixa->fundo_troco, 
                'status' => 'fechado_sem_movimento', 
                'data_fechamento' => now(), 
                'fechado_por' => $userId, 
                'observacao' => $motivo
            ]);

            return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
                ->with('success', 'Caixa encerrado administrativamente.');
        });
    }

    public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    {
        $request->validate([
            'dinheiro'       => 'required|numeric|min:0', 
            'pix'            => 'required|numeric|min:0', 
            'carteira'       => 'required|numeric|min:0',
            'cartao_debito'  => 'required|numeric|min:0', 
            'cartao_credito' => 'required|numeric|min:0'
        ]);

        $userId = auth()->id();
        $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
        $valoresFisicos = $request->only($formas);

        return DB::transaction(function () use ($caixa, $userId, $formas, $valoresFisicos) {
            $caixa->refresh();
            if ($caixa->status !== 'aberto' && $caixa->status !== 'inconsistente') { 
                throw new \Exception('Caixa indisponível para processamento.'); 
            }

            // Agrupa o faturamento real do PDV lançado durante o dia
            $vendasDoCaixa = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->where('tipo', 'venda')
                ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
                ->groupBy('forma_pagamento')
                ->get()
                ->keyBy('forma_pagamento');

            $valoresSistemas = [];
            $totalSistemaGeral = 0.00;
            $totalFisicoGeral = array_sum($valoresFisicos);

            foreach ($formas as $forma) {
                $vendaForma = (float)($vendasDoCaixa->get($forma)->total ?? 0.00);
                
                // O dinheiro do sistema soma as vendas mais o fundo de troco inicial
                $valoresSistemas[$forma] = ($forma === 'dinheiro') ? ($vendaForma + (float)$caixa->fundo_troco) : $vendaForma;
                $totalSistemaGeral += $valoresSistemas[$forma];
            }

            $diferencaGeral = $totalFisicoGeral - $totalSistemaGeral;

            // 1. Salva cabeçalho unificado
            $auditoriaId = $this->criarAuditoriaCabecalho($caixa->id, $userId, $totalSistemaGeral, $totalFisicoGeral, 'Fechamento comercial auditado');
            
            // 2. Salva detalhes (Mapeamento corrigido)
            $this->salvarAuditoriaDetalhes($auditoriaId, $formas, $valoresSistemas, $valoresFisicos);

            // 3. Popula a fita de histórico (Entradas manuais do fechamento)
            foreach ($valoresFisicos as $forma => $valor) {
                $this->salvarMovimentacaoHistorica($caixa->id, $auditoriaId, $userId, 'entrada_manual', $forma, (float)$valor, "Valor físico informado no fechamento");
            }

            // 4. Consolida o status final do caixa
            $caixa->update([
                'valor_fechamento' => $totalFisicoGeral, 
                'status' => (abs($diferencaGeral) <= 0.01 ? 'fechado' : 'inconsistente'), 
                'data_fechamento' => now(), 
                'fechado_por' => $userId
            ]);

            return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
                ->with('success', 'Conferência de caixa processada.');
        });
    }

    private function criarAuditoriaCabecalho(int $caixaId, int $userId, float $sistema, float $fisico, string $obs): int
    {
        $diferenca = $fisico - $sistema;
        return DB::table('auditorias_caixa')->insertGetId([
            'caixa_id'         => $caixaId, 
            'user_id'          => $userId, 
            'codigo_auditoria' => 'AUD-' . $caixaId . '-' . now()->format('YmdHis'),
            'total_sistema'    => $sistema, 
            'total_fisico'     => $fisico, 
            'diferenca'        => $diferenca,
            'status'           => (abs($diferenca) <= 0.01 ? 'concluida' : 'inconsistente'), // ENUM auditorias_caixa
            'observacao'       => $obs, 
            'data_auditoria'   => now()
        ]);
    }

    private function salvarAuditoriaDetalhes(int $auditoriaId, array $formas, array $valoresSistemas, array $valoresFisicos): void
    {
        foreach ($formas as $forma) {
            $sistema = (float)($valoresSistemas[$forma] ?? 0.00);
            $fisico  = (float)($valoresFisicos[$forma] ?? 0.00);
            $diff    = $fisico - $sistema;

            DB::table('auditoria_detalhes')->insert([
                'auditoria_id'    => $auditoriaId, 
                'forma_pagamento' => $forma, 
                'total_sistema'   => $sistema,
                'total_fisico'    => $fisico, 
                'diferenca'       => $diff, 
                'status'          => (abs($diff) <= 0.01 ? 'correto' : 'divergente'), // 🌟 ENUM auditoria_detalhes corrigido
                'created_at'      => now(), 
                'updated_at'      => now()
            ]);
        }
    }

    private function salvarMovimentacaoHistorica(int $caixaId, int $auditoriaId, int $userId, string $tipo, string $forma, float $valor, string $obs): void
    {
        DB::table('movimentacoes_caixa')->insert([
            'caixa_id'          => $caixaId, 
            'auditoria_id'      => $auditoriaId, 
            'user_id'           => $userId, 
            'tipo'              => $tipo,
            'forma_pagamento'   => $forma, 
            'valor'             => $valor, 
            'valor_auditado'    => 0.00, 
            'bandeira'          => null,
            'origem_id'         => null, 
            'observacao'        => $obs, 
            'data_movimentacao' => now(), 
            'created_at'        => now(), 
            'updated_at'        => now()
        ]);
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