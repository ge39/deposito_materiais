

<?php $__env->startSection('title', 'Editar Veículo'); ?>

<?php $__env->startSection('content'); ?>

    <?php if (isset($component)) { $__componentOriginal6931f8776b93d1225caf3e27f5ad9902 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6931f8776b93d1225caf3e27f5ad9902 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.page','data' => ['action' => route('veiculos.update', $veiculo),'method' => 'PUT','autocomplete' => 'off']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('veiculos.update', $veiculo)),'method' => 'PUT','autocomplete' => 'off']); ?>

        
         <?php $__env->slot('header', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginalb72c48229ce602e8b85243cb0cdf5ade = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb72c48229ce602e8b85243cb0cdf5ade = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.header','data' => ['title' => 'Editar Veículo','subtitle' => 'Atualize os dados operacionais do veículo para manter a frota consistente.','icon' => 'bi bi-pencil-square','backUrl' => route('veiculos.index'),'backLabel' => 'Voltar para veículos']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Editar Veículo','subtitle' => 'Atualize os dados operacionais do veículo para manter a frota consistente.','icon' => 'bi bi-pencil-square','back-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('veiculos.index')),'back-label' => 'Voltar para veículos']); ?>
                 <?php $__env->slot('actions', null, []); ?> 

                    <a
                        href="<?php echo e(route('veiculos.show', $veiculo)); ?>"
                        class="erp-btn erp-btn-outline-primary"
                    >
                        <i class="bi bi-eye"></i>
                        Ver detalhes
                    </a>

                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                        Fase 3 · Logística
                    </span>

                 <?php $__env->endSlot(); ?>
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

        
        <?php echo $__env->make('veiculos.partials._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
         <?php $__env->slot('actions', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal4050d27c437d0d38b06fd729840681e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4050d27c437d0d38b06fd729840681e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.erp.cadastro.actions','data' => ['cancelUrl' => route('veiculos.index'),'cancelLabel' => 'Voltar','showSubmit' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('erp.cadastro.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cancel-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('veiculos.index')),'cancel-label' => 'Voltar','show-submit' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
                 <?php $__env->slot('right', null, []); ?> 

                    <button
                        type="button"
                        class="erp-btn erp-btn-outline"
                        id="btnWizardAnterior"
                    >
                        <i class="bi bi-chevron-left"></i>
                        Anterior
                    </button>

                    <button
                        type="button"
                        class="erp-btn erp-btn-outline-primary"
                        id="btnWizardProximo"
                    >
                        Próximo
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <button
                        type="submit"
                        class="erp-btn erp-btn-primary"
                    >
                        <i class="bi bi-check-circle"></i>
                        Salvar Alterações
                    </button>

                 <?php $__env->endSlot(); ?>
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/veiculos/edit.blade.php ENDPATH**/ ?>