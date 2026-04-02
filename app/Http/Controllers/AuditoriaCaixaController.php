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
    public function index()
    {
        $auditorias = AuditoriaCaixa::with([
                'caixa',
                'usuario'
            ])
            ->withCount([
                'detalhes as divergencias_count' => function ($q) {
                    $q->where('status', 'divergente');
                }
            ])
            ->orderByDesc('data_auditoria')
            ->paginate(20);

        return view('auditoria_caixa.index', compact('auditorias'));
    }

    public function show(AuditoriaCaixa $auditoria)
    {
        $auditoria->load([
            'caixa',
            'caixa.usuario', // operador que abriu o caixa
            'usuario',
            'detalhes',
            'movimentacoesAuditoria.usuario' // quem fez movimentações
        ]);

        /*
        |--------------------------------------------------------------------------
        | Lançamentos manuais
        |--------------------------------------------------------------------------
        */
        $lancamentosManuais = MovimentacaoCaixa::with('usuario')
            ->where('caixa_id', $auditoria->caixa_id)
            ->whereIn('tipo', ['entrada_manual', 'saida_manual'])->whereIn('tipo', ['entrada_manual', 'saida_manual'])
            ->whereIn('tipo', ['saida_manual'])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Pagamentos confirmados do sistema por forma
        |--------------------------------------------------------------------------
        */
        $pagamentosSistema = DB::table('pagamentos_venda as pv')
            ->join('vendas as v', 'pv.venda_id', '=', 'v.id')
            ->where('v.caixa_id', $auditoria->caixa_id)
            ->where('pv.status', 'confirmado')
            ->select(
                'pv.forma_pagamento',
                 DB::raw('SUM(pv.valor) as total')
            )
            ->groupBy('pv.forma_pagamento')
            ->get();

         $total_sangrias = DB::table('movimentacoes_caixa')
        ->where('caixa_id', $auditoria->caixa_id)
        ->where('tipo', 'Saida_manual')
        ->where('forma_pagamento', 'sangria')
        ->sum('valor');

        /*
        |--------------------------------------------------------------------------
        | Movimentações vinculadas à auditoria
        |--------------------------------------------------------------------------
        */
        $movimentacoesAuditoria = MovimentacaoCaixa::with('usuario')
         ->where('caixa_id', $auditoria->caixa_id)
         ->where('valor', '>', 0) // ✅ somente valores maiores que zero
        ->where('tipo', 'auditoria') // 🔹 garantir que é correção de auditoria
        ->orderBy('data_movimentacao')
        ->get();

        return view('auditoria_caixa.show', compact(
            'auditoria',
             'lancamentosManuais',
             'pagamentosSistema',
            'total_sangrias',
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
    public function iniciar(Request $request, Caixa $caixa)
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

            $valoresFisicos = $request->only([
                'dinheiro','pix','carteira','cartao_debito','cartao_credito'
            ]);

            $pagamentos = $caixa->vendas
                ->flatMap->pagamentos
                ->where('status', 'confirmado');

            $totalSistema = $pagamentos->sum('valor');
            $totalFisico  = array_sum($valoresFisicos);
            $diferenca    = $totalFisico - $totalSistema;

            $auditoria = AuditoriaCaixa::create([
                'caixa_id'        => $caixa->id,
                'user_id'         => $userId,
                'codigo_auditoria'=> $this->gerarCodigoAuditoria($caixa->id),
                'total_sistema'   => $totalSistema,
                'total_fisico'    => $totalFisico,
                'diferenca'       => $diferenca,
                'status'          => $diferenca == 0 ? 'concluida' : 'inconsistente',
                'data_auditoria'  => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Vincular movimentações à auditoria
            |--------------------------------------------------------------------------
            */
            MovimentacaoCaixa::where('caixa_id', $caixa->id)
                ->whereNull('auditoria_id')
                ->update([
                    'auditoria_id' => $auditoria->id
                ]);

            $caixa->update([
                'status' => $diferenca == 0 ? 'fechado' : 'inconsistente'
            ]);

            return redirect()
                ->route('fechamento.confirmacao', $caixa->id)
                ->with('success', 'Auditoria realizada com sucesso.');
        });
    }

    private function gerarCodigoAuditoria($caixaId)
    {
        return 'AUD-' . $caixaId . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
    }
}