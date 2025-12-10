@extends('layouts.app')

@section('content')

<div class="container">

    <h3 class="mb-4">Finalizar Venda Nº {{ $venda->id }}</h3>

    <div class="alert alert-secondary fs-4">
        Total a pagar:
        <strong>R$ {{ number_format($venda->total, 2, ',', '.') }}</strong>
    </div>

    <form action="{{ route('pdv.finalizarConfirmado', $venda->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Forma de pagamento</label>
            <select class="form-select" name="forma_pagamento" required>
                <option value="">Selecione...</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao_credito">Cartão de Crédito</option>
                <option value="cartao_debito">Cartão de Débito</option>
                <option value="pix">PIX</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Valor recebido</label>
            <input type="number" step="0.01" name="valor_recebido"
                class="form-control" placeholder="Ex: 100.00">
        </div>

        <button class="btn btn-success w-100">Confirmar pagamento</button>

    </form>

</div>

@endsection
