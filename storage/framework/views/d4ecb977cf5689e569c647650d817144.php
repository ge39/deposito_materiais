

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Nova Empresa / Filial</h2>

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

        <form action="<?php echo e(route('empresa.store')); ?>"
              method="POST"
              id="empresaForm">
            <?php echo csrf_field(); ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text"
                           name="nome"
                           class="form-control"
                           value="<?php echo e(old('nome')); ?>"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">CNPJ</label>
                    <input type="text"
                           name="cnpj"
                           id="cnpj"
                           class="form-control"
                           value="<?php echo e(old('cnpj')); ?>"
                           maxlength="18">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Inscrição Estadual</label>
                    <input type="text"
                           name="inscricao_estadual"
                           id="inscricao_estadual"
                           class="form-control"
                           value="<?php echo e(old('inscricao_estadual')); ?>"
                           maxlength="20">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Telefone</label>
                    <input type="text"
                           name="telefone"
                           class="form-control"
                           value="<?php echo e(old('telefone')); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="<?php echo e(old('email')); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Site</label>
                    <input type="text"
                           name="site"
                           class="form-control"
                           value="<?php echo e(old('site')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">CEP</label>
                    <input type="text"
                           name="cep"
                           id="cep"
                           class="form-control"
                           value="<?php echo e(old('cep')); ?>"
                           maxlength="9">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Endereço</label>
                    <input type="text"
                           name="endereco"
                           id="endereco"
                           class="form-control"
                           value="<?php echo e(old('endereco')); ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Número</label>
                    <input type="text"
                           name="numero"
                           class="form-control"
                           value="<?php echo e(old('numero')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Complemento</label>
                    <input type="text"
                           name="complemento"
                           class="form-control"
                           value="<?php echo e(old('complemento')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bairro</label>
                    <input type="text"
                           name="bairro"
                           id="bairro"
                           class="form-control"
                           value="<?php echo e(old('bairro')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cidade</label>
                    <input type="text"
                           name="cidade"
                           id="cidade"
                           class="form-control"
                           value="<?php echo e(old('cidade')); ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado (UF)</label>
                    <input type="text"
                           name="estado"
                           id="estado"
                           class="form-control text-uppercase"
                           value="<?php echo e(old('estado')); ?>"
                           maxlength="2">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Latitude</label>
                    <input type="text"
                           class="form-control"
                           value="Gerada automaticamente ao salvar"
                           disabled>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Longitude</label>
                    <input type="text"
                           class="form-control"
                           value="Gerada automaticamente ao salvar"
                           disabled>
                </div>

                <div class="col-12">
                    <input type="hidden" name="ativo" value="0">
                    <div class="form-check">
                        <input type="checkbox"
                               name="ativo"
                               id="ativo"
                               class="form-check-input"
                               value="1"
                               <?php echo e(old('ativo', 1) ? 'checked' : ''); ?>>
                        <label for="ativo" class="form-check-label">Ativo</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="<?php echo e(route('empresa.index')); ?>" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('cep').addEventListener('blur', function () {
    const cep = this.value.replace(/\D/g, '');

    if (cep.length !== 8) {
        return;
    }

    fetch(`/buscar-cep?cep=${encodeURIComponent(cep)}`)
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                return;
            }

            document.getElementById('endereco').value = data.logradouro || '';
            document.getElementById('bairro').value = data.bairro || '';
            document.getElementById('cidade').value = data.localidade || '';
            document.getElementById('estado').value = data.uf || '';
        });
});

document.getElementById('cnpj').addEventListener('input', function () {
    let valor = this.value.replace(/\D/g, '').slice(0, 14);

    valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
    valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');

    this.value = valor;
});

document.getElementById('inscricao_estadual').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
});
</script>

<script src="<?php echo e(asset('js/form-masks.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\empresa\create.blade.php ENDPATH**/ ?>