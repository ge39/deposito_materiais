<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    DashboardController,
    ClienteController,
    FornecedorController,
    FuncionarioController,
    UserController,
    ProdutoController,
    VendaController,
    ItensVendaController,
    FrotaController,
    EntregaController,
    PosVendaController,
    MarcaController,
    UnidadeMedidaController,
    LoteController,
    CepController,
    DevolucaoController,
    EmpresaController,
    PedidoCompraController,
    OrcamentoController,
    PromocaoController
};

// ===============================
// DASHBOARD
// ===============================
Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth'); // Protege o dashboard para usuários logados

// ===============================
// AUTENTICAÇÃO
// ===============================

// Mostrar formulário de login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

// Processar login
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



// Formulário de login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

// Processar login
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ===============================
// PEDIDOS DE COMPRA
// ===============================
Route::patch('pedidos/{pedido}/status', [PedidoCompraController::class, 'updateStatus'])->name('pedidos.updateStatus');
Route::resource('pedidos', PedidoCompraController::class);
Route::get('/pedidos/pdf/{id}', [PedidoCompraController::class, 'gerarPdf'])->name('pedidos.pdf');

// Status específicos
Route::prefix('pedidos')->group(function() {
    Route::get('aprovar/{id}', [PedidoCompraController::class, 'aprovar'])->name('pedidos.aprovar');
    Route::get('receber/{id}', [PedidoCompraController::class, 'receber'])->name('pedidos.receber');
    Route::get('cancelar/{id}', [PedidoCompraController::class, 'cancelar'])->name('pedidos.cancelar');
});

// ===============================
// ORÇAMENTOS
// ===============================
Route::prefix('orcamentos')->name('orcamentos.')->group(function () {
    Route::get('/', [OrcamentoController::class, 'index'])->name('index');
    Route::get('/create', [OrcamentoController::class, 'create'])->name('create');
    Route::post('/', [OrcamentoController::class, 'store'])->name('store');
    Route::get('/{orcamento}', [OrcamentoController::class, 'show'])->name('show');
    Route::get('/{orcamento}/edit', [OrcamentoController::class, 'edit'])->name('edit');
    Route::put('/{orcamento}', [OrcamentoController::class, 'update'])->name('update');
    Route::delete('/{orcamento}', [OrcamentoController::class, 'destroy'])->name('destroy');
    Route::post('/{orcamento}/aprovar', [OrcamentoController::class, 'aprovar'])->name('aprovar');
    Route::post('/{orcamento}/cancelar', [OrcamentoController::class, 'cancelar'])->name('cancelar');
});
Route::get('/orcamentos/{orcamento}/pdf', [OrcamentoController::class, 'gerarPdf'])->name('orcamentos.gerarPdf');

// ===============================
// AUTENTICAÇÃO DE PRODUTOS E NÍVEIS
// ===============================
Route::middleware(['auth', 'checkNivel:admin,gerente'])->group(function () {
    Route::get('/produtos/promocoes', [ProdutoController::class, 'promocoes'])->name('produtos.promocoes');
    Route::post('/produtos/{id}/aplicar-desconto', [ProdutoController::class, 'aplicarDesconto'])->name('produtos.aplicarDesconto');
});
Route::middleware(['auth', 'checkNivel:admin'])->group(function () {
    Route::post('/produtos/{id}/atualizar-preco', [ProdutoController::class, 'atualizarPreco'])->name('produtos.atualizarPreco');
});

// ===============================
// EMPRESAS E FILIAIS
// ===============================
Route::prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/', [EmpresaController::class, 'index'])->name('index');
    Route::get('/create', [EmpresaController::class, 'create'])->name('create');
    Route::post('/', [EmpresaController::class, 'store'])->name('store');
    Route::get('/{empresa}/edit', [EmpresaController::class, 'edit'])->name('edit');
    Route::put('/{empresa}', [EmpresaController::class, 'update'])->name('update');
    Route::put('/{empresa}/desativar', [EmpresaController::class, 'desativar'])->name('desativar');
    Route::put('/{empresa}/ativar', [EmpresaController::class, 'ativar'])->name('ativar');
});
Route::get('/empresa/desativadas', [EmpresaController::class, 'desativadas'])->name('empresa.desativadas');
Route::resource('empresa', EmpresaController::class);

// ===============================
// DEVOLUÇÕES E TROCAS
// ===============================
Route::prefix('devolucoes')->group(function () {
    Route::get('/', [DevolucaoController::class, 'index'])->name('devolucoes.index');
    Route::get('/buscar', [DevolucaoController::class, 'buscar'])->name('devolucoes.buscar');
    Route::get('/pendentes', [DevolucaoController::class, 'pendentes'])->name('devolucoes.pendentes');
    Route::get('/todas', [DevolucaoController::class, 'todas'])->name('devolucoes.todas');
    Route::get('/registrar/{item_id}', [DevolucaoController::class, 'registrar'])->name('devolucoes.registrar');
    Route::get('/{devolucao}/cupom', [DevolucaoController::class, 'gerarCupom'])->name('devolucoes.cupom');
    Route::post('/salvar', [DevolucaoController::class, 'salvar'])->name('devolucoes.salvar');
    Route::put('/{devolucao}/aprovar', [DevolucaoController::class, 'aprovar'])->name('devolucoes.aprovar');
    Route::put('/{devolucao}/rejeitar', [DevolucaoController::class, 'rejeitar'])->name('devolucoes.rejeitar');
});
Route::resource('devolucoes', DevolucaoController::class);

// ===============================
// OUTRAS ROTAS PRINCIPAIS
// ===============================
Route::put('/users/desativar/{user}', [UserController::class, 'desativar'])->name('users.desativar');
Route::put('/clientes/ativar/{cliente}', [ClienteController::class, 'ativar'])->name('clientes.ativar');
Route::put('/clientes/desativar/{cliente}', [ClienteController::class, 'desativar'])->name('clientes.desativar');

Route::get('/funcionarios/buscar', [FuncionarioController::class, 'search'])->name('funcionarios.search');
Route::get('/buscar-funcionario/{cpf}', [FuncionarioController::class, 'buscarPorCPF']);
Route::put('/funcionarios/desativar/{funcionario}', [FuncionarioController::class, 'desativar'])->name('funcionarios.desativar');
Route::put('/funcionarios/ativar/{funcionario}', [FuncionarioController::class, 'ativar'])->name('funcionarios.ativar');

Route::get('/fornecedores/buscar', [FornecedorController::class, 'search'])->name('fornecedores.search');
Route::get('/fornecedores/inativos', [FornecedorController::class, 'inativos'])->name('fornecedores.inativos');
Route::put('/fornecedores/desativar/{fornecedor}', [FornecedorController::class, 'desativar'])->name('fornecedores.desativar');
Route::put('/fornecedores/ativar/{fornecedor}', [FornecedorController::class, 'ativar'])->name('fornecedores.ativar');

// PRODUTOS
Route::get('/produtos/buscar', [ProdutoController::class, 'search'])->name('produtos.search');
Route::get('/produtos/buscar/{nome}', [ProdutoController::class, 'buscarProdutoPorNome'])->name('produtos.buscar');
Route::get('/produtos/inativos', [ProdutoController::class, 'inativos'])->name('produtos.inativos');
Route::put('/produtos/{produto}/desativar', [ProdutoController::class, 'desativar'])->name('produtos.desativar');
Route::put('/produtos/{produto}/reativar', [ProdutoController::class, 'reativar'])->name('produtos.reativar');
Route::get('/produtos/grid', [ProdutoController::class, 'indexGrid'])->name('produtos.index-grid');
Route::get('/produtos/buscar2', [ProdutoController::class, 'search_grid'])->name('produtos.search_grid');

// MARCAS
Route::get('/marcas/inativos', [MarcaController::class, 'inativos'])->name('marcas.inativos');
Route::put('/marcas/{marca}/reativar', [MarcaController::class, 'reativar'])->name('marcas.reativar');

// UNIDADES
Route::get('/unidades/inativos', [UnidadeMedidaController::class, 'inativos'])->name('unidades.inativos');
Route::put('/unidades/{unidade}/reativar', [UnidadeMedidaController::class, 'reativar'])->name('unidades.reativar');

// CEP
Route::get('/buscar-cep', [CepController::class, 'buscar'])->name('buscar.cep');

// ===============================
// RECURSOS PRINCIPAIS
// ===============================
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

// ===============================
// PDV - PONTO DE VENDA
// ===============================
Route::prefix('pdv')->group(function () {
    Route::get('/', [VendaController::class, 'index'])->name('pdv.index');
    Route::get('/buscar-produto', [VendaController::class, 'buscarProduto'])->name('pdv.buscarProduto');
    Route::post('/adicionar-produto', [VendaController::class, 'adicionarProduto'])->name('pdv.adicionarProduto');
    Route::post('/remover-produto', [VendaController::class, 'removerProduto'])->name('pdv.removerProduto');
    Route::post('/finalizar', [VendaController::class, 'finalizarVenda'])->name('pdv.finalizarVenda');
    Route::get('/cupom/{venda}', [VendaController::class, 'gerarCupom'])->name('pdv.cupom');
});

// ===============================
// LOTES
// ===============================
Route::prefix('produtos/{produto_id}/lotes')->group(function () {
    Route::get('/', [LoteController::class, 'index'])->name('lotes.index');
    Route::get('/create', [LoteController::class, 'create'])->name('lotes.create');
    Route::post('/', [LoteController::class, 'store'])->name('lotes.store');
});

  // ===============================
// PROMOÇÕES E DESCONTOS
// ===============================
Route::middleware(['auth', 'can:gerenciar-promocoes'])->group(function () {
    Route::get('/promocoes', [PromocaoController::class, 'index'])->name('promocoes.index');
    Route::get('/promocoes/create', [PromocaoController::class, 'create'])->name('promocoes.create');
    Route::post('/promocoes', [PromocaoController::class, 'store'])->name('promocoes.store');
    Route::get('/promocoes/{promocao}', [PromocaoController::class, 'show'])->name('promocoes.show');
    Route::get('/promocoes/{promocao}/edit', [PromocaoController::class, 'edit'])->name('promocoes.edit');
    Route::put('/promocoes/{promocao}', [PromocaoController::class, 'update'])->name('promocoes.update');
    Route::delete('/promocoes/{promocao}', [PromocaoController::class, 'destroy'])->name('promocoes.destroy');
});