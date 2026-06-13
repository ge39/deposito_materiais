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
  
//    public function index($caixaId)
//     {
//         // 1️⃣ Carrega o caixa garantindo que ele exista
//         $caixa = Caixa::findOrFail($caixaId);

//         // 2️⃣ TOTAL ENTRADAS: Faturamento líquido total (Vendas + Recebimentos de Carteira + Aportes) = R$ 625,00
//         $total_entradas = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->whereIn('tipo', ['venda', 'entrada', 'aporte', 'entrada_manual', 'entrada_pagto_carteira'])
//             ->sum('valor');

//         // 🔍 BUSCA DA OBSERVAÇÃO
//         $movimentacaoComObservacao = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->whereNotNull('observacao')
//             ->where('observacao', '<>', '')
//             ->latest('id')
//             ->first();

//         $observacao = $movimentacaoComObservacao->observacao ?? '';

//         // 3️⃣ TOTAL SAÍDAS
//         $total_saidas = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa'])
//             ->sum('valor');

//         // 4️⃣ SANGRIAS
//         $total_sangrias = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->where('tipo', 'sangria')
//             ->sum('valor');

//         // =========================================================================
//         // 5️⃣ FORMAS DE PAGAMENTO DO CARD (SISTEMA): ESTRITAMENTE VENDAS DO PDV
//         // =========================================================================
//         $dinheiroVendas = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->where('forma_pagamento', 'dinheiro')
//             ->where('tipo', 'venda') 
//             ->sum('valor');

//         $pixReal = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->where('forma_pagamento', 'pix')
//             ->where('tipo', 'venda') 
//             ->sum('valor');

//         $carteiraReal = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->where('forma_pagamento', 'carteira')
//             ->where('tipo', 'venda')
//             ->sum('valor');

//         $debitoReal = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->whereIn('forma_pagamento', ['cartao_debito', 'debito', 'Debito'])
//             ->where('tipo', 'venda') 
//             ->sum('valor');

//         $creditoReal = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->whereIn('forma_pagamento', ['cartao_credito', 'credito', 'Credito'])
//             ->where('tipo', 'venda') 
//             ->sum('valor');

//         // Alimenta a matriz que gera as linhas do card da direita (Total: R$ 336,00)
//         $totaisPorForma = [
//             'dinheiro'       => (float) $dinheiroVendas,
//             'pix'            => (float) $pixReal,
//             'carteira'       => (float) $carteiraReal,
//             'cartao_debito'  => (float) $debitoReal,
//             'cartao_credito' => (float) $creditoReal
//         ];

//         // =========================================================================
//         // 📊 APURAÇÃO DO DINHEIRO REAL DA GAVETA (VENDAS + RECEBIMENTOS)
//         // =========================================================================
//         $dinheiroCarteiraRecebida = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->where('forma_pagamento', 'dinheiro')
//             ->whereIn('tipo', ['entrada', 'entrada_pagto_carteira']) 
//             ->sum('valor');

//         $dinheiroAportesManuais = DB::table('movimentacoes_caixa')
//             ->where('caixa_id', $caixaId)
//             ->where('forma_pagamento', 'dinheiro')
//             ->whereIn('tipo', ['entrada_manual', 'aporte']) 
//             ->sum('valor');

//         // Soma todo o dinheiro físico que entrou por qualquer motivo no turno (138,00 + 253,00)
//         $totalDinheiroEntradoNoTurno = $dinheiroVendas + $dinheiroCarteiraRecebida + $dinheiroAportesManuais;

//         // 🔥 FIX DO CARD DO MEIO: Fundo de troco (150,00) + Todo Dinheiro Vivo (391,00) - Saídas = R$ 541,00 esperados
//         $total_esperado = ($caixa->fundo_troco + $totalDinheiroEntradoNoTurno) - ($total_saidas + $total_sangrias);
        
//         // Total Geral do faturamento de vendas diretas do card da direita (R$ 336,00)
//         $totalGeralSistema = array_sum($totaisPorForma); 
        
//         $divergencia = 0.00;

//         // Carrega o histórico para o rodapé limpando a linha de abertura
//         $caixa->setRelation('movimentacoes', $caixa->movimentacoes()
//             ->where('tipo', '!=', 'abertura') 
//             ->orderBy('id', 'asc')
//             ->get());

//         return view('fechamento_caixa.index', compact(
//             'caixa',
//             'total_entradas',
//             'total_saidas',
//             'total_esperado',
//             'divergencia',
//             'totaisPorForma',
//             'totalGeralSistema',
//             'total_sangrias',
//             'observacao',
//         ));
//     }

    public function index($caixaId)
{
    // 1️⃣ Carrega o caixa garantindo que ele exista
    $caixa = Caixa::findOrFail($caixaId);

    // 2️⃣ TOTAL ENTRADAS: Faturamento líquido total movimentado no turno
    // 🎯 AJUSTE: Filtra estritamente para ignorar a linha de abertura (ID 38327) no faturamento bruto
    $entradasBrutas = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->whereIn('tipo', ['venda', 'entrada', 'aporte', 'entrada_manual', 'entrada_pagto_carteira'])
        ->where('tipo', '!=', 'abertura')
        ->where('forma_pagamento', '!=', 'abertura')
        ->sum('valor'); 

    // Isola as vendas feitas em carteira (fiado) que NÃO geraram entrada de dinheiro real hoje
    $vendasA_PrazoCarteira = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('tipo', 'venda')
        ->where('forma_pagamento', 'carteira')
        ->sum('valor'); 

    // O faturamento real desconsidera a venda a prazo, pois o dinheiro ainda não entrou
    $total_entradas = $entradasBrutas - $vendasA_PrazoCarteira; 

    // 🔍 BUSCA DA OBSERVAÇÃO
    $movimentacaoComObservacao = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->whereNotNull('observacao')
        ->where('observacao', '<>', '')
        ->latest('id')
        ->first();

    $observacao = $movimentacaoComObservacao->observacao ?? '';

    // 3️⃣ TOTAL SAÍDAS (Geral para exibição no topo)
    $total_saidas = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa'])
        ->sum('valor');

    // 4️⃣ SANGRIAS (Global para exibição no topo)
    $total_sangrias = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where(function($query) {
            $query->where('tipo', 'sangria')
                ->orWhere('forma_pagamento', 'Sangria')
                ->orWhere('forma_pagamento', 'sangria');
        })
        ->sum('valor');

    // =========================================================================
    // 5️⃣ FORMAS DE PAGAMENTO DO CARD (SISTEMA): ESTRITAMENTE VENDAS DO PDV
    // =========================================================================
    $dinheiroVendas = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('forma_pagamento', 'dinheiro')
        ->where('tipo', 'venda') 
        ->sum('valor');

    $pixReal = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('forma_pagamento', 'pix')
        ->where('tipo', 'venda') 
        ->sum('valor');

    $carteiraReal = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('forma_pagamento', 'carteira')
        ->where('tipo', 'venda')
        ->sum('valor');

    $debitoReal = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('tipo', 'venda') 
        ->where(function($query) {
            $query->whereIn('forma_pagamento', ['cartao_debito', 'debito', 'Debito', 'Cartao debito', 'cartao debito'])
                ->orWhere('forma_pagamento', 'LIKE', '%debito%');
        })
        ->sum('valor');

    $creditoReal = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('tipo', 'venda') 
        ->where(function($query) {
            $query->whereIn('forma_pagamento', ['cartao_credito', 'credito', 'Credito', 'Cartao credito', 'cartao credito'])
                ->orWhere('forma_pagamento', 'LIKE', '%credito%');
        })
        ->sum('valor');

    $totaisPorForma = [
        'dinheiro'       => (float) $dinheiroVendas,
        'pix'            => (float) $pixReal,
        'carteira'       => (float) $carteiraReal,
        'cartao_debito'  => (float) $debitoReal,
        'cartao_credito' => (float) $creditoReal
    ];

    // =========================================================================
    // 📊 APURAÇÃO E ISOLAMENTO EXCLUSIVO DO DINHEIRO REAL DA GAVETA
    // =========================================================================
    $dinheiroCarteiraRecebida = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('forma_pagamento', 'dinheiro')
        ->whereIn('tipo', ['entrada', 'entrada_pagto_carteira']) 
        ->sum('valor'); 

    $dinheiroAportesManuais = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where('forma_pagamento', 'dinheiro')
        ->whereIn('tipo', ['entrada_manual', 'aporte']) 
        ->sum('valor');

    // Busca saídas operacionais na forma dinheiro + qualquer sangria gravada
    $totalAbatimentosDinheiro = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->where(function($query) {
            $query->where('forma_pagamento', 'dinheiro')
                ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa']);
        })
        ->orWhere(function($query) use ($caixaId) {
            $query->where('caixa_id', $caixaId)
                ->where(function($q) {
                    $q->where('tipo', 'sangria')
                        ->orWhere('forma_pagamento', 'Sangria')
                        ->orWhere('forma_pagamento', 'sangria');
                });
        })
        ->sum('valor'); 

    // Consolida as entradas em espécie
    $totalDinheiroEntradoNoTurno = $dinheiroVendas + $dinheiroCarteiraRecebida + $dinheiroAportesManuais;

    // Cálculo exato do saldo físico esperado na gaveta
    $total_esperado = ($caixa->fundo_troco + $totalDinheiroEntradoNoTurno) - abs((float)$totalAbatimentosDinheiro);
    
    // Total Bruto das vendas do PDV (Card Azul)
    $totalGeralSistema = array_sum($totaisPorForma); 
    
    // 🎯 VARIÁVEIS EXCLUSIVAS PARA O BLOCO LÍQUIDO DO RODAPÉ DA BLADE (R$ 1.646,00)
    $totalRecebimentosCarteiraExclusivo = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->whereIn('tipo', ['entrada', 'entrada_pagto_carteira'])
        ->sum('valor');

    $totalMovimentadoLiquido = ($totalGeralSistema + $totalRecebimentosCarteiraExclusivo) - $total_sangrias;

    $divergencia = 0.00;

    // 🎯 AJUSTE: Removemos o filtro de exclusão para que a linha de abertura apareça na tabela da Blade
    $caixa->setRelation('movimentacoes', $caixa->movimentacoes()
        ->orderBy('id', 'asc')
        ->get());

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
        'totalRecebimentosCarteiraExclusivo', // 👈 Enviado para a linha miúda da Blade
        'totalMovimentadoLiquido'             // 👈 Enviado para o visor verde grande
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

  
    // public function fecharMovimentoComAuditoria(Request $request, Caixa $caixa)
    // {
    //     // 1️⃣ VALIDAÇÃO: Garante o recebimento dos valores do formulário
    //     $request->validate([
    //         'valores_fisicos' => 'required|array',
    //         'valores_fisicos.dinheiro' => 'required',
    //         'valores_fisicos.pix' => 'required',
    //         'valores_fisicos.carteira' => 'required',
    //         'valores_fisicos.cartao_debito' => 'required',
    //         'valores_fisicos.cartao_credito' => 'required',
    //         'carteira_fisicos' => 'nullable|array'
    //     ]);

    //     $userId = auth()->id();
    //     $formas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];
        
    //     // 2️⃣ TRATAMENTO DE ENTRADAS: Vendas do PDV
    //     $valoresFisicos = [];
    //     foreach ($formas as $forma) {
    //         $valorRaw = $request->input("valores_fisicos.{$forma}", 0);
    //         $valoresFisicos[$forma] = is_numeric($valorRaw) 
    //             ? (float) $valorRaw 
    //             : (float) str_replace(',', '.', str_replace('.', '', $valorRaw));
    //     }

    //     // Mapeia os valores declarados de Recebimento de Carteira
    //     $valoresFisicosCarteira = [];
    //     $formasCarteira = ['dinheiro', 'pix', 'cartao_debito'];
    //     foreach ($formasCarteira as $forma) {
    //         $valorRaw = $request->input("carteira_fisicos.{$forma}", 0);
    //         $valoresFisicosCarteira[$forma] = is_numeric($valorRaw) 
    //             ? (float) $valorRaw 
    //             : (float) str_replace(',', '.', str_replace('.', '', $valorRaw));
    //     }

    //     // 3️⃣ INÍCIO DA TRANSAÇÃO ATÔMICA
    //     return DB::transaction(function () use ($caixa, $userId, $formas, $valoresFisicos, $valoresFisicosCarteira, $request) {
            
    //         $caixa->refresh();
            
    //         if ($caixa->status !== 'aberto' && $caixa->status !== 'inconsistente') { 
    //             throw new \Exception('Este caixa já foi processado ou fechado por outra sessão.'); 
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 4️⃣ APURAÇÃO DE DADOS DA FITA COMERCIAL (VENDAS E RECEBIMENTOS)
    //         |--------------------------------------------------------------------------
    //         */
    //         $vendasDoCaixaRaw = DB::table('movimentacoes_caixa')
    //             ->where('caixa_id', $caixa->id)
    //             ->where('tipo', 'venda')
    //             ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
    //             ->groupBy('forma_pagamento')
    //             ->get();

    //         $vendasDoCaixa = collect();
    //         foreach ($vendasDoCaixaRaw as $venda) {
    //             $formaLimpa = str_replace(' ', '_', strtolower(trim($venda->forma_pagamento)));
    //             $vendasDoCaixa->put($formaLimpa, $venda);
    //         }

    //         $recebimentosCarteiraRaw = DB::table('movimentacoes_caixa')
    //             ->where('caixa_id', $caixa->id)
    //             ->whereIn('tipo', ['entrada', 'entrada_pagto_carteira'])
    //             ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
    //             ->groupBy('forma_pagamento')
    //             ->get();

    //         $recebimentosCarteira = collect();
    //         foreach ($recebimentosCarteiraRaw as $recebimento) {
    //             $formaLimpa = str_replace(' ', '_', strtolower(trim($recebimento->forma_pagamento)));
    //             $recebimentosCarteira->put($formaLimpa, $recebimento);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 5️⃣ COMPOSIÇÃO DOS VALORES E DEDUÇÃO AUTOMÁTICA DE SANGRIAS
    //         |--------------------------------------------------------------------------
    //         */
    //         $this->registrarSaidas($request, $caixa); 

    //         $totalSaidasFiscais = DB::table('movimentacoes_caixa')
    //             ->where('caixa_id', $caixa->id)
    //             ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa', 'sangria'])
    //             ->sum('valor');

    //         $valoresSistemas = [];
    //         $totalSistemaGeral = 0.00;
    //         $totalFisicoGeral = array_sum($valoresFisicos) + array_sum($valoresFisicosCarteira);

    //         foreach ($formas as $forma) {
    //             if ($forma === 'carteira') {
    //                 $valoresSistemas[$forma] = 0.00; 
    //             } else {
    //                 $vendaForma = $vendasDoCaixa->get($forma) ? (float)$vendasDoCaixa->get($forma)->total : 0.00;
    //                 $carteiraForma = $recebimentosCarteira->get($forma) ? (float)$recebimentosCarteira->get($forma)->total : 0.00;
                    
    //                 $valoresSistemas[$forma] = $vendaForma + $carteiraForma;
    //             }

    //             if ($forma === 'dinheiro') {
    //                 $valoresSistemas[$forma] += (float)$caixa->fundo_troco;
    //                 $valoresSistemas[$forma] -= (float)$totalSaidasFiscais;
    //             }

    //             $totalSistemaGeral += $valoresSistemas[$forma];
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | 6️⃣ 💎 A REGRA DE CONCILIAÇÃO INDIVIDUAL (BLINDAGEM DA INVERSÃO)
    //         |--------------------------------------------------------------------------
    //         */
    //         // Consolida o que o operador digitou nas duas colunas do formulário
    //         $valoresFisicosUnificados = [];
    //         foreach ($formas as $forma) {
    //             if ($forma === 'carteira') {
    //                 $valoresFisicosUnificados[$forma] = 0.00;
    //             } else {
    //                 $vendaFisica = $valoresFisicos[$forma] ?? 0.00;
    //                 $carteiraFisica = $valoresFisicosCarteira[$forma] ?? 0.00;
    //                 $valoresFisicosUnificados[$forma] = $vendaFisica + $carteiraFisica;
    //             }
    //         }

    //         // 🧠 PENALIZAÇÃO POR LINHA: Varre cada meio de pagamento individualmente.
    //         // Se qualquer linha tiver uma divergência (mesmo que centavos), desqualifica o fechamento limpo.
    //         $caixaInconsistente = false;
    //         foreach ($formas as $forma) {
    //             $sistemaForma  = $valoresSistemas[$forma] ?? 0.00;
    //             $unificadoForma = $valoresFisicosUnificados[$forma] ?? 0.00;
                
    //             // Tolerância estrita de 1 centavo por moeda
    //             if (abs($unificadoForma - $sistemaForma) > 0.01) {
    //                 $caixaInconsistente = true;
    //             }
    //         }

    //         // Define o status final: Se houver pendência em qualquer forma, salva como 'inconsistente'
    //         $novoStatusCaixa = ($caixaInconsistente) ? 'inconsistente' : 'fechado';

    //         // Grava o cabeçalho mestre da auditoria refletindo o status real
    //         $auditoriaId = $this->criarAuditoriaCabecalho(
    //             $caixa->id, 
    //             $userId, 
    //             $totalSistemaGeral, 
    //             $totalFisicoGeral, 
    //             $novoStatusCaixa === 'fechado' ? 'Fechamento comercial auditado e validado' : 'Fechamento inconsistente por divergência nas formas de pagamento'
    //         );
            
    //         // Grava as linhas vermelhas ou verdes detalhadas no banco
    //         $this->salvarAuditoriaDetalhes($auditoriaId, $valoresSistemas, $valoresFisicosUnificados);
            
    //         // Salva o status definitivo do caixa
    //         $caixa->update([
    //             'valor_fechamento' => $totalFisicoGeral, 
    //             'status'           => $novoStatusCaixa, 
    //             'data_fechamento'  => now(), 
    //             'fechado_por'      => $userId
    //         ]);

    //         return redirect("/fechamento_caixa/confirmacao/{$caixa->id}")
    //             ->with('success', 'Conferência física processada e registrada com sucesso.');
    //     });
    // }

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
            | 5️⃣ COMPOSIÇÃO DOS VALORES E DEDUÇÃO EXCLUSIVA DE SANGRIA NO DINHEIRO
            |--------------------------------------------------------------------------
            */
            // Registra saídas vindas da requisição atual, se houver
            $this->registrarSaidas($request, $caixa); 

            // 🔥 CORREÇÃO DA REGRA DE NEGÓCIO: Captura saídas físicas em dinheiro
            // Sangrias e despesas lançadas no caixa afetam única e exclusivamente o saldo em espécie.
            $totalSaidasDinheiro = DB::table('movimentacoes_caixa')
                ->where('caixa_id', $caixa->id)
                ->whereIn('tipo', ['saida_manual', 'cancelamento_venda', 'saida', 'despesa', 'sangria'])
                ->sum('valor');

            $valoresSistemas = [];
            $totalSistemaGeral = 0.00;
            $totalFisicoGeral = array_sum($valoresFisicos) + array_sum($valoresFisicosCarteira);

            foreach ($formas as $forma) {
                if ($forma === 'carteira') {
                    $valoresSistemas[$forma] = 0.00; 
                } else {
                    $vendaForma = $vendasDoCaixa->get($forma) ? (float)$vendasDoCaixa->get($forma)->total : 0.00;
                    $carteiraForma = $recebimentosCarteira->get($forma) ? (float)$recebimentosCarteira->get($forma)->total : 0.00;
                    
                    $valoresSistemas[$forma] = $vendaForma + $carteiraForma;
                }

                // 🎯 APLICANDO A SANGRIA ISOLADA NO FLUXO DE DINHEIRO
                if ($forma === 'dinheiro') {
                    $valoresSistemas[$forma] += (float)$caixa->fundo_troco;
                    $valoresSistemas[$forma] -= (float)$totalSaidasDinheiro;
                }

                $totalSistemaGeral += $valoresSistemas[$forma];
            }

            /*
            |--------------------------------------------------------------------------
            | 6️⃣ 💎 A REGRA DE CONCILIAÇÃO INDIVIDUAL (BLINDAGEM DA INVERSÃO)
            |--------------------------------------------------------------------------
            */
            // Consolida o que o operador digitou, entendendo que o campo 'dinheiro' já é o total bruto físico
            $valoresFisicosUnificados = [];
            foreach ($formas as $forma) {
                if ($forma === 'carteira') {
                    $valoresFisicosUnificados[$forma] = 0.00;
                } else {
                    // 🎯 ALTERAÇÃO: O dinheiro informado já é o total em espécie da gaveta, não somamos a carteira de forma duplicada
                    if ($forma === 'dinheiro') {
                        $valoresFisicosUnificados[$forma] = (float) ($valoresFisicos[$forma] ?? 0.00);
                    } else {
                        $vendaFisica = $valoresFisicos[$forma] ?? 0.00;
                        $carteiraFisica = $valoresFisicosCarteira[$forma] ?? 0.00;
                        $valoresFisicosUnificados[$forma] = $vendaFisica + $carteiraFisica;
                    }
                }
            }

            // 🧠 PENALIZAÇÃO POR LINHA: Varre cada meio de pagamento individualmente.
            $caixaInconsistente = false;
            foreach ($formas as $forma) {
                $sistemaForma  = $valoresSistemas[$forma] ?? 0.00;
                $unificadoForma = $valoresFisicosUnificados[$forma] ?? 0.00;
                
                // Tolerância estrita de 1 centavo por moeda
                if (abs($unificadoForma - $sistemaForma) > 0.01) {
                    $caixaInconsistente = true;
                }
            }

            // Define o status final: Se houver pendência em qualquer forma, salva como 'inconsistente'
            $novoStatusCaixa = ($caixaInconsistente) ? 'inconsistente' : 'fechado';

            // Grava o cabeçalho mestre da auditoria refletindo o status real
            $auditoriaId = $this->criarAuditoriaCabecalho(
                $caixa->id, 
                $userId, 
                $totalSistemaGeral, 
                $totalFisicoGeral, 
                $novoStatusCaixa === 'fechado' ? 'Fechamento comercial auditado e validado' : 'Fechamento inconsistente por divergência nas formas de pagamento'
            );
            
            // Grava as linhas vermelhas ou verdes detalhadas no banco
            $this->salvarAuditoriaDetalhes($auditoriaId, $valoresSistemas, $valoresFisicosUnificados);
            
            // Salva o status definitivo do caixa
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

    private function salvarAuditoriaDetalhes(int $auditoriaId, array $valoresSistemas, array $valoresFisicos): void
    {
        // Lista estática explícita para garantir o pente-fino das 5 formas da sua Blade
        $listaFormas = ['dinheiro', 'pix', 'carteira', 'cartao_debito', 'cartao_credito'];

        foreach ($listaFormas as $forma) {
            $sistema = (float)($valoresSistemas[$forma] ?? 0.00);
            $fisico  = (float)($valoresFisicos[$forma] ?? 0.00);
            $diff    = $fisico - $sistema;

            DB::table('auditoria_detalhes')->insert([
                'auditoria_id'    => $auditoriaId,
                'forma_pagamento' => $forma,
                'total_sistema'   => $sistema,
                'total_fisico'    => $fisico,
                'diferenca'       => $diff,
                'status'          => (abs($diff) <= 0.01 ? 'correto' : 'divergente'), // ENUM da sua tabela auditoria_detalhes
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
//    public function ajustarDivergencias(Request $request, int $caixaId)
//     {
//         $request->validate([
//             'formas'   => 'required|array|min:1',
//             'formas.*' => 'required',
//         ]);
        
//         DB::transaction(function () use ($request, $caixaId) {

//             // 1️⃣ CÁLCULO DAS VENDAS REAIS DO SISTEMA DIRETO DA FITA OFICIAL
//             $totalVendas = DB::table('movimentacoes_caixa')
//                 ->where('caixa_id', $caixaId)
//                 ->where('tipo', 'venda')
//                 ->sum('valor');

//             $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

//             if ($caixa->status !== 'inconsistente' && $caixa->status !== 'aberto') {
//                 throw new \Exception('Este caixa não está disponível para auditoria ou correção.');
//             }

//             $userId = auth()->id();
//             $totalAuditado = 0.00; // Acumulador do fechamento físico real

//             // 2️⃣ BUSCA O CABEÇALHO DA AUDITORIA GERAL PARA CONEXÃO DE DETALHES
//             $auditoria = DB::table('auditorias_caixa')
//                 ->where('caixa_id', $caixaId)
//                 ->latest('data_auditoria')
//                 ->first();

//             foreach ($request->formas as $forma => $valorStr) {
//                 // Conversão segura usando o helper do seu sistema
//                 $valorAuditado = $this->parseValorBR($valorStr);
//                 $totalAuditado += $valorAuditado; // 👈 CORRIGIDO: Acumula o valor físico real para fechar o caixa

//                 // Captura o valor real do sistema direto da fita de movimentações
//                 $valorSistema = DB::table('movimentacoes_caixa')
//                     ->where('caixa_id', $caixaId)
//                     ->where('tipo', 'venda')
//                     ->where('forma_pagamento', $forma)
//                     ->sum('valor');

//                 // Se for dinheiro vivo, adiciona o fundo de troco inicial ao esperado do sistema
//                 if ($forma === 'dinheiro') {
//                     $valorSistema += (float)$caixa->fundo_troco;
//                 }

//                 // 3️⃣ ATUALIZAÇÃO DA TABELA HISTÓRICA DO CAIXA
//                 MovimentacaoCaixa::updateOrCreate(
//                     [
//                         'caixa_id'        => $caixa->id,
//                         'tipo'            => 'auditoria',
//                         'forma_pagamento' => $forma,
//                     ],
//                     [
//                         'user_id'         => $userId,
//                         'valor'           => $valorSistema,     
//                         'valor_auditado'  => $valorAuditado,    
//                         'observacao'      => 'Auditoria por forma de pagamento realizada com sucesso',
//                         'data_movimentacao'=> now(),
//                     ]
//                 );

//                 // 4️⃣ SINCRO COM A TABELA auditoria_detalhes QUE ATIVAMOS
//                 if ($auditoria) {
//                     $novaDiferencaForma = $valorAuditado - $valorSistema;
                    
//                     DB::table('auditoria_detalhes')
//                         ->where('auditoria_id', $auditoria->id)
//                         ->where('forma_pagamento', $forma)
//                         ->update([
//                             'total_fisico' => $valorAuditado, // ou valor_informado conforme seu banco
//                             'diferenca'    => $novaDiferencaForma,
//                             'status'       => abs($novaDiferencaForma) <= 0.01 ? 'correto' : 'divergente',
//                             'updated_at'   => now()
//                         ]);
//                 }

//                 // 5️⃣ GERAÇÃO DE AJUSTES CONTÁBEIS SE HOUVER DIFERENÇA
//                 $diferenca = $valorAuditado - $valorSistema;

//                 if (abs($diferenca) > 0.01) {
//                     DB::table('movimentacoes_caixa')->insert([
//                         'caixa_id'          => $caixa->id,
//                         'auditoria_id'      => $auditoria ? $auditoria->id : null,
//                         'user_id'           => $userId,
//                         'tipo'              => $diferenca > 0 ? 'entrada_manual' : 'saida_manual',
//                         'forma_pagamento'   => $forma,
//                         'valor'             => abs($diferenca),
//                         'valor_auditado'    => 0.00,
//                         'bandeira'          => null,
//                         'origem_id'         => null,
//                         'observacao'        => "[AJUSTE AUDITORIA FISCAL] Forma: " . ucfirst($forma),
//                         'data_movimentacao' => now(),
//                         'created_at'        => now(),
//                         'updated_at'        => now()
//                     ]);
//                 }
//             }

//             // 6️⃣ ATUALIZAÇÃO DO MODEL DE CONFERÊNCIA ADMINISTRATIVA
//             MovimentacaoCaixa::where('caixa_id', $caixa->id)
//                 ->where('tipo', 'fechamento')
//                 ->latest('id')
//                 ->update([
//                     'valor_auditado' => $totalVendas,               
//                     'observacao'     => 'Auditoria concluída e homologada por forma de pagamento',
//                     'user_id'        => $userId,
//                     'updated_at'     => now(),
//                 ]);

//             // 7️⃣ ATUALIZAÇÃO DEFINITIVA DO CABEÇALHO DO CAIXA (Preenche a data e o valor da imagem!)
//             $caixa->update([
//                 'valor_fechamento' => $totalAuditado, // 👈 CORRIGIDO: Injeta o valor real no card da imagem
//                 'status'           => 'fechado',       // Força o status definitivo para Fechado
//                 'data_fechamento'  => now(),          // 👈 CORRIGIDO: Registra o relógio e remove o traço (-) da imagem
//                 'fechado_por'      => $userId,
//             ]);

//             // Atualiza a tabela auditorias_caixa se ela existir
//             if ($auditoria) {
//                 $novaDiferencaGeral = $totalAuditado - (float)$auditoria->total_sistema;
//                 DB::table('auditorias_caixa')
//                     ->where('id', $auditoria->id)
//                     ->update([
//                         'total_fisico' => $totalAuditado,
//                         'diferenca'    => $novaDiferencaGeral,
//                         'status'       => abs($novaDiferencaGeral) <= 0.01 ? 'concluida' : 'corrigida',
//                     ]);
//             }
//         });

//         // 8️⃣ REDIRECIONAMENTO SEGURO POR LINK LIMPO DIRETO
//         // Envia o alerta com a chave 'auditoria_sucesso' idêntica à que a sua View espera receber
//         return redirect("/fechamento_caixa/confirmacao/{$caixaId}")
//             ->with('auditoria_sucesso', 'A divergência do Caixa #' . $caixaId . ' foi corrigida pela auditoria.');
//     }

    // public function ajustarDivergencias(Request $request, int $caixaId)
    // {
    //     $request->validate([
    //         'formas'   => 'required|array|min:1',
    //         'formas.*' => 'required',
    //     ]);
        
    //     DB::transaction(function () use ($request, $caixaId) {

    //         $caixa = Caixa::lockForUpdate()->findOrFail($caixaId);

    //         if ($caixa->status !== 'inconsistente' && $caixa->status !== 'aberto') {
    //             throw new \Exception('Este caixa não está disponível para auditoria ou correção.');
    //         }

    //         $userId = auth()->id();
            
    //         // 🎯 AJUSTE 1: Limpa qualquer ajuste contábil ou linha de auditoria gerada anteriormente neste caixa
    //         // Isso impede o acúmulo de duplicidades e valores absurdos caso a tela seja salva mais de uma vez
    //         DB::table('movimentacoes_caixa')
    //             ->where('caixa_id', $caixaId)
    //             ->where(function($q) {
    //                 $q->where('tipo', 'auditoria')
    //                 ->orWhere('observacao', 'LIKE', '[AJUSTE AUDITORIA FISCAL]%');
    //             })
    //             ->delete();

    //         // 1️⃣ CÁLCULO DAS VENDAS REAIS DO SISTEMA DIRETO DA FITA OFICIAL (Vendas puras)
    //         $totalVendas = DB::table('movimentacoes_caixa')
    //             ->where('caixa_id', $caixaId)
    //             ->where('tipo', 'venda')
    //             ->sum('valor');

    //         $totalAuditado = 0.00; // Acumulador do fechamento físico real

    //         // 2️⃣ BUSCA O CABEÇALHO DA AUDITORIA GERAL PARA CONEXÃO DE DETALHES
    //         $auditoria = DB::table('auditorias_caixa')
    //             ->where('caixa_id', $caixaId)
    //             ->latest('data_auditoria')
    //             ->first();

    //         foreach ($request->formas as $forma => $valorStr) {
    //             $valorAuditado = $this->parseValorBR($valorStr);
    //             $totalAuditado += $valorAuditado; 

    //             // 🎯 AJUSTE 2: Mapeamento blindado com base no banco para capturar strings com espaço ou acentos
    //             if ($forma === 'dinheiro') {
    //                 $valorSistema = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'dinheiro')->where('tipo', 'venda')->sum('valor');
    //                 $valorSistema += (float)$caixa->fundo_troco; // Injeta o fundo no dinheiro
    //             } elseif ($forma === 'pix') {
    //                 $valorSistema = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'pix')->where('tipo', 'venda')->sum('valor');
    //             } elseif ($forma === 'carteira') {
    //                 $valorSistema = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('forma_pagamento', 'carteira')->where('tipo', 'venda')->sum('valor');
    //             } elseif ($forma === 'cartao_debito') {
    //                 $valorSistema = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 'venda')
    //                     ->where(function($q) { $q->whereIn('forma_pagamento', ['cartao_debito', 'debito', 'Debito', 'Cartao debito', 'cartao debito'])->orWhere('forma_pagamento', 'LIKE', '%debito%'); })->sum('valor');
    //             } elseif ($forma === 'cartao_credito') {
    //                 $valorSistema = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 'venda')
    //                     ->where(function($q) { $q->whereIn('forma_pagamento', ['cartao_credito', 'credito', 'Credito', 'Cartao credito', 'cartao credito'])->orWhere('forma_pagamento', 'LIKE', '%credito%'); })->sum('valor');
    //             } else {
    //                 $valorSistema = 0.00;
    //             }

    //             // 3️⃣ ATUALIZAÇÃO DA TABELA HISTÓRICA DO CAIXA
    //             MovimentacaoCaixa::create([
    //                 'caixa_id'          => $caixa->id,
    //                 'user_id'           => $userId,
    //                 'tipo'              => 'auditoria', // Salva de forma limpa após o delete do início do método
    //                 'forma_pagamento'   => $forma,
    //                 'valor'             => $valorSistema,     
    //                 'valor_auditado'    => $valorAuditado,    
    //                 'observacao'        => 'Auditoria por forma de pagamento realizada com sucesso',
    //                 'data_movimentacao' => now(),
    //             ]);

    //             // 4️⃣ SINCRO COM A TABELA auditoria_detalhes
    //             if ($auditoria) {
    //                 $novaDiferencaForma = $valorAuditado - $valorSistema;
                    
    //                 DB::table('auditoria_detalhes')
    //                     ->where('auditoria_id', $auditoria->id)
    //                     ->where('forma_pagamento', $forma)
    //                     ->update([
    //                         'total_fisico' => $valorAuditado, 
    //                         'diferenca'    => $novaDiferencaForma,
    //                         'status'       => abs($novaDiferencaForma) <= 0.01 ? 'correto' : 'divergente',
    //                         'updated_at'   => now()
    //                     ]);
    //             }

    //             // 5️⃣ GERAÇÃO DE AJUSTES CONTÁBEIS SE HOUVER DIFERENÇA
    //             $diferenca = $valorAuditado - $valorSistema;

    //             if (abs($diferenca) > 0.01) {
    //                 DB::table('movimentacoes_caixa')->insert([
    //                     'caixa_id'          => $caixa->id,
    //                     'auditoria_id'      => $auditoria ? $auditoria->id : null,
    //                     'user_id'           => $userId,
    //                     'tipo'              => $diferenca > 0 ? 'entrada_manual' : 'saida_manual',
    //                     'forma_pagamento'   => $forma,
    //                     'valor'             => abs($diferenca),
    //                     'valor_auditado'    => 0.00,
    //                     'bandeira'          => null,
    //                     'origem_id'         => null,
    //                     'observacao'        => "[AJUSTE AUDITORIA FISCAL] Forma: " . ucfirst($forma),
    //                     'data_movimentacao' => now(),
    //                     'created_at'        => now(),
    //                     'updated_at'        => now()
    //                 ]);
    //             }
    //         }

    //         // 6️⃣ ATUALIZAÇÃO DO MODEL DE CONFERÊNCIA ADMINISTRATIVA
    //         MovimentacaoCaixa::where('caixa_id', $caixa->id)
    //             ->where('tipo', 'fechamento')
    //             ->latest('id')
    //             ->update([
    //                 'valor_auditado' => $totalVendas,               
    //                 'observacao'     => 'Auditoria concluída e homologada por forma de pagamento',
    //                 'user_id'        => $userId,
    //                 'updated_at'     => now(),
    //             ]);

    //         // 7️⃣ ATUALIZAÇÃO DEFINITIVA DO CABEÇALHO DO CAIXA
    //         $caixa->update([
    //             'valor_fechamento' => $totalAuditado, 
    //             'status'           => 'fechado',       
    //             'data_fechamento'  => now(),          
    //             'fechado_por'      => $userId,
    //         ]);

    //         if ($auditoria) {
    //             $novaDiferencaGeral = $totalAuditado - (float)$auditoria->total_sistema;
    //             DB::table('auditorias_caixa')
    //                 ->where('id', $auditoria->id)
    //                 ->update([
    //                     'total_fisico' => $totalAuditado,
    //                     'diferenca'    => $novaDiferencaGeral,
    //                     'status'       => abs($novaDiferencaGeral) <= 0.01 ? 'concluida' : 'corrigida',
    //                 ]);
    //         }
    //     });

    //     return redirect("/fechamento_caixa/confirmacao/{$caixaId}")
    //         ->with('auditoria_sucesso', 'A divergência do Caixa #' . $caixaId . ' foi corrigida pela auditoria.');
    // }

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

    //FAZ A AUDITORIA DO FECHAMENTO
    // public function auditar(Request $request, $id)
    // {
    //     $movimentacao = MovimentacaoCaixa::findOrFail($id);

    //     if ($movimentacao->tipo !== 'fechamento') {
    //         return redirect()->back()
    //             ->withErrors('Somente o fechamento de caixa pode ser auditado.');
    //     }

    //     $request->validate([
    //         'valor_auditado' => ['required', 'numeric', 'min:0'],
    //     ]);

    //     $movimentacao->update([
    //         'valor_auditado' => $request->valor_auditado,
    //         'user_id' => auth()->id(),
    //         'observacao' => 'Correção de divergência',
    //     ]);

    //     return back()->with('success', 'Auditoria registrada com sucesso.');
    // }

    // FAZ A AUDITORIA DO FECHAMENTO SEGUINDO AS BOAS PRÁTICAS
    // public function auditar(\Illuminate\Http\Request $request, $caixaId)
    // {
    //     // 1️⃣ Validação rigorosa dos dados de entrada
    //     $request->validate([
    //         'valor_auditado' => ['required', 'numeric', 'min:0'],
    //         'justificativa'  => ['nullable', 'string', 'max:255'], // Campo opcional para o auditor
    //     ]);

    //     $usuarioLogado = auth()->user();
    //     if (!$usuarioLogado) {
    //         return redirect()->back()->withErrors('Sessão expirada. Faça login novamente.');
    //     }

    //     try {
    //         // 2️⃣ Executa a auditoria dentro de uma transação isolada para blindar múltiplos PDVs
    //         \Illuminate\Support\Facades\DB::transaction(function () use ($request, $caixaId, $usuarioLogado) {
                
    //             // 🔒 Localiza a movimentação de fechamento vinculada estritamente a este Caixa e aplica o travamento
    //             $movimentacao = \App\Models\MovimentacaoCaixa::where('caixa_id', (int) $caixaId)
    //                 ->where('tipo', 'fechamento')
    //                 ->lockForUpdate() // Impede concorrência de dados se dois gerentes tentarem auditar juntos
    //                 ->first();

    //             if (!$movimentacao) {
    //                 throw new \Exception('Operação Negada: O registro de fechamento para este caixa não foi localizado.');
    //             }

    //             // 🧠 PRESERVAÇÃO DE HISTÓRICO: Junta a observação antiga com a nota da nova auditoria
    //             $observacaoAntiga = $movimentacao->observacao ? $movimentacao->observacao . ' | ' : '';
    //             $notaAuditor = $request->input('justificativa', 'Correção de divergência efetuada na auditoria.');
    //             $novaObservacao = $observacaoAntiga . "Auditado por: {$usuarioLogado->nome}. Nota: " . $notaAuditor;

    //             // 3️⃣ Realiza a atualização segura dos valores auditados
    //             \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
    //                 ->where('id', $movimentacao->id)
    //                 ->update([
    //                     'valor_auditado' => (float) $request->valor_auditado,
    //                     'user_id'        => $usuarioLogado->id, // Grava qual auditor/gerente fez a correção
    //                     'observacao'     => $novaObservacao,
    //                     'updated_at'     => now()
    //                 ]);

    //             // Se o seu sistema possuir uma tabela de 'caixas', você pode opcionalmente mudar o status dele aqui
    //             \Illuminate\Support\Facades\DB::table('caixas')
    //                 ->where('id', $caixaId)
    //                 ->update([
    //                     'status'     => 'auditado', // Altera de 'inconsistente' para 'auditado'
    //                     'updated_at' => now()
    //                 ]);
    //         });

    //         return redirect()->back()->with('success', 'Auditoria de caixa registrada com sucesso.');

    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors('Erro ao processar auditoria: ' + $e->getMessage());
    //     }
    // }

    // FAZ A AUDITORIA DO FECHAMENTO RECALCULANDO OS TOTAIS DO SISTEMA DE FORMA SEPARADA
    // public function auditar(\Illuminate\Http\Request $request, $caixaId)
    // {
    //     // 1️⃣ Validação rigorosa do valor enviado pelo auditor
    //     $request->validate([
    //         'valor_auditado' => ['required', 'numeric', 'min:0'],
    //     ]);

    //     $usuarioLogado = auth()->user();
    //     if (!$usuarioLogado) {
    //         return redirect()->back()->withErrors('Sessão expirada. Faça login novamente.');
    //     }

    //     try {
    //         // 2️⃣ Executa a operação dentro de uma transação isolada (Impede erro concorrente em múltiplos PDVs)
    //         \Illuminate\Support\Facades\DB::transaction(function () use ($request, $caixaId, $usuarioLogado) {
                
    //             // 🔒 Localiza e trava a linha de movimentação do tipo 'fechamento' para este caixa específico
    //             $movimentacaoFechamento = \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
    //                 ->where('caixa_id', (int) $caixaId)
    //                 ->where('tipo', 'fechamento')
    //                 ->lockForUpdate()
    //                 ->first();

    //             if (!$movimentacaoFechamento) {
    //                 throw new \Exception('O registro de fechamento para este caixa não foi localizado.');
    //             }

    //             // 🔒 Localiza e trava a tabela mestre do Caixa para atualização dos cards da tela
    //             $caixa = \Illuminate\Support\Facades\DB::table('caixas')
    //                 ->where('id', (int) $caixaId)
    //                 ->lockForUpdate()
    //                 ->first();

    //             if (!$caixa) {
    //                 throw new \Exception('Caixa mestre não localizado.');
    //             }

    //             // 🧮 A NOVA MATEMÁTICA DA CORREÇÃO (SINCRONIZADA COM AS REGRAS DO INDEX):
                
    //             // ➔ Pega apenas o dinheiro físico que entrou por Vendas Diretas no Balcão
    //             $dinheiroVendasBalcao = \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
    //                 ->where('caixa_id', $caixaId)
    //                 ->where('forma_pagamento', 'dinheiro')
    //                 ->where('tipo', 'venda')
    //                 ->sum('valor');

    //             // ➔ Pega apenas o dinheiro físico que entrou por Recebimentos de Carteira (F9 / Quitações)
    //             $dinheiroCarteiraRecebida = \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
    //                 ->where('caixa_id', $caixaId)
    //                 ->where('forma_pagamento', 'dinheiro')
    //                 ->where('tipo', 'entrada')
    //                 ->sum('valor');

    //             // ➔ Soma também aportes ou entradas manuais avulsas em espécie se houverem
    //             $dinheiroAportesManuais = \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
    //                 ->where('caixa_id', $caixaId)
    //                 ->where('forma_pagamento', 'dinheiro')
    //                 ->where('tipo', 'entrada_manual')
    //                 ->sum('valor');

    //             // 📊 O total arrecadado real em dinheiro vivo neste turno é a união das três fontes limpas
    //             $faturamentoDinheiroTotal = (float)$dinheiroVendasBalcao + (float)$dinheiroCarteiraRecebida + (float)$dinheiroAportesManuais;

    //             // Total esperado em espécie na gaveta: Fundo de troco + Todo o faturamento real em dinheiro vivo
    //             $totalEsperadoDinheiro = (float) $caixa->fundo_troco + $faturamentoDinheiroTotal;

    //             // O novo valor informado pelo auditor na tela (Contagem física final do gerente)
    //             $valorAuditadoInformado = (float) $request->valor_auditado;

    //             // Diferença real entre o dinheiro que o sistema esperava e o que o gerente contou fisicamente
    //             $novaDivergencia = $valorAuditadoInformado - $totalEsperadoDinheiro;

    //             // Define o novo status contábil baseado no resultado do recálculo
    //             $novoStatus = (round($novaDivergencia, 2) == 0) ? 'auditado' : 'inconsistente_revisado';

    //             // 3️⃣ ATUALIZAÇÃO DA LINHA FILHO (movimentacoes_caixa)
    //             $observacaoAntiga = $movimentacaoFechamento->observacao ? $movimentacaoFechamento->observacao . ' | ' : '';
    //             \Illuminate\Support\Facades\DB::table('movimentacoes_caixa')
    //                 ->where('id', $movimentacaoFechamento->id)
    //                 ->update([
    //                     'valor_auditado' => $valorAuditadoInformado,
    //                     'user_id'        => $usuarioLogado->id,
    //                     'observacao'     => $observacaoAntiga . "Auditado por: {$usuarioLogado->name}. Correção de divergência financeira baseada em fluxo real.",
    //                     'updated_at'     => now()
    //                 ]);

    //             // 4️⃣ ATUALIZAÇÃO DA TABELA MESTRE (caixas) - CRUCIAL PARA CORRIGIR OS CARDS DA TELA
    //             \Illuminate\Support\Facades\DB::table('caixas')
    //                 ->where('id', $caixaId)
    //                 ->update([
    //                     'status'         => $novoStatus,
    //                     // Se o seu banco possuir colunas de controle de divergência na tabela caixas, atualize aqui:
    //                     // 'divergencia'  => $novaDivergencia, 
    //                     'updated_at'     => now()
    //                 ]);
    //         });

    //         return redirect()->back()->with('success', 'Auditoria contábil de caixa registrada com sucesso.');

    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors('Erro ao processar recálculo de auditoria: ' . $e->getMessage());
    //     }
    // }
   
    //CORRIGE AS DIVERGÊNCIAS APÓS A AUDITORIA FISCAL e imprime mensagem na tela
   public function auditoria($caixa)
    {
        $caixa = Caixa::findOrFail($caixa);

        $movimentacoes = MovimentacaoCaixa::where('caixa_id', $caixa->id)
                            ->where('tipo', 'fechamento')
                            ->get();

        return view('fechamento_caixa.confirmacao_auditoria', compact('caixa', 'movimentacoes'));

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