<div class="modal fade" id="modalProduto" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Selecionar Produto (F3)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="text" id="buscaProdutoPDV" class="form-control mb-2"
                       placeholder="Digite nome, SKU ou código">

                <div id="resultadoProdutoPDV"></div>

            </div>

        </div>
    </div>
</div>

<!-- BUSCA PRODUTO  Modal-->
<script>
    document.addEventListener('DOMContentLoaded', function () {

    let modalProduto = document.getElementById('modalProduto');

    modalProduto.addEventListener('shown.bs.modal', function () {
        document.getElementById('buscaProdutoPDV').focus();
    });

    // ======================================================================
    // BUSCA PRODUTO
    // ======================================================================
    document.getElementById('buscaProdutoPDV').addEventListener('keyup', function () {

        let query = this.value;

        if (query.length < 2) {
            document.getElementById('resultadoProdutoPDV').innerHTML = '';
            return;
        }

        fetch(`<?php echo e(route('pdv.buscarProduto')); ?>?query=` + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {

        // Se controller retorna objeto com { status, produtos } adapte aqui:
        // data = Array de produtos (formato atual do seu controller)
       let produtos = Array.isArray(data) ? data : (data.produtos ?? data);

        let html = `
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 150px;">Nome</th>
                            <th style="width: 100px;">Marca</th>
                            <th style="width: 40px;">Unid.</th>
                            <th style="width: 50px;">Qtd. Disp.</th>
                            <th style="width: 100px;">Barras</th>
                            <th style="width: 100px;">SKU</th>
                            <th style="width: 50px;">Preço</th>
                            <th style="width: 50px;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        produtos.forEach(p => {

            // Quantidade total retornada pelo controller
            const qtdDisp = p.quantidade_total_disponivel ?? 1;

            // Unidade — seu controller retorna diretamente p.unidade = sigla
            const unidade = p.unidade ?? 'UN';

            // Imagem (já vem asset() do controller)
            const imagem = p.imagem ?? '';

            const nomeEncode = encodeURIComponent(p.nome);

            html += `
                <tr class="pointer"
                    onclick="selecionarProdutoPDV(
                        ${p.id},
                        '${nomeEncode}',
                        ${Number(p.preco_venda || 0)},
                        '${p.codigo_barras ?? ''}',
                        '${p.sku ?? ''}',
                        '${(p.marca?.nome ?? '').replace(/'/g, "\\'")}',
                        '${unidade}',
                        '${qtdDisp}',
                        '${(imagem).replace(/'/g, "\\'")}'
                    )">

                    <td>${p.nome}</td>
                    <td>${p.marca?.nome ?? ''}</td>
                    <td class="text-center">${p.unidade}</td>
                    <td class="text-end ">${qtdDisp}</td>
                    <td>${p.codigo_barras ?? ''}</td>
                    <td>${p.sku ?? ''}</td>
                    <td>R$ ${parseFloat(p.preco_venda || 0).toFixed(2)}</td>

                    <td class="text-center">
                        <button class="btn btn-sm btn-primary">Selecionar</button>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('resultadoProdutoPDV').innerHTML = html;


        });
    });
    });
</script>

<script>
    document.getElementById('modalProduto')
    .addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop')
            .forEach(el => el.remove());
    });
</script>

<!--  FUNÇÃO FINAL E ÚNICA: SELECIONAR PRODUTO NO PDV -->
<script>
    // ======================================================================
    // FUNÇÃO FINAL E ÚNICA: SELECIONAR PRODUTO NO PDV
    // ======================================================================
    function selecionarProdutoPDV(id, nomeEncoded, preco, barras, sku, marca, unidade, qtdDisponivel, imagem) {

    const nome = decodeURIComponent(nomeEncoded);

    // Preenche ID (se existir)
    const elId = document.getElementById('produtoId');
    if (elId) elId.value = id;

    // Descrição
    const elDesc = document.getElementById('descricao');
    if (elDesc) elDesc.value = nome;

    // Preço de venda
    const elPreco = document.getElementById('preco_venda');
    if (elPreco) elPreco.value = parseFloat(preco).toFixed(2);

    // Quantidade disponível
    const elqtd_disponivel = document.getElementById('qtd_disponivel');
    if (elqtd_disponivel) elqtd_disponivel.value = qtdDisponivel;

    //Quantidade
    const elQuantidade = document.getElementById('quantidade').value = 1;
    if (elQuantidade) {
        elQuantidade.max = qtdDisponivel;   // <-- define o valor máximo permitido
    } 

    // Unidade
    const elUn = document.getElementById('unidade');
    if (elUn) elUn.value = unidade;

    // Preco total geral
    const elTotalGeral = document.getElementById('total_geral');
    if (elTotalGeral) elTotalGeral.value = parseFloat(preco).toFixed(2);

    // Código de barras 
    const elBarras = document.getElementById('codigo_barras');
    if (elBarras) elBarras.value = barras;

    // SKU
    const elSku = document.getElementById('sku');
    if (elSku) elSku.value = sku;

    // Marca
    const elMarca = document.getElementById('marca');
    if (elMarca) elMarca.value = marca;

    // Imagem: imagem já é URL completa do controller (asset(...))
    const elImg = document.getElementById('produto-imagem');
    if (elImg) {
        if (imagem && imagem !== '') {
            elImg.src = imagem;
        } else {
            elImg.src = "/images/produto-sem-imagem.png";
        }
    }

    // Fecha o modal (certifica-se que existe a instância)
    const modalEl = document.getElementById('modalProduto');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();

    // Move foco para campo quantidade
    const qtd = document.getElementById('codigo_barras');
    if (qtd) {
        qtd.focus();
        qtd.select();
    }
    }
</script>

<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_produto_pdv.blade.php ENDPATH**/ ?>