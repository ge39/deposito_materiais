

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Detalhes do Cliente</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Nome:</strong> <?php echo e($cliente->nome); ?></p>
            <p><strong>Tipo:</strong> <?php echo e(ucfirst($cliente->tipo)); ?></p>
            <p><strong>CPF/CNPJ:</strong> <?php echo e($cliente->cpf_cnpj); ?></p>
            <p><strong>Telefone:</strong> <?php echo e($cliente->telefone); ?></p>
            <p><strong>Email:</strong> <?php echo e($cliente->email); ?></p>
            <p><strong>Limite de Crédito:</strong> R$ <?php echo e(number_format($cliente->limite_credito, 2, ',', '.')); ?></p>
            <p><strong>Observações:</strong> <?php echo e($cliente->observacoes ?: '—'); ?></p>
            <p><strong>Criado em:</strong> <?php echo e($cliente->created_at->format('d/m/Y H:i')); ?></p>
        </div>
    </div>
    <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-secondary mt-3">Voltar</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/clientes/show.blade.php ENDPATH**/ ?>