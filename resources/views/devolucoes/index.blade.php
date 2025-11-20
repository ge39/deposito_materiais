@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Rastrear Devoluções Venda</h2>

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
                <a href="{{ route('devolucoes.pendentes') }}" class="btn btn-warning">Devoluções Pendentes</a>
            </div>
        </div>
    </form>

    <!-- Cards de resultados -->
    @if($itens->isNotEmpty())
        <div class="row row-cols-1 row-cols-md-2 g-3">
            @foreach($itens as $item)
                @php
                    $qtdDevolvida = $item->devolucoes
                        ->whereIn('status', ['aprovada','concluida'])
                        ->sum('quantidade');
                    $qtdDisponivel = $item->quantidade - $qtdDevolvida;
                @endphp
                <div class="col">
                    <div class="card h-100 shadow-sm 
                        @if($qtdDisponivel == 0) border-success @endif">
                        <div class="card-body">
                            <h5 class="card-title">
                                Venda #{{ $item->venda->id }} - {{ $item->produto->nome }}
                            </h5>
                            <p class="card-text mb-1"><strong>Data da Venda:</strong> {{ $item->venda->created_at->format('d/m/Y') }}</p>
                            <p class="card-text mb-1"><strong>Cliente:</strong> {{ $item->venda->cliente->nome }}</p>
                            <p class="card-text mb-1"><strong>Lote Rastreio:</strong> {{ $item->lote->id ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Valor Compra:</strong> R${{ number_format($item->subtotal,2,',','.') }}</p>
                            <p class="card-text mb-1">
                                <strong>Qtde Comprada:</strong> {{ $item->quantidade }} |
                                <strong>Devolvida:</strong> {{ $qtdDevolvida }} |
                                <strong>Disponível:</strong> {{ $qtdDisponivel }}
                            </p>                            
                            <p class="card-text mb-1"><strong>Valor Unidade:</strong> R${{ number_format($item->preco_unitario,2,',','.') }}</p>
                                @php
                                    $valorExtornado = $qtdDevolvida * $item->preco_unitario;
                                @endphp
                            <p class="card-text mb-1">
                                <strong>Valor Extornado:</strong>
                                R${{ number_format($valorExtornado, 2, ',', '.') }}
                            </p>
                            <p class="card-text mb-1"><strong>Subtotal:</strong> R${{ number_format($item->subtotal - $valorExtornado,2,',','.') }}</p>
                            <div class="mt-2 d-flex gap-2 align-items-start">
                                @if($qtdDisponivel > 0)
                                    <a href="{{ route('devolucoes.registrar', ['item_id' => $item->id]) }}" 
                                    class="btn btn-sm mt-3 btn-danger">
                                        <i class="bi bi-x-circle"></i> Devolver
                                    </a>
                                @else
                                    <div class="d-flex flex-column">
                                        <p class="card-text mb-1">
                                            Última Devolução:
                                            @if ($item->devolucoes->count() > 0)
                                                {{ $item->devolucoes->last()->updated_at->format('d/m/Y') }}
                                            @else
                                                — 
                                            @endif
                                        </p>
                                        
                                        @if ($item->devolucoes->count() > 0)
                                            <p class="text-success fw-bold mb-0 mt-2 text-start">
                                                Totalmente devolvido
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                <a href="{{ route('devolucoes.index') }}" class="btn btn-sm mt-3 btn-secondary">Voltar</a>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-warning text-center py-3" style="background-color: #f0d791;">
            Nenhum item encontrado
        </div>
    @endif
</div>
@endsection
