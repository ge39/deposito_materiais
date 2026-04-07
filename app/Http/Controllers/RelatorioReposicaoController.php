<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 use PDF; // Certifique-se de importar o facade

class RelatorioReposicaoController extends Controller
{
    public function index(Request $request)
    {
        $dados = $this->buscarDados($request);
        $resumo = $this->resumoProdutos($request);
        $totais = $this->totaisGerais($request);

        return view('relatorios.reposicao', compact('dados', 'resumo', 'totais'));
    }

    /**
     * 📊 LISTAGEM PRINCIPAL (AGRUPADO POR PRODUTO)
     */
    private function buscarDados(Request $request)
    {
        // 🔹 SUBQUERY: estoque real por produto (lotes)
        $estoqueSub = DB::table('lotes')
            ->select(
                'produto_id',
                DB::raw('SUM(quantidade_disponivel) as estoque_disponivel')
            )
            ->where('status', 1)
            ->groupBy('produto_id');

        $query = DB::table('itens_orcamento as io')
            ->join('orcamentos as o', 'o.id', '=', 'io.orcamento_id')
            ->join('produtos as p', 'p.id', '=', 'io.produto_id')

            // 🔥 JOIN COM ESTOQUE REAL
            ->leftJoinSub($estoqueSub, 'estoque', function ($join) {
                $join->on('estoque.produto_id', '=', 'p.id');
            })

            // unidade de medida
            ->leftJoin('unidades_medida as um', 'um.id', '=', 'p.unidade_medida_id')
            ->where('io.quantidade_pendente', '>', 0)
            ->whereIn('io.status', ['parcial', 'indisponivel'])
           

            ->select(
                'p.id as produto_id',
                'p.nome as produto_nome',
                'p.codigo_barras',
                'p.estoque_minimo',
                'um.sigla as unidade',

                // ✅ corrigido aqui
                DB::raw('GROUP_CONCAT(DISTINCT o.codigo_orcamento ORDER BY o.codigo_orcamento SEPARATOR ", ") as codigos_orcamento'),

                // previsão por item resumida (já que está agrupando)
                DB::raw('MIN(io.previsao_entrega) as previsao_entrega'),
                DB::raw('COALESCE(estoque.estoque_disponivel, 0) as estoque_disponivel'),

                DB::raw('SUM(io.quantidade) as total_quantidade'),
                DB::raw('SUM(io.quantidade_atendida) as total_atendida'),
                DB::raw('SUM(io.quantidade_pendente) as total_pendente'),
                DB::raw('SUM(io.subtotal) as valor_total'),

                DB::raw('
                    CASE 
                        WHEN SUM(io.quantidade_pendente) > COALESCE(estoque.estoque_disponivel,0)
                        THEN SUM(io.quantidade_pendente) - COALESCE(estoque.estoque_disponivel,0)
                        ELSE 0
                    END as necessidade_reposicao
                ')
            )

            ->groupBy(
                'p.id',
                'p.nome',
                'p.codigo_barras',
                'p.estoque_minimo',
                'um.sigla',
                'estoque.estoque_disponivel'
            );

        // 🔍 FILTROS
        $this->aplicarFiltros($query, $request);

         // 🔥 ORDENAR PELA DATA DE PREVISÃO
        $query->orderBy('io.previsao_entrega', 'asc'); // asc = mais próximo primeiro

        return $query
            ->havingRaw('SUM(io.quantidade_pendente) > 0')
            ->orderByDesc('total_pendente')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * 🔍 FILTROS
     */
    private function aplicarFiltros($query, Request $request)
    {
       if ($request->filled('data_inicio')) {
            $query->whereDate('io.previsao_entrega', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('io.previsao_entrega', '<=', $request->data_fim);
        }

        if ($request->filled('produto')) {
            $query->where('p.nome', 'like', $request->produto . '%');
        }

        if ($request->filled('codigo_barras')) {
            $query->where('p.codigo_barras', 'like', '%' . $request->codigo_barras . '%');
        }
    }

    /**
     * 📊 RESUMO POR PRODUTO
     */
    private function resumoProdutos(Request $request)
    {
        $query = DB::table('itens_orcamento as io')
            ->join('produtos as p', 'p.id', '=', 'io.produto_id')
            ->leftJoin('unidades_medida as um', 'um.id', '=', 'p.unidade_medida_id')
            ->join('orcamentos as o', 'o.id', '=', 'io.orcamento_id')

            ->where('io.quantidade_pendente', '>', 0)
            ->whereIn('io.status', ['parcial', 'indisponivel'])

            ->select(
                'p.id',
                'p.nome',
                'p.quantidade_estoque',
                'p.estoque_minimo',
                'um.sigla as unidade',

                // ✅ ADICIONE ISSO
                DB::raw('GROUP_CONCAT(DISTINCT o.id ORDER BY o.id SEPARATOR ",") as ids_orcamento'),
                DB::raw('GROUP_CONCAT(DISTINCT o.codigo_orcamento ORDER BY o.codigo_orcamento SEPARATOR ",") as codigos_orcamento'),

                DB::raw('SUM(io.quantidade_pendente) as total_pendente'),
                DB::raw('(SUM(io.quantidade_pendente) - p.quantidade_estoque) as necessidade')
            )
            ->groupBy(
                'p.id',
                'p.nome',
                'p.quantidade_estoque',
                'p.estoque_minimo',
                'um.sigla'
            );

        $this->aplicarFiltros($query, $request);

        return $query->orderByDesc('necessidade')->get();
    }

    /**
     * 💰 TOTAIS GERAIS
     */
    private function totaisGerais(Request $request)
    {
        $query = DB::table('itens_orcamento as io')
            ->join('orcamentos as o', 'o.id', '=', 'io.orcamento_id')
            ->join('produtos as p', 'p.id', '=', 'io.produto_id') // ✅ ADICIONA ISSO

            ->where('io.quantidade_pendente', '>', 0)
            ->whereIn('io.status', ['parcial', 'indisponivel']);

        $this->aplicarFiltros($query, $request);

        return $query->select(
            DB::raw('SUM(io.quantidade_pendente) as total_pendente'),
            DB::raw('SUM(io.subtotal) as valor_total')
        )->first();
    }

    public function gerarPdf(Request $request)
    {
        $dados = $this->buscarDados($request);
        $resumo = $this->resumoProdutos($request);
        $totais = $this->totaisGerais($request);

        // 🔥 pega orientação da URL
        $orientacao = $request->get('orientacao', 'portrait');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'relatorios.reposicao_pdf',
            compact('dados', 'resumo', 'totais')
        );

        // 🔥 aplica orientação dinamicamente
        $pdf->setPaper('A4', $orientacao);

        return $pdf->stream('relatorio_reposicao.pdf');
    }
}