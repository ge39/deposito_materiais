@extends('layouts.app')

@section('content')

<div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm" style="max-width: 520px; width: 100%;">

        <div class="card-header text-center">
            <h4 class="mb-0 fw-bold">Abertura de Caixa</h4>
        </div>

        <div class="card-body">

            {{-- Informações do terminal / usuário (somente leitura) --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Terminal</label>
                <input type="text"
                       class="form-control"
                       value="{{ $terminal->nome ?? $terminal->uuid ?? 'Terminal não identificado' }}"
                       readOnly>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Operador</label>
                <input type="text"
                       class="form-control"
                       value="{{ auth()->user()->name }}"
                       disabled>
            </div>

            <hr>

            <form method="POST" action="{{ route('caixa.store') }}">
                @csrf

                {{-- Fundo anterior --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Valor do fundo anterior</label>
                    <input type="text"
                           class="form-control"
                           value="{{ number_format($ultimoCaixa->valor_fechamento ?? 0, 2, ',', '.') }}"
                           disabled>
                </div>

                {{-- Fundo de troco --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Fundo de troco (R$)</label>
                    <input type="number"
                           name="fundo_troco"
                           step="0.01"
                           min="0"
                           class="form-control"
                           required>
                </div>

                {{-- Observação --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Observação (opcional)</label>
                    <textarea name="observacao"
                              class="form-control"
                              rows="3"></textarea>
                </div>

                {{-- Campos ocultos controlados pelo backend --}}
                <input type="hidden" name="terminal_id" value="{{ $terminal->id }}">
                <input type="hidden" name="terminal" value="{{ $terminal->nome ?? null }}">
                <input type="hidden" name="valor_fundo_anterior"
                       value="{{ $ultimoCaixa->valor_fechamento ?? 0 }}">

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success btn-lg fw-bold">
                        ABRIR CAIXA
                    </button>
                </div>
            </form>

        </div>

        <div class="card-footer text-center text-muted small">
            Data/Hora da abertura: {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>
</div>


<!-- 
<div class="container d-flex align-items-center justify-content-center bg-light" style="min-height: 100vh;">
    <div class="caixa-card">

        {{-- Cabeçalho --}}
        <div class="caixa-header mb-5">
            <h5>Abertura de Caixa2</h5>
            <small>Registro inicial do caixa operacional</small>
        </div>

        <form method="POST" action="{{ route('caixa.store') }}">
            @csrf

            {{-- IDENTIFICAÇÃO --}}
            <div class="mb-5">
                <div class="caixa-section-title">Identificação</div>

                <div class="mb-4">
                    <label class="caixa-label">Terminal</label>
                    <input type="text"
                           class="form-control bg-light"
                           value="{{ $terminal->nome ?? $terminal->uuid ?? 'Terminal não identificado' }}"
                           readonly>
                </div>

                <div class="mb-4">
                    <label class="caixa-label">Operador</label>
                    <input type="text"
                           class="form-control bg-light"
                           value="{{ auth()->user()->name }}"
                           readonly>
                </div>
            </div>

            {{-- FINANCEIRO --}}
            <div class="mb-5">
                <div class="caixa-section-title text-primary">Financeiro</div>

                <!-- <div class="mb-4">
                    <label class="caixa-label">Fundo anterior</label>
                    <input type="text"
                           class="form-control bg-light"
                           value="R$ {{ number_format($ultimoCaixa->valor_fechamento ?? 0, 2, ',', '.') }}"
                           readonly>
                </div> -->

                <div class="mb-4">
                    <label class="caixa-label">Fundo de troco inicial (R$)</label>
                    <input type="number"
                           name="fundo_troco"
                           step="0.01"
                           min="0"
                           class="form-control fundo-troco-input"
                           placeholder="0,00"
                           required
                           autofocus>
                </div>
            </div>

            {{-- OBSERVAÇÃO --}}
            <div class="mb-5">
                <div class="caixa-section-title text-secondary">Observação</div>
                <textarea name="observacao"
                          class="form-control"
                          rows="3"
                          placeholder="Observação opcional"></textarea>
            </div>

            {{-- Campos ocultos --}}
            <input type="hidden" name="empresa_id" value="{{ auth()->user()->empresa_id }}"> {{-- 👈 ADICIONE ESTA LINHA --}}
           @extends('layouts.app')
</div>
 -->

@endsection
