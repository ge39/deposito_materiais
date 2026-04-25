
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

    document.getElementById('modalProduto')
        .addEventListener('shown.bs.modal',function(){

        document.getElementById('buscaProdutoPDV').focus();

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
    document.getElementById('buscaProdutoPDV')
            .addEventListener('keyup',function(e){

            if(e.key==="ArrowDown"||e.key==="ArrowUp"||e.key==="Enter")
                return;

                let query=this.value;

                if(query.length<2){
                document.getElementById('resultadoProdutoPDV').innerHTML='';
                return;
            }

            fetch(`<?php echo e(route('pdv.buscarProduto')); ?>?query=`+encodeURIComponent(query))
            .then(res=>res.json())
            .then(data=>{

            produtos = Array.isArray(data) ? data : (data.produtos ?? data);

            produtoIndex=-1;

            let html='';

            produtos.forEach((p,i)=>{

            const qtdDisp = p.quantidade_total_disponivel ?? 0;
            const unidade = p.unidade ?? 'UN';

            html+=`

            <div class="produto-row" data-index="${i}"

                onclick="selecionarProdutoPDV(
                ${p.id},
                '${encodeURIComponent(p.nome)}',
                ${Number(p.preco_venda||0)},
                '${p.codigo_barras ?? ''}',
                '${p.sku ?? ''}',
                '${(p.marca?.nome ?? '').replace(/'/g,"\\'")}',
                '${unidade}',
                '${qtdDisp}',
                '${(p.imagem ?? '').replace(/'/g,"\\'")}'
                )">

                <div>${p.nome}</div>
                <div>${p.marca?.nome ?? ''}</div>
                <div>${unidade}</div>
                <div>${qtdDisp}</div>
                <div>${p.codigo_barras ?? ''}</div>
                <div>${p.sku ?? ''}</div>
                <div>R$ ${parseFloat(p.preco_venda||0).toFixed(2)}</div>

           
            </div>

            `;

        });

        document.getElementById('resultadoProdutoPDV').innerHTML=html;

        });

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

    // const elImg=document.getElementById('produto-imagem');
    // if(elImg){
    //     if(imagem && imagem!==''){
    //         elImg.src=imagem;
    //     }else{
    //         elImg.src="/images/produto-sem-imagem.png";
    //     }
    // }

    const modalEl=document.getElementById('modalProduto');
    const modalInstance=bootstrap.Modal.getInstance(modalEl);
    if(modalInstance) modalInstance.hide();

    const foco=document.getElementById('codigo_barras');
    if(foco){
        foco.focus();
        foco.select();
    }

}

</script>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/modals/modal_produto_pdv.blade.php ENDPATH**/ ?>