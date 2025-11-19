<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
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
            <!-- <li><a class="dropdown-item" href="{{ route('clientes.index') }}"><i class="bi bi-people me-2"></i>Clientes</a></li>
            <li><a class="dropdown-item" href="{{ route('fornecedores.index') }}"><i class="bi bi-truck me-2"></i>Fornecedores</a></li>
            <li><a class="dropdown-item" href="{{ route('funcionarios.index') }}"><i class="bi bi-person-badge me-2"></i>Funcionários</a></li> -->
            <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="bi bi-person-gear me-2"></i>Usuários</a></li>
            <!-- <li><a class="dropdown-item" href="{{ route('empresa.index') }}"><i class="bi bi-building me-2"></i>Empresa</a></li> -->
          </ul>
        </li>

        <!-- Produtos & Compras -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-box-seam me-1"></i>Produtos & Compras
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('produtos.index') }}"><i class="bi bi-box me-2"></i>Estoque</a></li>
            <li><a class="dropdown-item" href="{{ route('pedidos.index') }}"><i class="bi bi-cart-check me-2"></i>Pedido de Compra</a></li>
            <li><a class="dropdown-item" href="{{ route('orcamentos.index') }}"><i class="bi bi-clipboard-data me-2"></i>Orçamento</a></li>
          </ul>
        </li>

        <!-- Vendas -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-cash-stack me-1"></i>Vendas
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#"><i class="bi bi-receipt-cutoff me-2"></i>Vendas</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-list-ul me-2"></i>Itens Venda</a></li>
          </ul>
        </li>

        <!-- Pós-Venda -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-arrow-repeat me-1"></i>Pós-Venda
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('devolucoes.index') }}"><i class="bi bi-arrow-counterclockwise me-2"></i>Devoluções/Trocas</a></li>
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
@php
    $canAccessAdmin = in_array(auth()->user()->nivel_acesso, ['admin', 'gerente']);
@endphp
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle {{ !$canAccessAdmin ? 'disabled' : '' }}" href="#"
     data-bs-toggle="dropdown">
    <i class="bi bi-gear-wide-connected me-1"></i>Administração
  </a>
  <ul class="dropdown-menu">
    <li>
        <a class="dropdown-item {{ !$canAccessAdmin ? 'disabled' : '' }}" 
           href="{{ $canAccessAdmin ? route('users.index') : '#' }}">
           <i class="bi bi-person-gear me-2"></i>Gerenciar Usuários
        </a>
    </li>
    <li>
        <a class="dropdown-item {{ !$canAccessAdmin ? 'disabled' : '' }}" 
           href="{{ $canAccessAdmin ? route('empresa.index') : '#' }}">
           <i class="bi bi-building me-2"></i>Empresa
        </a>
    </li>
	          <li><a class="dropdown-item" href="{{ route('clientes.index') }}"><i class="bi bi-people me-2"></i>Clientes</a></li>
            <li><a class="dropdown-item" href="{{ route('fornecedores.index') }}"><i class="bi bi-truck me-2"></i>Fornecedores</a></li>
            <li><a class="dropdown-item" href="{{ route('funcionarios.index') }}"><i class="bi bi-person-badge me-2"></i>Funcionários</a></li>
    	<li><hr class="dropdown-divider"></li>

    <li class="dropdown-submenu">
        <a class="dropdown-item dropdown-toggle {{ !$canAccessAdmin ? 'disabled' : '' }}" href="#">
            <i class="bi bi-tag me-2"></i>Promoções & Descontos
        </a>
        <ul class="dropdown-menu">
             <li>
                <a class="dropdown-item {{ !$canAccessAdmin ? 'disabled' : '' }}" 
                   href="{{ $canAccessAdmin ? route('painel_promocao.index') : '#' }}">
                   <i class="bi bi-list-stars me-2"></i>Dashboard
                </a>
            </li>
            <li>
                <a class="dropdown-item {{ !$canAccessAdmin ? 'disabled' : '' }}" 
                   href="{{ $canAccessAdmin ? route('promocoes.index') : '#' }}">
                   <i class="bi bi-list-stars me-2"></i>Listar Promoções
                </a>
            </li>
            <li>
                <a class="dropdown-item {{ !$canAccessAdmin ? 'disabled' : '' }}" 
                   href="{{ $canAccessAdmin ? route('promocoes.create') : '#' }}">
                   <i class="bi bi-plus-circle me-2"></i>Nova Promoção
                </a>
            </li>
        </ul>
    </li>
  </ul>
</li>


      </ul>

      <!-- Usuário logado -->
      @auth
      <div class="d-flex align-items-center text-white">
        <span class="me-3">
          <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
        </span>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">
                Sair
            </button>
        </form>
      </div>
      @endauth
    </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Submenu flyout
    var promoSubmenu = document.querySelector('.dropdown-submenu .dropdown-toggle');

    if (promoSubmenu) {
        promoSubmenu.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var submenu = this.nextElementSibling;
            submenu.classList.toggle('show');
        });
    }

    document.addEventListener('click', function() {
        var submenu = document.querySelector('.dropdown-submenu .dropdown-menu');
        if(submenu) submenu.classList.remove('show');
    });
});
</script>

</body>
</html>
