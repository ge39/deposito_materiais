@extends('layouts.app')

@section('title', 'Rota do PDV não encontrada')

@section('content')
<div class="container py-5">
    <div class="text-center">

        <h2 class="text-danger fw-bold">Rota do PDV não encontrada</h2>

        <p class="text-muted mt-3">
            O recurso solicitado dentro do módulo PDV não existe.
        </p>

        <div class="alert alert-warning mt-4 text-start">
            <strong>Possíveis causas:</strong>
            <ul class="mb-0">
                <li>Terminal não configurado corretamente.</li>
                <li>Rota removida ou alterada.</li>
                <li>Acesso manual via URL incorreta.</li>
            </ul>
        </div>

        <div class="mt-4">
            <a href="{{ route('pdv.index') }}" class="btn btn-danger me-2">
                Voltar ao PDV
            </a>

            <a href="{{ url('/') }}" class="btn btn-dark px-4">
                Voltar ao Início
            </a>
        </div>

    </div>
</div>
@endsection