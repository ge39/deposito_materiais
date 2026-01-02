<!-- View completa do PDV gerada conforme layout solicitado -->
<!-- Você pode ajustar rota, ids e classes conforme sua lógica -->

@extends('layouts.app')
@section('content') 

<style>
    /* estilo pra bloqueio de caixa */
    :root {
        --bordo: #6b0f1a;
        --bordo-escuro: #4a0a12;
        --bordo-claro: #8b1c2b;
    }

    /* ===== OVERLAY CAIXA BLOQUEADO ===== */
    #modalBloquearCaixa {
        position: fixed;
        inset: 0;

        background: rgba(107, 15, 26, 0.42);

        z-index: 999999;

        /* ⚠️ IMPORTANTE: desativado por padrão */
        display: none;

        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    /* overlay só aparece quando o caixa está bloqueado */
    body.caixa-bloqueado #modalBloquearCaixa {
        display: flex;
    }
   
    /* CARIMBO */
    .carimbo-caixa {
        position: absolute;

        top: 50%;
        left: 50%;
        width: 65%;
        transform: translate(-50%, -50%) rotate(-25deg);

        font-size: 56px;
        font-weight: 900;

        color: rgba(255, 255, 255, 0.75);
        border: 6px solid rgba(255, 255, 255, 0.35);
        padding: 18px 55px;

        text-transform: uppercase;
        letter-spacing: 4px;

        user-select: none;
        pointer-events: none;
    }

    /* BOTÃO — único elemento ativo */
    .btn-abrir-caixa {
        position: absolute;

        top: 50%;
        left: 50%;

        transform: translate(-50%, calc(-50% + 120px));

        background: #ffffff;
        color: var(--bordo);
        border: 3px solid var(--bordo);

        padding: 16px 40px;
        font-size: 22px;
        font-weight: bold;

        cursor: pointer;
        z-index: 1;
    }


    .btn-abrir-caixa:hover {
        background: #f5f5f5;
    }

    /* BLOQUEIA SCROLL */
    body.caixa-bloqueado {
        overflow: hidden;
    }

    /* BLOQUEIO REAL DO PDV */
    body.caixa-bloqueado #pdv-app {
        pointer-events: none;
        filter: blur(1px) grayscale(60%);
    }

    /* fim estilo pra bloqueio de caixa */

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

       /* Estilo para toda a div de informações do caixa */
    .caixa-info {
         font-size: 20px !important; /* aumenta a fonte e garante prioridade */
        gap: 50px;
    }

    /* Aumenta os títulos (strong) */
    .caixa-info strong {
        font-size: 18px !important; /* aumenta a fonte e garante prioridade */
    }
    /* Cores para cada status do PDV */
    .status-aberto {
        color: green;
        font-weight: bold;
    }
    .status-fechado {
        color: red;
        font-weight: bold;
    }
    .status-pendente {
        color: orange;
        font-weight: bold;
    }
    .status-inconsistente {
        color: purple;
        font-weight: bold;
    }
    .listaCaixasEsquecidos{
        margin: 500px 20px 0;
        list-style: none;
        padding: 10; 
        font-size:18px; 
        font-weight:bold;
        color:snow;
        background-color: red;
    }
</style>

    
   <!-- OVERLAY -->
    <div id="modalBloquearCaixa" style="display: none;">
        <div class="carimbo-caixa">CAIXA BLOQUEADO</div>

        <!-- Lista de caixas esquecidos -->
         <div class="listaCaixasEsquecidos" id="listaCaixasEsquecidos">
            <h4>ERROR:</h4>
            <h4 style="color:yellow">Este caixa nao pode ser aberto - informe o responsavel da loja</h4>
            <ul>
            </ul>
        </div>

        <button class="btn-abrir-caixa"
            onclick="window.location.href='{{ route('caixa.abrir') }}'">
            ABRIR CAIXA
        </button>
    </div>
    <!-- FIM OVERLAY -->

     <!-- Informações do status do Caixa -->
    <div class="container-fluid p-0"  
         style="background:#e6e6e6; width:60%; margin-top:-15px; overflow-x:hidden;">
       
        <div class="caixa-info mb-3 p-0 border rounded shadow-sm bg-light d-flex justify-content-start align-items-center">
            <span><strong>Terminal: 00{{ $terminal->id }}</strong></span>
            <span><strong>Operador: {{ $operador }}</strong> </span>
            <span><strong>Status:  
                <span class="
                    {{ $status === 'Aberto' ? 'status-aberto' : '' }}
                    {{ $status === 'Fechado' ? 'status-fechado' : '' }}
                    {{ $status === 'Pendente' ? 'status-pendente' : '' }}
                    {{ $status === 'Inconsistente' ? 'status-inconsistente' : '' }}
                "><strong>
                    {{ $status }}
                </strong></span>
            </span>
        </div>
    </div>
    <!-- FILTROS DA VENDA -->
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
        <div class="col-md-2 fw-bold mb-0">
            <label>Data Venda</label>
            <input class="form-control" type="date" value="{{ date('Y-m-d') }}" readonly>
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
        <div class="col-md-2 fw-bold mb-0">
            <label>Contato Local</label>
            <input class="form-control" name="telefone" required >
        </div>
         <div class="col-md-5 fw-bold mb-0">
            <label>Endereço para entrega</label>
            <input class="form-control" name="endereco" required >
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
                <label class="fw-bold">Descrição</label><small id="alerta-lote" class="fw-bold d-none"></small>

                <input class="form-control form-control-sm fs-1 fw-bold" id="descricao" readonly>
                <small id="alerta-lote" class="fw-bold d-none"></small>

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
            <table class="table table-bordered table-sm bg-white" style="font-size: 20px !important;">
                <thead class="table-primary fw-bold text-center fs-10px">
                    <tr style="font-size: 20px !important;">
                        <td class="text-center" style="width:50px">Item</td>
                        <td class="text-center" style="width:250px;">Descrição</td>
                        <td class="text-center" style="width:70px">Qtde</td>
                        <td class="text-center" style="width:100px">Unid</td>
                        <td class="text-center" style="width:100px">Preço</td>
                        <td class="text-center" style="width:150px">SubTotal</td>
                    </tr>
                </thead>  
                <tbody id="lista-itens" ></tbody> 
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
        <div class="col btn btn-dark w-100 fw-bold d-flex flex-column align-items-center justify-content-center">
            <span class="fw-bold fs-1 fw-bold text-uppercase" style="font-size: 20px !important;">Total</span>
            <span id="totalGeral" class="fw-bold text-warning" style="font-size: 20px !important;">R$ 0.00</span>
        </div>
    </div>
    
</div>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('modalBloquearCaixa');

        // ⚠️ Estado atual do caixa
        // (futuramente isso vem do backend)
        const caixaFechado = true;

        if (caixaFechado) {
            document.body.classList.add('caixa-bloqueado');

            if (modalEl && window.bootstrap) {
                const modal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });

                modal.show();
            }
        }
    });
</script> -->
<!--  Verica caixa aberto -->
<script>
    console.log('🔍 Terminal recebido no PDV:', @json($terminal));
    console.log('💰 Caixa aberto recebido no PDV:', @json($caixaAberto));

    if (@json($caixaAberto) === null) {
        console.warn('⚠️ Nenhum caixa aberto encontrado para este terminal');
    } else {
        console.info('✅ Caixa aberto válido carregado no PDV');
    }
</script>

<!-- Modal de bloqueio do caixa -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('modalBloquearCaixa');

        // ⚠️ Estado real do caixa vindo do backend
        // const caixaFechado = @json(is_null($caixaAberto));
        const caixaFechado = @json($caixaAberto?->status !== 'aberto');

        console.log('🔍 Caixa fechado:', caixaFechado, 'Caixa:', @json($caixaAberto));

        if (caixaFechado) {
            document.body.classList.add('caixa-bloqueado');

            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });

                modal.show();
            } else {
                console.warn('⚠️ Modal não encontrado ou Bootstrap não carregado');
            }
        }
    });
</script>

<!-- caixas esquecidos abertos -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

    const listaDiv = document.getElementById('listaCaixasEsquecidos');

    fetch('/pdv/caixas-esquecidos')
        .then(response => response.json())
        .then(data => {

            // NÃO há caixas acima de 12h → PDV LIBERADO
            if (!data || data.length === 0) {
                listaDiv.style.display = 'none';
                document.body.classList.remove('caixa-bloqueado');
                return;
            }

            // HÁ caixas acima de 12h → PDV BLOQUEADO
            listaDiv.style.display = 'block';
            document.body.classList.add('caixa-bloqueado');

        })
        .catch(() => {
            // Em erro, por segurança, mantém bloqueado
            document.body.classList.add('caixa-bloqueado');
        });
    });
</script>

//  //oculta ou mostra a div de caixas esquecidos
<script>
    //oculta ou mostra a div de caixas esquecidos
    document.addEventListener('DOMContentLoaded', function () {

    const listaDiv = document.getElementById('listaCaixasEsquecidos');

    fetch('/pdv/caixas-esquecidos')
        .then(response => response.json())
        .then(data => {

            // Se não existe caixa acima de 12h → OCULTA
            if (!data || data.length === 0) {
                listaDiv.style.display = 'none';
                return;
            }

            // Existe caixa acima de 12h → MOSTRA
            listaDiv.style.display = 'block';

        })
        .catch(() => {
            // Em erro, por segurança, oculta
            listaDiv.style.display = 'none';
        });
    });
</script>

<script>
    const alerta = document.getElementById('alerta-lote');

    alerta.classList.add('d-none');
    alerta.textContent = '';
    alerta.className = 'fw-bold';

    if (data.lote_alerta?.tipo === 'vencido') {
        alerta.textContent = data.lote_alerta.mensagem;
        alerta.classList.add('text-danger');
        alerta.classList.remove('d-none');
    }

    if (data.lote_alerta?.tipo === 'a_vencer') {
        alerta.textContent = data.lote_alerta.mensagem;
        alerta.classList.add('text-warning');
        alerta.classList.remove('d-none');
    }

</script>

@endsection

<!-- Modals atahos -->
@include('pdv.modals.modal_cliente_pdv')
@include('pdv.modals.modal_produto_pdv')
@include('pdv.modals.modal_orcamento')


@vite([
    'resources/js/pdv/app.js',      {{-- BOOT --}}
    'resources/js/pdv/produto.js',
    'resources/js/pdv/carrinho.js',
    'resources/js/pdv/regras.js',
    'resources/js/pdv/orcamento.js',
    'resources/js/pdv/ui.js',
    'resources/js/pdv/atalhos.js',
])
<!-- Fim view completa do PDV -->
