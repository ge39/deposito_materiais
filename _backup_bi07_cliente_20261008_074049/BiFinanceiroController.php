<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiFinanceiroController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            auth()->check()
            && in_array(
                auth()->user()->nivel_acesso,
                ['admin', 'gerente'],
                true
            ),
            403
        );

        $dados = $request->validate([
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $hoje = CarbonImmutable::today();

        $inicio = isset($dados['data_inicio'])
            ? CarbonImmutable::parse($dados['data_inicio'])
            : $hoje->startOfMonth();

        $fim = isset($dados['data_fim'])
            ? CarbonImmutable::parse($dados['data_fim'])
            : $hoje;

        if ($fim->lt($inicio)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        $de = $inicio->startOfDay()->toDateTimeString();
        $ate = $fim->endOfDay()->toDateTimeString();

        // Faturamento: somente vendas finalizadas.
        $vendas = DB::table('vendas')
            ->where('status', 'finalizada')
            ->whereBetween('data_venda', [$de, $ate]);

        $faturamento = (float) (clone $vendas)->sum('total');
        $quantidadeVendas = (clone $vendas)->count();

        // Recebimento real de carteira registrado no caixa.
        $carteiraBase = DB::table('movimentacoes_caixa')
            ->where('tipo', 'entrada_pagto_carteira')
            ->whereBetween('data_movimentacao', [$de, $ate]);

        $recebidoCarteira = (float) (clone $carteiraBase)
            ->sum('valor');

        $carteiraPorForma = (clone $carteiraBase)
            ->selectRaw("COALESCE(NULLIF(forma_pagamento,''),'Nao informado') as forma")
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('SUM(valor) as total')
            ->groupBy('forma_pagamento')
            ->orderByDesc('total')
            ->get();

        // Saidas operacionais classificadas.
        // Sangrias ficam separadas.
        $tiposSaida = [
            'saida_manual',
            'saida',
            'outras_saidas',
            'despesa',
        ];

        $saidasBase = DB::table('movimentacoes_caixa')
            ->whereIn('tipo', $tiposSaida)
            ->whereBetween('data_movimentacao', [$de, $ate]);

        $saidas = (float) (clone $saidasBase)->sum('valor');

        $saidasPorTipo = (clone $saidasBase)
            ->select('tipo')
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('SUM(valor) as total')
            ->groupBy('tipo')
            ->get();

        // Carteira pendente: posicao atual, sem filtro de
        // periodo de venda. Exclui vendas canceladas.
        $pendencias = DB::table('pagamentos_venda as p')
            ->join('vendas as v', 'v.id', '=', 'p.venda_id')
            ->where('p.forma_pagamento', 'carteira')
            ->where('p.status', 'Pendente')
            ->where('v.status', '<>', 'cancelada');

        $carteiraPendente = (float) (clone $pendencias)
            ->sum('p.valor');

        $carteiraVencida = (float) (clone $pendencias)
            ->whereNotNull('p.data_vencimento')
            ->whereDate('p.data_vencimento', '<', $hoje->toDateString())
            ->sum('p.valor');

        $carteiraAVencer = (float) (clone $pendencias)
            ->whereNotNull('p.data_vencimento')
            ->whereDate('p.data_vencimento', '>=', $hoje->toDateString())
            ->sum('p.valor');

        $carteiraSemVencimento = (float) (clone $pendencias)
            ->whereNull('p.data_vencimento')
            ->sum('p.valor');

        $clientesDevedores = (clone $pendencias)
            ->whereNotNull('v.cliente_id')
            ->distinct()
            ->count('v.cliente_id');

        $devedores = (clone $pendencias)
            ->leftJoin('clientes as c', 'c.id', '=', 'v.cliente_id')
            ->select(
                'v.cliente_id',
                'c.nome as cliente',
                'p.data_vencimento'
            )
            ->selectRaw('SUM(p.valor) as saldo')
            ->groupBy(
                'v.cliente_id',
                'c.nome',
                'p.data_vencimento'
            )
            ->orderByRaw(
                'CASE WHEN p.data_vencimento IS NULL THEN 1 ELSE 0 END'
            )
            ->orderBy('p.data_vencimento')
            ->limit(100)
            ->get();

        // Alertas de classificacao no periodo.
        $semClassificacao = DB::table('movimentacoes_caixa')
            ->whereBetween('data_movimentacao', [$de, $ate])
            ->where(function ($q) {
                $q->whereNull('tipo')->orWhere('tipo', '');
            })
            ->selectRaw('COUNT(*) as quantidade')
            ->selectRaw('COALESCE(SUM(valor),0) as valor')
            ->first();

        // Visao diaria de movimentacoes classificadas.
        // Nao soma vendas de carteira como dinheiro recebido.
        // Nao soma sangria como despesa.
        $fluxo = DB::table('movimentacoes_caixa')
            ->whereBetween('data_movimentacao', [$de, $ate])
            ->where(function ($q) {
                $q->where(function ($interno) {
                    $interno->where('tipo', 'venda')
                        ->where('forma_pagamento', '<>', 'carteira');
                })
                ->orWhere('tipo', 'entrada_pagto_carteira')
                ->orWhereIn('tipo', [
                    'saida_manual', 'saida',
                    'outras_saidas', 'despesa'
                ]);
            })
            ->selectRaw('DATE(data_movimentacao) as data')
            ->selectRaw("
                SUM(CASE
                    WHEN tipo = 'entrada_pagto_carteira'
                      OR (tipo = 'venda' AND forma_pagamento <> 'carteira')
                    THEN valor ELSE 0 END) as entradas
            ")
            ->selectRaw("
                SUM(CASE
                    WHEN tipo IN (
                        'saida_manual','saida','outras_saidas','despesa'
                    )
                    THEN valor ELSE 0 END) as saidas
            ")
            ->groupByRaw('DATE(data_movimentacao)')
            ->orderBy('data')
            ->get();

        // BI07_MODAL_DETALHES_V1
        // Consultas analiticas exclusivamente de leitura.
        // Limite de 200 linhas por modal.

        $vendasDetalhes = DB::table('vendas as v')
            ->leftJoin('clientes as c', 'c.id', '=', 'v.cliente_id')
            ->where('v.status', 'finalizada')
            ->whereBetween('v.data_venda', [$de, $ate])
            ->select('v.id', 'v.data_venda', 'v.total',
                'c.nome as cliente')
            ->orderByDesc('v.data_venda')
            ->limit(200)
            ->get();

        $recebimentosDetalhes = (clone $carteiraBase)
            ->select('id', 'data_movimentacao',
                'caixa_id', 'forma_pagamento', 'valor')
            ->orderByDesc('data_movimentacao')
            ->limit(200)
            ->get();

        $saidasDetalhes = (clone $saidasBase)
            ->select('id', 'data_movimentacao', 'tipo',
                'forma_pagamento', 'valor', 'observacao')
            ->orderByDesc('data_movimentacao')
            ->limit(200)
            ->get();

        $pendenciasDetalhes = (clone $pendencias)
            ->leftJoin('clientes as c', 'c.id', '=', 'v.cliente_id')
            ->select('p.id', 'p.venda_id',
                'p.valor', 'p.data_vencimento',
                'c.nome as cliente')
            ->orderBy('p.data_vencimento')
            ->limit(200)
            ->get();

        $semTipoDetalhes = DB::table('movimentacoes_caixa')
            ->whereBetween('data_movimentacao', [$de, $ate])
            ->where(function ($q) {
                $q->whereNull('tipo')->orWhere('tipo', '');
            })
            ->select('id', 'data_movimentacao',
                'caixa_id', 'valor', 'observacao')
            ->orderByDesc('data_movimentacao')
            ->limit(200)
            ->get();
        return view('bi.financeiro.index', compact(
            'inicio', 'fim', 'faturamento',
            'quantidadeVendas', 'recebidoCarteira',
            'carteiraPorForma', 'saidas', 'saidasPorTipo',
            'carteiraPendente', 'carteiraVencida',
            'carteiraAVencer', 'carteiraSemVencimento',
            'clientesDevedores', 'devedores',
            'semClassificacao', 'fluxo',
            'vendasDetalhes',
            'recebimentosDetalhes',
            'saidasDetalhes',
            'pendenciasDetalhes',
            'semTipoDetalhes'
        ));
    }
}