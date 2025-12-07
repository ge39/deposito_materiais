<!-- pdv/recibo.blade.php -->
@extends('layouts.app')
@section('content')
<div class="container py-4">
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow-sm rounded-4">
<div class="card-header bg-dark text-white rounded-top-4 text-center">
<h5 class="mb-0">Recibo de Venda</h5>
</div>
<div class="card-body">
<p><strong>Venda:</strong> #{{ $venda->id }}</p>
<p><strong>Cliente:</strong> {{ $venda->cliente->nome }}</p>
<p><strong>Data:</strong> {{ $venda->data_venda }}</p>


<hr>
<h6 class="fw-bold">Itens</h6>
<ul class="list-group mb-3">
@foreach($itens as $item)
<li class="list-group-item d-flex justify-content-between">
{{ $item->produto->nome }} (x{{ $item->quantidade }})
<span>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
</li>
@endforeach
</ul>


<h4 class="text-end fw-bold">Total: R$ {{ number_format($venda->total, 2, ',', '.') }}</h4>


<div class="text-center mt-3">
<a href="{{ route('pdv.index') }}" class="btn btn-primary px-4">Nova Venda</a>
</div>
</div>
</div>
</div>
</div>
</div>
@endsection