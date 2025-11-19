<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui você pode registrar rotas para sua API. Essas rotas são carregadas
| pelo RouteServiceProvider e todas elas serão atribuídas ao grupo "api".
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/produto-dados/{id}', function($id) {
    $p = \App\Models\Produto::with('fornecedor', 'categoria')->find($id);

    return response()->json([
        'preco_venda'  => $p->preco_venda,
        'validade'     => $p->validade_produto,
        'estoque'      => $p->quantidade,
        'unidade'      => $p->unidade,
        'marca'        => $p->marca,
        'fornecedor'   => $p->fornecedor ? $p->fornecedor->nome : null,
        'categoria'    => $p->categoria ? $p->categoria->nome : null,
    ]);
});

