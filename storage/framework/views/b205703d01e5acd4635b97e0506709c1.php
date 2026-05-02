<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<title>🏗️ Depósito de Materiais</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* Submenu flyout */
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
    }
}
</style>
</head>
<body>

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

        <!-- Cadastro -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-lines-fill me-1"></i>Cadastro
          </a>
          <ul class="dropdown-menu">
            <!-- <li><a class="dropdown-item" href="<?php echo e(route('clientes.index')); ?>"><i class="bi bi-people me-2"></i>Clientes</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('fornecedores.index')); ?>"><i class="bi bi-truck me-2"></i>Fornecedores</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('funcionarios.index')); ?>"><i class="bi bi-person-badge me-2"></i>Funcionários</a></li> -->
            <li><a class="dropdown-item" href="<?php echo e(route('users.index')); ?>"><i class="bi bi-person-gear me-2"></i>Usuários</a></li>
            <!-- <li><a class="dropdown-item" href="<?php echo e(route('empresa.index')); ?>"><i class="bi bi-building me-2"></i>Empresa</a></li> -->
          </ul>
        </li>

        <!-- Produtos & Compras -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-box-seam me-1"></i>Produtos & Compras
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('produtos.index')); ?>"><i class="bi bi-box me-2"></i>Estoque</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('pedidos.index')); ?>"><i class="bi bi-cart-check me-2"></i>Pedido de Compra/Lotes</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('orcamentos.index')); ?>"><i class="bi bi-clipboard-data me-2"></i>Pedido/Orçamento/Clientes</a></li>
          </ul>
        </li>

        <!-- Vendas -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-cash-stack me-1"></i>Vendas
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('pdv.index')); ?>"><i class="bi bi-receipt-cutoff me-2"></i>Vendas</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-list-ul me-2"></i>Itens Venda</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('devolucoes.index')); ?>"><i class="bi bi-list-ul me-2"></i>Troca/Devoluções</a></li>
          </ul>
        </li>

        <!-- Pós-Venda -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-arrow-repeat me-1"></i>Pós-Venda
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo e(route('devolucoes.index')); ?>"><i class="bi bi-arrow-counterclockwise me-2"></i>Devoluções/Trocas</a></li>
          </ul>
        </li>

        <!-- Logística -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-truck me-1"></i>Logística
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#"><i class="bi bi-truck-front me-2"></i>Frotas</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-geo-alt me-2"></i>Entregas</a></li>
          </ul>
        </li>

       <!-- Administração -->
<?php
    $canAccessAdmin = auth()->check() && in_array(auth()->user()->nivel_acesso, ['admin', 'gerente']);
?>

<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#"
     data-bs-toggle="dropdown">
    <i class="bi bi-gear-wide-connected me-1"></i>Administração
  </a>
  <ul class="dropdown-menu">
    <li>
        <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
           href="<?php echo e($canAccessAdmin ? route('users.index') : '#'); ?>">
           <i class="bi bi-person-gear me-2"></i>Gerenciar Usuários
        </a>
    </li>
    <li>
        <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
           href="<?php echo e($canAccessAdmin ? route('empresa.index') : '#'); ?>">
           <i class="bi bi-building me-2"></i>Empresa
        </a>
    </li>
	          <li><a class="dropdown-item" href="<?php echo e(route('clientes.index')); ?>"><i class="bi bi-people me-2"></i>Clientes</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('fornecedores.index')); ?>"><i class="bi bi-truck me-2"></i>Fornecedores</a></li>
            <li><a class="dropdown-item" href="<?php echo e(route('funcionarios.index')); ?>"><i class="bi bi-person-badge me-2"></i>Funcionários</a></li>
    	<li><hr class="dropdown-divider"></li>
       <li class="dropdown-submenu">
        <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
            <i class="bi bi-tag me-2"></i>Financeiro
        </a>
        <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
                   href="<?php echo e($canAccessAdmin ? route('sangria-config.index') : '#'); ?>">
                   <i class="bi bi-list-stars me-2"></i>Define Sangria
                </a>
            </li>
             <li>
                <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
                   href="<?php echo e($canAccessAdmin ? route('fechamento.lista') : '#'); ?>">
                   <i class="bi bi-list-stars me-2"></i>Fechamento de Caixa
                </a>
            </li>
             <li>
                <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
                   href="<?php echo e($canAccessAdmin ? route('limites-view') : '#'); ?>">
                   <i class="bi bi-list-stars me-2"></i>ControleLimite Credito
                </a>
            </li>
            <li>
                <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>"
                  href="<?php echo e($canAccessAdmin ? route('auditoria_caixa.index') : '#'); ?>">
                  <i class="bi bi-list-stars me-2"></i>Relatório Auditoria de Caixa
                </a>
            </li>
           
        </ul>
    </li>
      <li><hr class="dropdown-divider"></li>
    <li class="dropdown-submenu">
        <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
            <i class="bi bi-tag me-2"></i>Promoções & Descontos
        </a>
        <ul class="dropdown-menu">
             <li>
                <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
                   href="<?php echo e($canAccessAdmin ? route('painel_promocao.index') : '#'); ?>">
                   <i class="bi bi-list-stars me-2"></i>Dashboard
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
     <li class="dropdown-submenu">
        <a class="dropdown-item dropdown-toggle <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" href="#">
            <i class="bi bi-tag me-2"></i>Relatórios
        </a>
        <ul class="dropdown-menu">
             <li>
                <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
                   href="<?php echo e($canAccessAdmin ? route('relatorio.reposicao') : '#'); ?>">
                   <i class="bi bi-list-stars me-2"></i>Orcamento / Repor/Estoque
                </a>
            </li>
            <li>
                <a class="dropdown-item <?php echo e(!$canAccessAdmin ? 'disabled' : ''); ?>" 
                   href="<?php echo e($canAccessAdmin ? route('dashboard.movimentacoes') : '#'); ?>">
                   <i class="bi bi-list-stars me-2"></i>Orcamento / Dashboard
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

      <!-- Usuário logado -->
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
</nav>

<div class="container mt-4">
    <?php echo $__env->yieldContent('content'); ?>
</div>

<footer class="mt-5 py-3 border-top bg-light text-center text-muted">
    <small>
        © <?php echo e(date('Y')); ?> <?php echo e(config('app.name') .' -  JMFSoftware2017'); ?> — Todos os direitos reservados.
    </small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      // Submenus flyout
      document.querySelectorAll('.dropdown-submenu .dropdown-toggle').forEach(function(toggle) {
          toggle.addEventListener('click', function(e) {
              e.preventDefault();
              e.stopPropagation();
              let submenu = this.nextElementSibling;
              if (submenu) {
                  submenu.classList.toggle('show');
              }
          });
      });

      document.addEventListener('click', function() {
          document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
              menu.classList.remove('show');
          });
      });
  });
</script>

    <!-- JS do Bootstrap e app.js -->
        <!-- <script src="<?php echo e(asset('js/app.js')); ?>"></script> -->

        <!-- Aqui serão inseridos os scripts específicos de cada página -->
    <?php echo $__env->yieldPushContent('scripts'); ?>
    
</body>


</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/layouts/app.blade.php ENDPATH**/ ?>