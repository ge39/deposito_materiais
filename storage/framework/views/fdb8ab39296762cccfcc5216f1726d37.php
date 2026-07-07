

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <form method="POST" action="<?php echo e(route('localizacoes-estoque.store')); ?>">
        <?php echo csrf_field(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="bi bi-geo-alt me-2"></i>Nova Localização de Estoque
                </h3>
                <small class="text-muted">
                    Cadastre uma posição física do depósito para coleta, romaneio e inventário.
                </small>
            </div>

            <div class="d-flex gap-2">
                <a href="<?php echo e(route('localizacoes-estoque.index')); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                </a>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save me-1"></i> Salvar Localização
                </button>
            </div>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <strong>Verifique os campos abaixo:</strong>
                <ul class="mb-0 mt-2">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($erro); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md">
                <div class="card border-secondary shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase text-muted">Setor</small>
                            <h6 class="fw-bold mb-1">Não definido</h6>
                            <h4 class="fw-bold mb-0">-</h4>
                        </div>
                        <i class="bi bi-geo-alt fs-1 text-secondary"></i>
                    </div>
                </div>
            </div>

            <div class="col-md">
                <div class="card border-warning shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase text-muted">Ordem</small>
                            <h6 class="fw-bold mb-1">Coleta</h6>
                            <h4 class="fw-bold mb-0">999999</h4>
                        </div>
                        <i class="bi bi-list-ol fs-1 text-warning"></i>
                    </div>
                </div>
            </div>

            <div class="col-md">
                <div class="card border-primary shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase text-muted">Status</small>
                            <h6 class="fw-bold mb-1">Ativo</h6>
                            <h4 class="fw-bold mb-0">-</h4>
                        </div>
                        <i class="bi bi-check-circle fs-1 text-primary"></i>
                    </div>
                </div>
            </div>

            <div class="col-md">
                <div class="card border-info shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase text-muted">Tipo</small>
                            <h6 class="fw-bold mb-1">-</h6>
                            <h4 class="fw-bold mb-0">-</h4>
                        </div>
                        <i class="bi bi-building fs-1 text-info"></i>
                    </div>
                </div>
            </div>

            <div class="col-md">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase text-muted">Uso</small>
                            <h6 class="fw-bold mb-1">Coleta / Romaneio</h6>
                            <h4 class="fw-bold mb-0">-</h4>
                        </div>
                        <i class="bi bi-box-seam fs-1 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white fw-bold">
                <i class="bi bi-funnel me-1"></i> Dados da Localização
            </div>

            <div class="card-body p-4">

                <h5 class="text-primary fw-bold border-bottom pb-2 mb-4">
                    Identificação
                </h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Código da Localização <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="codigo"
                            class="form-control <?php $__errorArgs = ['codigo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('codigo')); ?>"
                            placeholder="Ex: PAT01-R02-D"
                            required>
                        <?php $__errorArgs = ['codigo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Código curto usado no romaneio.</small>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Descrição da Localização</label>
                        <input
                            type="text"
                            name="descricao"
                            class="form-control <?php $__errorArgs = ['descricao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('descricao')); ?>"
                            placeholder="Ex: Pátio 1 - Rua 2 - Lado Direito">
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

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Tipo de Localização <span class="text-danger">*</span>
                        </label>
                        <select
                            name="tipo_localizacao"
                            class="form-select <?php $__errorArgs = ['tipo_localizacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required>
                            <option value="">Selecione o tipo...</option>
                            <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tipo); ?>" <?php echo e(old('tipo_localizacao') == $tipo ? 'selected' : ''); ?>>
                                    <?php echo e($tipo); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['tipo_localizacao'];
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
                </div>

                <h5 class="text-primary fw-bold border-bottom pb-2 mb-4">
                    Estrutura Física
                </h5>

                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Setor</label>
                        <input type="text" name="setor" class="form-control" value="<?php echo e(old('setor')); ?>" placeholder="Ex: Pátio 1">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Rua</label>
                        <input type="text" name="rua" class="form-control" value="<?php echo e(old('rua')); ?>" placeholder="Ex: 02">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Lado</label>
                        <input type="text" name="lado" class="form-control" value="<?php echo e(old('lado')); ?>" placeholder="Ex: Direito">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Módulo</label>
                        <input type="text" name="modulo" class="form-control" value="<?php echo e(old('modulo')); ?>" placeholder="Ex: M03">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Prateleira</label>
                        <input type="text" name="prateleira" class="form-control" value="<?php echo e(old('prateleira')); ?>" placeholder="Ex: P01">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Nível</label>
                        <input type="text" name="nivel" class="form-control" value="<?php echo e(old('nivel')); ?>" placeholder="Ex: N1">
                    </div>

                    <div class="col-md-12">
                        <small class="text-muted">
                            Preencha os detalhes da estrutura física para facilitar a localização no depósito.
                        </small>
                    </div>
                </div>

                <h5 class="text-primary fw-bold border-bottom pb-2 mb-4">
                    Ordem de Coleta e Regras
                </h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Ordem de Coleta <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="ordem_coleta"
                            class="form-control <?php $__errorArgs = ['ordem_coleta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('ordem_coleta', 999999)); ?>"
                            min="1"
                            required>
                        <?php $__errorArgs = ['ordem_coleta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <small class="text-muted">Define a Ordem no Percurso da localização no romaneio.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Status da Localização <span class="text-danger">*</span>
                        </label>
                        <select name="ativo" class="form-select">
                            <option value="1" <?php echo e(old('ativo', 1) == 1 ? 'selected' : ''); ?>>Ativo</option>
                            <option value="0" <?php echo e(old('ativo') === '0' ? 'selected' : ''); ?>>Inativo</option>
                        </select>
                        <small class="text-muted">Inativas não aparecem no cadastro de produto.</small>
                    </div>
                </div>

            </div>
        </div>

        <div class="card shadow-sm bg-light">
            <div class="card-body d-flex justify-content-between">
                <a href="<?php echo e(route('localizacoes-estoque.index')); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </a>

                <button type="submit" class="btn btn-success px-4">
                    <i class="bi bi-save me-1"></i> Salvar Localização
                </button>
            </div>
        </div>

    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/localizacoes-estoque/create.blade.php ENDPATH**/ ?>