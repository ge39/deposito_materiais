@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row g-3">
        <!-- Clientes -->
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Clientes</h5>
                    <p class="card-text">Gerencie seus clientes</p>
                    <a href="{{ route('clientes.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Funcionários -->
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Funcionários</h5>
                    <p class="card-text">Gerencie os funcionários</p>
                    <a href="{{ route('funcionarios.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Categorias -->
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Categorias</h5>
                    <p class="card-text">Gerencie categorias de produtos</p>
                    <a href="{{ route('categorias.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Fornecedores -->
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Fornecedores</h5>
                    <p class="card-text">Gerencie fornecedores</p>
                    <a href="{{ route('fornecedores.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Produtos -->
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Produtos</h5>
                    <p class="card-text">Gerencie seus produtos</p>
                    <a href="{{ route('produtos.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Vendas -->
        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">Vendas</h5>
                    <p class="card-text">Gerencie vendas realizadas</p>
                    <a href="{{ route('vendas.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Pedidos de Compras -->
        <div class="col-md-3">
            <div class="card text-white bg-dark">
                <div class="card-body">
                    <h5 class="card-title">Pedidos de Compras</h5>
                    <p class="card-text">Gerencie pedidos aos fornecedores</p>
                    <a href="{{ route('pedidos_compras.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>

        <!-- Pós-Venda -->
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Pós-Venda</h5>
                    <p class="card-text">Gerencie devoluções, trocas e atendimentos</p>
                    <a href="{{ route('pos_vendas.index') }}" class="btn btn-light btn-sm">Acessar</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
