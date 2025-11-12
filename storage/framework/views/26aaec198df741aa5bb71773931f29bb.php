

<?php $__env->startSection('content'); ?>

<div class="container mt-4">
    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Editar Orçamento #<?php echo e($orcamento->id); ?></h4>
        </div>

        <div class="card-body">
            <?php if(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <form action="<?php echo e(route('orcamentos.update', $orcamento->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
<div class="row mb-3 align-items-center">
    <div class="col-md-8">
        <h5 class="fw-bold text-primary mb-1">
            Código do Orçamento:
            <span class="text-dark">
                <?php echo e(now()->format('Ymd')); ?><?php echo e(str_pad($orcamento->id, 4, '0', STR_PAD_LEFT)); ?>

            </span>
        </h5>
        <small class="text-muted">Gerado em: <?php echo e(now()->format('d/m/Y H:i')); ?></small>
    </div>

    <div class="col-md-4 text-end">
        
        <?php echo DNS1D::getBarcodeHTML(now()->format('Ymd') . str_pad($orcamento->id, 4, '0', STR_PAD_LEFT), 'C128', 1.2, 40); ?>

    </div>
</div>


<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-light fw-bold">Dados do Cliente</div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nome</label>
                <input type="text" class="form-control" value="<?php echo e($orcamento->cliente->nome ?? ''); ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Telefone</label>
                <input type="text" class="form-control" value="<?php echo e($orcamento->cliente->telefone ?? ''); ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">CPF / CNPJ</label>
                <input type="text" class="form-control" value="<?php echo e($orcamento->cliente->documento ?? ''); ?>" readonly>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-12">
                <label class="form-label fw-semibold">Endereço</label>
                <input type="text" class="form-control"
                       value="<?php echo e($orcamento->cliente->endereco ?? ''); ?> <?php echo e($orcamento->cliente->numero ?? ''); ?> - <?php echo e($orcamento->cliente->bairro ?? ''); ?>, <?php echo e($orcamento->cliente->cidade ?? ''); ?>"
                       readonly>
            </div>
        </div>
    </div>
</div>


                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?php echo e($orcamento->observacoes); ?></textarea>
                </div>

                
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Itens do Orçamento</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle" id="tabela-itens">
                            <thead class="table-light">
                                <tr>
                                    <th>Produto</th>
                                    <th width="15%">Quantidade</th>
                                    <th width="20%">Preço Unitário</th>
                                    <th width="15%">Subtotal</th>
                                    <th width="5%">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $orcamento->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <select name="produtos[<?php echo e($loop->index); ?>][id]" class="form-select produto-select" required>
                                            <option value="">Selecione...</option>
                                            <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($produto->id); ?>"
                                                    <?php echo e($item->produto_id == $produto->id ? 'selected' : ''); ?>>
                                                    <?php echo e($produto->nome); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="produtos[<?php echo e($loop->index); ?>][quantidade]" 
                                               class="form-control quantidade" min="0.01" step="0.01"
                                               value="<?php echo e($item->quantidade); ?>" required>
                                    </td>
                                    <td>
                                        <input type="number" name="produtos[<?php echo e($loop->index); ?>][preco_unitario]" 
                                               class="form-control preco" min="0.01" step="0.01"
                                               value="<?php echo e($item->preco_unitario); ?>" required>
                                    </td>
                                    <td class="subtotal text-end fw-semibold">
                                        R$ <?php echo e(number_format($item->subtotal, 2, ',', '.')); ?>

                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger remover-item">&times;</button>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>

                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="adicionar-item">
                            + Adicionar Produto
                        </button>
                    </div>
                </div>

                
                <div class="text-end mt-4">
                    <h5>Total: <span id="total-geral">R$ <?php echo e(number_format($orcamento->total, 2, ',', '.')); ?></span></h5>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success px-4">Salvar Alterações</button>
                    <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabela = document.querySelector('#tabela-itens tbody');
    const btnAdd = document.querySelector('#adicionar-item');

    btnAdd.addEventListener('click', () => {
        const index = tabela.rows.length;
        const novaLinha = `
            <tr>
                <td>
                    <select name="produtos[${index}][id]" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($produto->id); ?>"><?php echo e($produto->descricao); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </td>
                <td><input type="number" name="produtos[${index}][quantidade]" class="form-control quantidade" step="0.01" min="0.01" required></td>
                <td><input type="number" name="produtos[${index}][preco_unitario]" class="form-control preco" step="0.01" min="0.01" required></td>
                <td class="subtotal text-end">R$ 0,00</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger remover-item">&times;</button></td>
            </tr>`;
        tabela.insertAdjacentHTML('beforeend', novaLinha);
    });

    document.addEventListener('input', e => {
        if (e.target.classList.contains('quantidade') || e.target.classList.contains('preco')) {
            const linha = e.target.closest('tr');
            const qtd = parseFloat(linha.querySelector('.quantidade').value) || 0;
            const preco = parseFloat(linha.querySelector('.preco').value) || 0;
            const subtotal = qtd * preco;
            linha.querySelector('.subtotal').textContent = 'R$ ' + subtotal.toFixed(2).replace('.', ',');
            atualizarTotal();
        }
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('remover-item')) {
            e.target.closest('tr').remove();
            atualizarTotal();
        }
    });

    function atualizarTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(td => {
            total += parseFloat(td.textContent.replace(/[R$\s]/g, '').replace(',', '.')) || 0;
        });
        document.getElementById('total-geral').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/edit.blade.php ENDPATH**/ ?>