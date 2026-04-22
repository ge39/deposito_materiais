<?php 
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\MovimentacaoOrcamento;
use Illuminate\Http\Request;

class MovimentacaoOrcamentoDashboardController extends Controller
{
    public function index(Request $request)
{
    $inicio = $request->inicio ?? now()->subDays(7)->format('Y-m-d');
    $fim    = $request->fim ?? now()->format('Y-m-d');
    $tipo   = $request->tipo;

    $query = MovimentacaoOrcamento::query()
        ->whereBetween('created_at', [$inicio.' 00:00:00', $fim.' 23:59:59']);

    if ($tipo) {
        $query->where('tipo', $tipo);
    }

    // KPIs
    $total = (clone $query)->count();

    $cancelamentos = (clone $query)
        ->where('tipo', 'cancelamento')
        ->count();

    $reservas = (clone $query)
        ->where('tipo', 'reserva')
        ->count();

    $taxaCancelamento = $reservas > 0
        ? ($cancelamentos / $reservas) * 100
        : 0;

    // Por tipo
    $porTipo = (clone $query)
        ->select('tipo', DB::raw('count(*) as total'))
        ->groupBy('tipo')
        ->pluck('total', 'tipo');

    // Por dia
    $porDia = (clone $query)
        ->select(DB::raw('DATE(created_at) as data'), DB::raw('count(*) as total'))
        ->groupBy('data')
        ->orderBy('data')
        ->get();

    // Top usuários
    $topUsuarios = (clone $query)
        ->select('user_id', DB::raw('count(*) as total'))
        ->groupBy('user_id')
        ->orderByDesc('total')
        ->with('user')
        ->limit(5)
        ->get();

    // Últimas
    $ultimas = (clone $query)
        ->with(['user', 'orcamento'])
        ->latest()
        ->limit(15)
        ->get();

    return view('dashboard.movimentacoes', compact(
        'total',
        'cancelamentos',
        'reservas',
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
}