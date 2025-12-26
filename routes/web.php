<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DashboardController,
    CategoriaController,
    ClienteController,
    FornecedorController,
    FuncionarioController,
    UserController,
    ProdutoController,
    PDV\OrcamentoPDVController,
    PdvController,
    ItensVendaController,
    FrotaController,
    EntregaController,
    PosVendaController,
    MarcaController,
    UnidadeMedidaController,
    Venda,
    LoteController,
    CepController,
    DevolucaoController,
    EmpresaController,
    PedidoCompraController,
    OrcamentoController,
    PromocaoController,
    PainelPromocaoController,
    PedidoCompraRecebimentoController
};

// ===============================
// AUTENTICAÇÃO
// ===============================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===============================
// ROTAS PROTEGIDAS (auth)
// ===============================
    Route::middleware('auth')->group(function () {

        // DASHBOARD
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // ===============================
        // PEDIDOS DE COMPRA
        // ===============================
        Route::prefix('pedidos')->name('pedidos.')->group(function () {

        // Atualizar status
        Route::patch('{pedido}/status', [PedidoCompraController::class, 'updateStatus'])
            ->name('updateStatus');

        // Aprovar pedido
        Route::get('aprovar/{id}', [PedidoCompraController::class, 'aprovar'])
            ->name('aprovar');

        // Tela de recebimento (VIEW)
        Route::get('receber-view/{id}', [PedidoCompraController::class, 'receberView'])
            ->name('receber.view');

        // Confirmar recebimento (POST) — ESTA É A ROTA QUE FALTAVA
        Route::post('receber-confirmar/{id}', [PedidoCompraController::class, 'receberConfirmar'])
            ->name('receber.confirmar');

        // Receber direto (se quiser manter)
        Route::post('receber/{id}', [PedidoCompraController::class, 'receber'])
            ->name('receber');

        // Cancelar pedido
        Route::get('cancelar/{id}', [PedidoCompraController::class, 'cancelar'])
            ->name('cancelar');

        // PDF
        Route::get('pdf/{id}', [PedidoCompraController::class, 'gerarPdf'])
            ->name('pdf');
    });

    // Resource
    Route::resource('pedidos', PedidoCompraController::class);


    // ===============================
    // ORÇAMENTOS
    // ===============================
    Route::prefix('orcamentos')->name('orcamentos.')->group(function () {
        Route::post('{orcamento}/aprovar', [OrcamentoController::class, 'aprovar'])->name('aprovar');
        Route::post('{orcamento}/cancelar', [OrcamentoController::class, 'cancelar'])->name('cancelar');
        Route::get('{orcamento}/pdf', [OrcamentoController::class, 'gerarPdf'])->name('gerarPdf');
    });
    Route::resource('orcamentos', OrcamentoController::class);

    // ===============================
    // PRODUTOS
    // ===============================
    Route::prefix('produtos')->name('produtos.')->group(function () {
        Route::get('buscar', [ProdutoController::class, 'search'])->name('search');
        Route::get('buscar/{nome}', [ProdutoController::class, 'buscarProdutoPorNome'])->name('buscar');
        Route::get('inativos', [ProdutoController::class, 'inativos'])->name('inativos');
        Route::get('grid', [ProdutoController::class, 'indexGrid'])->name('index-grid');
        Route::get('buscar2', [ProdutoController::class, 'search_grid'])->name('search_grid');
        Route::put('{produto}/desativar', [ProdutoController::class, 'desativar'])->name('desativar');
        Route::put('{produto}/reativar', [ProdutoController::class, 'reativar'])->name('reativar');
        
        // Atualização de preço (apenas admin)
        Route::middleware('checkNivel:admin')->post('{id}/atualizar-preco', [ProdutoController::class, 'atualizarPreco'])->name('atualizarPreco');
    });
    Route::resource('produtos', ProdutoController::class);
    

    // ===============================
    // PROMOÇÕES
    // ===============================
   Route::middleware(['auth', 'can:gerenciar-promocoes'])
    ->prefix('promocoes')
    ->name('promocoes.')
    ->group(function () {
        Route::get('/', [PromocaoController::class, 'index'])->name('index');
        Route::get('/create', [PromocaoController::class, 'create'])->name('create');
        Route::post('/', [PromocaoController::class, 'store'])->name('store');
        Route::get('/{promocao}', [PromocaoController::class, 'show'])->name('show');
        Route::get('/{promocao}/edit', [PromocaoController::class, 'edit'])->name('edit');
        Route::put('/{promocao}', [PromocaoController::class, 'update'])->name('update');
        Route::delete('/{promocao}', [PromocaoController::class, 'destroy'])->name('destroy');

     // 🔥 ROTA CORRETA DO TOGGLE
        Route::put('/{promocao}/toggle-status', [PromocaoController::class, 'toggleStatus'])
            ->name('toggleStatus');

        // Encerrar promoção manualmente
        Route::patch('/{promocao}/encerrar', [PromocaoController::class, 'encerrar'])
            ->name('encerrar');
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

     
    // Grupo de rotas protegido por autenticação
    Route::middleware(['auth'])->group(function () {

        // Rotas resource padrão (index, create, store, show, edit, update, destroy)
        Route::resource('clientes', ClienteController::class);

        // Rotas extras para ativar/desativar clientes
        Route::get('clientes/inativos', [ClienteController::class, 'inativos'])
            ->name('clientes.inativos');

        Route::patch('clientes/{cliente}/ativar', [ClienteController::class, 'ativar'])
            ->name('clientes.ativar');

        Route::patch('clientes/{cliente}/desativar', [ClienteController::class, 'desativar'])
            ->name('clientes.desativar');

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
    // FORNECEDORES (corrigido)
    // ===============================
    Route::middleware(['auth'])->prefix('fornecedores')->group(function () {
    Route::resource('/', FornecedorController::class)
        ->names([
            'index' => 'fornecedores.index',
            'create' => 'fornecedores.create',
            'store' => 'fornecedores.store',
            'show' => 'fornecedores.show',
            'edit' => 'fornecedores.edit',
            'update' => 'fornecedores.update',
            'destroy' => 'fornecedores.destroy',
        ]);

        // Rotas adicionais
        Route::get('/search', [FornecedorController::class, 'search'])->name('fornecedores.search');
        // Route::get('/fornecedores/inativos', [FornecedorController::class, 'inativos'])->name('fornecedores.inativos');
        Route::get('/{id}/edit', [FornecedorController::class, 'edit'])->name('fornecedores.edit.id'); // edição por ID
        Route::put('/{id}', [FornecedorController::class, 'update'])->name('fornecedores.update.id');
        Route::put('/fornecedores/{id}/desativar', [FornecedorController::class, 'desativar'])->name('fornecedores.desativar');
        Route::put('/fornecedores/{id}/ativar', [FornecedorController::class, 'ativar'])->name('fornecedores.ativar');
        Route::get('/inativos', [FornecedorController::class, 'inativos'])->name('fornecedores.inativos');
        Route::get('/orcamentos/{id}/pdf', [OrcamentoController::class, 'gerarPdf'])->name('orcamentos.pdf');
    });


        // ===============================
        // ROTAS PADRÃO
        // ===============================
        Route::resources([
        'users' => UserController::class,
        'clientes' => ClienteController::class,
        'funcionarios' => FuncionarioController::class,
        'marcas' => MarcaController::class,
        'unidades' => UnidadeMedidaController::class,
        'vendas' =>PdvController::class,
        'itens_venda' => ItensVendaController::class,
        'frotas' => FrotaController::class,
        'entregas' => EntregaController::class,
        'pos_venda' => PosVendaController::class,
    ]);

    // ===============================
    // ATIVA/DESATIVA
    // ===============================
    Route::middleware('checkNivel:admin,gerente')->group(function () {
        Route::put('users/desativar/{user}', [UserController::class, 'desativar'])->name('users.desativar');
        
        
        Route::put('clientes/ativar/{cliente}', [ClienteController::class, 'ativar'])->name('clientes.ativar');
        Route::put('clientes/desativar/{cliente}', [ClienteController::class, 'desativar'])->name('clientes.desativar');
        Route::put('funcionarios/desativar/{funcionario}', [FuncionarioController::class, 'desativar'])->name('funcionarios.desativar');
        Route::put('funcionarios/ativar/{funcionario}', [FuncionarioController::class, 'ativar'])->name('funcionarios.ativar');
        Route::get('funcionarios/search', [FuncionarioController::class, 'search'])->name('funcionarios.search');
    });

    // ===============================
    // OUTRAS ROTAS
    // ===============================
    Route::put('marcas/{marca}/reativar', [MarcaController::class, 'reativar'])->name('marcas.reativar');
    Route::put('unidades/{unidade}/reativar', [UnidadeMedidaController::class, 'reativar'])->name('unidades.reativar');

    // CEP
    Route::get('buscar-cep', [CepController::class, 'buscar'])->name('buscar.cep');

    // LOTES
    Route::prefix('produtos/{produto_id}/lotes')->name('lotes.')->group(function () {
        Route::get('/', [LoteController::class, 'index'])->name('index');
        Route::get('create', [LoteController::class, 'create'])->name('create');
        Route::post('/', [LoteController::class, 'store'])->name('store');
    });

    // CATEGORIAS / RELATÓRIOS
    Route::get('categorias/{id}/preco-medio', [CategoriaController::class, 'precoMedio']);

    
    // carregar orçamento no PDV
        Route::get('/pdv/orcamento/{codigo}', [PdvController::class, 'carregarOrcamento']);
    
    //buscar orcamentos no PDV    
    Route::get('/orcamentos/buscar', [OrcamentoController::class, 'buscar'])->name('orcamentos.buscar')
        ->middleware('auth');

    // Enviar orçamento por WhatsApp   
    Route::get('/orcamentos/{id}/whatsapp', [OrcamentoController::class, 'enviarWhatsApp'])
    ->name('orcamentos.whatsapp');

    // limpar edição de orçamentos e produtos
    Route::post('/orcamentos/{id}/limpar-edicao', [OrcamentoController::class, 'limparEdicao'])->name('orcamentos.limparEdicao');
    
    //Limpar edição de produtos
    Route::post('produtos/{id}/limpar-edicao', [ProdutoController::class, 'limparEdicao'])
    ->name('produtos.limparEdicao');

   Route::get('/painel_promocao', [\App\Http\Controllers\PainelPromocaoController::class, 'index'])
    ->name('painel_promocao.index')
    ->middleware(['auth']);

    Route::patch('/promocoes/{promocao}/toggle', [App\Http\Controllers\PromocaoController::class, 'toggle'])
    ->name('promocoes.toggle');

   Route::get('/promocoes/{id}/toggle-status', [PromocaoController::class, 'toggleStatus'])
    ->name('promocoes.toggleStatus')
    ->middleware('can:gerenciar-promocoes');

    // PDV
        Route::get('/pdv', [PdvController::class, 'index'])->name('pdv.index');

        ///Busca inteligente do PDV (produto, e orçamento)
        Route::get('/pdv/buscar-produto', [PDVController::class, 'buscarProduto'])->name('pdv.buscarProduto');
       
        //Busca inteligente do PDV (NOME cliente e CPF)
        Route::get('/pdv/buscar-cliente', [PDVController::class, 'buscarCliente'])->name('pdv.buscarCliente');

        //Busca produto por codigo de barras
        Route::get('/pdv/produto/{codigo}', [PDVController::class, 'buscarProdutoPorCodigo'])->name('pdv.buscarProdutoPorCodigo');
        
        //Busca orçamento por codigo
        // Route::get('/pdv/orcamento/{codigo}', [PdvController::class, 'buscarOrcamento']);

        //PDV/OrcamentoPDVController
        Route::get('/pdv/orcamento/{codigo}', [OrcamentoPDVController::class, 'buscar'])
            ->name('pdv.orcamento.buscar');

   


Route::get('/pdv/ultimo-id-venda', function() {
    try {
        // Busca o último id diretamente via query builder
        $ultimoId = DB::table('vendas')->max('id') ?? 0;
        return response()->json(['ultimo_id' => $ultimoId]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

});
