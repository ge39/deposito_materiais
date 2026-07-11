

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-truck me-2"></i>Cadastro de Veículos
            </h2>
            <p class="text-muted mb-0">
                Gestão da frota própria, agregada e terceirizada para apoio à expedição e logística.
            </p>
        </div>

        <a href="<?php echo e(route('veiculos.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Novo Veículo
        </a>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>Corrija os campos abaixo:</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Total</span>
                        <h4 class="fw-bold mb-0"><?php echo e($kpis['total'] ?? 0); ?></h4>
                    </div>
                    <i class="bi bi-truck fs-2 text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Ativos</span>
                        <h4 class="fw-bold mb-0"><?php echo e($kpis['ativos'] ?? 0); ?></h4>
                    </div>
                    <i class="bi bi-check-circle fs-2 text-success"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Disponíveis</span>
                        <h4 class="fw-bold mb-0"><?php echo e($kpis['disponiveis'] ?? 0); ?></h4>
                    </div>
                    <i class="bi bi-geo-alt fs-2 text-info"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Em operação</span>
                        <h4 class="fw-bold mb-0"><?php echo e($kpis['em_operacao'] ?? 0); ?></h4>
                    </div>
                    <i class="bi bi-box-seam fs-2 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Manutenção</span>
                        <h4 class="fw-bold mb-0"><?php echo e($kpis['manutencao'] ?? 0); ?></h4>
                    </div>
                    <i class="bi bi-tools fs-2 text-danger"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Pendências</span>
                        <h4 class="fw-bold mb-0"><?php echo e($kpis['pendencia_documental'] ?? 0); ?></h4>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-2 text-secondary"></i>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-funnel me-2"></i>Filtros de Consulta
            </span>
        </div>

        <div class="card-body">
            <form method="GET" action="<?php echo e(route('veiculos.index')); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Busca</label>
                    <input type="text"
                           name="busca"
                           value="<?php echo e(request('busca')); ?>"
                           class="form-control"
                           placeholder="Placa, modelo, marca, proprietário...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="Ativo" <?php if(request('status') === 'Ativo'): echo 'selected'; endif; ?>>Ativo</option>
                        <option value="Inativo" <?php if(request('status') === 'Inativo'): echo 'selected'; endif; ?>>Inativo</option>
                        <option value="Manutencao" <?php if(request('status') === 'Manutencao'): echo 'selected'; endif; ?>>Manutenção</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Disponibilidade</label>
                    <select name="disponibilidade" class="form-select">
                        <option value="">Todas</option>
                        <option value="Disponivel" <?php if(request('disponibilidade') === 'Disponivel'): echo 'selected'; endif; ?>>Disponível</option>
                        <option value="Reservado" <?php if(request('disponibilidade') === 'Reservado'): echo 'selected'; endif; ?>>Reservado</option>
                        <option value="Carregando" <?php if(request('disponibilidade') === 'Carregando'): echo 'selected'; endif; ?>>Carregando</option>
                        <option value="Em_rota" <?php if(request('disponibilidade') === 'Em_rota'): echo 'selected'; endif; ?>>Em rota</option>
                        <option value="Manutencao" <?php if(request('disponibilidade') === 'Manutencao'): echo 'selected'; endif; ?>>Manutenção</option>
                        <option value="Indisponivel" <?php if(request('disponibilidade') === 'Indisponivel'): echo 'selected'; endif; ?>>Indisponível</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tipo Frota</label>
                    <select name="tipo_frota" class="form-select">
                        <option value="">Todos</option>
                        <option value="Frota" <?php if(request('tipo_frota') === 'Frota'): echo 'selected'; endif; ?>>Frota</option>
                        <option value="Agregado" <?php if(request('tipo_frota') === 'Agregado'): echo 'selected'; endif; ?>>Agregado</option>
                        <option value="Terceirizado" <?php if(request('tipo_frota') === 'Terceirizado'): echo 'selected'; endif; ?>>Terceirizado</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Operação</label>
                    <select name="operacao_preferencial" class="form-select">
                        <option value="">Todas</option>
                        <option value="Urbana" <?php if(request('operacao_preferencial') === 'Urbana'): echo 'selected'; endif; ?>>Urbana</option>
                        <option value="Rodoviaria" <?php if(request('operacao_preferencial') === 'Rodoviaria'): echo 'selected'; endif; ?>>Rodoviária</option>
                        <option value="Mista" <?php if(request('operacao_preferencial') === 'Mista'): echo 'selected'; endif; ?>>Mista</option>
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>

                        <a href="<?php echo e(route('veiculos.index')); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-list-check me-2"></i>Veículos Cadastrados
            </span>

            <span class="badge bg-light text-dark">
                <?php echo e($veiculos->total()); ?> registro(s)
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Tipo</th>
                            <th>Capacidade</th>
                            <th>Motorista</th>
                            <th>Operação</th>
                            <th>Status</th>
                            <th>Docs</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                       <?php $__empty_1 = true; $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>

    <td>
        <div class="fw-bold"><?php echo e($veiculo->placa); ?></div>

        <div class="text-muted small">
            <?php echo e(trim(($veiculo->marca ?? '') . ' ' . ($veiculo->modelo ?? '')) ?: 'Modelo não informado'); ?>

        </div>

        <div class="text-muted small">
            <?php echo e($veiculo->ano_fabricacao ?? '-'); ?>

            ·
            <?php echo e($veiculo->cor ?? '-'); ?>

        </div>
    </td>

    <td>

        <span class="badge bg-primary">
            <?php echo e($veiculo->tipo_frota); ?>

        </span>

        <div class="small mt-1 fw-semibold">
            <?php echo e($veiculo->tipoVeiculo->descricao ?? '-'); ?>

        </div>

        <div class="small text-muted">
            <?php echo e($veiculo->classeVeiculo->descricao ?? '-'); ?>

        </div>

        <div class="small text-muted">
            <?php echo e($veiculo->tipoCarroceria->descricao ?? '-'); ?>

        </div>

    </td>

    <td>

        <div class="small">
            <strong>Kg:</strong>

            <?php echo e($veiculo->capacidade_kg
                ? number_format($veiculo->capacidade_kg,2,',','.')
                : '-'); ?>

        </div>

        <div class="small">
            <strong>m³:</strong>

            <?php echo e($veiculo->capacidade_m3
                ? number_format($veiculo->capacidade_m3,2,',','.')
                : '-'); ?>

        </div>

    </td>

    <td>

        <div class="small">
            <strong>Motorista:</strong>

            <?php echo e($veiculo->motoristaPadrao->nome ?? '-'); ?>

        </div>

    </td>

    <td>

        <?php

            $statusClass = match($veiculo->status){

                'Ativo'       => 'success',

                'Inativo'     => 'secondary',

                'Manutenção'  => 'danger',

                default       => 'secondary',

            };

            $disponibilidadeClass = match($veiculo->disponibilidade){

                'Disponível'    => 'success',

                'Reservado'     => 'warning',

                'Carregando'    => 'info',

                'Em rota'       => 'primary',

                'Manutenção'    => 'danger',

                'Indisponível'  => 'secondary',

                default         => 'secondary',

            };

                            ?>

                                    <span class="badge bg-<?php echo e($statusClass); ?>">
                                        <?php echo e($veiculo->status); ?>

                                    </span>

                                    <div class="mt-1">
                                        <span class="badge bg-<?php echo e($disponibilidadeClass); ?>">
                                            <?php echo e($veiculo->disponibilidade); ?>

                                        </span>
                                    </div>

                                </td>

                                <td class="text-end">

                                    <div class="btn-group">

                                        <a href="<?php echo e(route('veiculos.show',$veiculo)); ?>"
                                        class="btn btn-sm btn-outline-info"
                                        title="Visualizar">

                                            <i class="bi bi-eye"></i>

                                        </a>

                                        <a href="<?php echo e(route('veiculos.edit',$veiculo)); ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Editar">

                                            <i class="bi bi-pencil"></i>

                                        </a>

                                        <form
                                            action="<?php echo e(route('veiculos.destroy',$veiculo)); ?>"
                                            method="POST"
                                            onsubmit="return confirm('Deseja remover este veículo?')">

                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-truck fs-1 d-block mb-2"></i>
                                    Nenhum veículo encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($veiculos->hasPages()): ?>
            <div class="card-footer bg-white">
                <?php echo e($veiculos->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/veiculos/index.blade.php ENDPATH**/ ?>