

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Editar Cliente: <?php echo e($cliente->nome); ?></h2>

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

    <form action="<?php echo e(route('clientes.update', $cliente->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <!-- Dados Pessoais -->
        <h4 class="mb-3">Dados Pessoais</h4>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" value="<?php echo e($cliente->nome); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="fisica" <?php if($cliente->tipo == 'fisica'): ?> selected <?php endif; ?>>Pessoa Física</option>
                    <option value="juridica" <?php if($cliente->tipo == 'juridica'): ?> selected <?php endif; ?>>Pessoa Jurídica</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Data de Nascimento</label>
                <input type="date" name="data_nascimento" class="form-control" value="<?php echo e($cliente->data_nascimento?->format('Y-m-d')); ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Sexo</label>
                <select name="sexo" class="form-select">
                    <option value="masculino" <?php if($cliente->sexo == 'masculino'): ?> selected <?php endif; ?>>Masculino</option>
                    <option value="feminino" <?php if($cliente->sexo == 'feminino'): ?> selected <?php endif; ?>>Feminino</option>
                    <option value="outro" <?php if($cliente->sexo == 'outro'): ?> selected <?php endif; ?>>Outro</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" value="<?php echo e($cliente->telefone); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="<?php echo e($cliente->email); ?>">
            </div>
        </div>

        <!-- Endereço -->
        <h4 class="mb-3">Endereço</h4>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">CEP</label>
                <input type="text" name="cep" class="form-control" value="<?php echo e($cliente->cep); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" class="form-control" value="<?php echo e($cliente->endereco); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Número</label>
                <input type="text" name="numero" class="form-control" value="<?php echo e($cliente->numero); ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Bairro</label>
                <input type="text" name="bairro" class="form-control" value="<?php echo e($cliente->bairro); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" class="form-control" value="<?php echo e($cliente->cidade); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" class="form-control" value="<?php echo e($cliente->estado); ?>">
            </div>
        </div>

        <!-- Dados Documentais -->
        <h4 class="mb-3">Documentos</h4>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">CPF/CNPJ</label>
                <input type="text" name="cpf_cnpj" class="form-control" value="<?php echo e($cliente->cpf_cnpj); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">RG/Inscrição Estadual</label>
                <input type="text" name="rg_ie" class="form-control" value="<?php echo e($cliente->rg_ie); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Órgão Emissor</label>
                <input type="text" name="orgao_emissor" class="form-control" value="<?php echo e($cliente->orgao_emissor); ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Data de Emissão</label>
                <input type="date" name="data_emissao" class="form-control" value="<?php echo e($cliente->data_emissao?->format('Y-m-d')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Limite de Crédito (R$)</label>
                <input type="number" step="0.01" name="limite_credito" class="form-control" value="<?php echo e($cliente->limite_credito); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" rows="1" class="form-control"><?php echo e($cliente->observacoes); ?></textarea>
            </div>
        </div>
        <!-- Ativo -->
            <div class="col-md-2 form-check mt-2">
                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" <?php echo e($cliente->ativo ? 'checked' : ''); ?>>
                <label for="ativo" class="form-check-label">Ativo</label>
            </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-secondary">Voltar</a>
    </form>
</div>

<script>
    // Alerta automático desaparecendo
    const alerta = document.getElementById('alerta');
    if (alerta) {
        setTimeout(() => {
            alerta.style.display = 'none';
        }, 5000);
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/clientes/edit.blade.php ENDPATH**/ ?>