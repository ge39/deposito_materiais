@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">Abrir nova venda</h2>

    <form action="{{ route('pdv.abrirConfirmar') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Cliente (opcional)</label>
            <input type="text" class="form-control" name="cliente_nome"
                   placeholder="Nome do cliente">
        </div>

        <button class="btn btn-success w-100">Confirmar e abrir venda</button>
    </form>

</div>

@endsection
