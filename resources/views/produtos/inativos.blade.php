@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Produtos Inativos</h1>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary mb-3">Novo Produto</a>
    <a href="{{ route('produtos.inativos') }}" class="btn btn-secondary mb-3">Produtos Inativos</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($produtos->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Fornecedor</th>
                    <th>Marca</th>
                    <th>Unidade</th>
                    <th>Estoque</th>
                    <th>Preço Venda</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produtos as $produto)
                <tr>
                    <td>{{ $produto->nome }}</td>
                    <td>{{ $produto->categoria->nome ?? '' }}</td>
                    <td>{{ $produto->fornecedor->nome ?? '' }}</td>
                    <td>{{ $produto->marca->nome ?? '' }}</td>
                    <td>{{ $produto->unidade->nome ?? '' }}</td>
                    <td>{{ $produto->quantidade_estoque }}</td>
                    <td>R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('produtos.reativar', $produto->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Deseja reativar este produto?')">Ativar</button>
                        </form>


                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $produtos->links() }}
    @else
        <div class="alert alert-info">Nenhum produto ativo encontrado.</div>
    @endif
    <div class="row mb-3">
            <div class="col-md-12 d-flex align-items-center gap-3">
                <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
    </div>
</div>
@endsection
