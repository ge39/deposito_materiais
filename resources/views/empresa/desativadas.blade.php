

@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Empresas / Filiais Desativadas</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($empresas->isEmpty())
        <div class="alert alert-warning text-center" style="background-color: #f5deb3;">
            Nenhuma empresa desativada encontrada.
        </div>
        <a href="{{ route('empresa.index') }}" class="btn btn-secondary mb-4">
            <i class="bi bi-arrow-left-circle"></i> Voltar
        </a>
    @else
        <div class="row">
            @foreach ($empresas as $empresa)
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title mb-2">{{ $empresa->nome }}</h5>
                            <p class="card-text mb-1"><strong>CNPJ:</strong> {{ $empresa->cnpj ?? 'Não informado' }}</p>
                            <p class="card-text mb-1"><strong>Cidade:</strong> {{ $empresa->cidade ?? '-' }}</p>
                            <p class="card-text mb-3"><strong>Estado:</strong> {{ $empresa->estado ?? '-' }}</p>

                            <div class="d-flex justify-content-between">
                                 <form action="{{ route('empresa.ativar', $empresa->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success mb-4">
                                        <i class="bi bi-check-circle"></i> Ativar
                                    </button>
                                </form>
                                <a href="{{ route('empresa.index') }}" class="btn btn-secondary mb-4">
                                    <i class="bi bi-arrow-left-circle"></i> Voltar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
