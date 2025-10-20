<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistema de Depósito de Materiais</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="{{ asset('js/form-masks.js') }}"></script>
<script src="{{ asset('js/cep.js') }}"></script>

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
<div class="container-fluid">
<a class="navbar-brand" href="{{ url('/') }}">🏗️ Depósito</a>
<div class="collapse navbar-collapse">
<ul class="navbar-nav">
<li class="nav-item"><a class="nav-link" href="{{ route('clientes.index') }}">Clientes</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('fornecedores.index') }}">Fornecedores</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('funcionarios.index') }}">Funcionários</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Usuários</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('produtos.index') }}">Produtos</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('vendas.index') }}">Vendas</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('itens_venda.index') }}">Itens Venda</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('frotas.index') }}">Frotas</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('entregas.index') }}">Entregas</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('pos_venda.index') }}">Pós-Venda</a></li>
</ul>
</div>
</div>
</nav>
<div class="container">
@yield('content')
</
