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
                    <strong>Lote:</strong> <?php echo e($devolucao->itemVenda->lote->numero_lote ?? 'Sem lote'); ?> <br>
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
                                <!-- 🔥 CORREÇÃO: Mudado de 'storage/' para 'imgDevolucoes/' -->
                                <img src="<?php echo e(asset('imgDevolucoes/' . $imagem)); ?>" 
                                    class="img-zoom"
                                    alt="Imagem <?php echo e($idx + 1); ?>" 
                                    style="width: <?php echo e($tamanho); ?>px; height: <?php echo e($tamanho); ?>px; object-fit: cover; border-radius: 5px;">

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <span class="text-muted fs-7">Sem imagem</span>
                            <?php endif; ?>
                        </div>
              

                        <div class="col-md-8">
                            <strong>Motivo da Devolução:</strong>
                            <p><?php echo e($devolucao->motivo); ?></p>

                            <!-- <strong>Logs da Devolução:</strong>
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
                            </a> -->

                        </div>
                     <!-- Alinhamento dos botões principais originais -->
                    <div class="d-flex gap-2 mb-2">
                        <form action="<?php echo e(route('devolucoes.aprovar', $devolucao->id)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <button type="submit" id="btn-aprovar-<?php echo e($devolucao->id); ?>" class="btn btn-success btn-sm" disabled>
                                Aprovar
                            </button>
                        </form>

                        <!-- O botão rejeitar agora abre a seção inferior -->
                        <button type="button" class="btn btn-warning btn-sm btn-rejeitar-trigger" data-id="<?php echo e($devolucao->id); ?>">
                            Rejeitar
                        </button>

                        <a href="<?php echo e(route('devolucoes.cupom', $devolucao)); ?>" class="btn btn-primary btn-sm gerar-vale" data-id="<?php echo e($devolucao->id); ?>" target="_blank">
                            Gerar Vale-Troca
                        </a>
                    </div>

                    <!-- 🔥 SEÇÃO DE REJEIÇÃO: Adicionada abaixo do bloco de botões -->
                    <div id="secao-rejeitar-<?php echo e($devolucao->id); ?>" class="card p-3 bg-light border-warning mt-3 <?php echo e(($errors->has('motivo_rejeicao') || $errors->has('observacao')) && old('rejeicao_id') == $devolucao->id ? '' : 'd-none'); ?>">
                        <h6 class="text-danger fw-bold mb-3">Registrar Rejeição do Item</h6>
                        
                        <form action="<?php echo e(route('devolucoes.rejeitar', $devolucao->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            
                            <input type="hidden" name="rejeicao_id" value="<?php echo e($devolucao->id); ?>">

                            <!-- Campo 1: Motivo da Rejeição -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Motivo da Rejeição <span class="text-danger">*</span></label>
                                <input type="text" 
                                    name="motivo_rejeicao" 
                                    class="form-control form-control-sm <?php $__errorArgs = ['motivo_rejeicao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    value="<?php echo e(old('rejeicao_id') == $devolucao->id ? old('motivo_rejeicao') : ''); ?>" 
                                    placeholder="Ex: Item violado ou fora do prazo de garantia">
                                <?php $__errorArgs = ['motivo_rejeicao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <?php if(old('rejeicao_id') == $devolucao->id): ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php endif; ?>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Campo 2: Observação -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-1">Observação <span class="text-danger">*</span></label>
                                <textarea name="observacao" 
                                        rows="2" 
                                        class="form-control form-control-sm <?php $__errorArgs = ['observacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                        placeholder="Insira detalhes complementares da recusa..."><?php echo e(old('rejeicao_id') == $devolucao->id ? old('observacao') : ''); ?></textarea>
                                <?php $__errorArgs = ['observacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <?php if(old('rejeicao_id') == $devolucao->id): ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php endif; ?>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger btn-sm">Confirmar Rejeição Comercial</button>
                                <button type="button" class="btn btn-secondary btn-sm btn-cancelar-rejeitar" data-id="<?php echo e($devolucao->id); ?>">Cancelar</button>
                            </div>
                        </form>
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
        
        // 1. VALE-TROCA: Habilita o botão Aprovar do card correto
        document.querySelectorAll('.gerar-vale').forEach(btn => {
            btn.addEventListener('click', function (e) {
                let id = this.dataset.id;
                let btnAprovar = document.getElementById('btn-aprovar-' + id);
                if (btnAprovar) {
                    btnAprovar.removeAttribute('disabled');
                    btnAprovar.classList.remove('btn-secondary');
                    btnAprovar.classList.add('btn-success');
                }
            });
        });

        // 2. EXIBIR REJEIÇÃO: Abre o painel de inputs do card correto
        document.querySelectorAll('.btn-rejeitar-trigger').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                let secao = document.getElementById('secao-rejeitar-' + id);
                if (secao) {
                    secao.classList.remove('d-none');
                    secao.querySelector('input[name="motivo_rejeicao"]').focus();
                }
            });
        });

        // 3. CANCELAR REJEIÇÃO: Oculta o painel e limpa os campos
        document.querySelectorAll('.btn-cancelar-rejeitar').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                let secao = document.getElementById('secao-rejeitar-' + id);
                if (secao) {
                    secao.classList.add('d-none');
                    secao.querySelector('input[name="motivo_rejeicao"]').value = '';
                    secao.querySelector('textarea[name="observacao"]').value = '';
                }
            });
        });

        // 4. ZOOM DAS IMAGENS: Controla o estado ativo nos cliques
        const imagens = document.querySelectorAll('.img-zoom');
        imagens.forEach(img => {
            img.addEventListener('click', function(event) {
                event.stopPropagation();
                this.classList.toggle('active');
            });
        });

        document.addEventListener('click', function() {
            imagens.forEach(img => img.classList.remove('active'));
        });
    });
</script>




<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/pendentes.blade.php ENDPATH**/ ?>