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

    <style>
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
    </style>
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
                                <i class="bi bi-box me-2"></i>Estoque
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('pedidos.index')); ?>">
                                <i class="bi bi-cart-check me-2"></i>Pedido de Compra/Lotes
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
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Troca/Devoluções
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
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Devoluções/Trocas
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
                            <a class="dropdown-item disabled" href="#">
                                <i class="bi bi-truck-front me-2"></i>Frotas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item disabled" href="#">
                                <i class="bi bi-geo-alt me-2"></i>Entregas
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
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('users.index') : '#'); ?>"><i class="bi bi-person-gear me-2"></i>Usuários</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('empresa.index') : '#'); ?>"><i class="bi bi-building me-2"></i>Empresa</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('clientes.index') : '#'); ?>"><i class="bi bi-people me-2"></i>Clientes</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('fornecedores.index') : '#'); ?>"><i class="bi bi-truck me-2"></i>Fornecedores</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('funcionarios.index') : '#'); ?>"><i class="bi bi-person-badge me-2"></i>Funcionários</a></li>
    </ul>
</li>

<li><hr class="dropdown-divider"></li>


<li class="dropdown-submenu">
    <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
        <i class="bi bi-currency-dollar me-2"></i>Financeiro
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('sangria-config.index') : '#'); ?>"><i class="bi bi-cash-coin me-2"></i>Define Sangria</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('fechamento.lista') : '#'); ?>"><i class="bi bi-safe me-2"></i>Fechamento de Caixa</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('limites-view') : '#'); ?>"><i class="bi bi-credit-card-2-front me-2"></i>Controle Limite Crédito</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('auditoria_caixa.index') : '#'); ?>"><i class="bi bi-clipboard-check me-2"></i>Relatório Auditoria de Caixa</a></li>
    </ul>
</li>


<li class="dropdown-submenu">
    <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
        <i class="bi bi-boxes me-2"></i>Controle de Estoque
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('estoque-divergencias.index') : '#'); ?>"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Divergências de Estoque</a></li>
        <li><a class="dropdown-item disabled" href="#"><i class="bi bi-clipboard-check me-2"></i>Inventário Geral</a></li>
        <li><a class="dropdown-item disabled" href="#"><i class="bi bi-arrow-repeat me-2"></i>Ajustes de Estoque</a></li>
        <li><a class="dropdown-item disabled" href="#"><i class="bi bi-clock-history me-2"></i>Movimentações de Estoque</a></li>
    </ul>
</li>


<li class="dropdown-submenu">
    <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
        <i class="bi bi-bar-chart-line me-2"></i>Relatórios
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('relatorio.reposicao') : '#'); ?>"><i class="bi bi-box-arrow-in-down me-2"></i>Orçamento / Repor Estoque</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('dashboard.movimentacoes') : '#'); ?>"><i class="bi bi-graph-up-arrow me-2"></i>Orçamento / Dashboard</a></li>
    </ul>
</li>


<li class="dropdown-submenu">
    <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
        <i class="bi bi-shield-lock me-2"></i>Segurança
    </a>

    <ul class="dropdown-menu">

        
        <li>
            <a class="dropdown-item disabled" href="#">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>

        <li><hr class="dropdown-divider"></li>

        
        <li>
            <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
               href="<?php echo e($canAccessAdmin ? route('backups.index') : '#'); ?>">
                <i class="bi bi-database-check me-2"></i>
                Backup Manual
            </a>
        </li>

        <li>
            <a class="dropdown-item disabled" href="#">
                <i class="bi bi-clock-history me-2"></i>
                Backup Automático
            </a>
        </li>

        <li>
            <a class="dropdown-item disabled" href="#">
                <i class="bi bi-gear me-2"></i>
                Configuração do Backup
            </a>
        </li>

        <li>
            <a class="dropdown-item disabled" href="#">
                <i class="bi bi-folder-check me-2"></i>
                Histórico de Backups
            </a>
        </li>

        <li><hr class="dropdown-divider"></li>

        
        <li>
            <a class="dropdown-item disabled" href="#">
                <i class="bi bi-shield-check me-2"></i>
                Auditoria
            </a>
        </li>

        <li>
            <a class="dropdown-item disabled" href="#">
                <i class="bi bi-file-earmark-text me-2"></i>
                Logs do Sistema
            </a>
        </li>

    </ul>
</li>


<li class="dropdown-submenu">
    <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
        <i class="bi bi-tags me-2"></i>Promoções & Descontos
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('painel_promocao.index') : '#'); ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('promocoes.index') : '#'); ?>"><i class="bi bi-list-stars me-2"></i>Listar Promoções</a></li>
        <li><a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="<?php echo e($canAccessAdmin ? route('promocoes.create') : '#'); ?>"><i class="bi bi-plus-circle me-2"></i>Nova Promoção</a></li>
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