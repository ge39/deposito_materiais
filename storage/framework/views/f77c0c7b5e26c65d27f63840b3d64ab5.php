

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Editar Empresa / Filial</h2>

    <div class="card shadow-sm border rounded-2 p-4">
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('empresa.update', $empresa->id)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Nome</label>
                    <input type="text" name="nome" class="form-control" value="<?php echo e(old('nome', $empresa->nome)); ?>" required>
                </div>

                <div class="col-md-6">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" class="form-control" value="<?php echo e(old('cnpj', $empresa->cnpj)); ?>">
                </div>

                <div class="col-md-6">
                    <label>Inscrição Estadual</label>
                    <input type="text" name="inscricao_estadual" class="form-control" value="<?php echo e(old('inscricao_estadual', $empresa->inscricao_estadual)); ?>">
                </div>

                <div class="col-md-6">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?php echo e(old('telefone', $empresa->telefone)); ?>">
                </div>

                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $empresa->email)); ?>">
                </div>

                <div class="col-md-6">
                    <label>Site</label>
                    <input type="text" name="site" class="form-control" value="<?php echo e(old('site', $empresa->site)); ?>">
                </div>

                <div class="col-md-6">
                    <label>Endereço</label>
                    <input type="text" name="endereco" class="form-control" value="<?php echo e(old('endereco', $empresa->endereco)); ?>">
                </div>

                <div class="col-md-2">
                    <label>Número</label>
                    <input type="text" name="numero" class="form-control" value="<?php echo e(old('numero', $empresa->numero)); ?>">
                </div>

                <div class="col-md-4">
                    <label>Complemento</label>
                    <input type="text" name="complemento" class="form-control" value="<?php echo e(old('complemento', $empresa->complemento)); ?>">
                </div>

                <div class="col-md-4">
                    <label>Bairro</label>
                    <input type="text" name="bairro" class="form-control" value="<?php echo e(old('bairro', $empresa->bairro)); ?>">
                </div>

                <div class="col-md-4">
                    <label>Cidade</label>
                    <input type="text" name="cidade" class="form-control" value="<?php echo e(old('cidade', $empresa->cidade)); ?>">
                </div>

                <div class="col-md-2">
                    <label>Estado (UF)</label>
                    <input type="text" name="estado" class="form-control" value="<?php echo e(old('estado', $empresa->estado)); ?>">
                </div>

                <div class="col-md-2">
                    <label>CEP</label>
                    <input type="text" name="cep" class="form-control" value="<?php echo e(old('cep', $empresa->cep)); ?>">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">Atualizar</button>
                <a href="<?php echo e(route('empresa.index')); ?>" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/empresa/edit.blade.php ENDPATH**/ ?>