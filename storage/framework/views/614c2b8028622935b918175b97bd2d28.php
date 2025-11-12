

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Cadastrar Fornecedor</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success" id="alerta"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger" id="alerta">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('fornecedores.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="row g-3">

            <!-- Dados do Fornecedor -->
            <div class="col-md-8">
                <label for="nome" class="form-label">Nome/Razão Social</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?php echo e(old('nome')); ?>" required>
            </div>

            <div class="col-md-4">
                <label for="tipo" class="form-label">Tipo</label>
                <select name="tipo" id="tipo" class="form-select" required>
                    <option value="fisica" <?php if(old('tipo')=='fisica'): ?> selected <?php endif; ?>>Pessoa Física</option>
                    <option value="juridica" <?php if(old('tipo')=='juridica'): ?> selected <?php endif; ?>>Pessoa Jurídica</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="form-control" value="<?php echo e(old('cpf_cnpj')); ?>">
            </div>

            <div class="col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" name="telefone" id="telefone" class="form-control" value="<?php echo e(old('telefone')); ?>">
            </div>

            <div class="col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo e(old('email')); ?>">
            </div>

            <!-- Endereço com CEP -->
            <div class="col-md-3">
                <label for="cep" class="form-label">CEP</label>
                <input type="text" name="cep" id="cep" class="form-control" value="<?php echo e(old('cep')); ?>" onblur="buscarCep()">
            </div>

            <div class="col-md-5">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text" name="endereco" id="endereco" class="form-control" value="<?php echo e(old('endereco')); ?>">
            </div>

            <div class="col-md-2">
                <label for="numero" class="form-label">Número</label>
                <input type="text" name="numero" id="numero" class="form-control" value="<?php echo e(old('numero')); ?>">
            </div>
            <div class="col-md-4">
                <label for="cidade" class="form-label">Bairro</label>
                <input type="text" name="cidade" id="bairro" class="form-control" value="<?php echo e(old('bairro')); ?>">
            </div>
            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" name="cidade" id="cidade" class="form-control" value="<?php echo e(old('cidade')); ?>">
            </div>

            <div class="col-md-2">
                <label for="uf" class="form-label">Estado</label>
                <input type="text" name="uf" id="uf" class="form-control" value="<?php echo e(old('uf')); ?>">
            </div>

            <div class="col-md-12">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea name="observacoes" id="observacoes" class="form-control" rows="3"><?php echo e(old('observacoes')); ?></textarea>
            </div>

            <div class="col-md-12 form-check mt-2">
                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" <?php echo e(old('ativo', 1) ? 'checked' : ''); ?>>
                <label for="ativo" class="form-check-label">Ativo</label>
            </div>

        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>

<script src="<?php echo e(asset('js/form-masks.js')); ?>"></script>
<script src="<?php echo e(asset('js/cep.js')); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fornecedores/create.blade.php ENDPATH**/ ?>