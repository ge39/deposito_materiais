<?php

namespace App\Http\Controllers;

use App\Models\MovimentacaoOrcamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimentacaoOrcamentoDashboardController extends Controller
{
    public function index(Request $request)
    {
        $inicio = $request->inicio ?? now()->subDays(7)->format('Y-m-d');
        $fim    = $request->fim ?? now()->format('Y-m-d');
        $tipo   = $request->tipo;

        $baseQuery = MovimentacaoOrcamento::query()
            ->whereBetween('created_at', [
                $inicio . ' 00:00:00',
                $fim . ' 23:59:59'
            ])
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo));

        // 🔥 CARREGA DADOS UMA ÚNICA VEZ
        $movimentacoes = (clone $baseQuery)
            ->with(['user', 'orcamento', 'item.produto'])
            ->latest()
            ->get();

        // ================= KPI (sem requery desnecessário)
        $total = $movimentacoes->count();

        $cancelamentos = $movimentacoes->where('tipo', 'cancelamento')->count();

        $reservas = $movimentacoes->where('tipo', 'aguardando_estoque')->count();

        $aprovados = $movimentacoes->where('tipo', 'aprovado')->count();

        $taxaCancelamento = $reservas > 0
            ? round(($cancelamentos / $reservas) * 100, 2)
            : 0;

        // ================= AGRUPAMENTOS (em memória = rápido)
        $porTipo = $movimentacoes->groupBy('tipo')->map->count();

        $porDia = $movimentacoes->groupBy(fn ($m) => $m->created_at->format('Y-m-d'))
            ->map->count()
            ->sortKeys();

        $topUsuarios = $movimentacoes->groupBy('user_id')
            ->map(fn ($items) => [
                'user' => $items->first()->user,
                'total' => $items->count()
            ])
            ->sortByDesc('total')
            ->take(5);

        // últimas 15
        $ultimas = $movimentacoes->take(15);

        return view('dashboard.movimentacoes', compact(
            'total',
            'cancelamentos',
            'reservas',
            'aprovados',
            'taxaCancelamento',
            'porTipo',
            'porDia',
            'topUsuarios',
            'ultimas',
            'inicio',
            'fim',
            'tipo'
        ));
    }

    public function data(Request $request)
    {
        $inicio = $request->inicio ?? now()->subDays(7)->format('Y-m-d');
        $fim    = $request->fim ?? now()->format('Y-m-d');
        $tipo   = $request->tipo;

        $query = MovimentacaoOrcamento::query()
            ->whereBetween('created_at', [
                $inicio . ' 00:00:00',
                $fim . ' 23:59:59'
            ])
            ->when($tipo, fn($q) => $q->where('tipo', $tipo));

        $mov = $query->with(['user', 'item.produto'])->get();

        return response()->json([
            'kpis' => [
                'total' => $mov->count(),
                'aprovados' => $mov->where('tipo', 'aprovado')->count(),
                'cancelamentos' => $mov->where('tipo', 'cancelamento')->count(),
                'reservas' => $mov->where('tipo', 'aguardando_estoque')->count(),
            ],

            'porTipo' => $mov->groupBy('tipo')->map->count(),
            'porDia' => $mov->groupBy(fn($m) => $m->created_at->format('Y-m-d'))
                ->map->count()
                ->sortKeys(),

            'ultimas' => $mov->take(15)->values(),
        ]);
    }
}