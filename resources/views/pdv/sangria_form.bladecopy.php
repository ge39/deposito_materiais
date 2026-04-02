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
                    🚫 BLOQUEIO DE CAIXA - {{$caixa->id}}
                @else
                    ⚠️ LIMITE DE SANGRIA ATINGIDO - {{$configSangria->valor_limite ?? 0}}
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
                    R$ {{ number_format($limite_sangria, 2, ',', '.') }}
                </span>
            </p>

            @if($bloquearPDV)
                <div class="alert alert-danger fw-bold fs-2 shadow-sm">
                    PDV BLOQUEADO<br>
                    Realize a Sangria para continuar as vendas.
                </div>
            @else
                <div class="alert alert-warning fw-bold fs-2 shadow-sm">
                    Recomendado realizar Sangria.
                </div>
            @endif

            <hr>

            <h5 class="text-primary fw-bold">💰 Valor sugerido para Sangria:</h5>
            <h2 class="display-6 fw-bold text-success mb-4" id="valorSugeridoTexto">
                R$ {{ number_format($saldoAtual, 2, ',', '.') }}
            </h2>

            <p class="text-muted mb-4">
                Oriente a operadora a retirar este valor do caixa.
            </p>

            {{-- 🔔 MENSAGEM AJAX --}}
            <div id="mensagemAjax"></div>

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
                        <option value="manual">Manual</option>
                        <option value="limite_excedido">Limite Excedido</option>
                        <option value="encerramento">Encerramento</option>
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

                    @if($ultimaSangria)
                        <a href="{{ route('sangria.imprimir', $ultimaSangria) }}"
                           id="btnImprimirSangria"
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

{{-- ===============================
     SCRIPT JS MELHORADO
================================ --}}
<script>
function mostrarMensagem(tipo, mensagem) {
    const container = document.getElementById('mensagemAjax');

    container.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show shadow-sm mt-3" role="alert">
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

document.getElementById('formSangria')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('btnSangria');

    btn.disabled = true;
    btn.innerText = "Processando...";

    try {
        const response = await fetch(form.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Accept": "application/json"
            },
            body: new FormData(form)
        });

        const data = await response.json();

        if (data.success) {
            mostrarMensagem('success', '💰 Sangria realizada com sucesso!');

            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);

        } else {
            mostrarMensagem('danger', data.message || 'Erro ao realizar sangria.');
        }

    } catch (error) {
        console.error(error);
        mostrarMensagem('danger', 'Erro ao processar a requisição.');
    } finally {
        btn.disabled = false;
        btn.innerText = "✅ Efetuar Sangria";
    }
});

// foco automático
document.getElementById('valorInput')?.focus();
</script>

@endsection