@extends('layouts.app')

@section('content')
<div class="container py-5 mt-0 pt-0">
    
    <div class="card shadow-lg border-0">
        
        <div class="card-header 
            @if($bloquearPDV) bg-danger text-white 
            @else bg-warning text-dark 
            @endif">
            <h4 class="fw-bold mb-0">
                @if($bloquearPDV)
                    🚫 BLOQUEIO DE CAIXA - {{$codigo_operacao}}
                @else
                    ⚠️ LIMITE DE SANGRIA ATINGIDO - {{$codigo_operacao}}
                @endif
            </h4>
        </div>

        <div class="card-body text-center">

            <h5 class="mb-3">
                Saldo Atual:
                <span class="fw-bold" id="saldoAtualTexto">
                    R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                </span>
            </h5>

            <p class="mb-2">
                Limite configurado:
                <span class="fw-bold">
                    R$ {{ number_format($limiteSangria, 2, ',', '.') }}
                </span>
            </p>

            @if($bloquearPDV)
                <div class="alert alert-danger fw-bold fs-2 shadow-sm">
                    PDV BLOQUEADO<br>
                    Realize sangria para continuar as vendas.
                </div>
            @else
                <div class="alert alert-warning fw-bold fs-2 shadow-sm">
                    Recomendado realizar sangria.
                </div>
            @endif

            <hr>

            <h5 class="text-primary fw-bold">💰 Valor sugerido para sangria:</h5>
            <h2 class="display-6 fw-bold text-success mb-4" id="valorSugeridoTexto">
                R$ {{ number_format($saldoAtual, 2, ',', '.') }}
            </h2>

            <p class="text-muted mb-4">
                Oriente a operadora a retirar este valor do caixa.
            </p>

            <form id="formSangria"
                  action="{{ route('caixa.sangria.registrar', $caixa->id) }}"
                  method="POST"
                  class="w-50 mx-auto">
                @csrf

                <div class="mb-3 text-start">
                    <label for="valor" class="form-label fw-bold">Valor da Sangria</label>
                    <input type="number"
                           name="valor"
                           id="valorInput"
                           class="form-control @error('valor') is-invalid @enderror"
                           step="0.01"
                           min="0"
                           value="{{ old('valor', $valorSugeridoSangria ?? $saldoAtual) }}"
                           {{ $saldoAtual <= 0 ? 'disabled' : '' }}
                           required>
                    @error('valor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 text-start">
                    <label for="motivo" class="form-label fw-bold">Motivo</label>
                    <select name="motivo"
                            id="motivo"
                            class="form-select @error('motivo') is-invalid @enderror"
                            {{ $saldoAtual <= 0 ? 'disabled' : '' }}
                            required>
                        <option value="">Selecione</option>
                        <option value="manual" {{ old('motivo') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="limite_excedido" {{ old('motivo') == 'limite_excedido' ? 'selected' : '' }}>Limite Excedido</option>
                        <option value="encerramento" {{ old('motivo') == 'encerramento' ? 'selected' : '' }}>Encerramento</option>
                    </select>
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="submit"
                            id="btnSangria"
                            class="btn btn-success fw-bold px-4"
                            {{ $saldoAtual <= 0 ? 'disabled' : '' }}>
                        ✅ Efetuar Sangria
                    </button>

                    <a href="{{ route('pdv.index') }}" class="btn btn-secondary fw-bold px-4">
                        🔙 Voltar
                    </a>
                    
                   
                   {{-- Botão imprimir sempre visível --}}
                    @if($ultimaSangria)
                        <a href="{{ route('sangria.imprimir', $ultimaSangria) }}"
                        id="btnImprimirSangria"
                        target="_self"
                        class="btn btn-primary fw-bold px-4 mt-2">
                            🖨 Imprimir
                        </a>
                    @else
                        <a href="#"
                        id="btnImprimirSangria"
                        class="btn btn-primary fw-bold px-4 mt-2 disabled">
                            🖨 Imprimir
                        </a>
                    @endif
                </div>
            </form>

        </div>
    </div>

</div>

@endsection