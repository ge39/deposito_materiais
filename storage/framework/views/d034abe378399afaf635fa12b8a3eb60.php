<!-- View completa do PDV gerada conforme layout solicitado -->
<!-- Você pode ajustar rota, ids e classes conforme sua lógica -->


<?php $__env->startSection('content'); ?> 

<style>
    /* reset / box model */
    *, *::before, *::after { box-sizing: border-box !important; }

    /* garante 0 nas margens/paddings principais */
    html, body, #app, .app, .main, .container, .container-fluid {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        /* overflow-x: hidden !important; */
    }

    /* força o container a ocupar exatamente a largura sem deslocamento */
    .container-fluid {
        position: relative !important;
        left: 0 !important;
        right: 0 !important;
    }

    /* zera o gutter do Bootstrap (remove espaçamento lateral gerado pela grid) */
    .row {
        --bs-gutter-x: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* colunas compactas (ajuste fino para espaçamento interno) */
    [class*="col-"] {
        padding-left: 4px !important;
        padding-right: 4px !important;
    }

    /* garante que tabelas não forcem overflow lateral */
    table {
        width: 100% !important;
        margin: 0 !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }

    /* inputs / selects / buttons - sem margens laterais extras */
    input, select, textarea, button {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* fonte conforme pedido */
    .container-fluid * {
        font-size: 12px !important;
    }
    input#descricao,
    input#codigo_barras,
    input#preco_venda,
    input#quantidade,
    input#qtd_disponivel,
    input#total_geral,
    input#unidade {
        font-size: 28px !important; font-weight: bold;
    }
    .linha-carrinho.selecionada {
        background-color: #dbeafe !important;
        outline: 3px solid #2563eb;
    }

    #acoes-carrinho {
        display: block;
        gap: 10px;
    }
    #açoes-carrinho,
   .pdv-area {
    position: relative; /* cria o contexto do PDV */
    }

    .acoes-carrinho {
    position: absolute; /* flutua DENTRO do PDV */
    width:98.8%;
    display: none;
    cursor: move;
    background: #ffffff;
    border: 2px solid #ced4da;
    border-radius: 10px;
    padding: 10px;
    z-index: 1000;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
    .linha-selecionada {
    background-color: #fff3cd !important;
    outline: 3px solid #ffc107 !important;;
    }


</style>

<!-- ...aqui segue o resto da sua view (mantive o restante igual) -->
<div class="container-fluid p-0"  style="background:#e6e6e6; width:100%; margin-top:-20 ; overflow-x:hidden;">
    <!-- TOPO -->
    <div class="row g-2 mb-2 align-items-center">

        <!-- Venda -->
        <!-- <div class="col-md-1">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox">
                <label class="fw-bold mb-0">Venda</label>
            </div>
        </div> -->

        <!-- Consignado -->
        <!-- <div class="col-md-2">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox">
                <label class="fw-bold mb-0">Consignado</label>
            </div>
        </div> -->

        <!-- Nota Fiscal -->
        <!-- <div class="col-md-2">
            <div class="d-flex align-items-center gap-2">
                <label class="fw-bold mb-0">Nota Fiscal:</label>
                <select class="form-select form-select-sm" style="width: 70px;">
                    <option>SIM</option>
                    <option>NÃO</option>
                </select>
            </div>
        </div> -->

        <!-- Operação -->
        <!-- <div class="col-md-2">
            <div class="d-flex align-items-center gap-2">
                <label class="fw-bold mb-0">Operação:</label>
                <select class="form-select form-select-sm">
                    <option>VENDA</option>
                </select>
            </div>
        </div> -->

        <!-- Botão Contas a receber -->
        <!-- <div class="col-md-2">
            <button class="btn btn-secondary w-10 fw-bold">Contas a receber</button>
        </div> -->

        <!-- Andamento -->
        <!-- <div class="col-md-2">
            <div class="bg-success text-white fw-bold text-center p-1 rounded">
                ANDAMENTO
            </div>
        </div> -->

    </div>
    <!-- CAMPOS DA VENDA -->
    <div class="row g-2 mb-2 p-2" style="background:#3a3a3a; color:white;">
        <div class="col-md-1 fw-bold mb-0">
            <label>Nº Venda</label>
            <input class="form-control">
        </div>
        <div class="col-md-1 fw-bold mb-0">
            <label>Data Venda</label>
            <input class="form-control" type="date" value="<?php echo e(date('Y-m-d')); ?>" readonly>
        </div>
        <div class="col-md-2 fw-bold mb-0">
             <!-- <label>ID</label> -->
            <input type="hidden" name="cliente_id">
            <label>Cliente</label>
            <input class="form-control" name="nome" required readonly>
        </div>
         <div class="col-md-1 fw-bold mb-0">
            <label>Pessoa</label>
            <input class="form-control" name="pessoa" required readonly>
        </div>
        <div class="col-md-1 fw-bold mb-0">
            <label>Contato Local</label>
            <input class="form-control" name="telefone" required >
        </div>
         <div class="col-md-4 fw-bold mb-0">
            <label>Endereço para entrega</label>
            <input class="form-control" name="endereco" required >
        </div>
        <div class="col-md-2 fw-bold mb-0">
            <label>Op. de Caixa</label>
                <input class="form-control" name="nome" required readonly>
        
        </div>

    </div>

    <!-- CORPO PRINCIPAL -->
    <div class="row g-2 ">

        <!-- LADO ESQUERDO -->
        <div class="col-md-5 border border-2 p-2 " style="background:#3a3a3a; color:white;">
            <div class="border p-2 mb-2">
                <label class="fw-bold">Código Barras — (F3) Para pesquisar produtos</label>
                <input
                    type="text" class="form-control bg-warning" id="codigo_barras" name="codigo_barras" autocomplete="off"
                    autofocus
                >
            </div>
                        <!-- descrição -->
            <div class="border p-2 mb-2 ">
                <label class="fw-bold">Descrição</label>
                <input class="form-control form-control-sm fs-1 fw-bold" id="descricao" readonly>
            </div>

           <div class="row mt-2 border ">
                <!-- Quantidade -->
                <div class="col-md-3">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input class="form-control form-control-sm fw-bold"  type="number" name="quantidade" id="quantidade" value= "1" min="1" >
                    <small id="msgEstoque" class="text-danger fw-bold"></small>
                </div>

                <!-- Unidade -->
                <div class="col-md-4">
                    <label for="unidade" class="form-label">Unidade</label>
                    <input type="text" name="unidade" id="unidade" readOnly class="form-control form-control-sm fs-1 fw-bold">
                </div>
                <!-- Preço venda -->
                <div class="col-md-5">  
                    <label for="preco_venda" class="form-label">Preço Venda</label>
                    <input id="preco_venda" class="form-control form-control-sm fw-bold bg-warning" style="font-size: 20px;" readonly>
                </div>

            </div>

            <div class="row mt-2 border">
                <!-- Quantidade -->
                <div class="col-md-4">
                    <label class="fw-bold">Qtd.Disponivel</label>
                    <input class="form-control form-control-sm" name="qtd_disponivel" id="qtd_disponivel" type="text" min="1" step="1" readOnly>
                </div>

                    <!-- Total Geral -->
                <div class="col-md-8">
                    <label class="fw-bold">Sub Total</label>
                    <input class="form-control form-control-sm bg-warning" style="font-size: 20px;" name="total_geral" id="total_geral" type="text" readOnly>
                </div>
            </div>

            <!-- CAMPO DE IMAGEM DO PRODUTO -->
            <div class="border bg-white mt-1" style="height: 130px; display:flex; align-items:center; justify-content:center;">
                <img id="produto-imagem" src="" alt="Imagem" style="max-width:100%; max-height:100%; object-fit:contain;">
            </div>
        </div>

        <!-- LADO DIREITO: LISTA DE ITENS -->
        <div id="pdv-area" class="pdv-area col-md-7" style="background:#3a3a3a; color:white;">
            <table class="table table-bordered table-sm bg-white">
                <thead class="table-primary fw-bold text-center fs-10px">
                    <tr>
                        <td class="text-center" style="width:50px">Item</td>
                        <td class="text-center" style="width:250px">Descrição</td>
                        <td class="text-center" style="width:70px">Qtde</td>
                        <td class="text-center" style="width:100px">Unid</td>
                        <td class="text-center" style="width:100px">Preço</td>
                        <td class="text-center" style="width:150px">SubTotal</td>
                    </tr>
                </thead>  
                <tbody id="lista-itens"></tbody> 
            </table>

             <!-- BOTÕES DE AÇÃO DO ITEM SELECIONADO -->
                <div id="acoes-carrinho" class="acoes-carrinho mt-2 bg-dark d-none">
                    <div class="d-flex gap-2 justify-content-end">
                        <button id="btnDiminuir" class="btn btn-warning btn-lg">− Diminuir</button>
                        <button id="btnRemover" class="btn btn-danger btn-lg">Remover</button>
                        <button id="btnOcultar" class="btn btn-secondary btn-lg">Ocultar</button>
                    </div>
                </div>         
        </div>
    </div>

   <!-- RODAPÉ DOS BOTÕES -->
    <div class="col-md-12 row mt-1 d-flex flex-wrap gap-1">

        <div class="col">
            <button class="btn btn-primary w-100" >F1 Inicio Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-warning w-100" id="btnF2" >F2 Cliente</button>
        </div>

        <div class="col">
            <button class="btn btn-danger w-100">F3 Produto</button>
        </div>

        <div class="col">
            <button  class="btn btn-primary w-100">F4 Orçamento</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F5 Fin. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F6 Cancel. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-secondary w-100">F8 Local. Venda</button>
        </div>

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">F9 Alt. Qtde</button>
        </div> -->

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">F10 Cad. Produto</button>
        </div> -->

        <!-- <div class="col">
            <button class="btn btn-secondary w-100">Observ. na venda</button>
        </div> -->
         
        <div class="col btn btn-dark w-100 fw-bold d-flex flex-column align-items-center justify-content-center">
            <span class="fw-bold fs-1 fw-bold text-uppercase">Total</span>
            <span id="totalGeral" class="fw-bold text-warning" style="font-size: 20px !important;">R$ 0.00</span>
        </div>
    </div>
    
</div>
<?php $__env->stopSection(); ?>

<!-- Modals atahos -->
<?php echo $__env->make('pdv.modals.modal_cliente_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_produto_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_orcamento', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php echo app('Illuminate\Foundation\Vite')([
    'resources/js/pdv/app.js',      
    'resources/js/pdv/produto.js',
    'resources/js/pdv/carrinho.js',
    'resources/js/pdv/regras.js',
    'resources/js/pdv/orcamento.js',
    'resources/js/pdv/ui.js',
    'resources/js/pdv/atalhos.js',
]); ?>










<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/index.blade.php ENDPATH**/ ?>