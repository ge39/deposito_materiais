

<?php $__env->startSection('content'); ?>
<div class="container">

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                Novo Orçamento
            </h2>
            <p class="text-muted mb-0">
                Criação de orçamento com validade, atendimento, entrega, produtos, lotes e desconto global.
            </p>
        </div>

        <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary px-4">
            <i class="bi bi-arrow-left-circle me-1"></i>
            Voltar
        </a>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-check-circle me-1"></i>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger shadow-sm">
            <strong>
                <i class="bi bi-exclamation-triangle me-1"></i>
                Erro!
            </strong>
            Verifique os campos obrigatórios.
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-primary fs-3">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Cliente</div>
                        <small class="text-muted">Obrigatório para iniciar</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-warning fs-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Validade</div>
                        <small class="text-muted">Orçamento válido por 7 dias</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-success fs-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Produtos</div>
                        <small class="text-muted">Com controle por lote</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-info fs-3">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Atendimento</div>
                        <small class="text-muted">Retira loja ou entrega</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('orcamentos.store')); ?>" method="POST" id="formOrcamento">
        <?php echo csrf_field(); ?>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-pencil-square me-1"></i>
                    Dados do Orçamento
                </div>
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-calendar-check me-1"></i>
                    Validade padrão: 7 dias
                </span>
            </div>

            <div class="card-body">

                
                <div class="border rounded p-3 mb-3 bg-light">
                    <h5 class="mb-3 text-primary">
                        <i class="bi bi-person-lines-fill me-1"></i>
                        Cliente e Datas
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Cliente *</label>
                            <select name="cliente_id" id="clienteSelect" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($cliente->id); ?>"
                                        data-endereco="<?php echo e($cliente->endereco ?? ''); ?>"
                                        data-numero="<?php echo e($cliente->numero ?? ''); ?>"
                                        data-complemento="<?php echo e($cliente->complemento ?? ''); ?>"
                                        data-bairro="<?php echo e($cliente->bairro ?? ''); ?>"
                                        data-cidade="<?php echo e($cliente->cidade ?? ''); ?>"
                                        data-cep="<?php echo e($cliente->cep ?? ''); ?>"
                                        data-telefone="<?php echo e($cliente->telefone ?? ''); ?>"
                                    >
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

                        <div class="col-md-2 d-flex align-items-end">
                            <span class="badge bg-warning text-dark p-2 w-100">
                                <i class="bi bi-clock me-1"></i>
                                7 dias
                            </span>
                        </div>
                    </div>
                </div>

              
            <div class="border rounded p-3 mb-3 bg-light shadow-sm">
                <h5 class="mb-3 text-primary fw-bold">
                    <i class="bi bi-truck me-1"></i>
                    Atendimento / Entrega
                </h5>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            Forma de Entrega <span class="text-danger">*</span>
                        </label>
                        <select name="tipo_entrega" id="tipo_entrega" class="form-select" required>
                            <option value="" selected disabled>Selecione...</option>
                            <option value="retira_loja">Retira Loja</option>
                            <option value="entrega">Entrega</option>
                        </select>
                    </div>

                    <div class="col-md-3 campo-entrega d-none">
                        <label class="form-label fw-bold">Usar endereço cadastrado?</label>
                        <select name="usar_endereco_cliente" id="usar_endereco_cadastrado" class="form-select">
                            <option value="sim" selected>Sim</option>
                            <option value="nao">Não, informar outro endereço</option>
                        </select>
                    </div>

                    <div class="col-md-3 campo-entrega d-none">
                        <label class="form-label fw-bold">Data Prevista</label>
                        <input type="date" name="data_prevista_entrega" id="data_prevista_entrega" class="form-control" required>
                    </div>

                    <div class="col-md-3 campo-entrega d-none">
                        <label class="form-label fw-bold">Período</label>
                        <select name="periodo_entrega" id="periodo_entrega" class="form-select " required>
                            <option value="">Selecione...</option>
                            <option value="manha">Manhã</option>
                            <option value="tarde">Tarde</option>
                            <option value="comercial">Horário Comercial</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3 campo-entrega d-none">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Local da Entrega</label>
                        <input type="text" name="endereco_entrega" id="endereco_entrega" class="form-control" placeholder="Rua, avenida ou estrada">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Número</label>
                        <input type="text" name="numero_entrega" id="numero_entrega" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Complemento</label>
                        <input type="text" name="complemento_entrega" id="complemento_entrega" class="form-control" placeholder="Casa, bloco, referência">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">CEP</label>
                        <input type="text" name="cep_entrega" id="cep_entrega" class="form-control" placeholder="00000-000">
                    </div>
                </div>

                <div class="row mb-3 campo-entrega d-none">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Bairro</label>
                        <input type="text" name="bairro_entrega" id="bairro_entrega" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cidade</label>
                        <input type="text" name="cidade_entrega" id="cidade_entrega" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Responsável pelo Recebimento</label>
                        <input type="text" name="contato_entrega" id="contato_entrega" class="form-control">
                    </div>
                </div>

                <div class="row mb-0 campo-entrega d-none">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Telefone do Responsável</label>
                        <input type="text" name="telefone_entrega" id="telefone_entrega" class="form-control" placeholder="(00) 00000-0000">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-bold">Observação da Entrega</label>
                        <textarea name="observacao_entrega" id="observacao_entrega" class="form-control" rows="2" placeholder="Referência, restrição de acesso, horário combinado ou observações internas"></textarea>
                    </div>
                </div>
        </div>

                            
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 text-primary">
                                        <i class="bi bi-box-seam me-1"></i>
                                        Itens do Orçamento
                                    </h5>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle bg-white mb-0">
                                        <thead class="table-dark text-center">
                                            <tr>
                                                <th>Produto</th>
                                                <th>Lote</th>
                                                <th style="width: 120px;">Quantidade</th>
                                                <th>Unidade</th>
                                                <th>Preço</th>
                                                <th>Subtotal</th>
                                                <th style="width: 80px;">Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itensContainer"></tbody>
                                    </table>
                                </div>
                                    
                            </div>
                            <div class="d-flex justify-content-end align-items-end mb-3">
                                    <button type="button" class="btn btn-primary" id="addProduto">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Adicionar Produto
                                    </button>
                                </div>
                            
                            <div class="row justify-content-end mb-3">
                                <div class="col-md-5">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-success text-white">
                                            <i class="bi bi-cash-coin me-1"></i>
                                            Resumo Financeiro
                                        </div>

                                        <div class="card-body bg-light">
                                            <div class="row align-items-center mb-3">
                                                <div class="col-6 d-flex justify-content-between align-items-center border-end pe-3">
                                                    <span class="fw-bold text-secondary">Total Bruto:</span>
                                                    <span class="fs-5 fw-bold text-dark">
                                                        R$ <span id="totalBruto">0,00</span>
                                                    </span>
                                                </div>

                                                <div class="col-6 d-flex justify-content-between align-items-center ps-3">
                                                    <span class="fw-bold text-danger mb-0">Desconto (%):</span>
                                                    <div style="width: 100px;">
                                                        <input type="number" name="desconto_global" id="descontoGlobal"
                                                            class="form-control text-end fw-bold text-danger py-1"
                                                            min="0" max="100" value="0" step="1">

                                                        <input type="hidden" name="total_bruto_calculado" id="totalBrutoInput" value="0.00">
                                                        <input type="hidden" name="total_desconto_calculado" id="totalDescontoInput" value="0.00">
                                                        <input type="hidden" name="total_liquido_calculado" id="totalLiquidoInput" value="0.00">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-2 text-muted small">
                                                <span>Total Desconto:</span>
                                                <span>R$ <span id="totalDesconto">0,00</span></span>
                                            </div>

                                            <hr class="my-2">

                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-success fs-5">Valor com Desconto:</span>
                                                <span class="fw-bold text-success fs-4">
                                                    R$ <span id="totalComDesconto">0,00</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="text-end mb-2">
                                <button type="submit" class="btn btn-success px-4" id="btnSalvar">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Salvar Orçamento
                                </button>

                                <a href="<?php echo e(route('orcamentos.index')); ?>" class="btn btn-secondary px-4">
                                    <i class="bi bi-arrow-left-circle me-1"></i>
                                    Voltar
                                </a>
                            </div>

                            <!-- <div class="mb-3 bg-secondary p-3 rounded">
                                <label class="form-label text-warning d-block fw-bold">Observações:</label>
                                <textarea name="observacoes" class="form-control" rows="1">Sem observações</textarea>
                            </div> -->
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

        const totalBrutoSpan = document.getElementById('totalBruto');
        const descontoGlobalInput = document.getElementById('descontoGlobal');
        const totalDescontoSpan = document.getElementById('totalDesconto');
        const totalComDescontoSpan = document.getElementById('totalComDesconto');

        const totalBrutoInput = document.getElementById('totalBrutoInput');
        const totalDescontoInput = document.getElementById('totalDescontoInput');
        const totalLiquidoInput = document.getElementById('totalLiquidoInput');

        let index = 0;

        function getProdutosSelecionados() {
            const selecionados = [];

            tableBody.querySelectorAll('.produtoSelect').forEach(select => {
                if (select.value) {
                    selecionados.push(select.value);
                }
            });

            return selecionados;
        }

        function atualizarOpcoesProdutos() {
            const selecionados = getProdutosSelecionados();

            tableBody.querySelectorAll('.produtoSelect').forEach(select => {

                const valorAtual = select.value;

                select.querySelectorAll('option').forEach(option => {

                    if (!option.value) return;

                    if (option.value === valorAtual) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = selecionados.includes(option.value);
                });
            });
        }

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

                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remover">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(tr);
            index++;

            atualizarOpcoesProdutos();
        }

        function atualizarTotal() {
            let totalBruto = 0;
            let menorDescontoMaximoPermitido = 100;
            let produtoLimitanteNome = "";

            let percentualDesconto = parseFloat(descontoGlobalInput?.value) || 0;

            tableBody.querySelectorAll('tr').forEach(tr => {
                const produtoSelect = tr.querySelector('.produtoSelect');
                const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
                const precoCobrado = parseFloat(tr.querySelector('.preco').value) || 0;
                const subtotal = qtd * precoCobrado;

                tr.querySelector('.subtotalLabel').textContent = "R$ " + subtotal.toFixed(2).replace('.', ',');
                totalBruto += subtotal;

                if (produtoSelect && produtoSelect.value) {
                    const produtoId = produtoSelect.value;
                    const produtoDados = produtos.find(p => p.id == produtoId);

                    if (produtoDados) {
                        const pv1 = parseFloat(produtoDados.preco_venda) || 0;
                        const pv2 = parseFloat(produtoDados.preco_venda_2) || 0;
                        const pv3 = parseFloat(produtoDados.preco_venda_3) || 0;

                        const descMax1 = parseFloat(produtoDados.desconto_max_1) || 0;
                        const descMax2 = parseFloat(produtoDados.desconto_max_2) || 0;
                        const descMax3 = parseFloat(produtoDados.desconto_max_3) || 0;

                        let descMaxProduto = descMax1;

                        if (Math.abs(precoCobrado - pv2) < 0.01) {
                            descMaxProduto = descMax2;
                        } else if (Math.abs(precoCobrado - pv3) < 0.01) {
                            descMaxProduto = descMax3;
                        } else if (Math.abs(precoCobrado - pv1) >= 0.01) {
                            descMaxProduto = Math.min(descMax1, descMax2, descMax3);
                        }

                        if (descMaxProduto < menorDescontoMaximoPermitido) {
                            menorDescontoMaximoPermitido = descMaxProduto;
                            produtoLimitanteNome = produtoDados.nome || "";
                        }
                    }
                }
            });

            if (percentualDesconto > menorDescontoMaximoPermitido) {
                alert(`Atenção! O desconto de ${percentualDesconto}% excede o limite máximo permitido de ${menorDescontoMaximoPermitido}% definido pelo Markup para o produto: ${produtoLimitanteNome}. Valor reajustado.`);
                percentualDesconto = menorDescontoMaximoPermitido;

                if (descontoGlobalInput) {
                    descontoGlobalInput.value = menorDescontoMaximoPermitido;
                }
            }

            const valorDesconto = totalBruto * (percentualDesconto / 100);
            const totalComDesconto = totalBruto - valorDesconto;

            if (totalBrutoSpan) {
                totalBrutoSpan.textContent = totalBruto.toFixed(2).replace('.', ',');
            }

            if (totalDescontoSpan) {
                totalDescontoSpan.textContent = valorDesconto.toFixed(2).replace('.', ',');
            }

            if (totalComDescontoSpan) {
                totalComDescontoSpan.textContent = totalComDesconto.toFixed(2).replace('.', ',');
            }

            if (totalBrutoInput) {
                totalBrutoInput.value = totalBruto.toFixed(2);
            }

            if (totalDescontoInput) {
                totalDescontoInput.value = valorDesconto.toFixed(2);
            }

            if (totalLiquidoInput) {
                totalLiquidoInput.value = totalComDesconto.toFixed(2);
            }
        }

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

            atualizarOpcoesProdutos();
            atualizarTotal();
        });

        tableBody.addEventListener('input', e => {
            if (e.target.classList.contains('qtd')) {
                atualizarTotal();
            }
        });

        if (descontoGlobalInput) {
            descontoGlobalInput.addEventListener('input', atualizarTotal);
        }

        tableBody.addEventListener('click', e => {
            if (e.target.classList.contains('remover')) {
                e.target.closest('tr').remove();
                atualizarOpcoesProdutos();
                atualizarTotal();
            }
        });

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

        atualizarTotal();
    });
</script>

<!-- exibir ou ocultar valor frete -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoEntrega = document.getElementById('tipo_entrega');
        const cobrarFrete = document.getElementById('cobrar_frete');
        const camposEntrega = document.querySelectorAll('.campo-entrega');
        const camposFrete = document.querySelectorAll('.campo-frete');
        const valorFrete = document.getElementById('valor_frete');

        function atualizarEntrega() {
            const entrega = tipoEntrega?.value === 'entrega';

            camposEntrega.forEach(el => {
                el.classList.toggle('d-none', !entrega);
            });

            if (!entrega) {
                if (cobrarFrete) cobrarFrete.value = '0';
                if (valorFrete) valorFrete.value = '';
                atualizarFrete();
            }
        }

        function atualizarFrete() {
            const mostrarFrete =
                tipoEntrega?.value === 'entrega' &&
                cobrarFrete?.value === '1';

            camposFrete.forEach(el => {
                el.classList.toggle('d-none', !mostrarFrete);
            });

            if (!mostrarFrete && valorFrete) {
                valorFrete.value = '';
            }
        }

        tipoEntrega?.addEventListener('change', atualizarEntrega);
        cobrarFrete?.addEventListener('change', atualizarFrete);

        atualizarEntrega();
        atualizarFrete();
    });
</script>

<!-- Script forma de entrega -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoEntrega = document.getElementById('tipo_entrega');
        const camposEntrega = document.querySelectorAll('.campo-entrega');
        const usarEnderecoCadastrado = document.getElementById('usar_endereco_cadastrado');
        const clienteSelect = document.getElementById('clienteSelect');

        const enderecoEntrega = document.getElementById('endereco_entrega');
        const numeroEntrega = document.getElementById('numero_entrega');
        const complementoEntrega = document.getElementById('complemento_entrega');
        const bairroEntrega = document.getElementById('bairro_entrega');
        const cidadeEntrega = document.getElementById('cidade_entrega');
        const cepEntrega = document.getElementById('cep_entrega');
        const contatoEntrega = document.getElementById('contato_entrega');
        const telefoneEntrega = document.getElementById('telefone_entrega');

        function getClienteSelecionado() {
            if (!clienteSelect || !clienteSelect.value) {
                return null;
            }

            return clienteSelect.options[clienteSelect.selectedIndex];
        }

        function preencherEnderecoCliente() {
            const cliente = getClienteSelecionado();

            if (!cliente) {
                return;
            }

            if (enderecoEntrega) enderecoEntrega.value = cliente.dataset.endereco || '';
            if (numeroEntrega) numeroEntrega.value = cliente.dataset.numero || '';
            if (complementoEntrega) complementoEntrega.value = cliente.dataset.complemento || '';
            if (bairroEntrega) bairroEntrega.value = cliente.dataset.bairro || '';
            if (cidadeEntrega) cidadeEntrega.value = cliente.dataset.cidade || '';
            if (cepEntrega) cepEntrega.value = cliente.dataset.cep || '';
            if (telefoneEntrega) telefoneEntrega.value = cliente.dataset.telefone || '';
        }

        function limparEnderecoEntrega() {
            if (enderecoEntrega) enderecoEntrega.value = '';
            if (numeroEntrega) numeroEntrega.value = '';
            if (complementoEntrega) complementoEntrega.value = '';
            if (bairroEntrega) bairroEntrega.value = '';
            if (cidadeEntrega) cidadeEntrega.value = '';
            if (cepEntrega) cepEntrega.value = '';
            if (contatoEntrega) contatoEntrega.value = '';
            if (telefoneEntrega) telefoneEntrega.value = '';
        }

        function bloquearEndereco(bloquear) {
            [
                enderecoEntrega,
                numeroEntrega,
                complementoEntrega,
                bairroEntrega,
                cidadeEntrega,
                cepEntrega
            ].forEach(campo => {
                if (campo) {
                    campo.readOnly = bloquear;
                }
            });
        }

        function alternarUsoEndereco() {
            if (!usarEnderecoCadastrado) {
                return;
            }

            const usarCadastro = usarEnderecoCadastrado.value === 'sim';

            if (usarCadastro) {
                preencherEnderecoCliente();
                bloquearEndereco(true);
            } else {
                limparEnderecoEntrega();
                bloquearEndereco(false);
            }
        }

        function alternarCamposEntrega() {
            if (!tipoEntrega) {
                return;
            }

            const exibir = tipoEntrega.value === 'entrega';

            camposEntrega.forEach(campo => {
                campo.classList.toggle('d-none', !exibir);
            });

            if (!exibir) {
                limparEnderecoEntrega();
                bloquearEndereco(false);
                return;
            }

            alternarUsoEndereco();
        }

        if (tipoEntrega) {
            tipoEntrega.addEventListener('change', alternarCamposEntrega);
        }

        if (usarEnderecoCadastrado) {
            usarEnderecoCadastrado.addEventListener('change', alternarUsoEndereco);
        }

        if (clienteSelect) {
            clienteSelect.addEventListener('change', function () {
                if (
                    tipoEntrega &&
                    tipoEntrega.value === 'entrega' &&
                    usarEnderecoCadastrado &&
                    usarEnderecoCadastrado.value === 'sim'
                ) {
                    preencherEnderecoCliente();
                }
            });
        }

        alternarCamposEntrega();
    });
</script>

<!-- Bloqueio de Salvamento, clique ou enter acidental -->
<script>
    const btnSalvar = document.getElementById('btnSalvar');
    const formOrcamento = btnSalvar?.closest('form');

    let salvandoOrcamento = false;

    if (formOrcamento) {
        formOrcamento.addEventListener('submit', function (e) {

            if (salvandoOrcamento) {
                e.preventDefault();
                return false;
            }

            salvandoOrcamento = true;

            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Salvando...';
            }
        });
    }
</script>
<script src="<?php echo e(asset('js/orcamento.js')); ?>"></script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/create.blade.php ENDPATH**/ ?>