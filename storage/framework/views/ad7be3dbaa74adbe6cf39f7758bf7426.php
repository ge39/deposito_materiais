

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Detalhes do Fornecedor</h2>

    <div class="card p-3">
        <p><strong>ID:</strong> <?php echo e($fornecedor->id); ?></p>
        <p><strong>Nome:</strong> <?php echo e($fornecedor->nome); ?></p>
        <p><strong>CNPJ:</strong> <?php echo e($fornecedor->cnpj ?: '-'); ?></p>
        <p><strong>Telefone:</strong> <?php echo e($fornecedor->telefone ?: '-'); ?></p>
        <p><strong>Email:</strong> <?php echo e($fornecedor->email ?: '-'); ?></p>
        <p><strong>Cidade:</strong> <?php echo e($fornecedor->cidade ?: '-'); ?></p>
        <p><strong>Endereço:</strong> <?php echo e($fornecedor->endereco ?: '-'); ?></p>
        <p><strong>Observações:</strong> <?php echo e($fornecedor->observacoes ?: '-'); ?></p>
        <p><strong>Criado em:</strong> <?php echo e($fornecedor->created_at ? $fornecedor->created_at->format('d/m/Y H:i') : '-'); ?></p>
        <p><strong>Atualizado em:</strong> <?php echo e($fornecedor->updated_at ? $fornecedor->updated_at->format('d/m/Y H:i') : '-'); ?></p>
    </div>

    <div class="mt-3">
        <a href="<?php echo e(route('fornecedores.edit', $fornecedor)); ?>" class="btn btn-warning">Editar</a>
        <a href="<?php echo e(route('fornecedores.index')); ?>" class="btn btn-secondary">Voltar</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\fornecedores\show.blade.php ENDPATH**/ ?>