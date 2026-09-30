

<?php $__env->startSection('title', 'Detalhes do Veículo'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-truck-front me-2 text-primary"></i>
                Veículo <?php echo e($veiculo->placa); ?>

            </h1>
            <p class="text-muted mb-0">
                Painel operacional do veículo para expedição, romaneios e inteligência logística.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo e(route('veiculos.edit', $veiculo)); ?>" class="btn btn-primary">
                <i class="bi bi-pencil-square me-1"></i>
                Editar
            </a>

            <a href="<?php echo e(route('veiculos.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-4 align-items-center">

                <div class="col-lg-4">
                    <div class="p-4 rounded-4 bg-primary-subtle border border-primary-subtle h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-4 d-flex align-items-center justify-content-center"
                                 style="width: 64px; height: 64px;">
                                <i class="bi bi-truck-front fs-2"></i>
                            </div>

                            <div>
                                <div class="text-muted small">Placa</div>
                                <div class="h3 fw-bold mb-0"><?php echo e($veiculo->placa); ?></div>
                                <div class="text-muted">
                                    <?php echo e($veiculo->marca ?? 'Marca não informada'); ?>

                                    <?php echo e($veiculo->modelo ?? ''); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 h-100">
                                <small class="text-muted">Status</small>
                                <div class="mt-1">
                                    <?php
                                        $status = $veiculo->status ?? 'Ativo';

                                        $statusClass = match($status) {
                                            'Ativo' => 'success',
                                            'Manutenção' => 'warning',
                                            'Inativo' => 'secondary',
                                            'Indisponível' => 'danger',
                                            default => 'secondary',
                                        };
                                    ?>

                                    <span class="badge bg-<?php echo e($statusClass); ?>-subtle text-<?php echo e($statusClass); ?> border border-<?php echo e($statusClass); ?>-subtle px-3 py-2">
                                        <?php echo e($status); ?>

                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 h-100">
                                <small class="text-muted">Tipo</small>
                                <div class="fw-bold mt-1"><?php echo e($veiculo->tipo ?? 'Não informado'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 h-100">
                                <small class="text-muted">CNH exigida</small>
                                <div class="fw-bold mt-1"><?php echo e($veiculo->categoria_cnh ?? 'Não definida'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 h-100">
                                <small class="text-muted">Ano / Cor</small>
                                <div class="fw-bold mt-1">
                                    <?php echo e($veiculo->ano_fabricacao ?? '----'); ?>

                                    ·
                                    <?php echo e($veiculo->cor ?? 'Sem cor'); ?>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Capacidade de peso</small>
                    <div class="h4 fw-bold mb-0">
                        <?php echo e(number_format($veiculo->capacidade_kg ?? 0, 2, ',', '.')); ?> kg
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-2">
                        Peso operacional
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Volume</small>
                    <div class="h4 fw-bold mb-0">
                        <?php echo e(number_format($veiculo->capacidade_m3 ?? 0, 2, ',', '.')); ?> m³
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info-subtle mt-2">
                        Cubagem
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Paletes</small>
                    <div class="h4 fw-bold mb-0">
                        <?php echo e($veiculo->capacidade_paletes ?? 0); ?>

                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle mt-2">
                        Paletização
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Unidades</small>
                    <div class="h4 fw-bold mb-0">
                        <?php echo e($veiculo->capacidade_unidades ?? 0); ?>

                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-2">
                        Carga unitária
                    </span>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-card-text me-2 text-primary"></i>
                        Dados Cadastrais
                    </h6>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <small class="text-muted">Marca</small>
                            <div class="fw-semibold"><?php echo e($veiculo->marca ?? 'Não informada'); ?></div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Modelo</small>
                            <div class="fw-semibold"><?php echo e($veiculo->modelo ?? 'Não informado'); ?></div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Chassi</small>
                            <div class="fw-semibold"><?php echo e($veiculo->chassi ?? 'Não informado'); ?></div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Renavam</small>
                            <div class="fw-semibold"><?php echo e($veiculo->renavam ?? 'Não informado'); ?></div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Motorista padrão</small>
                            <div class="fw-semibold">
                                <?php echo e($veiculo->motoristaPadrao->nome ?? 'Sem motorista padrão'); ?>

                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted">Cadastrado em</small>
                            <div class="fw-semibold">
                                <?php echo e(optional($veiculo->created_at)->format('d/m/Y H:i') ?? 'Não informado'); ?>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-rulers me-2 text-info"></i>
                        Dimensões
                    </h6>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 text-center">
                                <small class="text-muted">Comprimento</small>
                                <div class="h5 fw-bold mb-0">
                                    <?php echo e(number_format($veiculo->comprimento_m ?? 0, 2, ',', '.')); ?> m
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 text-center">
                                <small class="text-muted">Largura</small>
                                <div class="h5 fw-bold mb-0">
                                    <?php echo e(number_format($veiculo->largura_m ?? 0, 2, ',', '.')); ?> m
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 text-center">
                                <small class="text-muted">Altura</small>
                                <div class="h5 fw-bold mb-0">
                                    <?php echo e(number_format($veiculo->altura_m ?? 0, 2, ',', '.')); ?> m
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="alert alert-info border-0 mt-4 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Essas medidas poderão ser usadas futuramente para restrições de acesso, altura máxima e roteirização.
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-tools me-2 text-warning"></i>
                        Recursos Operacionais
                    </h6>
                </div>

                <div class="card-body">
                    <?php
                        $recursos = [
                            'possui_munck' => ['label' => 'Munck', 'icone' => 'bi-truck'],
                            'possui_carroceria_aberta' => ['label' => 'Carroceria aberta', 'icone' => 'bi-box-arrow-up'],
                            'possui_carroceria_fechada' => ['label' => 'Carroceria fechada', 'icone' => 'bi-box'],
                            'possui_rastreador' => ['label' => 'Rastreador', 'icone' => 'bi-geo-alt'],
                        ];
                    ?>

                    <div class="row g-3">
                        <?php $__currentLoopData = $recursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $dados): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100 <?php echo e($veiculo->$campo ? 'bg-success-subtle border-success-subtle' : 'bg-light'); ?>">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi <?php echo e($dados['icone']); ?> fs-5 <?php echo e($veiculo->$campo ? 'text-success' : 'text-muted'); ?>"></i>
                                        <div class="fw-semibold"><?php echo e($dados['label']); ?></div>
                                    </div>

                                    <small class="<?php echo e($veiculo->$campo ? 'text-success' : 'text-muted'); ?>">
                                        <?php echo e($veiculo->$campo ? 'Disponível' : 'Não disponível'); ?>

                                    </small>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam me-2 text-danger"></i>
                        Tipos de Carga Aceitos
                    </h6>
                </div>

                <div class="card-body">
                    <?php
                        $cargas = [
                            'aceita_areia_pedra' => 'Areia / Pedra',
                            'aceita_blocos_tijolos' => 'Blocos / Tijolos',
                            'aceita_cimento_argamassa' => 'Cimento / Argamassa',
                            'aceita_tintas_quimicos' => 'Tintas / Químicos',
                            'aceita_madeiras' => 'Madeiras',
                            'aceita_ferragens' => 'Ferragens',
                            'aceita_pisos_revestimentos' => 'Pisos / Revestimentos',
                            'aceita_hidraulica_eletrica' => 'Hidráulica / Elétrica',
                        ];
                    ?>

                    <div class="d-flex flex-wrap gap-2">
                        <?php $__currentLoopData = $cargas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campo => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($veiculo->$campo): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?php echo e($label); ?>

                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border px-3 py-2">
                                    <i class="bi bi-x-circle me-1"></i>
                                    <?php echo e($label); ?>

                                </span>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-cpu me-2 text-primary"></i>
                        Inteligência Logística
                    </h6>

                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                        Evolução futura
                    </span>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-3">
                        Este veículo já está estruturado para participar das próximas etapas da inteligência logística.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="fw-bold mb-1">
                                    <i class="bi bi-signpost-split me-1 text-primary"></i>
                                    Roteirização
                                </div>
                                <small class="text-muted">
                                    Futuramente poderá sugerir rotas considerando bairro, região, endereço e capacidade do veículo.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="fw-bold mb-1">
                                    <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                                    Restrições de acesso
                                </div>
                                <small class="text-muted">
                                    Preparado para controle de altura, peso, zonas de restrição e rodízio.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="fw-bold mb-1">
                                    <i class="bi bi-boxes me-1 text-success"></i>
                                    Compatibilidade de carga
                                </div>
                                <small class="text-muted">
                                    As cargas aceitas ajudam a evitar seleção incorreta do veículo no romaneio.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="fw-bold mb-1">
                                    <i class="bi bi-calendar-check me-1 text-info"></i>
                                    Planejamento operacional
                                </div>
                                <small class="text-muted">
                                    Futuramente poderá cruzar disponibilidade, data prevista e agrupamento por regiões.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-activity me-2 text-secondary"></i>
                        Situação Operacional
                    </h6>
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Disponibilidade</small>
                        <div class="fw-bold">
                            <?php if(($veiculo->status ?? 'Ativo') === 'Ativo'): ?>
                                <span class="text-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Disponível para operação
                                </span>
                            <?php else: ?>
                                <span class="text-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Não disponível
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <small class="text-muted">Última atualização</small>
                        <div class="fw-semibold">
                            <?php echo e(optional($veiculo->updated_at)->format('d/m/Y H:i') ?? 'Não informado'); ?>

                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Observação</small>
                        <div class="fw-semibold">
                            <?php echo e($veiculo->observacao ?? 'Nenhuma observação registrada.'); ?>

                        </div>
                    </div>

                    <div class="alert alert-light border mb-0">
                        <i class="bi bi-clock-history me-1"></i>
                        Histórico de romaneios, manutenções e entregas poderá ser integrado nesta área.
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/veiculos/show.blade.php ENDPATH**/ ?>