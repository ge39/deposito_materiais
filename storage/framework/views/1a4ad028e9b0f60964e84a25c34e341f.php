

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Novo Pedido de Compra</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('pedidos.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        
        <div class="card p-3 mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label>Fornecedor</label>
                    <select name="fornecedor_id" id="fornecedorSelect" class="form-control" required>
                        <option value="">Selecione</option>
                        <?php $__currentLoopData = $fornecedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fornecedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($fornecedor->id); ?>"><?php echo e($fornecedor->nome); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Data do Pedido</label>
                    <input type="dateTime" name="data_pedido" class="form-control" value="<?php echo e(now()->format('Y-m-d H:i:s')); ?>" readOnly>
                </div>
            </div>
        </div>

        
        <div class="card p-3 mb-4" id="scrollTableContainer" style="max-height: 400px; overflow-y: auto;">
            <h5>Itens do Pedido</h5>
            <table class="table table-bordered" id="itensTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produto</th>
                        <th>Unidade</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário (R$)</th>
                        <th>Subtotal (R$)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <div class="text-end mt-2">
                <button type="button" class="btn btn-warning" id="addItem">Adicionar Item</button>
                <button type="submit" class="btn btn-success">Salvar Pedido</button>
                <a href="<?php echo e(route('pedidos.index')); ?>" class="btn btn-secondary">Voltar</a> 
            </div>
        </div>
    </form>
</div>

<script>
    const produtos = <?php echo json_encode($produtos->load('unidadeMedida'), 15, 512) ?>;
    const table = document.querySelector('#itensTable tbody');
    const fornecedorSelect = document.getElementById('fornecedorSelect');
    const scrollContainer = document.getElementById('scrollTableContainer');
    let fornecedorSelecionado = null;
    let itemIndex = 0;

    function atualizarIndices() {
        table.querySelectorAll('tr').forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    function atualizarTotalGeral() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(sub => {
            total += parseFloat(sub.value || 0);
        });
    }

    function atualizarValores(row) {
        const selectProduto = row.querySelector('.produto-select');
        const quantidade = row.querySelector('.quantidade');
        const valor = row.querySelector('.valor_unitario');
        const subtotal = row.querySelector('.subtotal');
        const unidadeInput = row.querySelector('.unidade_medida');

        selectProduto.addEventListener('change', () => {
            const selectedOption = selectProduto.selectedOptions[0];
            const produto = produtos.find(p => p.id == selectProduto.value);
            const valorUnit = parseFloat(produto?.preco_custo || 0);
            valor.value = valorUnit.toFixed(2);
            unidadeInput.value = produto?.unidade_medida?.nome || '';
            subtotal.value = (valorUnit * parseFloat(quantidade.value || 0)).toFixed(2);
            atualizarTotalGeral();
        });

        quantidade.addEventListener('input', () => {
            subtotal.value = (parseFloat(valor.value || 0) * parseFloat(quantidade.value || 0)).toFixed(2);
            atualizarTotalGeral();
        });

        row.querySelector('.removeItem').addEventListener('click', () => {
            row.remove();
            atualizarIndices();
            atualizarTotalGeral();
            if (!table.querySelectorAll('tr').length) {
                fornecedorSelecionado = null;
                fornecedorSelect.disabled = false;
            }
        });
    }

    document.getElementById('addItem').addEventListener('click', () => {
        const fornecedorAtual = fornecedorSelect.value;

        if (!fornecedorAtual) {
            alert('Selecione um fornecedor antes de adicionar itens.');
            return;
        }

        if (fornecedorSelecionado && fornecedorSelecionado !== fornecedorAtual) {
            alert('Não é permitido trocar o fornecedor após adicionar itens.');
            return;
        }

        const rows = table.querySelectorAll('tr');
        if (rows.length > 0) {
            const lastRowSelect = rows[rows.length - 1].querySelector('.produto-select');
            if (!lastRowSelect.value) {
                alert('Selecione o produto do item anterior antes de adicionar outro.');
                return;
            }
        }

        if (!fornecedorSelecionado) {
            fornecedorSelecionado = fornecedorAtual;
            fornecedorSelect.setAttribute('data-locked', 'true');
        }

        const row = document.createElement('tr');
        itemIndex++;
        let options = '<option value="">Selecione</option>';
        produtos.forEach(p => options += `<option value="${p.id}" data-preco-custo="${p.preco_custo}">${p.nome}</option>`);

        row.innerHTML = `
            <td class="text-center"></td>
            <td>
                <select name="itens[${itemIndex}][produto_id]" class="form-control produto-select" required>
                    ${options}
                </select>
            </td>
            <td><input type="text" name="itens[${itemIndex}][unidade_medida]" class="form-control unidade_medida" readonly></td>
            <td><input type="number" name="itens[${itemIndex}][quantidade]" class="form-control quantidade" min="1" value="1" required></td>
            <td><input type="text" name="itens[${itemIndex}][valor_unitario]" class="form-control valor_unitario" readonly></td>
            <td><input type="text" name="itens[${itemIndex}][subtotal]" class="form-control subtotal" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm removeItem">Remover</button></td>
        `;

        table.appendChild(row);
        atualizarValores(row);
        atualizarIndices();
        scrollContainer.scrollTop = scrollContainer.scrollHeight;

        const produtoSelect = row.querySelector('.produto-select');
        const unidadeInput = row.querySelector('.unidade_medida');

        produtoSelect.addEventListener('change', () => {
            const selectedValue = produtoSelect.value;
            const duplicado = Array.from(table.querySelectorAll('.produto-select'))
                .filter(s => s !== produtoSelect)
                .some(s => s.value === selectedValue);
            if (duplicado) {
                alert('Este produto já foi adicionado para este fornecedor.');
                produtoSelect.value = '';
                unidadeInput.value = '';
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pedidos/create.blade.php ENDPATH**/ ?>