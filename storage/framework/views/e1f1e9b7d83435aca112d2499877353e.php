

<?php $__env->startSection('content'); ?>

    <?php
        $etapas = [
            'Identificação',
            'Classificação',
            'Capacidades',
            'Recursos',
            'Status',
        ];

        $tipos = [
            'Caminhão' => 'Caminhão',
            'Carreta' => 'Carreta',
            'Utilitário' => 'Utilitário',
            'Moto' => 'Moto',
        ];

        $disponibilidades = [
            'Disponível' => 'Disponível',
            'Reservado' => 'Reservado',
            'Carregando' => 'Carregando',
            'Em rota' => 'Em rota',
            'Manutencao' => 'Manutenção',
            'Indisponível' => 'Indisponível',
        ];
    ?>

    <?php if (isset($component)) { $__componentOriginal6931f8776b93d1225caf3e27f5ad9902 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6931f8776b93d1225caf3e27f5ad9902 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.page','data' => ['action' => route('veiculos.store'),'method' => 'POST']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('veiculos.store')),'method' => 'POST']); ?>

         <?php $__env->slot('header', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginalb72c48229ce602e8b85243cb0cdf5ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb72c48229ce602e8b85243cb0cdf5ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.header','data' => ['title' => 'Cadastrar Veículo','subtitle' => 'Preencha os dados operacionais e técnicos do veículo.','icon' => 'bi bi-truck','backUrl' => route('veiculos.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Cadastrar Veículo','subtitle' => 'Preencha os dados operacionais e técnicos do veículo.','icon' => 'bi bi-truck','back-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('veiculos.index'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb72c48229ce602e8b85243cb0cdf5ade)): ?>
<?php $attributes = $__attributesOriginalb72c48229ce602e8b85243cb0cdf5ade; ?>
<?php unset($__attributesOriginalb72c48229ce602e8b85243cb0cdf5ade); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb72c48229ce602e8b85243cb0cdf5ade)): ?>
<?php $component = $__componentOriginalb72c48229ce602e8b85243cb0cdf5ade; ?>
<?php unset($__componentOriginalb72c48229ce602e8b85243cb0cdf5ade); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('wizard', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginalf3ff24ff8df7ab55832998347b43adb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf3ff24ff8df7ab55832998347b43adb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.wizard','data' => ['steps' => $etapas,'current' => 1]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.wizard'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['steps' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($etapas),'current' => 1]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf3ff24ff8df7ab55832998347b43adb5)): ?>
<?php $attributes = $__attributesOriginalf3ff24ff8df7ab55832998347b43adb5; ?>
<?php unset($__attributesOriginalf3ff24ff8df7ab55832998347b43adb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf3ff24ff8df7ab55832998347b43adb5)): ?>
<?php $component = $__componentOriginalf3ff24ff8df7ab55832998347b43adb5; ?>
<?php unset($__componentOriginalf3ff24ff8df7ab55832998347b43adb5); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

        <?php if (isset($component)) { $__componentOriginal198536b018288483144455c4b2ce2275 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal198536b018288483144455c4b2ce2275 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.section','data' => ['title' => 'Dados Básicos','description' => 'Informações principais de identificação do veículo.','icon' => 'bi bi-card-text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dados Básicos','description' => 'Informações principais de identificação do veículo.','icon' => 'bi bi-card-text']); ?>
            <div class="row g-3">

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.input','data' => ['name' => 'placa','label' => 'Placa','placeholder' => 'ABC1D23','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'placa','label' => 'Placa','placeholder' => 'ABC1D23','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $attributes = $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $component = $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.input','data' => ['name' => 'marca','label' => 'Marca','placeholder' => 'Ex.: Mercedes-Benz','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'marca','label' => 'Marca','placeholder' => 'Ex.: Mercedes-Benz','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $attributes = $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $component = $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.input','data' => ['name' => 'modelo','label' => 'Modelo','placeholder' => 'Ex.: Atego 1719','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'modelo','label' => 'Modelo','placeholder' => 'Ex.: Atego 1719','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $attributes = $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $component = $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
                </div>

                <div class="col-xl-3 col-lg-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.input','data' => ['name' => 'ano_fabricacao','label' => 'Ano de fabricação','type' => 'number','placeholder' => '2024']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'ano_fabricacao','label' => 'Ano de fabricação','type' => 'number','placeholder' => '2024']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $attributes = $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $component = $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
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

        <?php if (isset($component)) { $__componentOriginal198536b018288483144455c4b2ce2275 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal198536b018288483144455c4b2ce2275 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.section','data' => ['title' => 'Classificação Operacional','description' => 'Classificação e condição atual do veículo.','icon' => 'bi bi-diagram-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Classificação Operacional','description' => 'Classificação e condição atual do veículo.','icon' => 'bi bi-diagram-3']); ?>
            <div class="row g-3">

                <div class="col-xl-4 col-lg-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginal1819508a95c1f89a8a219cc37a1d40e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.select','data' => ['name' => 'tipo','label' => 'Tipo do veículo','options' => $tipos,'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'tipo','label' => 'Tipo do veículo','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tipos),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5)): ?>
<?php $attributes = $__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5; ?>
<?php unset($__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1819508a95c1f89a8a219cc37a1d40e5)): ?>
<?php $component = $__componentOriginal1819508a95c1f89a8a219cc37a1d40e5; ?>
<?php unset($__componentOriginal1819508a95c1f89a8a219cc37a1d40e5); ?>
<?php endif; ?>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginal1819508a95c1f89a8a219cc37a1d40e5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.select','data' => ['name' => 'disponibilidade','label' => 'Disponibilidade','options' => $disponibilidades,'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'disponibilidade','label' => 'Disponibilidade','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($disponibilidades),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5)): ?>
<?php $attributes = $__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5; ?>
<?php unset($__attributesOriginal1819508a95c1f89a8a219cc37a1d40e5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1819508a95c1f89a8a219cc37a1d40e5)): ?>
<?php $component = $__componentOriginal1819508a95c1f89a8a219cc37a1d40e5; ?>
<?php unset($__componentOriginal1819508a95c1f89a8a219cc37a1d40e5); ?>
<?php endif; ?>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.input','data' => ['name' => 'categoria_cnh','label' => 'Categoria mínima de CNH','placeholder' => 'Ex.: D']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'categoria_cnh','label' => 'Categoria mínima de CNH','placeholder' => 'Ex.: D']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $attributes = $__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__attributesOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a)): ?>
<?php $component = $__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a; ?>
<?php unset($__componentOriginal2d7b1de9731e39d6fa9208d33d1e614a); ?>
<?php endif; ?>
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

        <?php if (isset($component)) { $__componentOriginal198536b018288483144455c4b2ce2275 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal198536b018288483144455c4b2ce2275 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.section','data' => ['title' => 'Recursos','description' => 'Recursos e características operacionais.','icon' => 'bi bi-tools']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recursos','description' => 'Recursos e características operacionais.','icon' => 'bi bi-tools']); ?>
            <div class="row g-3">

                <div class="col-xl-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.checkbox','data' => ['name' => 'possui_munck','label' => 'Possui Munck','description' => 'Veículo equipado para içamento de cargas.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'possui_munck','label' => 'Possui Munck','description' => 'Veículo equipado para içamento de cargas.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c)): ?>
<?php $attributes = $__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c; ?>
<?php unset($__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c)): ?>
<?php $component = $__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c; ?>
<?php unset($__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c); ?>
<?php endif; ?>
                </div>

                <div class="col-xl-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.checkbox','data' => ['name' => 'possui_rastreador','label' => 'Possui rastreador','description' => 'Permite acompanhamento da localização.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'possui_rastreador','label' => 'Possui rastreador','description' => 'Permite acompanhamento da localização.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c)): ?>
<?php $attributes = $__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c; ?>
<?php unset($__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c)): ?>
<?php $component = $__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c; ?>
<?php unset($__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c); ?>
<?php endif; ?>
                </div>

                <div class="col-xl-4 col-md-6">
                    <?php if (isset($component)) { $__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.checkbox','data' => ['name' => 'possui_carroceria_aberta','label' => 'Carroceria aberta','description' => 'Adequado para materiais de grande volume.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.checkbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'possui_carroceria_aberta','label' => 'Carroceria aberta','description' => 'Adequado para materiais de grande volume.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c)): ?>
<?php $attributes = $__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c; ?>
<?php unset($__attributesOriginalbb764fc198340f2f18bb88ecbbf91b7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c)): ?>
<?php $component = $__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c; ?>
<?php unset($__componentOriginalbb764fc198340f2f18bb88ecbbf91b7c); ?>
<?php endif; ?>
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

        <?php if (isset($component)) { $__componentOriginal198536b018288483144455c4b2ce2275 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal198536b018288483144455c4b2ce2275 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.section','data' => ['title' => 'Observações','description' => 'Informações adicionais relevantes para a operação.','icon' => 'bi bi-chat-left-text']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Observações','description' => 'Informações adicionais relevantes para a operação.','icon' => 'bi bi-chat-left-text']); ?>
            <div class="row g-3">

                <div class="col-12">
                    <?php if (isset($component)) { $__componentOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.form.textarea','data' => ['name' => 'observacao','label' => 'Observação','placeholder' => 'Informe restrições, particularidades ou recomendações...','rows' => 4]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.form.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'observacao','label' => 'Observação','placeholder' => 'Informe restrições, particularidades ou recomendações...','rows' => 4]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0)): ?>
<?php $attributes = $__attributesOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0; ?>
<?php unset($__attributesOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0)): ?>
<?php $component = $__componentOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0; ?>
<?php unset($__componentOriginalacc0efb1e31063abc3b2ab2ae1e8f1d0); ?>
<?php endif; ?>
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

         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal4050d27c437d0d38b06fd729840681e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4050d27c437d0d38b06fd729840681e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.actions','data' => ['cancelUrl' => route('veiculos.index'),'cancelLabel' => 'Cancelar','submitLabel' => 'Salvar veículo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cancel-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('veiculos.index')),'cancel-label' => 'Cancelar','submit-label' => 'Salvar veículo']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4050d27c437d0d38b06fd729840681e3)): ?>
<?php $attributes = $__attributesOriginal4050d27c437d0d38b06fd729840681e3; ?>
<?php unset($__attributesOriginal4050d27c437d0d38b06fd729840681e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4050d27c437d0d38b06fd729840681e3)): ?>
<?php $component = $__componentOriginal4050d27c437d0d38b06fd729840681e3; ?>
<?php unset($__componentOriginal4050d27c437d0d38b06fd729840681e3); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6931f8776b93d1225caf3e27f5ad9902)): ?>
<?php $attributes = $__attributesOriginal6931f8776b93d1225caf3e27f5ad9902; ?>
<?php unset($__attributesOriginal6931f8776b93d1225caf3e27f5ad9902); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6931f8776b93d1225caf3e27f5ad9902)): ?>
<?php $component = $__componentOriginal6931f8776b93d1225caf3e27f5ad9902; ?>
<?php unset($__componentOriginal6931f8776b93d1225caf3e27f5ad9902); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\exemplos\cadastro.blade.php ENDPATH**/ ?>