

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
                    <?php
                        $vendaItem = $devolucao->vendaItem;
                        $venda = optional($vendaItem)->venda;
                        $cliente = optional($venda)->cliente;
                        $produtoVendaItem = optional($vendaItem)->produto;
                        $lote = optional($vendaItem)->lote;
                    ?>

                    <strong>Venda ID:</strong> 000<?php echo e($devolucao->venda_id?? '-'); ?> <br>
                    <strong>Cliente:</strong> <?php echo e($devolucao->itemVenda->venda->cliente->nome ?? ''); ?> <br>
                    <strong>Lote:</strong> <?php echo e($devolucao->itemVenda->lote->numero ?? 'Sem lote'); ?> <br>
                    <strong>Produto ID:</strong> 000<?php echo e(optional($devolucao->produto)->id ?? '-'); ?> <br>
                    <strong>Produto Nome:</strong> <?php echo e($devolucao->produto->nome ?? 'Sem Descrição'); ?> <br>
                    <strong>Motivo Rejeição:</strong> <?php echo e($devolucao->motivo_rejeicao); ?> <br>
                    <strong>Valor Unitario</strong> R$ <?php echo e($devolucao->itemVenda->preco_unitario ?? '-'); ?> <br>
                    <strong>Subtotal</strong> R$ <?php echo e(number_format($devolucao->itemVenda->preco_unitario * $devolucao->itemVenda->quantidade, 2, ',', '.')); ?> <br>
                    <strong>Quantidade Comprada:</strong> <?php echo e($devolucao->itemVenda->quantidade ?? 'Sem Descrição'); ?> <br>
                    <strong>Qtde Devolvida:</strong> <?php echo e($devolucao->quantidade); ?> <br>
                    <strong>Qtde Restante:</strong> <?php echo e($devolucao->itemVenda->quantidade - $devolucao->quantidade); ?>  <br>
                    <strong>Status:</strong> <span class="badge bg-warning"><?php echo e(ucfirst($devolucao->status)); ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-10 d-flex gap-2 flex-row flex-wrap align-items-start">

                            <?php
                                $imagens = [];
                                for ($i = 1; $i <= 4; $i++) {
                                    $campo = 'imagem' . $i;
                                    if ($devolucao->$campo) {
                                        $imagens[] = $devolucao->$campo;
                                    }
                                }
                                $multiplicador = count($imagens) > 1 ? 0.99 : 1;
                                $tamanho = 90 * $multiplicador;
                            ?>

                            <?php $__empty_2 = true; $__currentLoopData = $imagens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $imagem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <img src="<?php echo e(asset('storage/' . $imagem)); ?>" 
                                    class="img-zoom"
                                    alt="Imagem <?php echo e($idx + 1); ?>" 
                                    style="width: <?php echo e($tamanho); ?>px; height: <?php echo e($tamanho); ?>px; object-fit: cover; border-radius: 5px;">

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                Sem imagem
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

        <?php if($loop->index % 2 == 1 || $loop->last): ?>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="alert alert-info">
            Nenhuma devolução pendente encontrada.
        </div>
    <?php endif; ?>

   
</div>
<?php $__env->stopSection(); ?>

<style>
    .img-zoom {
        transition: transform 0.25s ease-in-out;
        cursor: zoom-in;
        z-index: 10;
        position: relative;
    }

    .img-zoom.active {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%) scale(3); /* zoom + centro */
        cursor: zoom-out;
        z-index: 9999;
        max-width: 90%;
        max-height: 90%;
    }

    /* Impede que o card estoure quando o zoom está ativo */
    .zoom-container {
        position: relative;
        overflow: hidden; /* Impede sair do container */
    }
</style>



<script>
    document.addEventListener("DOMContentLoaded", function() {

        document.querySelectorAll('.gerar-vale').forEach(btn => {
            btn.addEventListener('click', function (e) {

                // OPÇÃO 1 – deixa abrir o PDF normalmente:
                // (não usa preventDefault)

                let id = this.dataset.id;
                let btnAprovar = document.getElementById('btn-aprovar-' + id);

                if (btnAprovar) {
                    btnAprovar.removeAttribute('disabled');  // ✔ corrigido
                    btnAprovar.classList.remove('btn-secondary');
                    btnAprovar.classList.add('btn-success');
                }
            });
        });

    });

    document.addEventListener("DOMContentLoaded", function() {

        // Seleciona todas as imagens das devoluções
        const imagens = document.querySelectorAll('.img-zoom');

        imagens.forEach(img => {
            img.addEventListener('click', function(event) {
                event.stopPropagation(); // impede que o clique saia da imagem
                this.classList.toggle('active'); // ativa o zoom
            });
        });

        // Clicar fora da imagem tira o zoom
        document.addEventListener('click', function() {
            imagens.forEach(img => img.classList.remove('active'));
        });

    });


</script>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/pendentes.blade.php ENDPATH**/ ?>