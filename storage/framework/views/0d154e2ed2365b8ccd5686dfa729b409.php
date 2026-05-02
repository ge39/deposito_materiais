

<?php $__env->startSection('content'); ?>

<style>
    /* Centralização DataTables */
    div.dataTables_info,
    div.dataTables_paginate {
        text-align: center !important;
        float: none !important;
        justify-content: center !important;
    }

    .dataTables_paginate {
        display: flex;
        justify-content: center;
        margin-top: 10px;
    }

    .dataTables_wrapper .row {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Zebra rows */
    #tabela-clientes tbody tr:nth-child(odd) {
        background-color: #f8f9fa;
    }

    #tabela-clientes tbody tr:nth-child(even) {
        background-color: #ffffff;
    }

    #tabela-clientes tbody tr:hover {
        background-color: #e9f5ff !important;
        transition: 0.2s;
    }
</style>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📊 Controle de Crédito de Clientes</h2>

        <div>
            <button class="btn btn-danger btn-sm" onclick="filtrar('bloqueado')">🔴 Bloqueados</button>
            <button class="btn btn-warning btn-sm" onclick="filtrar('inativo')">🟡 Inativos</button>
            <button class="btn btn-success btn-sm" onclick="filtrar('ativo')">🟢 Ativos</button>
            <button class="btn btn-secondary btn-sm" onclick="filtrar('')">Todos</button>
        </div>
    </div>

    <div class="mb-3">
        <select id="filtroStatus" class="form-select" style="max-width:250px;">
            <option value="">Todos</option>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
            <option value="bloqueado">Bloqueado</option>
        </select>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table id="tabela-clientes" class="table table-hover table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Limite</th>
                        <th>Disponível</th>
                        <th>Usado</th>
                        <th>% Uso</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <?php
                            $percentual = $c->limite_credito > 0
                                ? ($c->total_usado / $c->limite_credito) * 100
                                : 0;

                            $statusUso = $c->credito_disponivel < 0
                                ? 'ESTOURADO'
                                : ($percentual >= 80 ? 'ALTO' : 'OK');
                        ?>

                        <tr data-status="<?php echo e($c->status_credito); ?>">

                            <td><?php echo e($c->cliente_id); ?></td>

                            <td class="text-start">
                                <strong><?php echo e($c->nome); ?></strong>
                            </td>

                            <td>R$ <?php echo e(number_format($c->limite_credito, 2, ',', '.')); ?></td>

                            <td>R$ <?php echo e(number_format($c->credito_disponivel, 2, ',', '.')); ?></td>

                            <td>R$ <?php echo e(number_format($c->total_usado, 2, ',', '.')); ?></td>

                            <td>
                                <span class="badge bg-info">
                                    <?php echo e(number_format($percentual, 1)); ?>%
                                </span>
                            </td>

                            <td>
                                <?php if($c->status_credito == 'bloqueado'): ?>
                                    <span class="badge bg-danger">🔴 BLOQUEADO</span>

                                <?php elseif($c->status_credito == 'inativo'): ?>
                                    <span class="badge bg-secondary">🟡 INATIVO</span>

                                <?php elseif($statusUso == 'ESTOURADO'): ?>
                                    <span class="badge bg-danger">🔴 ESTOURADO</span>

                                <?php elseif($statusUso == 'ALTO'): ?>
                                    <span class="badge bg-warning text-dark">🟡 ALTO USO</span>

                                <?php else: ?>
                                    <span class="badge bg-success">🟢 OK</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if($c->status_credito == 'bloqueado'): ?>
                                   <form method="POST" action="/clientes/<?php echo e($c->cliente_id); ?>/desbloquear" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            🔓 Desbloquear
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/clientes/<?php echo e($c->cliente_id); ?>/bloquear" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            🚫 Bloquear
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>

            </table>
                
        </div>
    </div>

</div>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    let tabela;

    $(document).ready(function () {

        tabela = $('#tabela-clientes').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '📥 Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: '📄 PDF',
                    className: 'btn btn-danger btn-sm'
                }
            ],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ até _END_ de _TOTAL_ registros",
                paginate: {
                    next: "Próximo",
                    previous: "Anterior"
                }
            }
        });

        // Filtro por status real (corrigido)
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

            let status = $('#filtroStatus').val();
            let rowStatus = $(tabela.row(dataIndex).node()).data('status');

            if (!status || status === rowStatus) {
                return true;
            }

            return false;
        });

        $('#filtroStatus').on('change', function () {
            tabela.draw();
        });

    });

    function filtrar(status) {
        $('#filtroStatus').val(status);
        tabela.draw();
    }

    // function desbloquearCliente(id, nome) {

    //     if (!confirm(`Tem certeza que deseja DESBLOQUEAR o cliente ${nome}?`)) return;

    //         fetch(`/clientes/${id}/desbloquear`, {
    //             method: 'POST',
    //             headers: {
    //                 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
    //                 'Accept': 'application/json'
    //             }
    //         })
    //     .then(() => location.reload());
    // }

    // function bloquearCliente(id, nome) {

    //     if (!confirm(`Bloquear ${nome}?`)) return;

    //     fetch(`<?php echo e(url('/clientes')); ?>/${id}/bloquear`, {
    //         method: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
    //             'Content-Type': 'application/json',
    //             'Accept': 'application/json'
    //         },
    //         body: JSON.stringify({}) // 👈 importante
    //     })
    //     .then(res => res.text())
    //     .then(data => {
    //         console.log('RESPOSTA:', data);
    //     })
    //     .catch(err => console.error(err));
    // }
</script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/limites/index.blade.php ENDPATH**/ ?>