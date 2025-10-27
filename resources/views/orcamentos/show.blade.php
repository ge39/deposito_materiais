@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Detalhes do Orçamento #{{ $orcamento->id }}</h2>

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5><strong>Cliente:</strong> {{ $orcamento->cliente->nome }}</h5>
            <p><strong>Data do Orçamento:</strong> {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}</p>
            <p><strong>Validade:</strong> {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</p>
            <p><strong>Status:</strong> 
                @if($orcamento->status == 'Aprovado')
                    <span class="badge bg-success">Aprovado</span>
                @elseif($orcamento->status == 'Cancelado')
                    <span class="badge bg-danger">Cancelado</span>
                @else
                    <span class="badge bg-secondary">Aberto</span>
                @endif
            </p>
            @if($orcamento->observacoes)
                <p><strong>Observações:</strong> {{ $orcamento->observacoes }}</p>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Itens do Orçamento</strong>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orcamento->itens as $item)
                        <tr>
                            <td>{{ $item->produto->nome }}</td>
                            <td>{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th>R$ {{ number_format($orcamento->total, 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>

        <div>
            <a href="{{ route('orcamentos.pdf', $orcamento->id) }}" target="_blank" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Gerar PDF
            </a>

            @if($orcamento->status == 'Aberto')
                <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
