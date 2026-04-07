


<?php $__env->startSection('content'); ?>
<div class="container">

<h2 class="mb-4">Novo Orçamento</h2>

<?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <strong>Erro!</strong> Verifique os campos obrigatórios.
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?php echo e(route('orcamentos.store')); ?>" method="POST" id="formOrcamento">
    <?php echo csrf_field(); ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <!-- Cliente e Datas -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Cliente *</label>
                    <select name="cliente_id" id="clienteSelect" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cliente->id); ?>">
                                <?php echo e($cliente->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Data</label>
                    <input type="date" name="data_orcamento" class="form-control"
                           value="<?php echo e(date('Y-m-d')); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Validade</label>
                    <input type="date" name="validade" class="form-control"
                           value="<?php echo e(date('Y-m-d', strtotime('+7 days'))); ?>">
                </div>
            </div>

            <hr>

            <h5>Itens do Orçamento</h5>

            <table class="table table-bordered" id="itensTable">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Estoque</th>
                        <th>Quantidade</th>
                        <th>Unidade</th>
                        <th>Preço</th>
                        <th>Subtotal</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="text-end">
                <button type="button" class="btn btn-primary" id="addProduto">+ Adicionar Produto</button>
                <button type="submit" class="btn btn-success">Salvar Orçamento</button>
                <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary">Voltar</a>
            </div>

            <div class="text-end mt-2">
                <h5>Total: R$ <span id="total">0,00</span></h5>
            </div>

            <div class="mb-3 mt-3">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3">Sem observações</textarea>
            </div>

        </div>
    </div>
</form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const produtos = <?php echo json_encode($produtos, 15, 512) ?>;
    const tableBody = document.querySelector('#itensTable tbody');
    const totalSpan = document.getElementById('total');
    const addBtn = document.getElementById('addProduto');
    const clienteSelect = document.getElementById('clienteSelect');

    let index = 0;

    function criarItem() {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>
                <select name="produtos[${index}][id]" class="form-select produtoSelect" required>
                    <option value="">Selecione...</option>
                    ${produtos.map(p => `
                        <option value="${p.id}"
                            data-preco="${p.preco_venda}"
                            data-unidade="${p.unidade_medida?.nome || ''}"
                            data-estoque="${p.lotes?.reduce((sum, l) => sum + l.quantidade_disponivel, 0) || 0}">
                            ${p.nome}
                        </option>
                    `).join('')}
                </select>
            </td>

            <td><span class="estoqueLabel">0</span></td>

            <td>
                <input type="number" name="produtos[${index}][quantidade]" class="form-control qtd" min="1" value="1" required>
            </td>

            <td>
                <span class="unidadeLabel"></span>
                <input type="hidden" name="produtos[${index}][unidade]" class="unidade">
            </td>

            <td>
                <span class="precoLabel">0,00</span>
                <input type="hidden" name="produtos[${index}][preco_unitario]" class="preco">
            </td>

            <td>
                <span class="subtotalLabel">0,00</span>
                <input type="hidden" class="subtotal">
            </td>

            <td>
                <button type="button" class="btn btn-sm btn-danger remover">X</button>
            </td>
        `;

        tableBody.appendChild(tr);
        index++;
    }

    function atualizarSubtotal() {
        let total = 0;
        tableBody.querySelectorAll('tr').forEach(tr => {
            const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
            const preco = parseFloat(tr.querySelector('.preco').value) || 0;
            const subtotal = qtd * preco;

            tr.querySelector('.subtotal').value = subtotal.toFixed(2);
            tr.querySelector('.subtotalLabel').textContent = subtotal.toFixed(2).replace('.', ',');

            total += subtotal;
        });
        totalSpan.textContent = total.toFixed(2).replace('.', ',');
    }

    addBtn.addEventListener('click', () => {
        if (!clienteSelect.value) {
            alert('Selecione um cliente primeiro!');
            clienteSelect.focus();
            return;
        }

        const lastRow = tableBody.querySelector('tr:last-child');
        if (lastRow) {
            const produto = lastRow.querySelector('.produtoSelect')?.value;
            const qtd = lastRow.querySelector('.qtd')?.value;
            const preco = lastRow.querySelector('.preco')?.value;

            if (!produto) {
                alert('Selecione um produto antes de adicionar outro.');
                lastRow.querySelector('.produtoSelect').focus();
                return;
            }

            if (!qtd || qtd <= 0) {
                alert('Informe uma quantidade válida.');
                lastRow.querySelector('.qtd').focus();
                return;
            }

            if (!preco || preco <= 0) {
                alert('Preço inválido. Selecione o produto novamente.');
                lastRow.querySelector('.produtoSelect').focus();
                return;
            }
        }

        criarItem();
    });

    tableBody.addEventListener('change', e => {
        if (e.target.classList.contains('produtoSelect')) {
            const selected = e.target.options[e.target.selectedIndex];
            const tr = e.target.closest('tr');

            const preco = parseFloat(selected.dataset.preco) || 0;
            const unidade = selected.dataset.unidade || '';
            const estoque = parseFloat(selected.dataset.estoque) || 0;

            tr.querySelector('.preco').value = preco;
            tr.querySelector('.precoLabel').textContent = preco.toFixed(2).replace('.', ',');

            tr.querySelector('.unidade').value = unidade;
            tr.querySelector('.unidadeLabel').textContent = unidade;

            tr.querySelector('.estoqueLabel').textContent = estoque;

            atualizarSubtotal();
        }
    });

    tableBody.addEventListener('input', e => {
        if (e.target.classList.contains('qtd')) {
            const tr = e.target.closest('tr');
            const estoque = parseFloat(tr.querySelector('.estoqueLabel').textContent) || 0;
            const qtd = parseFloat(e.target.value) || 0;

            if (qtd > estoque) {
                e.target.style.border = '2px solid red';
            } else {
                e.target.style.border = '';
            }

            atualizarSubtotal();
        }
    });

    tableBody.addEventListener('click', e => {
        if (e.target.classList.contains('remover')) {
            e.target.closest('tr').remove();
            atualizarSubtotal();
        }
    });

});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/orcamentos/create.blade.php ENDPATH**/ ?>