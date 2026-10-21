<?php $__env->startSection('content'); ?>

<style>
.rec01-page {
            background: #f3f5f8;
            color: #253247;
        }

        .rec-container {
            max-width: 1150px;
        }

        .rec-card {
            border: 1px solid #dee3eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0,0,0,.04);
        }

        .rec-card .card-header {
            padding: 16px 20px;
            font-weight: 600;
            background: white;
        }

        .rec-card .card-body {
            padding: 22px;
        }

        .rec-stat {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #dee3eb;
            height: 100%;
        }

        .rec-stat strong {
            display: block;
            font-size: 30px;
        }

        .rec-falha {
            border-left: 5px solid #dc3545;
        }

        code {
            overflow-wrap: anywhere;
        }

        .small-detail {
            font-size: 13px;
            color: #64748b;
        }
    
</style>

<div class="rec01-page">


<div class="container rec-container py-5">

    <header class="bg-white p-4 rounded-3 shadow-sm mb-4">
        <div class="d-flex justify-content-between
                    align-items-center flex-wrap gap-3">

            <div>
                <div class="text-muted small">
                    Administração / REC-01
                </div>

                <h3 class="mb-1">
                    Central de Recuperação de Dados
                </h3>

                <p class="text-muted mb-0">
                    Diagnóstico geral das tabelas do ERP
                </p>
            </div>

            <?php if(!$concluido): ?>
                <span class="badge bg-secondary">
                    Diagnóstico inconclusivo
                </span>
            <?php elseif($totalAusentes > 0): ?>
                <span class="badge bg-danger">
                    <?php echo e($totalAusentes); ?> falha(s) identificada(s)
                </span>
            <?php else: ?>
                <span class="badge bg-success">
                    Nenhuma ausência detectada
                </span>
            <?php endif; ?>

        </div>
    </header>

    <?php if($erro): ?>
        <div class="alert alert-warning">
            <?php echo e($erro); ?>

        </div>
    <?php elseif($totalAusentes > 0): ?>
        <div class="alert alert-danger p-4">
            <h4>Atenção: problemas identificados nos dados</h4>

            <p class="mb-0">
                O sistema encontrou uma ou mais tabelas
                esperadas que não estão disponíveis no
                banco de dados. Algumas funcionalidades
                podem estar interrompidas.
            </p>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Não foram identificadas tabelas ausentes
            entre as referências reconhecidas pela análise.
            Isso não garante a integridade completa do banco.
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="rec-stat">
                <div class="text-muted">Tabelas esperadas</div>
                <strong><?php echo e($totalEsperadas); ?></strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="rec-stat">
                <div class="text-muted">Tabelas presentes</div>
                <strong class="text-success">
                    <?php echo e($totalPresentes); ?>

                </strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="rec-stat">
                <div class="text-muted">Tabelas ausentes</div>
                <strong class="text-danger">
                    <?php echo e($totalAusentes); ?>

                </strong>
            </div>
        </div>

    </div>

    <section class="card rec-card mb-4">
        <div class="card-header">
            1. Tabelas com falhas identificadas
        </div>

        <div class="card-body">

            <?php $__empty_1 = true; $__currentLoopData = $falhas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $falha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <div class="border rounded p-3 mb-3 rec-falha">

                    <div class="d-flex justify-content-between
                                align-items-center flex-wrap gap-2">

                        <h5 class="mb-0">
                            <?php echo e($index + 1); ?>.
                            <code><?php echo e($falha['tabela']); ?></code>
                        </h5>

                        <span class="badge bg-danger">
                            Tabela ausente
                        </span>
                    </div>

                    <div class="mt-3">
                        <strong>Módulo provável:</strong>
                        <?php echo e($falha['modulo']); ?>

                    </div>

                    <div class="mt-2">
                        <strong>Possíveis nomes relacionados:</strong>

                        <?php if(count($falha['similares']) > 0): ?>
                            <?php $__currentLoopData = $falha['similares']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <code class="me-2"><?php echo e($nome); ?></code>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <span class="text-muted">
                                Nenhum identificado
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="small-detail mt-2 mb-0">
                        Nomes semelhantes são apenas sugestões,
                        não comprovam que uma tabela foi renomeada.
                    </p>

                    <div class="mt-3">
                        <strong>Relacionamentos identificados:</strong>
                        <?php echo e(count($falha['dependencias'])); ?>

                    </div>

                    <?php if(count($falha['dependencias']) > 0): ?>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tabela relacionada</th>
                                        <th>Coluna</th>
                                        <th>Referência</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $falha['dependencias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($dep->TABLE_NAME); ?></td>
                                            <td><?php echo e($dep->COLUMN_NAME); ?></td>
                                            <td>
                                                <?php echo e($dep->REFERENCED_TABLE_NAME); ?>

                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <details class="mt-3">
                        <summary class="small text-primary">
                            Ver referências no código
                        </summary>

                        <div class="mt-2 small">
                            <?php $__currentLoopData = $falha['origens']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $origem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <code><?php echo e($origem); ?></code>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </details>

                    <div class="alert alert-warning mt-3 mb-0">
                        Recuperação não autorizada.
                        Nenhuma alteração foi realizada.
                    </div>

                </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">
                    Nenhuma ausência foi confirmada pelo
                    inventário de código analisado.
                </p>
            <?php endif; ?>

        </div>
    </section>

    <section class="card rec-card mb-4">
        <div class="card-header">
            2. Backups automáticos localizados
        </div>

        <div class="card-body">

            <?php if(count($backups) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Data</th>
                                <th>Tamanho</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <code><?php echo e($backup['nome']); ?></code>
                                    </td>

                                    <td>
                                        <?php echo e(date('d/m/Y H:i', $backup['data'])); ?>

                                    </td>

                                    <td>
                                        <?php echo e(number_format(
                                            $backup['tamanho'] / 1048576,
                                            2, ',', '.'
                                        )); ?> MB
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mb-0">
                    Backups localizados no armazenamento.
                    A compatibilidade de cada backup com
                    cada tabela ainda será avaliada na simulação.
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    Nenhum backup foi localizado nesta consulta.
                </div>
            <?php endif; ?>

        </div>
    </section>

    <section class="card rec-card mb-4">
        <div class="card-header">
            3. Como deseja prosseguir?
        </div>

        <div class="card-body">

            <p>
                O diagnóstico identifica tabelas ausentes.
                A próxima etapa avaliará individualmente
                os impactos de cada recuperação.
            </p>

            <div class="alert alert-warning">
                Não execute restaurações ou renomeações
                sem verificar os relacionamentos e as
                consequências para os demais módulos.
            </div>

            <div class="d-grid gap-2">

                <button class="btn btn-primary"
                        type="button"
                        disabled>
                    Simular recuperação — próxima etapa
                </button>

                <button class="btn btn-outline-secondary"
                        type="button"
                        onclick="copiarDiagnostico()">
                    Copiar diagnóstico para o desenvolvedor
                </button>
            </div>

            <div id="rec01-msg"
                 class="small mt-3"
                 role="status"
                 aria-live="polite"></div>

        </div>
    </section>

    <section class="card rec-card mb-4">
        <div class="card-body">

            <h5>Não se sente seguro para continuar?</h5>

            <p class="mb-0">
                Interrompa o procedimento e entre em contato
                com o desenvolvedor do ERP ou responsável
                técnico. Encaminhe o diagnóstico para
                facilitar a análise e evitar alterações indevidas.
            </p>

        </div>
    </section>

    <footer class="text-center text-muted small py-3">
        REC-01 V4 — Inventário automático e diagnóstico
        somente leitura. Nenhuma restauração habilitada.
    </footer>

</div>

<?php
    $rec01Relatorio = [
        'banco' => $banco,
        'concluido' => $concluido,
        'esperadas' => $totalEsperadas,
        'presentes' => $totalPresentes,
        'ausentes' => $totalAusentes,
        'falhas' => array_map(
            fn ($f) => [
                'tabela' => $f['tabela'],
                'modulo' => $f['modulo'],
                'similares' => $f['similares'],
                'dependencias' => count($f['dependencias']),
            ],
            $falhas
        ),
        'backups' => array_column($backups, 'nome'),
    ];
?>

<script>
const dadosREC01 = <?php echo e(\Illuminate\Support\Js::from($rec01Relatorio)); ?>;

async function copiarDiagnostico() {
    const texto = [
        'REC-01 V4 - Diagnóstico Geral',
        'Banco: ' + dadosREC01.banco,
        'Diagnóstico concluído: ' +
            (dadosREC01.concluido ? 'Sim' : 'Não'),
        'Tabelas esperadas: ' + dadosREC01.esperadas,
        'Tabelas presentes: ' + dadosREC01.presentes,
        'Tabelas ausentes: ' + dadosREC01.ausentes,
        '',
        ...dadosREC01.falhas.map(f =>
            'Tabela: ' + f.tabela +
            ' | Módulo provável: ' + f.modulo +
            ' | Similares: ' + f.similares.join(', ') +
            ' | Relacionamentos: ' + f.dependencias
        ),
        '',
        'Backups localizados:',
        ...dadosREC01.backups,
        '',
        'Recuperação executada: NÃO'
    ].join('\n');

    const msg = document.getElementById('rec01-msg');

    try {
        await navigator.clipboard.writeText(texto);
        msg.className = 'small mt-3 text-success';
        msg.textContent = 'Diagnóstico copiado.';
    } catch (error) {
        msg.className = 'small mt-3 text-danger';
        msg.textContent = 'Não foi possível copiar.';
    }
}
</script>


</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/admin/recuperacao/painel.blade.php ENDPATH**/ ?>