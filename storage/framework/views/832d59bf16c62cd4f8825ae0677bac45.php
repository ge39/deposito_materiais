

<?php $__env->startSection('content'); ?>
<style>
    .ocorrencias-page {
        font-size: .92rem;
    }

    .ocorrencias-page .card {
        border-color: #d7dde2;
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .04);
    }

    .ocorrencias-page .card-header {
        background: #737e87;
        color: #fff;
        font-weight: 700;
    }

    .ocorrencias-page .resumo-label {
        color: #64717c;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .ocorrencias-page .resumo-valor {
        color: #17212b;
        font-weight: 700;
    }

    .ocorrencias-page .ocorrencia-card {
        border-left-width: 5px;
    }

    .ocorrencias-page .ocorrencia-critica {
        border-left-color: #dc3545;
    }

    .ocorrencias-page .ocorrencia-atencao {
        border-left-color: #ffc107;
    }

    .ocorrencias-page .ocorrencia-informativa {
        border-left-color: #0dcaf0;
    }

    .ocorrencias-page .etapa-box {
        border: 1px solid #d9dee3;
        border-radius: .45rem;
        background: #f8f9fa;
        padding: 1rem;
        height: 100%;
    }

    .ocorrencias-page .etapa-titulo {
        border-bottom: 1px solid #d9dee3;
        color: #26323d;
        font-weight: 700;
        margin-bottom: .85rem;
        padding-bottom: .55rem;
    }

    .ocorrencias-page .evidencia-thumb {
        align-items: center;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: .35rem;
        display: flex;
        gap: .65rem;
        min-height: 62px;
        padding: .5rem;
    }

    .ocorrencias-page .evidencia-thumb img {
        border-radius: .25rem;
        height: 48px;
        object-fit: cover;
        width: 58px;
    }

    .ocorrencias-page .historico-item {
        border-left: 3px solid #198754;
        margin-left: .35rem;
        padding: 0 0 .8rem .85rem;
    }

    .ocorrencias-page .form-label {
        font-weight: 600;
    }
</style>

<?php
    $ocorrencias = $romaneio->ocorrencias;
    $quantidadeAbertas = $ocorrencias
        ->whereNotIn('status', ['Resolvida', 'Cancelada'])
        ->count();
    $quantidadeLiberadas = $ocorrencias
        ->where('permite_fechamento_logistico', true)
        ->count();
?>

<div class="container-fluid ocorrencias-page py-3 px-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="bi bi-exclamation-diamond me-1"></i>
                Tratativa de ocorrências
            </h4>
            <div class="text-muted">
                Registre evidências, análise e decisão administrativa do retorno.
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge text-bg-dark">
                <?php echo e($romaneio->codigo_romaneio ?? ('ROM-' . $romaneio->id)); ?>

            </span>
            <span class="badge text-bg-warning">
                <?php echo e(str_replace('_', ' ', $romaneio->status)); ?>

            </span>
            <a
                href="<?php echo e(route('romaneios.show', $romaneio)); ?>"
                class="btn btn-outline-secondary btn-sm"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Voltar ao romaneio
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Revise os dados informados:</div>
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-truck me-1"></i>
            Dados do retorno
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Romaneio</div>
                    <div class="resumo-valor">
                        <?php echo e($romaneio->codigo_romaneio ?? ('#' . $romaneio->id)); ?>

                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Entrega</div>
                    <div class="resumo-valor">
                        <?php echo e($romaneio->entrega?->codigo_entrega ?? 'Não informada'); ?>

                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Cliente</div>
                    <div class="resumo-valor">
                        <?php echo e($romaneio->entrega?->cliente?->nome
                            ?? $romaneio->entrega?->venda?->cliente?->nome
                            ?? $romaneio->entrega?->orcamento?->cliente?->nome
                            ?? 'Não informado'); ?>

                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Motorista</div>
                    <div class="resumo-valor">
                        <?php echo e($equipe?->motorista?->nome ?? $romaneio->motorista?->nome ?? 'Não informado'); ?>

                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Ajudante</div>
                    <div class="resumo-valor">
                        <?php echo e($equipe?->ajudante?->nome ?? 'Não informado'); ?>

                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="resumo-label">Veículo</div>
                    <div class="resumo-valor">
                        <?php echo e($equipe?->veiculo?->placa ?? $romaneio->veiculo?->placa ?? 'Não informado'); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="alert alert-secondary mb-0 h-100">
                <div class="small text-uppercase fw-bold">Ocorrências registradas</div>
                <div class="fs-3 fw-bold"><?php echo e($ocorrencias->count()); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-warning mb-0 h-100">
                <div class="small text-uppercase fw-bold">Aguardando conclusão</div>
                <div class="fs-3 fw-bold"><?php echo e($quantidadeAbertas); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success mb-0 h-100">
                <div class="small text-uppercase fw-bold">Fechamento liberado</div>
                <div class="fs-3 fw-bold"><?php echo e($quantidadeLiberadas); ?></div>
            </div>
        </div>
    </div>

    <?php $__empty_1 = true; $__currentLoopData = $ocorrencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ocorrencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $entregaItem = $ocorrencia->romaneioItem?->entregaItem;
            $produto = $entregaItem?->vendaItem?->produto
                ?? $entregaItem?->itemOrcamento?->produto;
            $encerrada = in_array(
                $ocorrencia->status,
                ['Resolvida', 'Cancelada'],
                true
            );
            $classeCriticidade = match ($ocorrencia->criticidade) {
                'Critico' => 'ocorrencia-critica',
                'Atencao' => 'ocorrencia-atencao',
                default => 'ocorrencia-informativa',
            };
            $badgeCriticidade = match ($ocorrencia->criticidade) {
                'Critico' => 'text-bg-danger',
                'Atencao' => 'text-bg-warning',
                default => 'text-bg-info',
            };
        ?>

        <div class="card ocorrencia-card <?php echo e($classeCriticidade); ?> mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    Ocorrência #<?php echo e($ocorrencia->id); ?> — <?php echo e($ocorrencia->tipo); ?>

                </div>
                <div class="d-flex flex-wrap gap-1">
                    <span class="badge <?php echo e($badgeCriticidade); ?>">
                        <?php echo e($ocorrencia->criticidade); ?>

                    </span>
                    <span class="badge text-bg-light">
                        <?php echo e(str_replace('_', ' ', $ocorrencia->status)); ?>

                    </span>
                    <?php if($ocorrencia->permite_fechamento_logistico): ?>
                        <span class="badge text-bg-success">
                            Fechamento liberado
                        </span>
                    <?php else: ?>
                        <span class="badge text-bg-danger">
                            Bloqueando fluxo
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="resumo-label">Produto</div>
                        <div class="resumo-valor">
                            <?php echo e($produto?->nome ?? ('Item #' . ($ocorrencia->entrega_item_id ?? '—'))); ?>

                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Quantidade</div>
                        <div class="resumo-valor">
                            <?php echo e(number_format((float) $ocorrencia->quantidade_envolvida, 3, ',', '.')); ?>

                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Classificação inicial</div>
                        <div class="resumo-valor">
                            <?php echo e($ocorrencia->classificacao_inicial ?? 'Não informada'); ?>

                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Classificação final</div>
                        <div class="resumo-valor">
                            <?php echo e($ocorrencia->classificacao_final ?? 'Pendente'); ?>

                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="resumo-label">Destino</div>
                        <div class="resumo-valor">
                            <?php echo e(str_replace('_', ' ', $ocorrencia->destino_estoque)); ?>

                        </div>
                    </div>
                    <div class="col-12">
                        <div class="resumo-label">Descrição</div>
                        <div><?php echo e($ocorrencia->descricao); ?></div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-xl-4">
                        <div class="etapa-box">
                            <div class="etapa-titulo">
                                1. Responsável e evidências
                            </div>

                            <form
                                method="POST"
                                action="<?php echo e(route('romaneios.ocorrencias.responsavel', [$romaneio, $ocorrencia])); ?>"
                                class="mb-3"
                            >
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>

                                <label class="form-label">Responsável pela análise</label>
                                <div class="input-group">
                                    <select
                                        name="responsavel_analise_id"
                                        class="form-select"
                                        required
                                        <?php if($encerrada): echo 'disabled'; endif; ?>
                                    >
                                        <option value="">Selecione</option>
                                        <?php $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option
                                                value="<?php echo e($responsavel->id); ?>"
                                                <?php if((int) $ocorrencia->responsavel_analise_id === (int) $responsavel->id): echo 'selected'; endif; ?>
                                            >
                                                <?php echo e($responsavel->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <button
                                        type="submit"
                                        class="btn btn-outline-primary"
                                        <?php if($encerrada): echo 'disabled'; endif; ?>
                                    >
                                        Salvar
                                    </button>
                                </div>
                            </form>

                            <form
                                method="POST"
                                action="<?php echo e(route('romaneios.ocorrencias.evidencias.store', [$romaneio, $ocorrencia])); ?>"
                                enctype="multipart/form-data"
                                class="mb-3"
                            >
                                <?php echo csrf_field(); ?>

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo</label>
                                        <select name="tipo" class="form-select" required <?php if($encerrada): echo 'disabled'; endif; ?>>
                                            <option value="Foto">Foto</option>
                                            <option value="Documento">Documento</option>
                                            <option value="Comprovante">Comprovante</option>
                                            <option value="Assinatura">Assinatura</option>
                                            <option value="Outro">Outro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Arquivo</label>
                                        <input
                                            type="file"
                                            name="arquivo"
                                            class="form-control"
                                            accept="image/jpeg,image/png,image/webp,application/pdf"
                                            required
                                            <?php if($encerrada): echo 'disabled'; endif; ?>
                                        >
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Descrição</label>
                                        <input
                                            type="text"
                                            name="descricao"
                                            class="form-control"
                                            maxlength="500"
                                            placeholder="Identifique o conteúdo da evidência"
                                            <?php if($encerrada): echo 'disabled'; endif; ?>
                                        >
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-outline-success" <?php if($encerrada): echo 'disabled'; endif; ?>>
                                            <i class="bi bi-paperclip me-1"></i>
                                            Anexar evidência
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="d-grid gap-2">
                                <?php $__empty_2 = true; $__currentLoopData = $ocorrencia->anexos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <?php
                                        $urlAnexo = str_starts_with(
                                            (string) $anexo->caminho,
                                            'uploads/'
                                        )
                                            ? asset($anexo->caminho)
                                            : asset('storage/' . $anexo->caminho);
                                    ?>
                                    <a
                                        href="<?php echo e($urlAnexo); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="evidencia-thumb text-decoration-none"
                                    >
                                        <?php if(str_starts_with((string) $anexo->mime_type, 'image/')): ?>
                                            <img
                                                src="<?php echo e($urlAnexo); ?>"
                                                alt="Evidência da ocorrência"
                                            >
                                        <?php else: ?>
                                            <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                        <?php endif; ?>
                                        <span>
                                            <strong class="d-block"><?php echo e($anexo->nome_original); ?></strong>
                                            <small class="text-muted">
                                                <?php echo e($anexo->descricao ?? $anexo->tipo); ?>

                                            </small>
                                        </span>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <div class="alert alert-warning py-2 mb-0">
                                        Nenhuma evidência anexada.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="etapa-box">
                            <div class="etapa-titulo">
                                2. Autorização e decisão
                            </div>

                            <?php if($ocorrencia->exige_autorizacao): ?>
                                <?php if($ocorrencia->autorizada_por): ?>
                                    <div class="alert alert-success py-2">
                                        <strong>Autorizada.</strong>
                                        <?php echo e($ocorrencia->justificativa_autorizacao); ?>

                                    </div>
                                <?php else: ?>
                                    <form
                                        method="POST"
                                        action="<?php echo e(route('romaneios.ocorrencias.autorizar', [$romaneio, $ocorrencia])); ?>"
                                        class="mb-3"
                                    >
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>

                                        <label class="form-label">Justificativa da autorização</label>
                                        <textarea
                                            name="justificativa_autorizacao"
                                            class="form-control mb-2"
                                            rows="3"
                                            minlength="5"
                                            maxlength="2000"
                                            required
                                            <?php if($encerrada): echo 'disabled'; endif; ?>
                                        ></textarea>
                                        <button type="submit" class="btn btn-warning w-100" <?php if($encerrada): echo 'disabled'; endif; ?>>
                                            <i class="bi bi-shield-check me-1"></i>
                                            Autorizar ocorrência
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                            <form
                                method="POST"
                                action="<?php echo e(route('romaneios.ocorrencias.decisao', [$romaneio, $ocorrencia])); ?>"
                            >
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Classificação final</label>
                                        <select name="classificacao_final" class="form-select" required <?php if($encerrada): echo 'disabled'; endif; ?>>
                                            <option value="">Selecione</option>
                                            <?php $__currentLoopData = ['Extravio', 'Avaria', 'Recusa', 'Devolucao', 'Perda', 'Divergencia', 'Improcedente', 'Outro']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classificacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($classificacao); ?>" <?php if($ocorrencia->classificacao_final === $classificacao): echo 'selected'; endif; ?>>
                                                    <?php echo e($classificacao); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Destino do estoque</label>
                                        <select name="destino_estoque" class="form-select" required <?php if($encerrada): echo 'disabled'; endif; ?>>
                                            <?php $__currentLoopData = ['Sem_movimentacao', 'Quarentena', 'Reintegracao', 'Perda', 'Reposicao']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $destino): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($destino); ?>" <?php if($ocorrencia->destino_estoque === $destino): echo 'selected'; endif; ?>>
                                                    <?php echo e(str_replace('_', ' ', $destino)); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Orçamento de reposição</label>
                                        <input
                                            type="number"
                                            name="orcamento_reposicao_id"
                                            class="form-control"
                                            min="1"
                                            value="<?php echo e($ocorrencia->orcamento_reposicao_id); ?>"
                                            placeholder="ID do orçamento, quando aplicável"
                                            <?php if($encerrada): echo 'disabled'; endif; ?>
                                        >
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Decisão administrativa</label>
                                        <textarea
                                            name="decisao"
                                            class="form-control"
                                            rows="4"
                                            minlength="5"
                                            maxlength="5000"
                                            required
                                            <?php if($encerrada): echo 'disabled'; endif; ?>
                                        ><?php echo e($ocorrencia->decisao); ?></textarea>
                                    </div>
                                    <div class="col-12 d-grid">
                                        <button type="submit" class="btn btn-primary" <?php if($encerrada): echo 'disabled'; endif; ?>>
                                            <i class="bi bi-clipboard-check me-1"></i>
                                            Registrar decisão
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="etapa-box">
                            <div class="etapa-titulo">
                                3. Liberação e encerramento
                            </div>

                            <?php if(! $ocorrencia->permite_fechamento_logistico && ! $encerrada): ?>
                                <form
                                    method="POST"
                                    action="<?php echo e(route('romaneios.ocorrencias.liberar-fechamento', [$romaneio, $ocorrencia])); ?>"
                                    class="mb-3"
                                >
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>

                                    <div class="form-check border rounded p-3 ps-5 mb-2 bg-white">
                                        <input
                                            class="form-check-input confirmacao-acao"
                                            type="checkbox"
                                            id="liberar-<?php echo e($ocorrencia->id); ?>"
                                            data-target="btn-liberar-<?php echo e($ocorrencia->id); ?>"
                                        >
                                        <label class="form-check-label" for="liberar-<?php echo e($ocorrencia->id); ?>">
                                            Confirmo que o responsável e as evidências foram conferidos.
                                        </label>
                                    </div>
                                    <button
                                        type="submit"
                                        id="btn-liberar-<?php echo e($ocorrencia->id); ?>"
                                        class="btn btn-success w-100"
                                        disabled
                                    >
                                        <i class="bi bi-unlock me-1"></i>
                                        Liberar fechamento logístico
                                    </button>
                                </form>
                            <?php elseif($ocorrencia->permite_fechamento_logistico): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Esta ocorrência não impede mais o fechamento logístico.
                                </div>
                            <?php endif; ?>

                            <?php if(! $encerrada): ?>
                                <form
                                    method="POST"
                                    action="<?php echo e(route('romaneios.ocorrencias.resolver', [$romaneio, $ocorrencia])); ?>"
                                    class="mb-3"
                                >
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>

                                    <label class="form-label">Solução aplicada</label>
                                    <textarea
                                        name="solucao"
                                        class="form-control mb-2"
                                        rows="3"
                                        minlength="5"
                                        maxlength="5000"
                                        required
                                    ></textarea>
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        Resolver ocorrência
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="<?php echo e(route('romaneios.ocorrencias.cancelar', [$romaneio, $ocorrencia])); ?>"
                                >
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>

                                    <label class="form-label">Justificativa do cancelamento</label>
                                    <textarea
                                        name="justificativa"
                                        class="form-control mb-2"
                                        rows="2"
                                        minlength="5"
                                        maxlength="5000"
                                        required
                                    ></textarea>
                                    <div class="form-check border rounded p-3 ps-5 mb-2 bg-white">
                                        <input
                                            class="form-check-input confirmacao-acao"
                                            type="checkbox"
                                            id="cancelar-<?php echo e($ocorrencia->id); ?>"
                                            data-target="btn-cancelar-<?php echo e($ocorrencia->id); ?>"
                                        >
                                        <label class="form-check-label" for="cancelar-<?php echo e($ocorrencia->id); ?>">
                                            Confirmo que a ocorrência deve ser cancelada como improcedente.
                                        </label>
                                    </div>
                                    <button
                                        type="submit"
                                        id="btn-cancelar-<?php echo e($ocorrencia->id); ?>"
                                        class="btn btn-outline-danger w-100"
                                        disabled
                                    >
                                        Cancelar ocorrência
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-secondary">
                                    <strong><?php echo e($ocorrencia->status); ?>:</strong>
                                    <?php echo e($ocorrencia->solucao ?? $ocorrencia->decisao); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($ocorrencia->historicos->isNotEmpty()): ?>
                    <div class="mt-3">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="collapse"
                            data-bs-target="#historico-<?php echo e($ocorrencia->id); ?>"
                        >
                            <i class="bi bi-clock-history me-1"></i>
                            Exibir histórico
                        </button>

                        <div class="collapse mt-3" id="historico-<?php echo e($ocorrencia->id); ?>">
                            <?php $__currentLoopData = $ocorrencia->historicos->sortByDesc('id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $historico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="historico-item">
                                    <strong><?php echo e($historico->evento); ?></strong>
                                    <div class="small text-muted">
                                        <?php echo e(optional($historico->registrado_em)->format('d/m/Y H:i')); ?>

                                        — <?php echo e($historico->status_anterior ?? 'Início'); ?>

                                        → <?php echo e($historico->status_novo); ?>

                                    </div>
                                    <?php if($historico->descricao): ?>
                                        <div><?php echo e($historico->descricao); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="alert alert-info">
            Nenhuma ocorrência foi registrada para este romaneio.
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.confirmacao-acao').forEach(function (checkbox) {
            const botao = document.getElementById(checkbox.dataset.target);

            if (!botao) {
                return;
            }

            checkbox.addEventListener('change', function () {
                botao.disabled = !checkbox.checked;
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\romaneios\ocorrencias\index.blade.php ENDPATH**/ ?>