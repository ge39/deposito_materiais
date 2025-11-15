

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Detalhes da Promoção</h4>
            <a href="<?php echo e(route('promocoes.index')); ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Tipo de Abrangência:</strong><br>
                    <?php echo e(ucfirst($promocao->tipo_abrangencia)); ?>

                </div>
                <div class="col-md-6">
                    <strong>Status:</strong><br>
                    <?php if($promocao->em_promocao): ?>
                        <span class="badge bg-success">Ativa</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inativa</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($promocao->produto): ?>
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Produto:</strong><br>
                    <?php echo e($promocao->produto->nome); ?>

                </div>
            </div>
            <?php endif; ?>

            <?php if($promocao->categoria): ?>
            <div class="row mb-3">
                <div class="col-md-12">
                    <strong>Categoria:</strong><br>
                    <?php echo e($promocao->categoria->nome); ?>

                </div>
            </div>
            <?php endif; ?>

            <hr>

            <div class="row mb-3">
                <div class="col-md-3">
                    <strong>Desconto (%):</strong><br>
                    <?php echo e($promocao->desconto_percentual ?? '—'); ?>

                </div>
                <div class="col-md-3">
                    <strong>Acréscimo (%):</strong><br>
                    <?php echo e($promocao->acrescimo_percentual ?? '—'); ?>

                </div>
                <div class="col-md-3">
                    <strong>Acréscimo (R$):</strong><br>
                    <?php if($promocao->acrescimo_valor): ?>
                        R$ <?php echo e(number_format($promocao->acrescimo_valor, 2, ',', '.')); ?>

                    <?php else: ?>
                        —
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <strong>Preço Promocional:</strong><br>
                    <?php if($promocao->preco_promocional): ?>
                        R$ <?php echo e(number_format($promocao->preco_promocional, 2, ',', '.')); ?>

                    <?php else: ?>
                        —
                    <?php endif; ?>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Início:</strong><br>
                    <?php echo e($promocao->promocao_inicio ? \Carbon\Carbon::parse($promocao->promocao_inicio)->format('d/m/Y') : '—'); ?>

                </div>
                <div class="col-md-6">
                    <strong>Fim:</strong><br>
                    <?php echo e($promocao->promocao_fim ? \Carbon\Carbon::parse($promocao->promocao_fim)->format('d/m/Y') : '—'); ?>

                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between flex-wrap gap-2 mt-4">
                <div>
                    <a href="<?php echo e(route('promocoes.edit', $promocao->id)); ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <form action="<?php echo e(route('promocoes.destroy', $promocao->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta promoção?');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Excluir
                        </button>
                    </form>
                </div>

                <form action="<?php echo e(route('promocoes.toggleStatus', $promocao->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn <?php echo e($promocao->em_promocao ? 'btn-secondary' : 'btn-success'); ?>">
                        <?php if($promocao->em_promocao): ?>
                            <i class="bi bi-pause-circle"></i> Desativar Promoção
                        <?php else: ?>
                            <i class="bi bi-play-circle"></i> Ativar Promoção
                        <?php endif; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/promocoes/show.blade.php ENDPATH**/ ?>