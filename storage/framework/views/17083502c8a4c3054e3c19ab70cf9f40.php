

<?php $__env->startSection('content'); ?>
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-database-check me-2"></i>
                Backup do Sistema
            </h4>
            <small class="text-muted">
                Segurança, restauração e retenção dos dados do ERP.
            </small>
        </div>

        <form action="<?php echo e(route('backups.gerar')); ?>" method="POST" id="formGerarBackup">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-cloud-arrow-down me-1"></i>
                Gerar Novo Backup
            </button>
        </form>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success py-2">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="alert alert-<?php echo e($statusBackup['classe']); ?> d-flex align-items-center shadow-sm py-3">
        <i class="bi <?php echo e($statusBackup['icone']); ?> fs-3 me-3"></i>
        <div>
            <div class="fw-bold"><?php echo e($statusBackup['texto']); ?></div>
            <small><?php echo e($statusBackup['descricao']); ?></small>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-clock-history me-1"></i>Último Backup</small>
                    <div class="fw-bold mt-1"><?php echo e($resumo['ultimo_backup']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-success border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-archive me-1"></i>Total</small>
                    <div class="fw-bold mt-1"><?php echo e($resumo['total_backups']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-hdd me-1"></i>Espaço Utilizado</small>
                    <div class="fw-bold mt-1"><?php echo e($resumo['espaco_total']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-info border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-calendar-check me-1"></i>Retenção</small>
                    <div class="fw-bold mt-1"><?php echo e($resumo['retencao']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm border-start border-secondary border-4 h-100">
                <div class="card-body py-3">
                    <small class="text-muted"><i class="bi bi-cloud me-1"></i>Driver</small>
                    <div class="fw-bold mt-1"><?php echo e($resumo['driver']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-info-circle me-1"></i>
                    Informações do Ambiente
                </div>
                <div class="card-body small">
                    <div><strong>Driver:</strong> <?php echo e($resumo['driver']); ?></div>
                    <div><strong>Destino:</strong> <?php echo e($resumo['destino']); ?></div>
                    <div><strong>Compressão:</strong> <?php echo e($resumo['compressao']); ?></div>
                    <div><strong>Retenção:</strong> <?php echo e($resumo['retencao']); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-list-check me-1"></i>
                    Recomendações
                </div>
                <div class="card-body small">
                    <div>• Gere backup antes de atualizações, migrações ou alterações críticas.</div>
                    <div>• Baixe uma cópia externa periodicamente.</div>
                    <div>• Teste restauração apenas em ambiente controlado.</div>
                    <div>• Mantenha a retenção configurada conforme o espaço disponível.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-file-earmark-zip me-1"></i>
            Backups disponíveis
        </div>

        <div class="card-body p-0">
            <?php if($arquivos->isEmpty()): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Nenhum backup encontrado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Arquivo</th>
                                <th class="text-center">Data</th>
                                <th class="text-center">Hora</th>
                                <th class="text-end">Tamanho</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $arquivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark-zip text-warning me-2"></i>
                                        <strong><?php echo e($backup['nome']); ?></strong>
                                    </td>
                                    <td class="text-center"><?php echo e($backup['data']); ?></td>
                                    <td class="text-center"><?php echo e($backup['hora']); ?></td>
                                    <td class="text-end"><?php echo e($backup['tamanho']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">Completo</span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($backup['valido']): ?>
                                            <span class="badge bg-success">Válido</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inválido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo e(route('backups.download', $backup['nome'])); ?>"
                                           class="btn btn-success btn-sm"
                                           title="Baixar">
                                            <i class="bi bi-download"></i>
                                        </a>

                                        <form action="<?php echo e(route('backups.restaurar')); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="arquivo" value="<?php echo e($backup['nome']); ?>">
                                            <button type="submit"
                                                    class="btn btn-warning btn-sm"
                                                    title="Restaurar"
                                                    onclick="return confirm('ATENÇÃO: restaurar este backup pode sobrescrever os dados atuais. Deseja continuar?')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>

                                        <form action="<?php echo e(route('backups.destroy', $backup['nome'] )); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    title="Excluir"
                                                    onclick="return confirm('Deseja excluir este backup?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>

                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="card shadow-sm mt-3">
    <div class="card-header bg-light fw-bold">
        <i class="bi bi-clock-history me-1"></i>
        Últimas operações de backup
    </div>

    <div class="card-body p-0">
        <?php if($logs->isEmpty()): ?>
            <div class="p-4 text-center text-muted">
                Nenhum log registrado até o momento.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ação</th>
                            <th>Arquivo</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Tamanho</th>
                            <th class="text-center">Duração</th>
                            <th>Mensagem</th>
                            <th class="text-center">Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($log->acao); ?></strong>
                                </td>

                                <td><?php echo e($log->arquivo ?? '-'); ?></td>

                                <td class="text-center">
                                    <?php if($log->status === 'sucesso'): ?>
                                        <span class="badge bg-success">Sucesso</span>
                                    <?php elseif($log->status === 'erro'): ?>
                                        <span class="badge bg-danger">Erro</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-end">
                                    <?php echo e(number_format(($log->tamanho_bytes ?? 0) / 1024 / 1024, 2, ',', '.')); ?> MB
                                </td>

                                <td class="text-center">
                                    <?php echo e($log->duracao_ms ? number_format($log->duracao_ms / 1000, 2, ',', '.') . 's' : '-'); ?>

                                </td>

                                <td>
                                    <small><?php echo e($log->mensagem ?? '-'); ?></small>
                                </td>

                                <td class="text-center">
                                    <?php echo e(optional($log->created_at)->format('d/m/Y H:i')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="modal fade" id="modalGerandoBackup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5 class="fw-bold mb-2">Gerando backup...</h5>
                <p class="text-muted mb-0">
                    Aguarde. O sistema está copiando banco de dados, arquivos e compactando o pacote.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('formGerarBackup')?.addEventListener('submit', function () {
        const modal = new bootstrap.Modal(document.getElementById('modalGerandoBackup'), {
            backdrop: 'static',
            keyboard: false
        });

        modal.show();
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/backups/index.blade.php ENDPATH**/ ?>