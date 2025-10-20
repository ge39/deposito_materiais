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
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\UnidadeMedidaController;
use App\Http\Controllers\LoteController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Recursos principais
Route::resource('clientes', ClienteController::class);
Route::resource('fornecedores', FornecedorController::class);
Route::resource('funcionarios', FuncionarioController::class);
Route::resource('vendas', VendaController::class);
Route::resource('itens_venda', ItensVendaController::class);
Route::resource('frotas', FrotaController::class);
Route::resource('entregas', EntregaController::class);
Route::resource('pos_venda', PosVendaController::class);
Route::resource('users', UserController::class);
Route::resource('marcas', MarcaController::class);
Route::resource('unidades', UnidadeMedidaController::class);

// Usuários
Route::get('/buscar-funcionario/{cpf}', [UserController::class, 'buscarFuncionario']);
Route::put('/users/desativa/{user}', [UserController::class, 'desativa'])->name('users.desativa');

// Clientes
Route::put('/clientes/ativar/{cliente}', [ClienteController::class, 'ativar'])->name('cliente.ativar');
Route::put('/clientes/desativar/{cliente}', [ClienteController::class, 'desativar'])->name('cliente.desativar');

// Funcionários
Route::put('funcionarios/desativar/{funcionario}', [FuncionarioController::class, 'desativa'])->name('funcionarios.desativar');
Route::put('funcionarios/ativar/{funcionario}', [FuncionarioController::class, 'desativa'])->name('funcionarios.ativar');

// Fornecedores
Route::put('fornecedor/desativar/{fornecedor}', [FornecedorController::class, 'desativar'])->name('fornecedores.desativar');
Route::put('fornecedor/ativar/{fornecedor}', [FornecedorController::class, 'ativar'])->name('fornecedores.ativar');

// Rota para buscar CEP
Route::get('/buscar-cep', [App\Http\Controllers\CepController::class, 'buscar'])->name('buscar.cep');

// Produtos
Route::get('produtos/inativos', [ProdutoController::class, 'inativos'])->name('produtos.inativos');
Route::put('produtos/{produto}/reativar', [ProdutoController::class, 'reativar'])->name('produtos.reativar');
Route::put('produtos/{produto}/desativar', [ProdutoController::class, 'desativar'])->name('produtos.desativar');

// Marcas
Route::get('marcas/inativos', [MarcaController::class, 'inativos'])->name('marcas.inativos');
Route::put('marcas/{marca}/reativar', [MarcaController::class, 'reativar'])->name('marcas.reativar');

// Unidades de Medida
Route::get('unidades/inativos', [UnidadeMedidaController::class, 'inativos'])->name('unidades.inativos');
Route::put('unidades/{unidade}/reativar', [UnidadeMedidaController::class, 'reativar'])->name('unidades.reativar');

// Resource do Produto deve vir **depois** das rotas customizadas
Route::resource('produtos', ProdutoController::class);

// PDV - Ponto de Venda
Route::get('/pdv', [VendaController::class, 'index'])->name('pdv.index');
Route::get('/pdv/buscar-produto', [VendaController::class, 'buscarProduto'])->name('pdv.buscarProduto');
Route::post('/pdv/adicionar-produto', [VendaController::class, 'adicionarProduto'])->name('pdv.adicionarProduto');
Route::post('/pdv/remover-produto', [VendaController::class, 'removerProduto'])->name('pdv.removerProduto');
Route::post('/pdv/finalizar', [VendaController::class, 'finalizarVenda'])->name('pdv.finalizarVenda');
Route::get('/pdv/cupom/{venda}', [VendaController::class, 'gerarCupom'])->name('pdv.cupom');

// Lotes
Route::prefix('produtos/{produto_id}/lotes')->group(function () {
    Route::get('/', [LoteController::class, 'index'])->name('lotes.index');
    Route::get('/create', [LoteController::class, 'create'])->name('lotes.create');
    Route::post('/', [LoteController::class, 'store'])->name('lotes.store');


    
});