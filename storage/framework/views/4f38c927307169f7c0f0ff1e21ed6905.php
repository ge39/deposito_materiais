

<?php $__env->startSection('content'); ?>

<?php
    $primeiraRotaDisponivel = static function (array $rotas): ?string {
        foreach ($rotas as $rota) {
            if (\Illuminate\Support\Facades\Route::has($rota)) {
                return $rota;
            }
        }

        return null;
    };

    $rotaCategoria = $primeiraRotaDisponivel([
        'categorias.index',
    ]);

    $rotaFornecedor = $primeiraRotaDisponivel([
        'fornecedores.create',
        'fornecedores.index',
    ]);

    $rotaMarca = $primeiraRotaDisponivel([
        'marcas.index',
    ]);

    $rotaUnidadeMedida = $primeiraRotaDisponivel([
        'unidades-medida.index',
    ]);
?>

<style>
    body > div.container.mt-4 {
        max-width: 100% !important;
        width: 100% !important;
        padding-right: 0 !important;
        padding-left: 0 !important;
    }

    .produto-create-page {
        --erp-primary: #0d6efd;
        --erp-border: #dee2e6;
        --erp-surface: #ffffff;
        --erp-muted: #6c757d;

        max-width: 1600px;
        margin: 0 auto;
    }

    .produto-page-header {
        border-bottom: 1px solid var(--erp-border);
        background: var(--erp-surface);
    }

    .produto-page-title {
        color: #212529;
        font-size: 1.45rem;
        font-weight: 700;
        letter-spacing: -.02em;
    }

    .produto-page-subtitle {
        color: var(--erp-muted);
        font-size: .9rem;
    }

    .produto-section {
        padding: 1.25rem;
        border: 1px solid var(--erp-border);
        border-radius: .75rem;
        background: var(--erp-surface);
    }

    .produto-section-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        margin-bottom: 1rem;
        padding-bottom: .65rem;
        border-bottom: 1px solid var(--erp-border);
        color: #334155;
        font-size: .88rem;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    .produto-section-title .section-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.7rem;
        height: 1.7rem;
        border-radius: 50%;
        background: #e7f1ff;
        color: var(--erp-primary);
        font-size: .78rem;
    }

    .produto-create-page .form-label {
        margin-bottom: .4rem;
        color: #334155;
        font-size: .86rem;
        font-weight: 600;
    }

    .produto-create-page .form-control,
    .produto-create-page .form-select,
    .produto-create-page .input-group-text {
        min-height: 42px;
        border-color: #cfd6de;
    }

    .produto-create-page .form-control:focus,
    .produto-create-page .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .12);
    }

    .cadastro-auxiliar-link {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        color: var(--erp-primary);
        font-size: .78rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .cadastro-auxiliar-link:hover {
        color: #0a58ca;
        text-decoration: underline;
    }

    .price-card {
        overflow: hidden;
        border-width: 1px;
        border-radius: .65rem;
    }

    .produto-image-preview {
        width: 112px;
        height: 112px;
        object-fit: contain;
        padding: .4rem;
        border: 1px solid var(--erp-border);
        border-radius: .65rem;
        background: #f8f9fa;
    }

    .produto-actions {
        position: sticky;
        bottom: 0;
        z-index: 5;
        margin: 0 -1.25rem -1.25rem;
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--erp-border);
        border-radius: 0 0 .75rem .75rem;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(6px);
    }

    @media (max-width: 767.98px) {
        .produto-section {
            padding: 1rem;
        }

        .produto-actions {
            margin-right: -1rem;
            margin-bottom: -1rem;
            margin-left: -1rem;
            padding: .9rem 1rem;
        }
    }
</style>

<div class="container-fluid px-3 px-xl-4 py-4 produto-create-page text-dark">

    <div class="card border-0 shadow-sm bg-white text-dark">

        
        <div
            class="card-header produto-page-header
                   d-flex justify-content-between
                   align-items-center flex-wrap gap-3
                   px-3 px-lg-4 py-3">

            <div>
                <h1 class="produto-page-title mb-1">
                    <i class="bi bi-box-seam me-2 text-primary"></i>
                    Cadastro de Produto
                </h1>

                <p class="produto-page-subtitle mb-0">
                    Informações comerciais, custos, estoque,
                    logística e dados fiscais.
                </p>
            </div>

            <a
                href="<?php echo e(route('produtos.index')); ?>"
                class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>

        </div>

        <div class="card-body p-3 p-lg-4">

            
            <?php if(session('error')): ?>
                <div
                    class="alert alert-danger
                           d-flex align-items-center"
                    role="alert">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    <div>
                        <?php echo e(session('error')); ?>

                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($errors->any()): ?>
                <div class="alert alert-danger" role="alert">

                    <div class="fw-semibold mb-1">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Revise os campos destacados.
                    </div>

                    <ul class="mb-0 ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($erro); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>

                </div>
            <?php endif; ?>

            <form
                action="<?php echo e(route('produtos.store')); ?>"
                method="POST"
                enctype="multipart/form-data">

                <?php echo csrf_field(); ?>

                
                <div class="produto-section mb-4">

                    <h2 class="produto-section-title">
                        <span class="section-number">1</span>
                        Informações básicas
                    </h2>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label for="nome" class="form-label">
                                Nome do Produto
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                class="form-control
                                       <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="nome"
                                name="nome"
                                value="<?php echo e(old('nome')); ?>"
                                required>

                            <?php $__errorArgs = ['nome'];
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

                        <div class="col-md-3">

                            <label for="sku" class="form-label">
                                SKU
                            </label>

                            <input
                                type="text"
                                class="form-control
                                       <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="sku"
                                name="sku"
                                value="<?php echo e(old('sku')); ?>">

                            <?php $__errorArgs = ['sku'];
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

                        <div class="col-md-3">

                            <label
                                for="codigo_barras"
                                class="form-label">

                                Código de Barras
                            </label>

                            <input
                                type="text"
                                class="form-control
                                       <?php $__errorArgs = ['codigo_barras'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="codigo_barras"
                                name="codigo_barras"
                                value="<?php echo e(old('codigo_barras')); ?>">

                            <?php $__errorArgs = ['codigo_barras'];
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

                        
                        <div class="col-md-3">

                            <label
                                for="categoria_id"
                                class="form-label
                                       d-flex justify-content-between
                                       align-items-center gap-2">

                                <span>
                                    Categoria
                                    <span class="text-danger">*</span>
                                </span>

                                <?php if($rotaCategoria): ?>
                                    <a
                                        href="<?php echo e(route($rotaCategoria)); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="cadastro-auxiliar-link"
                                        title="Acessar cadastro de categorias">

                                        <i class="bi bi-plus-circle"></i>
                                        Cadastrar
                                    </a>
                                <?php endif; ?>

                            </label>

                            <select
                                class="form-select
                                       <?php $__errorArgs = ['categoria_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="categoria_id"
                                name="categoria_id"
                                required>

                                <option value="">
                                    Selecione...
                                </option>

                                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($categoria->id); ?>"
                                        <?php echo e(old('categoria_id')
                                            == $categoria->id
                                                ? 'selected'
                                                : ''); ?>>

                                        <?php echo e($categoria->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </select>

                            <?php $__errorArgs = ['categoria_id'];
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

                        
                        <div class="col-md-3">

                            <label
                                for="fornecedor_id"
                                class="form-label
                                       d-flex justify-content-between
                                       align-items-center gap-2">

                                <span>
                                    Fornecedor
                                    <span class="text-danger">*</span>
                                </span>

                                <?php if($rotaFornecedor): ?>
                                    <a
                                        href="<?php echo e(route($rotaFornecedor)); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="cadastro-auxiliar-link"
                                        title="Acessar cadastro de fornecedores">

                                        <i class="bi bi-plus-circle"></i>
                                        Cadastrar
                                    </a>
                                <?php endif; ?>

                            </label>

                            <select
                                class="form-select
                                       <?php $__errorArgs = ['fornecedor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="fornecedor_id"
                                name="fornecedor_id"
                                required>

                                <option value="">
                                    Selecione...
                                </option>

                                <?php $__currentLoopData = $fornecedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fornecedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($fornecedor->id); ?>"
                                        <?php echo e(old('fornecedor_id')
                                            == $fornecedor->id
                                                ? 'selected'
                                                : ''); ?>>

                                        <?php echo e($fornecedor->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </select>

                            <?php $__errorArgs = ['fornecedor_id'];
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

                        
                        <div class="col-md-3">

                            <label
                                for="marca_id"
                                class="form-label
                                       d-flex justify-content-between
                                       align-items-center gap-2">

                                <span>Marca</span>

                                <?php if($rotaMarca): ?>
                                    <a
                                        href="<?php echo e(route($rotaMarca)); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="cadastro-auxiliar-link"
                                        title="Acessar cadastro de marcas">

                                        <i class="bi bi-plus-circle"></i>
                                        Cadastrar
                                    </a>
                                <?php endif; ?>

                            </label>

                            <select
                                class="form-select
                                       <?php $__errorArgs = ['marca_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="marca_id"
                                name="marca_id">

                                <option value="">
                                    Selecione...
                                </option>

                                <?php $__currentLoopData = $marcas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $marca): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($marca->id); ?>"
                                        <?php echo e(old('marca_id') == $marca->id
                                                ? 'selected'
                                                : ''); ?>>

                                        <?php echo e($marca->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </select>

                            <?php $__errorArgs = ['marca_id'];
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

                        
                        <div class="col-md-3">

                            <label
                                for="unidade_medida_id"
                                class="form-label
                                       d-flex justify-content-between
                                       align-items-center gap-2">

                                <span>Unidade de Medida</span>

                                <?php if($rotaUnidadeMedida): ?>
                                    <a
                                        href="<?php echo e(route($rotaUnidadeMedida)); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="cadastro-auxiliar-link"
                                        title="Acessar cadastro de unidades de medida">

                                        <i class="bi bi-plus-circle"></i>
                                        Cadastrar
                                    </a>
                                <?php endif; ?>

                            </label>

                            <select
                                class="form-select
                                       <?php $__errorArgs = ['unidade_medida_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="unidade_medida_id"
                                name="unidade_medida_id">

                                <option value="">
                                    Selecione...
                                </option>

                                <?php $__currentLoopData = $unidadesMedida; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unidadeMedida): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($unidadeMedida->id); ?>"
                                        <?php echo e(old('unidade_medida_id')
                                            == $unidadeMedida->id
                                                ? 'selected'
                                                : ''); ?>>

                                        <?php echo e($unidadeMedida->nome); ?>


                                        <?php if($unidadeMedida->sigla): ?>
                                            (<?php echo e($unidadeMedida->sigla); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </select>

                            <?php $__errorArgs = ['unidade_medida_id'];
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

                
                <div class="produto-section mb-4">

                    <h2 class="produto-section-title">
                        <span class="section-number">2</span>
                        Custos e despesas operacionais
                    </h2>

                    <div class="row g-3">

                        <div class="col-md-3">

                            <label
                                for="preco_compra_atual"
                                class="form-label">

                                Preço de Compra — Nota (R$)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control calc-trigger"
                                id="preco_compra_atual"
                                name="preco_compra_atual"
                                value="<?php echo e(old(
                                        'preco_compra_atual',
                                        '0.00'
                                    )); ?>">

                        </div>

                        <div class="col-md-3">

                            <label
                                for="custo_frete_unidade"
                                class="form-label">

                                Frete Rateado/Unid. (R$)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control calc-trigger"
                                id="custo_frete_unidade"
                                name="custo_frete_unidade"
                                value="<?php echo e(old(
                                        'custo_frete_unidade',
                                        '0.00'
                                    )); ?>">

                        </div>

                        <div class="col-md-3">

                            <label
                                for="custo_imposto_entrada"
                                class="form-label">

                                Imposto Entrada/ST (R$)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control calc-trigger"
                                id="custo_imposto_entrada"
                                name="custo_imposto_entrada"
                                value="<?php echo e(old(
                                        'custo_imposto_entrada',
                                        '0.00'
                                    )); ?>">

                        </div>

                        <div class="col-md-3">

                            <label
                                for="custo_real_entrada"
                                class="form-label
                                       text-danger fw-bold">

                                Custo Real de Entrada (R$)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control bg-white
                                       text-danger fw-bold"
                                id="custo_real_entrada"
                                name="custo_real_entrada"
                                value="<?php echo e(old('custo_real_entrada', '0.00')); ?>"
                                readonly>

                        </div>

                        <div class="col-md-4">

                            <label
                                for="percentual_imposto_saida"
                                class="form-label">

                                Imposto sobre Venda (%)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control calc-trigger"
                                id="percentual_imposto_saida"
                                name="percentual_imposto_saida"
                                value="<?php echo e(old(
                                        'percentual_imposto_saida',
                                        '0.00'
                                    )); ?>">

                        </div>

                        <div class="col-md-4">

                            <label
                                for="percentual_comissao"
                                class="form-label">

                                Comissão do Vendedor (%)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control calc-trigger"
                                id="percentual_comissao"
                                name="percentual_comissao"
                                value="<?php echo e(old(
                                        'percentual_comissao',
                                        '0.00'
                                    )); ?>">

                        </div>

                        <div class="col-md-4">

                            <label
                                for="percentual_taxa_cartao"
                                class="form-label">

                                Taxa Administrativa Cartão (%)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control calc-trigger"
                                id="percentual_taxa_cartao"
                                name="percentual_taxa_cartao"
                                value="<?php echo e(old(
                                        'percentual_taxa_cartao',
                                        '0.00'
                                    )); ?>">

                        </div>

                    </div>

                </div>
                                
                <div class="produto-section mb-4">

                    <h2 class="produto-section-title">
                        <span class="section-number">3</span>
                        Tabelas de venda — markup por dentro
                    </h2>

                    <div class="row g-3">

                        
                        <div class="col-md-4">

                            <div
                                class="card price-card
                                       border-primary h-100 shadow-sm">

                                <div
                                    class="card-header
                                           bg-primary text-white">

                                    Tabela 1: Varejo / Balcão
                                </div>

                                <div class="card-body p-3">

                                    <div class="mb-3">

                                        <label
                                            for="markup_1"
                                            class="form-label">

                                            Lucro Desejado (%)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control calc-trigger"
                                            id="markup_1"
                                            name="markup_1"
                                            value="<?php echo e(old(
                                                    'markup_1',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                    <div class="mb-3">

                                        <label
                                            for="desconto_max_1"
                                            class="form-label">

                                            Limite de Desconto (%)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control calc-trigger"
                                            id="desconto_max_1"
                                            name="desconto_max_1"
                                            value="<?php echo e(old(
                                                    'desconto_max_1',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                    <div>

                                        <label
                                            for="preco_venda"
                                            class="form-label
                                                   fw-bold text-primary">

                                            Preço de Venda (R$)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control fw-bold
                                                   border-primary
                                                   text-primary"
                                            id="preco_venda"
                                            name="preco_venda"
                                            value="<?php echo e(old(
                                                    'preco_venda',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                </div>

                            </div>

                        </div>

                        
                        <div class="col-md-4">

                            <div
                                class="card price-card
                                       border-info h-100 shadow-sm">

                                <div
                                    class="card-header
                                           bg-info text-white">

                                    Tabela 2: Profissional / Empreiteiro
                                </div>

                                <div class="card-body p-3">

                                    <div class="mb-3">

                                        <label
                                            for="markup_2"
                                            class="form-label">

                                            Lucro Desejado (%)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control calc-trigger"
                                            id="markup_2"
                                            name="markup_2"
                                            value="<?php echo e(old(
                                                    'markup_2',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                    <div class="mb-3">

                                        <label
                                            for="desconto_max_2"
                                            class="form-label">

                                            Limite de Desconto (%)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control calc-trigger"
                                            id="desconto_max_2"
                                            name="desconto_max_2"
                                            value="<?php echo e(old(
                                                    'desconto_max_2',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                    <div>

                                        <label
                                            for="preco_venda_2"
                                            class="form-label
                                                   fw-bold text-info">

                                            Preço de Venda 2 (R$)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control fw-bold
                                                   border-info text-info"
                                            id="preco_venda_2"
                                            name="preco_venda_2"
                                            value="<?php echo e(old(
                                                    'preco_venda_2',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                </div>

                            </div>

                        </div>

                        
                        <div class="col-md-4">

                            <div
                                class="card price-card
                                       border-success h-100 shadow-sm">

                                <div
                                    class="card-header
                                           bg-success text-white">

                                    Tabela 3: Atacado / Carga Fechada
                                </div>

                                <div class="card-body p-3">

                                    <div class="mb-3">

                                        <label
                                            for="markup_3"
                                            class="form-label">

                                            Lucro Desejado (%)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control calc-trigger"
                                            id="markup_3"
                                            name="markup_3"
                                            value="<?php echo e(old(
                                                    'markup_3',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                    <div class="mb-3">

                                        <label
                                            for="desconto_max_3"
                                            class="form-label">

                                            Limite de Desconto (%)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control calc-trigger"
                                            id="desconto_max_3"
                                            name="desconto_max_3"
                                            value="<?php echo e(old(
                                                    'desconto_max_3',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                    <div>

                                        <label
                                            for="preco_venda_3"
                                            class="form-label
                                                   fw-bold text-success">

                                            Preço de Venda 3 (R$)
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="form-control fw-bold
                                                   border-success
                                                   text-success"
                                            id="preco_venda_3"
                                            name="preco_venda_3"
                                            value="<?php echo e(old(
                                                    'preco_venda_3',
                                                    '0.00'
                                                )); ?>">

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                
                <div class="produto-section mb-4">

                    <h2 class="produto-section-title">
                        <span class="section-number">4</span>
                        Controle físico, logística e imagem
                    </h2>

                    <div class="row g-3">

                        <div class="col-md-3">

                            <label
                                for="quantidade_estoque"
                                class="form-label">

                                Qtd. em Estoque
                            </label>

                            <input
                                type="number"
                                min="0"
                                class="form-control"
                                id="quantidade_estoque"
                                name="quantidade_estoque"
                                value="<?php echo e(old(
                                        'quantidade_estoque',
                                        0
                                    )); ?>">

                        </div>

                        <div class="col-md-3">

                            <label
                                for="estoque_minimo"
                                class="form-label">

                                Estoque Mínimo
                            </label>

                            <input
                                type="number"
                                min="0"
                                class="form-control"
                                id="estoque_minimo"
                                name="estoque_minimo"
                                value="<?php echo e(old(
                                        'estoque_minimo',
                                        0
                                    )); ?>">

                        </div>

                        <div class="col-md-6">

                            <label
                                for="localizacao_estoque_id"
                                class="form-label">

                                Localização de Estoque
                            </label>

                            <div class="input-group">

                                <select
                                    class="form-select
                                           <?php $__errorArgs = ['localizacao_estoque_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                           is-invalid
                                           <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="localizacao_estoque_id"
                                    name="localizacao_estoque_id">

                                    <option value="">
                                        Selecione uma localização...
                                    </option>

                                    <?php $__currentLoopData = $localizacoesEstoque; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localizacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            value="<?php echo e($localizacao->id); ?>"
                                            <?php echo e(old(
                                                    'localizacao_estoque_id'
                                                ) == $localizacao->id
                                                    ? 'selected'
                                                    : ''); ?>>

                                            <?php echo e($localizacao->codigo); ?>


                                            <?php if($localizacao->descricao): ?>
                                                -
                                                <?php echo e($localizacao->descricao); ?>

                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </select>

                                <?php if(
                                    \Illuminate\Support\Facades\Route::has(
                                        'localizacoes-estoque.create'
                                    )
                                ): ?>
                                    <a
                                        href="<?php echo e(route(
                                                'localizacoes-estoque.create'
                                            )); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn btn-outline-primary"
                                        title="Cadastrar localização">

                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                <?php endif; ?>

                            </div>

                            <?php $__errorArgs = ['localizacao_estoque_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div
                                    class="invalid-feedback d-block">

                                    <?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <small class="text-muted">
                                Escolha a posição física onde o produto
                                será armazenado.
                            </small>

                        </div>

                        <div class="col-md-3">

                            <label for="peso" class="form-label">
                                Peso (kg)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control"
                                id="peso"
                                name="peso"
                                value="<?php echo e(old('peso', '0.00')); ?>">

                        </div>

                        <div class="col-md-2">

                            <label for="largura" class="form-label">
                                Largura (cm)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control"
                                id="largura"
                                name="largura"
                                value="<?php echo e(old('largura', '0.00')); ?>">

                        </div>

                        <div class="col-md-2">

                            <label for="altura" class="form-label">
                                Altura (cm)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control"
                                id="altura"
                                name="altura"
                                value="<?php echo e(old('altura', '0.00')); ?>">

                        </div>

                        <div class="col-md-2">

                            <label
                                for="profundidade"
                                class="form-label">

                                Profundidade (cm)
                            </label>

                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                class="form-control"
                                id="profundidade"
                                name="profundidade"
                                value="<?php echo e(old(
                                        'profundidade',
                                        '0.00'
                                    )); ?>">

                        </div>

                        <div class="col-md-6">

                            <label for="imagem" class="form-label">
                                Imagem do Produto
                            </label>

                            <input
                                type="file"
                                class="form-control
                                       <?php $__errorArgs = ['imagem'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="imagem"
                                name="imagem"
                                accept="image/*"
                                onchange="previewImage(event)">

                            <?php $__errorArgs = ['imagem'];
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

                            <img
                                id="imagemPreview"
                                src="<?php echo e(asset(
                                        'image/produtos/'
                                        . 'produto-sem-imagem.PNG'
                                    )); ?>"
                                onerror="
                                    this.onerror = null;
                                    this.src = '<?php echo e(asset(
                                            'image/produtos/'
                                            . 'produto-sem-imagem.PNG'
                                        )); ?>';
                                "
                                alt="Prévia da imagem"
                                class="produto-image-preview mt-2">

                        </div>

                    </div>

                </div>
                                
                <div class="produto-section mb-4">

                    <h2 class="produto-section-title">
                        <span class="section-number">5</span>
                        Atributos fiscais e controle
                    </h2>

                    <div class="row g-3">

                        <div class="col-md-2">

                            <label for="ncm" class="form-label">
                                NCM
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="ncm"
                                name="ncm"
                                maxlength="8"
                                value="<?php echo e(old('ncm')); ?>">

                        </div>

                        <div class="col-md-2">

                            <label for="cest" class="form-label">
                                CEST
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="cest"
                                name="cest"
                                maxlength="7"
                                value="<?php echo e(old('cest')); ?>">

                        </div>

                        <div class="col-md-2">

                            <label for="cfop" class="form-label">
                                CFOP Padrão
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="cfop"
                                name="cfop"
                                maxlength="4"
                                value="<?php echo e(old('cfop')); ?>">

                        </div>

                        <div class="col-md-3">

                            <label
                                for="icms_csosn"
                                class="form-label">

                                ICMS / CSOSN
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="icms_csosn"
                                name="icms_csosn"
                                maxlength="4"
                                value="<?php echo e(old('icms_csosn')); ?>">

                        </div>

                        <div class="col-md-3">

                            <label
                                for="origem"
                                class="form-label">

                                Origem da Mercadoria
                            </label>

                            <select
                                class="form-select"
                                id="origem"
                                name="origem">

                                <option
                                    value="0"
                                    <?php echo e(old('origem', '0') === '0'
                                            ? 'selected'
                                            : ''); ?>>

                                    0 - Nacional
                                </option>

                                <option
                                    value="1"
                                    <?php echo e(old('origem') === '1'
                                            ? 'selected'
                                            : ''); ?>>

                                    1 - Estrangeira - Importação Direta
                                </option>

                                <option
                                    value="2"
                                    <?php echo e(old('origem') === '2'
                                            ? 'selected'
                                            : ''); ?>>

                                    2 - Estrangeira - Adquirida no
                                    Mercado Interno
                                </option>

                            </select>

                        </div>

                        <div class="col-12">

                            <label
                                for="descricao"
                                class="form-label">

                                Descrição Longa / Observações
                            </label>

                            <textarea
                                class="form-control"
                                id="descricao"
                                name="descricao"
                                rows="3"><?php echo e(old('descricao')); ?></textarea>

                        </div>

                    </div>

                </div>

                
                <div class="produto-section mb-4">

                    <h2 class="produto-section-title">
                        <span class="section-number">6</span>
                        Disponibilidade e validade
                    </h2>

                    <div class="row g-3 align-items-center">

                        <div class="col-md-3">

                            <input
                                type="hidden"
                                name="ativo"
                                value="0">

                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="ativo"
                                    name="ativo"
                                    value="1"
                                    <?php echo e(old('ativo', '1') === '1'
                                            ? 'checked'
                                            : ''); ?>>

                                <label
                                    class="form-check-label"
                                    for="ativo">

                                    Produto Ativo para Vendas
                                </label>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <input
                                type="hidden"
                                name="em_promocao"
                                value="0">

                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="em_promocao"
                                    name="em_promocao"
                                    value="1"
                                    <?php echo e(old('em_promocao', '0') === '1'
                                            ? 'checked'
                                            : ''); ?>>

                                <label
                                    class="form-check-label"
                                    for="em_promocao">

                                    Destacar em Promoção
                                </label>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <input
                                type="hidden"
                                name="controla_validade"
                                value="0">

                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="controla_validade"
                                    name="controla_validade"
                                    value="1"
                                    <?php echo e(old(
                                            'controla_validade',
                                            '1'
                                        ) === '1'
                                            ? 'checked'
                                            : ''); ?>

                                    onchange="
                                        toggleValidade(this)
                                    ">

                                <label
                                    class="form-check-label"
                                    for="controla_validade">

                                    Controlar Validade
                                </label>

                            </div>

                        </div>

                        <div
                            class="col-md-3"
                            id="validade_container">

                            <label
                                for="validade_produto"
                                class="form-label">

                                Data de Validade
                            </label>

                            <input
                                type="date"
                                class="form-control
                                       <?php $__errorArgs = ['validade_produto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                       is-invalid
                                       <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                id="validade_produto"
                                name="validade_produto"
                                value="<?php echo e(old('validade_produto')); ?>">

                            <?php $__errorArgs = ['validade_produto'];
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

                
                <div
                    class="produto-actions
                           d-flex justify-content-end
                           flex-wrap gap-2">

                    <button
                        type="reset"
                        class="btn btn-outline-secondary">

                        <i class="bi bi-eraser me-1"></i>
                        Limpar campos
                    </button>

                    <button
                        type="submit"
                        class="btn btn-success px-4">

                        <i class="bi bi-check-circle me-1"></i>
                        Salvar cadastro
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
    function valorNumerico(id) {
        const elemento = document.getElementById(id);

        if (!elemento) {
            return 0;
        }

        return parseFloat(elemento.value) || 0;
    }

    function calcularTabelasMarkup() {
        const compra = valorNumerico(
            'preco_compra_atual'
        );

        const frete = valorNumerico(
            'custo_frete_unidade'
        );

        const impostoEntrada = valorNumerico(
            'custo_imposto_entrada'
        );

        const custoReal =
            compra
            + frete
            + impostoEntrada;

        const campoCustoReal = document.getElementById(
            'custo_real_entrada'
        );

        if (campoCustoReal) {
            campoCustoReal.value = custoReal.toFixed(2);
        }

        if (custoReal === 0) {
            return;
        }

        const impostoSaida = valorNumerico(
            'percentual_imposto_saida'
        );

        const comissao = valorNumerico(
            'percentual_comissao'
        );

        const taxaCartao = valorNumerico(
            'percentual_taxa_cartao'
        );

        const totalCustosSaida =
            impostoSaida
            + comissao
            + taxaCartao;

        for (let tabela = 1; tabela <= 3; tabela++) {
            const markup = valorNumerico(
                'markup_' + tabela
            );

            const desconto = valorNumerico(
                'desconto_max_' + tabela
            );

            const totalDeducoes =
                totalCustosSaida
                + markup
                + desconto;

            const divisor =
                1
                - (totalDeducoes / 100);

            let precoFinal = 0;

            if (divisor > 0) {
                precoFinal = custoReal / divisor;
            } else {
                precoFinal =
                    custoReal
                    * (1 + (markup / 100));
            }

            const campoPrecoId =
                tabela === 1
                    ? 'preco_venda'
                    : 'preco_venda_' + tabela;

            const campoPreco = document.getElementById(
                campoPrecoId
            );

            if (campoPreco) {
                campoPreco.value = precoFinal.toFixed(2);
            }
        }
    }

    function previewImage(event) {
        const arquivo =
            event.target.files
            && event.target.files[0]
                ? event.target.files[0]
                : null;

        if (!arquivo) {
            return;
        }

        const leitor = new FileReader();

        leitor.onload = function (eventoLeitura) {
            const imagem = document.getElementById(
                'imagemPreview'
            );

            if (imagem) {
                imagem.src = eventoLeitura.target.result;
            }
        };

        leitor.readAsDataURL(arquivo);
    }

    function toggleValidade(checkbox) {
        const container = document.getElementById(
            'validade_container'
        );

        const campoValidade = document.getElementById(
            'validade_produto'
        );

        if (!container) {
            return;
        }

        container.style.display =
            checkbox.checked
                ? 'block'
                : 'none';

        if (
            campoValidade
            && !checkbox.checked
        ) {
            campoValidade.value = '';
        }
    }

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            document
                .querySelectorAll('.calc-trigger')
                .forEach(function (campo) {
                    campo.addEventListener(
                        'input',
                        calcularTabelasMarkup
                    );
                });

            const controlaValidade =
                document.getElementById(
                    'controla_validade'
                );

            if (controlaValidade) {
                toggleValidade(
                    controlaValidade
                );
            }

            calcularTabelasMarkup();

            const botaoLimpar =
                document.querySelector(
                    'button[type="reset"]'
                );

            if (botaoLimpar) {
                botaoLimpar.addEventListener(
                    'click',
                    function () {
                        window.setTimeout(
                            function () {
                                const checkbox =
                                    document.getElementById(
                                        'controla_validade'
                                    );

                                if (checkbox) {
                                    toggleValidade(
                                        checkbox
                                    );
                                }

                                calcularTabelasMarkup();
                            },
                            0
                        );
                    }
                );
            }
        }
    );
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\produtos\create.blade.php ENDPATH**/ ?>