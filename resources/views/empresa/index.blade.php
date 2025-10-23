@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Filiais / Empresas</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('empresa.create') }}" class="btn btn-primary mb-4">Nova Empresa / Filial</a>
    <a href="{{ route('empresa.desativadas') }}" class="btn btn-secondary mb-4">
        <i class="bi bi-archive"></i> Ver Desativadas
    </a>
    <div class="row g-4">
        @forelse($empresas as $empresa)
            <div class="col-md-4">
                <div class="card shadow-sm border rounded-2 h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $empresa->nome }}</h5>
                        <p class="card-text mb-1"><strong>CNPJ:</strong> {{ $empresa->cnpj }}</p>
                        <p class="card-text mb-1"><strong>Telefone:</strong> {{ $empresa->telefone }}</p>
                        <p class="card-text mb-3"><strong>Cidade / Estado:</strong> {{ $empresa->cidade }} / {{ $empresa->estado }}</p>

                        <div class="mt-auto d-flex justify-content-between">
                            <a href="{{ route('empresa.edit', $empresa->id) }}" class="btn btn-warning mb-4">Editar</a>

                            <form action="{{ route('empresa.desativar', $empresa->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger mb-4"
                                    onclick="return confirm('Deseja realmente desativar esta Empresa?');">
                                    Desativar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Nenhuma empresa cadastrada.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
