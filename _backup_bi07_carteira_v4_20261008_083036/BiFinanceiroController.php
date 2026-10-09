<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BiFinanceiroController extends Controller
{
    // BI07_CLIENTES_GERENCIAL_V3

    public function index(Request $request): View
    {
        abort_unless(
            auth()->check() &&
            in_array(auth()->user()->nivel_acesso, ['admin', 'gerente'], true),
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
        $dataHoje = $hoje->toDateString();

        // Ultima compra valida por cliente, historico completo.
        $ultimasCompras = DB::table('vendas')
            ->where('status', 'finalizada')
            ->whereNotNull('cliente_id')
            ->select('cliente_id')
            ->selectRaw('MAX(data_venda) as ultima_compra')
            ->groupBy('cliente_id');

        // Ultimo credito com origem pagamento na conta corrente.
        // Nao presume quitacao com base em vendas confirmadas.
        $ultimosPagamentos = DB::table('cliente_conta_correntes')
            ->where('tipo', 'credito')
            ->where('origem', 'pagamento')
            ->select('cliente_id')
            ->selectRaw('MAX(created_at) as ultimo_pagamento')
            ->groupBy('cliente_id');

        // Menor vencimento de carteira ainda pendente.
        $atrasos = DB::table('pagamentos_venda as p')
            ->join('vendas as v', 'v.id', '=', 'p.venda_id')
            ->where('p.forma_pagamento', 'carteira')
            ->where('p.status', 'Pendente')
            ->where('v.status', '<>', 'cancelada')
            ->whereNotNull('v.cliente_id')
            ->whereNotNull('p.data_vencimento')
            ->whereDate('p.data_vencimento', '<', $dataHoje)
            ->select('v.cliente_id')
            ->selectRaw('MIN(p.data_vencimento) as vencimento_antigo')
            ->groupBy('v.cliente_id');

        // Contexto cadastral dos clientes, sem multiplicar linhas.
        $clientes = DB::table('clientes as c')
            ->leftJoinSub($ultimasCompras, 'uc', function ($join) {
                $join->on('uc.cliente_id', '=', 'c.id');
            })
            ->leftJoinSub($ultimosPagamentos, 'up', function ($join) {
                $join->on('up.cliente_id', '=', 'c.id');
            })
            ->leftJoinSub($atrasos, 'at', function ($join) {
                $join->on('at.cliente_id', '=', 'c.id');
            })
            ->select(
                'c.id',
                'c.nome',
                'uc.ultima_compra',
                'up.ultimo_pagamento',
                'at.vencimento_antigo'
            )
            ->get()
            ->keyBy('id');

        // Fonte principal de faturamento.
        $vendas = DB::table('vendas as v')
            ->where('v.status', 'finalizada')
            ->whereBetween('v.data_venda', [$de, $ate]);

        $faturamento = (float) (clone $vendas)->sum('v.total');
        $quantidadeVendas = (int) (clone $vendas)->count();

        $vendasClientes = (clone $vendas)
            ->select('v.cliente_id')
            ->selectRaw('COUNT(*) as operacoes')
            ->selectRaw('COALESCE(SUM(v.total),0) as total')
            ->groupBy('v.cliente_id')
            ->get();

        // Carteira: posicao atual, sem limitar pelo filtro.
        $pendencias = DB::table('pagamentos_venda as p')
            ->join('vendas as v', 'v.id', '=', 'p.venda_id')
            ->where('p.forma_pagamento', 'carteira')
            ->where('p.status', 'Pendente')
            ->where('v.status', '<>', 'cancelada');

        $pendente = (float) (clone $pendencias)->sum('p.valor');

        $vencido = (float) (clone $pendencias)
            ->whereNotNull('p.data_vencimento')
            ->whereDate('p.data_vencimento', '<', $dataHoje)
            ->sum('p.valor');

        $aVencer = (float) (clone $pendencias)
            ->whereNotNull('p.data_vencimento')
            ->whereDate('p.data_vencimento', '>=', $dataHoje)
            ->sum('p.valor');

        $semVencimento = (float) (clone $pendencias)
            ->whereNull('p.data_vencimento')
            ->sum('p.valor');

        $clientesDevedores = (int) (clone $pendencias)
            ->whereNotNull('v.cliente_id')
            ->distinct()
            ->count('v.cliente_id');

        $agrupaCarteira = function ($base) {
            return $base
                ->select('v.cliente_id')
                ->selectRaw('COUNT(*) as operacoes')
                ->selectRaw('COALESCE(SUM(p.valor),0) as total')
                ->groupBy('v.cliente_id')
                ->get();
        };

        $gruposCarteira = [
            'pendente' => $agrupaCarteira(clone $pendencias),
            'vencido' => $agrupaCarteira(
                (clone $pendencias)
                    ->whereNotNull('p.data_vencimento')
                    ->whereDate('p.data_vencimento', '<', $dataHoje)
            ),
            'a_vencer' => $agrupaCarteira(
                (clone $pendencias)
                    ->whereNotNull('p.data_vencimento')
                    ->whereDate('p.data_vencimento', '>=', $dataHoje)
            ),
            'sem_vencimento' => $agrupaCarteira(
                (clone $pendencias)->whereNull('p.data_vencimento')
            ),
        ];

        // Recebimentos efetivos de carteira no caixa.
        // A estrutura auditada nao comprova um cliente_id
        // diretamente associado a estas movimentacoes.
        $recebimentos = DB::table('movimentacoes_caixa')
            ->where('tipo', 'entrada_pagto_carteira')
            ->whereBetween('data_movimentacao', [$de, $ate]);

        $totalRecebido = (float) (clone $recebimentos)->sum('valor');
        $qtdRecebida = (int) (clone $recebimentos)->count();

        // Saidas registradas: classificacao financeira parcial.
        $tiposSaida = ['saida_manual', 'saida', 'outras_saidas', 'despesa'];

        $saidas = DB::table('movimentacoes_caixa')
            ->whereIn('tipo', $tiposSaida)
            ->whereBetween('data_movimentacao', [$de, $ate]);

        $totalSaidas = (float) (clone $saidas)->sum('valor');
        $qtdSaidas = (int) (clone $saidas)->count();

        $semTipo = DB::table('movimentacoes_caixa')
            ->whereBetween('data_movimentacao', [$de, $ate])
            ->where(function ($q) {
                $q->whereNull('tipo')->orWhere('tipo', '');
            });

        $totalSemTipo = (float) (clone $semTipo)->sum('valor');
        $qtdSemTipo = (int) (clone $semTipo)->count();

        // Adapta agrupamentos reais ao formato gerencial.
        // BI07_CREDITO_MODAL_V1
        // Mesma fonte dos indicadores de credito do ERP.
        // A consulta e somente leitura.

        $creditosModal = DB::table('vw_cliente_credito_resumo')
            ->select(
                'cliente_id',
                'limite_credito',
                'total_usado',
                'credito_disponivel'
            )
            ->get()
            ->keyBy('cliente_id');

        $formatar = function ($registros) use ($clientes, $creditosModal, $dataHoje) {
            return collect($registros)->map(function ($r) use ($clientes, $creditosModal, $dataHoje) {
                $id = $r->cliente_id ?? null;
                $cliente = $id !== null ? $clientes->get($id) : null;
                $credito = $id !== null ? $creditosModal->get($id) : null;

                $dias = 0;
                if ($cliente && $cliente->vencimento_antigo) {
                    $dias = max(0, CarbonImmutable::parse($cliente->vencimento_antigo)
                        ->diffInDays(CarbonImmutable::parse($dataHoje), false));
                }

                return (object) [
                    'cliente' => $cliente->nome
                        ?? ($id === null ? 'Sem cliente identificado' : 'Cliente #'.$id),
                    'cliente_id' => $id,
                    'limite_credito' => $credito !== null
                        ? (float) $credito->limite_credito : null,
                    'gasto_atual' => $credito !== null
                        ? (float) $credito->total_usado : null,
                    'saldo_disponivel' => $credito !== null
                        ? (float) $credito->credito_disponivel : null,
                    'operacoes' => (int) $r->operacoes,
                    'total' => (float) $r->total,
                    'ultima_compra' => $cliente->ultima_compra ?? null,
                    'ultimo_pagamento' => $cliente->ultimo_pagamento ?? null,
                    'dias_atraso' => (int) $dias,
                    'sem_vencimento' => !$cliente || !$cliente->vencimento_antigo,
                ];
            })->sortByDesc('total')->values();
        };

        // Sem relacionamento confirmado, mostrar apenas
        // a parcela nao atribuida a clientes identificaveis.
        $naoVinculado = function ($qtd, $valor) use ($formatar) {
            if ($qtd === 0) {
                return collect();
            }
            return $formatar([(object) [
                'cliente_id' => null,
                'operacoes' => $qtd,
                'total' => $valor,
            ]]);
        };

        $cards = [
            ['Faturamento', $faturamento, true, 'bi-receipt', 'primary', 'faturamento'],
            ['Vendas finalizadas', $quantidadeVendas, false, 'bi-bag-check', 'primary', 'vendas'],
            ['Recebido carteira', $totalRecebido, true, 'bi-wallet2', 'success', 'recebimentos'],
            ['Saidas registradas', $totalSaidas, true, 'bi-arrow-up-circle', 'danger', 'saidas'],
            ['Carteira pendente', $pendente, true, 'bi-credit-card', 'warning', 'pendente'],
            ['Carteira vencida', $vencido, true, 'bi-exclamation-triangle', 'danger', 'vencido'],
            ['Carteira a vencer', $aVencer, true, 'bi-calendar-check', 'info', 'a_vencer'],
            ['Sem vencimento', $semVencimento, true, 'bi-calendar-x', 'warning', 'sem_vencimento'],
            ['Clientes devedores', $clientesDevedores, false, 'bi-people', 'secondary', 'devedores'],
            ['Mov. sem tipo', $qtdSemTipo, false, 'bi-shield-exclamation', 'danger', 'sem_tipo'],
        ];

        $dadosModais = [
            'faturamento' => $formatar($vendasClientes),
            'vendas' => $formatar($vendasClientes),
            'recebimentos' => $naoVinculado($qtdRecebida, $totalRecebido),
            'saidas' => $naoVinculado($qtdSaidas, $totalSaidas),
            'pendente' => $formatar($gruposCarteira['pendente']),
            'vencido' => $formatar($gruposCarteira['vencido']),
            'a_vencer' => $formatar($gruposCarteira['a_vencer']),
            'sem_vencimento' => $formatar($gruposCarteira['sem_vencimento']),
            'devedores' => $formatar($gruposCarteira['pendente']),
            'sem_tipo' => $naoVinculado($qtdSemTipo, $totalSemTipo),
        ];

        return view('bi.financeiro.index', compact(
            'inicio', 'fim', 'cards', 'dadosModais'
        ));
    }
}