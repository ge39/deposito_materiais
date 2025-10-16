<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\ItensVendaController;
use App\Http\Controllers\FrotaController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\PosVendaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('clientes.index'); // Página inicial
});

// Clientes
Route::resource('clientes', ClienteController::class);

// Fornecedores
Route::resource('fornecedores', FornecedorController::class);

// Funcionários
Route::resource('funcionarios', FuncionarioController::class);

// Usuários (login do sistema)
Route::resource('usuarios', UsuarioController::class);

// Produtos
Route::resource('produtos', ProdutoController::class);

// Vendas
Route::resource('vendas', VendaController::class);

// Itens de Venda
Route::resource('itens_venda', ItensVendaController::class);

// Frota
Route::resource('frotas', FrotaController::class);

// Entregas
Route::resource('entregas', EntregaController::class);

// Pós-venda
Route::resource('pos_venda', PosVendaController::class);
