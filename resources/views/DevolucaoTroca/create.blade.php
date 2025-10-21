@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nova Devolução/Troca</h2>

    <form action="{{ route('devolucoes.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="venda_id" class="form-label">Venda</label>
            <select name="venda_id" id="venda_id" class="form-select" required>
                <option value="">Selecione</option>
                @foreach($vendas as $venda)
                    <option value="{{ $venda->id }}">ID: {{ $venda->id }} - Cliente: {{ $venda->cliente->nome }}</option>
                @endforeach
            </select>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="produto_id" class="form-label">Produto</label>
                <select name="produto_id" id="produto_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($produtos as $produto)
                        <option value="{{ $produto->id }}">{{ $produto->descricao }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" name="quantidade" id="quantidade" class="form-control" value="1" min="1" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select name="tipo" id="tipo" class="form-select" required>
                <option value="devolucao">Devolução</option>
                <option value="troca">Troca</option>
            </select>
        </div>

        <div class="mb-3" id="produto_troca_div" style="display:none;">
            <label for="produto_troca_id" class="form-label">Produto para Troca</label>
            <select name="produto_troca_id" id="produto_troca_id" class="form-select">
                <option value="">Selecione</option>
                @foreach($produtos as $produto)
                    <option value="{{ $produto->id }}">{{ $produto->descricao }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" rows="2" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Registrar</button>
        <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

@push('scripts')
<script>
document.querySelector('#tipo').addEventListener('change', function(){
    document.querySelector('#produto_troca_div').style.display = this.value === 'troca' ? 'block' : 'none';
});
</script>
@endpush
@endsection
