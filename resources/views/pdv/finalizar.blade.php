<!-- pdv/finalizar.blade.php -->
@extends('layouts.app')
@section('content')
<div class="container py-4">
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow-sm rounded-4">
<div class="card-header bg-success text-white rounded-top-4">
<h5 class="mb-0">Finalizar Venda</h5>
</div>
<div class="card-body">
<p class="fs-4 text-center fw-bold">Total: R$ {{ number_format($venda->total, 2, ',', '.') }}</p>


<form action="{{ route('pdv.finalizarConfirmado', $venda->id) }}" method="POST">
@csrf


<label class="form-label">Forma de Pagamento</label>
<select name="pagamento" class="form-select mb-3" required>
<option value="dinheiro">Dinheiro</option>
<option value="pix">PIX</option>
<option value="cartao_credito">Cartão Crédito</option>
<option value="cartao_debito">Cartão Débito</option>
</select>


<button class="btn btn-success w-100 py-2">Confirmar Pagamento</button>
</form>
</div>
</div>
</div>
</div>
</div>
@endsection