@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Produtos Ativos</h1>
    <a href="{{ route('produtos.create') }}" class="btn btn-primary mb-3">Novo Produto</a>
    <a href="{{ route('produtos.inativos') }}" class="btn btn-secondary mb-3">Produtos Inativos</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($produtos->count())
        <table class="table table-bordered align-middle" style="table-layout: auto; width: 100%;">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Fornecedor</th>
                    <th>Marca</th>
                    <th>Unidade</th>
                    <th>Estoque</th>
                    <th>Mínimo</th>
                    <th>DT_Compra</th>
                    <th>Validade</th>
                    <th>Pr. Venda</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produtos as $produto)
                <tr>
                    <td>{{ $produto->nome }}</td>
                    <td class="w-10">{{ $produto->categoria->nome ?? '-' }}</td>
                    <td>{{ $produto->fornecedor->nome ?? '-' }}</td>
                    <td>{{ $produto->marca->nome ?? '-' }}</td>
                    <td> {{ $produto->unidadeMedida->nome ?? '' }}</td>
                    <td>{{ $produto->estoque_total }}</td>
                    <td>{{ $produto->estoque_minimo }}</td>
                    <td>{{ \Carbon\Carbon::parse($produto->data_compra)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($produto->validade)->format('d/m/Y') }}</td>
                    <td class="w-25">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>

                    <td style="white-space: normal; min-width: 220px;">
                        <div class="d-flex gap-1 flex-wrap justify-content-start">
                            <a href="{{ route('produtos.show', $produto->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <a href="{{ route('lotes.index', $produto->id) }}" class="btn btn-sm btn-primary">Lotes</a>
                            <form action="{{ route('produtos.desativar', $produto->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deseja desativar este produto?')">Desativar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $produtos->links() }}
    @else
        <div class="alert alert-info">Nenhum produto ativo encontrado.</div>
    @endif
</div>
@endsection
