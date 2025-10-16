@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Ocorrência Pós-Venda #{{ $posVenda->id }}</h2>

    <form action="{{ route('pos_vendas.update', $posVenda->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control" required>
                <option value="pendente" {{ $posVenda->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                <option value="concluido" {{ $posVenda->status == 'concluido' ? 'selected' : '' }}>Concluído</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3" class="form-control">{{ $posVenda->descricao }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Atualizar Ocorrência</button>
        <a href="{{ route('pos_vendas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
