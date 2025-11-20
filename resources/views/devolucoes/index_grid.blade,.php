@extends('layouts.app')

@section('content')
<div class="container">
     @if(session('error'))
    <div class="alert alert-danger mt-3">
        {{ session('error') }}
    </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mt-3">
            @foreach($errors->all() as $err)
                <div>{{ $err }}</div>
            @endforeach
        </div>
    @endif
    <h2>Rastrear Venda</h2>

    <!-- Formulário de filtros -->
    <form action="{{ route('devolucoes.buscar') }}" method="GET" class="mb-4">
        <div class="row g-4">
            <div class="col-md-3">
                <label>Venda</label>
                <select name="venda_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach($vendas as $venda)
                        <option value="{{ $venda->id }}" {{ request('venda_id') == $venda->id ? 'selected' : '' }}>
                            Venda #{{ $venda->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Cliente</label>
                <select name="cliente_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Produto</label>
                <select name="produto_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($produtos as $produto)
                        <option value="{{ $produto->id }}" {{ request('produto_id') == $produto->id ? 'selected' : '' }}>
                            {{ $produto->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>Lote</label>
                <select name="lote_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->id }}" {{ request('lote_id') == $lote->id ? 'selected' : '' }}>
                            Lote #{{ $lote->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Limpar</a>
                <a href="{{ route('devolucoes.pendentes') }}" class="btn btn-warning">Devoluções Pendente</a>
            </div>
        </div>
    </form>

    <!-- Tabela de resultados -->
    @if($itens->isNotEmpty())
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Venda</th>
                    <th>Cliente</th>
                    <th>Produto</th>
                    <th>Lote</th>
                    <th>QTD</th>
                    <th>Qtd Devolvida</th>
                    <th>Disponível</th>
                    <th>Vl.Unit</th>
                    <th>Desconto</th>
                    <th>VL.Total</th>
                    <th>Data da Venda</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itens as $item)
                    @php
                        // Calcula quantidade devolvida dinamicamente
                        $qtdDevolvida = $item->devolucoes
                            ->whereIn('status', ['aprovada','concluida'])
                            ->sum('quantidade');

                        $qtdDisponivel = $item->quantidade - $qtdDevolvida;
                    @endphp
                    <tr @if($qtdDisponivel == 0) class="table-success" @endif>
                        <td>{{ $item->venda->id }}</td>
                        <td>{{ $item->venda->cliente->nome }}</td>
                        <td>{{ $item->produto->nome }}</td>
                        <td>Lote #{{ $item->lote->id ?? '-' }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td><span class="badge bg-info">{{ $qtdDevolvida }}</span></td>
                        <td><span class="badge bg-warning text-dark">{{ $qtdDisponivel }}</span></td>
                        <td>R${{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                        <td>R${{ number_format($item->desconto, 2, ',', '.') }}</td>
                        <td>R${{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        <td>{{ $item->venda->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($qtdDisponivel > 0)
                                <a href="{{ route('devolucoes.registrar', ['item_id' => $item->id]) }}" 
                                   class="btn btn-sm btn-danger">
                                    <i class="bi bi-x-circle"></i> Devolver
                                </a>
                            @else
                              <td>{{ $item->venda->updated_at->format('d/m/Y') }}</td>
                               <span class="text-success">Totalmente devolvido</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="card mt-4 alert alert-warning text-center py-3" style="background-color: #f0d791;">
            <div class="card-body">
                <h5 class="card-title mb-0 text-muted">Nenhum item encontrado</h5>
            </div>
        </div>
    @endif
</div>
@endsection
