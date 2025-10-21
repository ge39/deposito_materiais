@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Devoluções e Trocas</h2>
        <a href="{{ route('devolucoes.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nova Devolução/Troca
        </a>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if($devolucoes->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($devolucoes as $devolucao)
                <div class="col">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $devolucao->produto->nome ?? '-' }}</h5>
                            <p class="card-text mb-1"><strong>Cliente:</strong> {{ $devolucao->venda->cliente->nome ?? '-' }}</p>
                            <p class="card-text mb-1"><strong>Tipo:</strong> {{ ucfirst($devolucao->tipo) }}</p>
                            <p class="card-text mb-1"><strong>Quantidade:</strong> {{ $devolucao->quantidade }}</p>
                            <p class="card-text mb-1"><strong>Motivo:</strong> {{ $devolucao->motivo }}</p>
                            <p class="card-text mb-1"><strong>Data:</strong> {{ $devolucao->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('devolucoes.show', $devolucao->id) }}" class="btn btn-sm btn-info mb-1">Ver</a>
                            <a href="{{ route('devolucoes.edit', $devolucao->id) }}" class="btn btn-sm btn-warning mb-1">Editar</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $devolucoes->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="alert alert-info text-center py-4 shadow-sm rounded mt-3">
            Nenhuma devolução ou troca registrada.
        </div>
    @endif
</div>
@endsection
