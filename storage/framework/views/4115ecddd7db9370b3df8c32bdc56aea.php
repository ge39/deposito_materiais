

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Pedidos de Compras</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="<?php echo e(route('pedidos.create')); ?>" class="btn btn-primary">Novo Pedido</a>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <div class="d-inline-block">
            <?php echo e($pedidos->links('pagination::bootstrap-5')); ?>

        </div>
    </div>

    <div class="card p-3">

        <!-- Header -->
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1rem; 
                    font-weight: bold; background-color: #343a40; color: #fff;
                    padding: 0.5rem;">
            <div>ID</div>
            <div>Fornecedor</div>
            <div>Data Pedido</div>
            <div>Total</div>
            <div>Status</div>
            <div>Criado por</div>
            <div>Ações</div>
        </div>

        <!-- Rows -->
        <div>
            <?php $__empty_1 = true; $__currentLoopData = $pedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1rem;
                            padding: 0.5rem; border-bottom: 1px solid #dee2e6;">

                    <div><?php echo e($pedido->id); ?></div>
                    <div><?php echo e($pedido->fornecedor->nome ?? '-'); ?></div>
                    <div><?php echo e(\Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y')); ?></div>

                    <div>
                        R$ <?php echo e(number_format($pedido->lotes->sum(fn($lote) => $lote->quantidade * $lote->preco_compra), 2, ',', '.')); ?>

                    </div>
                   <div>
                        <?php
                            $statusClasses = [
                                'pendente' => 'badge bg-warning text-dark',
                                'aprovado' => 'badge bg-primary',
                                'recebido' => 'badge bg-success',
                                'cancelado' => 'badge bg-danger'
                            ];
                        ?>

                        <span class="<?php echo e($statusClasses[$pedido->status] ?? 'badge bg-secondary'); ?>">
                            <?php echo e(ucfirst($pedido->status)); ?>

                        </span>
                    </div>

                    <div><?php echo e($pedido->user->name ?? '-'); ?></div>

                    <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                        <a href="<?php echo e(route('pedidos.show', $pedido->id)); ?>" 
                           class="btn btn-info btn-sm"
                           style="font-size:0.65rem; padding:0.25rem 0.4rem;">
                            View
                        </a>

                        <?php if(!in_array($pedido->status, ['cancelado','recebido'])): ?>
                            <a href="<?php echo e(route('pedidos.edit', $pedido->id)); ?>" 
                               class="btn btn-warning btn-sm"
                               style="font-size:0.65rem; padding:0.25rem;">
                                Editar
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo e(route('pedidos.pdf', $pedido->id)); ?>" target="_blank" 
                           class="btn btn-success btn-sm"
                           style="font-size:0.65rem; padding:0.25rem;">
                             Print
                        </a>

                        <?php if($pedido->status === 'pendente'): ?>
                            <a href="<?php echo e(route('pedidos.aprovar', $pedido->id)); ?>" 
                               class="btn btn-primary btn-sm"
                               style="font-size:0.65rem; padding:0.25rem;">
                                Aprovar
                            </a>

                            <a href="<?php echo e(route('pedidos.cancelar', $pedido->id)); ?>" 
                               class="btn btn-danger btn-sm"
                               style="font-size:0.65rem; padding:0.25rem;">
                                Cancelar
                            </a>
                                <?php elseif($pedido->status === 'aprovado'): ?> 
                                    <button 
                                        class="btn btn-success btn-sm"
                                        onclick="window.location='<?php echo e(route('pedidos.receber.view', $pedido->id)); ?>'"
                                        style="font-size:0.65rem; padding:0.25rem;">
                                        Receber
                                    </button>

                                <?php endif; ?>


                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center p-3">Nenhum pedido encontrado.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <div class="d-inline-block">
            <?php echo e($pedidos->links('pagination::bootstrap-5')); ?>

        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>

<!-- Modal de Recebimento -->
<div class="modal fade" id="modalReceber" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Receber Pedido</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="modalBodyReceber">
        <!-- Conteúdo será carregado via AJAX -->
      </div>

    </div>
  </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-receber').forEach(btn => {
            btn.addEventListener('click', async function () {
                const id = this.dataset.id;
                try {
                    // Pega o HTML da view (retorna a partial blade)
                    const res = await fetch("<?php echo e(url('pedidos/modal-recebimento')); ?>/" + id, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) throw new Error('Falha ao carregar modal');

                    const html = await res.text();

                    // Insere no body e mostra (Bootstrap)
                    let wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    document.body.appendChild(wrapper);

                    // assume que a partial contém <div class="modal" id="modalReceber-..."> e inicializa o modal
                    const modalEl = wrapper.querySelector('.modal');
                    if (modalEl) {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();

                        // remove modal do DOM ao fechar
                        modalEl.addEventListener('hidden.bs.modal', () => wrapper.remove());
                    }
                } catch (err) {
                    console.error(err);
                    alert('Erro ao abrir tela de recebimento: ' + err.message);
                }
            });
        });
    });
</script>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pedidos/index.blade.php ENDPATH**/ ?>