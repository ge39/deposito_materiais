

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Orçamentos</h2>
        <a href="<?php echo e(route('orcamentos.create')); ?>" class="btn btn-primary">
            Novo Orçamento
        </a>
    </div>
    <form method="GET" class="mb-4">
        <div class="row g-2">

            <!-- FILTRO STATUS -->
            <div class="col-md-4">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- Todos os Status --</option>
                    <option value="Aguardando Aprovação" <?php echo e(request('status') == 'Aguardando Aprovação' ? 'selected' : ''); ?>>Aguardando Aprovação</option>
                    <option value="Aprovado" <?php echo e(request('status') == 'Aprovado' ? 'selected' : ''); ?>>Aprovado</option>
                    <option value="Expirado" <?php echo e(request('status') == 'Expirado' ? 'selected' : ''); ?>>Expirado</option>
                </select>
            </div>

            <!-- BUSCA POR CÓDIGO -->
            <div class="col-md-4">
                <label class="form-label fw-bold">Código do Orçamento</label>
                <input type="text" name="codigo_orcamento" class="form-control"
                    placeholder="Ex: 1025"
                    value="<?php echo e(request('codigo_orcamento')); ?>">
            </div>

            <!-- BOTÃO -->
             <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1  h-50">Buscar</button>
                <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-secondary flex-grow-1  h-50">Limpar</a>
            </div>

        </div>
    </form>


    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-striped">

         <div class="d-flex justify-content-center mt-3">
            <div class="d-inline-block">
                <?php echo e($orcamentos->links('pagination::bootstrap-5')); ?>

            </div>
        </div>

        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Total</th>
                <th>Status</th>
                <th>Código</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $orcamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $orcamento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr <?php if($orcamento->status === 'Expirado'): ?> class="text-danger" <?php endif; ?>>
            <td><?php echo e($orcamento->id); ?></td>
            <td><?php echo e($orcamento->cliente->nome ?? '-'); ?></td>
            <td><?php echo e(\Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y')); ?></td>
            <td>R$ <?php echo e(number_format($orcamento->total, 2, ',', '.')); ?></td>
            <td>
                <?php if($orcamento->status === 'Expirado'): ?>
                    <span class="badge bg-danger" style="font-size: 14px;">
                        Expirado
                    </span>
                <?php elseif($orcamento->status === 'Aguardando aprovacao'): ?>
                    <span class="badge bg-warning text-dark" style="font-size: 14px;">
                        Aguardando aprovação
                    </span>
                <?php elseif($orcamento->status === 'Aprovado'): ?>
                    <span class="badge bg-success" style="font-size: 14px;">
                        Aprovado
                    </span>
                <?php elseif($orcamento->status === 'Cancelado'): ?>
                    <span class="badge bg-secondary" style="font-size: 14px;">
                        Cancelado
                    </span>
                <?php endif; ?>
            </td>

            <td><?php echo e($orcamento->codigo_orcamento); ?></td>
            <td>
                <a href="<?php echo e(route('orcamentos.edit', $orcamento->id)); ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="<?php echo e(route('orcamentos.gerarPdf', $orcamento->id)); ?>" class="btn btn-primary" target="_blank">Gerar PDF</a>
                <a href="<?php echo e(route('orcamentos.whatsapp', $orcamento->id)); ?>" 
                    class="btn btn-success btn-sm" 
                    target="_blank">
                        Enviar WhatsApp
                </a>
            </td>
        </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($orcamentos->isEmpty()): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted fw-bold py-3 text-red-500">
                        Nenhum orçamento encontrado para os filtros informados.
                    </td>
                </tr>
            <?php endif; ?>

        </tbody>

    </table>
   
    <div class="d-flex justify-content-center mt-3">
        <div class="d-inline-block">
            <?php echo e($orcamentos->links('pagination::bootstrap-5')); ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/index.blade.php ENDPATH**/ ?>