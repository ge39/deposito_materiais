<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title>Depósito de Materiais</title>

    
    <link rel="icon" type="image/svg+xml"
          href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🏗️%3C/text%3E%3C/svg%3E">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- <style>
        html,
        body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }

        main {
            flex: 1;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu > .dropdown-menu {
            top: 0;
            left: 100%;
            margin-left: 0.1rem;
        }

        @media (max-width: 991px) {
            .dropdown-submenu > .dropdown-menu {
                left: 0;
                position: static;
                margin-left: 1rem;
            }
        }

        .dropdown-item.disabled,
        .nav-link.disabled {
            pointer-events: none;
            opacity: .55;
        }
    </style> -->

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

<?php
    $canAccessAdmin = auth()->check() && in_array(auth()->user()->nivel_acesso, ['admin', 'gerente']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="<?php echo e(route('dashboard')); ?>">
            🏗️ Depósito
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
                aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam me-1"></i>Produtos & Compras
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('produtos.index')); ?>">
                                <i class="bi bi-box me-2"></i>Estoque / Produtos
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('pedidos.index')); ?>">
                                <i class="bi bi-cart-check me-2"></i>Pedido de Compra / Lotes
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('orcamentos.index')); ?>">
                                <i class="bi bi-clipboard-data me-2"></i>Emissão Orçamento
                            </a>
                        </li>
                    </ul>
                </li>

                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-cash-stack me-1"></i>Vendas
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('pdv.index')); ?>">
                                <i class="bi bi-receipt-cutoff me-2"></i>PDV / Vendas
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item disabled" href="#">
                                <i class="bi bi-list-ul me-2"></i>Itens Venda
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('devolucoes.index')); ?>">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Troca / Devoluções
                            </a>
                        </li>
                    </ul>
                </li>

                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-arrow-repeat me-1"></i>Pós-Venda
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('devolucoes.index')); ?>">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Devoluções / Trocas
                            </a>
                        </li>
                    </ul>
                </li>

                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-truck me-1"></i>Logística
                    </a>

                    <ul class="dropdown-menu">

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('entregas.index')); ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Entregas
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('romaneios.index')); ?>">
                                <i class="bi bi-card-checklist me-2"></i>Romaneios
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('expedicao.index')); ?>">
                                <i class="bi bi-truck-front me-2"></i>Expedição
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('localizacoes-estoque.index')); ?>">
                                <i class="bi bi-geo-alt me-2"></i>Localizações de Estoque
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item disabled" href="#">
                                <i class="bi bi-signpost-2 me-2"></i>Rotas
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item disabled" href="#">
                                <i class="bi bi-truck me-2"></i>Frota
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item disabled" href="#">
                                <i class="bi bi-person-badge me-2"></i>Motoristas
                            </a>
                        </li>

                    </ul>
                </li>

                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear-wide-connected me-1"></i>Administração
                    </a>

                    <ul class="dropdown-menu">

                        
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
                                <i class="bi bi-folder2-open me-2"></i>Cadastros
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('users.index') : '#'); ?>">
                                        <i class="bi bi-person-gear me-2"></i>Usuários
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('empresa.index') : '#'); ?>">
                                        <i class="bi bi-building me-2"></i>Empresa
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('clientes.index') : '#'); ?>">
                                        <i class="bi bi-people me-2"></i>Clientes
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('fornecedores.index') : '#'); ?>">
                                        <i class="bi bi-truck me-2"></i>Fornecedores
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('funcionarios.index') : '#'); ?>">
                                        <i class="bi bi-person-badge me-2"></i>Funcionários
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo e(route('veiculos.index')); ?>">
                                        <i class="bi bi-truck-front me-2"></i>
                                        Veículos
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
                                <i class="bi bi-currency-dollar me-2"></i>Financeiro
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('sangria-config.index') : '#'); ?>">
                                        <i class="bi bi-cash-coin me-2"></i>Define Sangria
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('fechamento.lista') : '#'); ?>">
                                        <i class="bi bi-safe me-2"></i>Fechamento de Caixa
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('limites-view') : '#'); ?>">
                                        <i class="bi bi-credit-card-2-front me-2"></i>Controle Limite Crédito
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('auditoria_caixa.index') : '#'); ?>">
                                        <i class="bi bi-clipboard-check me-2"></i>Relatório Auditoria de Caixa
                                    </a>
                                </li>
                            </ul>
                        </li>

                        
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
                                <i class="bi bi-boxes me-2"></i>Controle de Estoque
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('estoque-divergencias.index') : '#'); ?>">
                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Divergências de Estoque
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-clipboard-check me-2"></i>Inventário Geral
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-arrow-repeat me-2"></i>Ajustes de Estoque
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-clock-history me-2"></i>Movimentações de Estoque
                                    </a>
                                </li>
                            </ul>
                        </li>

                        
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
                                <i class="bi bi-bar-chart-line me-2"></i>Relatórios
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('relatorio.reposicao') : '#'); ?>">
                                        <i class="bi bi-box-arrow-in-down me-2"></i>Orçamento / Repor Estoque
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('dashboard.movimentacoes') : '#'); ?>">
                                        <i class="bi bi-graph-up-arrow me-2"></i>Orçamento / Dashboard
                                    </a>
                                </li>
                            </ul>
                        </li>

                        
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
                                <i class="bi bi-shield-lock me-2"></i>Segurança
                            </a>

                            <ul class="dropdown-menu">

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('backups.index') : '#'); ?>">
                                        <i class="bi bi-database-check me-2"></i>Backup Manual
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-clock-history me-2"></i>Backup Automático
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-gear me-2"></i>Configuração do Backup
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-folder-check me-2"></i>Histórico de Backups
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-shield-check me-2"></i>Auditoria
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item disabled" href="#">
                                        <i class="bi bi-file-earmark-text me-2"></i>Logs do Sistema
                                    </a>
                                </li>

                            </ul>
                        </li>

                        
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
                                <i class="bi bi-tags me-2"></i>Promoções & Descontos
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('painel_promocao.index') : '#'); ?>">
                                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('promocoes.index') : '#'); ?>">
                                        <i class="bi bi-list-stars me-2"></i>Listar Promoções
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                                       href="<?php echo e($canAccessAdmin ? route('promocoes.create') : '#'); ?>">
                                        <i class="bi bi-plus-circle me-2"></i>Nova Promoção
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </li>

            </ul>

            
            <?php if(auth()->guard()->check()): ?>
                <div class="d-flex align-items-center text-white">
                    <span class="me-3">
                        <i class="bi bi-person-circle me-1"></i><?php echo e(Auth::user()->name); ?>

                    </span>

                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>

                        <button type="submit" class="btn btn-outline-light btn-sm">
                            Sair
                        </button>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>
</nav>

<main class="container mt-4">
 <?php if(isset($bloqueioEdicao)): ?>
    <div id="configuracao-bloqueio-edicao"
         class="d-none"
         data-recurso-tipo="<?php echo e($bloqueioEdicaoRecursoTipo ?? ''); ?>"
         data-recurso-id="<?php echo e($bloqueioEdicaoRecursoId ?? ''); ?>"
         data-token="<?php echo e($bloqueioEdicaoToken ?? ''); ?>"
         data-pode-editar="<?php echo e(($podeEditarArquivo ?? false)
                ? '1'
                : '0'); ?>"
         data-mensagem="<?php echo e($mensagemBloqueioEdicao ?? ''); ?>"
         data-url-adquirir="<?php echo e(route('edicao-bloqueios.adquirir')); ?>"
         data-url-renovar="<?php echo e(route('edicao-bloqueios.renovar')); ?>"
         data-url-liberar="<?php echo e(route('edicao-bloqueios.liberar')); ?>"
         data-url-retorno="<?php echo e(route('entregas.index')); ?>">
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const elementoConfiguracao =
                    document.getElementById(
                        'configuracao-bloqueio-edicao'
                    );

                if (! elementoConfiguracao) {
                    return;
                }

                const configuracao = {
                    recursoTipo:
                        elementoConfiguracao
                            .dataset
                            .recursoTipo,

                    recursoId:
                        Number(
                            elementoConfiguracao
                                .dataset
                                .recursoId
                        ),

                    token:
                        elementoConfiguracao
                            .dataset
                            .token,

                    podeEditar:
                        elementoConfiguracao
                            .dataset
                            .podeEditar
                        === '1',

                    mensagem:
                        elementoConfiguracao
                            .dataset
                            .mensagem,

                    urlAdquirir:
                        elementoConfiguracao
                            .dataset
                            .urlAdquirir,

                    urlRenovar:
                        elementoConfiguracao
                            .dataset
                            .urlRenovar,

                    urlLiberar:
                        elementoConfiguracao
                            .dataset
                            .urlLiberar,

                    urlRetorno:
                        elementoConfiguracao
                            .dataset
                            .urlRetorno,

                    csrf:
                        document.querySelector(
                            'meta[name="csrf-token"]'
                        )?.content
                        ?? '',
                };

                const areaPrincipal =
                    document.querySelector(
                        'main'
                    );

                if (
                    ! areaPrincipal
                    || ! configuracao.recursoTipo
                    || ! configuracao.recursoId
                ) {
                    return;
                }

                const formularios =
                    Array.from(
                        areaPrincipal
                            .querySelectorAll(
                                'form'
                            )
                    );

                const formulariosMutacao =
                    formularios.filter(
                        function (formulario) {
                            return (
                                formulario
                                    .getAttribute(
                                        'method'
                                    )
                                ?? 'GET'
                            ).toUpperCase()
                                !== 'GET';
                        }
                    );

                const formulariosNavegacao =
                    formularios.filter(
                        function (formulario) {
                            return (
                                formulario
                                    .getAttribute(
                                        'method'
                                    )
                                ?? 'GET'
                            ).toUpperCase()
                                === 'GET';
                        }
                    );

                let formularioEmEnvio =
                    false;

                let navegacaoEmAndamento =
                    false;

                let bloqueioProprio =
                    configuracao.podeEditar;

                let renovacaoEmAndamento =
                    false;

                let aquisicaoEmAndamento =
                    false;

                let liberacaoEmAndamento =
                    null;

                let intervaloRenovacao =
                    null;

                let intervaloAquisicao =
                    null;

                let mensagemErroLiberacao =
                    'Não foi possível finalizar a edição. Tente novamente.';

                function pararRenovacao() {
                    if (! intervaloRenovacao) {
                        return;
                    }

                    window.clearInterval(
                        intervaloRenovacao
                    );

                    intervaloRenovacao =
                        null;
                }

                function pararAquisicao() {
                    if (! intervaloAquisicao) {
                        return;
                    }

                    window.clearInterval(
                        intervaloAquisicao
                    );

                    intervaloAquisicao =
                        null;
                }

                function inserirToken(
                    formulario
                ) {
                    let campo =
                        formulario
                            .querySelector(
                                'input[name="bloqueio_edicao_token"]'
                            );

                    if (! campo) {
                        campo =
                            document.createElement(
                                'input'
                            );

                        campo.type =
                            'hidden';

                        campo.name =
                            'bloqueio_edicao_token';

                        formulario.appendChild(
                            campo
                        );
                    }

                    campo.value =
                        configuracao.token;
                }

                function dadosBloqueio() {
                    return {
                        recurso_tipo:
                            configuracao
                                .recursoTipo,

                        recurso_id:
                            configuracao
                                .recursoId,

                        token:
                            configuracao.token,
                    };
                }

                function marcarBloqueioLiberado() {
                    bloqueioProprio =
                        false;

                    configuracao.podeEditar =
                        false;

                    pararRenovacao();
                    pararAquisicao();
                }

                function tornarSomenteConsulta(
                    mensagem
                ) {
                    configuracao.podeEditar =
                        false;

                    formulariosMutacao.forEach(
                        function (formulario) {
                            formulario
                                .querySelectorAll(
                                    'input:not([type="hidden"]), select, textarea, button'
                                )
                                .forEach(
                                    function (controle) {
                                        if (
                                            ! controle
                                                .disabled
                                        ) {
                                            controle
                                                .dataset
                                                .desabilitadoPeloBloqueio =
                                                    '1';

                                            controle.disabled =
                                                true;
                                        }
                                    }
                                );
                        }
                    );

                    areaPrincipal
                        .querySelectorAll(
                            '[data-requer-edicao]'
                        )
                        .forEach(
                            function (elemento) {
                                if (
                                    ! elemento
                                        .classList
                                        .contains(
                                            'disabled'
                                        )
                                ) {
                                    elemento
                                        .dataset
                                        .desabilitadoPeloBloqueio =
                                            '1';
                                }

                                elemento
                                    .classList
                                    .add(
                                        'disabled'
                                    );

                                elemento
                                    .setAttribute(
                                        'aria-disabled',
                                        'true'
                                    );
                            }
                        );

                    const alerta =
                        document.getElementById(
                            'alerta-bloqueio-edicao'
                        );

                    if (! alerta) {
                        return;
                    }

                    alerta.className =
                        'alert alert-warning border-warning shadow-sm';

                    alerta.innerHTML =
                        '<div class="fw-bold mb-1">'
                        + '<i class="bi bi-lock-fill me-1"></i>'
                        + 'Arquivo indisponível para edição'
                        + '</div>'
                        + '<div class="mensagem-bloqueio"></div>'
                        + '<small class="d-block mt-1">'
                        + 'Os dados permanecem disponíveis somente para consulta.'
                        + '</small>';

                    alerta
                        .querySelector(
                            '.mensagem-bloqueio'
                        )
                        .textContent =
                            mensagem
                            ?? 'Esta entrega está sendo editada em outra sessão.';
                }

                async function finalizarEdicao() {
                    const botao =
                        document.getElementById(
                            'finalizar-edicao-arquivo'
                        );

                    if (botao) {
                        botao.disabled =
                            true;
                    }

                    const liberou =
                        await liberarBloqueio();

                    if (! liberou) {
                        if (botao) {
                            botao.disabled =
                                false;
                        }

                        window.alert(
                            mensagemErroLiberacao
                        );

                        return;
                    }

                    navegacaoEmAndamento =
                        true;

                    window.location.assign(
                        configuracao.urlRetorno
                    );
                }

                function atualizarAlertaEdicao() {
                    const alerta =
                        document.getElementById(
                            'alerta-bloqueio-edicao'
                        );

                    if (! alerta) {
                        return;
                    }

                    alerta.className =
                        'alert alert-info border-info py-2 d-flex align-items-center justify-content-between gap-3';

                    alerta.innerHTML =
                        '<span>'
                        + '<i class="bi bi-unlock-fill me-1"></i>'
                        + 'Este arquivo está reservado para sua edição.'
                        + '</span>'
                        + '<button type="button" '
                        + 'id="finalizar-edicao-arquivo" '
                        + 'class="btn btn-outline-dark btn-sm">'
                        + '<i class="bi bi-check2-circle me-1"></i>'
                        + 'Finalizar edição'
                        + '</button>';

                    document
                        .getElementById(
                            'finalizar-edicao-arquivo'
                        )
                        ?.addEventListener(
                            'click',
                            finalizarEdicao
                        );
                }

                function iniciarRenovacao() {
                    if (intervaloRenovacao) {
                        return;
                    }

                    intervaloRenovacao =
                        window.setInterval(
                            renovarBloqueio,
                            60000
                        );
                }

                function iniciarEsperaEdicao() {
                    if (intervaloAquisicao) {
                        return;
                    }

                    intervaloAquisicao =
                        window.setInterval(
                            tentarAdquirirBloqueio,
                            3000
                        );
                }

                function habilitarEdicao(
                    token
                ) {
                    configuracao.token =
                        token;

                    configuracao.podeEditar =
                        true;

                    configuracao.mensagem =
                        null;

                    bloqueioProprio =
                        true;

                    pararAquisicao();

                    areaPrincipal
                        .querySelectorAll(
                            '[data-desabilitado-pelo-bloqueio="1"]'
                        )
                        .forEach(
                            function (elemento) {
                                if (
                                    'disabled'
                                    in elemento
                                ) {
                                    elemento.disabled =
                                        false;
                                }

                                elemento
                                    .classList
                                    .remove(
                                        'disabled'
                                    );

                                elemento
                                    .removeAttribute(
                                        'aria-disabled'
                                    );

                                delete elemento
                                    .dataset
                                    .desabilitadoPeloBloqueio;
                            }
                        );

                    formulariosMutacao.forEach(
                        function (formulario) {
                            inserirToken(
                                formulario
                            );
                        }
                    );

                    atualizarAlertaEdicao();
                    iniciarRenovacao();
                }

                async function tentarAdquirirBloqueio() {
                    if (
                        aquisicaoEmAndamento
                        || configuracao.podeEditar
                        || navegacaoEmAndamento
                        || document.visibilityState
                            === 'hidden'
                    ) {
                        return;
                    }

                    aquisicaoEmAndamento =
                        true;

                    try {
                        const resposta =
                            await fetch(
                                configuracao
                                    .urlAdquirir,
                                {
                                    method:
                                        'POST',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'Content-Type':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            configuracao
                                                .csrf,
                                    },

                                    credentials:
                                        'same-origin',

                                    cache:
                                        'no-store',

                                    body:
                                        JSON.stringify({
                                            recurso_tipo:
                                                configuracao
                                                    .recursoTipo,

                                            recurso_id:
                                                configuracao
                                                    .recursoId,
                                        }),
                                }
                            );

                        if (! resposta.ok) {
                            return;
                        }

                        const estado =
                            await resposta.json();

                        if (
                            estado.pode_editar
                            && estado.token
                        ) {
                            configuracao.token =
                                estado.token;

                            configuracao.podeEditar =
                                true;

                            bloqueioProprio =
                                true;

                            /*
                            * Recarregamos para recuperar os dados
                            * mais recentes antes da edição.
                            */
                            window.location.reload();

                            return;
                        }

                        if (
                            estado.mensagem
                            && estado.mensagem
                                !== configuracao
                                    .mensagem
                        ) {
                            configuracao.mensagem =
                                estado.mensagem;

                            tornarSomenteConsulta(
                                estado.mensagem
                            );
                        }
                    } catch (erro) {
                        /*
                        * A próxima tentativa sincronizará novamente.
                        */
                    } finally {
                        aquisicaoEmAndamento =
                            false;
                    }
                }

                async function renovarBloqueio() {
                    if (
                        renovacaoEmAndamento
                        || ! bloqueioProprio
                        || navegacaoEmAndamento
                        || document.visibilityState
                            === 'hidden'
                    ) {
                        return;
                    }

                    renovacaoEmAndamento =
                        true;

                    try {
                        const resposta =
                            await fetch(
                                configuracao
                                    .urlRenovar,
                                {
                                    method:
                                        'PATCH',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'Content-Type':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            configuracao
                                                .csrf,
                                    },

                                    credentials:
                                        'same-origin',

                                    cache:
                                        'no-store',

                                    body:
                                        JSON.stringify(
                                            dadosBloqueio()
                                        ),
                                }
                            );

                        if (! resposta.ok) {
                            const dados =
                                await resposta
                                    .json()
                                    .catch(
                                        function () {
                                            return {};
                                        }
                                    );

                            bloqueioProprio =
                                false;

                            configuracao
                                .podeEditar =
                                    false;

                            pararRenovacao();

                            tornarSomenteConsulta(
                                dados.message
                                ?? 'Sua sessão de edição não está mais ativa.'
                            );

                            iniciarEsperaEdicao();
                        }
                    } catch (erro) {
                        /*
                        * Uma falha transitória não remove o bloqueio.
                        */
                    } finally {
                        renovacaoEmAndamento =
                            false;
                    }
                }

                async function executarLiberacao() {
                    if (! bloqueioProprio) {
                        return true;
                    }

                    try {
                        const resposta =
                            await fetch(
                                configuracao
                                    .urlLiberar,
                                {
                                    method:
                                        'POST',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'Content-Type':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            configuracao
                                                .csrf,
                                    },

                                    credentials:
                                        'same-origin',

                                    cache:
                                        'no-store',

                                    keepalive:
                                        true,

                                    body:
                                        JSON.stringify(
                                            dadosBloqueio()
                                        ),
                                }
                            );

                        if (resposta.ok) {
                            marcarBloqueioLiberado();

                            return true;
                        }

                        const dados =
                            await resposta
                                .json()
                                .catch(
                                    function () {
                                        return {};
                                    }
                                );

                        mensagemErroLiberacao =
                            dados.message
                            ?? 'Não foi possível liberar a edição. Tente novamente.';

                        return false;
                    } catch (erro) {
                        mensagemErroLiberacao =
                            'Não foi possível comunicar com o servidor para liberar a edição.';

                        return false;
                    }
                }

                async function liberarBloqueio() {
                    if (! bloqueioProprio) {
                        return true;
                    }

                    if (liberacaoEmAndamento) {
                        return liberacaoEmAndamento;
                    }

                    liberacaoEmAndamento =
                        executarLiberacao();

                    try {
                        return await liberacaoEmAndamento;
                    } finally {
                        liberacaoEmAndamento =
                            null;
                    }
                }

                async function navegarAposLiberar(
                    destino
                ) {
                    if (navegacaoEmAndamento) {
                        return;
                    }

                    navegacaoEmAndamento =
                        true;

                    const liberou =
                        await liberarBloqueio();

                    if (! liberou) {
                        navegacaoEmAndamento =
                            false;

                        window.alert(
                            mensagemErroLiberacao
                        );

                        return;
                    }

                    window.location.assign(
                        destino
                    );
                }

                function devePreservarBloqueio(
                    elemento,
                    destino
                ) {
                    if (
                        elemento?.dataset
                            ?.liberarBloqueioEdicao
                        === '1'
                    ) {
                        return false;
                    }

                    if (
                        elemento?.dataset
                            ?.preservarBloqueioEdicao
                        === '1'
                    ) {
                        return true;
                    }

                    if (
                        configuracao.recursoTipo
                            !== 'entrega'
                        || destino.origin
                            !== window.location.origin
                    ) {
                        return false;
                    }

                    const caminhoEntrega =
                        '/entregas/'
                        + configuracao.recursoId;

                    if (
                        destino.pathname
                            === caminhoEntrega
                        || destino.pathname.startsWith(
                            caminhoEntrega + '/'
                        )
                    ) {
                        return true;
                    }

                    return /^\/romaneios\/\d+(?:\/|$)/
                        .test(
                            destino.pathname
                        );
                }

                formulariosMutacao.forEach(
                    function (formulario) {
                        formulario.addEventListener(
                            'submit',
                            function (evento) {
                                if (
                                    ! configuracao
                                        .podeEditar
                                ) {
                                    evento.preventDefault();

                                    return;
                                }

                                if (
                                    ! evento.defaultPrevented
                                ) {
                                    /*
                                    * Formulários operacionais mantêm
                                    * o bloqueio durante o redirect.
                                    */
                                    formularioEmEnvio =
                                        true;
                                }
                            }
                        );

                        const enviarNativamente =
                            formulario.submit.bind(
                                formulario
                            );

                        formulario.submit =
                            function () {
                                if (
                                    ! configuracao
                                        .podeEditar
                                ) {
                                    return;
                                }

                                formularioEmEnvio =
                                    true;

                                enviarNativamente();
                            };
                    }
                );

                formulariosNavegacao.forEach(
                    function (formulario) {
                        formulario.addEventListener(
                            'submit',
                            async function (evento) {
                                if (
                                    ! bloqueioProprio
                                    || navegacaoEmAndamento
                                ) {
                                    return;
                                }

                                const destino =
                                    new URL(
                                        formulario.action
                                        || window.location.href,
                                        window.location.origin
                                    );

                                if (
                                    devePreservarBloqueio(
                                        formulario,
                                        destino
                                    )
                                ) {
                                    navegacaoEmAndamento =
                                        true;

                                    return;
                                }

                                evento.preventDefault();

                                const dados =
                                    new FormData(
                                        formulario
                                    );

                                dados.forEach(
                                    function (
                                        valor,
                                        chave
                                    ) {
                                        destino
                                            .searchParams
                                            .append(
                                                chave,
                                                valor
                                            );
                                    }
                                );

                                await navegarAposLiberar(
                                    destino.toString()
                                );
                            }
                        );
                    }
                );

                areaPrincipal.addEventListener(
                    'click',
                    function (evento) {
                        const elemento =
                            evento.target.closest(
                                '[data-requer-edicao]'
                            );

                        if (
                            elemento
                            && ! configuracao
                                .podeEditar
                        ) {
                            evento.preventDefault();
                            evento.stopPropagation();
                        }
                    },
                    true
                );

                document.addEventListener(
                    'click',
                    async function (evento) {
                        const link =
                            evento.target.closest(
                                'a[href]'
                            );

                        if (
                            ! link
                            || ! bloqueioProprio
                            || navegacaoEmAndamento
                            || evento.defaultPrevented
                            || evento.button !== 0
                            || evento.ctrlKey
                            || evento.metaKey
                            || evento.shiftKey
                            || evento.altKey
                            || link.target === '_blank'
                            || link.hasAttribute(
                                'download'
                            )
                            || link.classList
                                .contains(
                                    'disabled'
                                )
                        ) {
                            return;
                        }

                        const href =
                            link.getAttribute(
                                'href'
                            );

                        if (
                            ! href
                            || href === '#'
                            || href.startsWith('#')
                            || href.startsWith(
                                'javascript:'
                            )
                            || href.startsWith(
                                'mailto:'
                            )
                            || href.startsWith(
                                'tel:'
                            )
                        ) {
                            return;
                        }

                        const destino =
                            new URL(
                                link.href,
                                window.location.origin
                            );

                        if (
                            devePreservarBloqueio(
                                link,
                                destino
                            )
                        ) {
                            navegacaoEmAndamento =
                                true;

                            return;
                        }

                        evento.preventDefault();
                        evento.stopImmediatePropagation();

                        await navegarAposLiberar(
                            destino.toString()
                        );
                    },
                    true
                );

                const formularioLogout =
                    document.querySelector(
                        'form[action$="/logout"]'
                    );

                formularioLogout?.addEventListener(
                    'submit',
                    async function (evento) {
                        if (
                            ! bloqueioProprio
                            || navegacaoEmAndamento
                        ) {
                            return;
                        }

                        evento.preventDefault();

                        const liberou =
                            await liberarBloqueio();

                        if (! liberou) {
                            window.alert(
                                mensagemErroLiberacao
                            );

                            return;
                        }

                        formularioEmEnvio =
                            true;

                        HTMLFormElement
                            .prototype
                            .submit
                            .call(
                                formularioLogout
                            );
                    }
                );

                function liberarAoFechar() {
                    if (
                        ! bloqueioProprio
                        || formularioEmEnvio
                        || navegacaoEmAndamento
                    ) {
                        return;
                    }

                    const dados =
                        new FormData();

                    dados.append(
                        '_token',
                        configuracao.csrf
                    );

                    dados.append(
                        'recurso_tipo',
                        configuracao
                            .recursoTipo
                    );

                    dados.append(
                        'recurso_id',
                        configuracao
                            .recursoId
                    );

                    dados.append(
                        'token',
                        configuracao.token
                    );

                    navigator.sendBeacon(
                        configuracao
                            .urlLiberar,
                        dados
                    );
                }

                if (configuracao.podeEditar) {
                    habilitarEdicao(
                        configuracao.token
                    );
                } else {
                    bloqueioProprio =
                        false;

                    tornarSomenteConsulta(
                        configuracao.mensagem
                    );

                    iniciarEsperaEdicao();
                }

                document.addEventListener(
                    'visibilitychange',
                    function () {
                        if (
                            document.visibilityState
                            !== 'visible'
                            || navegacaoEmAndamento
                        ) {
                            return;
                        }

                        if (
                            configuracao.podeEditar
                        ) {
                            renovarBloqueio();
                        } else {
                            tentarAdquirirBloqueio();
                        }
                    }
                );

                if (
                    window.navigation
                    && typeof window.navigation
                        .addEventListener
                        === 'function'
                ) {
                    window.navigation.addEventListener(
                        'navigate',
                        function (evento) {
                            if (
                                evento.navigationType
                                    !== 'traverse'
                                || ! bloqueioProprio
                                || ! evento.destination
                                    ?.url
                            ) {
                                return;
                            }

                            const destino =
                                new URL(
                                    evento.destination.url,
                                    window.location.origin
                                );

                            if (
                                devePreservarBloqueio(
                                    null,
                                    destino
                                )
                            ) {
                                /*
                                * Voltar ou avançar entre páginas do
                                * mesmo fluxo operacional não libera
                                * o bloqueio da entrega.
                                */
                                navegacaoEmAndamento =
                                    true;
                            }
                        }
                    );
                }

                window.addEventListener(
                    'pageshow',
                    function (evento) {
                        if (! evento.persisted) {
                            return;
                        }

                        /*
                        * Uma página operacional restaurada pelo
                        * BFCache não pode reutilizar controles e
                        * token mantidos apenas na memória.
                        * O reload executa novamente o middleware.
                        */
                        window.location.reload();
                    }
                );

                window.addEventListener(
                    'pagehide',
                    liberarAoFechar
                );
            }
        );
    </script>
<?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>
</main>

<?php if(!request()->routeIs('pdv.*')): ?>
    <footer class="mt-5 py-3 border-top bg-light text-center text-muted">
        <small>
            © <?php echo e(date('Y')); ?> <?php echo e(config('app.name', 'Depósito de Materiais')); ?> — JMFSoftware2017
        </small>
    </footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dropdown-submenu .dropdown-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const submenu = this.nextElementSibling;

                document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function (menu) {
                    if (menu !== submenu) {
                        menu.classList.remove('show');
                    }
                });

                if (submenu) {
                    submenu.classList.toggle('show');
                }
            });
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function (menu) {
                menu.classList.remove('show');
            });
        });
    });
</script>


<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/layouts/app.blade.php ENDPATH**/ ?>