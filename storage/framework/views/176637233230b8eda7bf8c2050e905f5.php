

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Detalhes do Funcionário</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Nome:</strong> <?php echo e($funcionario->nome); ?></p>
            <p><strong>Função:</strong> <?php echo e(ucfirst($funcionario->funcao)); ?></p>
            <p><strong>Telefone:</strong> <?php echo e($funcionario->telefone); ?></p>
            <p><strong>Email:</strong> <?php echo e($funcionario->email); ?></p>
            <p><strong>Endereco:</strong> <?php echo e($funcionario->endereco); ?></p>
            <p><strong>Cidade:</strong> <?php echo e($funcionario->cidade); ?></p>
            <p><strong>Salário:</strong> R$ <?php echo e(number_format($funcionario->salario, 2, ',', '.')); ?></p>
            <p><strong>Data de Admissão:</strong> <?php echo e($funcionario->data_admissao ? \Carbon\Carbon::parse($funcionario->data_admissao)->format('d/m/Y') : '—'); ?></p>
            <p><strong>Observações:</strong> <?php echo e($funcionario->observacoes ?: '—'); ?></p>
            <p><strong>Criado em:</strong> <?php echo e($funcionario->created_at->format('d/m/Y H:i')); ?></p>
        </div>
    </div>
    <a href="<?php echo e(route('funcionarios.index')); ?>" class="btn btn-secondary mt-3">Voltar</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/funcionarios/show.blade.php ENDPATH**/ ?>