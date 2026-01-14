<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
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
            return redirect()->back()->withErrors('Terminal não identificado.');
        }

        $caixaAberto = Caixa::where('terminal_id', $terminal->id)
            ->where('status', 'aberto')
            ->first();

        if ($caixaAberto) {
            return redirect()->route('pdv.index')
                            ->with('warning', 'Caixa já aberto para este terminal.');
        }

        $valorFundoAnterior = $request->input('valor_fundo_anterior', 0.00);
        $fundoTroco = $request->input('fundo_troco', 0.00);

        $caixa = Caixa::create([
            'user_id' => $user->id,
            'terminal_id' => $terminal->id,
            'terminal' => $terminal->identificador,
            'valor_fundo_anterior' => $valorFundoAnterior,
            'fundo_troco' => $fundoTroco,
            'divergencia_abertura' => $fundoTroco - $valorFundoAnterior,
            'valor_abertura' => $fundoTroco,
            'status' => 'aberto',
            'observacao' => $request->input('observacao', null),
        ]);

        // Aqui chamamos o CaixaService para registrar a movimentação
        CaixaService::registrarMovimentacaoCaixa([
            'caixa_id' => $caixa->id,
            'user_id'  => $user->id,
            'tipo'     => 'abertura',
            'valor'    => $fundoTroco,
            'observacao' => 'Abertura de caixa',
        ]);

        return redirect()->route('pdv.index')
                        ->with('success', 'Caixa aberto com sucesso.')
                        ->with('caixa_id', $caixa->id);
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
}