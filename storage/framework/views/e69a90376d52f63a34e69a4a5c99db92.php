<?php $__env->startSection('content'); ?>
<?php echo $__env->make('cadastros-auxiliares._styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid px-3 px-xl-4 py-4 cadastro-auxiliar-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">
                <i class="bi <?php echo e($icone); ?> me-2 text-primary"></i><?php echo e($titulo); ?>

            </h1>
            <p class="page-subtitle mb-0">Atualize os dados do registro #<?php echo e($registro->id); ?>.</p>
        </div>

        <a href="<?php echo e(route($routePrefix . '.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1">
                <i class="bi bi-exclamation-circle me-1"></i>Revise os campos destacados.
            </div>
            <ul class="mb-0 ps-3">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom px-3 px-lg-4 py-3">
            <h2 class="h6 fw-bold mb-1">Dados da <?php echo e(mb_strtolower($singular)); ?></h2>
            <p class="text-muted small mb-0">Os campos com asterisco são obrigatórios.</p>
        </div>

        <div class="card-body p-3 p-lg-4">
            <form action="<?php echo e(route($routePrefix . '.update', $registro)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="row g-3">
                    <div class="<?php echo e($possuiSigla ? 'col-md-8' : 'col-12'); ?>">
                        <label for="nome" class="form-label">
                            Nome <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="nome"
                            name="nome"
                            value="<?php echo e(old('nome', $registro->nome)); ?>"
                            required>
                        <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <?php if($possuiSigla): ?>
                        <div class="col-md-4">
                            <label for="sigla" class="form-label">
                                Sigla <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control text-uppercase <?php $__errorArgs = ['sigla'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="sigla"
                                name="sigla"
                                maxlength="10"
                                value="<?php echo e(old('sigla', $registro->sigla)); ?>"
                                required>
                            <?php $__errorArgs = ['sigla'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endif; ?>

                    <?php if($possuiDescricao): ?>
                        <div class="col-12">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea
                                class="form-control <?php $__errorArgs = ['descricao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="descricao"
                                name="descricao"
                                rows="4"><?php echo e(old('descricao', $registro->descricao)); ?></textarea>
                            <?php $__errorArgs = ['descricao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endif; ?>

                    <div class="col-12">
                        <input type="hidden" name="ativo" value="0">
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="ativo"
                                name="ativo"
                                value="1"
                                <?php echo e(old('ativo', (string) $registro->ativo) === '1' ? 'checked' : ''); ?>>
                            <label class="form-check-label fw-semibold" for="ativo">Registro ativo</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end flex-wrap gap-2 border-top mt-4 pt-3">
                    <a href="<?php echo e(route($routePrefix . '.index')); ?>" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i>Salvar alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\cadastros-auxiliares\edit.blade.php ENDPATH**/ ?>