@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalhes da Devolução/Troca</h2>

    <div class="card p-3 mb-3">
        <p><strong>ID:</strong> {{ $devolucao->id }}</p>
        <p><strong>Venda:</strong> {{ $devolucao->venda->id }}</p>
        <p><strong>Produto:</strong> {{ $devolucao->produto->descricao }}</p>
        <p><strong>Tipo:</strong> {{ ucfirst($devolucao->tipo) }}</p>
        <p><strong>Produto Troca:</strong> {{ $devolucao->produtoTroca->descricao ?? '-' }}</p>
        <p><strong>Quantidade:</strong> {{ $devolucao->quantidade }}</p>
        <p><strong>Diferença (R$):</strong> {{ number_format($devolucao->diferenca,2,',','.') }}</p>
        <p><strong>Observações:</strong> {{ $devolucao->observacoes ?: '-' }}</p>
        <p><strong>Registrado em:</strong> {{ $devolucao->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
