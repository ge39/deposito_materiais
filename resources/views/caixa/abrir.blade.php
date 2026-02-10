@extends('layouts.app')

@section('content')

<style>
    /* Card principal do caixa */
    .caixa-card {
        font-size: 16px;
        border-radius: 14px;
        background: linear-gradient(135deg, #f0f8ff, #ffffff);
        border: 1px solid #d1d5db;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        padding: 50px 35px;
        max-width: 550px;
        width: 100%;
        text-align: center;
        transition: all 0.3s ease-in-out;
    }

    /* Cabeçalho do form */
    .caixa-header h5 {
        font-size: 2rem;
        font-weight: 800;
        color: #0d6efd; /* azul suave */
        margin-bottom: 6px;
    }

    .caixa-header small {
        font-size: 1rem;
        color: #6c757d;
    }

    /* Seções internas */
    .caixa-section-title {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #198754; /* verde discreto */
        margin-bottom: 14px;
    }

    .caixa-label {
        font-size: 1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 6px;
        display: block;
    }

    /* Inputs com destaque e sombra leve */
    .caixa-card input,
    .caixa-card textarea {
        font-size: 1.1rem;
        text-align: center;
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 12px 14px;
        background-color: #f9fafb;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        transition: all 0.3s ease-in-out;
    }

    /* Destaque do input ao foco */
    .caixa-card input:focus,
    .caixa-card textarea:focus {
        border-color: #0d6efd;
        background-color: #e7f1ff;
        box-shadow: 0 0 12px rgba(13,110,253,0.2);
        outline: none;
        transform: scale(1.02);
    }

    /* Destaque especial para Fundo de Troco */
    .fundo-troco-input:focus {
        border-color: #198754 !important;
        background-color: #e6f4ea;
        box-shadow: 0 0 14px rgba(25,135,84,0.25);
        transform: scale(1.03);
    }

    /* Botão principal */
    .btn-abrir {
        font-size: 1.25rem;
        font-weight: 700;
        padding: 14px 0;
        border-radius: 10px;
        background: linear-gradient(to right, #0d6efd, #0ea5e9);
        color: white;
        transition: all 0.2s ease-in-out;
    }

    .btn-abrir:hover {
        background: linear-gradient(to right, #0b5ed7, #0d6efd);
        transform: scale(1.02);
    }

    /* Rodapé */
    .caixa-footer {
        font-size: 0.95rem;
        color: #6c757d;
        margin-top: 28px;
        padding-top: 16px;
        border-top: 1px solid #e0e0e0;
    }
</style>

<div class="container d-flex align-items-center justify-content-center bg-light" style="min-height: 100vh;">
    <div class="caixa-card">

        {{-- Cabeçalho --}}
        <div class="caixa-header mb-5">
            <h5>Abertura de Caixa</h5>
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
            <input type="hidden" name="terminal_id" value="{{ $terminal->id }}">
            <input type="hidden" name="terminal" value="{{ $terminal->nome ?? null }}">
            <input type="hidden" name="valor_fundo_anterior"
                   value="{{ $ultimoCaixa->valor_fechamento ?? 0 }}">

            {{-- Botão --}}
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-abrir">
                    Abrir Caixa
                </button>
            </div>

        </form>

        {{-- Rodapé --}}
        <div class="caixa-footer">
            Abertura registrada em {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>
</div>

@endsection
