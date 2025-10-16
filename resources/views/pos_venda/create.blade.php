@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nova Ocorrência Pós-Venda</h2>

    <form action="{{ route('pos_vendas.store') }}" method="POST">
        @csrf
        <input type="hidden" name="venda_id" value="{{ $venda_id }}">

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select name="tipo" id="tipo" class="form-control" required>
                <option value="">Selecione</option>
                <option value="devolucao">Devolução</option>
                <option value="troca">Troca</option>
                <option value="atendimento">Atendimento</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="valor_devolucao" class="form-label">Valor Devolução</label>
            <input type="number" step="0.01" name="valor_devolucao" id="valor_devolucao" class="form-control" value="0">
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao" rows="3" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Registrar Ocorrência</button>
        <a href="{{ route('pos_vendas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
