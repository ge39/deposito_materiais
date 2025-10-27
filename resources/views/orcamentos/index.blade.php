@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Orçamentos do Cliente</h1>

    {{-- Mensagens de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3 text-end">
        <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">Novo Orçamento</a>
    </div>

    <div class="d-flex flex-column gap-3">
        @forelse($orcamentos as $orcamento)
            <div class="card shadow-sm p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <strong>#{{ $orcamento->id }} - {{ $orcamento->cliente->nome }}</strong><br>
                        <small>Orçamento: {{ $orcamento->numero_orcamento }}</small><br>
                        <small>Data: {{ \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y') }}</small><br>
                        <small>Total: R$ {{ number_format($orcamento->total, 2, ',', '.') }}</small><br>
                        <small>Observações: {{ $orcamento->observacoes ?? '-' }}</small>
                    </div>
                    <div class="mt-2 mt-lg-0 d-flex gap-1 flex-wrap">
                        <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Deseja realmente deletar este orçamento?')">Deletar</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-secondary text-center">
                Nenhum orçamento encontrado.
            </div>
        @endforelse
    </div>
</div>
@endsection
