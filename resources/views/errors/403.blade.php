@extends('layouts.app')

@section('content')
<div class="text-center mt-5">
    <h1 class="text-danger">403 - Acesso negado</h1>
    <p>Você não tem permissão para acessar esta página.</p>
    <a href="{{ url('/') }}" class="btn btn-primary mt-3">Voltar</a>
</div>
@endsection
