

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Nova Promoção</h4>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary">Voltar</a>
        </div>

        <div class="card-body">
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <strong>Ops!</strong> Verifique os erros abaixo:<br><br>
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($erro); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="formPromocao" action="<?php echo e(route('promocoes.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                
                <div class="mb-3">
                    <label for="tipo_abrangencia" class="form-label">Tipo de Abrangência</label>
                    <select name="tipo_abrangencia" id="tipo_abrangencia" class="form-select" required onchange="toggleCampos(this.value)">
                        <option value="">Selecione...</option>
                        <option value="produto">Por Produto</option>
                        <option value="categoria">Por Categoria</option>
                    </select>
                </div>

                
                <div class="mb-3 d-none" id="campo_produto">
                    <label for="produto_id" class="form-label">Produto</label>
                    <select name="produto_id" id="produto_id" class="form-select" onchange="atualizarPreco()">
                        <option value="">Selecione um produto...</option>
                        <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option 
                                value="<?php echo e($produto->id); ?>"
                                data-preco="<?php echo e($produto->preco_venda); ?>"
                                data-nome="<?php echo e($produto->nome); ?>"
                                data-marca="<?php echo e($produto->marca->nome); ?>"
                                data-unidade="<?php echo e($produto->unidadeMedida->nome); ?>"
                                data-estoque="<?php echo e($produto->quantidade_estoque); ?>"
                                data-fornecedor="<?php echo e($produto->fornecedor->nome ?? ''); ?>"
                                data-descricao="<?php echo e($produto->descricao); ?>"
                            >
                                <?php echo e($produto->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="mb-3 d-none" id="campo_categoria">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="form-select" onchange="atualizarPreco()">
                        <option value="">Selecione uma categoria...</option>
                        <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($categoria->id); ?>"><?php echo e($categoria->nome); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <hr>

                
                <div class="row">

                    
                    <div class="row g-2 align-items-end mb-3">

                        <div class="col-auto">
                            <label class="form-label">Produto</label>
                            <input type="text" id="visu_nome" class="form-control form-control-sm" readonly>
                        </div>

                        <div class="col-auto">
                            <label class="form-label">Marca</label>
                            <input type="text" id="visu_marca" class="form-control form-control-sm" readonly>
                        </div>

                        <div class="col-auto">
                            <label class="form-label">Unidade</label>
                            <input type="text" id="visu_unidade" class="form-control form-control-sm" readonly>
                        </div>

                        <div class="col-auto">
                            <label class="form-label">Estoque</label>
                            <input type="text" id="visu_estoque" class="form-control form-control-sm" readonly>
                        </div>

                        <div class="col-auto">
                            <label class="form-label" style=" size:100%">Fornecedor</label>
                            <input type="text" id="visu_fornecedor" class="form-control form-control-sm" readonly>
                        </div>
                         <div class="col-auto">
                            <label class="form-label">Descrição</label>
                            <input type="text" size="150px" id="visu_descricao" class="form-control form-control-sm" readonly>
                        </div>

                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="preco_venda" class="form-label" style="color:green;font-weight:bold;">Preço Venda (R$)</label>
                        <input type="number" name="preco_original" id="preco_venda" class="form-control" style="color:#000;font-weight:bold;" readonly>
                        
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="desconto_percentual" class="form-label">Desconto (%)</label>
                        <input type="number" name="desconto_percentual" id="desconto_percentual" class="form-control" step="0.01" min="1" oninput="simularPreco()">
                        <div id="msg_desconto" class="text-danger small mt-1"></div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="acrescimo_percentual" class="form-label">Acréscimo (%)</label>
                        <input type="number" name="acrescimo_percentual" id="acrescimo_percentual" class="form-control" step="0.01" min="1" oninput="simularPreco()">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="acrescimo_valor" class="form-label">Acréscimo (R$)</label>
                        <input type="number" name="acrescimo_valor" id="acrescimo_valor" class="form-control" step="0.01" min="1" oninput="simularPreco()">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="preco_simulado" class="form-label" style="color:red;font-weight:bold;">Preço Simulado (R$)</label>
                   <input type="number" name="preco_promocional" id="preco_simulado" class="form-control" style="color:#000;font-weight:bold;" readonly>
                
                </div>

                <hr>

                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="promocao_inicio" class="form-label">Data de Início</label>
                        <input type="date" name="promocao_inicio" id="promocao_inicio" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="promocao_fim" class="form-label">Data de Fim</label>
                        <input type="date" name="promocao_fim" id="promocao_fim" class="form-control" value="<?php echo e(date('Y-m-d', strtotime('+2 days'))); ?>" required>
                    </div>
                </div>

                
                <!-- <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                    <label class="form-check-label" for="status">Ativar promoção imediatamente</label>
                </div> -->

                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Salvar Promoção
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    function toggleCampos(valor) {
        const campoProduto = document.getElementById('campo_produto');
        const campoCategoria = document.getElementById('campo_categoria');

        campoProduto.classList.add('d-none');
        campoCategoria.classList.add('d-none');

        document.getElementById('preco_venda').value = '';
        document.getElementById('preco_simulado').value = '';
        document.getElementById('desconto_percentual').value = '';
        document.getElementById('acrescimo_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';

        document.getElementById('preco_venda').disabled = true;
        document.getElementById('desconto_percentual').disabled = true;
        document.getElementById('acrescimo_percentual').disabled = true;
        document.getElementById('acrescimo_valor').disabled = true;

        if(valor === 'produto') {
            campoProduto.classList.remove('d-none');
        } 
        else if(valor === 'categoria') {
            campoCategoria.classList.remove('d-none');
            document.getElementById('desconto_percentual').disabled = false;
            document.getElementById('acrescimo_percentual').disabled = false;
            document.getElementById('acrescimo_valor').disabled = false;
        }

        atualizarPreco();
    }

    function atualizarPreco() {

        const tipo = document.getElementById('tipo_abrangencia').value;

        if(tipo !== 'produto')
            return;

        const opt = document.getElementById('produto_id').selectedOptions[0];
        if(!opt) return;

        // ■■■ PREENCHER TODOS OS CAMPOS DE VISUALIZAÇÃO ■■■
        document.getElementById('visu_nome').value        = opt.dataset.nome || '';
        document.getElementById('visu_marca').value       = opt.dataset.marca || '';
        document.getElementById('visu_unidade').value     = opt.dataset.unidade || '';
        document.getElementById('visu_estoque').value     = opt.dataset.estoque || '';
        document.getElementById('visu_fornecedor').value  = opt.dataset.fornecedor || '';
        document.getElementById('visu_descricao').value  = opt.dataset.descricao || '';
        
        // preço
        const preco = parseFloat(opt.dataset.preco || 0);

        document.getElementById('preco_venda').value = preco.toFixed(2);
        document.getElementById('preco_simulado').value = '';

        if(preco > 0){
            document.getElementById('desconto_percentual').disabled = false;
            document.getElementById('acrescimo_percentual').disabled = false;
            document.getElementById('acrescimo_valor').disabled = false;
        }
    }

    // Validar desconto máximo
    if (parseFloat(this.value) > 10) {
        msgDesconto.textContent = 'O desconto não pode ser maior que 10%.';
        this.value = ''; // limpa o campo
    } else {
        msgDesconto.textContent = '';
    }
    
    function simularPreco() {
        const precoVenda = parseFloat(document.getElementById('preco_venda').value || 0);

        let precoFinal = precoVenda;
        const desconto = parseFloat(document.getElementById('desconto_percentual').value) || 0;
        const acrescimoPercentual = parseFloat(document.getElementById('acrescimo_percentual').value) || 0;
        const acrescimoValor = parseFloat(document.getElementById('acrescimo_valor').value) || 0;

        if(desconto > 0) precoFinal -= precoVenda * (desconto / 100);
        else if(acrescimoPercentual > 0) precoFinal += precoVenda * (acrescimoPercentual / 100);
        else if(acrescimoValor > 0) precoFinal += acrescimoValor;

        document.getElementById('preco_simulado').value = precoFinal.toFixed(2);
    }

</script>

<!-- <script>
    function toggleCampos(valor) {
        const campoProduto = document.getElementById('campo_produto');
        const campoCategoria = document.getElementById('campo_categoria');
        const msgDesconto = document.getElementById('msg_desconto');

        campoProduto.classList.add('d-none');
        campoCategoria.classList.add('d-none');

        // Resetar campos
        document.getElementById('preco_venda').value = '';
        document.getElementById('preco_simulado').value = '';
        document.getElementById('desconto_percentual').value = '';
        document.getElementById('acrescimo_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';

        // Desabilitar tudo por padrão
        document.getElementById('preco_venda').disabled = true;
        document.getElementById('preco_simulado').disabled = true;
        document.getElementById('desconto_percentual').disabled = true;
        document.getElementById('acrescimo_percentual').disabled = true;
        document.getElementById('acrescimo_valor').disabled = true;

        // Referência ao elemento de mensagem
        document.getElementById('desconto_percentual').addEventListener('input', function() {
        document.getElementById('acrescimo_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';
        
        // Validar desconto máximo
        if (parseFloat(this.value) > 10) {
            msgDesconto.textContent = 'O desconto não pode ser maior que 10%.';
            this.value = ''; // limpa o campo
        } else {
            msgDesconto.textContent = '';
        }
        
        simularPreco();
        });

        if(valor === 'produto') {
            campoProduto.classList.remove('d-none');
            document.getElementById('preco_venda').disabled = false; // habilitar para produto
        } else if(valor === 'categoria') {
            campoCategoria.classList.remove('d-none');
            // Habilitar somente os campos de desconto/acréscimo
            document.getElementById('desconto_percentual').disabled = false;
            document.getElementById('acrescimo_percentual').disabled = false;
            document.getElementById('acrescimo_valor').disabled = false;
        }

        atualizarPreco();
    }
    function atualizarPreco() {

        const tipo = document.getElementById('tipo_abrangencia').value;

        if(tipo !== 'produto')
            return;

        const opt = document.getElementById('produto_id').selectedOptions[0];
        if(!opt) return;

        // ■■■ PREENCHER TODOS OS CAMPOS DE VISUALIZAÇÃO ■■■
        document.getElementById('visu_nome').value        = opt.dataset.nome || '';
        document.getElementById('visu_marca').value       = opt.dataset.marca || '';
        document.getElementById('visu_unidade').value     = opt.dataset.unidade || '';
        document.getElementById('visu_estoque').value     = opt.dataset.estoque || '';
        document.getElementById('visu_fornecedor').value  = opt.dataset.fornecedor || '';
        document.getElementById('visu_descricao').value  = opt.dataset.descricao || '';
        
        // preço
        const preco = parseFloat(opt.dataset.preco || 0);

        document.getElementById('preco_venda').value = preco.toFixed(2);
        document.getElementById('preco_simulado').value = '';

        if(preco > 0){
            document.getElementById('desconto_percentual').disabled = false;
            document.getElementById('acrescimo_percentual').disabled = false;
            document.getElementById('acrescimo_valor').disabled = false;
        }
    }


    function simularPreco() {
        const tipo = document.getElementById('tipo_abrangencia').value;
        const precoVenda = parseFloat(document.getElementById('preco_venda').value || 0);

        let precoFinal = precoVenda;
        const desconto = parseFloat(document.getElementById('desconto_percentual').value) || 0;
        const acrescimoPercentual = parseFloat(document.getElementById('acrescimo_percentual').value) || 0;
        const acrescimoValor = parseFloat(document.getElementById('acrescimo_valor').value) || 0;

        if(tipo === 'produto') {
            if(desconto > 0) precoFinal -= precoVenda * (desconto / 100);
            else if(acrescimoPercentual > 0) precoFinal += precoVenda * (acrescimoPercentual / 100);
            else if(acrescimoValor > 0) precoFinal += acrescimoValor;
        } else if(tipo === 'categoria') {
            precoFinal = 0; // preço simulado não usado
        }

        document.getElementById('preco_simulado').value = precoFinal.toFixed(2);
    }

    // Limpar campos conflitantes
    document.getElementById('desconto_percentual').addEventListener('input', function() {
        document.getElementById('acrescimo_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';
        simularPreco();
    });
    document.getElementById('acrescimo_percentual').addEventListener('input', function() {
        document.getElementById('desconto_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';
        simularPreco();
    });
    document.getElementById('acrescimo_valor').addEventListener('input', function() {
        document.getElementById('desconto_percentual').value = '';
        document.getElementById('acrescimo_percentual').value = '';
        simularPreco();
    });

    // Validação antes de enviar
    document.getElementById('formPromocao').addEventListener('submit', function(e){
        const tipo = document.getElementById('tipo_abrangencia').value;
        const produto = document.getElementById('produto_id').value;
        const categoria = document.getElementById('categoria_id').value;
        const precoVenda = parseFloat(document.getElementById('preco_venda').value) || 0;
        const desconto = parseFloat(document.getElementById('desconto_percentual').value) || 0;
        const acrescimoPerc = parseFloat(document.getElementById('acrescimo_percentual').value) || 0;
        const acrescimoValor = parseFloat(document.getElementById('acrescimo_valor').value) || 0;
        

        if(tipo === '') {
            alert('Selecione o tipo de abrangência.');
            e.preventDefault(); return;
        }
        if(tipo === 'produto' && produto === '') {
            alert('Selecione um produto.');
            e.preventDefault(); return;
        }
        if(tipo === 'categoria' && categoria === '') {
            alert('Selecione uma categoria.');
            e.preventDefault(); return;
        }
        if(tipo === 'produto' && precoVenda < 1) {
            alert('O preço de venda deve ser maior que zero.');
            e.preventDefault(); return;
        }
        if(desconto < 1 && acrescimoPerc < 1 && acrescimoValor < 1 && tipo === 'produto') {
            alert('Informe pelo menos um valor válido de desconto ou acréscimo (maior que 0).');
            e.preventDefault(); return;
        }
        if(desconto > 50) {
            alert('O desconto não pode ser maior que 50%.');
            e.preventDefault(); return;
        }
    });
</script> -->


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/promocoes/create.blade.php ENDPATH**/ ?>