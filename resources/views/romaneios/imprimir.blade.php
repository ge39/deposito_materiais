@extends('layouts.app')

@section('content')

<style>

@media print{

    .no-print{
        display:none !important;
    }

    body{
        background:#FFF;
    }

    .page-break{
        page-break-after:always;
    }

}

.table td,
.table th{
    vertical-align:middle;
}

.assinatura{
    margin-top:70px;
    text-align:center;
}

.assinatura hr{
    border:1px solid #000;
    margin-bottom:4px;
}

</style>

<div class="container-fluid">

<div class="d-flex justify-content-between mb-4 no-print">

    <a href="{{ route('romaneios.show',$romaneio->id) }}"
       class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>

    <button
        onclick="window.print();"
        class="btn btn-primary">

        <i class="bi bi-printer"></i>
        Imprimir

    </button>

</div>

@for($via=1;$via<=2;$via++)

<div class="card mb-5">

<div class="card-body">

<div class="row">

<div class="col-8">

<h3 class="fw-bold">

{{ config('app.name') }}

</h3>

<h5>

ROMANEIO DE SEPARAÇÃO

</h5>

</div>

<div class="col-4 text-end">

<strong>Via:</strong>

@if($via==1)

Expedição

@else

Motorista

@endif

<br>

<strong>Romaneio:</strong>

{{ $romaneio->codigo_romaneio }}

<br>

<strong>Data:</strong>

{{ \Carbon\Carbon::parse($romaneio->data_emissao)->format('d/m/Y H:i') }}

</div>

</div>

<hr>

<div class="row mb-3">

<div class="col-md-6">

<strong>Cliente</strong><br>

{{ optional($romaneio->entrega)->cliente->nome ?? '-' }}

</div>

<div class="col-md-3">

<strong>Entrega</strong><br>

#{{ $romaneio->entrega_id }}

</div>

<div class="col-md-3">

<strong>Status</strong><br>

{{ $romaneio->status }}

</div>

</div>

<div class="row mb-3">

<div class="col-md-6">

<strong>Motorista</strong><br>

{{ optional($romaneio->motorista)->nome ?? 'Não definido' }}

</div>

<div class="col-md-6">

<strong>Veículo</strong><br>

{{ optional($romaneio->veiculo)->placa ?? 'Não definido' }}

</div>

</div>

<table class="table table-bordered table-sm">

<thead class="table-dark">

<tr>

<th width="8%">Ordem</th>

<th width="14%">Localização</th>

<th>Produto</th>

<th width="10%">Un.</th>

<th width="10%">Qtde</th>

<th width="10%">Separado</th>

<th width="10%">Conferido</th>

</tr>

</thead>

<tbody>

@foreach($romaneio->itens as $indice=>$item)

@php

$produto =
$item->entregaItem->produto
?? $item->entregaItem->vendaItem->produto
?? $item->entregaItem->itemOrcamento->produto;

@endphp

<tr>

<td class="text-center">

{{ $indice+1 }}

</td>

<td>

{{ optional($produto->localizacaoEstoque)->codigo ?? '---' }}

</td>

<td>

{{ $produto->nome }}

</td>

<td class="text-center">

{{ optional($produto->unidadeMedida)->sigla }}

</td>

<td class="text-center">

{{ number_format($item->quantidade_prevista,2,',','.') }}

</td>

<td></td>

<td></td>

</tr>

@endforeach

</tbody>

</table>

<div class="row mt-4">

<div class="col-12">

<strong>Observações</strong>

<div style="height:80px;border:1px solid #000;"></div>

</div>

</div>

<div class="row mt-5">

<div class="col-4">

<div class="assinatura">

<hr>

Separado por

</div>

</div>

<div class="col-4">

<div class="assinatura">

<hr>

Conferido por

</div>

</div>

<div class="col-4">

<div class="assinatura">

<hr>

Motorista

</div>

</div>

</div>

</div>

</div>

@if($via==1)

<div class="page-break"></div>

@endif

@endfor

</div>

@endsection