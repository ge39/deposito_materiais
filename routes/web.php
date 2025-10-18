<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\ItensVendaController;
use App\Http\Controllers\FrotaController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\PosVendaController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Recursos principais
Route::resource('clientes', ClienteController::class);
Route::resource('fornecedores', FornecedorController::class);
Route::resource('funcionarios', FuncionarioController::class);
Route::resource('produtos', ProdutoController::class);
Route::resource('vendas', VendaController::class);
Route::resource('itens_venda', ItensVendaController::class);
Route::resource('frotas', FrotaController::class);
Route::resource('entregas', EntregaController::class);
Route::resource('pos_venda', PosVendaController::class);
Route::resource('users', UserController::class);

Route::get('/buscar-funcionario/{cpf}', [UserController::class, 'buscarFuncionario']);
Route::put('funcionarios/desativa/{funcionario}', [FuncionarioController::class, 'desativa'])->name('funcionarios.desativa');
Route::get('/buscar-funcionario/{cpf}', [UserController::class, 'buscarFuncionario']);
// Rota para desativar usuário
Route::put('/users/desativa/{user}', [UserController::class, 'desativa'])->name('users.desativa');
