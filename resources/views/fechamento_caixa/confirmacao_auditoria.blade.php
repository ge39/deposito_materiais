@extends('layouts.app')

@section('content')

<div class="container my-5">

    {{-- Cabeçalho --}}
    <div class="text-center mb-4">
        <h3 class="fw-bold text-success">Correção de Divergências do Caixa #{{ $caixa->id }}</h3>
        <p class="text-muted fs-4">Os valores divergentes do caixa <strong>#{{ $caixa->id }}</strong> foram corrigidos pela auditoria.</p>
    </div>

    {{-- Card principal --}}
    <div class="card shadow-sm border-success">
        <div class="card-header bg-success text-light fw-bold">
            Detalhes do Caixa
        </div>

        <div class="card-body fs-5">

            {{-- Mensagem de sucesso dentro do card --}}
            @if(session('auditoria_sucesso'))
                <div class="alert alert-success d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                    <div>
                        {{ session('auditoria_sucesso') }}
                    </div>
                </div>
            @endif

            {{-- Informações do caixa --}}
            <div class="row mb-4 border-bottom border-secondary p-1">
                <div class="col-md-3">
                    <strong>ID (Caixa)</strong><br>
                    {{ $caixa->id }}
                </div>
                <div class="col-md-3">
                    <strong>Operador</strong><br>
                    {{ $caixa->usuario->name ?? 'Não identificado' }}
                </div>
                <div class="col-md-3">
                    <strong>Terminal</strong><br>
                    {{ $caixa->terminal_id }}
                </div>
                <div class="col-md-3">
                    <strong>Data de Fechamento</strong><br>
                    {{ $caixa->data_fechamento?->format('d/m/Y H:i') ?? '-' }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <strong>Status</strong><br>
                    <span class="badge bg-success">{{ ucfirst($caixa->status) }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Fundo de Troco</strong><br>
                    R$ {{ number_format($caixa->fundo_troco, 2, ',', '.') }}
                </div>
                <div class="col-md-3">
                    <strong>Fechamento</strong><br>
                    R$ {{ number_format($caixa->valor_fechamento, 2, ',', '.') }}
                </div>
                
            </div>

            {{-- Botões --}}
            <div class="mt-4 text-end">
                <a href="{{ route('caixa.abrir') }}" class="btn btn-primary me-2">
                    Abrir Novo Caixa
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    Sair
                </a>
            </div>

        </div>
    </div>

</div>

@endsection
