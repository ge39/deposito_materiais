

<?php $__env->startSection('content'); ?>

<style>
    /* Container principal */
    .fechamento-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Título da página */
    .fechamento-container h4 {
        font-size: 2rem;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: 30px;
    }

    /* Card do caixa */
    .card-header {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .card-body {
        font-size: 1rem;
    }

    /* Badges de status */
    .badge-status {
        font-size: 0.95rem;
        padding: 0.5em 0.75em;
    }

    /* Inputs */
    .form-control {
        font-size: 1rem;
        border-radius: 6px;
        padding: 10px 12px;
        transition: all 0.2s ease-in-out;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 8px rgba(13,110,253,0.2);
        outline: none;
        transform: scale(1.02);
    }

    /* Destaque para valores financeiros */
    .input-financeiro:focus {
        border-color: #198754;
        background-color: #e6f4ea;
        box-shadow: 0 0 10px rgba(25,135,84,0.2);
    }

    /* Cards de entrada e saída */
    .card.shadow-sm {
        border-radius: 10px;
        box-shadow: 0 6px 15px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    /* Headers de cartões coloridos */
    .card-header.bg-success {
        background: #198754;
    }
    .card-header.bg-danger {
        background: #dc3545;
    }
    .card-header.bg-primary {
        background: #0d6efd;
    }

    /* Row dos inputs */
    .row.align-items-center {
        margin-bottom: 10px;
    }

    .fw-semibold {
        font-weight: 600;
    }

    /* Botões finais */
    .btn-submit {
        font-size: 1.05rem;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }

    .btn-submit:hover {
        transform: scale(1.03);
    }
</style>

<div class="fechamento-container">

    <h4 class="bg-secondary text-center text-white ">Fechamento do Caixa <?php echo e($caixa->id); ?> </h4>

    <form method="POST" action="<?php echo e(route('fechamento.fechar', $caixa->id)); ?>">
        <?php echo csrf_field(); ?>

        
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                ✅ Dados do Caixa - Fechamento #<?php echo e($caixa->id); ?> - Sem Movimento
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>ID (Caixa)</strong><br><?php echo e($caixa->id); ?></div>
                    <div class="col-md-4"><strong>Operador</strong><br><?php echo e($caixa->usuario->name ?? 'Não identificado'); ?></div>
                    <div class="col-md-4"><strong>Terminal ID</strong><br><?php echo e($caixa->terminal_id); ?></div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Abertura</strong><br><?php echo e(\Carbon\Carbon::parse($caixa->data_abertura)->format('d/m/Y H:i')); ?></div>
                    
                    <div class="col-md-4">
                        <strong>Status</strong><br>
                        <?php
                            $statusLabel = match($caixa->status) {
                                'aberto' => 'Aberto',
                                'pendente' => 'Pendente',
                                'fechado' => 'Fechado',
                                'fechado_sem_movimento' => 'Fechado sem movimento',
                                'inconsistente' => 'Inconsistente',
                                default => ucfirst($caixa->status),
                            };
                        ?>
                        <span class="badge badge-status bg-success"><?php echo e($statusLabel); ?></span>
                    </div>
                    <div class="col-md-4"><strong>Fundo de Troco</strong><br>R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?></div>
                </div>
            </div>
        </div>

        
        <?php if(!$caixa->possuiVendas()): ?>
            <div class="card mb-4 border-success shadow-sm">
                <div class="card-header bg-success text-white">Fechamento sem Movimentação</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="motivo_fechamento" class="fw-bold">Motivo do fechamento</label>
                        <select name="motivo_fechamento" id="motivo_fechamento" class="form-control" required>
                            <option value="">Selecione o motivo</option>
                            <option value="Caixa aberto sem movimento">Caixa aberto sem movimento</option>
                            <option value="Troca de operador do caixa">Troca de operador do caixa</option>
                            <option value="Sistema indisponível">Sistema indisponível</option>
                            <option value="Loja não abriu">Loja não abriu</option>
                            <option value="Erro hardware">Erro hardware</option>
                            <option value="Erro operacional">Erro operacional</option>
                        </select>
                    </div>
                </div>
            </div>

        
        <?php else: ?>
            <div class="row mt-4">

                
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white fw-bold">Valores por Forma de Pagamento</div>
                        <div class="card-body">
                            <?php $__currentLoopData = ['dinheiro'=>'Dinheiro','pix'=>'Pix','carteira'=>'Carteira','cartao_debito'=>'Cartão Débito','cartao_credito'=>'Cartão Crédito']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="row align-items-center">
                                    <div class="col-4 fw-semibold"><?php echo e($label); ?></div>
                                    <div class="col-8">
                                        <input type="number" step="0.01" name="<?php echo e($name); ?>" class="form-control input-financeiro" value="<?php echo e(old($name,0)); ?>" required>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white fw-bold">Bandeiras de Cartão</div>
                        <div class="card-body">
                            <?php $__currentLoopData = ['bandeira_visa'=>'Visa','bandeira_mastercard'=>'Mastercard','bandeira_elo'=>'Elo','bandeira_amex'=>'Amex','bandeira_hipercard'=>'Hipercard']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="row align-items-center">
                                    <div class="col-4 fw-semibold"><?php echo e($label); ?></div>
                                    <div class="col-8">
                                        <input type="number" step="0.01" name="<?php echo e($name); ?>" class="form-control input-financeiro" value="<?php echo e(old($name,0)); ?>">
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row mt-4">
                
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white fw-bold">Entradas de Caixa</div>
                        <div class="card-body">
                            <?php $__currentLoopData = ['entrada_suprimento'=>'Suprimento','entrada_ajuste'=>'Ajuste Positivo','entrada_devolucao'=>'Devolução em Dinheiro','entrada_outros'=>'Outras Entradas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="row align-items-center">
                                    <div class="col-4 fw-bold"><?php echo e($label); ?></div>
                                    <div class="col-8">
                                        <input type="number" step="0.01" name="<?php echo e($name); ?>" class="form-control input-financeiro text-end" value="<?php echo e(old($name,0)); ?>">
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white fw-bold">Saídas de Caixa</div>
                        <div class="card-body">
                            <?php $__currentLoopData = ['saida_sangria'=>'Sangria','saida_despesa'=>'Despesas','saida_ajuste'=>'Ajuste Negativo','saida_outros'=>'Outras Saídas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="row align-items-center">
                                    <div class="col-4 fw-bold"><?php echo e($label); ?></div>
                                    <div class="col-8">
                                        <input type="number" step="0.01" name="<?php echo e($name); ?>" class="form-control input-financeiro text-end" value="<?php echo e(old($name,0)); ?>">
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-success btn-submit">Confirmar Fechamento</button>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary btn-submit">Cancelar</a>
        </div>

    </form>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/fechamento_caixa/fechamento.blade.php ENDPATH**/ ?>