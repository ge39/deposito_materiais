

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3>Fechamento / Auditoria de Caixa #<?php echo e($caixa->id); ?></h3>

    
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card p-2">
                <strong>Abertura:</strong> R$ <?php echo e(number_format($caixa->valor_abertura, 2, ',', '.')); ?><br>
                <strong>Fundo de Troco:</strong> R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?><br>
                <strong>Data Abertura:</strong> <?php echo e($caixa->data_abertura->format('d/m/Y H:i')); ?><br>
                <strong>Status:</strong> <?php echo e(ucfirst($caixa->status)); ?>

            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <strong>Total Entradas:</strong> R$ <?php echo e(number_format($total_entradas, 2, ',', '.')); ?><br>
                <strong>Total Saídas:</strong> R$ <?php echo e(number_format($total_saidas, 2, ',', '.')); ?><br>
                <strong>Total Esperado:</strong> R$ <?php echo e(number_format($total_esperado, 2, ',', '.')); ?><br>
                <strong>Divergência:</strong> 
                <span class="<?php echo e($divergencia != 0 ? 'text-danger fw-bold' : 'text-success fw-bold'); ?>">
                    R$ <?php echo e(number_format($divergencia, 2, ',', '.')); ?>

                </span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <strong>Totais por Forma de Pagamento (Sistema):</strong>
                <ul class="list-unstyled mb-0">
                    <?php $__currentLoopData = ['dinheiro','pix','carteira','cartao_debito','cartao_credito']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forma): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e(ucfirst(str_replace('_',' ',$forma))); ?>: 
                            R$ <?php echo e(number_format($totaisPorForma[$forma] ?? 0, 2, ',', '.')); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>

    
    <?php if($caixa->estaAberto() && auth()->user()->can('fechar-caixa')): ?>
    <form method="POST" action="<?php echo e(route('fechamento.fechar', $caixa->id)); ?>">
        <?php echo csrf_field(); ?>
        <h5>Valores Físicos Conferidos</h5>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="dinheiro" class="form-label">Dinheiro</label>
                <input type="text" class="form-control" name="dinheiro" id="dinheiro" 
                       value="<?php echo e(number_format($totaisPorForma['dinheiro'] ?? 0, 2, ',', '.')); ?>">
            </div>
            <div class="col-md-4">
                <label for="pix" class="form-label">Pix</label>
                <input type="text" class="form-control" name="pix" id="pix" 
                       value="<?php echo e(number_format($totaisPorForma['pix'] ?? 0, 2, ',', '.')); ?>">
            </div>
            <div class="col-md-4">
                <label for="carteira" class="form-label">Carteira</label>
                <input type="text" class="form-control" name="carteira" id="carteira" 
                       value="<?php echo e(number_format($totaisPorForma['carteira'] ?? 0, 2, ',', '.')); ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cartao_debito" class="form-label">Cartão Débito</label>
                <input type="text" class="form-control" name="cartao_debito" id="cartao_debito" 
                       value="<?php echo e(number_format($totaisPorForma['cartao_debito'] ?? 0, 2, ',', '.')); ?>">
            </div>
            <div class="col-md-6">
                <label for="cartao_credito" class="form-label">Cartão Crédito</label>
                <input type="text" class="form-control" name="cartao_credito" id="cartao_credito" 
                       value="<?php echo e(number_format($totaisPorForma['cartao_credito'] ?? 0, 2, ',', '.')); ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Fechar Caixa</button>
    </form>
    <?php endif; ?>

    
    <div class="row mt-4">
        <div class="col-12">
            <h5>Movimentações do Caixa</h5>
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Origem</th>
                        <th>Data</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $caixa->movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($mov->id); ?></td>
                        <td><?php echo e(ucfirst($mov->tipo)); ?></td>
                        <td>R$ <?php echo e(number_format($mov->valor, 2, ',', '.')); ?></td>
                        <td><?php echo e($mov->origem_id ?? '-'); ?></td>
                        <td><?php echo e($mov->data_movimentacao->format('d/m/Y H:i')); ?></td>
                        <td><?php echo e($mov->observacao ?? '-'); ?></td>
                    </tr>
                    
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                
            </table>
                <div class="mt-3">
                    <a href="<?php echo e(route('fechamento.lancar_valores', $caixa->id)); ?>"
                    class="btn btn-primary btn-sm">
                        Lançamento de Valores Manuais
                    </a>
                </div>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/index.blade.php ENDPATH**/ ?>