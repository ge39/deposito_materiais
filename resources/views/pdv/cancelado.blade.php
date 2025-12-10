@extends('layouts.app')

@section('content')
<div class="container text-center mt-5">

    <h3 class="text-danger">Venda cancelada com sucesso</h3>
    <a href="{{ route('pdv.index') }}" class="btn btn-primary mt-3">Voltar ao PDV</a>

</div>
@endsection
