<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\PosVendaController;
use App\Http\Controllers\PedidoCompraController;

// Página inicial
Route::get('/', function () {
    return view('welcome');
});

// Clientes
Route::prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::get('/create', [ClienteController::class, 'create'])->name('create');
    Route::post('/store', [ClienteController::class, 'store'])->name('store');
    Route::get('/{id}', [ClienteController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ClienteController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ClienteController::class, 'update'])->name('update');
    Route::delete('/{id}', [ClienteController::class, 'destroy'])->name('destroy');
});

// Funcionários
Route::prefix('funcionarios')->name('funcionarios.')->group(function () {
    Route::get('/', [FuncionarioController::class, 'index'])->name('index');
    Route::get('/create', [FuncionarioController::class, 'create'])->name('create');
    Route::post('/store', [FuncionarioController::class, 'store'])->name('store');
    Route::get('/{id}', [FuncionarioController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [FuncionarioController::class, 'edit'])->name('edit');
    Route::put('/{id}', [FuncionarioController::class, 'update'])->name('update');
    Route::delete('/{id}', [FuncionarioController::class, 'destroy'])->name('destroy');
});

// Categorias
Route::prefix('categorias')->name('categorias.')->group(function () {
    Route::get('/', [CategoriaController::class, 'index'])->name('index');
    Route::get('/create', [CategoriaController::class, 'create'])->name('create');
    Route::post('/store', [CategoriaController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [CategoriaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CategoriaController::class, 'update'])->name('update');
    Route::delete('/{id}', [CategoriaController::class, 'destroy'])->name('destroy');
});

// Fornecedores
Route::prefix('fornecedores')->name('fornecedores.')->group(function () {
    Route::get('/', [FornecedorController::class, 'index'])->name('index');
    Route::get('/create', [FornecedorController::class, 'create'])->name('create');
    Route::post('/store', [FornecedorController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [FornecedorController::class, 'edit'])->name('edit');
    Route::put('/{id}', [FornecedorController::class, 'update'])->name('update');
    Route::delete('/{id}', [FornecedorController::class, 'destroy'])->name('destroy');
});

// Produtos
Route::prefix('produtos')->name('produtos.')->group(function () {
    Route::get('/', [ProdutoController::class, 'index'])->name('index');
    Route::get('/create', [ProdutoController::class, 'create'])->name('create');
    Route::post('/store', [ProdutoController::class, 'store'])->name('store');
    Route::get('/{id}', [ProdutoController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ProdutoController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ProdutoController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProdutoController::class, 'destroy'])->name('destroy');
});

// Vendas
Route::prefix('vendas')->name('vendas.')->group(function () {
    Route::get('/', [VendaController::class, 'index'])->name('index');
    Route::get('/create', [VendaController::class, 'create'])->name('create');
    Route::post('/store', [VendaController::class, 'store'])->name('store');
    Route::get('/{id}', [VendaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [VendaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [VendaController::class, 'update'])->name('update');
    Route::delete('/{id}', [VendaController::class, 'destroy'])->name('destroy');
});

// Pós-Venda

Route::prefix('pos-vendas')->name('pos_vendas.')->group(function () {
    Route::get('/', [PosVendaController::class, 'index'])->name('index');
    Route::get('/create/{venda_id}', [PosVendaController::class, 'create'])->name('create');
    Route::post('/store', [PosVendaController::class, 'store'])->name('store');
    Route::get('/{id}', [PosVendaController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PosVendaController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PosVendaController::class, 'update'])->name('update');
    Route::delete('/{id}', [PosVendaController::class, 'destroy'])->name('destroy');
});


// Pedidos de Compras
Route::prefix('pedidos-compras')->name('pedidos_compras.')->group(function () {
    Route::get('/', [PedidoCompraController::class, 'index'])->name('index');
    Route::get('/create', [PedidoCompraController::class, 'create'])->name('create');
    Route::post('/store', [PedidoCompraController::class, 'store'])->name('store');
    Route::get('/{id}', [PedidoCompraController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PedidoCompraController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PedidoCompraController::class, 'update'])->name('update');
});
