<?php $__env->startSection('content'); ?>
<style>
    .devolucoes-pendentes-page {
        color: #17212b;
        font-size: .92rem;
        margin-left: calc(50% - 50vw + 8px);
        width: calc(100vw - 16px);
    }

    .devolucoes-pendentes-page .page-header {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .devolucoes-pendentes-page .resumo-card {
        background: #fff8e1;
        border: 1px solid #ffda6a;
        border-radius: .5rem;
        padding: .75rem 1rem;
    }

    .devolucoes-pendentes-page .devolucao-card {
        border: 1px solid #d7dde2;
        border-left: 5px solid #ffc107;
        border-radius: .55rem;
        box-shadow: 0 .2rem .55rem rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    .devolucoes-pendentes-page .devolucao-header {
        align-items: center;
        background: #737e87;
        color: #fff;
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        justify-content: space-between;
        padding: .75rem 1rem;
    }

    .devolucoes-pendentes-page .secao {
        background: #f8f9fa;
        border: 1px solid #d9dee3;
        border-radius: .45rem;
        height: 100%;
        padding: .85rem;
    }

    .devolucoes-pendentes-page .secao-titulo {
        border-bottom: 1px solid #d9dee3;
        font-weight: 800;
        margin-bottom: .75rem;
        padding-bottom: .5rem;
    }

    .devolucoes-pendentes-page .dado-label {
        color: #64717c;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .devolucoes-pendentes-page .dado-valor {
        font-weight: 700;
        margin-top: .1rem;
    }

    .devolucoes-pendentes-page .quantidade-card {
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: .4rem;
        padding: .6rem;
        text-align: center;
    }

    .devolucoes-pendentes-page .quantidade-card strong {
        display: block;
        font-size: 1.08rem;
    }

    .devolucoes-pendentes-page .evidencias-grid {
        display: grid;
        gap: .6rem;
        grid-template-columns: repeat(auto-fit, minmax(105px, 1fr));
    }

    .devolucoes-pendentes-page .evidencia-preview {
        align-items: center;
        background: #fff;
        border: 1px dashed #adb5bd;
        border-radius: .4rem;
        display: flex;
        justify-content: center;
        min-height: 105px;
        overflow: hidden;
    }

    .devolucoes-pendentes-page .evidencia-preview img {
        height: 105px;
        object-fit: cover;
        width: 100%;
    }

    .devolucoes-pendentes-page .acoes-box {
        background: #f8fbff;
        border: 1px solid #9ec5fe;
        border-radius: .45rem;
        padding: .85rem;
    }

    .devolucoes-pendentes-page .triagem-grid th,
    .devolucoes-pendentes-page .triagem-grid td {
        white-space: nowrap;
        vertical-align: middle;
    }

    .devolucoes-pendentes-page .triagem-grid td.observacao {
        min-width: 190px;
        white-space: normal;
    }
</style>

<div class="container-fluid px-3 py-3 devolucoes-pendentes-page">
    <div class="page-header">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-arrow-counterclockwise me-2"></i>
                Tratamento de devoluções
            </h3>
            <div class="text-muted">
                Confira as linhas da triagem e registre a decisão operacional.
            </div>
        </div>

        <a
            href="<?php echo e(url('/devolucoes')); ?>"
            class="btn btn-outline-secondary"
            data-bs-toggle="tooltip"
            title="Voltar para a relação geral de devoluções."
        >
            <i class="bi bi-arrow-left me-1"></i>
            Voltar às devoluções
        </a>
    </div>

    <?php $__currentLoopData = ['success' => 'success', 'warning' => 'warning', 'error' => 'danger']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sessao => $classe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(session($sessao)): ?>
            <div class="alert alert-<?php echo e($classe); ?>">
                <?php echo e(session($sessao)); ?>

            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Não foi possível concluir a operação:</div>
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($erro); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="resumo-card mb-3">
        <div class="small text-uppercase fw-bold">Aguardando tratamento</div>
        <div class="fs-4 fw-bold"><?php echo e($devolucoes->count()); ?></div>
    </div>

    <div class="d-grid gap-3">
        <?php $__empty_1 = true; $__currentLoopData = $devolucoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $devolucao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $itemVenda = $devolucao->itemVenda;
                $venda = $itemVenda?->venda ?? $devolucao->venda;
                $produto = $itemVenda?->produto ?? $devolucao->produto;
                $detalheComLote = $devolucao->lotes->first(
                    fn ($detalhe) => $detalhe->lote !== null
                );
                $loteOriginal = $itemVenda?->lote
                    ?? $detalheComLote?->lote;
                $quantidadeComprada = (float) ($itemVenda?->quantidade ?? 0);
                $quantidadeDevolvida = (float) $devolucao->quantidade;
                $quantidadeRestante = max(0, $quantidadeComprada - $quantidadeDevolvida);
                $valorUnitario = (float) ($itemVenda?->preco_unitario ?? 0);
                $subtotalDevolucao = $quantidadeDevolvida * $valorUnitario;
                $possuiTriagem = $devolucao->lotes->contains(
                    fn ($detalhe) => ! empty($detalhe->romaneio_ocorrencia_avaliacao_id)
                );
                $linhasPendentes = $devolucao->lotes
                    ->where('status_processamento', 'Pendente')
                    ->count();
                $codigoProdutoOperacao = str_pad(
                    (string) $devolucao->produto_id,
                    5,
                    '0',
                    STR_PAD_LEFT
                );
                $nomeProdutoOperacao = $produto?->nome
                    ?? 'Produto não encontrado';
                $itemProdutoOperacao =
                    "#{$codigoProdutoOperacao} — {$nomeProdutoOperacao}";
                $quantidadeOperacao = number_format(
                    $quantidadeDevolvida,
                    3,
                    ',',
                    '.'
                );
                $loteOperacao = $loteOriginal?->numero_lote
                    ?? 'sem lote identificado';
                $totalLinhasTriagem = $devolucao->lotes
                    ->filter(
                        fn ($detalhe) =>
                            ! empty(
                                $detalhe
                                    ->romaneio_ocorrencia_avaliacao_id
                            )
                    )
                    ->count();
                $descricaoLinhasTriagem = $totalLinhasTriagem === 1
                    ? '1 linha da triagem'
                    : "{$totalLinhasTriagem} linhas da triagem";
                $confirmacaoConferir =
                    "Confirma que você conferiu o item "
                    . "{$itemProdutoOperacao}, "
                    . "quantidade {$quantidadeOperacao}, "
                    . "lote {$loteOperacao}, "
                    . "conforme {$descricaoLinhasTriagem}?";
                $confirmacaoAprovar =
                    "Confirma a aprovação e o processamento da devolução "
                    . "do item {$itemProdutoOperacao}, "
                    . "quantidade {$quantidadeOperacao}, "
                    . "lote {$loteOperacao}?";

                $evidencias = collect();

                foreach ([
                    $devolucao->imagem1,
                    $devolucao->imagem2,
                    $devolucao->imagem3,
                    $devolucao->imagem4,
                ] as $imagem) {
                    if (! $imagem) {
                        continue;
                    }

                    $evidencias->push([
                        'url' => str_contains($imagem, '/')
                            ? asset($imagem)
                            : asset('imgDevolucoes/' . $imagem),
                        'mime_type' => 'image',
                        'descricao' => 'Evidência da devolução',
                    ]);
                }

                foreach ($devolucao->ocorrencia?->anexos ?? collect() as $anexo) {
                    $caminho = (string) $anexo->caminho;
                    $arquivoPublico = str_starts_with($caminho, 'image/')
                        || str_starts_with($caminho, 'uploads/');

                    $evidencias->push([
                        'url' => $arquivoPublico
                            ? asset($caminho)
                            : asset('storage/' . $caminho),
                        'mime_type' => (string) $anexo->mime_type,
                        'descricao' => $anexo->descricao ?: $anexo->nome_original,
                    ]);
                }
            ?>

            <article class="devolucao-card">
                <header class="devolucao-header">
                    <div>
                        <div class="fw-bold fs-5">
                            Devolução #<?php echo e($devolucao->id); ?>

                            <?php if($devolucao->romaneio_ocorrencia_id): ?>
                                <span class="badge text-bg-primary ms-1">
                                    OCORRÊNCIA #<?php echo e($devolucao->romaneio_ocorrencia_id); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                        <small>
                            Venda #<?php echo e(str_pad((string) $devolucao->venda_id, 6, '0', STR_PAD_LEFT)); ?>

                            · <?php echo e($venda?->cliente?->nome ?? 'Cliente não informado'); ?>

                        </small>
                    </div>

                    <span class="badge text-bg-warning">
                        <?php echo e(ucfirst(str_replace('_', ' ', $devolucao->status))); ?>

                    </span>
                </header>

                <div class="p-3">
                    <div class="alert <?php echo e($possuiTriagem ? 'alert-info' : 'alert-warning'); ?> py-2">
                        <strong>Próxima ação:</strong>
                        <?php if($possuiTriagem): ?>
                            confira
                            <strong><?php echo e($descricaoLinhasTriagem); ?></strong>
                            do item
                            <strong><?php echo e($itemProdutoOperacao); ?></strong>,
                            quantidade
                            <strong><?php echo e($quantidadeOperacao); ?></strong>,
                            lote
                            <strong><?php echo e($loteOperacao); ?></strong>.
                        <?php else: ?>
                            confira os dados do item
                            <strong><?php echo e($itemProdutoOperacao); ?></strong>
                            e escolha aprovar ou rejeitar esta devolução.
                        <?php endif; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-xl-5">
                            <section class="secao">
                                <div class="secao-titulo">Produto e origem</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="dado-label">Produto</div>
                                        <div class="dado-valor">
                                            #<?php echo e(str_pad((string) $devolucao->produto_id, 5, '0', STR_PAD_LEFT)); ?>

                                            — <?php echo e($produto?->nome ?? 'Produto não encontrado'); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="dado-label">Lote original</div>
                                        <div class="dado-valor">
                                            <?php echo e($loteOriginal?->numero_lote ?? 'Sem lote identificado'); ?>

                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="dado-label">Tratamento</div>
                                        <div class="dado-valor">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $devolucao->destino_estoque))); ?>

                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="dado-label">Motivo</div>
                                        <div class="mt-1"><?php echo e($devolucao->motivo); ?></div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div class="col-xl-4">
                            <section class="secao">
                                <div class="secao-titulo">Quantidades e valores</div>
                                <div class="row g-2 mb-3">
                                    <?php $__currentLoopData = [
                                        'Comprada' => $quantidadeComprada,
                                        'Devolvida' => $quantidadeDevolvida,
                                        'Restante' => $quantidadeRestante,
                                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rotulo => $quantidade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-4">
                                            <div class="quantidade-card <?php echo e($rotulo === 'Devolvida' ? 'border-warning' : ''); ?>">
                                                <small><?php echo e($rotulo); ?></small>
                                                <strong><?php echo e(number_format($quantidade, 3, ',', '.')); ?></strong>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2">
                                    <span>Valor unitário</span>
                                    <strong>R$ <?php echo e(number_format($valorUnitario, 2, ',', '.')); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <span>Valor da devolução</span>
                                    <strong>R$ <?php echo e(number_format($subtotalDevolucao, 2, ',', '.')); ?></strong>
                                </div>
                            </section>
                        </div>

                        <div class="col-xl-3">
                            <section class="secao">
                                <div class="secao-titulo">
                                    Evidências
                                    <span class="badge text-bg-secondary float-end"><?php echo e($evidencias->count()); ?></span>
                                </div>
                                <?php if($evidencias->isNotEmpty()): ?>
                                    <div class="evidencias-grid">
                                        <?php $__currentLoopData = $evidencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evidencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>
                                                <a
                                                    href="<?php echo e($evidencia['url']); ?>"
                                                    target="_blank"
                                                    class="evidencia-preview"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e($evidencia['descricao']); ?>"
                                                >
                                                    <?php if(str_starts_with($evidencia['mime_type'], 'image')): ?>
                                                        <img src="<?php echo e($evidencia['url']); ?>" alt="<?php echo e($evidencia['descricao']); ?>">
                                                    <?php else: ?>
                                                        <span class="text-danger text-center">
                                                            <i class="bi bi-file-earmark-pdf fs-2 d-block"></i>
                                                            Documento
                                                        </span>
                                                    <?php endif; ?>
                                                </a>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="evidencia-preview text-muted text-center p-3">
                                        <div>
                                            <i class="bi bi-image fs-2 d-block mb-1"></i>
                                            Nenhuma evidência registrada
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </section>
                        </div>
                    </div>

                    <?php if($possuiTriagem): ?>
                        <section class="secao mt-3 p-0 overflow-hidden">
                            <div class="secao-titulo px-3 pt-3 mb-0">
                                Linhas importadas da triagem
                                <span class="badge text-bg-primary float-end">
                                    <?php echo e($devolucao->lotes->count()); ?> linhas
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover triagem-grid mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center">Linha</th>
                                            <th class="text-end">Qtd.</th>
                                            <th>Embalagem</th>
                                            <th>Conteúdo</th>
                                            <th>Integridade</th>
                                            <th>Validade</th>
                                            <th>Reaproveitamento</th>
                                            <th>Destino</th>
                                            <th>Lote</th>
                                            <th>Status</th>
                                            <th>Observação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $devolucao->lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalhe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $avaliacao = $detalhe->avaliacao;
                                                $statusClasse = match ($detalhe->status_processamento) {
                                                    'Processado' => 'text-bg-success',
                                                    'Cancelado' => 'text-bg-secondary',
                                                    default => 'text-bg-warning',
                                                };
                                            ?>
                                            <tr>
                                                <td class="text-center fw-bold">
                                                    <?php echo e($avaliacao?->ordem ?? $loop->iteration); ?>

                                                </td>
                                                <td class="text-end fw-bold">
                                                    <?php echo e(number_format((float) $detalhe->quantidade, 3, ',', '.')); ?>

                                                </td>
                                                <td><?php echo e(str_replace('_', ' ', $avaliacao?->embalagem ?? '—')); ?></td>
                                                <td><?php echo e(str_replace('_', ' ', $avaliacao?->conteudo ?? '—')); ?></td>
                                                <td><?php echo e(str_replace('_', ' ', $avaliacao?->integridade ?? '—')); ?></td>
                                                <td><?php echo e(str_replace('_', ' ', $avaliacao?->validade_status ?? '—')); ?></td>
                                                <td><?php echo e(str_replace('_', ' ', $avaliacao?->reaproveitamento ?? '—')); ?></td>
                                                <td>
                                                    <span class="badge text-bg-primary">
                                                        <?php echo e(str_replace('_', ' ', $detalhe->destino_estoque)); ?>

                                                    </span>
                                                </td>
                                                <td><?php echo e($detalhe->lote?->numero_lote ?? 'Não identificado'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo e($statusClasse); ?>">
                                                        <?php echo e($detalhe->status_processamento); ?>

                                                    </span>
                                                </td>
                                                <td class="observacao"><?php echo e($avaliacao?->observacao ?: '—'); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-end">
                                                <?php echo e(number_format((float) $devolucao->lotes->sum('quantidade'), 3, ',', '.')); ?>

                                            </th>
                                            <th colspan="7"></th>
                                            <th>
                                                <span class="badge <?php echo e($linhasPendentes ? 'text-bg-warning' : 'text-bg-success'); ?>">
                                                    <?php echo e($linhasPendentes); ?> pendente(s)
                                                </span>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </section>
                    <?php endif; ?>
                    <?php
                        $exigeVoucher = ! empty(
                            $devolucao->romaneio_ocorrencia_id
                        );

                        $possuiVoucher = ! empty(
                            $devolucao->vale_compra_id
                        );

                        $devolucaoEncerrada = $devolucao->estaEncerrada();

                        $requisitoVoucherAtendido =
                            ! $exigeVoucher
                            || $possuiVoucher;

                        $podeConfirmarTriagem =
                            ! $devolucaoEncerrada
                            && $possuiTriagem
                            && $requisitoVoucherAtendido;

                        $podeAprovarInicialmente =
                            ! $devolucaoEncerrada
                            && ! $possuiTriagem
                            && $requisitoVoucherAtendido;
                    ?>

                    <div class="acoes-box mt-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-bold">
                                    Decisão do operador
                                </div>

                                <small class="text-muted">
                                    <?php if($devolucaoEncerrada): ?>
                                        Esta devolução já foi processada e permanece disponível somente para conferência.
                                    <?php elseif($exigeVoucher && ! $possuiVoucher): ?>
                                        Primeiro gere e imprima o comprovante do item
                                        <strong><?php echo e($itemProdutoOperacao); ?></strong>.
                                        Depois, a conferência será liberada.
                                    <?php elseif($possuiTriagem): ?>
                                        <span id="orientacaoTriagem<?php echo e($devolucao->id); ?>">
                                            <strong>Etapa 1:</strong>
                                            confira se a quantidade
                                            <strong><?php echo e($quantidadeOperacao); ?></strong>
                                            do item
                                            <strong><?php echo e($itemProdutoOperacao); ?></strong>,
                                            lote
                                            <strong><?php echo e($loteOperacao); ?></strong>,
                                            está corretamente distribuída nas
                                            <?php echo e($descricaoLinhasTriagem); ?> exibidas acima.
                                            Depois confirme a conferência para liberar
                                            a etapa 2.
                                        </span>
                                    <?php else: ?>
                                        A aprovação processará o destino da devolução
                                        do item
                                        <strong><?php echo e($itemProdutoOperacao); ?></strong>,
                                        quantidade
                                        <strong><?php echo e($quantidadeOperacao); ?></strong>.
                                    <?php endif; ?>
                                </small>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <?php if($exigeVoucher): ?>
                                    <form
                                        method="POST"
                                        action="<?php echo e(route('devolucoes.vale-troca', $devolucao)); ?>"
                                        target="_blank"
                                        onsubmit="window.setTimeout(function () {
                                            window.location.reload();
                                        }, 1500);"
                                    >
                                        <?php echo csrf_field(); ?>

                                        <button
                                            type="submit"
                                            class="btn <?php echo e($possuiVoucher
                                                ? 'btn-outline-secondary'
                                                : 'btn-outline-primary'); ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="<?php echo e($possuiVoucher
                                                ? 'Reimprimir o voucher já emitido sem gerar outro registro.'
                                                : 'Gerar o voucher, registrar o crédito e abrir o documento para impressão.'); ?>"
                                        >
                                            <i class="bi bi-printer me-1"></i>

                                            <?php echo e($possuiVoucher
                                                ? 'Reimprimir comprovante'
                                                : 'Imprimir comprovante'); ?>

                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <a
                                        href="<?php echo e(route('devolucoes.cupom', $devolucao)); ?>"
                                        class="btn btn-outline-secondary"
                                        target="_blank"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Abrir o comprovante desta devolução para impressão."
                                    >
                                        <i class="bi bi-printer me-1"></i>
                                        Imprimir comprovante
                                    </a>
                                <?php endif; ?>

                                <?php if(! $devolucaoEncerrada): ?>
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRejeitar<?php echo e($devolucao->id); ?>"
                                        title="Rejeitar toda a devolução mediante justificativa."
                                    >
                                        <i class="bi bi-x-circle me-1"></i>
                                        Rejeitar
                                    </button>

                                    <?php if($possuiTriagem): ?>
                                        <button
                                            type="button"
                                            id="confirmarTriagem<?php echo e($devolucao->id); ?>"
                                            class="btn <?php echo e($podeConfirmarTriagem
                                                ? 'btn-primary'
                                                : 'btn-secondary'); ?>"
                                            <?php if(! $podeConfirmarTriagem): echo 'disabled'; endif; ?>
                                            data-confirmar-triagem
                                            data-devolucao-id="<?php echo e($devolucao->id); ?>"
                                            data-mensagem-confirmacao="<?php echo e($confirmacaoConferir); ?>"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="<?php echo e(! $requisitoVoucherAtendido
                                                ? "Imprima o comprovante antes de conferir o item {$itemProdutoOperacao}."
                                                : "Confirmar a conferência do item {$itemProdutoOperacao}, quantidade {$quantidadeOperacao}, lote {$loteOperacao}."); ?>"
                                        >
                                            <i class="bi <?php echo e($podeConfirmarTriagem
                                                ? 'bi-clipboard-check'
                                                : 'bi-lock-fill'); ?> me-1"></i>

                                            1. Confirmar item <?php echo e($itemProdutoOperacao); ?>

                                        </button>
                                    <?php endif; ?>

                                    <form
                                        method="POST"
                                        action="<?php echo e(route('devolucoes.aprovar', $devolucao)); ?>"
                                        data-mensagem-confirmacao="<?php echo e($confirmacaoAprovar); ?>"
                                        onsubmit="return window.confirm(
                                            this.dataset.mensagemConfirmacao
                                        );"
                                    >
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>

                                        <button
                                            type="submit"
                                            id="aprovarDevolucao<?php echo e($devolucao->id); ?>"
                                            class="btn <?php echo e($podeAprovarInicialmente
                                                ? 'btn-success'
                                                : 'btn-secondary'); ?>"
                                            <?php if(! $podeAprovarInicialmente): echo 'disabled'; endif; ?>
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="<?php echo e(! $requisitoVoucherAtendido
                                                ? "Imprima o comprovante antes de aprovar a devolução do item {$itemProdutoOperacao}."
                                                : ($possuiTriagem
                                                    ? "Primeiro confirme a conferência do item {$itemProdutoOperacao}."
                                                    : "Aprovar e processar a devolução do item {$itemProdutoOperacao}.")); ?>"
                                        >
                                            <i class="bi <?php echo e($podeAprovarInicialmente
                                                ? 'bi-check-circle'
                                                : 'bi-lock-fill'); ?> me-1"></i>

                                            <?php echo e($possuiTriagem
                                                ? "2. Aprovar devolução — {$nomeProdutoOperacao}"
                                                : "Aprovar devolução — {$nomeProdutoOperacao}"); ?>

                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span
                                        class="badge text-bg-success align-self-center px-3 py-2"
                                    >
                                        <i class="bi bi-check-circle me-1"></i>
                                        Devolução concluída
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <div class="modal fade" id="modalRejeitar<?php echo e($devolucao->id); ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="<?php echo e(route('devolucoes.rejeitar', $devolucao)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="modal-header">
                                <h5 class="modal-title">Rejeitar devolução #<?php echo e($devolucao->id); ?></h5>
                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Fechar"
                                ></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning py-2">
                                    Informe claramente por que o material não será aceito.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Motivo da rejeição</label>
                                    <input type="text" name="motivo_rejeicao" class="form-control" maxlength="255" required>
                                </div>
                                <div>
                                    <label class="form-label">Observação</label>
                                    <textarea name="observacao" class="form-control" rows="4" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal"
                                    data-bs-toggle="tooltip"
                                    title="Fechar sem rejeitar a devolução."
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                    data-bs-toggle="tooltip"
                                    title="Confirmar a rejeição de toda a devolução."
                                >
                                    Confirmar rejeição
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="alert alert-success text-center py-4">
                <i class="bi bi-check-circle fs-2 d-block mb-2"></i>
                <strong>Nenhuma devolução aguardando tratamento.</strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document
            .querySelectorAll('[data-confirmar-triagem]')
            .forEach(function (botaoConfirmar) {
                botaoConfirmar.addEventListener('click', function () {
                    const confirmou = window.confirm(
                        botaoConfirmar.dataset.mensagemConfirmacao
                    );

                    if (! confirmou) {
                        return;
                    }

                    const devolucaoId =
                        botaoConfirmar.dataset.devolucaoId;
                    const botaoAprovar = document.getElementById(
                        'aprovarDevolucao' + devolucaoId
                    );
                    const orientacao = document.getElementById(
                        'orientacaoTriagem' + devolucaoId
                    );

                    if (! botaoAprovar) {
                        return;
                    }

                    botaoConfirmar.disabled = true;
                    botaoConfirmar.classList.remove('btn-primary');
                    botaoConfirmar.classList.add('btn-success');
                    botaoConfirmar.innerHTML =
                        '<i class="bi bi-check-circle-fill me-1"></i>'
                        + '1. Item conferido';

                    botaoAprovar.disabled = false;
                    botaoAprovar.classList.remove('btn-secondary');
                    botaoAprovar.classList.add('btn-success');

                    const iconeAprovar =
                        botaoAprovar.querySelector('i');

                    if (iconeAprovar) {
                        iconeAprovar.classList.remove('bi-lock-fill');
                        iconeAprovar.classList.add('bi-check-circle');
                    }

                    if (orientacao) {
                        orientacao.innerHTML =
                            '<strong>Etapa 1 concluída.</strong> '
                            + 'O item foi conferido. Agora execute a etapa 2 '
                            + 'para aprovar e processar a devolução.';
                    }

                    const tooltipConfirmar =
                        bootstrap.Tooltip.getInstance(botaoConfirmar);
                    const tooltipAprovar =
                        bootstrap.Tooltip.getInstance(botaoAprovar);

                    if (tooltipConfirmar) {
                        tooltipConfirmar.dispose();
                    }

                    if (tooltipAprovar) {
                        tooltipAprovar.dispose();
                    }
                });
            });

        document
            .querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(function (elemento) {
                /*
                 * Elementos que abrem modal usam data-bs-toggle="modal"
                 * e, por isso, não entram nesta seleção.
                 */
                bootstrap.Tooltip.getOrCreateInstance(elemento);
            });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\devolucoes\pendentes.blade.php ENDPATH**/ ?>