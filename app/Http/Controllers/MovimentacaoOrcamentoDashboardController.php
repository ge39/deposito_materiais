<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use Illuminate\Http\Request;

class MovimentacaoOrcamentoDashboardController extends Controller
{
    /**
     * Query base de ORÇAMENTOS (não mais movimentações)
     */
    private function baseQuery($inicio, $fim, $tipo = null, $orcamentoId = null)
    {
        return Orcamento::query()

            ->when($orcamentoId, fn ($q) =>
                $q->where('id', $orcamentoId)
            )

            ->whereHas('movimentacoes', function ($q) use ($inicio, $fim, $tipo) {
                $q->whereBetween('created_at', [
                    $inicio . ' 00:00:00',
                    $fim . ' 23:59:59'
                ])
                ->when($tipo, fn ($q) => $q->where('tipo', $tipo));
            });
    }

    public function index(Request $request)
    {
        $inicio       = $request->inicio ?? now()->subDays(7)->format('Y-m-d');
        $fim          = $request->fim ?? now()->format('Y-m-d');
        $tipo         = $request->tipo;
        $orcamentoId  = $request->orcamento_id;

        // 🔥 Agora a base é ORÇAMENTO
        $orcamentos = $this->baseQuery($inicio, $fim, $tipo, $orcamentoId)

            ->with([
                'movimentacoes' => function ($q) use ($inicio, $fim, $tipo) {
                    $q->whereBetween('created_at', [
                        $inicio . ' 00:00:00',
                        $fim . ' 23:59:59'
                    ])
                    ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
                    ->with(['user', 'item.produto'])
                    ->latest();
                }
            ])

            ->latest()
            ->get();

        // ================= KPI (AGORA CORRETOS)

        $totalOrcamentos = $orcamentos->count();

        $orcamentosAprovados = $orcamentos->filter(
            fn ($orc) => $orc->movimentacoes->contains('tipo', 'aprovado')
        )->count();

        $orcamentosCancelados = $orcamentos->filter(
            fn ($orc) => $orc->movimentacoes->contains('tipo', 'cancelamento')
        )->count();

        $orcamentosReservas = $orcamentos->filter(
            fn ($orc) => $orc->movimentacoes->contains('tipo', 'aguardando_estoque')
        )->count();

        $taxaCancelamento = $orcamentosReservas > 0
            ? round(($orcamentosCancelados / $orcamentosReservas) * 100, 2)
            : 0;

        // ================= AGRUPAMENTOS

        // por tipo (nível orçamento)
        $porTipo = $orcamentos
            ->map(fn ($orc) => $orc->movimentacoes->pluck('tipo')->unique())
            ->flatten()
            ->countBy();

        // por dia (ainda baseado nas movimentações)
        $porDia = $orcamentos
            ->flatMap->movimentacoes
            ->groupBy(fn ($m) => $m->created_at->format('Y-m-d'))
            ->map->count()
            ->sortKeys();

        // top usuários (continua válido)
        $topUsuarios = $orcamentos
            ->flatMap->movimentacoes
            ->groupBy('user_id')
            ->map(fn ($items) => [
                'user'  => $items->first()->user,
                'total' => $items->count()
            ])
            ->sortByDesc('total')
            ->take(5);

        // últimas movimentações (detalhe)
        $ultimas = $orcamentos
            ->flatMap->movimentacoes
            ->sortByDesc('created_at')
            ->take(15);

        // lista para filtro
        $listaOrcamentos = Orcamento::select('id')->latest()->take(100)->get();

        return view('dashboard.movimentacoes', compact(
            'totalOrcamentos',
            'orcamentosAprovados',
            'orcamentosCancelados',
            'orcamentosReservas',
            'taxaCancelamento',
            'porTipo',
            'porDia',
            'topUsuarios',
            'ultimas',
            'orcamentos', // 🔥 importante para view agrupada
            'inicio',
            'fim',
            'tipo',
            'orcamentoId',
            'listaOrcamentos'
        ));
    }

    public function data(Request $request)
    {
        $inicio       = $request->inicio ?? now()->subDays(7)->format('Y-m-d');
        $fim          = $request->fim ?? now()->format('Y-m-d');
        $tipo         = $request->tipo;
        $orcamentoId  = $request->orcamento_id;

        $orcamentos = $this->baseQuery($inicio, $fim, $tipo, $orcamentoId)
            ->with(['movimentacoes' => function ($q) use ($inicio, $fim, $tipo) {
                $q->whereBetween('created_at', [
                    $inicio . ' 00:00:00',
                    $fim . ' 23:59:59'
                ])
                ->when($tipo, fn ($q) => $q->where('tipo', $tipo));
            }])
            ->get();

        $mov = $orcamentos->flatMap->movimentacoes;

        return response()->json([
            'kpis' => [
                'total_orcamentos' => $orcamentos->count(),
                'aprovados' => $orcamentos->filter(fn($o) => $o->movimentacoes->contains('tipo','aprovado'))->count(),
                'cancelamentos' => $orcamentos->filter(fn($o) => $o->movimentacoes->contains('tipo','cancelamento'))->count(),
                'reservas' => $orcamentos->filter(fn($o) => $o->movimentacoes->contains('tipo','aguardando_estoque'))->count(),
            ],

            'porTipo' => $orcamentos
                ->map(fn ($o) => $o->movimentacoes->pluck('tipo')->unique())
                ->flatten()
                ->countBy(),

            'porDia' => $mov
                ->groupBy(fn ($m) => $m->created_at->format('Y-m-d'))
                ->map->count()
                ->sortKeys(),

            'ultimas' => $mov
                ->sortByDesc('created_at')
                ->take(15)
                ->values(),
        ]);
    }
}