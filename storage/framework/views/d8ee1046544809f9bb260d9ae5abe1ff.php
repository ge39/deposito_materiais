

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Devoluções Pendentes</h2>
    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2 mb-2">
            <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
    <?php $__empty_1 = true; $__currentLoopData = $devolucoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $devolucao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php if($loop->index % 2 == 0): ?>
            <div class="row mb-4">
        <?php endif; ?>
         
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <strong>Venda ID:</strong> 000<?php echo e(optional($devolucao->vendaItem->venda->cliente)->id ?? '-'); ?> <br>
                    <strong>Cliente:</strong> <?php echo e(optional($devolucao->vendaItem->venda->cliente)->nome ?? '-'); ?> <br>
                    <strong>Lote:</strong> <?php echo e(optional($devolucao->vendaItem->lote)->id ?? '-'); ?> <br>
                    <strong>Codigo Produto:</strong> 000<?php echo e(optional($devolucao->produto)->id ?? '-'); ?> <br>
                    <strong>Descrição:</strong> <?php echo e(optional($devolucao->vendaItem->produto)->nome ?? '-'); ?> <br>
                    <strong>Qtde Devolvida:</strong> <?php echo e($devolucao->quantidade); ?> <br>
                    <strong>Status:</strong> <span class="badge bg-warning"><?php echo e(ucfirst($devolucao->status)); ?></span>
                </div>

                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <?php if(optional($devolucao->vendaItem->produto)->imagem): ?>
                                <img src="<?php echo e(asset('storage/' . $devolucao->vendaItem->produto->imagem)); ?>" class="img-fluid rounded" alt="Produto">
                            <?php else: ?>
                                <img src="<?php echo e(asset('images/no-image.png')); ?>" class="img-fluid rounded" alt="Sem imagem">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <strong>Motivo da Devolução:</strong>
                            <p><?php echo e($devolucao->motivo); ?></p>

                            <strong>Logs da Devolução:</strong>
                            <ul class="list-group list-group-flush mb-2">
                                <?php $__currentLoopData = optional($devolucao->logs); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="list-group-item">
                                        <small>
                                            <strong><?php echo e(ucfirst($log->acao)); ?></strong> - <?php echo e($log->descricao); ?> 
                                            (<?php echo e($log->usuario); ?> em <?php echo e($log->created_at->format('d/m/Y H:i')); ?>)
                                        </small>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                                                                <form action="<?php echo e(route('devolucoes.aprovar', $devolucao->id)); ?>"
                                    method="POST" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit"
                                            id="btn-aprovar-<?php echo e($devolucao->id); ?>"
                                            class="btn btn-success btn-sm"
                                            disabled>
                                        Aprovar
                                    </button>
                                </form>

                                <form action="<?php echo e(route('devolucoes.rejeitar', $devolucao->id)); ?>"
                                    method="POST" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        Rejeitar
                                    </button>
                                </form>

                                <a href="<?php echo e(route('devolucoes.cupom', $devolucao)); ?>"
                                class="btn btn-primary btn-sm gerar-vale"
                                data-id="<?php echo e($devolucao->id); ?>"
                                target="_blank">
                                    Gerar Vale-Troca
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if($loop->index % 2 == 1 || $loop->last): ?>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="alert alert-info">
            Nenhuma devolução pendente encontrada.
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-center">
        <?php echo e($devolucoes->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<script>
document.addEventListener("DOMContentLoaded", function() {

    // Quando clicar no botão "Gerar Vale-Troca"
    document.querySelectorAll('.gerar-vale').forEach(btn => {

        btn.addEventListener('click', function() {

            let id = this.getAttribute('data-id');

            // Habilita o botão Aprovar correspondente
            let btnAprovar = document.getElementById('btn-aprovar-' + id);
            if (btnAprovar) {
                btnAprovar.removeAttribute('disabled');
                btnAprovar.classList.add('btn-success');
            }
        });

    });

});
</script>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/pendentes.blade.php ENDPATH**/ ?>