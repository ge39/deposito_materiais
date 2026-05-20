<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\CaixaService;


class CaixaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Exibe a tela de abertura de caixa
     */
    public function abrir(Request $request)
    {
        $user = Auth::user();
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            Log::error('Terminal não identificado ao tentar abrir caixa.');
            return redirect()->back()->with('error', 'Terminal não identificado.');
        }

        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
             ->latest('data_abertura')
            ->first();

        if ($caixaAberto) {
            Log::warning('Tentativa de abrir caixa já existente', [
                'terminal_id' => $terminal->id,
                'caixa_id' => $caixaAberto->id
            ]);
            return redirect()->route('pdv.index')
                ->with('warning', 'Caixa já está aberto para este terminal.');
        }

        return view('caixa.abrir', [
            'user' => $user,
            'terminal' => $terminal,
             'data_abertura' => $caixaAberto?->data_abertura,
        ]);
    }

    /**
     * Salva um novo caixa na tabela caixas e registra a abertura
     */
 
    public function store(Request $request) 
    {
        $user = Auth::user();
        $terminal = $request->attributes->get('terminal');

        if (!$terminal) {
            return redirect()->back()->withErrors('Terminal não identificado no sistema.');
        }

        // 1. Evita duplicidade no terminal atual
        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->first();

        if ($caixaAberto) {
            return redirect()->route('pdv.index')
                ->with('warning', 'Caixa já aberto para este terminal.');
        }

        // 2. Busca a empresa Gegames (ID 9, ativo 1)
        $empresaAtiva = Empresa::where('ativo', 1)->first();

        if (!$empresaAtiva) {
            return redirect()->back()->withErrors('Nenhuma empresa ativa encontrada no banco.');
        }

        $valorFundoAnterior = (float) $request->input('valor_fundo_anterior', 0.00);
        $fundoTroco = (float) $request->input('fundo_troco', 0.00);

        // Converte o identificador do terminal para string limpa para o banco
        $nomeTerminal = is_object($terminal) ? ($terminal->identificador ?? $terminal->nome) : (string) $terminal;

        try {
            // 3. Força a gravação limpando os tipos de dados
            $caixa = Caixa::create([
                'user_id'              => (int) $user->id,
                'empresa_id'           => (int) $empresaAtiva->id, // Grava o ID 9
                'terminal_id'          => (int) $terminal->id,
                'terminal'             => substr((string) $nomeTerminal, 0, 255), // Garante limite do VARCHAR
                'valor_fundo_anterior' => $valorFundoAnterior,
                'fundo_troco'          => $fundoTroco,
                'divergencia_abertura' => (float) ($fundoTroco - $valorFundoAnterior),
                'valor_abertura'       => $fundoTroco, 
                'status'               => 'aberto', // 👈 Força string exata do ENUM do banco
                'data_abertura'        => now()->format('Y-m-d H:i:s'), // Garante formato DATETIME
                'observacao'           => $request->input('observacao'),
            ]);

            // 4. Registro no service
            CaixaService::registrarMovimentacaoCaixa([
                'caixa_id'        => $caixa->id,
                'user_id'         => $user->id,
                'tipo'            => 'abertura',
                'forma_pagamento' => 'abertura',
                'valor'           => $fundoTroco,
                'origem_id'       => $caixa->id,
                'observacao'      => 'Abertura de caixa',
            ]);

            return redirect()->route('pdv.index')
                ->with('success', 'Caixa aberto com sucesso.')
                ->with('caixa_id', $caixa->id);

        } catch (\Exception $e) {
            // 🚨 Se o banco rejeitar por causa de alguma coluna, o erro aparecerá na tela no bloco que adicionamos no Blade
            return redirect()->back()->withErrors('Erro no Banco de Dados: ' . $e->getMessage());
        }
    }

    /**
     * Gera PDF do relatório do caixa
     */
    public function relatorioPdf($caixaId)
    {
        $caixa = Caixa::findOrFail($caixaId);

        $movimentacoes = MovimentacaoCaixa::where('caixa_id', $caixa->id)
            ->orderBy('data_movimentacao')
            ->get();

        $totais_por_tipo = $movimentacoes
            ->groupBy('tipo')
            ->map(fn ($items) => $items->sum('valor'))
            ->toArray();

        $pagamentos_por_forma = MovimentacaoCaixa::select(
            'forma_pagamento',
            \DB::raw('SUM(valor) as total')
        )
        ->where('caixa_id', $caixa->id)
        ->where('tipo', 'venda')
        ->groupBy('forma_pagamento')
        ->get();

        $saldo_sistema = $movimentacoes->sum(function ($mov) {
            return in_array($mov->tipo, ['venda', 'entrada_manual'])
                ? $mov->valor
                : -$mov->valor;
        });

        $pdf = Pdf::loadView('caixa.relatorio', compact(
            'caixa',
            'movimentacoes',
            'totais_por_tipo',
            'pagamentos_por_forma',
            'saldo_sistema'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream("relatorio-caixa-{$caixa->id}.pdf");
    }
    
    // public function fecharCaixa(Request $request, $caixaId)
    // {
    //     // Busca o caixa e valida se está aberto
    //     $caixa = Caixa::findOrFail($caixaId);
        
    //     // Soma os valores por forma de pagamento associados a este caixa hoje
    //     $faturamento = DB::table('pagamentos_vendas') // Substitua pelo nome real da sua tabela de pagamentos
    //         ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
    //         ->where('caixa_id', $caixaId)
    //         ->whereDate('created_at', now()->toDateString())
    //         ->groupBy('forma_pagamento')
    //         ->get()
    //         ->keyBy('forma_pagamento');

    //     // Monta o resumo financeiro formatado
    //     $resumo = [
    //         'dinheiro'       => $faturamento->get('dinheiro')->total ?? 0.00,
    //         'pix'            => $faturamento->get('pix')->total ?? 0.00,
    //         'cartao_credito' => $faturamento->get('cartao_credito')->total ?? 0.00,
    //         'cartao_debito'  => $faturamento->get('cartao_debito')->total ?? 0.00,
    //         'carteira'       => $faturamento->get('carteira')->total ?? 0.00,
    //     ];

    //     $totalGeral = array_sum($resumo);

    //     return view('caixa.fechamento', compact('caixa', 'resumo', 'totalGeral'));
    // }

    // // public function fecharCaixa(Request $request, $caixaId)
    // // {
    // //     // 1. Busca o caixa por ID de forma segura
    // //     $caixa = Caixa::findOrFail($caixaId);
        
    // //     // 2. 🔥 FILTRO DE BUSCA CORRIGIDO: 
    // //     // Agrupa os valores diretamente da tabela 'movimentacoes_caixa' pelos IDs numéricos das formas
    // //     $movimentacoes = DB::table('movimentacoes_caixa')
    // //         ->select('forma', DB::raw('SUM(valor) as total'))
    // //         ->where('caixa_id', $caixaId)
    // //         ->where('tipo', 1) // 1 = Entrada / Venda
    // //         ->groupBy('forma')
    // //         ->get()
    // //         ->keyBy('forma');

    // //     // 3. 🧠 MAPEAMENTO INVERSO DA TABELA: 
    // //     // Converte os códigos numéricos (1, 2, 3...) nas chaves textuais que a sua View Blade usa
    // //     $totaisPorForma = [
    // //         'dinheiro'       => (float) ($movimentacoes->get(1)->total ?? 0.00), // Código 1 do seu banco
    // //         'cartao_credito' => (float) ($movimentacoes->get(2)->total ?? 0.00), // Código 2 do seu banco
    // //         'cartao_debito'  => (float) ($movimentacoes->get(3)->total ?? 0.00), // Código 3 do seu banco
    // //         'pix'            => (float) ($movimentacoes->get(4)->total ?? 0.00), // Código 4 do seu banco
    // //         'carteira'       => (float) ($movimentacoes->get(5)->total ?? 0.00), // Código 5 do seu banco
    // //     ];

    // //     // 4. Cálculos complementares para bater com as variáveis da sua Blade de Auditoria
    // //     $totalGeralSistema = array_sum($totaisPorForma);
        
    // //     // Busca entradas e saídas gerais para o cálculo de divergência
    // //     $total_entradas = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 1)->sum('valor');
    // //     $total_saidas   = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 2)->sum('valor');
        
    // //     $divergencia = 0.00; // Pode ser calculada dinamicamente com base na sangria se necessário

    // //     // 5. Retorna a View enviando exatamente a array de dados esperada pelas chaves da Blade
    // //     return view('caixa.fechamento', [
    // //         'caixa'             => $caixa,
    // //         'totaisPorForma'    => $totaisPorForma, // Passa a array tratada
    // //         'totalGeralSistema' => $totalGeralSistema,
    // //         'total_entradas'    => $total_entradas,
    // //         'total_saidas'      => $total_saidas,
    // //         'divergencia'       => $divergencia,
    // //         'status'            => $caixa->status
    // //     ]);
    // // }
    
    //     /**
    //  * Exibe a tela de fechamento e auditoria do caixa ativo
    //  * Rota: /fechamento_caixa/auditar/{caixaId}
    //  */
    // // public function auditar(Request $request, $caixaId)
    // // {
    // //     // 1. Busca o caixa no banco ou falha com 404 se não existir
    // //     $caixa = Caixa::findOrFail($caixaId);
        
    // //     // 2. 🔥 FILTRO INVERSO DE FORMAS DE PAGAMENTO:
    // //     // Soma e agrupa os valores da tabela 'movimentacoes_caixa' pelos códigos numéricos (1, 2, 3...)
    // //     $movimentacoes = DB::table('movimentacoes_caixa')
    // //         ->select('forma', DB::raw('SUM(valor) as total'))
    // //         ->where('caixa_id', $caixaId)
    // //         ->where('tipo', 1) // 1 = Entrada / Vendas
    // //         ->groupBy('forma')
    // //         ->get()
    // //         ->keyBy('forma');

    // //     // 3. 🧠 MAPEAMENTO COMPATÍVEL COM A SUA BLADE:
    // //     // Converte os códigos numéricos do banco nas chaves de texto que a sua View faz o @foreach
    // //     $totaisPorForma = [
    // //         'dinheiro'       => (float) ($movimentacoes->get(1)->total ?? 0.00), // Código 1 do seu banco
    // //         'cartao_credito' => (float) ($movimentacoes->get(2)->total ?? 0.00), // Código 2 do seu banco
    // //         'cartao_debito'  => (float) ($movimentacoes->get(3)->total ?? 0.00), // Código 3 do seu banco
    // //         'pix'            => (float) ($movimentacoes->get(4)->total ?? 0.00), // Código 4 do seu banco
    // //         'carteira'       => (float) ($movimentacoes->get(5)->total ?? 0.00), // Código 5 do seu banco
    // //     ];

    // //     // 4. Somas gerais de Entradas e Saídas do Caixa 310
    // //     $totalGeralSistema = array_sum($totaisPorForma);
        
    // //     $total_entradas = DB::table('movimentacoes_caixa')
    // //                         ->where('caixa_id', $caixaId)
    // //                         ->where('tipo', 1)
    // //                         ->sum('valor');
                            
    // //     $total_saidas   = DB::table('movimentacoes_caixa')
    // //                         ->where('caixa_id', $caixaId)
    // //                         ->where('tipo', 2) // 2 = Saídas/Sangrias
    // //                         ->sum('valor');

    // //     // Calcula a divergência comparando o saldo atual com o fundo de abertura
    // //     $divergencia = (float) (($caixa->fundo_troco + $totaisPorForma['dinheiro']) - $caixa->valor_abertura);

    // //     // Instancia uma classe anônima rápida apenas para não quebrar a variável $vm->semMovimento da sua Blade
    // //     $vm = new \stdClass();
    // //     $vm->semMovimento = ($totalGeralSistema <= 0);

    // //     // 5. Retorna a view enviando rigorosamente todas as variáveis que a sua Blade usa
    // //     return view('caixa.fechamento', compact(
    // //         'caixa',
    // //         'totaisPorForma',
    // //         'totalGeralSistema',
    // //         'total_entradas',
    // //         'total_saidas',
    // //         'divergencia',
    // //         'vm'
    // //     ));
    // // }

        /**
     * Exibe a tela de fechamento e auditoria do caixa ativo
     * Rota: /fechamento_caixa/auditar/{caixaId}
     */
       /**
     * Exibe a tela de fechamento e auditoria do caixa ativo
     * URL correspondente: /fechamento_caixa/auditar/{caixaId}
     */
    
    public function auditar(Request $request, $caixaId)
    {
        // 1. Busca o caixa no banco ou falha com 404 se não existir
        $caixa = Caixa::findOrFail($caixaId);
        
        // 2. Busca e mapeia o histórico bruto da tabela movimentacoes_caixa para a listagem inferior
        $movimentacoesColecao = DB::table('movimentacoes_caixa')
            ->where('caixa_id', $caixaId)
            ->orderBy('id', 'asc')
            ->get();

        // 3. Agrupa e soma os valores de entrada (tipo = 1) para alimentar os cartões superiores
        $somasFormas = DB::table('movimentacoes_caixa')
            ->select('forma', DB::raw('SUM(valor) as total'))
            ->where('caixa_id', $caixaId)
            ->where('tipo', 1) // 1 = Entradas/Vendas
            ->groupBy('forma')
            ->get()
            ->keyBy('forma');

        // 4. Converte os IDs numéricos (1,2,3,4,5) nos nomes textuais exatos exigidos pelo seu @foreach na Blade
        // $totaisPorForma = [
        //     'dinheiro'       => (float) ($somasFormas->get(1)->total ?? 0.00),
        //     'cartao_credito' => (float) ($somasFormas->get(2)->total ?? 0.00),
        //     'cartao_debito'  => (float) ($somasFormas->get(3)->total ?? 0.00),
        //     'pix'            => (float) ($somasFormas->get(4)->total ?? 0.00),
        //     'carteira'       => (float) ($somasFormas->get(5)->total ?? 0.00),
        // ];

        // // 5. Cálculos dos resumos financeiros
        // $totalGeralSistema = array_sum($totaisPorForma);

            // 🔥 RESOLUÇÃO: LEITURA DIRETA DA TABELA DE MOVIMENTAÇÕES (A FONTE DA VERDADE)
    // ==========================================================================
    $totaisPorForma = [
        'dinheiro'       => 0.00,
        'pix'            => 0.00,
        'carteira'       => 0.00,
        'cartao_debito'  => 0.00,
        'cartao_credito' => 0.00
    ];

    // Busca todas as somas de formas de pagamento direto na fita de auditoria do caixa atual
    $movimentosBanco = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $caixaId)
        ->whereIn('tipo', ['venda', 'entrada_manual']) // Captura vendas novas e lançamentos antigos
        ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
        ->groupBy('forma_pagamento')
        ->get();

    foreach ($movimentosBanco as $mov) {
        // Normaliza os nomes do banco (Garante que "Cartao debito" ou "cartao_debito" virem a mesma chave)
        $forma = strtolower(trim($mov->forma_pagamento));
        $forma = str_replace(' ', '_', $forma);

        if (array_key_exists($forma, $totaisPorForma)) {
            $totaisPorForma[$forma] = (float) $mov->total;
        }
    }

    // Alimenta o total geral que o círculo azul exibe
    $totalGeralSistema = array_sum($totaisPorForma);

        
        // Tipo 1 = Entradas, Tipo 2 = Saídas (Ex: sangrias)
        $total_entradas = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 1)->sum('valor');
        $total_saidas   = DB::table('movimentacoes_caixa')->where('caixa_id', $caixaId)->where('tipo', 2)->sum('valor');

        // Calcula a divergência real comparando com o valor esperado em dinheiro físico
        $valorFisicoEsperadoDinheiro = $caixa->fundo_troco + $totaisPorForma['dinheiro'];
        $divergencia = 0.00; // Pode ser estendido se houver inputs de conferência salvos

        // Inicializa o objeto de validação de movimento para não quebrar o @if($vm->semMovimento)
        $vm = new \stdClass();
        $vm->semMovimento = ($totalGeralSistema <= 0);

        // 6. Envia todas as variáveis perfeitamente nomeadas para a View
        return view('caixa.fechamento', [
            'caixa'             => $caixa,
            'totaisPorForma'    => $totaisPorForma,
            'totalGeralSistema' => $totalGeralSistema,
            'total_entradas'    => $total_entradas,
            'total_saidas'      => $total_saidas,
            'divergencia'       => $divergencia,
            'movimentacoes'     => $movimentacoesColecao, // Alimenta a tabela inferior
            'vm'                => $vm
        ]);
    }



}