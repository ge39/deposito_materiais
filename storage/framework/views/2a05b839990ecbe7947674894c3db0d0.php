

<?php $__env->startSection('content'); ?>
<div class="container">

    <!-- 
    <?php
        $alertType = 'success'; // padrão
        $message = session('success') ?? session('popup_message') ?? null;

        if (session('success') && str_contains(session('success'), 'excluída')) {
            $alertType = 'danger';
        } elseif (session('success') && str_contains(session('success'), 'encerrada')) {
            $alertType = 'warning';
        } elseif (session('popup_message')) {
            $alertType = 'info';
        }
    ?>

    <?php if($message): ?>
        <div class="alert alert-<?php echo e($alertType); ?> alert-dismissible fade show" role="alert">
            <?php echo e($message); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?> -->



    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Editar Promoção</h4>

        
        <form action="<?php echo e(route('promocoes.encerrar', $promocao->id)); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja encerrar esta promoção?')">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-x-circle"></i> Encerrar Promoção
            </button>
        </form>
    </div>


        <div class="card-body">

            <form action="<?php echo e(route('promocoes.update', $promocao->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <h5 class="mb-3">Dados do Produto - ID <?php echo e($promocao->produto_id); ?></h5>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Produto </label>
                        <input type="text" class="form-control" value="<?php echo e($promocao->produto->nome); ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Marca</label>
                        <input type="text" class="form-control" value="<?php echo e($promocao->produto->marca->nome); ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Unidade</label>
                        <input type="text" class="form-control" value="<?php echo e($promocao->produto->unidadeMedida->nome); ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Fornecedor</label>
                        <input type="text" class="form-control" value="<?php echo e($promocao->produto->fornecedor->nome ?? '---'); ?>" readonly>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Descrição</label>
                        <textarea class="form-control" rows="2" readonly><?php echo e($promocao->produto->descricao); ?></textarea>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Dados da Promoção</h5>

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label>Preço Original</label>
                        <input type="text" name="preco_original" class="form-control" value="<?php echo e($promocao->preco_original); ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Desconto (%)</label>
                        <input type="number" name="desconto_percentual" class="form-control" min="<?php echo e($promocao->desconto_percentual); ?>" value="<?php echo e($promocao->desconto_percentual); ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Preço Promocional</label>
                        <input type="text" name="preco_promocional" class="form-control" value="<?php echo e($promocao->preco_promocional); ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Status</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="status" id="status1" value="1" autocomplete="off" <?php echo e($promocao->status == 1 ? 'checked' : ''); ?>>
                            <label class="btn btn-outline-success" for="status1">Ativo</label>

                            <!-- <input type="radio" class="btn-check" name="status" id="status0" value="0" autocomplete="off" <?php echo e($promocao->status == 0 ? 'checked' : ''); ?>>
                            <label class="btn btn-outline-secondary" for="status0">Inativo</label> -->
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Data Início</label>
                        <input type="date" name="promocao_inicio" class="form-control" value="<?php echo e($promocao->promocao_inicio); ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Data Fim</label>
                        <input type="date" name="promocao_fim" class="form-control" value="<?php echo e($promocao->promocao_fim); ?>" min="<?php echo e($promocao->promocao_fim); ?>">
                    </div>

                </div>

                
                <div>
                  <button type="submit"class="btn btn-success mt-3" > Salvar Alterações </button>
                 <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary mt-3">Voltar</a>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    const precoOriginal = parseFloat('<?php echo e($promocao->preco_original); ?>');
    const descontoInput = document.querySelector('[name="desconto_percentual"]');
    const precoPromocionalInput = document.querySelector('[name="preco_promocional"]');

    descontoInput.addEventListener('input', function() {
        let descontoAtual = parseFloat(this.value);
        if(descontoAtual < <?php echo e($promocao->desconto_percentual); ?>) {
            descontoAtual = <?php echo e($promocao->desconto_percentual); ?>;
            this.value = descontoAtual;
        }
        const precoPromocional = precoOriginal - (precoOriginal * descontoAtual / 100);
        precoPromocionalInput.value = precoPromocional.toFixed(2);
    });
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/promocoes/edit.blade.php ENDPATH**/ ?>