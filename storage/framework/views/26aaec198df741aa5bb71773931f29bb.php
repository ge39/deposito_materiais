

<?php $__env->startSection('content'); ?>
<div class="container">

<h2 class="mb-4">Editar Orçamento</h2>

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
                            <option value="<?php echo e($cliente->id); ?>" data-perfil="<?php echo e($cliente->tipo_cliente ?? 'markup_1'); ?>">
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

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Lote</th>
                        <th>Quantidade</th>
                        <th>Unidade</th>
                        <th>Preço</th>
                        <th style="width: 100px;">Desc. (%)</th> 
                        <th>Subtotal</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody id="itensContainer"></tbody>
            </table>

            <div class="text-end">
                <button type="button" class="btn btn-primary" id="addProduto">+ Adicionar Produto</button>
                <button type="submit" class="btn btn-success" id="btnSalvar" atualizarBotaoSalvar()>Salvar Orçamento</button>
                <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary">Voltar</a>
            </div>

                <div class="text-end mt-2 d-flex justify-content-end align-items-center gap-4">
                    
                    <h5 class="text-danger mb-0">Total Desconto: R$ <span id="totalDesconto">0,00</span></h5>
                    
                    
                    <h5 class="text-muted mb-0">Total Bruto: R$ <span id="totalBruto">0,00</span></h5>

                    
                    <h4 class="text-success mb-0 fw-bold">Valor com Desconto: R$ <span id="totalLiquido">0,00</span></h4>
                </div>


            <div class="mb-3 mt-3 bg-secondary  p-3 rounded">
                <label class="form-label text-warning">Observações:</label>
                <label class="form-label text-light"> insira aqui as informações que vão aparecer impressos no documento de entrega.</label>
                <label class="form-label text-warning"> Ex: melhor periodo para entrega: manha ou tarde, nome da pessoa que vai receber ?</label>
                <textarea name="observacoes" class="form-control" rows="3">Sem observações</textarea>
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
        const totalSpan = document.getElementById('total');

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
        // function criarItem() {

        //     const tr = document.createElement('tr');

        //     tr.innerHTML = `
        //         <td>
        //             <select name="produtos[${index}][id]" class="form-select produtoSelect" required>
        //                 <option value="">Selecione...</option>
        //                 ${produtos.map(p => `
        //                     <option value="${p.id}"
        //                         data-preco="${p.preco_venda}"
        //                         data-desc-max="${p.desconto_max_1 || 0}" 
        //                         data-unidade="${p.unidade_medida?.nome || ''}">
        //                         ${p.id} - ${p.nome}
        //                     </option>
        //                 `).join('')}
        //             </select>
        //         </td>

        //         <td>
        //             <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
        //                 <option value="">Selecione o lote</option>
        //             </select>
        //         </td>

        //         <td>
        //             <input type="number"
        //                 name="produtos[${index}][quantidade]"
        //                 class="form-control qtd"
        //                 value="1" min="1" required>
        //         </td>

        //         <td>
        //             <span class="unidadeLabel"></span>
        //             <input type="hidden" name="produtos[${index}][unidade]" class="unidade">
        //         </td>

        //         <td>
        //             <span class="precoLabel">0,00</span>
        //             <input type="hidden" name="produtos[${index}][preco_unitario]" class="preco">
                    
        //             
        //             <input type="hidden" class="maxDesc" value="0">
        //         </td>


        //         <td>
        //             <span class="subtotalLabel">0,00</span>
        //         </td>

        //         <td>
        //             <button type="button" class="btn btn-sm btn-danger remover">X</button>
        //         </td>
        //     `;

        //     tableBody.appendChild(tr);
        //     index++;

        //     // 🔥 atualiza opções ao criar nova linha
        //     atualizarOpcoesProdutos();
        // }

                // ================================
        // CRIAR ITEM (COM CAMPOS DE DESCONTO)
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
                    
                    <input type="hidden" class="maxDesc" value="0">
                </td>

                
                <td>
                    <input type="number"
                        name="produtos[${index}][desconto]"
                        class="form-control desc"
                        value="0" min="0" max="100" step="1">
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

            // 🔥 atualiza opções ao criar nova linha original
            atualizarOpcoesProdutos();

              // 🚀 OUVIINTE DO DESCONTO: Faz o cálculo rodar em tempo real ao digitar
            const inputDesc = tr.querySelector('.desc');
            if (inputDesc) {
                inputDesc.addEventListener('input', atualizarTotal);
            }
        }

        // ================================
        // ATUALIZAR TOTAL
        // ================================
        // function atualizarTotal() {
        //     let total = 0;

        //     tableBody.querySelectorAll('tr').forEach(tr => {

        //         const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
        //         const preco = parseFloat(tr.querySelector('.preco').value) || 0;

        //         const subtotal = qtd * preco;

        //         tr.querySelector('.subtotalLabel').textContent =
        //             subtotal.toFixed(2).replace('.', ',');

        //         total += subtotal;
        //     });

        //     totalSpan.textContent = total.toFixed(2).replace('.', ',');
        // }
        // =======================================================================
        // ATUALIZAR TOTAL (Calcula Bruto, Desconto Acumulado e Valor Líquido)
        // =======================================================================
        function atualizarTotal() {
            let acumuladoBruto = 0;
            let acumuladoDesconto = 0;
            let acumuladoLiquido = 0;

            tableBody.querySelectorAll('tr').forEach(tr => {
                const inputQtd = tr.querySelector('.qtd');
                const inputPreco = tr.querySelector('.preco');
                const inputDesc = tr.querySelector('.desc');

                // Garante que quantidade e desconto sejam tratados como inteiros conforme a nova regra
                const qtd = inputQtd ? (parseInt(inputQtd.value) || 0) : 0;
                const precoUnitario = inputPreco ? (parseFloat(inputPreco.value) || 0) : 0;
                const descPercent = inputDesc ? (parseInt(inputDesc.value) || 0) : 0;

                // 1. Matemática do Item: Incide a porcentagem sobre o valor unitário
                const brutoItem = qtd * precoUnitario;
                const valorDescontoUnitario = precoUnitario * (descPercent / 100);
                const valorDescontoTotalItem = qtd * valorDescontoUnitario;
                
                let liquidoItem = brutoItem - valorDescontoTotalItem;
                if (liquidoItem < 0) liquidoItem = 0;

                // 2. Atualiza o Subtotal visível da linha na tabela
                const subtotalLabel = tr.querySelector('.subtotalLabel');
                if (subtotalLabel) {
                    subtotalLabel.textContent = liquidoItem.toFixed(2).replace('.', ',');
                }

                // 3. Acumula os valores para o resumo do rodapé
                acumuladoBruto += brutoItem;
                acumuladoDesconto += valorDescontoTotalItem;
                acumuladoLiquido += liquidoItem;
            });

            // 🚀 INJEÇÃO DOS VALORES NOS TRÊS CAMPOS REQUISITADOS DO RODAPÉ
            const spanDesconto = document.getElementById('totalDesconto');
            const spanBruto = document.getElementById('totalBruto');
            const spanLiquido = document.getElementById('totalLiquido');
            const spanTotalAntigo = document.getElementById('total'); // Mantém para não quebrar a tag antiga se houver

            if (spanDesconto) spanDesconto.textContent = acumuladoDesconto.toFixed(2).replace('.', ',');
            if (spanBruto) spanBruto.textContent = acumuladoBruto.toFixed(2).replace('.', ',');
            if (spanLiquido) spanLiquido.textContent = acumuladoLiquido.toFixed(2).replace('.', ',');
            if (spanTotalAntigo) spanTotalAntigo.textContent = acumuladoLiquido.toFixed(2).replace('.', ',');
        }

        // ================================
        // PRODUTO ALTERADO + LOTES
        // ================================
        // tableBody.addEventListener('change', e => {

        //     if (!e.target.classList.contains('produtoSelect')) return;

        //     if (!clienteSelect.value) {
        //         alert('Selecione o cliente primeiro!');
        //         e.target.value = '';
        //         return;
        //     }

        //     const produtoId = e.target.value;
        //     const produto = produtos.find(p => p.id == produtoId);
        //     const tr = e.target.closest('tr');

        //     const preco = parseFloat(produto?.preco_venda || 0);
        //     const unidade = produto?.unidade_medida?.nome || '';

        //     tr.querySelector('.preco').value = preco;
        //     tr.querySelector('.precoLabel').textContent = preco.toFixed(2).replace('.', ',');

        //     tr.querySelector('.unidade').value = unidade;
        //     tr.querySelector('.unidadeLabel').textContent = unidade;

        //     // ============================
        //     // LOTES
        //     // ============================
        //     const loteSelect = tr.querySelector('.loteSelect');
        //     loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

        //     if (!produto || !produto.lotes) return;

        //     const lotesValidos = produto.lotes.filter(l => {

        //         const disponivel =
        //             (parseFloat(l.quantidade) || 0) -
        //             (parseFloat(l.quantidade_reservada) || 0);

        //         return l.status == 1 && disponivel > 0;
        //     });

        //     if (lotesValidos.length === 0) {
        //         loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
        //         return;
        //     }

        //     lotesValidos.forEach(l => {

        //         const disponivel =
        //             (parseFloat(l.quantidade) || 0) -
        //             (parseFloat(l.quantidade_reservada) || 0);

        //         loteSelect.innerHTML += `
        //             <option value="${l.id}">
        //                 ${l.numero_lote} | Qtd: ${disponivel}
        //             </option>
        //         `;
        //     });

        //     // 🔥 ATUALIZA BLOQUEIO DE PRODUTOS
        //     atualizarOpcoesProdutos();

        //     atualizarTotal();
        // });

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

            // =======================================================================
            // 🚀 INTERCEPTAÇÃO CIRÚRGICA: DEFINE PREÇO E DESCONTO POR MARKUP DO CLIENTE
            // =======================================================================
            // Captura o perfil cadastrado no option do cliente selecionado no topo
            const clienteOpt = clienteSelect.options[clienteSelect.selectedIndex];
            const perfilCliente = clienteOpt ? (clienteOpt.dataset.perfil || 'markup_1') : 'markup_1';

            let preco = 0;
            let maxDesc = 0;

            // Alinha dinamicamente os valores de acordo com as colunas reais do banco
            if (perfilCliente === 'markup_2') {
                preco = parseFloat(produto?.preco_venda_2 || produto?.preco_venda || 0);
                maxDesc = parseFloat(produto?.desconto_max_2 || 0);
            } else if (perfilCliente === 'markup_3') {
                preco = parseFloat(produto?.preco_venda_3 || produto?.preco_venda || 0);
                maxDesc = parseFloat(produto?.desconto_max_3 || 0);
            } else {
                preco = parseFloat(produto?.preco_venda || 0);
                maxDesc = parseFloat(produto?.desconto_max_1 || 0);
            }

            const unidade = produto?.unidade_medida?.nome || '';

            // Grava e exibe o preço correto do perfil na linha
            tr.querySelector('.preco').value = preco;
            tr.querySelector('.precoLabel').textContent = preco.toFixed(2).replace('.', ',');

            // 🚀 GRAVA E EXIBE O LIMITE MÁXIMO DE DESCONTO NO INPUT OCULTO E NO PLACEHOLDER
            const inputMaxDesc = tr.querySelector('.maxDesc');
            const inputDesc = tr.querySelector('.desc');
            
            if (inputMaxDesc) inputMaxDesc.value = maxDesc;
            if (inputDesc) {
                inputDesc.placeholder = `Até ${maxDesc}%`;
                inputDesc.max = maxDesc;
            }

            tr.querySelector('.unidade').value = unidade;
            tr.querySelector('.unidadeLabel').textContent = unidade;

            // ============================
            // LOTES (Sua lógica original intocada)
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
            atualizarOpcoesProdutos();

            atualizarTotal();
        });


        // ================================
        // QUANTIDADE
        // ================================
        // tableBody.addEventListener('input', e => {
        //     if (e.target.classList.contains('qtd')) {
        //         atualizarTotal();
        //     }
        // });
                // =======================================================================
        // 🎯 TRAVA RIGIDA DE DIGITAÇÃO: IMPEDE DESCONTO ACIMA DO LIMITE DO BANCO
        // =======================================================================
               // =======================================================================
        // 🎯 TRAVA DE DIGITAÇÃO: MENSAGEM CLARA QUANDO O PRODUTO NÃO TEM DESCONTO
        // =======================================================================
        tableBody.addEventListener('input', e => {
            
            if (!e.target.classList.contains('desc')) {
                atualizarTotal();
                return;
            }

            const tr = e.target.closest('tr');
            if (!tr) return;

            const inputMaxDesc = tr.querySelector('.maxDesc');
            const maxPermitido = inputMaxDesc ? (parseInt(inputMaxDesc.value) || 0) : 0;
            
            let valorDigitado = parseInt(e.target.value) || 0;

            // 🚫 VERIFICAÇÃO EM TEMPO REAL:
            if (valorDigitado > maxPermitido) {
                
                // 🚀 MENSAGEM CUSTOMIZADA: Trata o caso do produto com 0% no banco de dados
                if (maxPermitido === 0) {
                    alert(`📢 Item sem Margem: Este produto não possui margem de desconto autorizada no cadastro.`);
                } else {
                    alert(`🚨 Limite Excedido: O desconto máximo permitido para este produto neste perfil é de ${maxPermitido}%!`);
                }
                
                // Força o campo a retornar para o limite legal (que é 0 ou o teto do banco)
                e.target.value = maxPermitido; 
            }

            atualizarTotal();
        });


         // 🚫 VERIFICAÇÃO EM TEMPO REAL: Se passar do limite, bloqueia na hora!
            // if (valorDigitado > maxPermitido) {
            //     alert(`🚨 Margem Violada: O desconto máximo permitido para este produto neste perfil de cliente é de ${maxPermitido}%!`);
                
            //     // Força o valor do campo a voltar exatamente para o teto máximo legal
            //     e.target.value = maxPermitido;
            // }


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
                const qtd = lastRow.querySelector('.qtd')?.value;
                const preco = lastRow.querySelector('.preco')?.value;

                if (!produto || !qtd || !preco) {
                    alert('Complete o item antes de adicionar outro');
                    return;
                }

                if (!lote) {
                    alert('Selecione o lote antes de adicionar outro');
                    lastRow.querySelector('.loteSelect')?.focus();
                    return;
                }

                if (qtd <= 0) {
                    alert('Informe uma quantidade válida');
                    lastRow.querySelector('.qtd')?.focus();
                    return;
                }
            }

            criarItem();
        });

    });
</script>

<script src="<?php echo e(asset('js/orcamento.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/edit.blade.php ENDPATH**/ ?>