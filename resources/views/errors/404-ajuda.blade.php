@extends('layouts.app')

@section('title', 'Página não encontrada')

@section('content')
<div class="container py-5">
    <div class="text-center">

        <h2 class="text-secondary fw-bold">Página não encontrada (404)</h2>

        <p class="text-muted mt-3">
            A rota acessada não existe ou foi removida.
        </p>

        <div class="alert alert-light border mt-4 text-start">
            <strong>Você pode:</strong>
            <ul class="mb-0">
                <li>Verificar se digitou o endereço corretamente.</li>
                <li>Retornar ao painel principal.</li>
                <li>Utilizar o menu do sistema para navegar.</li>
            </ul>
        </div>

        <div class="mt-4">
            <a href="{{ url('/') }}" class="btn btn-dark px-4">
                Voltar ao Início
            </a>
        </div>

    </div>
</div>
@endsection