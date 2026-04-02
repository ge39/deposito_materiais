 

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card border-dark mx-auto" style="max-width: 400px;">
        <div class="card-body text-center" style="font-family: monospace; font-size: 12px;">
            <h5 class="card-title"><?php echo e(config('app.name')); ?></h5>
            <p class="mb-1"><strong>COMPROVANTE DE SANGRIA</strong></p>
            <hr class="my-1">

            <p class="mb-1">Código: <?php echo e($sangria->codigo_operacao); ?> - <?php echo e($sangria->id); ?></p>
            <p class="mb-1">PDV: <?php echo e($sangria->numero_pdv); ?></p>
            <p class="mb-1">Operador: <?php echo e($sangria->user->name); ?></p>
            <p class="mb-1">Data: <?php echo e($sangria->created_at->format('d/m/Y H:i')); ?></p>
            <hr class="my-1">
            <p class="mb-1"><strong>SALDO SANGRIA - DINHEIRO</strong></p>
            <p class="mb-1">Saldo anterior: R$ <?php echo e(number_format($sangria->saldo_antes, 2, ',', '.')); ?></p>
            <p class="mb-1">Valor Sangria: R$ <?php echo e(number_format($sangria->valor, 2, ',', '.')); ?></p>
            
            <p class="mb-1">Saldo posterior: R$ <?php echo e(number_format($sangria->saldo_depois, 2, ',', '.')); ?></p>
            <p class="mb-1">Motivo: <?php echo e(ucfirst($sangria->motivo)); ?></p>
            <hr class="my-1">

            <p class="mb-1">Assinatura Operador:</p>
            </br>
            <div class="mb-3" style="border-top: 1px solid #000; width: 80%; margin: 0 auto;"></div>

            <button class="btn btn-primary btn-sm mt-3" onclick="window.print()">Imprimir</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('styles'); ?>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 400px;
        }
        button {
            display: none;
        }
    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/pdv/sangria_impressao.blade.php ENDPATH**/ ?>