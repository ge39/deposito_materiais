<h3>Histórico do Orçamento</h3>

<div class="timeline">
    <?php $__currentLoopData = $movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="timeline-item">
            
            <div class="timeline-icon 
                <?php if($mov->tipo == 'atendido'): ?> bg-success
                <?php elseif($mov->tipo == 'pendente'): ?> bg-warning
                <?php elseif($mov->tipo == 'reserva'): ?> bg-primary
                <?php else: ?> bg-secondary
                <?php endif; ?>">
            </div>

            <div class="timeline-content">
                <strong>
                    <?php switch($mov->tipo):
                        case ('atendido'): ?> ✔ Atendido <?php break; ?>
                        <?php case ('pendente'): ?> ⚠ Pendente <?php break; ?>
                        <?php case ('reserva'): ?> 📦 Reserva <?php break; ?>
                        <?php case ('status'): ?> 🔄 Status <?php break; ?>
                        <?php default: ?> ℹ Informação
                    <?php endswitch; ?>
                </strong>

                <p><?php echo e($mov->descricao); ?></p>

                <?php if($mov->quantidade): ?>
                    <small>Quantidade: <?php echo e($mov->quantidade); ?></small><br>
                <?php endif; ?>

                <small class="text-muted">
                    <?php echo e($mov->created_at->format('d/m/Y H:i')); ?>

                </small>
            </div>

        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\movimentacoes\timeline.blade.php ENDPATH**/ ?>