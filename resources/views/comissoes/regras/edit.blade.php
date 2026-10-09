@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Editar Regra de Comissão
        </h1>

        <div class="text-muted">
            Atualize os parâmetros da regra.
        </div>

    </div>


    <form
        method="POST"
        action="{{
            route(
                'comissoes.regras.update',
                $regra
            )
        }}"
    >

        @csrf
        @method('PUT')

        @include('comissoes.regras._form')

    </form>

</div>

@endsection