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
use App\Http\Controllers\CepController;
use App\Http\Controllers\DevolucaoController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\PedidoCompraController;


// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// Pedidos de Compra
Route::resource('pedidos', PedidoCompraController::class);
Route::patch('pedidos/{pedido}/status', [PedidoCompraController::class, 'updateStatus'])->name('pedidos.updateStatus');

//Empresas e Filiais
Route::prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/', [EmpresaController::class, 'index'])->name('index');
    Route::get('/create', [EmpresaController::class, 'create'])->name('create');
    Route::post('/', [EmpresaController::class, 'store'])->name('store');
    Route::get('/{empresa}/edit', [EmpresaController::class, 'edit'])->name('edit');
    Route::put('/{empresa}', [EmpresaController::class, 'update'])->name('update');
    Route::put('/{empresa}/desativar', [EmpresaController::class, 'desativar'])->name('desativar');
    Route::put('/{empresa}/ativar', [EmpresaController::class, 'ativar'])->name('ativar');
});

// ✅ Rota personalizada para listar desativadas — FORA do prefixo e ANTES do resource
Route::get('/empresa/desativadas', [EmpresaController::class, 'desativadas'])
    ->name('empresa.desativadas');

// ✅ Resource no final
Route::resource('empresa', EmpresaController::class);



Route::resource('empresa', EmpresaController::class);
// ROTAS DE DEVOLUÇÃO
 Route::prefix('devolucoes')->group(function () {

    // Página inicial de devoluções (busca e filtragem)
    Route::get('/', [DevolucaoController::class, 'index'])->name('devolucoes.index');

    // Buscar vendas para devolução
    Route::get('/buscar', [DevolucaoController::class, 'buscar'])->name('devolucoes.buscar');

    // Listar devoluções pendentes
    Route::get('/pendentes', [DevolucaoController::class, 'pendentes'])->name('devolucoes.pendentes');

    // Listar todas as devoluções
    Route::get('/todas', [DevolucaoController::class, 'todas'])->name('devolucoes.todas');

    // Registrar devolução de um item específico
    Route::get('/registrar/{item_id}', [DevolucaoController::class, 'registrar'])
        ->name('devolucoes.registrar');

        // Gerar cupom de troca
        Route::get('devolucoes/{devolucao}/cupom', [DevolucaoController::class, 'gerarCupom'])
    ->name('devolucoes.cupom');

    // Salvar a devolução
    Route::post('/salvar', [DevolucaoController::class, 'salvar'])->name('devolucoes.salvar');

    // Aprovar ou rejeitar devoluções existentes
    Route::put('/{devolucao}/aprovar', [DevolucaoController::class, 'aprovar'])->name('devolucoes.aprovar');
    Route::put('/{devolucao}/rejeitar', [DevolucaoController::class, 'rejeitar'])->name('devolucoes.rejeitar');

});


// Usuários
Route::put('/users/desativar/{user}', [UserController::class, 'desativar'])->name('users.desativar');

// Clientes
Route::put('/clientes/ativar/{cliente}', [ClienteController::class, 'ativar'])->name('clientes.ativar');
Route::put('/clientes/desativar/{cliente}', [ClienteController::class, 'desativar'])->name('clientes.desativar');

// Funcionários
Route::get('/funcionarios/buscar', [FuncionarioController::class, 'search'])->name('funcionarios.search');
// Buscar funcionário por CPF ajax
Route::get('/buscar-funcionario/{cpf}', [FuncionarioController::class, 'buscarPorCPF']);
Route::put('/funcionarios/desativar/{funcionario}', [FuncionarioController::class, 'desativar'])->name('funcionarios.desativar');
Route::put('/funcionarios/ativar/{funcionario}', [FuncionarioController::class, 'ativar'])->name('funcionarios.ativar');

// Fornecedores
Route::get('/fornecedores/buscar', [FornecedorController::class, 'search'])->name('fornecedores.search');
Route::get('/fornecedores/inativos', [FornecedorController::class, 'inativos'])->name('fornecedores.inativos');
Route::put('/fornecedores/desativar/{fornecedor}', [FornecedorController::class, 'desativar'])->name('fornecedores.desativar');
Route::put('/fornecedores/ativar/{fornecedor}', [FornecedorController::class, 'ativar'])->name('fornecedores.ativar');

// Produtos
Route::get('/produtos/buscar', [ProdutoController::class, 'search'])->name('produtos.search');
Route::get('/produtos/inativos', [ProdutoController::class, 'inativos'])->name('produtos.inativos');
Route::put('/produtos/{produto}/desativar', [ProdutoController::class, 'desativar'])->name('produtos.desativar');
Route::put('/produtos/{produto}/reativar', [ProdutoController::class, 'reativar'])->name('produtos.reativar');

// Marcas
Route::get('/marcas/inativos', [MarcaController::class, 'inativos'])->name('marcas.inativos');
Route::put('/marcas/{marca}/reativar', [MarcaController::class, 'reativar'])->name('marcas.reativar');

// Unidades de Medida
Route::get('/unidades/inativos', [UnidadeMedidaController::class, 'inativos'])->name('unidades.inativos');
Route::put('/unidades/{unidade}/reativar', [UnidadeMedidaController::class, 'reativar'])->name('unidades.reativar');

// CEP
Route::get('/buscar-cep', [CepController::class, 'buscar'])->name('buscar.cep');

// Recursos principais
Route::resources([
    'clientes' => ClienteController::class,
    'fornecedores' => FornecedorController::class,
    'funcionarios' => FuncionarioController::class,
    'vendas' => VendaController::class,
    'itens_venda' => ItensVendaController::class,
    'frotas' => FrotaController::class,
    'entregas' => EntregaController::class,
    'pos_venda' => PosVendaController::class,
    'users' => UserController::class,
    'marcas' => MarcaController::class,
    'unidades' => UnidadeMedidaController::class,
    'produtos' => ProdutoController::class,
]);

// PDV - Ponto de Venda
Route::prefix('pdv')->group(function () {
    Route::get('/', [VendaController::class, 'index'])->name('pdv.index');
    Route::get('/buscar-produto', [VendaController::class, 'buscarProduto'])->name('pdv.buscarProduto');
    Route::post('/adicionar-produto', [VendaController::class, 'adicionarProduto'])->name('pdv.adicionarProduto');
    Route::post('/remover-produto', [VendaController::class, 'removerProduto'])->name('pdv.removerProduto');
    Route::post('/finalizar', [VendaController::class, 'finalizarVenda'])->name('pdv.finalizarVenda');
    Route::get('/cupom/{venda}', [VendaController::class, 'gerarCupom'])->name('pdv.cupom');
});

// Devoluções e Trocas
Route::resource('devolucoes', DevolucaoController::class);
Route::put('devolucoes/{devolucao}/aprovar', [DevolucaoController::class, 'aprovar'])->name('devolucoes.aprovar');
Route::put('devolucoes/{devolucao}/rejeitar', [DevolucaoController::class, 'rejeitar'])->name('devolucoes.rejeitar');

// Lotes
Route::prefix('produtos/{produto_id}/lotes')->group(function () {
    Route::get('/', [LoteController::class, 'index'])->name('lotes.index');
    Route::get('/create', [LoteController::class, 'create'])->name('lotes.create');
    Route::post('/', [LoteController::class, 'store'])->name('lotes.store');
});
