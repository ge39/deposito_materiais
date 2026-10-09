<?php $__env->startSection('content'); ?>

<?php echo $__env->make('bi.partials.styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $moeda = fn($v) => 'R$ '.number_format((float)$v, 2, ',', '.');
    $numero = fn($v) => number_format((float)$v, 0, ',', '.');
    $decimal = fn($v) => number_format((float)$v, 3, ',', '.');

    $resumo = $dados['resumo'];

    $rankings = [
        'mais_vendidos' => [
            'titulo' => 'Mais vendidos',
            'icone' => 'bi-graph-up-arrow',
            'cor' => 'success',
            'tipo' => 'quantidade',
        ],
        'menos_vendidos' => [
            'titulo' => 'Menos vendidos',
            'icone' => 'bi-graph-down-arrow',
            'cor' => 'warning',
            'tipo' => 'quantidade',
        ],
        'maior_faturamento' => [
            'titulo' => 'Maior faturamento',
            'icone' => 'bi-currency-dollar',
            'cor' => 'primary',
            'tipo' => 'moeda',
        ],
        'menor_faturamento' => [
            'titulo' => 'Menor faturamento',
            'icone' => 'bi-graph-down',
            'cor' => 'secondary',
            'tipo' => 'moeda',
        ],
    ];

    $cards = [
        [
            'id' => 'ativos',
            'titulo' => 'Produtos ativos',
            'valor' => $numero($resumo['produtos_ativos']),
            'icone' => 'bi-box-seam',
            'cor' => 'primary',
            'rota' => 'bi.produtos.ativos',
        ],
        [
            'id' => 'estoque_zero',
            'titulo' => 'Produtos sem estoque',
            'valor' => $numero($resumo['estoque_zero']),
            'icone' => 'bi-exclamation-triangle',
            'cor' => 'danger',
            'rota' => 'bi.produtos.estoque-zero',
        ],
        [
            'id' => 'abaixo_minimo',
            'titulo' => 'Abaixo do minimo',
            'valor' => $numero($resumo['abaixo_minimo']),
            'icone' => 'bi-arrow-down-circle',
            'cor' => 'warning',
            'rota' => 'bi.produtos.abaixo-minimo',
        ],
        [
            'id' => 'valor_estoque',
            'titulo' => 'Valor financeiro do estoque',
            'valor' => $moeda($resumo['valor_estoque']),
            'icone' => 'bi-cash-stack',
            'cor' => 'success',
            'rota' => 'bi.produtos.valor-estoque',
        ],
    ];

    foreach ($rankings as $chave => $config) {
        $lista = collect($dados[$chave] ?? []);

        $primeiro = $lista->first();

        $valor = $primeiro
            ? ($config['tipo'] === 'moeda'
                ? $moeda($primeiro->faturamento ?? 0)
                : $decimal($primeiro->quantidade_vendida ?? 0))
            : 'Sem dados';

        $cards[] = [
            'id' => $chave,
            'titulo' => $config['titulo'],
            'valor' => $valor,
            'icone' => $config['icone'],
            'cor' => $config['cor'],
            'rota' => null,
        ];
    }

    $cards[] = [
        'id' => 'estoque_parado',
        'titulo' => 'Estoque parado',
        'valor' => $numero($resumo['estoque_parado']),
        'icone' => 'bi-clock-history',
        'cor' => 'warning',
        'rota' => 'bi.produtos.estoque-parado',
    ];

    $criticos = collect($dados['cobertura']['criticos'] ?? []);

    $cards[] = [
        'id' => 'cobertura',
        'titulo' => 'Cobertura de estoque',
        'valor' => $numero(
            $dados['cobertura']['produtos_calculados'] ?? 0
        ),
        'icone' => 'bi-calendar-range',
        'cor' => 'info',
        'rota' => null,
    ];
    // DESCRICOES GERENCIAIS DOS CARDS BI-08
    $descricoesBI08 = [
        'ativos' =>
            'Total de produtos ativos cadastrados no ERP.',

        'estoque_zero' =>
            'Produtos com saldo zerado ou negativo, sujeitos a ruptura de estoque.',

        'abaixo_minimo' =>
            'Produtos com estoque inferior ao minimo cadastrado.',

        'valor_estoque' =>
            'Valor financeiro do estoque disponivel conforme calculo do ERP.',

        'mais_vendidos' =>
            'Quantidade vendida pelo produto lider no periodo.',

        'menos_vendidos' =>
            'Quantidade vendida pelo produto de menor giro no ranking.',

        'maior_faturamento' =>
            'Receita do produto com maior faturamento no periodo.',

        'menor_faturamento' =>
            'Receita do produto com menor faturamento entre os vendidos.',

        'estoque_parado' =>
            'Produtos com saldo positivo e nenhuma venda no periodo.',

        'cobertura' =>
            'Produtos com cobertura calculada a partir do saldo disponível e da média diária de vendas.',
    ];

    foreach ($cards as &$card) {
        $card['descricao'] = $descricoesBI08[$card['id']] ?? '';
    }
    unset($card);
?>

<div class="container-fluid px-4 py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-boxes me-2"></i>
                BI - Produtos & Estoque
            </h2>
            <div class="text-muted">
                Giro, Curva ABC, estoque parado, cobertura e ruptura.
            </div>
        </div>

        <a href="<?php echo e(route('bi.index')); ?>"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Central BI
        </a>
    </div>

    

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET"
                  action="<?php echo e(route('bi.produtos.index')); ?>"
                  class="row g-3 align-items-end">

                <div class="col-md-2">
                    <label class="form-label">Data inicial</label>
                    <input type="date"
                           name="data_inicio"
                           value="<?php echo e($filtros['data_inicio']); ?>"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Data final</label>
                    <input type="date"
                           name="data_fim"
                           value="<?php echo e($filtros['data_fim']); ?>"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Produto / SKU</label>
                    <input type="text"
                           name="q"
                           value="<?php echo e($filtros['q'] ?? ''); ?>"
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-select">
                        <option value="">Todas</option>
                        <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($categoria->id); ?>"
                                <?php if((string)($filtros['categoria_id'] ?? '') === (string)$categoria->id): echo 'selected'; endif; ?>>
                                <?php echo e($categoria->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Fornecedor</label>
                    <select name="fornecedor_id" class="form-select">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $fornecedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fornecedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($fornecedor->id); ?>"
                                <?php if((string)($filtros['fornecedor_id'] ?? '') === (string)$fornecedor->id): echo 'selected'; endif; ?>>
                                <?php echo e($fornecedor->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Marca</label>
                    <select name="marca_id" class="form-select">
                        <option value="">Todas</option>
                        <?php $__currentLoopData = $marcas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $marca): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($marca->id); ?>"
                                <?php if((string)($filtros['marca_id'] ?? '') === (string)$marca->id): echo 'selected'; endif; ?>>
                                <?php echo e($marca->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Promocao</label>
                    <select name="promocao" class="form-select">
                        <option value="">Todas</option>
                        <option value="1" <?php if(($filtros['promocao'] ?? '') === '1'): echo 'selected'; endif; ?>>Sim</option>
                        <option value="0" <?php if(($filtros['promocao'] ?? '') === '0'): echo 'selected'; endif; ?>>Nao</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>
                        Aplicar filtros
                    </button>
                    <a href="<?php echo e(route('bi.produtos.index')); ?>"
                       class="btn btn-outline-secondary">
                        Limpar
                    </a>
                </div>

            </form>
        </div>
    </div>

    

    <div class="row jmf-bi-grid g-4 mb-4">

        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-4 col-md-6">
                <div class="card jmf-bi-card h-100 border-<?php echo e($card['cor']); ?>">
                    <div class="card-body">

                        <div class="jmf-bi-card-header">
                            <div class="jmf-bi-card-title">
                                <i class="bi <?php echo e($card['icone']); ?> text-<?php echo e($card['cor']); ?>"></i>
                                <?php echo e($card['titulo']); ?>

                            </div>
                        </div>

                        <div class="jmf-bi-card-value">
                            <?php echo e($card['valor']); ?>

                        </div>

                        <div class="jmf-bi-card-footer">
                            <div class="jmf-bi-card-description mb-2">
                                <?php echo e($card['descricao']); ?>

                            </div>
                            <a href="#"
                               data-bs-toggle="modal"
                               data-bs-target="#bi08Modal<?php echo e($card['id']); ?>"
                               class="text-<?php echo e($card['cor']); ?> text-decoration-none fw-semibold">
                                Ver detalhes
                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

    

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>
                <i class="bi bi-bar-chart-line me-2"></i>
                Curva ABC por faturamento
            </strong>
            <a href="#"
               data-bs-toggle="modal"
               data-bs-target="#bi08ModalABC"
               class="text-decoration-none">
                Ver detalhes
                <i class="bi bi-box-arrow-up-right ms-1"></i>
            </a>
        </div>

        <div class="card-body">
            <?php
                $abcA = (int)$dados['abc']['classe_a'];
                $abcB = (int)$dados['abc']['classe_b'];
                $abcC = (int)$dados['abc']['classe_c'];
                $abcTotal = max(1, $abcA + $abcB + $abcC);
            ?>

            <div class="row g-4">

                <?php $__currentLoopData = [
                    ['nome'=>'Classe A','valor'=>$abcA,'cor'=>'success'],
                    ['nome'=>'Classe B','valor'=>$abcB,'cor'=>'warning'],
                    ['nome'=>'Classe C','valor'=>$abcC,'cor'=>'secondary']
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <div class="col-md-4">
                        <div class="fw-semibold mb-2">
                            <?php echo e($classe['nome']); ?>

                            <span class="float-end">
                                <?php echo e($numero($classe['valor'])); ?> produtos
                            </span>
                        </div>

                        <div class="progress" style="height:22px">
                            <div class="progress-bar bg-<?php echo e($classe['cor']); ?>"
                                 style="width:<?php echo e(($classe['valor'] / $abcTotal) * 100); ?>%">
                            </div>
                        </div>

                        <div class="text-muted mt-2">
                            <?php echo e(number_format($classe['valor'] / $abcTotal * 100, 2, ',', '.')); ?>%
                            dos produtos classificados
                        </div>
                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>

            <p class="text-muted mt-3 mb-0">
                Classificacao por faturamento acumulado:
                A ate 80%, B ate 95% e C restante.
                As barras representam a proporcao de produtos de cada classe.
            </p>
        </div>
    </div>

    

    <div class="row g-4 mb-4">

        <?php $__currentLoopData = $rankings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chave => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $lista = collect($dados[$chave] ?? []);
                $maximo = max(
                    1,
                    (float)$lista->max(
                        $config['tipo'] === 'moeda'
                            ? 'faturamento'
                            : 'quantidade_vendida'
                    )
                );
            ?>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong><?php echo e($config['titulo']); ?></strong>
                        <a href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#bi08Modal<?php echo e($chave); ?>"
                           class="text-decoration-none">
                            Ver detalhes
                        </a>
                    </div>

                    <div class="card-body">

                        <?php $__empty_1 = true; $__currentLoopData = $lista->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $valorGrafico = (float)(
                                    $config['tipo'] === 'moeda'
                                        ? $produto->faturamento
                                        : $produto->quantidade_vendida
                                );
                            ?>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between gap-2 mb-1">
                                    <span class="text-truncate">
                                        <?php echo e($produto->nome); ?>

                                    </span>
                                    <strong class="text-nowrap">
                                        <?php echo e($config['tipo'] === 'moeda'
                                            ? $moeda($valorGrafico)
                                            : $decimal($valorGrafico)); ?>

                                    </strong>
                                </div>
                                <div class="progress" style="height:12px">
                                    <div class="progress-bar bg-<?php echo e($config['cor']); ?>"
                                         style="width:<?php echo e(min(100, max(0, $valorGrafico / $maximo * 100))); ?>%">
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-muted text-center py-4">
                                Sem dados no periodo.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

    

    <div class="alert alert-warning">
        <i class="bi bi-lock me-2"></i>
        <strong>Margem e valor agregado:</strong>
        indicador ainda nao publicado.
        O custo historico das vendas precisa ser homologado
        antes do calculo de margem e lucro.
    </div>

    <div class="text-muted small mb-4">
        Periodo analitico:
        <?php echo e($dados['periodo']['inicio']->format('d/m/Y')); ?>

        ate
        <?php echo e($dados['periodo']['fim']->format('d/m/Y')); ?>.
        Estoque fisico calculado pela posicao atual consolidada dos lotes.
    </div>

</div>



<?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<div class="modal fade jmf-bi-modal"
     id="bi08Modal<?php echo e($card['id']); ?>"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi <?php echo e($card['icone']); ?> me-2"></i>
                    <?php echo e($card['titulo']); ?>

                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-<?php echo e($card['cor']); ?>">
                            <div class="card-body text-center">
                                <div class="text-muted">Indicador</div>
                                <h3 class="mb-0"><?php echo e($card['valor']); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-muted">Periodo analisado</div>
                                <h5 class="mb-0">
                                    <?php echo e($dados['periodo']['inicio']->format('d/m/Y')); ?>

                                    a
                                    <?php echo e($dados['periodo']['fim']->format('d/m/Y')); ?>

                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($card['id'] === 'estoque_zero'): ?>

                    

                    <?php
                        $semEstoque = collect(
                            $dados['sem_estoque_completo'] ?? []
                        );

                        $totalZerados = $semEstoque->filter(
                            fn($p) => (float)$p->estoque_disponivel == 0
                        )->count();

                        $totalNegativos = $semEstoque->filter(
                            fn($p) => (float)$p->estoque_disponivel < 0
                        )->count();
                    ?>

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <div class="card border-danger h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted">
                                        Total sem estoque
                                    </div>
                                    <h3 class="fw-bold text-danger mb-0">
                                        <?php echo e($numero($semEstoque->count())); ?>

                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-warning h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted">
                                        Disponivel = 0
                                    </div>
                                    <h3 class="fw-bold mb-0">
                                        <?php echo e($numero($totalZerados)); ?>

                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-danger h-100">
                                <div class="card-body text-center">
                                    <div class="text-muted">
                                        Disponivel negativo
                                    </div>
                                    <h3 class="fw-bold text-danger mb-0">
                                        <?php echo e($numero($totalNegativos)); ?>

                                    </h3>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-hover align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th>SKU</th>
                                    <th>Categoria</th>
                                    <th>Fornecedor</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Reservado</th>
                                    <th class="text-end">Disponivel</th>
                                    <th>Ultima venda</th>
                                    <th>Situacao</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $semEstoque; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $saldo = (float)$produto->estoque_disponivel;
                                    ?>

                                    <tr>
                                        <td><?php echo e($produto->id); ?></td>
                                        <td class="fw-semibold">
                                            <?php echo e($produto->nome); ?>

                                        </td>
                                        <td><?php echo e($produto->sku ?? '-'); ?></td>
                                        <td><?php echo e($produto->categoria ?? '-'); ?></td>
                                        <td><?php echo e($produto->fornecedor ?? '-'); ?></td>

                                        <td class="text-end">
                                            <?php echo e($decimal($produto->estoque_total)); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($decimal($produto->estoque_reservado)); ?>

                                        </td>

                                        <td class="text-end fw-bold">
                                            <?php echo e($decimal($saldo)); ?>

                                        </td>

                                        
                                        <td class="text-nowrap">
                                            <?php echo e($produto->ultima_venda
                                                ? \Carbon\Carbon::parse($produto->ultima_venda)->format('d/m/Y')
                                                : 'Nunca vendeu'); ?>

                                        </td>

                                        <td>
                                            <?php if($saldo < 0): ?>
                                                <span class="badge bg-danger">
                                                    Negativo
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">
                                                    Zerado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="10"
                                            class="text-center text-muted py-4">
                                            Nenhum produto sem estoque encontrado.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="5">
                                        TOTAL GERAL -
                                        <?php echo e($numero($semEstoque->count())); ?>

                                        PRODUTOS
                                    </td>

                                    <td class="text-end">
                                        <?php echo e($decimal($semEstoque->sum('estoque_total'))); ?>

                                    </td>

                                    <td class="text-end">
                                        <?php echo e($decimal($semEstoque->sum('estoque_reservado'))); ?>

                                    </td>

                                    <td class="text-end">
                                        <?php echo e($decimal($semEstoque->sum('estoque_disponivel'))); ?>

                                    </td>

                                    <td>-</td>
                                    <td>
                                        <?php echo e($numero($totalZerados)); ?> Z /
                                        <?php echo e($numero($totalNegativos)); ?> N
                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3 flex-wrap">

                        <a href="<?php echo e(route('bi.produtos.estoque-zero', $filtros)); ?>"
                           class="btn btn-outline-primary">
                            <i class="bi bi-list-ul me-1"></i>
                            Relatorio completo
                        </a>

                        <a href="<?php echo e(route('bi.produtos.estoque-zero.pdf', $filtros)); ?>"
                           class="btn btn-outline-danger">
                            <i class="bi bi-file-earmark-pdf me-1"></i>
                            Exportar PDF
                        </a>

                        <a href="<?php echo e(route('bi.produtos.estoque-zero.planilha', $filtros)); ?>"
                           class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                            Exportar CSV
                        </a>

                    </div>

                <?php elseif(isset($rankings[$card['id']])): ?>

                    <?php
                        $registros = collect($dados[$card['id']] ?? []);
                        $totalQuantidade = $registros->sum('quantidade_vendida');
                        $totalFaturamento = $registros->sum('faturamento');
                    ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-end">Quantidade vendida</th>
                                    <th class="text-end">Faturamento</th>
                                    <th class="text-end">Estoque</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($produto->nome); ?></td>
                                        <td class="text-end">
                                            <?php echo e($decimal($produto->quantidade_vendida ?? 0)); ?>

                                        </td>
                                        <td class="text-end">
                                            <?php echo e(isset($produto->faturamento)
                                                ? $moeda($produto->faturamento)
                                                : '-'); ?>

                                        </td>
                                        <td class="text-end">
                                            <?php echo e(isset($produto->estoque_disponivel)
                                                ? $decimal($produto->estoque_disponivel)
                                                : '-'); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Nenhum produto encontrado.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>TOTAL GERAL DO RANKING</td>
                                    <td class="text-end">
                                        <?php echo e($decimal($totalQuantidade)); ?>

                                    </td>
                                    <td class="text-end">
                                        <?php echo e($registros->contains(fn($p) => isset($p->faturamento))
                                            ? $moeda($totalFaturamento)
                                            : '-'); ?>

                                    </td>
                                    <td class="text-end">-</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="text-muted small mt-2">
                        Totais referentes exclusivamente aos produtos retornados
                        neste ranking, nao ao catalogo completo.
                    </p>

                                <?php elseif($card['id'] === 'cobertura'): ?>

                    

                    <?php
                        $produtosCobertura = collect(
                            $dados['cobertura']['todos'] ?? []
                        );

                        // Faixas referenciais provisórias.
                        // Não representam configuração homologada.
                        $limitesCobertura = [
                            'critica' => 7,
                            'atencao' => 15,
                            'adequada' => 60,
                            'elevada' => 180,
                        ];

                        $classificarCobertura = function($dias)
                            use ($limitesCobertura) {

                            if ($dias === null) {
                                return [
                                    'nome' => 'Sem cálculo',
                                    'cor' => 'secondary'
                                ];
                            }

                            $dias = (float)$dias;

                            if ($dias <= $limitesCobertura['critica']) {
                                return [
                                    'nome' => 'Crítica',
                                    'cor' => 'danger'
                                ];
                            }

                            if ($dias <= $limitesCobertura['atencao']) {
                                return [
                                    'nome' => 'Atenção',
                                    'cor' => 'warning'
                                ];
                            }

                            if ($dias <= $limitesCobertura['adequada']) {
                                return [
                                    'nome' => 'Adequada',
                                    'cor' => 'success'
                                ];
                            }

                            if ($dias <= $limitesCobertura['elevada']) {
                                return [
                                    'nome' => 'Elevada',
                                    'cor' => 'info'
                                ];
                            }

                            return [
                                'nome' => 'Excessiva',
                                'cor' => 'secondary'
                            ];
                        };

                        $gruposCobertura = $produtosCobertura
                            ->groupBy(function($produto)
                                use ($classificarCobertura) {

                                return $classificarCobertura(
                                    $produto->cobertura_dias
                                )['nome'];
                            });

                        $totalCobertura = $produtosCobertura->count();

                        $qtdCritica =
                            $gruposCobertura->get('Crítica', collect())->count();

                        $qtdAtencao =
                            $gruposCobertura->get('Atenção', collect())->count();

                        $qtdAdequada =
                            $gruposCobertura->get('Adequada', collect())->count();

                        $qtdElevada =
                            $gruposCobertura->get('Elevada', collect())->count();

                        $qtdExcessiva =
                            $gruposCobertura->get('Excessiva', collect())->count();

                        $totalClassificado =
                            $qtdCritica +
                            $qtdAtencao +
                            $qtdAdequada +
                            $qtdElevada +
                            $qtdExcessiva;
                    ?>

                    <div class="alert alert-info">
                        <strong>O que significa cobertura?</strong>
                        É a estimativa de quantos dias o estoque
                        disponível atenderá à demanda, considerando
                        a média diária de vendas do período.

                        <div class="mt-2">
                            <strong>Fórmula:</strong>
                            Estoque disponível / Média diária vendida.
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Classificação provisória:</strong>
                        até 7 dias: Crítica;
                        acima de 7 até 15: Atenção;
                        acima de 15 até 60: Adequada;
                        acima de 60 até 180: Elevada;
                        acima de 180: Excessiva.

                        <div class="mt-1">
                            Esses limites ainda não foram homologados
                            como regra de negócio da empresa.
                        </div>
                    </div>

                    

                    <div class="row g-3 mb-4">

                        <?php $__currentLoopData = [
                            ['titulo'=>'Analisados',
                             'valor'=>$totalCobertura,
                             'cor'=>'primary'],

                            ['titulo'=>'Crítica',
                             'valor'=>$qtdCritica,
                             'cor'=>'danger'],

                            ['titulo'=>'Atenção',
                             'valor'=>$qtdAtencao,
                             'cor'=>'warning'],

                            ['titulo'=>'Adequada',
                             'valor'=>$qtdAdequada,
                             'cor'=>'success'],

                            ['titulo'=>'Elevada',
                             'valor'=>$qtdElevada,
                             'cor'=>'info'],

                            ['titulo'=>'Excessiva',
                             'valor'=>$qtdExcessiva,
                             'cor'=>'secondary']
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indicador): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <div class="col-md-4 col-lg-2">
                                <div class="card border-<?php echo e($indicador['cor']); ?> h-100">
                                    <div class="card-body text-center">
                                        <div class="small text-muted">
                                            <?php echo e($indicador['titulo']); ?>

                                        </div>

                                        <h4 class="fw-bold mb-0">
                                            <?php echo e($numero($indicador['valor'])); ?>

                                        </h4>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </div>

                    

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered
                                      table-striped table-hover align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>Produto</th>
                                    <th>Fornecedor</th>
                                    <th class="text-end">
                                        Disponível
                                    </th>
                                    <th class="text-end">
                                        Mínimo
                                    </th>
                                    <th class="text-end">
                                        Vendido
                                    </th>
                                    <th class="text-end">
                                        Média/dia
                                    </th>
                                    <th class="text-end">
                                        Cobertura
                                    </th>
                                    <th>Última venda</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php $__empty_1 = true; $__currentLoopData = $produtosCobertura; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                                    <?php
                                        $dias = $produto->cobertura_dias;
                                        $situacao = $classificarCobertura($dias);

                                        $ultimaVenda = $produto->ultima_venda
                                            ? \Carbon\Carbon::parse(
                                                $produto->ultima_venda
                                            )->format('d/m/Y')
                                            : 'Nunca vendeu';
                                    ?>

                                    <tr>
                                        <td class="fw-semibold">
                                            <?php echo e($produto->nome); ?>

                                        </td>

                                        <td>
                                            <?php echo e($produto->fornecedor ?? '-'); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($decimal($produto->estoque_disponivel)); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($decimal($produto->estoque_minimo)); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e($decimal($produto->quantidade_vendida)); ?>

                                        </td>

                                        <td class="text-end">
                                            <?php echo e(number_format(
                                                (float)$produto->media_diaria_venda,
                                                4, ',', '.'
                                            )); ?>

                                        </td>

                                        <td class="text-end fw-bold">
                                            <?php echo e($dias === null
                                                ? '-'
                                                : number_format(
                                                    (float)$dias, 1, ',', '.'
                                                ).' dias'); ?>

                                        </td>

                                        <td class="text-nowrap">
                                            <?php echo e($ultimaVenda); ?>

                                        </td>

                                        <td>
                                            <span class="badge bg-<?php echo e($situacao['cor']); ?>

                                                <?php echo e($situacao['cor'] === 'warning'
                                                   || $situacao['cor'] === 'info'
                                                   ? 'text-dark'
                                                   : ''); ?>">
                                                <?php echo e($situacao['nome']); ?>

                                            </span>
                                        </td>
                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9"
                                            class="text-center text-muted py-4">
                                            Nenhum produto com cobertura calculável
                                            encontrado no período.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            </tbody>

                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="8">
                                        TOTAL GERAL DE PRODUTOS CLASSIFICADOS
                                    </td>

                                    <td>
                                        <?php echo e($numero($totalClassificado)); ?>

                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>

                    <div class="small text-muted mt-3">
                        São apresentados os produtos com estoque disponível
                        positivo e vendas positivas no período.
                        Produtos sem vendas não possuem média diária
                        suficiente para este cálculo e permanecem
                        identificados pelo indicador Estoque Parado.

                        <div class="mt-2">
                            Cobertura não equivale à data garantida
                            de ruptura. Prazo do fornecedor e estoque
                            de segurança ainda não participam deste cálculo.
                        </div>
                    </div>

                <?php else: ?>

                    <div class="text-center py-3">

                        <?php if($card['id'] === 'estoque_parado'): ?>
                            <p>
                                Produtos com saldo positivo e nenhuma venda
                                no periodo selecionado.
                            </p>
                        <?php elseif($card['id'] === 'estoque_zero'): ?>
                            <p>
                                Produtos com saldo consolidado dos lotes
                                igual ou menor que zero.
                            </p>
                        <?php else: ?>
                            <p>
                                Consulte o relatorio analitico deste indicador,
                                preservado no ERP.
                            </p>
                        <?php endif; ?>

                        <?php if($card['rota']): ?>
                            <a href="<?php echo e(route($card['rota'], $filtros)); ?>"
                               class="btn btn-outline-primary">
                                <i class="bi bi-list-ul me-1"></i>
                                Abrir relatorio completo
                            </a>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



<div class="modal fade jmf-bi-modal"
     id="bi08ModalABC"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Curva ABC por faturamento</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <?php
                    $rankingABC = collect($dados['abc']['ranking']);
                    $totalABC = $rankingABC->sum('faturamento');
                ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Classe</th>
                                <th class="text-end">Faturamento</th>
                                <th class="text-end">Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $rankingABC; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($produto->nome); ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary">
                                            <?php echo e($produto->abc); ?>

                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php echo e($moeda($produto->faturamento)); ?>

                                    </td>
                                    <td class="text-end">
                                        <?php echo e(number_format((float)$produto->percentual_acumulado,2,',','.')); ?>%
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Nenhum dado encontrado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>TOTAL GERAL</td>
                                <td><?php echo e($numero($rankingABC->count())); ?> produtos</td>
                                <td class="text-end"><?php echo e($moeda($totalABC)); ?></td>
                                <td class="text-end">
                                    <?php echo e($totalABC > 0 ? '100,00%' : '0,00%'); ?>

                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/bi/produtos/index.blade.php ENDPATH**/ ?>