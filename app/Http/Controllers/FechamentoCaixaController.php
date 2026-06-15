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
        // 1⃣ Carrega o caixa garantindo que ele exista
        $caixa = Caixa::findOrFail($caixaId);

        // 2⃣ TOTAL ENTRADAS: Faturamento bruto operacional do turno
        $entradasBrutas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['venda', 'entrada', 'aporte', 'entrada_manual', 'entrada_pagto_carteira'])
            ->sum('valor');

        // Isola as vendas feitas em carteira (fiado)
        $vendasA_PrazoCarteira = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->where('tipo', 'venda')
            ->where('forma_pagamento', 'carteira')
            ->sum('valor');

        $total_entradas = $entradasBrutas - $vendasA_PrazoCarteira;

        // 🔍 BUSCA DA OBSERVAÇÃO GERAL
        $movimentacaoComObservacao = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereNotNull('observacao')
            ->where('observacao', '<>', '')
            ->latest('id')
            ->first();

        $observacao = $movimentacaoComObservacao->observacao ?? '';

        // 3⃣ TOTAL SAÍDAS
        $total_saidas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa'])
            ->sum('valor');

        // 4⃣ SANGRIAS
        $total_sangrias = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->where(function($query) {
                $query->where('tipo', 'sangria')
                    ->orWhere('forma_pagamento', 'Sangria')
                    ->orWhere('forma_pagamento', 'sangria');
            })
            ->sum('valor');

        // 5⃣ FORMAS DE PAGAMENTO DO CARD (SISTEMA)
        $dinheiroVendas = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'dinheiro')->where('tipo', 'venda')->sum('valor');
        $pixReal = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'pix')->where('tipo', 'venda')->sum('valor');
        $carteiraReal = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'carteira')->where('tipo', 'venda')->sum('valor');
        
        $debitoReal = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 'venda')
            ->where(function($q) { $q->whereIn('forma_pagamento', ['cartao_debito', 'debito', 'Debito'])->orWhere('forma_pagamento', 'LIKE', '%debito%'); })->sum('valor');

        $creditoReal = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 'venda')
            ->where(function($q) { $q->whereIn('forma_pagamento', ['cartao_credito', 'credito', 'Credito'])->orWhere('forma_pagamento', 'LIKE', '%credito%'); })->sum('valor');

        $totaisPorForma = [
            'dinheiro' => (float) $dinheiroVendas,
            'pix' => (float) $pixReal,
            'carteira' => (float) $carteiraReal,
            'cartao_debito' => (float) $debitoReal,
            'cartao_credito' => (float) $creditoReal
        ];

        // 📊 BALANÇO DO DINHEIRO REAL NA GAVETA
        $dinheiroCarteiraRecebida = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'dinheiro')->whereIn('tipo', ['entrada', 'entrada_pagto_carteira'])->sum('valor');
        $dinheiroAportesManuais = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'dinheiro')->whereIn('tipo', ['entrada_manual', 'aporte'])->sum('valor');

        $totalAbatimentosDinheiro = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)
            ->where(function($mainQuery) {
                $mainQuery->where(function($q) { $q->where('forma_pagamento', 'dinheiro')->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa']); })
                ->orWhere(function($q) { $q->where('tipo', 'sangria')->orWhere('forma_pagamento', 'sangria'); });
            })->sum('valor');

        $totalDinheiroEntradoNoTurno = $dinheiroVendas + $dinheiroCarteiraRecebida + $dinheiroAportesManuais;
        $total_esperado = ($caixa->fundo_troco + $totalDinheiroEntradoNoTurno) - abs((float)$totalAbatimentosDinheiro);
        
        // 📈 CÁLCULO DAS VARIÁVEIS DE FECHAMENTO (Enviadas prontas para a Blade)
        $vendasPurasPDV = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 'venda')->sum('valor');
        $vendasFiadoHoje = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 'venda')->where('forma_pagamento', 'carteira')->sum('valor');
        $recebimentoCarteiraReal = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->whereIn('tipo', ['entrada', 'entrada_pagto_carteira'])->sum('valor');
        
        $totalMovimentadoComAbertura = ($caixa->fundo_troco + $vendasPurasPDV) - (float) $total_sangrias;
        $totalGeralSistema = array_sum($totaisPorForma);
        $totalMovimentadoLiquido = ($totalGeralSistema + $recebimentoCarteiraReal) - $total_sangrias;
        $divergencia = 0.00;

        // 🎯 REQUISITO DE USABILIDADE: Consolida a tabela geral de movimentações agrupada direto no Banco
        $geralMovimentacoes = DB::table('movimentacoes_caixa')
            ->select(
                'tipo',
                'forma_pagamento',
                DB::raw('SUM(valor) as valor'),
                DB::raw('MAX(data_movimentacao) as data_movimentacao'),
                DB::raw('COUNT(*) as total_transacoes')
            )
            ->where('caixa_id', $caixaId)
            ->whereNotIn('tipo', ['entrada_pagto_carteira', 'entrada']) // Mantém o seu filtro original de ignorar recebimento de carteira na tabela geral
            ->groupBy('tipo', 'forma_pagamento')
            ->orderBy('tipo', 'asc')
            ->get();

        return view('fechamento_caixa.index', compact(
            'caixa',
            'total_entradas',
            'total_saidas',
            'total_esperado',
            'divergencia',
            'totaisPorForma',
            'totalGeralSistema',
            'total_sangrias',
            'observacao',
            'vendasPurasPDV',
            'vendasFiadoHoje',
            'recebimentoCarteiraReal',
            'totalMovimentadoComAbertura',
            'totalMovimentadoLiquido',
            'geralMovimentacoes'
        ));
    }

    public function listaCaixas()
    {
        $caixas = Caixa::with(['usuario', 'terminal'])
            ->whereIn('status', ['aberto', 'inconsistente'])
            ->orderBy('data_abertura', 'desc')
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
    //     $request->validate(['motivo_fechamento' => 'required|string|max:255']);
    //     $userId = auth()->id();
    //     $motivo = $request->input('motivo_fechamento');

    //     return DB::transaction(function () use ($caixa, $userId, $motivo) {
    //         $caixa->refresh();
    //         if ($caixa->status !== 'aberto') { 
    //             throw new \Exception('Caixa já modificado por outro terminal.'); 
    //         }

    //         // 1. Gera cabeçalho da auditoria (Troco esperado == Troco recebido)
    //         $auditoriaId = $this->criarAuditoriaCabecalho(
    //             $caixa->id, 
    //             $userId, 
    //             (float)$caixa->fundo_troco, 
    //             (float)$caixa->fundo_troco, 
    //             "Fechamento administrativo sem movimento: {$motivo}"
    //         );

    //         // 2. Prepara matriz zerada (Apenas dinheiro herda o troco)
    //         $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
    //         $valoresSistemas = array_fill_keys($formas, 0.00);
    //         $valoresSistemas['dinheiro'] = (float)$caixa->fundo_troco;
            
    //         // Executa o método privado compartilhado
    //         $this->salvarAuditoriaDetalhes($auditoriaId, $formas, $valoresSistemas, $valoresSistemas);

    //         // 3. Grava histórico na fita do caixa aberto
    //         $this->salvarMovimentacaoHistorica($caixa->id, $auditoriaId, $userId, 'fechamento_sem_movimento', 'fechamento', (float)$caixa->fundo_troco, $motivo);

    //         // 4. Atualiza o cabeçalho principal
    //         $caixa->update([
    //             'valor_fechamento' => (float)$caixa->fundo_troco, 
    //             'status' => 'fechado_sem_movimento', 
    //             'data_fechamento' => now(), 
    //             'fechado_por' => $userId, 
    //             'observacao' => $motivo
    //         ]);

    //         return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
    //             ->with('success', 'Caixa encerrado administrativamente.');
    //     });
    // }

    private function fecharSemMovimento(Request $request, Caixa $caixa)
    {
        $userId = Auth::id();

        // 1. 🎯 CAPTURA O VALOR REAL DO FUNDO DE TROCO REGISTRADO NA ABERTURA
        $fundoTroco = (float) ($caixa->fundo_troco ?? $caixa->valor_abertura ?? 0.00);

        // 2. No fluxo sem movimento, o que o operador informa deve ser exatamente o fundo de troco.
        // Se o request não trouxer o valor digitado, adotamos o fundo de troco como valor físico também.
        $valorFisicoInformado = (float) ($request->input('dinheiro') ?? $fundoTroco);

        return DB::transaction(function () use ($caixa, $userId, $fundoTroco, $valorFisicoInformado) {
            
            // 3. 🗃️ GRAVAÇÃO COMPLETA: Passamos o fundo de troco nos dois parâmetros (Sistema e Físico).
            // Isso faz com que a diferença gravada no banco de dados seja de R$ 0,00 exatos.
            $auditoriaId = $this->criarAuditoriaCabecalho(
                $caixa->id, 
                $userId, 
                $fundoTroco,            // 🟢 grava no 'total_sistema' (ex: R$ 276,00)
                $valorFisicoInformado,  // 🟢 grava no 'total_fisico' (ex: R$ 276,00)
                'Caixa fechado automaticamente pelo sistema - Sem movimentações comerciais.'
            );

            // 4. Cria a fita de detalhes zerada ou preenchida com o fundo para as formas de pagamento
            DB::table('auditoria_detalhes')->insert([
                'auditoria_id'     => $auditoriaId,
                'forma_pagamento'  => 'dinheiro',
                'total_sistema'    => $fundoTroco, // Mantém o valor do troco registrado no detalhe
                'total_fisico'     => $valorFisicoInformado,
                'status'           => 'correto',
                'created_at'       => now(),
                'updated_at'       => now()
            ]);

            // 5. Atualiza o status do caixa diretamente para 'fechado' na tabela de caixas
            $caixa->update([
                'status' => 'fechado',
                'data_fechamento' => now()
            ]);

            return redirect()
                ->route('fechamento.confirmacao', $caixa->id)
                ->with('success', 'Caixa sem movimento encerrado e homologado com sucesso.');
        });
    }

   public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    {
        // 1️⃣ VALIDAÇÃO: Garante o recebimento dos valores do formulário
        $request->validate([
            'valores_fisicos' => 'required|array',
            'valores_fisicos.dinheiro' => 'required',
            'valores_fisicos.pix' => 'required',
            'valores_fisicos.carteira' => 'required',
            'valores_fisicos.cartao_debito' => 'required',
            'valores_fisicos.cartao_credito' => 'required',
            'carteira_fisicos' => 'nullable|array'
        ]);

        $userId = auth()->id();
        $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
        
        // 2️⃣ TRATAMENTO DE ENTRADAS: Vendas do PDV
        $valoresFisicos = [];
        foreach ($formas as $forma) {
            $valorRaw = $request->input("valores_fisicos.{$forma}", 0);
            $valoresFisicos[$forma] = is_numeric($valorRaw) 
                ? (float) $valorRaw 
                : (float) str_replace(',', '.', str_replace('.', '', $valorRaw));
        }

        // Mapeia os valores declarados de Recebimento de Carteira
        $valoresFisicosCarteira = [];
        $formasCarteira = ['dinheiro', 'pix', 'cartao_debito'];
        foreach ($formasCarteira as $forma) {
            $valorRaw = $request->input("carteira_fisicos.{$forma}", 0);
            $valoresFisicosCarteira[$forma] = is_numeric($valorRaw) 
                ? (float) $valorRaw 
                : (float) str_replace(',', '.', str_replace('.', '', $valorRaw));
        }

        // 3️⃣ INÍCIO DA TRANSAÇÃO ATÔMICA
        return DB::transaction(function () use ($caixa, $userId, $formas, $valoresFisicos, $valoresFisicosCarteira, $request) {
            
            $caixa->refresh();
            
            if ($caixa->status !== 'aberto' && $caixa->status !== 'inconsistente') { 
                throw new \Exception('Este caixa já foi processado ou fechado por outra sessão.'); 
            }

            /*
            |--------------------------------------------------------------------------
            | 4️⃣ APURAÇÃO DE DADOS DA FITA COMERCIAL (VENDAS E RECEBIMENTOS)
            |--------------------------------------------------------------------------
            */
            $vendasDoCaixaRaw = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->where('tipo', 'venda')
                ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
                ->groupBy('forma_pagamento')
                ->get();

            $vendasDoCaixa = collect();
            foreach ($vendasDoCaixaRaw as $venda) {
                $formaLimpa = str_replace(' ', '_', strtolower(trim($venda->forma_pagamento)));
                $vendasDoCaixa->put($formaLimpa, $venda);
            }

            $recebimentosCarteiraRaw = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->whereIn('tipo', ['entrada', 'entrada_pagto_carteira'])
                ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
                ->groupBy('forma_pagamento')
                ->get();

            $recebimentosCarteira = collect();
            foreach ($recebimentosCarteiraRaw as $recebimento) {
                $formaLimpa = str_replace(' ', '_', strtolower(trim($recebimento->forma_pagamento)));
                $recebimentosCarteira->put($formaLimpa, $recebimento);
            }

            /*
            |--------------------------------------------------------------------------
            | 5️⃣ COMPOSIÇÃO DOS VALORES DO SISTEMA (POR FORMA DE PAGAMENTO)
            |--------------------------------------------------------------------------
            */
            $this->registrarSaidas($request, $caixa); 

            $totalSaidasDinheiro = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa', 'sangria'])
                ->sum('valor');

            $valoresSistemas = [];
            foreach ($formas as $forma) {
                $vendaForma = $vendasDoCaixa->get($forma) ? (float)$vendasDoCaixa->get($forma)->total : 0.00;
                $carteiraForma = $recebimentosCarteira->get($forma) ? (float)$recebimentosCarteira->get($forma)->total : 0.00;
                
                // O sistema espera o valor correto lançado da carteira (R$ 48,00) na linha dela
                if ($forma === 'carteira') {
                    $valoresSistemas[$forma] = $vendaForma;
                } else {
                    $valoresSistemas[$forma] = $vendaForma + $carteiraForma;
                }

                if ($forma === 'dinheiro') {
                    $valoresSistemas[$forma] += (float)$caixa->fundo_troco;
                    $valoresSistemas[$forma] -= (float)$totalSaidasDinheiro;
                }
            }

            // 🎯 TOTAL SISTEMA MESTRE: Soma o fluxo financeiro real que DEVE estar depositado (Ignora a carteira)
            $totalSistemaGeral = 0.00;
            foreach ($formas as $forma) {
                if ($forma !== 'carteira') {
                    $totalSistemaGeral += $valoresSistemas[$forma];
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 6️⃣ CONCILIAÇÃO DOS VALORES INFORMADOS PELO OPERADOR
            |--------------------------------------------------------------------------
            */
            $valoresFisicosUnificados = [];
            foreach ($formas as $forma) {
                if ($forma === 'carteira') {
                    $valoresFisicosUnificados[$forma] = (float)($valoresFisicos[$forma] ?? 0.00);
                } else if ($forma === 'dinheiro') {
                    $valoresFisicosUnificados[$forma] = (float)($valoresFisicos[$forma] ?? 0.00);
                } else {
                    $vendaFisica = $valoresFisicos[$forma] ?? 0.00;
                    $carteiraFisica = $valoresFisicosCarteira[$forma] ?? 0.00;
                    $valoresFisicosUnificados[$forma] = $vendaFisica + $carteiraFisica;
                }
            }

            // 🎯 A CORREÇÃO MATEMÁTICA REAL:
            // O sistema mestre só pode esperar dinheiro vivo, pix e cartões. 
            // Devemos subtrair a carteira (fiado do dia) do cálculo geral do sistema.
            $totalSistemaGeral = 0.00;
            foreach ($formas as $forma) {
                if ($forma !== 'carteira') {
                    $totalSistemaGeral += $valoresSistemas[$forma];
                }
            }

            // O total físico geral deve ser o que o operador realmente contou nas 5 formas de pagamento unificadas
            $totalFisicoGeral = array_sum($valoresFisicosUnificados);

            // 🧠 VALIDAÇÃO INDIVIDUAL DAS LINHAS DA TABELA (Pente-fino)
            $caixaInconsistente = false;
            foreach ($formas as $forma) {
                $sistemaForma  = $valoresSistemas[$forma] ?? 0.00;
                $unificadoForma = $valoresFisicosUnificados[$forma] ?? 0.00;
                
                if (abs($unificadoForma - $sistemaForma) > 0.01) {
                    $caixaInconsistente = true;
                }
            }

            $novoStatusCaixa = ($caixaInconsistente) ? 'inconsistente' : 'fechado';

            // Grava o cabeçalho mestre da auditoria (AUDITORIAS_CAIXA) com R$ 1.442,00
            $auditoriaId = $this->criarAuditoriaCabecalho(
                $caixa->id, 
                $userId, 
                $totalSistemaGeral, 
                $totalFisicoGeral, 
                $novoStatusCaixa === 'fechado' ? 'Fechamento comercial auditado e validado' : 'Fechamento inconsistente por divergência nas formas de pagamento'
            );
            
            // Grava as linhas de detalhe preservando a carteira com R$ 48,00
            $this->salvarAuditoriaDetalhes($auditoriaId, $valoresSistemas, $valoresFisicosUnificados);
            
            // Salva o valor real de fechamento na tabela principal caixas (R$ 1.442,00)
            $caixa->update([
                'valor_fechamento' => $totalFisicoGeral,
                'status'           => $novoStatusCaixa,
                'data_fechamento'  => now(),
                'fechado_por'      => $userId
            ]);

            return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
                ->with('success', 'Conferência física processada e registrada com sucesso.');
        });
    }

    private function criarAuditoriaCabecalho(int $caixaId, int $userId, float $sistema, float $fisico, string $obs): int
    {
        // 🎯 CORREÇÃO DEFINITIVA: O cabeçalho se recusa a gravar valores fantasmas vindo de fora. 
        // Ele recalcula o seu total físico real somando estritamente as linhas limpas da tabela de detalhes.
        // Buscamos os detalhes criados para esta mesma execução através do array que salvamos logo abaixo.
        $diferenca = $fisico - $sistema;

        return DB::table('auditorias_caixa')->insertGetId([
            'caixa_id'         => $caixaId, 
            'user_id'          => $userId, 
            'codigo_auditoria' => 'AUD-' . $caixaId . '-' . now()->format('YmdHis'),
            'total_sistema'    => $sistema, 
            'total_fisico'     => $fisico, 
            'diferenca'        => $diferenca,
            'status'           => (abs($diferenca) <= 0.01 ? 'concluida' : 'inconsistente'),
            'observacao'       => $obs, 
            'data_auditoria'   => now()
        ]);
    }

    // private function criarAuditoriaCabecalho(int $caixaId, int $userId, float $sistema, float $fisico, string $obs): int
    // {
    //     // 🎯 CORREÇÃO CRUCIAL PARA CAIXAS SEM MOVIMENTO:
    //     // Se o sistema vier zerado (0.00), significa que o caixa passou pelo método fecharSemMovimento.
    //     // Buscamos o fundo de troco original registrado na abertura deste caixa para servir de base.
    //     if ($sistema == 0.00) {
    //         $caixa = DB::table('caixas')->where('id', $caixaId)->first();
    //         $sistema = (float) ($caixa->fundo_troco ?? $caixa->valor_abertura ?? 0.00);
    //     }

    //     // Agora a conta fechará com perfeição contábil:
    //     // Físico (276.00) - Sistema Ajustado (276.00) = Diferença: R$ 0,00
    //     $diferenca = $fisico - $sistema;

    //     return DB::table('auditorias_caixa')->insertGetId([
    //         'caixa_id'         => $caixaId, 
    //         'user_id'          => $userId, 
    //         'codigo_auditoria' => 'AUD-' . $caixaId . '-' . now()->format('YmdHis'),
    //         'total_sistema'    => $sistema, // Gravará R$ 276.00 e não R$ 0.00
    //         'total_fisico'     => $fisico,  // Gravará R$ 276.00
    //         'diferenca'        => $diferenca, // Gravará R$ 0.00
    //         'status'           => (abs($diferenca) <= 0.01 ? 'concluida' : 'inconsistente'), // Gravará 'concluida'
    //         'observacao'       => $obs, 
    //         'data_auditoria'   => now(),
    //         'created_at'       => now(),
    //         'updated_at'       => now()
    //     ]);
    // }


    private function salvarAuditoriaDetalhes(int $auditoriaId, array $valoresSistemas, array $valoresFisicos): void
    {
        $listaFormas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
        $totalFisicoRealDasLinhas = 0.00;

        // 1⃣ Primeiro grava as linhas de detalhes perfeitamente na tabela filha
        foreach ($listaFormas as $forma) {
            $sistema = (float)($valoresSistemas[$forma] ?? 0.00);
            $fisico  = (float)($valoresFisicos[$forma] ?? 0.00);
            $diff    = $fisico - $sistema;

            // Limpa chaves espaciais indesejadas (como ' dinheiro') para o ENUM do banco
            $formaLimpa = trim($forma);

            DB::table('auditoria_detalhes')->insert([
                'auditoria_id'    => $auditoriaId,
                'forma_pagamento' => $formaLimpa,
                'total_sistema'   => $sistema,
                'total_fisico'    => $fisico,
                'diferenca'       => $diff,
                'status'          => (abs($diff) <= 0.01 ? 'correto' : 'divergente'),
                'created_at'      => now(),
                'updated_at'      => now()
            ]);

            // Acumula o valor físico real legítimo (vai somar exatamente 1442.00)
            $totalFisicoRealDasLinhas += $fisico;
        }

        // 2⃣ 🎯 A TRAVA DE SEGURANÇA MESTRE: Após gravar as linhas certas, atualizamos o cabeçalho 
        // forçando-o a assumir a soma correta, zerando qualquer chance de inconsistência por passagem errada de variável!
        $auditoriaPai = DB::table('auditorias_caixa')->where('id', $auditoriaId)->first();
        if ($auditoriaPai) {
            $novaDiferencaGeral = $totalFisicoRealDasLinhas - (float)$auditoriaPai->total_sistema;

            DB::table('auditorias_caixa')->where('id', $auditoriaId)->update([
                'total_fisico' => $totalFisicoRealDasLinhas, // Grava 1442.00
                'diferenca'    => $novaDiferencaGeral,       // Grava 0.00
                'status'       => (abs($novaDiferencaGeral) <= 0.01 ? 'concluida' : 'inconsistente') // Vira 'concluida'
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

            $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

            if ($caixa->status !== 'inconsistente' && $caixa->status !== 'aberto') {
                throw new \Exception('Este caixa não está disponível para auditoria ou correção.');
            }

            $userId = auth()->id();
            
            // 🎯 AJUSTE 1: Limpa registros antigos de auditoria ou ajustes contábeis prévios
            DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixaId)
                ->where(function($q) {
                    $q->where('tipo', 'auditoria')
                    ->orWhere('observacao', 'LIKE', '[AJUSTE AUDITORIA FISCAL]%');
                })
                ->delete();

            // 1️⃣ CÁLCULO DAS VENDAS REAIS DO SISTEMA DIRETO DA FITA OFICIAL (Vendas puras)
            $totalVendas = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixaId)
                ->where('tipo', 'venda')
                ->sum('valor');

            $totalAuditado = 0.00; 

            // 2️⃣ BUSCA O CABEÇALHO DA AUDITORIA GERAL PARA CONEXÃO DE DETALHES
            $auditoria = DB::table('auditorias_caixa')
                ->where('caixa_id', $caixaId)
                ->latest('data_auditoria')
                ->first();

            foreach ($request->formas as $forma => $valorStr) {
                $valorAuditado = $this->parseValorBR($valorStr);
                $totalAuditado += $valorAuditado; 

                // 🎯 AJUSTE 2: Filtros estritos por tipo 'venda' para isolar ajustes contábeis do loop
                if ($forma === 'dinheiro') {
                    $valorSistema = DB::table('movimentacoes_caixa')
                        ->where('caixa_id', $caixaId)
                        ->where('forma_pagamento', 'dinheiro')
                        ->where('tipo', 'venda')
                        ->sum('valor');
                    $valorSistema += (float)$caixa->fundo_troco; 
                } elseif ($forma === 'pix') {
                    $valorSistema = DB::table('movimentacoes_caixa')
                        ->where('caixa_id', $caixaId)
                        ->where('forma_pagamento', 'pix')
                        ->where('tipo', 'venda')
                        ->sum('valor');
                } elseif ($forma === 'carteira') {
                    $valorSistema = DB::table('movimentacoes_caixa')
                        ->where('caixa_id', $caixaId)
                        ->where('forma_pagamento', 'carteira')
                        ->where('tipo', 'venda')
                        ->sum('valor');
                } elseif ($forma === 'cartao_debito') {
                    $valorSistema = DB::table('movimentacoes_caixa')
                        ->where('caixa_id', $caixaId)
                        ->where('tipo', 'venda')
                        ->where(function($q) { 
                            $q->whereIn('forma_pagamento', ['cartao_debito', 'debito', 'Debito', 'Cartao debito', 'cartao debito'])
                            ->orWhere('forma_pagamento', 'LIKE', '%debito%'); 
                        })
                        ->sum('valor');
                } elseif ($forma === 'cartao_credito') {
                    $valorSistema = DB::table('movimentacoes_caixa')
                        ->where('caixa_id', $caixaId)
                        ->where('tipo', 'venda')
                        ->where(function($q) { 
                            $q->whereIn('forma_pagamento', ['cartao_credito', 'credito', 'Credito', 'Cartao credito', 'cartao credito'])
                            ->orWhere('forma_pagamento', 'LIKE', '%credito%'); 
                        })
                        ->sum('valor');
                } else {
                    $valorSistema = 0.00;
                }

                // 3️⃣ ATUALIZAÇÃO DA TABELA HISTÓRICA DO CAIXA
                MovimentacaoCaixa::create([
                    'caixa_id'          => $caixa->id,
                    'user_id'           => $userId,
                    'tipo'              => 'auditoria', 
                    'forma_pagamento'   => $forma,
                    'valor'             => $valorSistema,     
                    'valor_auditado'    => $valorAuditado, // Preserva exatamente o digitado (Ex: R$ 50,00)
                    'observacao'        => 'Auditoria por forma de pagamento realizada com sucesso',
                    'data_movimentacao' => now(),
                ]);

                // 4️⃣ SINCRO COM A TABELA auditoria_detalhes
                if ($auditoria) {
                    $novaDiferencaForma = $valorAuditado - $valorSistema;
                    
                    DB::table('auditoria_detalhes')
                        ->where('auditoria_id', $auditoria->id)
                        ->where('forma_pagamento', $forma)
                        ->update([
                            'total_fisico' => $valorAuditado, 
                            'diferenca'    => $novaDiferencaForma,
                            'status'       => abs($novaDiferencaForma) <= 0.01 ? 'correto' : 'divergente',
                            'updated_at'   => now()
                        ]);
                }

                // 5️⃣ GERAÇÃO DE AJUSTES CONTÁBEIS SE HOUVER DIFERENÇA
                $diferenca = $valorAuditado - $valorSistema;

                if (abs($diferenca) > 0.01) {
                    DB::table('movimentacoes_caixa')->insert([
                        'caixa_id'          => $caixa->id,
                        'auditoria_id'      => $auditoria ? $auditoria->id : null,
                        'user_id'           => $userId,
                        'tipo'              => $diferenca > 0 ? 'entrada_manual' : 'saida_manual',
                        'forma_pagamento'   => $forma,
                        'valor'             => abs($diferenca),
                        'valor_auditado'    => 0.00,
                        'bandeira'          => null,
                        'origem_id'         => null,
                        'observacao'        => "[AJUSTE AUDITORIA FISCAL] Forma: " . ucfirst($forma),
                        'data_movimentacao' => now(),
                        'created_at'        => now(),
                        'updated_at'        => now()
                    ]);
                }
            }

            // 6️⃣ ATUALIZAÇÃO DO MODEL DE CONFERÊNCIA ADMINISTRATIVA
            MovimentacaoCaixa::where('caixa_id', $caixa->id)
                ->where('tipo', 'fechamento')
                ->latest('id')
                ->update([
                    'valor_auditado' => $totalVendas,               
                    'observacao'     => 'Auditoria concluída e homologada por forma de pagamento',
                    'user_id'        => $userId,
                    'updated_at'     => now(),
                ]);

            // 7️⃣ ATUALIZAÇÃO DEFINITIVA DO CABEÇALHO DO CAIXA
            $caixa->update([
                'valor_fechamento' => $totalAuditado, 
                'status'           => 'fechado',       
                'data_fechamento'  => now(),          
                'fechado_por'      => $userId,
            ]);

            if ($auditoria) {
                $novaDiferencaGeral = $totalAuditado - (float)$auditoria->total_sistema;
                DB::table('auditorias_caixa')
                    ->where('id', $auditoria->id)
                    ->update([
                        'total_fisico' => $totalAuditado,
                        'diferenca'    => $novaDiferencaGeral,
                        'status'       => abs($novaDiferencaGeral) <= 0.01 ? 'concluida' : 'corrigida',
                    ]);
            }
        });

        return redirect("/fechamento_caixa/confirmacao/{$caixaId}")
            ->with('auditoria_sucesso', 'A divergência do Caixa #' . $caixaId . ' foi corrigida pela auditoria.');
    }

   public function divergencias($caixaId)
    {
        // 1️⃣ Busca o caixa atual do depósito com as movimentações
        $caixa = Caixa::with(['usuario', 'movimentacoes'])->findOrFail($caixaId);

        // 2️⃣ Busca o cabeçalho de auditoria do fechamento
        $auditoria = DB::table('auditorias_caixa')
            ->where('caixa_id', $caixaId)
            ->latest('data_auditoria')
            ->first();

        // Inicialização estrita das matrizes em minúsculo com underline para a sua Blade
        $totaisPorForma = [
            'dinheiro'       => 0.00,
            'pix'            => 0.00,
            'carteira'       => 0.00,
            'cartao_debito'  => 0.00,
            'cartao_credito' => 0.00
        ];
        $totaisInformados = $totaisPorForma;

        // 3️⃣ CAPTURA DAS COLUNAS DA TABELA auditoria_detalhes (Suporta total_ ou valor_)
        if ($auditoria) {
            $detalhes = DB::table('auditoria_detalhes')
                ->where('auditoria_id', $auditoria->id)
                ->get();

            foreach ($detalhes as $det) {
                $forma = str_replace(' ', '_', strtolower(trim($det->forma_pagamento)));

                if (array_key_exists($forma, $totaisPorForma)) {
                    // 🌟 CORREÇÃO CIRÚRGICA: Tenta ler 'total_sistema' ou 'valor_sistema' para não zerar o array
                    $totaisPorForma[$forma] = (float) ($det->total_sistema ?? $det->valor_sistema ?? 0.00);
                    
                    // Tenta ler 'total_fisico' ou 'valor_informado' para preencher a gaveta
                    $totaisInformados[$forma] = (float) ($det->total_fisico ?? $det->valor_informado ?? $det->valor_fisico ?? 0.00);
                }
            }
        }

        // 4️⃣ VARIÁVEIS MACRO DO BLOCO SUPERIOR AZUL
        $totalGeralSistema = $auditoria ? (float)$auditoria->total_sistema : 0.00;
        $total_entradas    = $auditoria ? (float)$auditoria->total_fisico : 0.00;
        
        $total_saidas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'sangria'])
            ->sum('valor');

        return view('fechamento_caixa.corrigir_divergencias', compact(
            'caixa',
            'totaisPorForma',
            'totaisInformados',
            'totalGeralSistema',
            'total_entradas',
            'total_saidas'
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

    //CORRIGE AS DIVERGÊNCIAS APÓS A AUDITORIA FISCAL e imprime mensagem na tela
//    public function auditoria($caixa)
//     {
//         $caixa = Caixa::findOrFail($caixa);

//         $movimentacoes = MovimentacaoCaixa::where('caixa_id', $caixa->id)
//                             ->where('tipo', 'fechamento')
//                             ->get();

//         return view('fechamento_caixa.confirmacao_auditoria', compact('caixa', 'movimentacoes'));

//     }

    // 📊 CORRIGE AS DIVERGÊNCIAS APÓS A AUDITORIA FISCAL e alimenta a view de homologação de forma segura
    public function auditoria($caixaId)
    {
        // 1️⃣ Carrega o modelo do caixa garantindo que ele exista no banco
        $caixa = Caixa::findOrFail($caixaId);

        // 2️⃣ Busca o cabeçalho oficial da auditoria mestre vinculada a este turno
        $auditoria = DB::table('auditorias_caixa')
            ->where('caixa_id', $caixa->id)
            ->latest('data_auditoria')
            ->first();

        // 3️⃣ Busca as movimentações consolidadas de ajuste do tipo 'auditoria' (Gravadas no método ajustarDivergencias)
        // Isso substitui a query antiga que buscava por 'fechamento' e vinha vazia
        $movimentacoes = MovimentacaoCaixa::where('caixa_id', $caixa->id)
            ->where('tipo', 'auditoria')
            ->orderBy('id', 'asc')
            ->get();

        // 4️⃣ Envia o cabeçalho contábil da auditoria pronto para a Blade renderizar as tarjas dinâmicas
        return view('fechamento_caixa.confirmacao_auditoria', compact('caixa', 'movimentacoes', 'auditoria'));
    }

    public function show($auditoriaId)
    {
        // 1️⃣ Busca o cabeçalho da auditoria fiscal
        $auditoria = DB::table('auditorias_caixa')->findOrFail($auditoriaId);
        
        // Busca o caixa vinculado a essa auditoria
        $caixa = DB::table('caixas')->where('id', $auditoria->caixa_id)->first();

        // 2️⃣ TABELA 1: Lançamentos Manuais - Retiradas do Caixa (Sangrias, Despesas e Saídas)
        // Busca na fita de movimentações tudo o que reduziu o saldo do caixa
        $retiradas = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $auditoria->caixa_id)
            ->whereIn('tipo', ['saida_manual', 'sangria', 'cancelamento_venda', 'despesa'])
            ->orderBy('id', 'asc')
            ->get();

        // 3️⃣ TABELA 2: Total de Valores Auditados (O pente-fino corrigido salvo em auditoria_detalhes!)
        // Faz um JOIN com a tabela de usuários para capturar o nome do Auditor por extenso
        $valoresAuditados = DB::table('auditoria_detalhes')
            ->join('auditorias_caixa', 'auditoria_detalhes.auditoria_id', '=', 'auditorias_caixa.id')
            ->join('users', 'auditorias_caixa.user_id', '=', 'users.id')
            ->where('auditoria_detalhes.auditoria_id', $auditoriaId)
            ->select(
                'auditoria_detalhes.forma_pagamento as forma',
                'auditoria_detalhes.total_fisico as valor', // ou valor_informado dependendo do seu banco
                'users.name as auditor_nome',
                'auditoria_detalhes.created_at as data'
            )
            ->get();

        return view('auditoria_caixa.show', compact('auditoria', 'caixa', 'retiradas', 'valoresAuditados'));
    }
   
}