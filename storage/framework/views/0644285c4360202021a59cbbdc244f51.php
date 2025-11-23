

<?php $__env->startSection('content'); ?>

<div class="container">

            <?php if(session('error')): ?>
            <div class="alert alert-danger">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

    <h2 class="mb-3">Registrar Devolução / Troca - Venda #<?php echo e($venda->id); ?></h2>
    <h4 class="mb-0">Cliente: <?php echo e($venda->cliente->nome); ?></h4>

    
    <p class="mb-0"><strong>Total da Venda:</strong> 
        R$ <?php echo e(number_format($venda->total, 2, ',', '.')); ?>

    </p>

    <p class="mb-4"><strong>Data da Venda:</strong> 
        <?php echo e(\Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y')); ?>

    </p>
    

    <div class="d-flex justify-content-end mb-3">
        <a href="<?php echo e(route('devolucoes.index')); ?>" class="btn btn-secondary btn-sm">Voltar</a>
    </div>

    <div class="row">

        <?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemVenda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php
                $qtdDevolvida = $itemVenda->devolucoes
                    ->whereIn('status', ['aprovada', 'concluida'])
                    ->sum('quantidade');

                $qtdDisponivel = $itemVenda->quantidade - $qtdDevolvida;

                $valorExtornado = $qtdDevolvida * $itemVenda->preco_unitario;

                $jaDevolvido = $qtdDisponivel <= 0;

                $devolucoes = $itemVenda->devolucoes ?? collect();
            ?>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm position-relative compact-card">

                    <?php if($jaDevolvido): ?>
                        <div class="stamped">PRODUTO JÁ DEVOLVIDO</div>
                    <?php endif; ?>

                    <div class="card-body">

                        
                        <div class="d-flex align-items-center mb-3">
                            <img 
                                src="<?php echo e(asset('storage/' . ($itemVenda->produto->imagem ?? ''))); ?>"
                                class="product-img"
                            />
                            <div class="ms-3">
                                <strong class="product-name"><?php echo e($itemVenda->produto->nome); ?></strong>
                                <div class="small text-muted">Cód. Item: 000<?php echo e($itemVenda->produto_id); ?></div>
                            </div>
                        </div>

                        
                        <div class="info-block mb-3">
                            <div><strong>Lote:</strong> 000<?php echo e($itemVenda->lote_id); ?></div>
                            <div><strong>Comprada:</strong> <?php echo e($itemVenda->quantidade); ?></div>
                            <div><strong>Preco Unit.:</strong> <?php echo e($itemVenda->preco_unitario); ?></div>
                            <div><strong>Já Devolvida:</strong> <?php echo e($qtdDevolvida); ?> <?php echo e($itemVenda->produto->unidadeMedida->sigla); ?></div>
                            <div><strong>Disponível:</strong> <?php echo e($qtdDisponivel); ?></div>
                            <div><strong>Valor Compra:</strong> R$ <?php echo e(number_format($itemVenda->subtotal, 2, ',', '.')); ?></div>
                            <div><strong>Valor Extornado:</strong> R$ <?php echo e(number_format($valorExtornado, 2, ',', '.')); ?></div>
                            <div><strong>Data da Venda:</strong> <?php echo e($itemVenda->venda->created_at->format('d/m/Y')); ?></div>
                            <div><strong>Última Devolução:</strong>
                                <?php if($devolucoes->count() > 0): ?>
                                    <?php echo e($devolucoes->last()->created_at->format('d/m/Y')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <?php if(!$jaDevolvido): ?>
                        <form 
                            action="<?php echo e(route('devolucoes.salvar')); ?>" 
                            method="POST" 
                            enctype="multipart/form-data"
                            class="form-compact"
                        >
                            <?php echo csrf_field(); ?>

                            <input type="hidden" name="item_id" value="<?php echo e($itemVenda->id); ?>">

                            <div class="row g-2">

                                <div class="col-4">
                                    <label class="form-label small">À Devolver</label>
                                    <input 
                                        type="number" 
                                        name="quantidade" 
                                        min="1" 
                                        max="<?php echo e($qtdDisponivel); ?>"
                                        class="form-control form-control-sm"
                                        required
                                    >
                                </div>

                                
                                <div class="col-8">
                                    <label class="form-label small">Motivo</label>
                                    <select 
                                        name="motivo" 
                                        class="form-control form-control-sm motivo-select"
                                    ></select>

                                    
                                    <input 
                                        type="text"
                                        name="motivo_outro"
                                        class="form-control form-control-sm mt-1 d-none outro-motivo-input"
                                        placeholder="Descreva o motivo"
                                    >
                                </div>
                            </div>

                            
                            <label class="mt-2 small">Evidências (até 4 imagens)</label>

                            <div class="d-flex flex-wrap gap-2 mb-2">

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
                                        <label for="imagem-<?php echo e($itemVenda->id); ?>-<?php echo e($i); ?>" class="image-label">
                                            <img 
                                                id="preview-<?php echo e($itemVenda->id); ?>-<?php echo e($i); ?>" 
                                                class="img-preview"
                                                alt=""
                                            >
                                        </label>
                                    </div>
                                <?php endfor; ?>

                            </div>

                            <button class="btn btn-danger btn-sm w-100 mt-2">Confirmar</button>

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
        const previewId = 'preview-' + this.id.split('-')[1] + '-' + this.id.split('-')[2];
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


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const motivos = [
    "Atraso na obra",
    "Bloqueio no acesso à obra",
    "Carga incompleta na separação",
    "Cliente comprou a mais",
    "Cliente mudou de ideia",
    "Cliente recusou a receber",
    "Cor ou tonalidade divergente",
    "Defeito de fabricação",
    "Descrição incorreta do produto",
    "Desistência após orçamento",
    "Embalagem danificada",
    "Entrega fora do prazo",
    "Erro de cadastro no sistema",
    "Erro na conferência da mercadoria",
    "Erro na separação do pedido",
    "Erro no lançamento da venda",
    "Estoque desatualizado",
    "Fornecedor enviou produto errado",
    "Item faltando no pedido",
    "Material com defeito",
    "Material incompatível com o projeto",
    "Medida ou especificação incorreta",
    "Pedido duplicado",
    "Perda de material na obra",
    "Preço divergente na compra",
    "Problema no transporte",
    "Produto avariado no transporte",
    "Produto quebrado ou avariado",
    "Produto diferente do solicitado",
    "Produto excedente na obra",
    "Produto não serviu para a obra",
    "Quantidade incorreta",
    "Tamanho ou medida incompatível",
    "Troca por preferência do cliente",
    "Variação de lote não aceita",
    "Vencimento próximo do material",
];

    document.querySelectorAll('.motivo-select').forEach(select => {
        motivos.forEach(m => {
            let op = new Option(m, m, false, false);
            select.appendChild(op);
        });

        $(select).select2({
            placeholder: "Selecione ou digite o motivo",
            allowClear: true,
            width: 'resolve'
        });
    });

});
</script>


<style>
.compact-card {
    border-radius: 10px;
    padding: 10px;
}

.product-img {
    width: 100px; 
    height: 100px; 
    object-fit: cover; 
    border-radius: 8px;
    border: 1px solid #ddd;
}

.product-name {
    font-size: 1.1rem;
}

.info-block {
    font-size: 0.85rem;
    line-height: 1.2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 15px;
}

.form-compact .form-label {
    margin-bottom: 2px;
}

.image-container {
    width: 70px;
    height: 70px;
    position: relative;
}

.image-label {
    width: 100%;
    height: 100%;
    border: 1px dashed #ccc;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    cursor: pointer;
}

.img-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.img-preview:not(.has-image)::before {
    content: "+";
    font-size: 1.6rem;
    color: #aaa;
    position: absolute;
}

.stamped {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-15deg);
    background: rgba(255, 0, 0, 0.75);
    color: white;
    font-weight: bold;
    padding: 6px 15px;
    border-radius: 6px;
    font-size: 0.9rem;
    z-index: 20;
}
</style>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/registrar.blade.php ENDPATH**/ ?>