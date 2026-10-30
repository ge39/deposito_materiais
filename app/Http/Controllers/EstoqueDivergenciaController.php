<?php

namespace App\Http\Controllers;

use App\Models\EstoqueDivergencia;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EstoqueDivergenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = EstoqueDivergencia::query();

        if ($request->filled('produto')) {
            $query->whereHas('produto', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->produto . '%');
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('created_at', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('created_at', '<=', $request->data_final);
        }

        // ESTDIV01_HISTORICO
        // Paginar produtos depois de aplicar os filtros.
        $divergencias = (clone $query)
            ->select('produto_id')
            ->selectRaw('MAX(id) AS ultima_ocorrencia_id')
            ->groupBy('produto_id')
            ->orderByDesc('ultima_ocorrencia_id')
            ->paginate(20)
            ->withQueryString();

        // Buscar todas as ocorrencias filtradas dos produtos
        // da pagina, evitando totais parciais.
        $produtosNaPagina = $divergencias
            ->getCollection()
            ->pluck('produto_id');

        $registrosPorProduto = (clone $query)
            ->with([
                'produto',
                'venda',
                'caixa',
                'usuario',
            ])
            ->where(function ($filtro) use ($produtosNaPagina) {
                $filtro->whereIn(
                    'produto_id',
                    $produtosNaPagina
                        ->filter(fn ($id) => $id !== null)
                        ->values()
                        ->all()
                );

                if ($produtosNaPagina->contains(null)) {
                    $filtro->orWhereNull('produto_id');
                }
            })
            ->orderByDesc('id')
            ->get()
            ->groupBy('produto_id');

        // Cada elemento da paginacao passa a representar
        // um produto e todas as suas ocorrencias filtradas.
        $divergencias->setCollection(
            $divergencias->getCollection()->map(
                fn ($produto) => $registrosPorProduto->get(
                    $produto->produto_id,
                    collect()
                )
            )
        );

        return view('estoque_divergencias.index', compact('divergencias'));
    }

    public function show($id)
    {
        $divergencia = EstoqueDivergencia::with([
            'produto',
            'venda',
            'caixa',
            'usuario'
        ])->findOrFail($id);

        return view('estoque_divergencias.show', compact('divergencia'));
    }

    private function registrarDivergenciaEstoque(
        int $produtoId,
        int $vendaId,
        ?int $caixaId,
        float $quantidadeSolicitada,
        float $quantidadeAtendida
        ): void {
        $diferenca = $quantidadeSolicitada - $quantidadeAtendida;

        if ($diferenca <= 0) {
            return;
        }

        EstoqueDivergencia::create([
            'produto_id' => $produtoId,
            'venda_id' => $vendaId,
            'caixa_id' => $caixaId,
            'quantidade_solicitada' => $quantidadeSolicitada,
            'quantidade_atendida' => $quantidadeAtendida,
            'diferenca' => $diferenca,
            'tipo' => 'venda',
            'observacao' => 'Venda PDV finalizada com quantidade acima do estoque virtual.',
            'usuario_id' => auth()->id(),
            'created_at' => now(),
        ]);
    }

    public function pdf(Request $request)
    {
        $query = EstoqueDivergencia::with([
            'produto',
            'usuario'
        ]);

        if ($request->filled('produto')) {
            $query->whereHas('produto', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->produto . '%');
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('created_at', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('created_at', '<=', $request->data_final);
        }

        $divergencias = $query
            ->orderBy('produto_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy('produto_id');

        $empresa = Empresa::first();

        $pdf = Pdf::loadView('estoque_divergencias.pdf', compact(
            'divergencias',
            'empresa'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('relatorio-divergencias-estoque.pdf');
    }
}