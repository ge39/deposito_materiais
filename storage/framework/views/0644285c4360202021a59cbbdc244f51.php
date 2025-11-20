

<?php $__env->startSection('content'); ?>

<?php $__currentLoopData = $item->venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemVenda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <?php
        // Somente devoluções aprovadas ou concluídas
        $qtdDevolvida = $itemVenda->devolucoes
            ->whereIn('status', ['aprovada', 'concluida'])
            ->sum('quantidade');

        // Quantidade disponível para nova devolução
        $qtdDisponivel = $itemVenda->quantidade - $qtdDevolvida;

        // Valor extornado até agora
        $valorExtornado = $qtdDevolvida * $itemVenda->preco_unitario;

        // Se já devolveu tudo
        $jaDevolvido = $qtdDisponivel <= 0;

        // Lista de devoluções
        $devolucoes = $itemVenda->devolucoes ?? collect();
    ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="container">
    <h2 class="mb-4">Registrar Devolução / Troca - Venda #<?php echo e($item->venda->id); ?></h2>
    <h4 class="mb-4">Cliente: <?php echo e($item->venda->cliente->nome); ?></h4>

    <div class="row">
        <div class="col-12 d-flex justify-content-end gap-2 mb-2">
            <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-secondary">Voltar</a>
        </div>

        <?php $__currentLoopData = $item->venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemVenda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                // Somente devoluções aprovadas ou concluídas
                $qtdDevolvida = $itemVenda->devolucoes
                    ->whereIn('status', ['aprovada', 'concluida'])
                    ->sum('quantidade');

                // Quantidade ainda disponível
                $qtdDisponivel = $itemVenda->quantidade - $qtdDevolvida;

                // Valor extornado
                $valorExtornado = $qtdDevolvida * $itemVenda->preco_unitario;

                // Já devolveu tudo?
                $jaDevolvido = $qtdDisponivel <= 0;

                $devolucoes = $itemVenda->devolucoes ?? collect();
            ?>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm position-relative">

                    
                    <?php if($jaDevolvido): ?>
                        <div class="stamped">PRODUTO JÁ DEVOLVIDO</div>
                    <?php endif; ?>

                    <div class="card-header bg-light d-flex align-items-center gap-3">
                        <img 
                            src="<?php echo e(asset('storage/' . ($itemVenda->produto->imagem ?? ''))); ?>"
                            class="card-img-top"
                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                        >
                        <strong><?php echo e($itemVenda->produto->nome); ?></strong>
                    </div>

                    <div class="card-body">

                        <p><strong>Código ID:</strong> 000<?php echo e($itemVenda->id); ?></p>
                        <p><strong>Lote Rastreio:</strong> 000<?php echo e($itemVenda->lote_id); ?></p>
                        <p><strong>Comprada:</strong> <?php echo e($itemVenda->quantidade); ?></p>

                        <p><strong>Valor Compra:</strong> 
                            R$<?php echo e(number_format($itemVenda->subtotal, 2, ',', '.')); ?>

                        </p>

                        <p><strong>Valor Extornado:</strong> 
                            R<?php echo e(number_format($valorExtornado, 2, ',', '.')); ?>

                        </p>

                        <p><strong>Já Devolvida:</strong>
                            <?php echo e($qtdDevolvida); ?> <?php echo e($itemVenda->produto->unidadeMedida->sigla); ?>

                        </p>

                        <p><strong>Data da Venda:</strong> 
                            <?php echo e($itemVenda->venda->created_at->format('d/m/Y')); ?>

                        </p>

                        <p><strong>Última Devolução:</strong>
                            <?php if($devolucoes->count() > 0): ?>
                                <?php echo e($devolucoes->last()->created_at->format('d/m/Y')); ?>

                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </p>

                        <?php if(!$jaDevolvido): ?>
                        <form 
                            action="<?php echo e(route('devolucoes.salvar')); ?>" 
                            method="POST" 
                            enctype="multipart/form-data" 
                            class="d-flex flex-column gap-2"
                        >
                            <?php echo csrf_field(); ?>

                            <input type="hidden" name="item_id" value="<?php echo e($itemVenda->id); ?>">

                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0">À Devolver:</label>
                                <input 
                                    type="number"
                                    name="quantidade" 
                                    class="form-control"
                                    min="1" 
                                    max="<?php echo e($qtdDisponivel); ?>"
                                    placeholder="0" 
                                    required
                                    style="width: 100px;"
                                >
                            </div>

                            <input 
                                type="text" 
                                name="motivo" 
                                class="form-control" 
                                placeholder="Motivo da devolução" 
                                required
                            >

                            <label class="mt-2">Evidências (opcional, até 4 imagens):</label>

                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <?php for($i = 1; $i <= 4; $i++): ?>
                                    <div class="image-container">
                                        <input 
                                            type="file" 
                                            name="imagem<?php echo e($i); ?>" 
                                            id="imagem-<?php echo e($itemVenda->id); ?>-<?php echo e($i); ?>" 
                                            class="image-input" 
                                            accept="image/*" 
                                            hidden
                                        >
                                        <label 
                                            for="imagem-<?php echo e($itemVenda->id); ?>-<?php echo e($i); ?>" 
                                            class="image-label"
                                        >
                                            <img 
                                                id="preview-<?php echo e($itemVenda->id); ?>-<?php echo e($i); ?>" 
                                                class="img-preview"
                                                src="" 
                                                alt="Adicionar imagem"
                                            >
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                                    Confirmar
                                </button>
                                <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-secondary btn-sm flex-grow-1">
                                    Voltar
                                </a>
                            </div>
                        </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>


<script>
document.querySelectorAll('.image-input').forEach(input => {
    input.addEventListener('change', function() {

        const file = this.files[0];

        const previewId =
            'preview-' + this.id.split('-')[1] + '-' + this.id.split('-')[2];

        const preview = document.getElementById(previewId);

        if (file) {
            const reader = new FileReader();
            reader.onload = ev => {
                preview.src = ev.target.result;
                preview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.classList.remove('has-image');
        }
    });
});
</script>


<style>
.image-container {
    position: relative;
    width: 100px;
    height: 100px;
}

.image-label {
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100px;
    height: 100px;
    background-color: #f8f9fa;
    border: 1px dashed #ccc;
    border-radius: 8px;
    transition: background 0.3s;
}

.image-label:hover {
    background-color: #e9ecef;
}

.img-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    background-color: #f8f9fa;
}

.img-preview:not(.has-image)::before {
    content: "+";
    position: absolute;
    font-size: 2rem;
    color: #bbb;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Carimbo produto devolvido */
.stamped {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-20deg);
    background: rgba(255, 0, 0, 0.7);
    color: white;
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 1rem;
    z-index: 10;
    text-align: center;
}
</style>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/registrar.blade.php ENDPATH**/ ?>