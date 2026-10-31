

<?php if (isset($component)) { $__componentOriginal198536b018288483144455c4b2ce2275 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal198536b018288483144455c4b2ce2275 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.section','data' => ['title' => 'Identificação do Veículo','description' => 'Informações principais para identificar, classificar e disponibilizar o veículo para a operação.','icon' => 'bi bi-truck-front']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Identificação do Veículo','description' => 'Informações principais para identificar, classificar e disponibilizar o veículo para a operação.','icon' => 'bi bi-truck-front']); ?>
    
    <div class="card border rounded-3 shadow-none mb-4">

        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between gap-3">

                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center text-primary">
                        <i class="bi bi-card-checklist"></i>
                    </span>

                    <div>
                        <h3 class="h6 fw-bold text-dark mb-0">
                            Dados Básicos
                        </h3>

                        <small class="text-muted">
                            Dados principais de identificação e vínculo do veículo.
                        </small>
                    </div>
                </div>

                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    Campos com * são obrigatórios
                </span>

            </div>
        </div>

        <div class="card-body p-4">

            <div class="row g-4">

                
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label
                        for="placa"
                        class="form-label fw-semibold"
                    >
                        Placa
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-car-front"></i>
                        </span>

                        <input
                            type="text"
                            name="placa"
                            id="placa"
                            class="form-control <?php $__errorArgs = ['placa'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('placa', $veiculo->placa ?? '')); ?>"
                            placeholder="Ex.: ABC1D23"
                            maxlength="20"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                            required
                        >

                        <?php $__errorArgs = ['placa'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label
                        for="marca"
                        class="form-label fw-semibold"
                    >
                        Fabricante / Marca
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-shield-check"></i>
                        </span>

                        <input
                            type="text"
                            name="marca"
                            id="marca"
                            class="form-control <?php $__errorArgs = ['marca'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('marca', $veiculo->marca ?? '')); ?>"
                            placeholder="Ex.: Mercedes-Benz"
                            maxlength="80"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                        >

                        <?php $__errorArgs = ['marca'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-4 col-lg-4 col-md-6">

                    <label
                        for="modelo"
                        class="form-label fw-semibold"
                    >
                        Modelo
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-truck"></i>
                        </span>

                        <input
                            type="text"
                            name="modelo"
                            id="modelo"
                            class="form-control <?php $__errorArgs = ['modelo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('modelo', $veiculo->modelo ?? '')); ?>"
                            placeholder="Ex.: Accelo 815"
                            maxlength="100"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                            required
                        >

                        <?php $__errorArgs = ['modelo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="ano_fabricacao"
                        class="form-label fw-semibold"
                    >
                        Ano de fabricação
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-calendar3"></i>
                        </span>

                        <input
                            type="number"
                            name="ano_fabricacao"
                            id="ano_fabricacao"
                            class="form-control <?php $__errorArgs = ['ano_fabricacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('ano_fabricacao', $veiculo->ano_fabricacao ?? '')); ?>"
                            placeholder="Ex.: 2024"
                            min="1900"
                            max="<?php echo e(now()->year + 1); ?>"
                        >

                        <?php $__errorArgs = ['ano_fabricacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="cor"
                        class="form-label fw-semibold"
                    >
                        Cor
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-palette"></i>
                        </span>

                        <input
                            type="text"
                            name="cor"
                            id="cor"
                            class="form-control <?php $__errorArgs = ['cor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            value="<?php echo e(old('cor', $veiculo->cor ?? '')); ?>"
                            placeholder="Ex.: Branco"
                            maxlength="50"
                            autocomplete="off"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();"
                        >

                        <?php $__errorArgs = ['cor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="tipo_frota"
                        class="form-label fw-semibold"
                    >
                        Tipo da Frota
                    </label>

                    <?php
                        $tipoFrotaSelecionado = old(
                            'tipo_frota',
                            $veiculo->tipo_frota ?? 'Frota'
                        );
                    ?>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-buildings"></i>
                        </span>

                        <select
                            name="tipo_frota"
                            id="tipo_frota"
                            class="form-select <?php $__errorArgs = ['tipo_frota'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                            <option
                                value="Frota"
                                <?php if($tipoFrotaSelecionado === 'Frota'): echo 'selected'; endif; ?>
                            >
                                Frota própria
                            </option>

                            <option
                                value="Agregado"
                                <?php if($tipoFrotaSelecionado === 'Agregado'): echo 'selected'; endif; ?>
                            >
                                Agregado
                            </option>

                            <option
                                value="Terceirizado"
                                <?php if($tipoFrotaSelecionado === 'Terceirizado'): echo 'selected'; endif; ?>
                            >
                                Terceirizado
                            </option>
                        </select>

                        <?php $__errorArgs = ['tipo_frota'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-3 col-lg-3 col-md-6">

                    <label
                        for="motorista_padrao_id"
                        class="form-label fw-semibold"
                    >
                        Motorista padrão
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-person"></i>
                        </span>

                        <select
                            name="motorista_padrao_id"
                            id="motorista_padrao_id"
                            class="form-select <?php $__errorArgs = ['motorista_padrao_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                            <option value="">
                                Sem motorista padrão
                            </option>

                            <?php if(isset($motoristas)): ?>
                                <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($motorista->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'motorista_padrao_id',
                                                $veiculo->motorista_padrao_id ?? ''
                                            ) === (string) $motorista->id
                                        ): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($motorista->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>

                        <?php $__errorArgs = ['motorista_padrao_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

    
    <div class="card border rounded-3 shadow-none mb-4">

        <div class="card-header bg-white border-bottom py-3">

            <div class="d-flex align-items-center gap-2">

                <span class="d-inline-flex align-items-center justify-content-center text-primary">
                    <i class="bi bi-diagram-3"></i>
                </span>

                <div>
                    <h3 class="h6 fw-bold text-dark mb-0">
                        Classificação
                    </h3>

                    <small class="text-muted">
                        Classificação operacional utilizada pela logística e pela expedição.
                    </small>
                </div>

            </div>

        </div>

        <div class="card-body p-4">

            <div class="row g-4">

                
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="tipo_veiculo_id"
                        class="form-label fw-semibold"
                    >
                        Tipo de Veículo
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-truck-front"></i>
                        </span>

                        <select
                            name="tipo_veiculo_id"
                            id="tipo_veiculo_id"
                            class="form-select <?php $__errorArgs = ['tipo_veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            <?php $__currentLoopData = $tiposVeiculo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($tipo->id); ?>"
                                    <?php if(
                                        (string) old(
                                            'tipo_veiculo_id',
                                            $veiculo->tipo_veiculo_id ?? ''
                                        ) === (string) $tipo->id
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($tipo->descricao); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <?php $__errorArgs = ['tipo_veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="classe_veiculo_id"
                        class="form-label fw-semibold"
                    >
                        Classe
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-ui-checks-grid"></i>
                        </span>

                        <select
                            name="classe_veiculo_id"
                            id="classe_veiculo_id"
                            class="form-select <?php $__errorArgs = ['classe_veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            <?php if(isset($classesVeiculo)): ?>
                                <?php $__currentLoopData = $classesVeiculo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($classe->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'classe_veiculo_id',
                                                $veiculo->classe_veiculo_id ?? ''
                                            ) === (string) $classe->id
                                        ): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($classe->descricao); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>

                        <?php $__errorArgs = ['classe_veiculo_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="tipo_carroceria_id"
                        class="form-label fw-semibold"
                    >
                        Tipo de Carroceria
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-box-seam"></i>
                        </span>

                        <select
                            name="tipo_carroceria_id"
                            id="tipo_carroceria_id"
                            class="form-select <?php $__errorArgs = ['tipo_carroceria_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            <?php if(isset($tiposCarroceria)): ?>
                                <?php $__currentLoopData = $tiposCarroceria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $carroceria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($carroceria->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'tipo_carroceria_id',
                                                $veiculo->tipo_carroceria_id ?? ''
                                            ) === (string) $carroceria->id
                                        ): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($carroceria->descricao); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </select>

                        <?php $__errorArgs = ['tipo_carroceria_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

                
                <div class="col-xl-3 col-lg-6 col-md-6">

                    <label
                        for="categoria_cnh"
                        class="form-label fw-semibold"
                    >
                        CNH exigida
                    </label>

                    <?php
                        $categoriaSelecionada = old(
                            'categoria_cnh',
                            $veiculo->categoria_cnh ?? ''
                        );
                    ?>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-person-vcard"></i>
                        </span>

                        <select
                            name="categoria_cnh"
                            id="categoria_cnh"
                            class="form-select <?php $__errorArgs = ['categoria_cnh'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        >
                            <option value="">
                                Não definida
                            </option>

                            <?php $__currentLoopData = ['A', 'B', 'C', 'D', 'E', 'AB', 'AC', 'AD', 'AE']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($categoria); ?>"
                                    <?php if($categoriaSelecionada === $categoria): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($categoria); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                        <?php $__errorArgs = ['categoria_cnh'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

    
    <div class="row g-4">

        
        <div class="col-xl-5 col-lg-5">

            <div class="card border rounded-3 shadow-none h-100">

                <div class="card-header bg-white border-bottom py-3">

                    <div class="d-flex align-items-center gap-2">

                        <span class="d-inline-flex align-items-center justify-content-center text-primary">
                            <i class="bi bi-clock-history"></i>
                        </span>

                        <div>
                            <h3 class="h6 fw-bold text-dark mb-0">
                                Disponibilidade
                            </h3>

                            <small class="text-muted">
                                Situação operacional atual do veículo.
                            </small>
                        </div>

                    </div>

                </div>

                <div class="card-body p-4">

                    <label
                        for="disponibilidade"
                        class="form-label fw-semibold"
                    >
                        Disponibilidade
                        <span class="text-danger">*</span>
                    </label>

                    <?php
                        $disponibilidadeSelecionada = old(
                            'disponibilidade',
                            $veiculo->disponibilidade ?? ''
                        );
                    ?>

                    <div class="input-group">

                        <span class="input-group-text bg-light text-primary">
                            <i class="bi bi-activity"></i>
                        </span>

                        <select
                            name="disponibilidade"
                            id="disponibilidade"
                            class="form-select <?php $__errorArgs = ['disponibilidade'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            required
                        >
                            <option value="">
                                Selecione...
                            </option>

                            <option
                                value="Disponível"
                                <?php if($disponibilidadeSelecionada === 'Disponível'): echo 'selected'; endif; ?>
                            >
                                Disponível
                            </option>

                            <option
                                value="Reservado"
                                <?php if($disponibilidadeSelecionada === 'Reservado'): echo 'selected'; endif; ?>
                            >
                                Reservado
                            </option>

                            <option
                                value="Carregando"
                                <?php if($disponibilidadeSelecionada === 'Carregando'): echo 'selected'; endif; ?>
                            >
                                Carregando
                            </option>

                            <option
                                value="Em rota"
                                <?php if($disponibilidadeSelecionada === 'Em rota'): echo 'selected'; endif; ?>
                            >
                                Em rota
                            </option>

                            <option
                                value="Manutencao"
                                <?php if($disponibilidadeSelecionada === 'Manutencao'): echo 'selected'; endif; ?>
                            >
                                Manutenção
                            </option>

                            <option
                                value="Indisponível"
                                <?php if($disponibilidadeSelecionada === 'Indisponível'): echo 'selected'; endif; ?>
                            >
                                Indisponível
                            </option>
                        </select>

                        <?php $__errorArgs = ['disponibilidade'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback">
                                <?php echo e($message); ?>

                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    </div>

                    <small class="d-block text-muted mt-2">
                        Esse status será utilizado pela expedição e pelos romaneios.
                    </small>

                </div>

            </div>

        </div>

        
        <div class="col-xl-7 col-lg-7">

            <div class="card border rounded-3 shadow-none h-100">

                <div class="card-header bg-white border-bottom py-3">

                    <div class="d-flex align-items-center gap-2">

                        <span class="d-inline-flex align-items-center justify-content-center text-primary">
                            <i class="bi bi-gear"></i>
                        </span>

                        <div>
                            <h3 class="h6 fw-bold text-dark mb-0">
                                Informações Técnicas
                            </h3>

                            <small class="text-muted">
                                Identificadores oficiais e técnicos do veículo.
                            </small>
                        </div>

                    </div>

                </div>

                <div class="card-body p-4">

                    <div class="row g-4">

                        
                        <div class="col-md-6">

                            <label
                                for="chassi"
                                class="form-label fw-semibold"
                            >
                                Chassi
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-upc-scan"></i>
                                </span>

                                <input
                                    type="text"
                                    name="chassi"
                                    id="chassi"
                                    class="form-control <?php $__errorArgs = ['chassi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('chassi', $veiculo->chassi ?? '')); ?>"
                                    placeholder="Número do chassi"
                                    maxlength="80"
                                    autocomplete="off"
                                >

                                <?php $__errorArgs = ['chassi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            </div>

                        </div>

                        
                        <div class="col-md-6">

                            <label
                                for="renavam"
                                class="form-label fw-semibold"
                            >
                                Renavam
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-light text-primary">
                                    <i class="bi bi-file-earmark-text"></i>
                                </span>

                                <input
                                    type="text"
                                    name="renavam"
                                    id="renavam"
                                    class="form-control <?php $__errorArgs = ['renavam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('renavam', $veiculo->renavam ?? '')); ?>"
                                    placeholder="Número do Renavam"
                                    maxlength="80"
                                    autocomplete="off"
                                >

                                <?php $__errorArgs = ['renavam'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback">
                                        <?php echo e($message); ?>

                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal198536b018288483144455c4b2ce2275)): ?>
<?php $attributes = $__attributesOriginal198536b018288483144455c4b2ce2275; ?>
<?php unset($__attributesOriginal198536b018288483144455c4b2ce2275); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal198536b018288483144455c4b2ce2275)): ?>
<?php $component = $__componentOriginal198536b018288483144455c4b2ce2275; ?>
<?php unset($__componentOriginal198536b018288483144455c4b2ce2275); ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipo = document.getElementById('tipo_veiculo_id');
        const classe = document.getElementById('classe_veiculo_id');
        const carroceria = document.getElementById('tipo_carroceria_id');

        if (!tipo || !classe || !carroceria) {
            return;
        }

        const classeSelecionada = <?php echo json_encode(
            old(
                'classe_veiculo_id', $veiculo->classe_veiculo_id ?? ''
            ), 512) ?>;

        const carroceriaSelecionada = <?php echo json_encode(
            old(
                'tipo_carroceria_id', $veiculo->tipo_carroceria_id ?? ''
            ), 512) ?>;

        function definirCarregando(select, texto = 'Carregando...') {
            select.innerHTML = '';

            const option = document.createElement('option');
            option.value = '';
            option.textContent = texto;

            select.appendChild(option);
            select.disabled = true;
        }

        function definirPlaceholder(select, texto = 'Selecione...') {
            select.innerHTML = '';

            const option = document.createElement('option');
            option.value = '';
            option.textContent = texto;

            select.appendChild(option);
            select.disabled = false;
        }

        function preencherSelect(select, dados, selecionado = '') {
            definirPlaceholder(select);

            dados.forEach(function (item) {
                const option = document.createElement('option');

                option.value = item.id;
                option.textContent = item.descricao;
                option.selected =
                    String(item.id) === String(selecionado);

                select.appendChild(option);
            });
        }

        async function carregarClasses(tipoId, classeId = '') {
            definirCarregando(classe);
            definirPlaceholder(carroceria);

            if (!tipoId) {
                definirPlaceholder(classe);
                return;
            }

            try {
                const response = await fetch(
                    `/frota/classes/${encodeURIComponent(tipoId)}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `Erro HTTP ${response.status}`
                    );
                }

                const dados = await response.json();

                preencherSelect(classe, dados, classeId);

                if (classeId) {
                    await carregarCarrocerias(
                        classeId,
                        carroceriaSelecionada
                    );
                }
            } catch (error) {
                console.error(
                    'Erro ao carregar classes do veículo:',
                    error
                );

                definirPlaceholder(
                    classe,
                    'Não foi possível carregar'
                );
            }
        }

        async function carregarCarrocerias(
            classeId,
            carroceriaId = ''
        ) {
            definirCarregando(carroceria);

            if (!classeId) {
                definirPlaceholder(carroceria);
                return;
            }

            try {
                const response = await fetch(
                    `/frota/carrocerias/${encodeURIComponent(classeId)}`,
                    {
                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        `Erro HTTP ${response.status}`
                    );
                }

                const dados = await response.json();

                preencherSelect(
                    carroceria,
                    dados,
                    carroceriaId
                );
            } catch (error) {
                console.error(
                    'Erro ao carregar carrocerias do veículo:',
                    error
                );

                definirPlaceholder(
                    carroceria,
                    'Não foi possível carregar'
                );
            }
        }

        tipo.addEventListener('change', function () {
            carregarClasses(this.value);
        });

        classe.addEventListener('change', function () {
            carregarCarrocerias(this.value);
        });

        if (tipo.value) {
            carregarClasses(
                tipo.value,
                classeSelecionada
            );
        }
    });
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/veiculos/partials/_identificacao.blade.php ENDPATH**/ ?>