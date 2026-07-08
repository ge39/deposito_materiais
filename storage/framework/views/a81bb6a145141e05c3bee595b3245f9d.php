

<?php $__env->startSection('content'); ?>
<?php
    $entrega = $romaneio->entrega ?? null;
    $orcamento = $entrega->orcamento ?? null;
    $cliente = $orcamento->cliente ?? $entrega->cliente ?? null;

    $motoristasDisponiveis = $motoristas ?? $funcionarios ?? collect();
    $veiculosDisponiveis = $veiculos ?? collect();

    $clienteNome = $cliente->nome
        ?? $cliente->razao_social
        ?? 'Cliente não informado';

    $codigoOrcamento = $orcamento->codigo_orcamento
        ?? $orcamento->codigo
        ?? $orcamento->numero
        ?? '-';

    $codigoRomaneio = $romaneio->codigo_romaneio
        ?? '#' . $romaneio->id;

    $codigoEntrega = $entrega->codigo_entrega
        ?? (!empty($entrega->id) ? '#' . $entrega->id : '-');

    $dataPrevista = !empty($entrega?->data_prevista_entrega)
        ? \Carbon\Carbon::parse($entrega->data_prevista_entrega)->format('d/m/Y')
        : '-';

    $periodo = $entrega->periodo_entrega ?? 'Não definido';

    $enderecoEntrega = $entrega->endereco_entrega
        ?? $entrega->endereco_entrega_concatenado
        ?? 'Endereço não informado';

    $motoristaAtual = $romaneio->motorista->nome
        ?? $romaneio->motorista->name
        ?? 'Não atribuído';

    $veiculoAtual = $romaneio->veiculo
        ? trim(($romaneio->veiculo->placa ?? '') . ' - ' . ($romaneio->veiculo->modelo ?? ''))
        : 'Não atribuído';

    $statusRomaneio = $romaneio->status ?? 'Gerado';
?>

<div class="container-fluid px-2">

    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-truck me-2"></i>
                Atribuir Motorista e Veículo ao Romaneio
            </h4>
            <small class="text-muted">
                Romaneio <?php echo e($codigoRomaneio); ?> |
                Entrega <?php echo e($codigoEntrega); ?>

            </small>
        </div>

        <div class="d-flex gap-1">
            <a href="<?php echo e(route('expedicao.index')); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar
            </a>

            <?php if($entrega): ?>
                <a href="<?php echo e(route('entregas.show', $entrega->id)); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye me-1"></i>
                    Ver Entrega
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if($errors->any()): ?>
        <div class="alert alert-secondary alert-dismissible fade show shadow-sm py-2" role="alert">
            <strong>Corrija os campos abaixo.</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-secondary alert-dismissible fade show shadow-sm py-2" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="row g-2 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body py-2">
                    <small class="text-muted fw-semibold">CLIENTE</small>
                    <div class="fw-bold"><?php echo e($clienteNome); ?></div>
                    <small class="text-muted">Orçamento: <?php echo e($codigoOrcamento); ?></small>
                </div>
            </div>
        </div>
                <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-success h-100">
                <div class="card-body py-2">
                    <small class="text-muted fw-semibold">DATA DA ENTREGA</small>
                    <div class="fw-bold"><?php echo e($dataPrevista); ?></div>
                    <small class="text-muted">Período: <?php echo e(ucfirst($periodo)); ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body py-2">
                    <small class="text-muted fw-semibold">MOTORISTA DO ROMANEIO</small>
                    <div class="fw-bold"><?php echo e($motoristaAtual); ?></div>
                    <small class="text-muted">Responsável pelo carregamento/saída</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-dark h-100">
                <div class="card-body py-2">
                    <small class="text-muted fw-semibold">VEÍCULO DO ROMANEIO</small>
                    <div class="fw-bold"><?php echo e($veiculoAtual); ?></div>
                    <small class="text-muted">Recurso de transporte</small>
                </div>
            </div>
        </div>
    </div>

    <!-- <form method="POST" action="<?php echo e(request()->url()); ?>">-->
    <form method="POST" action="<?php echo e(route('expedicao.salvar-equipe', $romaneio->id)); ?>"> 
     <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

        <div class="row g-3">

            
            <div class="col-md-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <strong>
                            <i class="bi bi-person-badge me-2"></i>
                            Equipe do Romaneio
                        </strong>

                        <span class="badge bg-light text-dark">
                            <?php echo e(str_replace('_', ' ', $statusRomaneio)); ?>

                        </span>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="motorista_id" class="form-label fw-semibold">
                                    Motorista
                                </label>

                                <select name="motorista_id" id="motorista_id" class="form-select" required>
                                    <option value="">Selecione o motorista</option>

                                    <?php $__currentLoopData = $motoristasDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $motorista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($motorista->id); ?>"
                                            <?php if(old('motorista_id', $romaneio->motorista_id) == $motorista->id): echo 'selected'; endif; ?>>
                                            <?php echo e($motorista->nome ?? $motorista->name); ?>

                                            <?php echo e(!empty($motorista->telefone) ? ' — ' . $motorista->telefone : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>

                                <small class="text-muted">
                                    Funcionário responsável pela condução do romaneio.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="veiculo_id" class="form-label fw-semibold">
                                    Veículo
                                </label>

                                <select name="veiculo_id" id="veiculo_id" class="form-select" required>
                                    <option value="">Selecione o veículo</option>

                                    <?php $__currentLoopData = $veiculosDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($veiculo->id); ?>"
                                            data-placa="<?php echo e($veiculo->placa); ?>"
                                            data-modelo="<?php echo e($veiculo->modelo); ?>"
                                            data-marca="<?php echo e($veiculo->marca); ?>"
                                            data-tipo="<?php echo e($veiculo->tipo); ?>"
                                            data-tipo-frota="<?php echo e($veiculo->tipo_frota); ?>"
                                            data-operacao="<?php echo e($veiculo->operacao_preferencial); ?>"
                                            data-disponibilidade="<?php echo e($veiculo->disponibilidade); ?>"
                                            data-status="<?php echo e($veiculo->status); ?>"
                                            data-capacidade-kg="<?php echo e($veiculo->capacidade_kg); ?>"
                                            data-capacidade-m3="<?php echo e($veiculo->capacidade_m3); ?>"
                                            data-comprimento="<?php echo e($veiculo->comprimento_m); ?>"
                                            data-largura="<?php echo e($veiculo->largura_m); ?>"
                                            data-altura="<?php echo e($veiculo->altura_m); ?>"
                                            data-altura-total="<?php echo e($veiculo->altura_total_m); ?>"
                                            data-munck="<?php echo e($veiculo->possui_munck ? 'Sim' : 'Não'); ?>"
                                            data-carroceria-aberta="<?php echo e($veiculo->possui_carroceria_aberta ? 'Sim' : 'Não'); ?>"
                                            data-carroceria-fechada="<?php echo e($veiculo->possui_carroceria_fechada ? 'Sim' : 'Não'); ?>"
                                            data-rastreador="<?php echo e($veiculo->possui_rastreador ? 'Sim' : 'Não'); ?>"
                                            data-areia-pedra="<?php echo e($veiculo->aceita_areia_pedra ? 'Sim' : 'Não'); ?>"
                                            data-blocos="<?php echo e($veiculo->aceita_blocos_tijolos ? 'Sim' : 'Não'); ?>"
                                            data-cimento="<?php echo e($veiculo->aceita_cimento_argamassa ? 'Sim' : 'Não'); ?>"
                                            data-tintas="<?php echo e($veiculo->aceita_tintas_quimicos ? 'Sim' : 'Não'); ?>"
                                            data-telhas="<?php echo e($veiculo->aceita_telhas ? 'Sim' : 'Não'); ?>"
                                            data-madeiras="<?php echo e($veiculo->aceita_madeiras ? 'Sim' : 'Não'); ?>"
                                            data-rodizio="<?php echo e($veiculo->restricao_rodizio ? 'Sim' : 'Não'); ?>"
                                            data-zona-central="<?php echo e($veiculo->restricao_zona_central ? 'Sim' : 'Não'); ?>"
                                            data-restricao-altura="<?php echo e($veiculo->restricao_altura ? 'Sim' : 'Não'); ?>"
                                            data-restricao-peso="<?php echo e($veiculo->restricao_peso ? 'Sim' : 'Não'); ?>"
                                            data-consumo="<?php echo e($veiculo->consumo_medio_km_l); ?>"
                                            data-custo-km="<?php echo e($veiculo->custo_medio_km); ?>"
                                            <?php if(old('veiculo_id', $romaneio->veiculo_id) == $veiculo->id): echo 'selected'; endif; ?>>
                                            <?php echo e($veiculo->placa); ?>

                                            <?php echo e($veiculo->modelo ? ' — ' . $veiculo->modelo : ''); ?>

                                            <?php echo e($veiculo->tipo ? ' — ' . $veiculo->tipo : ''); ?>

                                            <?php echo e($veiculo->tipo_frota ? ' — ' . $veiculo->tipo_frota : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>

                                <small class="text-muted">
                                    Selecione pela placa/modelo e confira a ficha técnica ao lado.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <strong>
                            <i class="bi bi-geo-alt me-2"></i>
                            Local da Entrega
                        </strong>
                    </div>

                    <div class="card-body py-2">
                        <div class="text-muted small">
                            <?php echo e($enderecoEntrega); ?>

                        </div>

                        <?php if(!empty($entrega?->observacao_entrega)): ?>
                            <hr class="my-2">
                            <div class="small">
                                <strong>Observação:</strong>
                                <?php echo e($entrega->observacao_entrega); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
                        
            <div class="col-md-5">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <strong>
                            <i class="bi bi-truck-front me-2"></i>
                            Ficha Técnica do Veículo
                        </strong>

                        <span id="veiculoDisponibilidadeBadge" class="badge bg-light text-dark">
                            Não selecionado
                        </span>
                    </div>

                    <div class="card-body" id="veiculoFichaVazia">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-truck fs-2 d-block mb-2"></i>
                            Selecione um veículo para visualizar suas características operacionais.
                        </div>
                    </div>

                    <div class="card-body d-none" id="veiculoFicha">
                        <div class="mb-3">
                            <h5 class="mb-0 fw-bold" id="veiculoTitulo">-</h5>
                            <small class="text-muted" id="veiculoSubtitulo">-</small>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <small class="text-muted">Tipo</small>
                                    <div class="fw-semibold" id="veiculoTipo">-</div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <small class="text-muted">Frota</small>
                                    <div class="fw-semibold" id="veiculoTipoFrota">-</div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <small class="text-muted">Operação</small>
                                    <div class="fw-semibold" id="veiculoOperacao">-</div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <small class="text-muted">Status</small>
                                    <div class="fw-semibold" id="veiculoStatus">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-box-seam me-1"></i>
                                Capacidade
                            </div>

                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-dark" id="veiculoCapacidadeKg">- kg</span>
                                <span class="badge bg-secondary" id="veiculoCapacidadeM3">- m³</span>
                                <span class="badge bg-light text-dark border" id="veiculoDimensoes">Dimensões: -</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-tools me-1"></i>
                                Recursos
                            </div>

                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark border" id="badgeMunck">Munck: -</span>
                                <span class="badge bg-light text-dark border" id="badgeCarroceriaAberta">Aberta: -</span>
                                <span class="badge bg-light text-dark border" id="badgeCarroceriaFechada">Fechada: -</span>
                                <span class="badge bg-light text-dark border" id="badgeRastreador">Rastreador: -</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-check2-square me-1"></i>
                                Materiais Aceitos
                            </div>

                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge" id="badgeAreiaPedra">Areia/Pedra</span>
                                <span class="badge" id="badgeBlocos">Blocos/Tijolos</span>
                                <span class="badge" id="badgeCimento">Cimento/Argamassa</span>
                                <span class="badge" id="badgeTintas">Tintas/Químicos</span>
                                <span class="badge" id="badgeTelhas">Telhas</span>
                                <span class="badge" id="badgeMadeiras">Madeiras</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Restrições
                            </div>

                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge" id="badgeRodizio">Rodízio</span>
                                <span class="badge" id="badgeZonaCentral">Zona Central</span>
                                <span class="badge" id="badgeRestricaoAltura">Altura</span>
                                <span class="badge" id="badgeRestricaoPeso">Peso</span>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 mb-0">
                            <i class="bi bi-lightbulb me-1"></i>
                            Confira capacidade, carroceria, materiais aceitos e restrições antes de salvar a equipe do romaneio.
                        </div>
                    </div>
                </div>

                
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <a href="<?php echo e(route('expedicao.index')); ?>" class="btn btn-outline-secondary">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            Salvar Equipe
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectVeiculo = document.getElementById('veiculo_id');

    const ficha = document.getElementById('veiculoFicha');
    const fichaVazia = document.getElementById('veiculoFichaVazia');
    const badgeDisponibilidade = document.getElementById('veiculoDisponibilidadeBadge');

    function valorFormatado(valor, sufixo = '') {
        if (!valor || valor === 'null') {
            return '-';
        }

        const numero = Number(valor);

        if (Number.isNaN(numero)) {
            return valor;
        }

        return numero.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + sufixo;
    }

    function aplicarBadgeSimNao(elementoId, valor, texto) {
        const elemento = document.getElementById(elementoId);

        if (!elemento) {
            return;
        }

        elemento.textContent = texto + ': ' + valor;

        elemento.className = valor === 'Sim'
            ? 'badge bg-success'
            : 'badge bg-secondary';
    }

    function aplicarBadgeAceite(elementoId, valor, texto) {
        const elemento = document.getElementById(elementoId);

        if (!elemento) {
            return;
        }

        elemento.textContent = texto;

        elemento.className = valor === 'Sim'
            ? 'badge bg-success'
            : 'badge bg-secondary';
    }

    function aplicarBadgeRestricao(elementoId, valor, texto) {
        const elemento = document.getElementById(elementoId);

        if (!elemento) {
            return;
        }

        elemento.textContent = texto;

        elemento.className = valor === 'Sim'
            ? 'badge bg-secondary'
            : 'badge bg-success';
    }

    function atualizarFicha() {
        const option = selectVeiculo.options[selectVeiculo.selectedIndex];

        if (!option || !option.value) {
            ficha.classList.add('d-none');
            fichaVazia.classList.remove('d-none');
            badgeDisponibilidade.textContent = 'Não selecionado';
            badgeDisponibilidade.className = 'badge bg-light text-dark';
            return;
        }

        ficha.classList.remove('d-none');
        fichaVazia.classList.add('d-none');

        const placa = option.dataset.placa || '-';
        const modelo = option.dataset.modelo || '-';
        const marca = option.dataset.marca || '-';
        const tipo = option.dataset.tipo || '-';
        const tipoFrota = option.dataset.tipoFrota || '-';
        const operacao = option.dataset.operacao || '-';
        const disponibilidade = option.dataset.disponibilidade || '-';
        const status = option.dataset.status || '-';

        document.getElementById('veiculoTitulo').textContent = placa + ' • ' + modelo;
        document.getElementById('veiculoSubtitulo').textContent = marca;
        document.getElementById('veiculoTipo').textContent = tipo;
        document.getElementById('veiculoTipoFrota').textContent = tipoFrota;
        document.getElementById('veiculoOperacao').textContent = operacao;
        document.getElementById('veiculoStatus').textContent = status;

        badgeDisponibilidade.textContent = disponibilidade;
        badgeDisponibilidade.className = disponibilidade === 'Disponivel'
            ? 'badge bg-success'
            : 'badge bg-warning text-dark';

        document.getElementById('veiculoCapacidadeKg').textContent =
            valorFormatado(option.dataset.capacidadeKg, ' kg');

        document.getElementById('veiculoCapacidadeM3').textContent =
            valorFormatado(option.dataset.capacidadeM3, ' m³');

        document.getElementById('veiculoDimensoes').textContent =
            'Dimensões: ' +
            (option.dataset.comprimento || '-') + 'm x ' +
            (option.dataset.largura || '-') + 'm x ' +
            (option.dataset.altura || '-') + 'm';

        aplicarBadgeSimNao('badgeMunck', option.dataset.munck, 'Munck');
        aplicarBadgeSimNao('badgeCarroceriaAberta', option.dataset.carroceriaAberta, 'Aberta');
        aplicarBadgeSimNao('badgeCarroceriaFechada', option.dataset.carroceriaFechada, 'Fechada');
        aplicarBadgeSimNao('badgeRastreador', option.dataset.rastreador, 'Rastreador');

        aplicarBadgeAceite('badgeAreiaPedra', option.dataset.areiaPedra, 'Areia/Pedra');
        aplicarBadgeAceite('badgeBlocos', option.dataset.blocos, 'Blocos/Tijolos');
        aplicarBadgeAceite('badgeCimento', option.dataset.cimento, 'Cimento/Argamassa');
        aplicarBadgeAceite('badgeTintas', option.dataset.tintas, 'Tintas/Químicos');
        aplicarBadgeAceite('badgeTelhas', option.dataset.telhas, 'Telhas');
        aplicarBadgeAceite('badgeMadeiras', option.dataset.madeiras, 'Madeiras');

        aplicarBadgeRestricao('badgeRodizio', option.dataset.rodizio, 'Rodízio');
        aplicarBadgeRestricao('badgeZonaCentral', option.dataset.zonaCentral, 'Zona Central');
        aplicarBadgeRestricao('badgeRestricaoAltura', option.dataset.restricaoAltura, 'Altura');
        aplicarBadgeRestricao('badgeRestricaoPeso', option.dataset.restricaoPeso, 'Peso');
    }

    if (selectVeiculo) {
        selectVeiculo.addEventListener('change', atualizarFicha);
        atualizarFicha();
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/expedicao/atribuir-equipe.blade.php ENDPATH**/ ?>