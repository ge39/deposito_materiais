<?php $__env->startSection('content'); ?>
<?php
    $simulacao = $simulacao ?? collect();
    $filtros = $filtros ?? [];

    $totalProdutos = $simulacao->count();

    $estoqueDisponivel = $simulacao->sum(
        'disponivel'
    );

    $mediaAtual = $totalProdutos > 0
        ? $simulacao->avg('preco_atual')
        : 0;

    $mediaNova = $totalProdutos > 0
        ? $simulacao->avg('novo_preco')
        : 0;

    $valorEstoqueCusto = $simulacao->sum(
        'valor_estoque_custo'
    );

    $valorVendaAtual = $simulacao->sum(
        'valor_venda_atual'
    );

    $valorVendaNovo = $simulacao->sum(
        'valor_venda_novo'
    );

    $impactoFinanceiro = $simulacao->sum(
        'impacto_financeiro'
    );
?>

<style>
    .price-adjust-page {
        --navy: #072b62;
        --border: #d9e0e8;
        --green: #198754;

        color: #1f2937;
    }

    .price-adjust-page .page-header {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .price-adjust-page .page-title {
        color: var(--navy);
        font-size: 1.45rem;
        font-weight: 850;
        margin: 0;
    }

    .price-adjust-page .page-subtitle {
        color: #6b7280;
        font-size: .78rem;
        margin-top: .2rem;
    }

    .price-adjust-page .section-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: .55rem;
        box-shadow:
            0 .15rem .4rem
            rgba(15, 23, 42, .06);
        margin-bottom: .85rem;
        overflow: hidden;
    }

    .price-adjust-page .section-title {
        align-items: center;
        background: #f8fafc;
        border-bottom:
            1px solid var(--border);
        color: var(--navy);
        display: flex;
        font-size: .78rem;
        font-weight: 850;
        justify-content: space-between;
        padding: .65rem .8rem;
        text-transform: uppercase;
    }

    .price-adjust-page .section-body {
        padding: .85rem;
    }

    .price-adjust-page .form-label {
        color: #42526a;
        font-size: .68rem;
        font-weight: 800;
        margin-bottom: .22rem;
        text-transform: uppercase;
    }

    .price-adjust-page .form-control,
    .price-adjust-page .form-select {
        font-size: .8rem;
    }

    .price-adjust-page .scope-field {
        display: none;
    }

    .price-adjust-page
    .scope-field.is-active {
        display: block;
    }

    .price-adjust-page .warning-box {
        background: #fff8e7;
        border: 1px solid #efd18a;
        border-radius: .4rem;
        color: #6e5200;
        font-size: .7rem;
        padding: .65rem .75rem;
    }

    .price-adjust-page .summary-grid,
    .price-adjust-page .financial-grid {
        display: grid;
        gap: .55rem;
        grid-template-columns:
            repeat(
                4,
                minmax(0, 1fr)
            );
    }

    .price-adjust-page .financial-grid {
        margin-top: .55rem;
    }

    .price-adjust-page .summary-card {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: .45rem;
        padding: .65rem .7rem;
    }

    .price-adjust-page
    .summary-card.financial {
        background: #fbfcfe;
    }

    .price-adjust-page .summary-label {
        color: #6b7280;
        font-size: .61rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .price-adjust-page .summary-value {
        color: var(--navy);
        font-size: 1rem;
        font-weight: 900;
        margin-top: .15rem;
    }

    .price-adjust-page .summary-note {
        color: #7b8794;
        font-size: .58rem;
        margin-top: .12rem;
    }

    .price-adjust-page .preview-table {
        margin: 0;
        min-width: 1450px;
    }

    .price-adjust-page
    .preview-table th {
        background: var(--navy);
        color: #fff;
        font-size: .62rem;
        padding: .45rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .price-adjust-page
    .preview-table td {
        font-size: .68rem;
        padding: .45rem;
        vertical-align: middle;
    }

    .price-adjust-page
    .preview-table tbody tr:hover td {
        background: #f4f8fd;
    }

    .price-adjust-page .price-old {
        color: #6b7280;
        font-weight: 700;
    }

    .price-adjust-page .price-new {
        color: var(--green);
        font-weight: 900;
    }

    .price-adjust-page .money-cost {
        color: #7a5b00;
        font-weight: 800;
    }

    .price-adjust-page .money-sale {
        color: #174f97;
        font-weight: 800;
    }

    .price-adjust-page .empty-preview {
        color: #6b7280;
        padding: 2rem 1rem;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .price-adjust-page
        .summary-grid,
        .price-adjust-page
        .financial-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }
    }

    @media (max-width: 575.98px) {
        .price-adjust-page
        .summary-grid,
        .price-adjust-page
        .financial-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid px-3 px-xl-4 py-3 price-adjust-page">

    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="bi bi-tags-fill me-2"></i>
                Ajuste de Preços em Massa
            </h1>

            <div class="page-subtitle">
                Simule alterações comerciais e estime
                o valor financeiro do estoque sem
                movimentar quantidades, reservas ou lotes.
            </div>
        </div>

        <a
            href="<?php echo e(route('produtos.index')); ?>"
            class="btn btn-outline-secondary btn-sm"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Voltar para Produtos
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-1"></i>
            <?php echo e(session('success')); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <?php echo e(session('error')); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>
                Não foi possível simular.
            </strong>

            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form
        method="POST"
        action="<?php echo e(route('ajuste-precos.simular')); ?>"
    >
        <?php echo csrf_field(); ?>

        <div class="section-card">
            <div class="section-title">
                <span>
                    <i class="bi bi-funnel-fill me-1"></i>
                    1. Seleção dos produtos
                </span>

                <span class="badge bg-secondary">
                    Simulação
                </span>
            </div>

            <div class="section-body">
                <div class="row g-3">

                    <div class="col-md-3">
                        <label
                            class="form-label"
                            for="escopo"
                        >
                            Aplicar ajuste em
                        </label>

                        <select
                            class="form-select"
                            id="escopo"
                            name="escopo"
                            required
                        >
                            <?php $__currentLoopData = [
                                'categoria'
                                    => 'Categoria',
                                'fornecedor'
                                    => 'Fornecedor',
                                'produto'
                                    => 'Produto específico',
                                'selecionados'
                                    => 'Produtos selecionados',
                                'todos'
                                    => 'Todos os produtos',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($valor); ?>"
                                    <?php if(
                                        old(
                                            'escopo',
                                            $filtros['escopo']
                                                ?? 'categoria'
                                        ) === $valor
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($rotulo); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-5">

                        <div
                            class="scope-field"
                            data-scope-field="categoria"
                        >
                            <label class="form-label">
                                Categoria
                            </label>

                            <select
                                class="form-select"
                                name="categoria_id"
                            >
                                <option value="">
                                    Selecione
                                </option>

                                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($categoria->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'categoria_id',
                                                $filtros[
                                                    'categoria_id'
                                                ] ?? ''
                                            )
                                            ===
                                            (string)
                                            $categoria->id
                                        ): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($categoria->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div
                            class="scope-field"
                            data-scope-field="fornecedor"
                        >
                            <label class="form-label">
                                Fornecedor
                            </label>

                            <select
                                class="form-select"
                                name="fornecedor_id"
                            >
                                <option value="">
                                    Selecione
                                </option>

                                <?php $__currentLoopData = $fornecedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fornecedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($fornecedor->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'fornecedor_id',
                                                $filtros[
                                                    'fornecedor_id'
                                                ] ?? ''
                                            )
                                            ===
                                            (string)
                                            $fornecedor->id
                                        ): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($fornecedor->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div
                            class="scope-field"
                            data-scope-field="produto"
                        >
                            <label class="form-label">
                                Produto
                            </label>

                            <select
                                class="form-select"
                                name="produto_id"
                            >
                                <option value="">
                                    Selecione
                                </option>

                                <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($produto->id); ?>"
                                        <?php if(
                                            (string) old(
                                                'produto_id',
                                                $filtros[
                                                    'produto_id'
                                                ] ?? ''
                                            )
                                            ===
                                            (string)
                                            $produto->id
                                        ): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($produto->nome); ?>


                                        <?php if($produto->sku): ?>
                                            — <?php echo e($produto->sku); ?>

                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div
                            class="scope-field"
                            data-scope-field="selecionados"
                        >
                            <label class="form-label">
                                Produtos selecionados
                            </label>

                            <select
                                class="form-select"
                                name="produto_ids[]"
                                multiple
                                size="6"
                            >
                                <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($produto->id); ?>"
                                        <?php if(
                                            in_array(
                                                (string)
                                                $produto->id,
                                                array_map(
                                                    'strval',
                                                    old(
                                                        'produto_ids',
                                                        $filtros[
                                                            'produto_ids'
                                                        ]
                                                        ?? []
                                                    )
                                                ),
                                                true
                                            )
                                        ): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($produto->nome); ?>


                                        <?php if($produto->sku): ?>
                                            — <?php echo e($produto->sku); ?>

                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <div class="form-text">
                                Use Ctrl para selecionar
                                vários produtos.
                            </div>
                        </div>

                        <div
                            class="scope-field"
                            data-scope-field="todos"
                        >
                            <div
                                class="
                                    alert
                                    alert-light
                                    border
                                    mb-0
                                    py-2
                                "
                            >
                                Todos os produtos compatíveis
                                com os demais filtros serão
                                incluídos.
                            </div>
                        </div>

                    </div>

                    <div class="col-md-2">
                        <label class="form-label">
                            Situação
                        </label>

                        <select
                            class="form-select"
                            name="situacao"
                        >
                            <?php $__currentLoopData = [
                                'ativos'
                                    => 'Somente ativos',
                                'inativos'
                                    => 'Somente inativos',
                                'todos'
                                    => 'Todos',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($valor); ?>"
                                    <?php if(
                                        old(
                                            'situacao',
                                            $filtros[
                                                'situacao'
                                            ] ?? 'ativos'
                                        ) === $valor
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($rotulo); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">
                            Estoque
                        </label>

                        <select
                            class="form-select"
                            name="estoque"
                        >
                            <?php $__currentLoopData = [
                                'com_estoque'
                                    => 'Com estoque',
                                'sem_estoque'
                                    => 'Sem estoque',
                                'todos'
                                    => 'Todos',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($valor); ?>"
                                    <?php if(
                                        old(
                                            'estoque',
                                            $filtros[
                                                'estoque'
                                            ]
                                            ?? 'com_estoque'
                                        ) === $valor
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($rotulo); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title">
                <span>
                    <i class="bi bi-calculator-fill me-1"></i>
                    2. Regra do ajuste
                </span>
            </div>

            <div class="section-body">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label">
                            Preço a alterar
                        </label>

                        <select
                            class="form-select"
                            name="campo_preco"
                            required
                        >
                            <?php $__currentLoopData = [
                                'preco_venda'
                                    => 'Preço de Venda 1',
                                'preco_venda_2'
                                    => 'Preço de Venda 2',
                                'preco_venda_3'
                                    => 'Preço de Venda 3',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($valor); ?>"
                                    <?php if(
                                        old(
                                            'campo_preco',
                                            $filtros[
                                                'campo_preco'
                                            ]
                                            ?? 'preco_venda'
                                        ) === $valor
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($rotulo); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Tipo de ajuste
                        </label>

                        <select
                            class="form-select"
                            name="tipo_ajuste"
                            required
                        >
                            <?php $__currentLoopData = [
                                'acrescimo_percentual'
                                    => 'Acréscimo percentual',
                                'desconto_percentual'
                                    => 'Desconto percentual',
                                'acrescimo_valor'
                                    => 'Acréscimo em valor',
                                'desconto_valor'
                                    => 'Desconto em valor',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <option
                                    value="<?php echo e($valor); ?>"
                                    <?php if(
                                        old(
                                            'tipo_ajuste',
                                            $filtros[
                                                'tipo_ajuste'
                                            ]
                                            ??
                                            'acrescimo_percentual'
                                        ) === $valor
                                    ): echo 'selected'; endif; ?>
                                >
                                    <?php echo e($rotulo); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">
                            Valor
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            name="valor_ajuste"
                            min="0"
                            step="0.01"
                            value="<?php echo e(old(
                                'valor_ajuste',
                                $filtros[
                                    'valor_ajuste'
                                ] ?? '5.00'
                            )); ?>"
                            required
                        >
                    </div>

                    <div class="col-md-3">
                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            <i
                                class="
                                    bi
                                    bi-play-circle
                                    me-1
                                "
                            ></i>
                            Simular Ajuste
                        </button>
                    </div>

                </div>

                <div class="warning-box mt-3">
                    <i
                        class="
                            bi
                            bi-info-circle-fill
                            me-1
                        "
                    ></i>

                    Nesta fase nenhuma alteração
                    é gravada. A simulação não
                    modifica produtos, lotes,
                    reservas, estoque ou auditoria.
                </div>
            </div>
        </div>
    </form>

    <div class="section-card">
        <div class="section-title">
            <span>
                <i class="bi bi-eye-fill me-1"></i>
                3. Pré-visualização e valor do estoque
            </span>

            <?php if($totalProdutos > 0): ?>
                <span class="badge bg-primary">
                    <?php echo e($totalProdutos); ?>

                    produto(s)
                </span>
            <?php endif; ?>
        </div>

        <div class="section-body">

            <?php if($totalProdutos > 0): ?>

                <div class="summary-grid">

                    <div class="summary-card">
                        <div class="summary-label">
                            Produtos afetados
                        </div>

                        <div class="summary-value">
                            <?php echo e($totalProdutos); ?>

                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="summary-label">
                            Estoque disponível
                        </div>

                        <div class="summary-value">
                            <?php echo e(number_format(
                                $estoqueDisponivel,
                                3,
                                ',',
                                '.'
                            )); ?>

                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="summary-label">
                            Preço médio atual
                        </div>

                        <div class="summary-value">
                            R$ <?php echo e(number_format(
                                $mediaAtual,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>
                    </div>

                    <div class="summary-card">
                        <div class="summary-label">
                            Preço médio novo
                        </div>

                        <div class="summary-value">
                            R$ <?php echo e(number_format(
                                $mediaNova,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>
                    </div>

                </div>

                <div class="financial-grid">

                    <div class="summary-card financial">
                        <div class="summary-label">
                            Valor do estoque a custo
                        </div>

                        <div class="summary-value">
                            R$ <?php echo e(number_format(
                                $valorEstoqueCusto,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>

                        <div class="summary-note">
                            Disponível × custo médio
                            dos lotes
                        </div>
                    </div>

                    <div class="summary-card financial">
                        <div class="summary-label">
                            Potencial de venda atual
                        </div>

                        <div class="summary-value">
                            R$ <?php echo e(number_format(
                                $valorVendaAtual,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>

                        <div class="summary-note">
                            Disponível × preço atual
                        </div>
                    </div>

                    <div class="summary-card financial">
                        <div class="summary-label">
                            Potencial após ajuste
                        </div>

                        <div class="summary-value">
                            R$ <?php echo e(number_format(
                                $valorVendaNovo,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>

                        <div class="summary-note">
                            Disponível × novo preço
                        </div>
                    </div>

                    <div class="summary-card financial">
                        <div class="summary-label">
                            Impacto financeiro
                        </div>

                        <div
                            class="
                                summary-value
                                <?php echo e($impactoFinanceiro < 0
                                        ? 'text-danger'
                                        : 'text-success'); ?>

                            "
                        >
                            <?php echo e($impactoFinanceiro >= 0
                                    ? '+'
                                    : ''); ?>


                            R$ <?php echo e(number_format(
                                $impactoFinanceiro,
                                2,
                                ',',
                                '.'
                            )); ?>

                        </div>

                        <div class="summary-note">
                            Potencial ajustado −
                            potencial atual
                        </div>
                    </div>

                </div>

                <div class="table-responsive mt-3">
                    <table
                        class="
                            table
                            table-bordered
                            preview-table
                        "
                    >
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Fornecedor</th>
                                <th>Disponível</th>
                                <th>Custo médio</th>
                                <th>Valor estoque</th>
                                <th>Preço atual</th>
                                <th>Venda atual</th>
                                <th>Novo preço</th>
                                <th>Venda ajustada</th>
                                <th>Impacto</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $simulacao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?php echo e($item['nome']); ?>

                                        </strong>

                                        <?php if($item['sku']): ?>
                                            <div
                                                class="
                                                    text-muted
                                                    small
                                                "
                                            >
                                                SKU:
                                                <?php echo e($item['sku']); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php echo e($item[
                                                'categoria'
                                            ]); ?>

                                    </td>

                                    <td>
                                        <?php echo e($item[
                                                'fornecedor'
                                            ]); ?>

                                    </td>

                                    <td class="text-end">
                                        <?php echo e(number_format(
                                            $item[
                                                'disponivel'
                                            ],
                                            3,
                                            ',',
                                            '.'
                                        )); ?>


                                        <?php echo e($item[
                                                'unidade'
                                            ]); ?>

                                    </td>

                                    <td
                                        class="
                                            text-end
                                            money-cost
                                        "
                                    >
                                        R$ <?php echo e(number_format(
                                            $item[
                                                'custo_medio'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </td>

                                    <td
                                        class="
                                            text-end
                                            money-cost
                                        "
                                    >
                                        R$ <?php echo e(number_format(
                                            $item[
                                                'valor_estoque_custo'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </td>

                                    <td
                                        class="
                                            text-end
                                            price-old
                                        "
                                    >
                                        R$ <?php echo e(number_format(
                                            $item[
                                                'preco_atual'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </td>

                                    <td
                                        class="
                                            text-end
                                            money-sale
                                        "
                                    >
                                        R$ <?php echo e(number_format(
                                            $item[
                                                'valor_venda_atual'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </td>

                                    <td
                                        class="
                                            text-end
                                            price-new
                                        "
                                    >
                                        R$ <?php echo e(number_format(
                                            $item[
                                                'novo_preco'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </td>

                                    <td
                                        class="
                                            text-end
                                            money-sale
                                        "
                                    >
                                        R$ <?php echo e(number_format(
                                            $item[
                                                'valor_venda_novo'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </td>

                                    <td
                                        class="
                                            text-end
                                            fw-bold
                                            <?php echo e($item[
                                                    'impacto_financeiro'
                                                ] < 0
                                                    ? 'text-danger'
                                                    : 'text-success'); ?>

                                        "
                                    >
                                        <?php echo e($item[
                                                'impacto_financeiro'
                                            ] >= 0
                                                ? '+'
                                                : ''); ?>


                                        R$ <?php echo e(number_format(
                                            $item[
                                                'impacto_financeiro'
                                            ],
                                            2,
                                            ',',
                                            '.'
                                        )); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div
                    class="
                        d-flex
                        flex-wrap
                        gap-2
                        justify-content-end
                        mt-3
                    "
                >
                    <a
                        href="<?php echo e(route(
                                'ajuste-precos.index'
                            )); ?>"
                        class="
                            btn
                            btn-outline-secondary
                        "
                    >
                        Limpar Simulação
                    </a>

                    <form
                        method="POST"
                        action="<?php echo e(route('ajuste-precos.aplicar')); ?>"
                        class="d-inline"
                        onsubmit="
                            return confirm(
                                'Confirma a alteração dos preços dos '
                                + '<?php echo e($totalProdutos); ?> produto(s)? '
                                + 'Esta operação será registrada na auditoria.'
                            );
                        "
                    >
                        <?php echo csrf_field(); ?>

                        <input
                            type="hidden"
                            name="escopo"
                            value="<?php echo e($filtros['escopo']); ?>"
                        >

                        <input
                            type="hidden"
                            name="categoria_id"
                            value="<?php echo e($filtros['categoria_id'] ?? ''); ?>"
                        >

                        <input
                            type="hidden"
                            name="fornecedor_id"
                            value="<?php echo e($filtros['fornecedor_id'] ?? ''); ?>"
                        >

                        <input
                            type="hidden"
                            name="produto_id"
                            value="<?php echo e($filtros['produto_id'] ?? ''); ?>"
                        >

                        <?php $__currentLoopData = $filtros['produto_ids'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produtoIdSelecionado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input
                                type="hidden"
                                name="produto_ids[]"
                                value="<?php echo e($produtoIdSelecionado); ?>"
                            >
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <input
                            type="hidden"
                            name="situacao"
                            value="<?php echo e($filtros['situacao']); ?>"
                        >

                        <input
                            type="hidden"
                            name="estoque"
                            value="<?php echo e($filtros['estoque']); ?>"
                        >

                        <input
                            type="hidden"
                            name="campo_preco"
                            value="<?php echo e($filtros['campo_preco']); ?>"
                        >

                        <input
                            type="hidden"
                            name="tipo_ajuste"
                            value="<?php echo e($filtros['tipo_ajuste']); ?>"
                        >

                        <input
                            type="hidden"
                            name="valor_ajuste"
                            value="<?php echo e($filtros['valor_ajuste']); ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            <i
                                class="
                                    bi
                                    bi-check-circle-fill
                                    me-1
                                "
                            ></i>
                            Confirmar Alteração
                        </button>
                    </form>
                </div>

            <?php else: ?>

                <div class="empty-preview">
                    <i
                        class="
                            bi
                            bi-calculator
                            fs-2
                            d-block
                            mb-2
                        "
                    ></i>

                    Configure os filtros e clique em
                    <strong>
                        Simular Ajuste
                    </strong>.
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const escopo =
            document.getElementById(
                'escopo'
            );

        const campos = [
            ...document.querySelectorAll(
                '[data-scope-field]'
            )
        ];

        function atualizarEscopo() {
            campos.forEach(
                function (campo) {
                    campo.classList.toggle(
                        'is-active',
                        campo.dataset.scopeField
                            === escopo.value
                    );
                }
            );
        }

        escopo.addEventListener(
            'change',
            atualizarEscopo
        );

        atualizarEscopo();
    }
);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\ajuste-precos\index.blade.php ENDPATH**/ ?>