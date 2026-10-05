<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use App\Models\Produto;

class FornecedorProdutoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if (! in_array($user->nivel_acesso, ['admin', 'gerente'])) {
                abort(403, 'Acesso negado!');
            }

            return $next($request);
        });
    }

    public function index(Fornecedor $fornecedor)
    {
        $produtos = Produto::query()
            ->with('categoria')
            ->where('fornecedor_id', $fornecedor->id)
            ->orderBy('nome')
            ->get();

        $totalProdutos = $produtos->count();

        $produtosAtivos = $produtos
            ->where('ativo', true)
            ->count();

        return view(
            'fornecedores.produtos',
            compact(
                'fornecedor',
                'produtos',
                'totalProdutos',
                'produtosAtivos'
            )
        );
    }
}