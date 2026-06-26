@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <h4 class="mb-3">Detalhes da Divergência de Estoque</h4>

    <div class="card">
        <div class="card-body">

            <p><strong>ID:</strong> {{ $divergencia->id }}</p>
            <p><strong>Produto:</strong> {{ $divergencia->produto->nome ?? '-' }}</p>
            <p><strong>Venda:</strong> {{ $divergencia->venda_id ?? '-' }}</p>
            <p><strong>Caixa:</strong> {{ $divergencia->caixa_id ?? '-' }}</p>

            <hr>

            <p><strong>Quantidade Solicitada:</strong> {{ number_format($divergencia->quantidade_solicitada, 3, ',', '.') }}</p>
            <p><strong>Quantidade Atendida:</strong> {{ number_format($divergencia->quantidade_atendida, 3, ',', '.') }}</p>
            <p><strong>Diferença:</strong> {{ number_format($divergencia->diferenca, 3, ',', '.') }}</p>

            <hr>

            <p><strong>Tipo:</strong> {{ ucfirst($divergencia->tipo) }}</p>
            <p><strong>Observação:</strong> {{ $divergencia->observacao ?? '-' }}</p>
            <p><strong>Usuário:</strong> {{ $divergencia->usuario->name ?? '-' }}</p>
            <p><strong>Data:</strong> {{ optional($divergencia->created_at)->format('d/m/Y H:i') }}</p>

            <a href="{{ route('estoque-divergencias.index') }}" class="btn btn-secondary">
                Voltar
            </a>

        </div>
    </div>

</div>
@endsection