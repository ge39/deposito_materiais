@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Devoluções / Trocas</h2>

    {{-- Mensagens de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Formulário de Filtro --}}
    <form method="GET" action="{{ route('devolucoes.index') }}" class="mb-4">
        <div class="row g-3">

            {{-- Cliente --}}
            <div class="col-md-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select name="cliente_id" id="cliente_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Venda --}}
            <div class="col-md-2">
                <label for="venda_id" class="form-label">Venda #</label>
                <input type="number" name="venda_id" id="venda_id" class="form-control" value="{{ request('venda_id') }}">
            </div>

            {{-- Lote --}}
            <div class="col-md-2">
                <label for="lote_id" class="form-label">Lote</label>
                <input type="text" name="lote_id" id="lote_id" class="form-control" value="{{ request('lote_id') }}">
            </div>

            {{-- Código Produto --}}
            <div class="col-md-2">
                <label for="produto_codigo" class="form-label">Código Produto</label>
                <input type="text" name="produto_codigo" id="produto_codigo" class="form-control" value="{{ request('produto_codigo') }}">
            </div>

            {{-- Data --}}
            <div class="col-md-3">
                <label for="data" class="form-label">Data</label>
                <input type="date" name="data" id="data" class="form-control" value="{{ request('data') }}">
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Limpar</a>
        </div>
    </form>

    {{-- Tabela de Devoluções --}}
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Produto</th>
                    <th>Venda</th>
                    <th>Lote</th>
                    <th>Motivo</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devolucoes as $devolucao)
                    <tr>
                        <td>{{ $devolucao->id }}</td>
                        <td>{{ $devolucao->cliente ? $devolucao->cliente->nome : 'Não informado' }}</td>
                        <td>{{ $devolucao->item ? $devolucao->item->produto->nome : '-' }}</td>
                        <td>{{ $devolucao->venda_id ?? '-' }}</td>
                        <td>{{ $devolucao->item && $devolucao->item->lote ? $devolucao->item->lote->codigo : '-' }}</td>
                        <td>{{ $devolucao->motivo }}</td>
                        <td>{{ ucfirst($devolucao->status) }}</td>
                        <td>{{ $devolucao->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('devolucoes.show', $devolucao) }}" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Nenhuma devolução encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    <div class="mt-3">
        {{ $devolucoes->appends(request()->query())->links() }}
    </div>
</div>
@endsection
