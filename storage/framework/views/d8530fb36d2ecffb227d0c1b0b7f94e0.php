
<!-- MODAL PRODUTO PDV -->
<div class="modal fade" id="modalProduto" tabindex="-1">
    <div class="modal-dialog modal-xl" style="max-width:98%;">
        <div class="modal-content modal-produto-pdv">

            <div class="modal-header">
                <h5 class="modal-title">Selecionar Produto (F3)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="text"
                id="buscaProdutoPDV"
                class="form-control mb-2"
                placeholder="Digite nome, SKU ou código">

                <!-- CABEÇALHO -->
                <div class="produto-header">

                <div>Nome</div>
                <div>Marca</div>
                <div>Unid.</div>
                <div>Qtd.</div>
                <div>Barras</div>
                <div>SKU</div>
                <div>Preço</div>


            </div>

            <!-- RESULTADO -->
            <div id="resultadoProdutoPDV"></div>

            </div>
        </div>
    </div>
</div>

<style>

    .modal-produto-pdv{
    width:100%;
    max-height:80vh;
    overflow-y:auto;
    }

    .produto-header,
    .produto-row{
    display:flex;
    align-items:center;
    font-size:14px;
    }

    .produto-header{
    background:#212529;
    color:white;
    font-weight:bold;
    padding:6px;
    }

    .produto-row{
    padding:4px;
    border-bottom:1px solid #ddd;
    cursor:pointer;
    }

    .produto-row:hover{
    background:#f2f2f2;
    }

    .produto-row.active{
    background:#0d6efd;
    color:white;
    }

    .produto-row div,
    .produto-header div{
    padding:2px 4px;
    }

    .produto-row div:nth-child(1),
    .produto-header div:nth-child(1){width:300px}

    .produto-row div:nth-child(2),
    .produto-header div:nth-child(2){width:220px}

    .produto-row div:nth-child(3),
    .produto-header div:nth-child(3){width:60px}

    .produto-row div:nth-child(4),
    .produto-header div:nth-child(4){width:80px}

    .produto-row div:nth-child(5),
    .produto-header div:nth-child(5){width:250px}

    .produto-row div:nth-child(6),
    .produto-header div:nth-child(6){width:120px}

    .produto-row div:nth-child(7),
    .produto-header div:nth-child(7){width:100px}

    .produto-row div:nth-child(8),
    .produto-header div:nth-child(8){width:90px}

    .produto-row button{
    font-size:10px;
    padding:2px 6px;
    }

</style>

<script>
    function getTipoMarkupCliente() {
        // 1. Tenta ver se a linha do cliente selecionado no HTML tem o atributo de markup
        const linhaSelecionada = document.querySelector('.cliente-row.active') || document.querySelector('.cliente-row');
        if (linhaSelecionada && linhaSelecionada.getAttribute('data-markup')) {
            return linhaSelecionada.getAttribute('data-markup');
        }

        // 2. Se não achar, verifica se o objeto global window.cliente existe e tem a propriedade
        if (window.cliente && window.cliente.tipo_cliente) {
            return window.cliente.tipo_cliente;
        }

        // 3. Retorno seguro padrão
        return 'markup_1';
    }



    let produtoIndex = -1;
    let produtos = [];

    /* F3 abre modal */

    document.addEventListener('keydown',function(e){

        if(e.key==="F3"){
        e.preventDefault();
        const modal=new bootstrap.Modal(document.getElementById('modalProduto'));
        modal.show();
        }

    });

    /* FOCO AUTOMÁTICO */

    /* FOCO AUTOMÁTICO E RESET DA TELA */
    document.getElementById('modalProduto')
    .addEventListener('shown.bs.modal', function(){
        
        // 🎯 Aguarda 100 milissegundos para a animação do Bootstrap estabilizar no navegador
        setTimeout(() => {
            const inputBusca = document.getElementById('buscaProdutoPDV');
            const divResultados = document.getElementById('resultadoProdutoPDV');

            // 1. Limpa o texto antigo e puxa o cursor do teclado de volta
            if (inputBusca) {
                inputBusca.value = ''; 
                inputBusca.focus();    // 🚀 Força o cursor a piscar dentro da caixa limpa
            }

            // 2. Limpa os blocos de produtos da pesquisa anterior
            if (divResultados) {
                divResultados.innerHTML = '';
            }
        }, 100);

    });



    /* LIMPAR BACKDROP */

    document.getElementById('modalProduto')
        .addEventListener('hidden.bs.modal',function(){

        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop')
        .forEach(el=>el.remove());

        // produtoIndex=-1;

    });

    /* BUSCA PRODUTO */
    // document.getElementById('buscaProdutoPDV')
    //         .addEventListener('keyup',function(e){

    //         if(e.key==="ArrowDown"||e.key==="ArrowUp"||e.key==="Enter")
    //             return;

    //             let query=this.value;

    //             if(query.length<2){
    //             document.getElementById('resultadoProdutoPDV').innerHTML='';
    //             return;
    //         }

    //         fetch(`<?php echo e(route('pdv.buscarProduto')); ?>?query=`+encodeURIComponent(query))
    //         .then(res=>res.json())
    //         .then(data=>{

    //         produtos = Array.isArray(data) ? data : (data.produtos ?? data);

    //         produtoIndex=-1;

    //         let html='';

    //         produtos.forEach((p,i)=>{

    //         const qtdDisp = p.quantidade_total_disponivel ?? 0;
    //         const unidade = p.unidade ?? 'UN';

    //         html+=`

    //         <div class="produto-row" data-index="${i}"

    //     //         onclick="selecionarProdutoPDV(
    //     //         ${p.id},
    //     //         '${encodeURIComponent(p.nome)}',
    //     //         ${Number(p.preco_venda||0)},
    //     //         '${p.codigo_barras ?? ''}',
    //     //         '${p.sku ?? ''}',
    //     //         '${(p.marca?.nome ?? '').replace(/'/g,"\\'")}',
    //     //         '${unidade}',
    //     //         '${qtdDisp}',
    //     //         '${(p.imagem ?? '').replace(/'/g,"\\'")}'
    //     //         )">

    //     //         <div>${p.nome}</div>
    //     //         <div>${p.marca?.nome ?? ''}</div>
    //     //         <div>${unidade}</div>
    //     //         <div>${qtdDisp}</div>
    //     //         <div>${p.codigo_barras ?? ''}</div>
    //     //         <div>${p.sku ?? ''}</div>
    //     //         <div>R$ ${parseFloat(p.preco_venda||0).toFixed(2)}</div>

        
    //     //     </div>

    //     //     `;

    //     // });
    //     // 1. Descobre o markup ativo antes de desenhar as linhas na tela
    //     const tipoMarkup = getTipoMarkupCliente();

    //     produtos.forEach((p, i) => {
    //         const qtdDisp = p.quantidade_total_disponivel ?? 0;
    //         const unidade = p.unidade ?? 'UN';

    //         // 2. Define o preço certo de forma pontual
    //         let precoExibicao = Number(p.preco_venda || 0);
    //         if (tipoMarkup === 'markup_2' && Number(p.preco_venda_2) > 0) {
    //             precoExibicao = Number(p.preco_venda_2);
    //         } else if (tipoMarkup === 'markup_3' && Number(p.preco_venda_3) > 0) {
    //             precoExibicao = Number(p.preco_venda_3);
    //         }

    //         // 3. Renderiza o HTML com a variável precoExibicao injetada
    //         html += `
    //         <div class="produto-row" data-index="${i}"
    //             onclick="selecionarProdutoPDV(
    //             ${p.id},
    //             '${encodeURIComponent(p.nome)}',
    //             ${precoExibicao},
    //             '${p.codigo_barras ?? ''}',
    //             '${p.sku ?? ''}',
    //             '${(p.marca?.nome ?? '').replace(/'/g, "\\'")}',
    //             '${unidade}',
    //             '${qtdDisp}',
    //             '${(p.imagem ?? '').replace(/'/g, "\\'")}'
    //             )">

    //             <div>${p.nome}</div>
    //             <div>${p.marca?.nome ?? ''}</div>
    //             <div>${unidade}</div>
    //             <div>${qtdDisp}</div>
    //             <div>${p.codigo_barras ?? ''}</div>
    //             <div>${p.sku ?? ''}</div>
    //             <div>R$ ${precoExibicao.toFixed(2)}</div>
    //         </div>
    //         `;
    //     });


    //     document.getElementById('resultadoProdutoPDV').innerHTML=html;

    //     });

    // });

    /* BUSCA PRODUTO CORRIGIDA E PROTEGIDA */
    document.getElementById('buscaProdutoPDV')
        .addEventListener('keyup', function(e) {

            if (e.key === "ArrowDown" || e.key === "ArrowUp" || e.key === "Enter")
                return;

            let query = this.value;

            if (query.length < 2) {
                document.getElementById('resultadoProdutoPDV').innerHTML = '';
                return;
            }

            fetch(`<?php echo e(route('pdv.buscarProduto')); ?>?query=` + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {

                    produtos = Array.isArray(data) ? data : (data.produtos ?? data);
                    produtoIndex = -1;
                    let html = '';

                    // produtos.forEach((p, i) => {
                    //     const qtdDisp = p.quantidade_total_disponivel ?? 0;
                    //     const unidade = p.unidade ?? 'UN';

                    //     // Resolve o preço correto do produto fora da concatenação da string HTML
                    //     let precoExibicao = Number(p.preco_venda || 0);
                    //     if (tipoMarkup === 'markup_2' && Number(p.preco_venda_2) > 0) {
                    //         precoExibicao = Number(p.preco_venda_2);
                    //     } else if (tipoMarkup === 'markup_3' && Number(p.preco_venda_3) > 0) {
                    //         precoExibicao = Number(p.preco_venda_3);
                    //     }

                    //     html += `
                    //     <div class="produto-row" data-index="${i}"
                    //         onclick="selecionarProdutoPDV(
                    //         ${p.id},
                    //         '${encodeURIComponent(p.nome)}',
                    //         ${precoExibicao},
                    //         '${p.codigo_barras ?? ''}',
                    //         '${p.sku ?? ''}',
                    //         '${(p.marca?.nome ?? '').replace(/'/g, "\\'")}',
                    //         '${unidade}',
                    //         '${qtdDisp}',
                    //         '${(p.imagem ?? '').replace(/'/g, "\\'")}'
                    //         )">

                    //         <div>${p.nome}</div>
                    //         <div>${p.marca?.nome ?? ''}</div>
                    //         <div>${unidade}</div>
                    //         <div>${qtdDisp}</div>
                    //         <div>${p.codigo_barras ?? ''}</div>
                    //         <div>${p.sku ?? ''}</div>
                    //         <div>R$ ${precoExibicao.toFixed(2)}</div>
                    //     </div>
                    //     `;
                    // });
                    // ====================================================================
                    // MATEMÁTICA REAL: CÁLCULO BASEADO NOS MARKUPS DA TABELA PRODUTOS
                    // ====================================================================
                    const tipoMarkupAtivo = document.getElementById('tipo_cliente_pdv')?.value || 'markup_1';
                    produtos.forEach((p, i) => {
                        const qtdDisp = p.quantidade_total_disponivel ?? 0;
                        const unidade = p.unidade ?? 'UN';

                        // 1. Captura os valores numéricos da sua tabela
                        const precoBaseGeral = Number(p.preco_venda || 0);
                        const precoEstatico2 = Number(p.preco_venda_2 || 0);
                        const precoEstatico3 = Number(p.preco_venda_3 || 0);
                        const custoReal = Number(p.custo_real_entrada || 0);

                        // 2. Captura os percentuais de markup reais da tabela produtos
                        const m1 = Number(p.markup_1 || 0);
                        const m2 = Number(p.markup_2 || 0);
                        const m3 = Number(p.markup_3 || 0);

                        let precoExibicao = precoBaseGeral;

                        // 3. Aplica a regra dinâmica baseada no tipo de markup selecionado do cliente
                        if (tipoMarkupAtivo === 'markup_2') {
                            // Se houver preço estático preenchido, usa ele. Se não, calcula com base no custo + markup_2
                            if (precoEstatico2 > 0) {
                                precoExibicao = precoEstatico2;
                            } else if (custoReal > 0 && m2 > 0) {
                                precoExibicao = custoReal * (1 + (m2 / 100));
                            }
                        } else if (tipoMarkupAtivo === 'markup_3') {
                            // Se houver preço estático preenchido, usa ele. Se não, calcula com base no custo + markup_3
                            if (precoEstatico3 > 0) {
                                precoExibicao = precoEstatico3;
                            } else if (custoReal > 0 && m3 > 0) {
                                precoExibicao = custoReal * (1 + (m3 / 100));
                            }
                        } else {
                            // Padrão markup_1
                            if (precoBaseGeral <= 0 && custoReal > 0 && m1 > 0) {
                                precoExibicao = custoReal * (1 + (m1 / 100));
                            }
                        }

                        // Garante que o valor final seja arredondado para duas casas decimais
                        precoExibicao = Number(precoExibicao.toFixed(2));

                        // 4. Monta o seu HTML original com a variável precoExibicao calculada
                        html += `
                        <div class="produto-row" data-index="${i}"
                            onclick="selecionarProdutoPDV(
                            ${p.id},
                            '${encodeURIComponent(p.nome)}',
                            ${precoExibicao},
                            '${p.codigo_barras ?? ''}',
                            '${p.sku ?? ''}',
                            '${(p.marca?.nome ?? '').replace(/'/g, "\\'")}',
                            '${unidade}',
                            '${qtdDisp}',
                            '${(p.imagem ?? '').replace(/'/g, "\\'")}'
                            )">

                            <div>${p.nome}</div>
                            <div>${p.marca?.nome ?? ''}</div>
                            <div>${unidade}</div>
                            <div>${qtdDisp}</div>
                            <div>${p.codigo_barras ?? ''}</div>
                            <div>${p.sku ?? ''}</div>
                            <div>R$ ${precoExibicao.toFixed(2)}</div>
                        </div>
                        `;
                    });
                    document.getElementById('resultadoProdutoPDV').innerHTML = html;
                })
                .catch(err => console.error("Erro na busca de produtos:", err)); // Exibe no console caso a rota falhe
        });
        /* NAVEGAÇÃO TECLADO */
        document.addEventListener('keydown',function(e){
            const rows=document.querySelectorAll(".produto-row");

            if(rows.length===0) return;

            if(e.key==="ArrowDown"){
                e.preventDefault();
                produtoIndex++;
            if(produtoIndex>=rows.length) produtoIndex=rows.length-1;
            }

            if(e.key==="ArrowUp"){
                e.preventDefault();
                produtoIndex--;
            if(produtoIndex<0) produtoIndex=0;
            }

            if(e.key==="Enter"){
                e.preventDefault();
                if(produtoIndex>=0){
                    
                    const p=produtos[produtoIndex];
                    selecionarProdutoPDV(
                        p.id,
                        encodeURIComponent(p.nome),
                        Number(p.preco_venda || 0),
                        p.codigo_barras ?? '',
                        p.sku ?? '',
                        p.marca?.nome ?? '',
                        p.unidade ?? 'UN',
                        p.quantidade_total_disponivel ?? 0,
                        p.imagem ?? ''
                    );
                }
            }

            rows.forEach(r=>r.classList.remove("active"));

            if(rows[produtoIndex])
            rows[produtoIndex].classList.add("active");

        });

        /* FUNÇÃO SELECIONAR PRODUTO */

        function selecionarProdutoPDV(id,nomeEncoded,preco,barras,sku,marca,unidade,qtdDisponivel,imagem){

        const nome=decodeURIComponent(nomeEncoded);

        const elId=document.getElementById('produtoId');
        if(elId) elId.value=id;

        const elDesc=document.getElementById('descricao');
        if(elDesc) elDesc.value=nome;

        const elPreco=document.getElementById('preco_venda');
        if(elPreco) elPreco.value=parseFloat(preco).toFixed(2);

        const elQtd=document.getElementById('qtd_disponivel');
        if(elQtd) elQtd.value=qtdDisponivel;

        const elQuantidade=document.getElementById('quantidade');
        if(elQuantidade){
        elQuantidade.value=1;
        elQuantidade.max=qtdDisponivel;
        }

        const elUn=document.getElementById('unidade');
        if(elUn) elUn.value=unidade;

        const elTotal=document.getElementById('total_geral');
        if(elTotal) elTotal.value=parseFloat(preco).toFixed(2);

        const elBarras=document.getElementById('codigo_barras');
        if(elBarras) elBarras.value=barras;

        const elSku=document.getElementById('sku');
        if(elSku) elSku.value=sku;

        const elMarca=document.getElementById('marca');
        if(elMarca) elMarca.value=marca;

        const elImg=document.getElementById('produto-imagem');
        if(elImg){
            if(imagem && imagem!==''){
                elImg.src=imagem;
            }else{
                elImg.src="/images/produto-sem-imagem.png";
            }
        }

    // ========================================================
    // FECHA MODAL - ESTRATÉGIA AGRESSIVA DE DESBLOQUEIO DE TELA
    // ========================================================
    const modalEl = document.getElementById('modalProduto');
        if (modalEl) {
            // 1. Força o encerramento visual imediato escondendo as classes de estilo
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');
            modalEl.removeAttribute('role');

            // 2. Encerra e limpa a instância na memória do Bootstrap
            if (typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                    if (typeof modalInstance.dispose === 'function') {
                        modalInstance.dispose(); 
                    }
                }
            }

            // 3. Destrava fisicamente a rolagem e cliques do Body do site
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';

            // 4. Varre a tela e remove todas as cortinas escuras (backdrops) duplicadas
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
        }

        // ========================================================
        // CONTROLE DE FOCO SEGURO NO INPUT DO PDV
        // ========================================================
        setTimeout(() => {
            const foco = document.getElementById('codigo_barras');
            if (foco) {
                foco.focus();
                foco.setSelectionRange(0, 0);
            }
        }, 50); // Delay curto para garantir que o DOM foi totalmente liberado
    }
</script>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_produto_pdv.blade.php ENDPATH**/ ?>