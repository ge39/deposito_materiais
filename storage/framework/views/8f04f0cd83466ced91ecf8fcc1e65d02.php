

<?php $__env->startSection('content'); ?>

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

    <a href="<?php echo e(route('romaneios.show',$romaneio->id)); ?>"
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

<?php for($via=1;$via<=2;$via++): ?>

<div class="card mb-5">

<div class="card-body">

<div class="row">

<div class="col-8">

<h3 class="fw-bold">

<?php echo e(config('app.name')); ?>


</h3>

<h5>

ROMANEIO DE SEPARAÇÃO

</h5>

</div>

<div class="col-4 text-end">

<strong>Via:</strong>

<?php if($via==1): ?>

Expedição

<?php else: ?>

Motorista

<?php endif; ?>

<br>

<strong>Romaneio:</strong>

<?php echo e($romaneio->codigo_romaneio); ?>


<br>

<strong>Data:</strong>

<?php echo e(\Carbon\Carbon::parse($romaneio->data_emissao)->format('d/m/Y H:i')); ?>


</div>

</div>

<hr>

<div class="row mb-3">

<div class="col-md-6">

<strong>Cliente</strong><br>

<?php echo e(optional($romaneio->entrega)->cliente->nome ?? '-'); ?>


</div>

<div class="col-md-3">

<strong>Entrega</strong><br>

#<?php echo e($romaneio->entrega_id); ?>


</div>

<div class="col-md-3">

<strong>Status</strong><br>

<?php echo e($romaneio->status); ?>


</div>

</div>

<div class="row mb-3">

<div class="col-md-6">

<strong>Motorista</strong><br>

<?php echo e(optional($romaneio->motorista)->nome ?? 'Não definido'); ?>


</div>

<div class="col-md-6">

<strong>Veículo</strong><br>

<?php echo e(optional($romaneio->veiculo)->placa ?? 'Não definido'); ?>


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

<?php $__currentLoopData = $romaneio->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<?php

$produto =
$item->entregaItem->produto
?? $item->entregaItem->vendaItem->produto
?? $item->entregaItem->itemOrcamento->produto;

?>

<tr>

<td class="text-center">

<?php echo e($indice+1); ?>


</td>

<td>

<?php echo e(optional($produto->localizacaoEstoque)->codigo ?? '---'); ?>


</td>

<td>

<?php echo e($produto->nome); ?>


</td>

<td class="text-center">

<?php echo e(optional($produto->unidadeMedida)->sigla); ?>


</td>

<td class="text-center">

<?php echo e(number_format($item->quantidade_prevista,2,',','.')); ?>


</td>

<td></td>

<td></td>

</tr>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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

<?php if($via==1): ?>

<div class="page-break"></div>

<?php endif; ?>

<?php endfor; ?>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/imprimir.blade.php ENDPATH**/ ?>