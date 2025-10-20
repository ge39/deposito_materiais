@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Unidade de Medida</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('unidades.update', $unidade->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="nome" class="form-control" value="{{ old('nome', $unidade->nome) }}" required>
        </div>

        <div class="mb-3">
            <label>Sigla</label>
            <input type="text" name="sigla" class="form-control" value="{{ old('sigla', $unidade->sigla) }}" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="ativo" value="1" class="form-check-input" {{ $unidade->ativo ? 'checked' : '' }}>
            <label class="form-check-label">Ativo</label>
        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="{{ route('unidades.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
@endsection
