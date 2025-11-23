@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Rastrear Devoluções Venda</h2>

    <!-- Formulário de busca unificado -->
    <form action="{{ route('devolucoes.buscar') }}" method="GET" class="mb-4">
        <div class="row g-4">
            <div class="col-md-12">
                <label>Pesquisar Venda ou Cliente</label>
                <input type="text" name="search" class="form-control" placeholder="Digite ID da venda ou nome do cliente" value="{{ request('search') }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Limpar</a>
                <a href="{{ route('devolucoes.pendentes') }}" class="btn btn-warning">Devoluções Pendentes</a>
            </div>
        </div>
    </form>

    <!-- Cards de resultados -->
    @if($vendas->isNotEmpty())
        
        <div class="row row-cols-1 row-cols-md-2 g-3">
           
            @foreach($vendas as $item)
                @php
                    // Já temos tudo calculado na query
                    $qtdDisponivel = $item->quantidade_disponivel;
                @endphp
                <div class="col">
                    <div class="card h-100 shadow-sm 
                        @if($qtdDisponivel == 0) border-success @endif">
                        <div class="card-body">
                            <h5 class="card-title">Venda #{{ $item->venda_id }}</h5>
                            <p class="card-text mb-1"><strong>Data da Venda:</strong> {{ \Carbon\Carbon::parse($item->data_venda)->format('d/m/Y') }}</p>
                            <p class="card-text mb-1"><strong>Cliente:</strong> {{ $item->cliente_nome }}</p>
                            <p class="card-text mb-1">
                                <strong>Qtde Comprada:</strong> {{ $item->quantidade_comprada }} |
                                <strong>Devolvida:</strong> {{ $item->quantidade_devolvida }} |
                                <strong>Disponível:</strong> {{ $qtdDisponivel }}
                            </p>
                            <p class="card-text mb-1"><strong>Valor Total:</strong> R${{ number_format($item->valor_total,2,',','.') }}</p>
                            <p class="card-text mb-1"><strong>Valor Extornado:</strong> R${{ number_format($item->valor_extornado,2,',','.') }}</p>

                            <div class="mt-2 d-flex gap-2 align-items-start">
                                @if($qtdDisponivel > 0)
                                    <a href="{{ route('devolucoes.registrar', ['item_id' => $item->venda_id]) }}" 
                                       class="btn btn-sm mt-3 btn-danger">
                                        <i class="bi bi-x-circle"></i> Devolver
                                    </a>
                                @else
                                    <p class="text-success fw-bold mb-0 mt-2">Totalmente devolvido</p>
                                @endif

                                <a href="{{ route('devolucoes.index') }}" class="btn btn-sm mt-3 btn-secondary">Voltar</a>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <!-- Links de paginação -->
        <div class="mt-4 d-flex justify-content-center">
          {{ $vendas->links('pagination::bootstrap-5') }}
        </div
    @else
        <div class="alert alert-warning text-center py-3" style="background-color: #f0d791;">
            Nenhuma venda encontrada
        </div>
    @endif
</div>
@endsection
