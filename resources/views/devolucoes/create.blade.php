@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Registrar Devolução</h2>

    {{-- Mensagens de erro --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Card principal --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Dados da Venda</h5>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Cliente</label>
                    <input type="text" class="form-control" value="{{ $item->venda->cliente->nome }}" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Produto</label>
                    <input type="text" class="form-control" value="{{ $item->produto->nome }}" disabled>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qtde Vendida</label>
                    <input type="text" class="form-control" value="{{ $item->quantidade }}" disabled>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor Unitário</label>
                    <input type="text" class="form-control" value="R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}" disabled>
                </div>
            </div>

            {{-- Formulário de devolução --}}
            <form action="{{ route('devolucoes.store') }}" method="POST">
                @csrf
                <input type="hidden" name="venda_item_id" value="{{ $item->id }}">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="quantidade" class="form-label">Quantidade a Devolver</label>
                        <input type="number" name="quantidade" id="quantidade" class="form-control" min="1" max="{{ $item->quantidade }}" required>
                    </div>

                    <div class="col-md-5">
                        <label for="motivo" class="form-label">Motivo</label>
                        <input type="text" name="motivo" id="motivo" class="form-control" placeholder="Ex: Defeito no produto" required>
                    </div>

                    <div class="col-md-4">
                        <label for="tipo" class="form-label">Tipo de Devolução</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="defeito">Defeito</option>
                            <option value="erro">Erro de envio</option>
                            <option value="insatisfacao">Insatisfação</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observacao" class="form-label">Observações (opcional)</label>
                    <textarea name="observacao" id="observacao" rows="3" class="form-control"></textarea>
                </div>

                {{-- Botões --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('rastreio.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-danger">Registrar Devolução</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
