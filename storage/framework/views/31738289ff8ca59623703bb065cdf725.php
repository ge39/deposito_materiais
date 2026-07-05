

<?php $__env->startSection('content'); ?>

<style>
      .entrega-checkbox{
        cursor: pointer;
    }
    #tabelaEntregasRomaneio.table-hover tbody tr:hover > * {
        background-color: #cff4fc !important; /* bg-info suave */
    }

    #tabelaEntregasRomaneio tbody tr.linha-selecionada > * {
        background-color: #d6e9ff !important;
    }
    .kpi-card {
        min-height: 86px;
        border-width: 3px !important;
        border-radius: 6px;
    }

    .kpi-card .card-body {
        padding: 0.85rem 1rem;
    }

    .operational-header {
        background: #6c757d;
        color: #fff;
        padding: 0.45rem 0.75rem;
        font-size: 0.95rem;
        border-radius: 4px 4px 0 0 !important;
    }

    .operational-table-header {
        background: #6c757d;
        color: #fff;
        padding: 0.55rem 0.75rem;
        font-size: 0.95rem;
        border-radius: 4px 4px 0 0 !important;
    }

    #tabelaEntregasRomaneio thead th {
        font-size: 0.9rem;
        white-space: nowrap;
        vertical-align: middle;
    }

    #tabelaEntregasRomaneio tbody td {
        font-size: 0.9rem;
        vertical-align: middle;
    }

    #tabelaEntregasRomaneio .badge {
        font-size: 0.75rem;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
</style>

<div class="container-fluid py-4">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-clipboard-plus me-2"></i>Novo Romaneio
            </h3>
            <small class="text-muted">
                Controle de montagem, conferência e expedição dos romaneios.
            </small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?php echo e(route('expedicao.index')); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>

            <button type="submit" form="formRomaneio" class="btn btn-primary">
                <i class="bi bi-box-seam me-1"></i> Criar Romaneio
            </button>
        </div>
    </div>

    
    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i><?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Verifique os campos:</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
    <div class="row g-2 mb-2">
        <div class="col-md-2">
            <div class="card kpi-card border-secondary">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Romaneio</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Disponíveis</div>
                            <h3 class="fw-bold mb-0"><?php echo e($entregasDisponiveis->count()); ?></h3>
                        </div>
                        <i class="bi bi-clipboard-check fs-2 text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card kpi-card border-primary">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Seleção</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Selecionadas</div>
                            <h3 class="fw-bold mb-0 text-primary" id="contadorSelecionadasTopo">0</h3>
                        </div>
                        <i class="bi bi-check2-square fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card kpi-card border-warning">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Separação</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Separando</div>
                            <h3 class="fw-bold mb-0">
                                <?php echo e($entregasDisponiveis->where('status', 'Separando')->count()); ?>

                            </h3>
                        </div>
                        <i class="bi bi-lightning-charge fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card kpi-card border-info">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Equipe</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Motoristas</div>
                            <h3 class="fw-bold mb-0"><?php echo e($motoristas->count()); ?></h3>
                        </div>
                        <i class="bi bi-person-badge fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card kpi-card border-dark">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Frota</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Veículos</div>
                            <h3 class="fw-bold mb-0"><?php echo e($veiculos->count()); ?></h3>
                        </div>
                        <i class="bi bi-truck-front fs-2 text-dark"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card kpi-card border-success">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Operação</small>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Status</div>
                            <h3 class="fw-bold mb-0 text-success">Novo</h3>
                        </div>
                        <i class="bi bi-check-circle fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="formRomaneio" action="<?php echo e(route('romaneios.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        
        <div class="card shadow-sm mb-2">
            <div class="card-header operational-header">
                <i class="bi bi-truck-front me-2"></i>
                <strong>Dados da Expedição</strong>
            </div>

            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Motorista</label>
                        <select name="motorista_id" class="form-select">
                            <option value="">Selecione o motorista</option>
                            <?php $__currentLoopData = $motoristas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($motorista->id); ?>" <?php if(old('motorista_id') == $motorista->id): echo 'selected'; endif; ?>>
                                    <?php echo e($motorista->nome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Veículo</label>
                        <select name="veiculo_id" class="form-select">
                            <option value="">Selecione o veículo</option>
                            <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($veiculo->id); ?>" <?php if(old('veiculo_id') == $veiculo->id): echo 'selected'; endif; ?>>
                                    <?php echo e($veiculo->descricao ?? $veiculo->nome ?? 'Veículo #' . $veiculo->id); ?>

                                    <?php if(!empty($veiculo->placa)): ?>
                                        - <?php echo e($veiculo->placa); ?>

                                    <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Observação</label>
                        <input type="text"
                               name="observacao"
                               class="form-control"
                               value="<?php echo e(old('observacao')); ?>"
                               placeholder="Observações da expedição, rota, prioridade ou carregamento...">
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card shadow-sm mb-3">
            <div class="card-header operational-header">
                <i class="bi bi-funnel me-2"></i>
                <strong>Filtros</strong>
            </div>

            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Buscar Entrega</label>
                        <input type="text"
                               id="filtroEntregas"
                               class="form-control"
                               placeholder="Cliente, endereço, código ou número da entrega...">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select id="filtroStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="separando">Separando</option>
                            <option value="aguardando_separacao">Aguardando separação</option>
                            <!-- <option value="faturado">Faturado</option> -->
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Data Prevista</label>
                        <input type="date" id="filtroData" class="form-control">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" id="marcarTodas" class="btn btn-primary">
                            <i class="bi bi-check2-square me-1"></i> Marcar Visíveis
                        </button>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" id="limparSelecao" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card shadow-sm">
            <div class="card-header operational-table-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-list-check me-2"></i>
                    <strong>Entregas Disponíveis para Romaneio</strong>
                </div>

                <div class="d-flex gap-2">
                    <span class="badge bg-light text-dark">
                        Total: <?php echo e($entregasDisponiveis->count()); ?>

                    </span>
                    <span class="badge bg-warning text-dark">
                        Separando: <?php echo e($entregasDisponiveis->where('status', 'Separando')->count()); ?>

                    </span>
                    <span class="badge bg-primary">
                        Selecionadas: <span id="contadorSelecionadasTabela">0</span>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
               <table class="table table-bordered table-hover align-middle mb-0" id="tabelaEntregasRomaneio">
    <thead class="table-dark">
        <tr>
            <th style="width: 45px;"></th>
            <th>ID</th>
            <th>Código</th>
            <th>Venda</th>
            <th>Orçamento</th>
            <th>Cliente</th>
            <th>Endereço</th>
            <th class="text-center">Previsão</th>
            <th class="text-center">Itens</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>

    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $entregasDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entrega): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $statusOriginal = $entrega->status ?? 'Não informado';
                $status = strtolower($statusOriginal);

                $dataPrevista = !empty($entrega->data_prevista)
                    ? \Carbon\Carbon::parse($entrega->data_prevista)->format('Y-m-d')
                    : '';

                $classeBadge = match($status) {
                    'separando' => 'bg-warning text-dark',
                    'aguardando_separacao' => 'bg-secondary text-light',
                    'carregado' => 'bg-info text-dark',
                    'em_rota' => 'bg-dark',
                    'entregue' => 'bg-success',
                    'cancelado' => 'bg-danger',
                    default => 'bg-secondary text-light'
                };

                $labelStatus = match($status) {
                    'separando' => 'Separando',
                    'aguardando_separacao' => 'Aguardando separação',
                    'carregado' => 'Carregado',
                    'em_rota' => 'Em rota',
                    'entregue' => 'Entregue',
                    'cancelado' => 'Cancelado',
                    default => ucfirst(str_replace('_', ' ', $statusOriginal))
                };
            ?>

            <tr data-status="<?php echo e($status); ?>"
                data-data="<?php echo e($dataPrevista); ?>"
                data-search="<?php echo e(strtolower(
                    ($entrega->venda_id ?? '') . ' ' .
                    ($entrega->orcamento_id ?? '') . ' ' .
                    ($entrega->orcamento->cliente->nome ?? '') . ' ' .
                    ($entrega->orcamento->cliente->telefone ?? '') . ' ' .
                    ($entrega->endereco_entrega ?? '') . ' ' .
                    ($entrega->codigo_entrega ?? '') . ' ' .
                    $entrega->id . ' ' .
                    ($entrega->status ?? '')
                )); ?>">
                
                <td class="text-center">
                    <input type="checkbox"
                           name="entregas[]"
                           value="<?php echo e($entrega->id); ?>"
                           class="form-check-input entrega-checkbox"
                           <?php if(is_array(old('entregas')) && in_array($entrega->id, old('entregas'))): echo 'checked'; endif; ?>>
                </td>

                <td class="fw-bold text-center">
                    <?php echo e($entrega->id); ?>

                </td>

                <td>
                    <span class="fw-bold"><?php echo e($entrega->codigo_entrega ?? 'Sem código'); ?></span>
                </td>

                <td class="text-center">
                    <span class="fw-semibold"><?php echo e($entrega->venda_id ?? '-'); ?></span>
                </td>

                <td class="text-center">
                    <span class="fw-semibold"><?php echo e($entrega->orcamento_id ?? '-'); ?></span>
                </td>

                <td>
                    <div class="fw-semibold">
                        <?php echo e($entrega->orcamento->cliente->nome ?? 'Cliente não informado'); ?>

                    </div>
                    <small class="text-muted">
                        <?php echo e($entrega->orcamento->cliente->telefone ?? 'Telefone não informado'); ?>

                    </small>
                </td>

                <td>
                    <small><?php echo e($entrega->endereco_entrega ?? 'Endereço não informado'); ?></small>
                </td>

                <td class="text-center">
                    <?php if(!empty($entrega->data_prevista)): ?>
                        <?php echo e(\Carbon\Carbon::parse($entrega->data_prevista)->format('d/m/Y')); ?>

                    <?php else: ?>
                        <span class="text-muted">Não informada</span>
                    <?php endif; ?>
                </td>

                <td class="text-center">
                    <span class="badge bg-light text-dark border">
                        <?php echo e($entrega->itens->count()); ?>

                    </span>
                </td>

                <td class="text-center">
                    <span class="badge <?php echo e($classeBadge); ?>">
                        <?php echo e($labelStatus); ?>

                    </span>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="10" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    Nenhuma entrega disponível para romaneio.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
            </div>

            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    O romaneio será criado somente com as entregas selecionadas.
                </small>

                <div class="d-flex gap-2">
                    <a href="<?php echo e(route('expedicao.index')); ?>" class="btn btn-outline-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Criar Romaneio
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filtroTexto = document.getElementById('filtroEntregas');
        const filtroStatus = document.getElementById('filtroStatus');
        const filtroData = document.getElementById('filtroData');
        const linhas = document.querySelectorAll('#tabelaEntregasRomaneio tbody tr[data-search]');
        const checkboxes = document.querySelectorAll('.entrega-checkbox');
        const contadorTopo = document.getElementById('contadorSelecionadasTopo');
        const contadorTabela = document.getElementById('contadorSelecionadasTabela');
        const botaoMarcarTodas = document.getElementById('marcarTodas');
        const botaoLimpar = document.getElementById('limparSelecao');

        function atualizarContador() {
            const total = document.querySelectorAll('.entrega-checkbox:checked').length;

            if (contadorTopo) contadorTopo.textContent = total;
            if (contadorTabela) contadorTabela.textContent = total;
        }

        function aplicarFiltros() {
            const termo = filtroTexto ? filtroTexto.value.toLowerCase().trim() : '';
            const status = filtroStatus ? filtroStatus.value.toLowerCase().trim() : '';
            const data = filtroData ? filtroData.value : '';

            linhas.forEach(function (linha) {
                const textoLinha = linha.dataset.search || '';
                const statusLinha = linha.dataset.status || '';
                const dataLinha = linha.dataset.data || '';

                const passaTexto = !termo || textoLinha.includes(termo);
                const passaStatus = !status || statusLinha.includes(status);
                const passaData = !data || dataLinha === data;

                linha.style.display = passaTexto && passaStatus && passaData ? '' : 'none';
            });
        }

        function linhasVisiveis() {
            return Array.from(linhas).filter(function (linha) {
                return linha.style.display !== 'none';
            });
        }

        if (filtroTexto) filtroTexto.addEventListener('input', aplicarFiltros);
        if (filtroStatus) filtroStatus.addEventListener('change', aplicarFiltros);
        if (filtroData) filtroData.addEventListener('change', aplicarFiltros);

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', atualizarContador);
        });

        if (botaoMarcarTodas) {
            botaoMarcarTodas.addEventListener('click', function () {
                linhasVisiveis().forEach(function (linha) {
                    const checkbox = linha.querySelector('.entrega-checkbox');

                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                atualizarContador();
            });
        }

        if (botaoLimpar) {
            botaoLimpar.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                atualizarContador();
            });
        }

        atualizarContador();
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/create.blade.php ENDPATH**/ ?>