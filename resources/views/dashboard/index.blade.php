@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard</h1>
    <p>Bem-vindo ao sistema! Use o menu para navegar entre clientes, fornecedores, produtos, vendas, pós-venda, etc.</p>

    <div class="row mt-4">
        <div class="col-md-3">
            <a href="{{ route('clientes.index') }}" class="btn btn-primary w-100">Clientes</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('fornecedores.index') }}" class="btn btn-primary w-100">Fornecedores</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('produtos.index') }}" class="btn btn-primary w-100">Produtos</a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('vendas.index') }}" class="btn btn-primary w-100">Vendas</a>
        </div>
    </div>
</div>
@endsection
