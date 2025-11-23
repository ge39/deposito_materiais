@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-4">
    <h2 class="mb-4">Devoluções</h2>

    <!-- Formulário de busca por código de venda -->
    <form action="{{ route('devolucoes.buscar') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="codigo_venda" class="form-control" placeholder="Digite o código da venda" value="{{ request('codigo_venda') }}">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <!-- Lista de vendas -->
    <div class="card">
        <div class="card-body">
            @if($vendas->isEmpty())
                <p>Nenhuma venda encontrada.</p>
            @else
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID Venda</th>
                            <th>Cliente</th>
                            <th>Data da Venda</th>
                            <th>Valor Total</th>
                            <th>Quantidade Comprada</th>
                            <th>Quantidade Devolvida</th>
                            <th>Quantidade Disponível</th>
                            <th>Valor Extornado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendas as $venda)
                        <tr>
                            <td>{{ $venda->venda_id }}</td>
                            <td>{{ $venda->cliente_nome }}</td>
                            <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                            <td>{{ $venda->quantidade_comprada }}</td>
                            <td>{{ $venda->quantidade_devolvida }}</td>
                            <td>{{ $venda->quantidade_disponivel }}</td>
                            <td>R$ {{ number_format($venda->valor_extornado, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
