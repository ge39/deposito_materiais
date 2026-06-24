

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

            <!-- Tabela sem a coluna de desconto na linha -->
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Lote</th>
                        <th style="width: 120px;">Quantidade</th>
                        <th>Unidade</th>
                        <th>Preço</th>
                        <th>Subtotal</th>
                        <th style="width: 80px;" class="text-center">Ação</th>
                    </tr>
                </thead>
                <tbody id="itensContainer"></tbody>
            </table>

            <!-- Botão de adicionar produto logo abaixo da tabela -->
            <div class="mb-4 col-12 d-flex justify-content-end pe-3">
                <button type="button" class="btn btn-primary" id="addProduto">+ Adicionar Produto</button>
            </div>

            <!-- 📑 NOVA SEÇÃO DE DESCONTO GLOBAL E RESUMO FINANCEIRO -->
            <div class="row justify-content-end mb-3">
                <div class="col-md-5">
                    <div class="p-3 border rounded bg-light">

                        <!-- 📑 Total Bruto e Desconto Alinhados em Linha -->
                        <div class="row align-items-center mb-3">
                            <!-- Bloco do Total Bruto -->
                            <div class="col-6 d-flex justify-content-between align-items-center border-end pe-3">
                                <span class="fw-bold text-secondary">Total Bruto:</span>
                                <span class="fs-5 fw-bold text-dark">R$ <span id="totalBruto">0,00</span></span>
                            </div>

                            <!-- Bloco do Desconto Global -->
                            <div class="col-6 d-flex justify-content-between align-items-center ps-3">
                                <span class="fw-bold text-danger mb-0">Desconto (%):</span>
                                <div style="width: 100px;">
                                    <input type="number" name="desconto_global" id="descontoGlobal" 
                                        class="form-control text-end fw-bold text-danger py-1" 
                                        min="0" max="100" value="0" step="1">

                                        <!-- Inputs ocultos para enviar os totais calculados pelo JavaScript -->
                                        <input type="hidden" name="total_bruto_calculado" id="totalBrutoInput" value="5235.00">
                                        <input type="hidden" name="total_desconto_calculado" id="totalDescontoInput" value="261.75">
                                        <input type="hidden" name="total_liquido_calculado" id="totalLiquidoInput" value="4973.25">
                                </div>
                            </div>
                        </div>

                        <!-- Valor total do desconto em R$ -->
                        <div class="d-flex justify-content-between align-items-center mb-2 text-muted small">
                            <span>Total Desconto:</span>
                            <span>R$ <span id="totalDesconto">0,00</span></span>
                        </div>

                        <hr class="my-2">

                        <!-- Valor Líquido Final com Desconto -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success fs-5">Valor com Desconto:</span>
                            <span class="fw-bold text-success fs-4">R$ <span id="totalComDesconto">0,00</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🛠️ BOTÕES DE AÇÃO DO RODAPÉ (Abaixo do Desconto, acima de Voltar) -->
            <div class="text-end mb-4">
                <button type="submit" class="btn btn-success px-4" id="btnSalvar">Salvar Orçamento</button>
                <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary px-4">Voltar</a>
            </div>

              <!-- Bloco de Observações -->
            <div class="mb-3 bg-secondary p-3 rounded">
                <label class="form-label text-warning d-block fw-bold">Observações:</label>
                <!-- <label class="form-label text-light">insira aqui as informações que vão aparecer impressos no documento de entrega.</label> -->
                <!-- <label class="form-label text-warning d-block small">Ex: melhor periodo para entrega: manha ou tarde, nome da pessoa que vai receber ?</label> -->
                <textarea name="observacoes" class="form-control" rows="1">Sem observações</textarea>
            </div>
        </div>
    </div>
</form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const produtos = <?php echo json_encode($produtos, 15, 512) ?>;
        const tableBody = document.getElementById('itensContainer');
        const addBtn = document.getElementById('addProduto');
        const clienteSelect = document.getElementById('clienteSelect');
        
        // 📊 Elementos da nova seção de desconto global mapeados da Blade
        const totalBrutoSpan = document.getElementById('totalBruto');
        const descontoGlobalInput = document.getElementById('descontoGlobal');
        const totalDescontoSpan = document.getElementById('totalDesconto');
        const totalComDescontoSpan = document.getElementById('totalComDesconto');

        let index = 0;

        // ================================
        // 🔥 PRODUTOS SELECIONADOS
        // ================================
        function getProdutosSelecionados() {
            const selecionados = [];

            tableBody.querySelectorAll('.produtoSelect').forEach(select => {
                if (select.value) {
                    selecionados.push(select.value);
                }
            });

            return selecionados;
        }

        // ================================
        // 🔥 ATUALIZA OPTIONS (OCULTA USADOS)
        // ================================
        function atualizarOpcoesProdutos() {
            const selecionados = getProdutosSelecionados();

            tableBody.querySelectorAll('.produtoSelect').forEach(select => {

                const valorAtual = select.value;

                select.querySelectorAll('option').forEach(option => {

                    if (!option.value) return;

                    // mantém o selecionado atual visível
                    if (option.value === valorAtual) {
                        option.hidden = false;
                        return;
                    }

                    // oculta se já foi usado em outro select
                    option.hidden = selecionados.includes(option.value);
                });
            });
        }

        // ================================
        // CRIAR ITEM
        // ================================
        function criarItem() {

            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>
                    <select name="produtos[${index}][id]" class="form-select produtoSelect" required>
                        <option value="">Selecione...</option>
                        ${produtos.map(p => `
                            <option value="${p.id}"
                                data-preco="${p.preco_venda}"
                                data-unidade="${p.unidade_medida?.nome || ''}">
                                ${p.id} - ${p.nome}
                            </option>
                        `).join('')}
                    </select>
                </td>

                <td>
                    <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
                        <option value="">Selecione o lote</option>
                    </select>
                </td>

                <td>
                    <input type="number"
                        name="produtos[${index}][quantidade]"
                        class="form-control qtd"
                        value="1" min="1" required>
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
                </td>

                <td>
                    <button type="button" class="btn btn-sm btn-danger remover">X</button>
                </td>
            `;

            tableBody.appendChild(tr);
            index++;

            // 🔥 atualiza opções ao criar nova linha
            atualizarOpcoesProdutos();
        }

               // ================================
        // 📊 ATUALIZAR TOTAL (ALINHADO COM A ESTRUTURA DO BANCO)
        // ================================
        function atualizarTotal() {
            let totalBruto = 0;
            let menorDescontoMaximoPermitido = 100; // Começa com o teto de 100%
            let produtoLimitanteNome = "";

            // 1. Lê a porcentagem do desconto global digitada no rodapé
            let percentualDesconto = parseFloat(descontoGlobalInput?.value) || 0;

            // 2. Varre os itens da tabela para somar subtotais e calcular as travas de desconto
            tableBody.querySelectorAll('tr').forEach(tr => {
                const produtoSelect = tr.querySelector('.produtoSelect');
                const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
                const precoCobrado = parseFloat(tr.querySelector('.preco').value) || 0;

                const subtotal = qtd * precoCobrado;
                tr.querySelector('.subtotalLabel').textContent = subtotal.toFixed(2).replace('.', ',');
                totalBruto += subtotal;

                // Validação dinâmica do desconto baseado no preço cobrado
                if (produtoSelect && produtoSelect.value) {
                    const produtoId = produtoSelect.value;
                    const produtoDados = produtos.find(p => p.id == produtoId);
                    
                    if (produtoDados) {
                        // Resgata os preços e descontos reais mapeados do banco de dados
                        const pv1 = parseFloat(produtoDados.preco_venda) || 0;
                        const pv2 = parseFloat(produtoDados.preco_venda_2) || 0;
                        const pv3 = parseFloat(produtoDados.preco_venda_3) || 0;

                        const descMax1 = parseFloat(produtoDados.desconto_max_1) || 0;
                        const descMax2 = parseFloat(produtoDados.desconto_max_2) || 0;
                        const descMax3 = parseFloat(produtoDados.desconto_max_3) || 0;

                        // Descobre qual tabela de preço bate com o valor unitário cobrado na linha
                        let descMaxProduto = descMax1; // Fallback padrão para tabela 1

                        if (Math.abs(precoCobrado - pv2) < 0.01) {
                            descMaxProduto = descMax2;
                        } else if (Math.abs(precoCobrado - pv3) < 0.01) {
                            descMaxProduto = descMax3;
                        } else if (Math.abs(precoCobrado - pv1) >= 0.01) {
                            // Se o preço foi editado manualmente e não bate com nenhum, assume o menor dos limites por segurança
                            descMaxProduto = Math.min(descMax1, descMax2, descMax3);
                        }

                        // O produto com o menor limite dita a regra do desconto global do orçamento
                        if (descMaxProduto < menorDescontoMaximoPermitido) {
                            menorDescontoMaximoPermitido = descMaxProduto;
                            produtoLimitanteNome = produtoDados.nome || "";
                        }
                    }
                }
            });

            // 3. 🔥 TRAVA DE SEGURANÇA: Bloqueia caso ultrapasse o limite do markup do produto
            if (percentualDesconto > menorDescontoMaximoPermitido) {
                alert(`Atenção! O desconto de ${percentualDesconto}% excede o limite máximo permitido de ${menorDescontoMaximoPermitido}% definido pelo Markup para o produto: ${produtoLimitanteNome}. Valor reajustado.`);
                percentualDesconto = menorDescontoMaximoPermitido;
                if(descontoGlobalInput) {
                    descontoGlobalInput.value = menorDescontoMaximoPermitido;
                }
            }

            // 4. Atualiza o Total Bruto na tela
            if(totalBrutoSpan) {
                totalBrutoSpan.textContent = totalBruto.toFixed(2).replace('.', ',');
            }

            // 5. Calcula o valor em R$ do desconto abatido e o Valor Líquido Final
            const valorDesconto = totalBruto * (percentualDesconto / 100);
            const totalComDesconto = totalBruto - valorDesconto;

            // 6. Atualiza os novos elementos na tela
            if(totalDescontoSpan) {
                totalDescontoSpan.textContent = valorDesconto.toFixed(2).replace('.', ',');
            }
            if(totalComDescontoSpan) {
                totalComDescontoSpan.textContent = totalComDesconto.toFixed(2).replace('.', ',');
            }
        }


        // ================================
        // PRODUTO ALTERADO + LOTES
        // ================================
        tableBody.addEventListener('change', e => {

            if (!e.target.classList.contains('produtoSelect')) return;

            if (!clienteSelect.value) {
                alert('Selecione o cliente primeiro!');
                e.target.value = '';
                return;
            }

            const produtoId = e.target.value;
            const produto = produtos.find(p => p.id == produtoId);
            const tr = e.target.closest('tr');

            const preco = parseFloat(produto?.preco_venda || 0);
            const unidade = produto?.unidade_medida?.nome || '';

            tr.querySelector('.preco').value = preco;
            tr.querySelector('.precoLabel').textContent = preco.toFixed(2).replace('.', ',');

            tr.querySelector('.unidade').value = unidade;
            tr.querySelector('.unidadeLabel').textContent = unidade;

            // ============================
            // LOTES
            // ============================
            const loteSelect = tr.querySelector('.loteSelect');
            loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

            if (!produto || !produto.lotes) return;

            const lotesValidos = produto.lotes.filter(l => {

                const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                return l.status == 1 && disponivel > 0;
            });

            if (lotesValidos.length === 0) {
                loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
                return;
            }

            lotesValidos.forEach(l => {

                const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                loteSelect.innerHTML += `
                    <option value="${l.id}">
                        ${l.numero_lote} | Qtd: ${disponivel}
                    </option>
                `;
            });

            // 🔥 ATUALIZA BLOQUEIO DE PRODUTOS
                        // 🔥 ATUALIZA BLOQUEIO DE PRODUTOS
            atualizarOpcoesProdutos();

            atualizarTotal();
        });

        // ================================
        // QUANTIDADE E INPUT DE DESCONTO GLOBAL
        // ================================
        tableBody.addEventListener('input', e => {
            if (e.target.classList.contains('qtd')) {
                atualizarTotal();
            }
        });

        // 🔥 Escuta mudanças no campo de desconto global para recalcular os valores na hora
        if (descontoGlobalInput) {
            descontoGlobalInput.addEventListener('input', atualizarTotal);
        }

        // ================================
        // REMOVER
        // ================================
        tableBody.addEventListener('click', e => {
            if (e.target.classList.contains('remover')) {

                e.target.closest('tr').remove();

                // 🔥 libera produto novamente
                atualizarOpcoesProdutos();

                atualizarTotal();
            }
        });

        // ================================
        // ADICIONAR PRODUTO
        // ================================
        addBtn.addEventListener('click', () => {

            if (!clienteSelect.value) {
                alert('Selecione um cliente primeiro!');
                return;
            }

            const lastRow = tableBody.querySelector('tr:last-child');

            if (lastRow) {
                const produto = lastRow.querySelector('.produtoSelect')?.value;
                const lote = lastRow.querySelector('.loteSelect')?.value;

                if (!produto || !lote) {
                    alert('Preencha o produto e o lote da linha anterior antes de adicionar um novo!');
                    return;
                }
            }

            criarItem();
        });
    });
</script>

<script src="<?php echo e(asset('js/orcamento.js')); ?>"></script>

<!-- <?php $__env->stopSection(); ?> -->
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/create.blade.php ENDPATH**/ ?>