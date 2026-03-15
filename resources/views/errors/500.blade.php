@extends('layouts.app')

@section('title', 'Erro Interno')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">

                    <div class="mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 60px;"></i>
                    </div>

                    <h3 class="fw-bold text-danger">
                        Erro Interno do Sistema (500)
                    </h3>

                    <p class="text-muted mt-3">
                        {{ $exception->getMessage() 
                            ?: 'Ocorreu uma falha inesperada durante o processamento da operação.' }}
                    </p>

                    <div class="alert alert-light border mt-4 text-start">
                        <strong>Ação recomendada:</strong>
                        <ul class="mb-0">
                            <li>Tente atualizar a página.</li>
                            <li>Verifique se o caixa está aberto.</li>
                            <li>Se o problema persistir, contate o administrador.</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <button onclick="location.reload()" class="btn btn-outline-secondary me-2">
                            Recarregar
                        </button>

                        <div class="mt-4">
                            <a href="{{ url('/') }}" class="btn btn-dark px-4">
                                Voltar ao Início
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection