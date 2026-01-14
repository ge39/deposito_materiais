<?php

use Illuminate\Support\Facades\Route;
use App\Models\Venda;
use App\Models\Caixa;

use App\Http\Controllers\{
    AuthController,
    DashboardController,
    CaixaController,
    CategoriaController,
    ClienteController,
    FornecedorController,
    FuncionarioController,
    UserController,
    ProdutoController,
    PDV\OrcamentoPDVController,
    PdvController,
    ItensVendaController,
    FechamentoCaixaController,
    FrotaController,
    EntregaController,
    PosVendaController,
    MarcaController,
    UnidadeMedidaController,
    VendaController,
    LoteController,
    CepController,
    DevolucaoController,
    EmpresaController,
    PedidoCompraController,
    OrcamentoController,
    PromocaoController,
    PainelPromocaoController
};

// ===============================
// AUTENTICAÇÃO
// ===============================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===============================
// ROTAS PROTEGIDAS
// ===============================
Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ===============================
    // PEDIDOS DE COMPRA
    // ===============================
    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::patch('{pedido}/status', [PedidoCompraController::class, 'updateStatus'])->name('updateStatus');
        Route::get('aprovar/{id}', [PedidoCompraController::class, 'aprovar'])->name('aprovar');
        Route::get('receber-view/{id}', [PedidoCompraController::class, 'receberView'])->name('receber.view');
        Route::post('receber-confirmar/{id}', [PedidoCompraController::class, 'receberConfirmar'])->name('receber.confirmar');
        Route::post('receber/{id}', [PedidoCompraController::class, 'receber'])->name('receber');
        Route::get('cancelar/{id}', [PedidoCompraController::class, 'cancelar'])->name('cancelar');
        Route::get('pdf/{id}', [PedidoCompraController::class, 'gerarPdf'])->name('pdf');
    });
    Route::resource('pedidos', PedidoCompraController::class);

    // ===============================
    // ORÇAMENTOS
    // ===============================
    Route::prefix('orcamentos')->name('orcamentos.')->group(function () {
        Route::post('{orcamento}/aprovar', [OrcamentoController::class, 'aprovar'])->name('aprovar');
        Route::post('{orcamento}/cancelar', [OrcamentoController::class, 'cancelar'])->name('cancelar');
        Route::get('{orcamento}/pdf', [OrcamentoController::class, 'gerarPdf'])->name('gerarPdf');
        Route::get('buscar', [OrcamentoController::class, 'buscar'])->name('buscar');
        Route::get('{id}/whatsapp', [OrcamentoController::class, 'enviarWhatsApp'])->name('whatsapp');
        Route::post('{id}/limpar-edicao', [OrcamentoController::class, 'limparEdicao'])->name('limparEdicao');
    });
    Route::resource('orcamentos', OrcamentoController::class);

    // ===============================
    // PRODUTOS
    // ===============================
    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('buscar', [ProdutoController::class, 'search'])->name('search');
        Route::get('buscar-por-nome/{nome}', [ProdutoController::class, 'buscarProdutoPorNome'])->name('buscarNome');
        Route::get('inativos', [ProdutoController::class, 'inativos'])->name('inativos');
        Route::get('grid', [ProdutoController::class, 'indexGrid'])->name('index-grid');
        Route::get('buscar2', [ProdutoController::class, 'search_grid'])->name('search_grid');
        Route::put('{produto}/desativar', [ProdutoController::class, 'desativar'])->name('desativar');
        Route::put('{produto}/reativar', [ProdutoController::class, 'reativar'])->name('reativar');
        Route::middleware('checkNivel:admin')->post('{id}/atualizar-preco', [ProdutoController::class, 'atualizarPreco'])->name('atualizarPreco');
        Route::post('{id}/limpar-edicao', [ProdutoController::class, 'limparEdicao'])->name('limparEdicao');
    });
    Route::resource('produtos', ProdutoController::class);

    // ===============================
    // PROMOÇÕES
    // ===============================
    Route::middleware('can:gerenciar-promocoes')->prefix('promocoes')->name('promocoes.')->group(function () {
        Route::get('/', [PromocaoController::class, 'index'])->name('index');
        Route::get('create', [PromocaoController::class, 'create'])->name('create');
        Route::post('/', [PromocaoController::class, 'store'])->name('store');
        Route::get('{promocao}', [PromocaoController::class, 'show'])->name('show');
        Route::get('{promocao}/edit', [PromocaoController::class, 'edit'])->name('edit');
        Route::put('{promocao}', [PromocaoController::class, 'update'])->name('update');
        Route::delete('{promocao}', [PromocaoController::class, 'destroy'])->name('destroy');
        Route::put('{promocao}/toggle-status', [PromocaoController::class, 'toggleStatus'])->name('toggleStatus');
        Route::patch('{promocao}/encerrar', [PromocaoController::class, 'encerrar'])->name('encerrar');
    });

    // ===============================
    // EMPRESAS
    // ===============================
    Route::prefix('empresa')->name('empresa.')->group(function () {
        Route::put('{empresa}/desativar', [EmpresaController::class, 'desativar'])->name('desativar');
        Route::put('{empresa}/ativar', [EmpresaController::class, 'ativar'])->name('ativar');
        Route::get('desativadas', [EmpresaController::class, 'desativadas'])->name('desativadas');
    });
    Route::resource('empresa', EmpresaController::class);

    // ===============================
    // CLIENTES
    // ===============================
    Route::resource('clientes', ClienteController::class);
    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('inativos', [ClienteController::class, 'inativos'])->name('inativos');
        Route::patch('{cliente}/ativar', [ClienteController::class, 'ativar'])->name('ativar');
        Route::patch('{cliente}/desativar', [ClienteController::class, 'desativar'])->name('desativar');
    });

    // ===============================
    // DEVOLUÇÕES
    // ===============================
    Route::prefix('devolucoes')->name('devolucoes.')->group(function () {
        Route::get('buscar', [DevolucaoController::class, 'buscar'])->name('buscar');
        Route::get('pendentes', [DevolucaoController::class, 'pendentes'])->name('pendentes');
        Route::get('todas', [DevolucaoController::class, 'todas'])->name('todas');
        Route::get('registrar/{item_id}', [DevolucaoController::class, 'registrar'])->name('registrar');
        Route::get('{devolucao}/cupom', [DevolucaoController::class, 'gerarCupom'])->name('cupom');
        Route::post('salvar', [DevolucaoController::class, 'salvar'])->name('salvar');
        Route::put('{devolucao}/aprovar', [DevolucaoController::class, 'aprovar'])->name('aprovar');
        Route::put('{devolucao}/rejeitar', [DevolucaoController::class, 'rejeitar'])->name('rejeitar');
    });
    Route::resource('devolucoes', DevolucaoController::class);

    // ===============================
    // FORNECEDORES
    // ===============================
    Route::resource('fornecedores', FornecedorController::class)->names([
        'index' => 'fornecedores.index',
        'create' => 'fornecedores.create',
        'store' => 'fornecedores.store',
        'show' => 'fornecedores.show',
        'edit' => 'fornecedores.edit',
        'update' => 'fornecedores.update',
        'destroy' => 'fornecedores.destroy',
    ]);
    Route::prefix('fornecedores')->name('fornecedores.')->group(function () {
        Route::get('search', [FornecedorController::class, 'search'])->name('search');
        Route::put('{id}/desativar', [FornecedorController::class, 'desativar'])->name('desativar');
        Route::put('{id}/ativar', [FornecedorController::class, 'ativar'])->name('ativar');
        Route::get('inativos', [FornecedorController::class, 'inativos'])->name('inativos');
    });

    // ===============================
    // OUTROS RESOURCES
    // ===============================
    Route::resources([
        'users' => UserController::class,
        'funcionarios' => FuncionarioController::class,
        'marcas' => MarcaController::class,
        'unidades' => UnidadeMedidaController::class,
        'vendas' => PdvController::class,
        'itens_venda' => ItensVendaController::class,
        'frotas' => FrotaController::class,
        'entregas' => EntregaController::class,
        'pos_venda' => PosVendaController::class,
    ]);

    // ===============================
    // ATIVA/DESATIVA (admin/gerente)
    // ===============================
    Route::middleware('checkNivel:admin,gerente')->group(function () {
        Route::put('users/desativar/{user}', [UserController::class, 'desativar'])->name('users.desativar');
        Route::put('funcionarios/desativar/{funcionario}', [FuncionarioController::class, 'desativar'])->name('funcionarios.desativar');
        Route::put('funcionarios/ativar/{funcionario}', [FuncionarioController::class, 'ativar'])->name('funcionarios.ativar');
        Route::get('funcionarios/search', [FuncionarioController::class, 'search'])->name('funcionarios.search');
    });

    // ===============================
    // CEP
    // ===============================
    Route::get('buscar-cep', [CepController::class, 'buscar'])->name('buscar.cep');

    // ===============================
    // LOTES
    // ===============================
    Route::prefix('produtos/{produto_id}/lotes')->name('lotes.')->group(function () {
        Route::get('/', [LoteController::class, 'index'])->name('index');
        Route::get('create', [LoteController::class, 'create'])->name('create');
        Route::post('/', [LoteController::class, 'store'])->name('store');
    });

    // ===============================
    // CATEGORIAS / RELATÓRIOS
    // ===============================
    Route::get('categorias/{id}/preco-medio', [CategoriaController::class, 'precoMedio']);

    // ===============================
    // PDV
    // ===============================
    Route::prefix('pdv')->name('pdv.')->middleware(['auth', 'terminal'])->group(function () {

        Route::get('/', [PdvController::class, 'index'])->name('index');

        // Route::post('/venda', [PdvController::class, 'store'])->name('venda.store');
        Route::get('buscar-produto', [PdvController::class, 'buscarProduto'])->name('buscarProduto');
        Route::get('buscar-cliente', [PdvController::class, 'buscarCliente'])->name('buscarCliente');
        Route::get('produto/{codigo}', [PdvController::class, 'buscarProdutoPorCodigo'])->name('buscarProdutoPorCodigo');
        Route::get('orcamento/{codigo}', [OrcamentoPDVController::class, 'buscar'])->name('orcamento.buscar');
       
    });

        //Vendas
        Route::post('/vendas', [VendaController::class, 'store'])->name('vendas.store');

    // ===============================
    // Abertura de Caixa
    // ===============================
    Route::middleware(['auth', 'terminal'])->group(function () {
        // Tela de abertura
        Route::get('/caixa/abrir', [CaixaController::class, 'abrir'])->name('caixa.abrir');
        // Salvar abertura do caixa
        Route::post('/caixa/abrir', [CaixaController::class, 'store'])->name('caixa.store');
    });

    // ===============================
    // Painel de Promoção
    // ===============================
    Route::get('/painel_promocao', [PainelPromocaoController::class, 'index'])->name('painel_promocao.index');

    // Auditoria e fechamento de caixa
      // Lista caixas (GET)
    Route::get('/fechamento_caixa', [FechamentoCaixaController::class, 'listaCaixas'])
        ->name('fechamento.lista')
        ->middleware('auth');

    Route::prefix('fechamento_caixa')->middleware('auth')->group(function () {
        // Auditar caixa
        Route::get('/auditar/{caixa}', [FechamentoCaixaController::class, 'index'])
            ->name('fechamento.auditar');

        // Consolidar pagamentos
        Route::get('/consolidar/{caixa}', [FechamentoCaixaController::class, 'consolidarPagamentos'])
            ->name('fechamento.consolidar');

        // Fechar caixa (POST) - já processa os valores manuais
        Route::post('/fechar/{caixa}', [FechamentoCaixaController::class, 'fechar'])
            ->name('fechamento.fechar');
        });

        Route::prefix('pdv')->group(function () {
            Route::get('/caixas-esquecidos', [PDVController::class, 'caixasEsquecidos']);
        });


        Route::prefix('fechamento_caixa')->group(function () {
            Route::get('/lancar_valores/{caixa}', 
                [FechamentoCaixaController::class, 'lancarValores']
            )->name('fechamento.lancar_valores');
        });
});