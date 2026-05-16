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
    
    public function fecharCaixa(Request $request, $caixaId)
    {
        // Busca o caixa e valida se está aberto
        $caixa = Caixa::findOrFail($caixaId);
        
        // Soma os valores por forma de pagamento associados a este caixa hoje
        $faturamento = DB::table('pagamentos_vendas') // Substitua pelo nome real da sua tabela de pagamentos
            ->select('forma_pagamento', DB::raw('SUM(valor) as total'))
            ->where('caixa_id', $caixaId)
            ->whereDate('created_at', now()->toDateString())
            ->groupBy('forma_pagamento')
            ->get()
            ->keyBy('forma_pagamento');

        // Monta o resumo financeiro formatado
        $resumo = [
            'dinheiro'       => $faturamento->get('dinheiro')->total ?? 0.00,
            'pix'            => $faturamento->get('pix')->total ?? 0.00,
            'cartao_credito' => $faturamento->get('cartao_credito')->total ?? 0.00,
            'cartao_debito'  => $faturamento->get('cartao_debito')->total ?? 0.00,
            'carteira'       => $faturamento->get('carteira')->total ?? 0.00,
        ];

        $totalGeral = array_sum($resumo);

        return view('caixa.fechamento', compact('caixa', 'resumo', 'totalGeral'));
    }

}