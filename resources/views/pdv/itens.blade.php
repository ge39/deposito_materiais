@extends('layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">Venda Nº {{ $venda->id }}</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- FORM PARA ADICIONAR ITEM --}}
    <div class="card mb-4">
        <div class="card-body">

            <form action="{{ route('pdv.adicionarItem', $venda->id) }}" method="POST">
                @csrf

                <div class="row g-2">

                    <div class="col-md-8">
                        <input type="text" name="produto"
                               class="form-control"
                               placeholder="Nome, código ou código de barras">
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="quantidade"
                               min="1" value="1"
                               class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Adicionar</button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    {{-- LISTA DE ITENS --}}
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Produto</th>
                <th width="70">Qtd</th>
                <th width="120">Preço</th>
                <th width="120">Total</th>
                <th width="60">Remover</th>
            </tr>
        </thead>

        <tbody>

            @foreach($venda->itens as $item)
                <tr>
                    <td>{{ $item->produto->nome }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                    <td>
                        <form method="POST"
                              action="{{ route('pdv.removerItem', $item->id) }}"
                              onsubmit="return confirm('Remover item?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm w-100">X</button>
                        </form>
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>

    {{-- TOTAL --}}
    <div class="alert alert-primary text-end fs-4">
        Total: <strong>R$ {{ number_format($venda->total, 2, ',', '.') }}</strong>
    </div>

    <div class="d-flex gap-2">

        <a href="{{ route('pdv.finalizar', $venda->id) }}"
           class="btn btn-success w-100">
            Finalizar venda
        </a>

        <form method="POST" action="{{ route('pdv.cancelar', $venda->id) }}">
            @csrf
            <button class="btn btn-danger w-100"
                onclick="return confirm('Cancelar venda?')">
                Cancelar
            </button>
        </form>

    </div>

</div>
@endsection
