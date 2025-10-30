@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h2>Orçamento #{{ $orcamento->id ?? 'N/A' }}</h2>
        <div class="d-flex gap-3 flex-wrap">
            <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>
            @if($orcamento->status === 'Aberto')
                <form action="{{ route('orcamentos.aprovar', $orcamento->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-success">Aprovar</button>
                </form>
                <form action="{{ route('orcamentos.cancelar', $orcamento->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-danger">Cancelar</button>
                </form>
            @endif
            <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" class="btn btn-primary" target="_blank">Gerar PDF</a>
        </div>
    </div>

    <div class="card mb-4 p-3">
        <h4>Informações do Orçamento</h4>
        <div class="d-flex flex-wrap gap-3">
            <div><strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? '-' }}</div>
            <div><strong>Fornecedor:</strong> {{ $orcamento->fornecedor->nome ?? '-' }}</div>
            <div><strong>Data:</strong> {{ $orcamento->data_orcamento ?? '-' }}</div>
            <div><strong>Validade:</strong> {{ $orcamento->validade ?? '-' }}</div>
            <div><strong>Status:</strong> {{ $orcamento->status ?? '-' }}</div>
            <div><strong>Total:</strong> R$ {{ number_format($orcamento->total ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="mt-2"><strong>Observações:</strong> {{ $orcamento->observacoes ?? '-' }}</div>
    </div>

    <div class="card p-3">
        <h4>Itens do Orçamento</h4>
        @if($orcamento->itens->isEmpty())
            <div>Nenhum item adicionado.</div>
        @else
            <div class="d-flex flex-column gap-2">
                @foreach($orcamento->itens as $item)
                    <div class="d-flex justify-content-between p-2 border rounded">
                        <div>
                            <strong>Produto:</strong> {{ $item->produto->descricao ?? '-' }} <br>
                            <strong>Fornecedor:</strong> {{ $item->produto->fornecedor->nome ?? '-' }}
                        </div>
                        <div>
                            <strong>Quantidade:</strong> {{ $item->quantidade }} <br>
                            <strong>Preço Unitário:</strong> R$ {{ number_format($item->preco_unitario, 2, ',', '.') }} <br>
                            <strong>Subtotal:</strong> R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
