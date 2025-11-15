

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Editar Promoção</h4>
            <a href="<?php echo e(route('promocoes.index')); ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card-body">
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <strong>Ops!</strong> Verifique os erros abaixo:<br><br>
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($erro); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('promocoes.update', $promocao->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
                <div class="mb-3">
                    <label for="tipo_abrangencia" class="form-label">Tipo de Abrangência</label>
                    <select name="tipo_abrangencia" id="tipo_abrangencia" class="form-select" required onchange="toggleCampos(this.value)">
                        <option value="">Selecione...</option>
                        <option value="produto" <?php echo e($promocao->tipo_abrangencia == 'produto' ? 'selected' : ''); ?>>Por Produto</option>
                        <option value="categoria" <?php echo e($promocao->tipo_abrangencia == 'categoria' ? 'selected' : ''); ?>>Por Categoria</option>
                    </select>
                </div>

                
                <div class="mb-3 <?php echo e($promocao->tipo_abrangencia == 'produto' ? '' : 'd-none'); ?>" id="campo_produto">
                    <label for="produto_id" class="form-label">Produto</label>
                    <select name="produto_id" id="produto_id" class="form-select">
                        <option value="">Selecione um produto...</option>
                        <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($produto->id); ?>" <?php echo e($promocao->produto_id == $produto->id ? 'selected' : ''); ?>>
                                <?php echo e($produto->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="mb-3 <?php echo e($promocao->tipo_abrangencia == 'categoria' ? '' : 'd-none'); ?>" id="campo_categoria">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="form-select">
                        <option value="">Selecione uma categoria...</option>
                        <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($categoria->id); ?>" <?php echo e($promocao->categoria_id == $categoria->id ? 'selected' : ''); ?>>
                                <?php echo e($categoria->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <hr>

                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="desconto_percentual" class="form-label">Desconto (%)</label>
                        <input type="number" name="desconto_percentual" id="desconto_percentual"
                            value="<?php echo e($promocao->desconto_percentual); ?>" class="form-control" step="0.01" min="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="acrescimo_percentual" class="form-label">Acréscimo (%)</label>
                        <input type="number" name="acrescimo_percentual" id="acrescimo_percentual"
                            value="<?php echo e($promocao->acrescimo_percentual); ?>" class="form-control" step="0.01" min="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="acrescimo_valor" class="form-label">Acréscimo (R$)</label>
                        <input type="number" name="acrescimo_valor" id="acrescimo_valor"
                            value="<?php echo e($promocao->acrescimo_valor); ?>" class="form-control" step="0.01" min="0">
                    </div>
                </div>

                <hr>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="promocao_inicio" class="form-label">Data de Início</label>
                        <input type="date" name="promocao_inicio" id="promocao_inicio"
                            value="<?php echo e(\Carbon\Carbon::parse($promocao->promocao_inicio)->format('Y-m-d')); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="promocao_fim" class="form-label">Data de Fim</label>
                        <input type="date" name="promocao_fim" id="promocao_fim"
                            value="<?php echo e(\Carbon\Carbon::parse($promocao->promocao_fim)->format('Y-m-d')); ?>" class="form-control" required>
                    </div>
                </div>

                
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="em_promocao" id="em_promocao"
                        <?php echo e($promocao->em_promocao ? 'checked' : ''); ?>>
                    <label class="form-check-label" for="em_promocao">Promoção ativa</label>
                </div>

                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Atualizar Promoção
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function toggleCampos(valor) {
        const campoProduto = document.getElementById('campo_produto');
        const campoCategoria = document.getElementById('campo_categoria');

        campoProduto.classList.add('d-none');
        campoCategoria.classList.add('d-none');

        if (valor === 'produto') {
            campoProduto.classList.remove('d-none');
        } else if (valor === 'categoria') {
            campoCategoria.classList.remove('d-none');
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/promocoes/edit.blade.php ENDPATH**/ ?>