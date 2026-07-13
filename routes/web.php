<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    AuditoriaCaixaController,
    DashboardController,
    CaixaController,
    ContaCorrenteController,
    CategoriaController,
    ClienteCreditoController,
    ClienteController,
    Cliente,
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
    PainelPromocaoController,
    SangriaController,
    SangriaConfigController,
    RelatorioReposicaoController,
    MovimentacaoOrcamentoController,
    MovimentacaoOrcamentoDashboardController,
    MovimentacaoCaixaController,
    LimiteClienteController,
    EstoqueDivergenciaController,
    BackupController,
    RomaneioController,
    ExpedicaoController,
    LocalizacaoEstoqueController,
    VeiculoController,
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
        Route::post('receber/{id}', [PedidoCompraController::class, 'receber'])->name('receber');
        Route::get('cancelar/{id}', [PedidoCompraController::class, 'cancelar'])->name('cancelar');
        Route::get('pdf/{id}', [PedidoCompraController::class, 'gerarPdf'])->name('pdf');
        
    });
    Route::resource('pedidos', PedidoCompraController::class);

    // ==========================================================
    // ORÇAMENTOS (Painel Administrativo - Criação, Edição, PDF)
    // ==========================================================
    Route::prefix('orcamentos')->name('orcamentos.')->group(function () {
        Route::post('{orcamento}/aprovar', [OrcamentoController::class, 'aprovar'])->name('aprovar');
        Route::post('{orcamento}/reativar', [OrcamentoController::class, 'reativar'])->name('reativar');
        Route::post('{orcamento}/cancelar', [OrcamentoController::class, 'cancelar'])->name('cancelar');
        Route::get('{orcamento}/pdf', [OrcamentoController::class, 'gerarPdf'])->name('gerarPdf');
        Route::get('buscar', [OrcamentoController::class, 'buscar'])->name('buscar');
        Route::get('{id}/whatsapp', [OrcamentoController::class, 'enviarWhatsApp'])->name('whatsapp');
        Route::post('{id}/limpar-edicao', [OrcamentoController::class, 'limparEdicao'])->name('limparEdicao');
    });
    Route::resource('orcamentos', OrcamentoController::class);

    // ==========================================================
    // 🛒 ECOSSISTEMA DO PDV / CAIXA (Rotas Isoladas)
    // ==========================================================
    Route::get('/pdv/orcamento/{codigo}', [App\Http\Controllers\PDV\OrcamentoPDVController::class, 'buscar']);

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

    Route::get('/api/lotes/{produto}', function ($produtoId) {
        return \App\Models\Lote::where('produto_id', $produtoId)
            ->where('quantidade_disponivel', '>', 0)
            ->where('status', 1)
            ->get();
    });
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
        
        // 🚀 CORRIGIDO: Deixe apenas 'toggle', o grupo adicionará o prefixo 'promocoes.' automaticamente
        Route::put('{promocao}/toggle-status', [PromocaoController::class, 'toggleStatus'])->name('toggle');

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
   Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::get('buscar', [ClienteController::class, 'buscar'])->name('buscar');
        Route::get('inativos', [ClienteController::class, 'inativos'])->name('inativos');
        Route::patch('{cliente}/ativar', [ClienteController::class, 'ativar'])->name('ativar');
        Route::patch('{cliente}/desativar', [ClienteController::class, 'desativar'])->name('desativar');
    });

    Route::resource('clientes', ClienteController::class);

    // ===============================
    // DEVOLUÇÕES
    // ===============================
    Route::prefix('devolucoes')->name('devolucoes.')->group(function () {
        // 1. Rotas estáticas/fixas (Precisam vir PRIMEIRO para evitar conflito com os IDs do Resource)
        Route::get('buscar', [DevolucaoController::class, 'buscar'])->name('buscar');
        Route::get('pendentes', [DevolucaoController::class, 'pendentes'])->name('pendentes');
        Route::get('todas', [DevolucaoController::class, 'todas'])->name('todas');
        
        // 2. Rotas parametrizadas específicas do fluxo
        // Route::get('registrar/{item_id}', [DevolucaoController::class, 'registrar'])->name('registrar');
        // Mude de 'registrar/{item_id}' para:
        Route::get('registrar/{venda_id}', [DevolucaoController::class, 'registrar'])->name('registrar');

        Route::get('{devolucao}/cupom', [DevolucaoController::class, 'gerarCupom'])->name('cupom');
        Route::post('salvar', [DevolucaoController::class, 'salvar'])->name('salvar');
        Route::put('{devolucao}/aprovar', [DevolucaoController::class, 'aprovar'])->name('aprovar');
        Route::put('{devolucao}/rejeitar', [DevolucaoController::class, 'rejeitar'])->name('rejeitar');
    });

    // 3. Resource limpo (Apenas os métodos padrão do CRUD que você não customizou acima)
    Route::resource('devolucoes', DevolucaoController::class)->except([
        'show', 'store' // Remove os métodos que entram em conflito com 'buscar', 'todas' e 'salvar'
    ]);


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
        Route::get('buscar-produto', [PdvController::class, 'buscarProduto'])->name('buscarProduto');
        Route::get('buscar-cliente', [PdvController::class, 'buscarCliente'])->name('buscarCliente');
        Route::get('produto/{codigo}', [PdvController::class, 'buscarProdutoPorCodigo'])->name('buscarProdutoPorCodigo');
        Route::get('orcamento/{codigo}', [OrcamentoPDVController::class, 'buscar'])->name('orcamento.buscar');
        Route::get('/caixas-esquecidos', [PdvController::class, 'caixasEsquecidos']);
        
    });

        // ========================================================
    // ROTAS DE VENDAS E CUPOM TÉRMICO (ORGANIZADO E FILTRADO)
    // ========================================================
    
    // 🎯 ATALHO ALT + P: Aponta para o controlador correto (VendaController) e fica no topo
    // Route::get('/pdv/ultima-venda-id', [\App\Http\Controllers\VendaController::class, 'obterUltimaVendaId']);

    // // Rotas de processamento e fechamento da venda
    // Route::post('/vendas', [\App\Http\Controllers\VendaController::class, 'store'])->name('vendas.store');
    // Route::post('/vendas/finalizar', [\App\Http\Controllers\VendaController::class, 'finalizar']);
    
    // // Rota do Cupom com parâmetro {id} (Fica abaixo das rotas fixas)
    // Route::get('/venda/{id}/cupom', [\App\Http\Controllers\VendaController::class, 'cupom'])->name('venda.cupom');

    //  //Vendas
    // Route::post('/vendas', [VendaController::class, 'store'])->name('vendas.store');
    // Route::post('/vendas/finalizar', [VendaController::class, 'finalizar']);
    // Route::get('/venda/{id}/cupom', [VendaController::class, 'cupom'])->name('venda.cupom');

    // 1️⃣ Rota específica de finalização (DEVE FICAR ACIMA DO RESOURCE)
    Route::post('/vendas/finalizar', [\App\Http\Controllers\VendaController::class, 'finalizar']);

    // 2️⃣ Atalhos auxiliares do PDV
    Route::get('/pdv/ultima-venda-id', [\App\Http\Controllers\VendaController::class, 'obterUltimaVendaId']);

    // 🎯 AQUI ESTÁ O SEGREDO: Criamos o caminho direto sem o prefixo apontando para o mesmo Controller
    Route::get('/venda/{id}/cupom', [\App\Http\Controllers\VendaController::class, 'cupom']);

    Route::prefix('vendas')->group(function () {
        Route::get('/venda/{id}/cupom', [\App\Http\Controllers\VendaController::class, 'cupom'])
            ->name('venda.cupom');
    });

    // 3️⃣ Resource padrão (Gera index, create, show, update, destroy)
    Route::resource('vendas', PdvController::class);


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

        
     //Fechamento Caixa
        Route::prefix('fechamento_caixa')->group(function () 
        { Route::get('/lancar_valores/{caixa}', [FechamentoCaixaController::class, 'lancarValores'])->name('fechamento.lancar_valores'); });
        Route::get('fechamento/caixa/{caixa}/corrigir', [FechamentoCaixaController::class, 'corrigirDivergencias'])->name('fechamento.corrigir');
        Route::post('fechamento/caixa/{caixa}/ajustar', [FechamentoCaixaController::class, 'ajustarDivergencias'])->name('fechamento.ajustar');
        Route::get('/fechamento/{caixa}/divergencias', [FechamentoCaixaController::class, 'divergencias'])->name('fechamento.divergencias');
        Route::get('/fechamento_caixa/fechamento/{caixa}', [FechamentoCaixaController::class, 'fechamento'])->name('fechamento.view');

        //rota da mensagem de confirmação para fechamento do caixa com ou sem auditoria
        Route::get(
            '/fechamento_caixa/confirmacao/{caixa}',
            [FechamentoCaixaController::class, 'confirmacao']
        )->name('fechamento.confirmacao');
        
        //rota para correção do caixa com inconsistencia
         Route::get('/fechamento/{caixa}/confirmacao_auditoria', [FechamentoCaixaController::class, 'auditoria'])
         ->name('fechamento.auditoria');

    });


    //Auditoria do caixa
    Route::prefix('auditoria-caixa')
    ->name('auditoria_caixa.')
    ->group(function () {

        Route::get('/', [AuditoriaCaixaController::class, 'index'])
            ->name('index');

        Route::get('/{auditoria}/exportar', 
            [AuditoriaCaixaController::class, 'exportar']
        )->name('exportar');

        Route::get('/{auditoria}', 
            [AuditoriaCaixaController::class, 'show']
        )->name('show');
    });

    //Sangria
   // No seu arquivo de rotas
    Route::prefix('pdv')->group(function() {
        
        // RETORNA OS DADOS DE BLOQUEIO E SALDO DO PDV
        Route::get('verificar-sangria', [PdvController::class, 'verificarSangria'])
            ->name('pdv.verificar-sangria');

        // Suas rotas atuais de sangria...
        Route::get('caixa/{caixa}/sangria', [SangriaController::class, 'criarForm'])->name('caixa.sangria.form');
        Route::post('caixa/{caixa}/sangria', [SangriaController::class, 'registrar'])->name('caixa.sangria.registrar');
        Route::get('sangria/{sangria}/imprimir', [SangriaController::class, 'imprimir'])->name('sangria.imprimir');
    });
     //Sangria conf
    Route::get('/sangria-config', [SangriaConfigController::class, 'index'])
    ->name('sangria-config.index');

    Route::post('/sangria-config', [SangriaConfigController::class, 'store'])
    ->name('sangria-config.store');

    // conta corrente
    Route::get('/clientes/{id}/conta-corrente', 
        [ContaCorrenteController::class, 'extrato']
    )->name('clientes.conta_corrente');

    Route::post('/clientes/{id}/conta-corrente/pagar',
        [ContaCorrenteController::class, 'pagar']
    )->name('clientes.conta_corrente.pagar');

    Route::get(
        '/clientes/{cliente}/conta-corrente',
        [ContaCorrenteController::class, 'show']
    )->name('clientes.conta_corrente.show');

    // API utilizada pelo painel do PDV para checagem de limites e bloqueios
    Route::get('/api/cliente/financeiro/{id}', [ContaCorrenteController::class, 'obterFinanceiro'])->name('api.cliente.financeiro');

    // Rota para o caixa receber o pagamento do fiado/carteira do cliente
    Route::post('/clientes/{id}/conta-corrente/receber', [ContaCorrenteController::class, 'receberPagamentoFiado'])->name('clientes.conta_corrente.receber');

        //Relatorio tabela itens_orcamento quantidade_pendente
    Route::get('/relatorio/reposicao', [RelatorioReposicaoController::class, 'index'])
    ->name('relatorio.reposicao');
    
    // pdf do relatorio itens_orcamentos
    Route::get('/relatorio/reposicao/pdf', [App\Http\Controllers\RelatorioReposicaoController::class, 'gerarPdf'])
    ->name('relatorio.reposicao.pdf');

    //Relatorio Movimentacao de orcamentos
    Route::get('/movimentacoes', [MovimentacaoOrcamentoController::class, 'index']);
    Route::get('/movimentacoes/orcamento/{id}', [MovimentacaoOrcamentoController::class, 'showByOrcamento']);

    //Dashboard orcamento movimentacoes
    Route::get('/dashboard/movimentacoes', [MovimentacaoOrcamentoDashboardController::class, 'index'])
    ->name('dashboard.movimentacoes');

    Route::get('/dashboard/movimentacoes/data', [MovimentacaoOrcamentoDashboardController::class, 'data'])
    ->name('dashboard.movimentacoes.data');

       //exibe saldo cliente no pdv
    // Route::get('/cliente/{id}/saldo', [ClienteController::class, 'saldo']);

    Route::get('/api/cliente/financeiro/{id}', [ContaCorrenteController::class,
     'infoClienteFinanceiro']
    );   

    Route::get('/limites', [LimiteClienteController::class, 'index']);
    Route::get('/limites-view', [LimiteClienteController::class, 'tela'])->name('limites-view');
    Route::get('/limites/estourados', [LimiteClienteController::class, 'estourados']);
    Route::get('/limites/risco', [LimiteClienteController::class, 'risco']);
    Route::get('/limites/{id}', [LimiteClienteController::class, 'show']);
    Route::post('/clientes/{id}/bloquear', [LimiteClienteController::class, 'bloquear']);
    Route::post('/clientes/{id}/desbloquear', [LimiteClienteController::class, 'desbloquear']); 
    

    //Pagamento de Carteira
// 🔒 GRUPO DE ROTAS DE CRÉDITO E CARTEIRA (PROTEGIDAS POR AUTENTICAÇÃO)
Route::middleware(['auth'])->group(function () {

    // Tela de Renderização Principal
    Route::get('/financeiro/recebimento-credito', function () {
        $clientes = Cliente::whereHas('creditoAtivo')->orderBy('nome')->get();
        return view('credito.recebimento', compact('clientes'));
    });

    // Sub-grupo de Operações por Cliente
    Route::prefix('clientes/{id}/credito')->group(function () {
        Route::post('/pagar', [ClienteCreditoController::class, 'pagarCredito']);
        Route::post('/aumentar-limite', [ClienteCreditoController::class, 'aumentarLimite']);
    });

    // Rota dedicada a executar o estorno
    Route::post('credito/movimentacoes/{id}/estornar', [ClienteCreditoController::class, 'estornar']);

    // 💎 AS DUAS ROTAS DA IMPRESSÃO AGORA NO LUGAR CORRETO (DENTRO DO AUTH)
    Route::post('/clientes/credito/obter-pagamento', [ClienteCreditoController::class, 'obterPagamento']);
    Route::get('/clientes/credito/exibircomprovante/{id}', [ClienteCreditoController::class, 'exibirComprovante'])->name('credito.comprovante');

});




// Agrupadas por autenticação para garantir o user_id no PDV e na Gerência
Route::middleware(['auth'])->group(function () {
    
    // -----------------------------------------------------------------
    // FLUXO DE MOVIMENTAÇÃO DO OPERADOR (PDV INDIVIDUAL)
    // -----------------------------------------------------------------
    Route::post('/caixa/movimentacoes', [MovimentacaoCaixaController::class, 'store'])
        ->name('caixa.movimentacoes.store');

    Route::put('/caixa/movimentacoes/{caixa}', [MovimentacaoCaixaController::class, 'update'])
        ->name('caixa.movimentacoes.update');


    // -----------------------------------------------------------------
    // FLUXO GERENCIAL MULTI-CAIXAS (SAÍDAS EM REDE / RATEIO)
    // -----------------------------------------------------------------
    
    // ✅ ROTA ADICIONADA: Abre a interface da Blade com a tabela de caixas e o formulário
    Route::get('/gerencia/caixa/saidas', [MovimentacaoCaixaController::class, 'painelGerencialSaidas'])
        ->name('gerencia.caixa.painel_saidas');

    // Rota AJAX que processa o salvamento em segundo plano (Disparada pelo JS do botão)
    Route::post('/gerencia/caixa/registrar-saida-lote', [MovimentacaoCaixaController::class, 'registrarSaidaLote'])
        ->name('gerencia.caixa.registrar_saida_lote');
});

// Rota para abrir o histórico de saídas e permitir a reimpressão
Route::get('/gerencia/caixa/saidas/historico', [MovimentacaoCaixaController::class, 'historicoSaidas'])
    ->name('gerencia.caixa.saidas.historico');

// <!-- verificarSangriaPeriodicamente -->
Route::get(
    '/pdv/verificar-sangria/{caixa}',
    [PDVController::class, 'verificarSangriaAjax']
)->name('pdv.verificar.sangria');


// vendas com estoque negativo, com registro do estoque_divergencia
Route::prefix('estoque-divergencias')->name('estoque-divergencias.')->group(function () {
    Route::get('/', [EstoqueDivergenciaController::class, 'index'])->name('index');
    Route::get('/pdf', [EstoqueDivergenciaController::class, 'pdf'])->name('pdf');
    Route::get('/{id}', [EstoqueDivergenciaController::class, 'show'])->name('show');
});


// =====================================================
// Backup do Sistema
// =====================================================
Route::middleware(['auth'])
    ->prefix('backups')
    ->name('backups.')
    ->group(function () {

        Route::get('/', [BackupController::class, 'index'])
            ->name('index');

        Route::post('/gerar', [BackupController::class, 'gerar'])
            ->name('gerar');

        Route::post('/restaurar', [BackupController::class, 'restaurar'])
            ->name('restaurar');

        Route::get('/download/{arquivo}', [BackupController::class, 'download'])
            ->name('download');

        Route::delete('/{arquivo}', [BackupController::class, 'destroy'])
            ->name('destroy');
    });

    // Entregas
    Route::prefix('entregas')
        ->name('entregas.')
        ->group(function () {
            Route::get('/', [EntregaController::class, 'index'])->name('index');
            Route::get('/{entrega}', [EntregaController::class, 'show'])->name('show');

            Route::patch('/{entrega}/separar', [EntregaController::class, 'separar'])->name('separar');
            Route::patch('/{entrega}/carregar', [EntregaController::class, 'carregar'])->name('carregar');
            Route::patch('/{entrega}/rota', [EntregaController::class, 'enviarParaRota'])->name('rota');
            Route::patch('/{entrega}/confirmar', [EntregaController::class, 'confirmar'])->name('confirmar');
            Route::patch('/{entrega}/cancelar', [EntregaController::class, 'cancelar'])->name('cancelar');
            Route::get('/{entrega}/atribuir-equipe', [EntregaController::class, 'atribuirEquipe']) ->name('atribuir-equipe');
            Route::put('/{entrega}/atribuir-equipe', [EntregaController::class, 'salvarEquipe'])->name('salvar-equipe');
            Route::resource('pedidos', PedidoCompraController::class);
    });

   
  

    // EXPEDIÇÃO
    Route::prefix('expedicao')->name('expedicao.')->group(function () {
        Route::get('/', [ExpedicaoController::class, 'index'])
            ->name('index');

        Route::get('/romaneio/{romaneio}', [ExpedicaoController::class, 'show'])
            ->name('show');

        Route::get('/romaneio/{romaneio}/atribuir-equipe', [ExpedicaoController::class, 'atribuirEquipe'])
            ->name('atribuir-equipe');

        Route::put('/romaneio/{romaneio}/salvar-equipe', [ExpedicaoController::class, 'salvarEquipe'])
            ->name('salvar-equipe');

        Route::get('/romaneio/{romaneio}/operacao', [ExpedicaoController::class, 'operacao'])
            ->name('operacao');

        Route::post('/romaneio/{romaneio}/iniciar-separacao', [ExpedicaoController::class, 'iniciarSeparacao'])
            ->name('iniciar-separacao');

        Route::post('/romaneio/{romaneio}/iniciar-carregamento', [ExpedicaoController::class, 'iniciarCarregamento'])
            ->name('iniciar-carregamento');

        Route::post('/romaneio/{romaneio}/finalizar-carregamento', [ExpedicaoController::class, 'finalizarCarregamento'])
            ->name('finalizar-carregamento');

        Route::post('/romaneio/{romaneio}/liberar-rota', [ExpedicaoController::class, 'liberarRota'])
            ->name('liberar-rota');
    });

    // LOCALIZAÇÕES DE ESTOQUE
    Route::prefix('localizacoes-estoque')
    
    ->name('localizacoes-estoque.')
    ->group(function () {
        Route::get('/', [LocalizacaoEstoqueController::class, 'index'])->name('index');
        Route::get('/criar', [LocalizacaoEstoqueController::class, 'create'])->name('create');
        Route::post('/', [LocalizacaoEstoqueController::class, 'store'])->name('store');
        Route::get('/{localizacaoEstoque}/editar', [LocalizacaoEstoqueController::class, 'edit'])->name('edit');
        Route::put('/{localizacaoEstoque}', [LocalizacaoEstoqueController::class, 'update'])->name('update');
        Route::delete('/{localizacaoEstoque}', [LocalizacaoEstoqueController::class, 'destroy'])->name('destroy');
        Route::get('/romaneios/{romaneio}/imprimir', [RomaneioController::class, 'imprimir'])->name('romaneios.imprimir');
    });

    // Veiculos
    Route::prefix('veiculos')->name('veiculos.')->group(function () {
        Route::get('/', [VeiculoController::class, 'index'])->name('index');
        Route::get('/create', [VeiculoController::class, 'create'])->name('create');
        Route::post('/', [VeiculoController::class, 'store'])->name('store');
        Route::get('/{veiculo}', [VeiculoController::class, 'show'])->name('show');
        Route::get('/{veiculo}/edit', [VeiculoController::class, 'edit'])->name('edit');
        Route::put('/{veiculo}', [VeiculoController::class, 'update'])->name('update');
        Route::delete('/{veiculo}', [VeiculoController::class, 'destroy'])->name('destroy');
    });

        Route::resource('veiculos', VeiculoController::class);
  
        // Frota
        Route::prefix('frota')
            ->name('frota.')
            ->group(function () {

                Route::get(
                    'classes/{tipoVeiculo}',
                    [FrotaController::class, 'classesPorTipo']
                )->name('classes');

                Route::get(
                    'carrocerias/{classeVeiculo}',
                    [FrotaController::class, 'carroceriasPorClasse']
                )->name('carrocerias');

        });
        
    // ROMANEIOS
    // Route::prefix('romaneios')->name('romaneios.')->group(function () {
    //     Route::get('/', [RomaneioController::class, 'index'])->name('index');
    //     Route::get('/criar', [RomaneioController::class, 'create'])->name('create');
    //     Route::post('/', [RomaneioController::class, 'store'])->name('store');
    //     Route::get('/{romaneio}/imprimir', [RomaneioController::class, 'imprimir'])->name('imprimir');
    //     Route::post('/{romaneio}/cancelar', [RomaneioController::class, 'cancelar'])->name('cancelar');
    //     Route::get('/{romaneio}/separacao', [RomaneioController::class, 'separacao'])->name('separacao');
    //     Route::put('/{romaneio}/operacao', [RomaneioController::class, 'atualizarOperacao'])->name('operacao.update');
    //     Route::get('/{romaneio}', [RomaneioController::class, 'show'])->name('show');
    // });

    // ROMANEIOS
Route::prefix('romaneios')
    ->name('romaneios.')
    ->group(function () {
        Route::get(
            '/',
            [RomaneioController::class, 'index']
        )->name('index');

        Route::get(
            '/criar',
            [RomaneioController::class, 'create']
        )->name('create');

        Route::post(
            '/',
            [RomaneioController::class, 'store']
        )->name('store');

        Route::post(
            '/{romaneio}/registrar-impressao',
            [RomaneioController::class, 'registrarImpressao']
        )->name('registrar-impressao');

        Route::get(
            '/{romaneio}/imprimir',
            [RomaneioController::class, 'imprimir']
        )->name('imprimir');

        Route::post(
            '/{romaneio}/cancelar',
            [RomaneioController::class, 'cancelar']
        )->name('cancelar');

        Route::get(
            '/{romaneio}/separacao',
            [RomaneioController::class, 'separacao']
        )->name('separacao');

        Route::put(
            '/{romaneio}/operacao',
            [RomaneioController::class, 'atualizarOperacao']
        )->name('operacao.update');

        Route::get(
            '/{romaneio}',
            [RomaneioController::class, 'show']
        )->name('show');
    });
            

