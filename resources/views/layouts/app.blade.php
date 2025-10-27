<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu Fixo Horizontal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('dashboard') }}">🏗️ Depósito</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
            aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <!-- Cadastro -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Cadastro</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('clientes.index') }}">Clientes</a></li>
            <li><a class="dropdown-item" href="{{ route('fornecedores.index') }}">Fornecedores</a></li>
            <li><a class="dropdown-item" href="{{ route('funcionarios.index') }}">Funcionários</a></li>
            <li><a class="dropdown-item" href="{{ route('users.index') }}">Usuários</a></li>
            <li><a class="dropdown-item" href="{{ route('empresa.index') }}">Empresa</a></li>
          </ul>
        </li>

        <!-- Produtos & Compras -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Produtos & Compras</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('produtos.index') }}">Estoque</a></li>
            <li><a class="dropdown-item" href="{{ route('pedidos.index') }}">Pedido de Compra</a></li>
            <li><a class="dropdown-item" href="{{ route('orcamentos.index') }}">Orçamento</a></li>
          </ul>
        </li>

        <!-- Vendas -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Vendas</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Vendas</a></li>
            <li><a class="dropdown-item" href="#">Itens Venda</a></li>
          </ul>
        </li>

        <!-- Pós-Venda -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Pós-Venda</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('devolucoes.index') }}">Devoluções/Trocas</a></li>
          </ul>
        </li>

        <!-- Logística -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Logística</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Frotas</a></li>
            <li><a class="dropdown-item" href="#">Entregas</a></li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>


<!-- Seus scripts JS no final do body -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
