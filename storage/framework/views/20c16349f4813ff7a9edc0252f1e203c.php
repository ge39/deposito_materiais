

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <?php
        $statusRomaneio = $romaneio->status ?? 'Aberto';
        $progresso = $resumo['progresso'] ?? 0;

        $totalItens = $resumo['total_itens'] ?? 0;
        $pendentes = $resumo['pendentes'] ?? 0;
        $carregando = $resumo['carregando'] ?? 0;
        $carregados = $resumo['carregados'] ?? 0;
        $parciais = $resumo['parciais'] ?? 0;

        $entregasIds = $romaneio->itens
            ->map(fn($item) => $item->entregaItem->entrega->id ?? null)
            ->filter()
            ->unique();

        $clientesIds = $romaneio->itens
            ->map(fn($item) => $item->entregaItem->entrega->cliente->id ?? null)
            ->filter()
            ->unique();

        $totalEntregas = $entregasIds->count();
        $totalClientes = $clientesIds->count();

        $checkSeparacao = in_array($statusRomaneio, ['Em Separação', 'Carregando', 'Carregado', 'Liberado', 'Em Rota', 'Entregue']);
        $checkCarregamento = in_array($statusRomaneio, ['Carregando', 'Carregado', 'Liberado', 'Em Rota', 'Entregue']);
        $checkConferencia = in_array($statusRomaneio, ['Carregado', 'Liberado', 'Em Rota', 'Entregue']);
        $checkLiberado = in_array($statusRomaneio, ['Liberado', 'Em Rota', 'Entregue']);

        $statusBadgeRomaneio = match($statusRomaneio) {
            'Aberto' => 'bg-secondary',
            'Em Separação' => 'bg-warning text-dark',
            'Carregando' => 'bg-primary',
            'Carregado' => 'bg-success',
            'Liberado', 'Em Rota' => 'bg-dark',
            'Entregue' => 'bg-success',
            'Cancelado' => 'bg-danger',
            default => 'bg-secondary',
        };
    ?>

    
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-truck me-2"></i>Operação de Expedição
            </h3>

            <div class="text-muted">
                Romaneio:
                <strong><?php echo e($romaneio->codigo ?? '#' . $romaneio->id); ?></strong>

                <span class="mx-2">|</span>

                Status:
                <span class="badge <?php echo e($statusBadgeRomaneio); ?>">
                    <?php echo e($statusRomaneio); ?>

                </span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo e(route('expedicao.show', $romaneio->id)); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>

            <a href="<?php echo e(route('romaneios.imprimir', $romaneio->id)); ?>" target="_blank" class="btn btn-outline-dark">
                <i class="bi bi-printer"></i> Imprimir
            </a>

            <button type="button" onclick="window.location.reload()" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i> Atualizar
            </button>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success shadow-sm border-0">
            <i class="bi bi-check-circle me-1"></i><?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger shadow-sm border-0">
            <i class="bi bi-exclamation-triangle me-1"></i><?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Itens</small>
                    <h4 class="fw-bold mb-0"><?php echo e($totalItens); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Pendentes</small>
                    <h4 class="fw-bold mb-0 text-secondary"><?php echo e($pendentes); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Carregando</small>
                    <h4 class="fw-bold mb-0 text-primary"><?php echo e($carregando); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Carregados</small>
                    <h4 class="fw-bold mb-0 text-success"><?php echo e($carregados); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Parciais</small>
                    <h4 class="fw-bold mb-0 text-warning"><?php echo e($parciais); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Progresso</small>
                    <h4 class="fw-bold mb-0"><?php echo e($progresso); ?>%</h4>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">

        
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-box-seam me-2"></i>Resumo da Carga
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Entregas</span>
                        <strong><?php echo e($totalEntregas); ?></strong>
                    </div>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Clientes</span>
                        <strong><?php echo e($totalClientes); ?></strong>
                    </div>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Itens</span>
                        <strong><?php echo e($totalItens); ?></strong>
                    </div>

                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Pendentes</span>
                        <strong><?php echo e($pendentes); ?></strong>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Parciais</span>
                        <strong><?php echo e($parciais); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-person-badge me-2"></i>Equipe e Veículo
                </div>

                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Motorista</small>
                        <div class="fw-semibold">
                            <?php echo e($romaneio->motorista->nome ?? 'Não informado'); ?>

                        </div>
                        <small class="text-muted">
                            <?php echo e($romaneio->motorista->telefone ?? 'Telefone não informado'); ?>

                        </small>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Veículo</small>
                        <div class="fw-semibold">
                            <?php echo e($romaneio->veiculo->descricao ?? $romaneio->veiculo->nome ?? 'Não informado'); ?>

                        </div>
                        <small class="text-muted">
                            Placa: <?php echo e($romaneio->veiculo->placa ?? 'Não informada'); ?>

                        </small>
                    </div>

                    <div>
                        <small class="text-muted">Data do Romaneio</small>
                        <div class="fw-semibold">
                            <?php echo e(!empty($romaneio->data_romaneio) ? \Carbon\Carbon::parse($romaneio->data_romaneio)->format('d/m/Y') : 'Não informada'); ?>

                        </div>
                        <small class="text-muted">
                            Criado em: <?php echo e(optional($romaneio->created_at)->format('d/m/Y H:i')); ?>

                        </small>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-ui-checks-grid me-2"></i>Checklist da Expedição
                </div>

                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?php echo e($romaneio ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'); ?>"></i>
                        <span>Romaneio criado</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?php echo e($checkSeparacao ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'); ?>"></i>
                        <span>Separação iniciada</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?php echo e($checkCarregamento ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'); ?>"></i>
                        <span>Carregamento iniciado</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?php echo e($checkConferencia ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'); ?>"></i>
                        <span>Conferência concluída</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <i class="bi <?php echo e($checkLiberado ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'); ?>"></i>
                        <span>Liberado para rota</span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-speedometer2 me-2"></i>Progresso
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Carregamento</strong>
                        <span><?php echo e($progresso); ?>%</span>
                    </div>

                    <div class="progress mb-3" style="height: 24px;">
                        <div class="progress-bar"
                             role="progressbar"
                             style="width: <?php echo e($progresso); ?>%;"
                             aria-valuenow="<?php echo e($progresso); ?>"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            <?php echo e($progresso); ?>%
                        </div>
                    </div>

                    <small class="text-muted">
                        A barra considera os itens carregados em relação ao total previsto para o romaneio.
                    </small>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-lightning-charge me-2"></i>Ações da Operação
            </span>

            <small class="text-muted">
                Avanço operacional por etapa
            </small>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Etapa 1</small>
                        <h6 class="fw-bold mb-2">Separação</h6>

                        <?php if($statusRomaneio === 'Aberto'): ?>
                            <form action="<?php echo e(route('expedicao.iniciar-separacao', $romaneio->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-box-seam"></i> Iniciar Separação
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">
                                <?php echo e($checkSeparacao ? 'Etapa iniciada' : 'Aguardando'); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Etapa 2</small>
                        <h6 class="fw-bold mb-2">Carregamento</h6>

                        <?php if(in_array($statusRomaneio, ['Aberto', 'Em Separação'])): ?>
                            <form action="<?php echo e(route('expedicao.iniciar-carregamento', $romaneio->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-truck"></i> Iniciar Carregamento
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">
                                <?php echo e($checkCarregamento ? 'Etapa iniciada' : 'Aguardando'); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Etapa 3</small>
                        <h6 class="fw-bold mb-2">Finalização</h6>

                        <?php if($statusRomaneio === 'Carregando'): ?>
                            <form action="<?php echo e(route('expedicao.finalizar-carregamento', $romaneio->id)); ?>"
                                  method="POST"
                                  onsubmit="return confirm('Deseja finalizar o carregamento deste romaneio?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check2-circle"></i> Finalizar
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">
                                <?php echo e($checkConferencia ? 'Conferido' : 'Aguardando'); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted">Etapa 4</small>
                        <h6 class="fw-bold mb-2">Liberação</h6>

                        <?php if($statusRomaneio === 'Carregado'): ?>
                            <form action="<?php echo e(route('expedicao.liberar-rota', $romaneio->id)); ?>"
                                  method="POST"
                                  onsubmit="return confirm('Deseja liberar este romaneio para rota?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-dark w-100">
                                    <i class="bi bi-signpost-split"></i> Liberar Rota
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">
                                <?php echo e($checkLiberado ? 'Liberado' : 'Aguardando'); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold">
            <i class="bi bi-funnel me-2"></i>Filtros da Conferência
        </div>

        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Pesquisar item</label>
                    <input type="text"
                           id="filtroItens"
                           class="form-control"
                           placeholder="Digite produto, cliente, entrega, endereço ou status...">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Filtro rápido</label>
                    <select id="filtroStatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="Pendente">Pendentes</option>
                        <option value="Separado">Separados</option>
                        <option value="Carregando">Carregando</option>
                        <option value="Carregado">Carregados</option>
                        <option value="Parcial">Parciais</option>
                        <option value="Devolvido">Devolvidos</option>
                        <option value="Cancelado">Cancelados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>
                <i class="bi bi-list-check me-2"></i>Conferência dos Itens
            </span>

            <small class="text-muted">
                Conferência inline por quantidade carregada
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelaItensExpedicao">
                <thead class="table-light">
                    <tr>
                        <th>Entrega</th>
                        <th>Cliente / Endereço</th>
                        <th>Produto</th>
                        <th class="text-center">Previsto</th>
                        <th class="text-center">Carregado</th>
                        <th class="text-center">Diferença</th>
                        <th class="text-center">Status</th>
                        <th style="width: 290px;">Operação</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $romaneio->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $entregaItem = $item->entregaItem ?? null;
                            $entrega = $entregaItem->entrega ?? null;
                            $cliente = $entrega->cliente ?? null;
                            $produto = $entregaItem->produto ?? null;

                            $previsto = (float) ($item->quantidade_prevista ?? 0);
                            $carregado = (float) ($item->quantidade_carregada ?? 0);
                            $diferenca = $previsto - $carregado;

                            $badgeClass = match($item->status) {
                                'Carregado' => 'bg-success',
                                'Parcial' => 'bg-warning text-dark',
                                'Carregando' => 'bg-primary',
                                'Separado' => 'bg-info text-dark',
                                'Devolvido' => 'bg-dark',
                                'Cancelado' => 'bg-danger',
                                default => 'bg-secondary',
                            };

                            $textoBusca = strtolower(
                                ($produto->nome ?? '') . ' ' .
                                ($cliente->nome ?? '') . ' ' .
                                ($entrega->id ?? '') . ' ' .
                                ($entrega->endereco_entrega ?? '') . ' ' .
                                ($item->status ?? '')
                            );
                        ?>

                        <tr data-status="<?php echo e($item->status); ?>"
                            data-search="<?php echo e($textoBusca); ?>">

                            <td>
                                <div class="fw-semibold">
                                    #<?php echo e($entrega->id ?? '-'); ?>

                                </div>

                                <small class="text-muted">
                                    Item: #<?php echo e($item->entrega_item_id); ?>

                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    <?php echo e($cliente->nome ?? 'Cliente não informado'); ?>

                                </div>

                                <small class="text-muted">
                                    <?php echo e($entrega->endereco_entrega ?? 'Endereço não informado'); ?>

                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    <?php echo e($produto->nome ?? 'Produto não informado'); ?>

                                </div>

                                <small class="text-muted">
                                    Código: <?php echo e($produto->codigo ?? $produto->id ?? '-'); ?>

                                </small>
                            </td>

                            <td class="text-center">
                                <?php echo e(number_format($previsto, 2, ',', '.')); ?>

                            </td>

                            <td class="text-center">
                                <?php echo e(number_format($carregado, 2, ',', '.')); ?>

                            </td>

                            <td class="text-center">
                                <?php if($diferenca > 0): ?>
                                    <span class="badge bg-warning text-dark">
                                        <?php echo e(number_format($diferenca, 2, ',', '.')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success">
                                        OK
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <span class="badge <?php echo e($badgeClass); ?>">
                                    <?php echo e($item->status); ?>

                                </span>
                            </td>

                            <td>
                                <?php if($statusRomaneio === 'Carregando'): ?>
                                    <form action="<?php echo e(route('expedicao.confirmar-item', $romaneio->id)); ?>"
                                          method="POST"
                                          class="d-flex gap-2 align-items-center">
                                        <?php echo csrf_field(); ?>

                                        <input type="hidden" name="romaneio_item_id" value="<?php echo e($item->id); ?>">

                                        <input type="number"
                                               name="quantidade_carregada"
                                               class="form-control form-control-sm"
                                               step="0.01"
                                               min="0"
                                               max="<?php echo e($previsto); ?>"
                                               value="<?php echo e($carregado > 0 ? $carregado : $previsto); ?>">

                                        <button type="submit" class="btn btn-sm btn-success" title="Confirmar carregamento">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted">
                                        Inicie o carregamento para conferir.
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Nenhum item encontrado neste romaneio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Total de entregas</small>
                    <h5 class="fw-bold mb-0"><?php echo e($totalEntregas); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Itens pendentes</small>
                    <h5 class="fw-bold mb-0"><?php echo e($pendentes); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Itens parciais</small>
                    <h5 class="fw-bold mb-0"><?php echo e($parciais); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <small class="text-muted">Progresso geral</small>
                    <h5 class="fw-bold mb-0"><?php echo e($progresso); ?>%</h5>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBusca = document.getElementById('filtroItens');
    const filtroStatus = document.getElementById('filtroStatus');
    const linhas = document.querySelectorAll('#tabelaItensExpedicao tbody tr[data-search]');

    function aplicarFiltros() {
        const termo = (inputBusca.value || '').toLowerCase().trim();
        const status = filtroStatus.value;

        linhas.forEach(function (linha) {
            const texto = linha.dataset.search || '';
            const statusLinha = linha.dataset.status || '';

            const passaBusca = !termo || texto.includes(termo);
            const passaStatus = !status || statusLinha === status;

            linha.style.display = (passaBusca && passaStatus) ? '' : 'none';
        });
    }

    if (inputBusca) {
        inputBusca.addEventListener('input', aplicarFiltros);
    }

    if (filtroStatus) {
        filtroStatus.addEventListener('change', aplicarFiltros);
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/expedicao/operacao.blade.php ENDPATH**/ ?>