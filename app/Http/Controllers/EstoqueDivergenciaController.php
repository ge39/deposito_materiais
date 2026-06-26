<?php

namespace App\Http\Controllers;

use App\Models\EstoqueDivergencia;
use App\Models\Lote;
use Illuminate\Http\Request;

class EstoqueDivergenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = EstoqueDivergencia::with([
            'produto',
            'venda',
            'caixa',
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

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $divergencias = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

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
}