




<?php $__env->startSection('content'); ?>
<style>
    .price-adjust-page {
        --navy: #072b62;
        --blue: #2267bd;
        --border: #d9e0e8;
        --soft: #f6f8fb;
        --green: #198754;
        --red: #dc3545;
        --orange: #f48120;
        color: #1f2937;
    }

    .price-adjust-page .page-header {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .price-adjust-page .page-title {
        color: var(--navy);
        font-size: 1.45rem;
        font-weight: 850;
        margin: 0;
    }

    .price-adjust-page .page-subtitle {
        color: #6b7280;
        font-size: .78rem;
        margin-top: .2rem;
    }

    .price-adjust-page .section-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: .55rem;
        box-shadow: 0 .15rem .4rem rgba(15, 23, 42, .06);
        margin-bottom: .85rem;
        overflow: hidden;
    }

    .price-adjust-page .section-title {
        align-items: center;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        color: var(--navy);
        display: flex;
        font-size: .78rem;
        font-weight: 850;
        gap: .45rem;
        justify-content: space-between;
        padding: .65rem .8rem;
        text-transform: uppercase;
    }

    .price-adjust-page .section-body {
        padding: .85rem;
    }

    .price-adjust-page .form-label {
        color: #42526a;
        font-size: .68rem;
        font-weight: 800;
        margin-bottom: .22rem;
        text-transform: uppercase;
    }

    .price-adjust-page .form-control,
    .price-adjust-page .form-select {
        font-size: .8rem;
    }

    .price-adjust-page .helper {
        color: #7a8798;
        font-size: .64rem;
        margin-top: .2rem;
    }

    .price-adjust-page .summary-grid {
        display: grid;
        gap: .55rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .price-adjust-page .summary-card {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: .45rem;
        padding: .65rem .7rem;
    }

    .price-adjust-page .summary-label {
        color: #6b7280;
        font-size: .61rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .price-adjust-page .summary-value {
        color: var(--navy);
        font-size: 1rem;
        font-weight: 900;
        margin-top: .15rem;
    }

    .price-adjust-page .preview-table {
        margin: 0;
        min-width: 940px;
    }

    .price-adjust-page .preview-table th {
        background: var(--navy);
        color: #fff;
        font-size: .66rem;
        padding: .48rem;
        text-align: center;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .price-adjust-page .preview-table td {
        font-size: .72rem;
        padding: .48rem;
        vertical-align: middle;
    }

    .price-adjust-page .preview-table tbody tr:hover td {
        background: #f4f8fd;
    }

    .price-adjust-page .price-old {
        color: #6b7280;
        font-weight: 700;
    }

    .price-adjust-page .price-new {
        color: var(--green);
        font-weight: 900;
    }

    .price-adjust-page .adjustment-positive {
        color: var(--green);
        font-weight: 850;
    }

    .price-adjust-page .adjustment-negative {
        color: var(--red);
        font-weight: 850;
    }

    .price-adjust-page .audit-row {
        align-items: center;
        border-bottom: 1px solid #edf0f4;
        display: grid;
        gap: .65rem;
        grid-template-columns: 150px minmax(160px, .8fr) minmax(220px, 1.4fr) 110px;
        padding: .55rem .7rem;
    }

    .price-adjust-page .audit-row:last-child {
        border-bottom: 0;
    }

    .price-adjust-page .audit-main {
        font-size: .7rem;
        font-weight: 800;
    }

    .price-adjust-page .audit-detail {
        color: #6b7280;
        font-size: .64rem;
        margin-top: .1rem;
    }

    .price-adjust-page .actions-bar {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: flex-end;
    }

    .price-adjust-page .warning-box {
        background: #fff8e7;
        border: 1px solid #efd18a;
        border-radius: .4rem;
        color: #6e5200;
        font-size: .7rem;
        padding: .65rem .75rem;
    }

    .price-adjust-page .badge-scope {
        background: #e8f1fb;
        border: 1px solid #aac5e8;
        color: #164f94;
        font-size: .6rem;
        font-weight: 850;
    }

    @media (max-width: 991.98px) {
        .price-adjust-page .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .price-adjust-page .audit-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .price-adjust-page .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid px-3 px-xl-4 py-3 price-adjust-page">

    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="bi bi-tags-fill me-2"></i>
                Ajuste de Preços em Massa
            </h1>

            <div class="page-subtitle">
                Atualização comercial de preços sem movimentar quantidades ou lotes de estoque.
            </div>
        </div>

        <a href="#" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar
        </a>
    </div>

    <div class="section-card">
        <div class="section-title">
            <span>
                <i class="bi bi-funnel-fill me-1"></i>
                1. Seleção dos produtos
            </span>

            <span class="badge badge-scope">
                Somente simulação
            </span>
        </div>

        <div class="section-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Aplicar ajuste em</label>
                    <select class="form-select" id="escopo">
                        <option value="categoria" selected>Categoria</option>
                        <option value="fornecedor">Fornecedor</option>
                        <option value="produto">Produto específico</option>
                        <option value="selecionados">Produtos selecionados</option>
                        <option value="todos">Todos os produtos</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Filtro</label>
                    <select class="form-select">
                        <option selected>Cimento</option>
                        <option>Argamassa</option>
                        <option>Areia e Pedra</option>
                        <option>Blocos e Tijolos</option>
                    </select>
                    <div class="helper">
                        O conteúdo deste campo muda conforme o escopo escolhido.
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Situação</label>
                    <select class="form-select">
                        <option selected>Somente ativos</option>
                        <option>Todos</option>
                        <option>Somente inativos</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Estoque</label>
                    <select class="form-select">
                        <option selected>Com estoque disponível</option>
                        <option>Todos os produtos</option>
                        <option>Sem estoque</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-title">
            <span>
                <i class="bi bi-calculator-fill me-1"></i>
                2. Regra do ajuste
            </span>
        </div>

        <div class="section-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Preço a alterar</label>
                    <select class="form-select" id="campo-preco">
                        <option value="preco_venda" selected>Preço de Venda 1</option>
                        <option value="preco_venda_2">Preço de Venda 2</option>
                        <option value="preco_venda_3">Preço de Venda 3</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de ajuste</label>
                    <select class="form-select" id="tipo-ajuste">
                        <option value="acrescimo_percentual" selected>Acréscimo percentual</option>
                        <option value="desconto_percentual">Desconto percentual</option>
                        <option value="acrescimo_valor">Acréscimo em valor</option>
                        <option value="desconto_valor">Desconto em valor</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Valor</label>
                    <div class="input-group">
                        <span class="input-group-text" id="prefixo-ajuste">%</span>
                        <input
                            type="number"
                            class="form-control"
                            id="valor-ajuste"
                            value="5.00"
                            min="0"
                            step="0.01"
                        >
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Arredondamento</label>
                    <select class="form-select">
                        <option selected>2 casas decimais</option>
                        <option>Final .90</option>
                        <option>Final .99</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button"
                            class="btn btn-primary w-100"
                            id="btn-simular">
                        <i class="bi bi-play-circle me-1"></i>
                        Simular
                    </button>
                </div>

            </div>

            <div class="warning-box mt-3">
                <i class="bi bi-info-circle-fill me-1"></i>
                Esta operação altera somente o preço comercial do produto.
                Quantidade, reserva, custo e lotes permanecem inalterados.
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-title">
            <span>
                <i class="bi bi-eye-fill me-1"></i>
                3. Pré-visualização
            </span>

            <span class="badge bg-secondary" id="preview-status">
                Simulação
            </span>
        </div>

        <div class="section-body">

            <div class="summary-grid mb-3">
                <div class="summary-card">
                    <div class="summary-label">Produtos afetados</div>
                    <div class="summary-value" id="sum-produtos">4</div>
                </div>

                <div class="summary-card">
                    <div class="summary-label">Estoque disponível</div>
                    <div class="summary-value">420,000</div>
                </div>

                <div class="summary-card">
                    <div class="summary-label">Preço médio atual</div>
                    <div class="summary-value" id="sum-atual">R$ 29,13</div>
                </div>

                <div class="summary-card">
                    <div class="summary-label">Preço médio novo</div>
                    <div class="summary-value" id="sum-novo">R$ 30,59</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered preview-table">
                    <thead>
                        <tr>
                            <th style="width: 42px;">
                                <input type="checkbox" checked>
                            </th>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Fornecedor</th>
                            <th>Estoque</th>
                            <th>Preço Atual</th>
                            <th>Ajuste</th>
                            <th>Novo Preço</th>
                        </tr>
                    </thead>

                    <tbody id="preview-body">
                        <tr data-preco="35.00">
                            <td class="text-center">
                                <input type="checkbox" checked>
                            </td>
                            <td>
                                <strong>Cimento CP-II 50kg</strong>
                                <div class="text-muted small">SKU: CIM-CP2-50</div>
                            </td>
                            <td>Cimento</td>
                            <td>Fornecedor Alfa</td>
                            <td class="text-end">150,000 SC</td>
                            <td class="text-end price-old">R$ 35,00</td>
                            <td class="text-end adjustment-positive ajuste">+5,00%</td>
                            <td class="text-end price-new novo-preco">R$ 36,75</td>
                        </tr>

                        <tr data-preco="39.00">
                            <td class="text-center">
                                <input type="checkbox" checked>
                            </td>
                            <td>
                                <strong>Cimento CP-III 50kg</strong>
                                <div class="text-muted small">SKU: CIM-CP3-50</div>
                            </td>
                            <td>Cimento</td>
                            <td>Fornecedor Alfa</td>
                            <td class="text-end">80,000 SC</td>
                            <td class="text-end price-old">R$ 39,00</td>
                            <td class="text-end adjustment-positive ajuste">+5,00%</td>
                            <td class="text-end price-new novo-preco">R$ 40,95</td>
                        </tr>

                        <tr data-preco="22.50">
                            <td class="text-center">
                                <input type="checkbox" checked>
                            </td>
                            <td>
                                <strong>Argamassa AC-II 20kg</strong>
                                <div class="text-muted small">SKU: ARG-AC2-20</div>
                            </td>
                            <td>Cimento</td>
                            <td>Fornecedor Beta</td>
                            <td class="text-end">110,000 SC</td>
                            <td class="text-end price-old">R$ 22,50</td>
                            <td class="text-end adjustment-positive ajuste">+5,00%</td>
                            <td class="text-end price-new novo-preco">R$ 23,63</td>
                        </tr>

                        <tr data-preco="20.00">
                            <td class="text-center">
                                <input type="checkbox" checked>
                            </td>
                            <td>
                                <strong>Argamassa AC-I 20kg</strong>
                                <div class="text-muted small">SKU: ARG-AC1-20</div>
                            </td>
                            <td>Cimento</td>
                            <td>Fornecedor Beta</td>
                            <td class="text-end">80,000 SC</td>
                            <td class="text-end price-old">R$ 20,00</td>
                            <td class="text-end adjustment-positive ajuste">+5,00%</td>
                            <td class="text-end price-new novo-preco">R$ 21,00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="actions-bar mt-3">
                <button type="button" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>

                <button type="button" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Recalcular
                </button>

                <button type="button" class="btn btn-success">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Confirmar Alteração
                </button>
            </div>

        </div>
    </div>

    <div class="section-card">
        <div class="section-title">
            <span>
                <i class="bi bi-clock-history me-1"></i>
                Histórico recente de ajustes
            </span>

            <button type="button"
                    class="btn btn-outline-secondary btn-sm">
                Ver histórico completo
            </button>
        </div>

        <div>
            <div class="audit-row">
                <div>
                    <div class="audit-main">01/10/2026 17:42</div>
                    <div class="audit-detail">Jeremias</div>
                </div>

                <div>
                    <div class="audit-main">Categoria: Cimento</div>
                    <div class="audit-detail">Preço de Venda 1</div>
                </div>

                <div>
                    <div class="audit-main">Acréscimo percentual de 5,00%</div>
                    <div class="audit-detail">18 produtos alterados</div>
                </div>

                <div class="text-end">
                    <button class="btn btn-outline-primary btn-sm">
                        Detalhes
                    </button>
                </div>
            </div>

            <div class="audit-row">
                <div>
                    <div class="audit-main">29/09/2026 09:15</div>
                    <div class="audit-detail">Administrador</div>
                </div>

                <div>
                    <div class="audit-main">Fornecedor: Beta</div>
                    <div class="audit-detail">Preço de Venda 1</div>
                </div>

                <div>
                    <div class="audit-main">Desconto fixo de R$ 0,50</div>
                    <div class="audit-detail">7 produtos alterados</div>
                </div>

                <div class="text-end">
                    <button class="btn btn-outline-primary btn-sm">
                        Detalhes
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipo = document.getElementById('tipo-ajuste');
    const valor = document.getElementById('valor-ajuste');
    const prefixo = document.getElementById('prefixo-ajuste');
    const btnSimular = document.getElementById('btn-simular');
    const linhas = [...document.querySelectorAll('#preview-body tr')];

    const moeda = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });

    function atualizarPrefixo() {
        const percentual = tipo.value.includes('percentual');
        prefixo.textContent = percentual ? '%' : 'R$';
    }

    function simular() {
        const ajuste = Math.max(0, Number(valor.value || 0));
        let somaAtual = 0;
        let somaNova = 0;
        let total = 0;

        linhas.forEach(function (linha) {
            const preco = Number(linha.dataset.preco || 0);
            let novo = preco;
            let rotulo = '';

            switch (tipo.value) {
                case 'acrescimo_percentual':
                    novo = preco * (1 + ajuste / 100);
                    rotulo = '+' + ajuste.toFixed(2).replace('.', ',') + '%';
                    break;

                case 'desconto_percentual':
                    novo = preco * (1 - ajuste / 100);
                    rotulo = '-' + ajuste.toFixed(2).replace('.', ',') + '%';
                    break;

                case 'acrescimo_valor':
                    novo = preco + ajuste;
                    rotulo = '+ ' + moeda.format(ajuste);
                    break;

                case 'desconto_valor':
                    novo = Math.max(0, preco - ajuste);
                    rotulo = '- ' + moeda.format(ajuste);
                    break;
            }

            novo = Math.max(0, Math.round((novo + Number.EPSILON) * 100) / 100);

            const celulaAjuste = linha.querySelector('.ajuste');
            const celulaNovo = linha.querySelector('.novo-preco');

            celulaAjuste.textContent = rotulo;
            celulaAjuste.classList.toggle(
                'adjustment-negative',
                tipo.value.startsWith('desconto')
            );
            celulaAjuste.classList.toggle(
                'adjustment-positive',
                tipo.value.startsWith('acrescimo')
            );

            celulaNovo.textContent = moeda.format(novo);

            somaAtual += preco;
            somaNova += novo;
            total++;
        });

        document.getElementById('sum-produtos').textContent = total;
        document.getElementById('sum-atual').textContent =
            moeda.format(total ? somaAtual / total : 0);
        document.getElementById('sum-novo').textContent =
            moeda.format(total ? somaNova / total : 0);
    }

    tipo.addEventListener('change', function () {
        atualizarPrefixo();
        simular();
    });

    valor.addEventListener('input', simular);
    btnSimular.addEventListener('click', simular);

    atualizarPrefixo();
    simular();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\produtos\ajuste_precos.blade.php ENDPATH**/ ?>