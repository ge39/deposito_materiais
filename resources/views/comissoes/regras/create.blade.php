@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Nova Regra de Comissão
        </h1>

        <div class="text-muted">
            Defina vendedor, escopo,
            percentual e vigência.
        </div>

    </div>


    <form
        method="POST"
        action="{{ route('comissoes.regras.store') }}"
    >
        @csrf

        @include('comissoes.regras._form')

    </form>

</div>

@endsection