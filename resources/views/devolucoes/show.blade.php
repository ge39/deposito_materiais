@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes da Devolução #{{ $devolucao->id }}</h2>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            Informações da Devolução
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4"><strong>Cliente:</strong> {{ $devolucao->cliente ? $devolucao->cliente->nome : 'Não informado' }}</div>
                <div class="col-md-4"><strong>CPF:</strong> {{ $devolucao->cliente ? $devolucao->cliente->cpf : '-' }}</div>
                <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($devolucao->status) }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4"><strong>Produto:</strong> {{ $devolucao->item ? $devolucao->item->produto->nome : '-' }}</div>
                <div class="col-md-4"><strong>Código Produto:</strong> {{ $devolucao->item && $devolucao->item->produto ? $devolucao->item->produto->codigo : '-' }}</div>
                <div class="col-md-4"><strong>Quantidade:</strong> {{ $devolucao->item ? $devolucao->item->quantidade : '-' }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4"><strong>Venda #:</strong> {{ $devolucao->venda_id ?? '-' }}</div>
                <div class="col-md-4"><strong>Lote:</strong> {{ $devolucao->item && $devolucao->item->lote ? $devolucao->item->lote->codigo : '-' }}</div>
                <div class="col-md-4"><strong>Data da Devolução:</strong> {{ $devolucao->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-12"><strong>Motivo:</strong> {{ $devolucao->motivo }}</div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Voltar</a>
        <a href="{{ route('devolucoes.edit', $devolucao) }}" class="btn btn-primary">Editar</a>
    </div>
</div>
@endsection
