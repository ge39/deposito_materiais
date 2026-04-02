@extends('layouts.app')

@section('content')
<div class="container">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0 fw-semibold">
            Pedido / Orçamento #{{ $orcamento->id }}
        </h4>

        <div class="d-flex gap-2 flex-wrap">

            <a href="{{ route('orcamentos.index') }}" class="btn btn-outline-secondary btn-sm">
                ← Voltar
            </a>

            @if($orcamento->status === 'Aberto')
                <form action="{{ route('orcamentos.aprovar', $orcamento->id) }}" method="POST">
                    @csrf
                    <button class="btn btn-success btn-sm">Aprovar</button>
                </form>

                <form action="{{ route('orcamentos.cancelar', $orcamento->id) }}" method="POST">
                    @csrf
                    <button class="btn btn-danger btn-sm">Cancelar</button>
                </form>
            @endif

            <a href="{{ route('orcamentos.gerarPdf', $orcamento->id) }}" target="_blank" class="btn btn-primary btn-sm">
                PDF
            </a>

        </div>
    </div>

    <!-- RESUMO -->
    <div class="row mb-4 g-3">

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Status</small><br>
                    <span class="badge bg-info text-dark">
                        {{ $orcamento->status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Data</small><br>
                    {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Validade</small><br>
                    {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Total</small><br>
                    <span class="fw-bold text-success">
                        R$ {{ number_format($orcamento->total, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- 👤 CLIENTE -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-light fw-semibold">
            👤 Dados do Cliente
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-4">
                    <small class="text-muted">Nome</small><br>
                    <strong>{{ $orcamento->cliente->nome ?? '-' }}</strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Telefone</small><br>
                    {{ $orcamento->cliente->telefone ?? '-' }}
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Cidade</small><br>
                    {{ $orcamento->cliente->cidade ?? '-' }}
                </div>

                <div class="col-md-12">
                    <small class="text-muted">Endereço</small><br>
                    {{ $orcamento->cliente->endereco ?? '-' }}
                </div>

            </div>
        </div>
    </div>

    <!-- 📦 ITENS ATENDIDOS -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-header bg-success text-white fw-semibold">
            📦 Itens Atendidos
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Produto</th>
                            <th>Qtd</th>
                            <th>Lote</th>
                            <th>Preço</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orcamento->itens->where('quantidade_atendida', '>', 0) as $item)
                            <tr class="text-center">

                                <td class="text-start">
                                    {{ $item->produto->descricao ?? '-' }}
                                </td>

                                <td>
                                    {{ number_format($item->quantidade_atendida, 2, ',', '.') }}
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $item->lote->numero_lote ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}
                                </td>

                                <td class="fw-semibold text-success">
                                    R$ {{ number_format($item->quantidade_atendida * $item->preco_unitario, 2, ',', '.') }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Nenhum item atendido.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ⏳ ITENS PENDENTES -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning fw-semibold">
            ⏳ Itens Pendentes / Aguardando Estoque
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Produto</th>
                            <th>Pendente</th>
                            <th>Previsão</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orcamento->itens->where('quantidade_pendente', '>', 0) as $item)
                            <tr class="text-center">

                                <td class="text-start">
                                    {{ $item->produto->descricao ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        {{ number_format($item->quantidade_pendente, 2, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->previsao_entrega 
                                        ? \Carbon\Carbon::parse($item->previsao_entrega)->format('d/m/Y')
                                        : '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        Aguardando
                                    </span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Nenhum item pendente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection