@extends('layouts.app')

@section('content')

@if(session('success'))
    <div class="alert alert-primary text-center">
        {{ session('success') }}
    </div>
@endif

<div class="container mt-4">

    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">⚙️ Configuração de Sangria</h5>
                </div>

                <div class="card-body">

                    {{-- Mensagem de sucesso --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sangria-config.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Valor limite do caixa  R$ {{  $config->valor_limite ?? '0,00' }}
                            </label>

                            <input 
                                type="number" 
                                step="0.01" 
                                name="valor_limite" 
                                class="form-control @error('valor_limite') is-invalid @enderror"
                                placeholder="Ex: 500.00"
                                value="{{ old('valor_limite', $config->valor_limite ?? '') }}"
                                required
                            >

                            @error('valor_limite')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Quando o caixa ultrapassar esse valor, a sangria será sugerida automaticamente.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                Voltar
                            </a>

                            <button type="submit" class="btn btn-success">
                                💾 Salvar Configuração
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection