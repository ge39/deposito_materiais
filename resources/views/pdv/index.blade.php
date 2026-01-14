<!-- View completa do PDV gerada conforme layout solicitado -->
<!-- Você pode ajustar rota, ids e classes conforme sua lógica -->

@extends('layouts.app')
@section('content') 

// Quando abrir o PDV
<?php  session(['terminal_id' => $terminal->id])?>;

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
        top: 40%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-25deg);
        font-size: 48px;
        font-weight: 900;
        border-radius:15px;
        color: rgba(255, 255, 255, 0.75);
        background:snow;
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
        left: 40%;
        transform: translate(-50%, calc(-50% + 120px));
        /* background: #ffffff; */
        color: var(--bordo);
        border-radius: 10px;
        border: 3px solid var(--bordo);
        padding: 14px 20px;
        width:160px;
        gap:30px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1;
    }
    .btn-sair-caixa {
        position: absolute;
        top: 50%;
        left: 60%;
        border-radius: 10px;
        transform: translate(-50%, calc(-50% + 120px));
        /* background: #ffffff; */
        color: var(--bordo);
        border: 3px solid var(--bordo);
        padding: 14px 20px;
        width:150px;
        gap:30px;
        font-size: 14px;
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
        /* font-size: 16px !important; */
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
        /* padding: 10px; */
        z-index: 1000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .linha-selecionada {
        background-color: #fff3cd !important;
        outline: 3px solid #ffc107 !important;;
    }

       /* Estilo para toda a div de informações do caixa */
    .caixa-info {
         font-size: 16px !important; /* aumenta a fonte e garante prioridade */
         gap: 50px;
    }

    /* Aumenta os títulos (strong) */
    .caixa-info strong {
          padding:10px;
        font-size: 16px !important; /* aumenta a fonte e garante prioridade */
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

<div class="container-fluid p-0">   
   <!-- OVERLAY -->
    <div id="modalBloquearCaixa" style="display: none;">
        <div class="carimbo-caixa">
            <span class="
                {{ $status === 'Aberto' ? 'status-aberto' : '' }}
                {{ $status === 'Fechado' ? 'status-fechado' : '' }}
                {{ $status === 'Pendente' ? 'status-pendente' : '' }}
                {{ $status === 'Inconsistente' ? 'status-inconsistente' : '' }}
            " style="padding: 5px 10px; border-radius: 5px; font-weight: bold">
              CAIXA {{ $status }}
            </span>
        </div>
        <!-- Lista de caixas esquecidos -->
        <div class="listaCaixasEsquecidos" id="listaCaixasEsquecidos">
            <ul></ul>
        </div>

       <div class="d-flex mb-5 justify-content-between p-3 w-100 bg-lavender">
            <button class="btn-abrir-caixa btn-primary btn-sm px-4"
                onclick="window.location.href='{{ route('caixa.abrir') }}'">
                ABRIR CAIXA
            </button>
            
            <button class="btn-sair-caixa btn-warning btn-sm px-4"
                onclick="window.location.href='{{ route('dashboard') }}'">
                SAIR
            </button>
        </div>

    </div>
    <!-- FIM OVERLAY -->

     <!-- Informações do status do Caixa -->
    <div class="container-fluid p-0" 
         style="background:#e6e6e6; margin-top:-18px; overflow-x:hidden;">
       
        <div class="caixa-info mb-3 p-0 border rounded shadow-sm bg-light d-flex justify-content-start align-items-center">
            <span><strong>Terminal: 00{{ $terminal->id }}</strong></span>
            <span><strong>Operador: {{ $operador }}</strong> </span>
             <span><strong>ID: {{ $operadorId }}</strong> </span>
            <span><strong>Status Caixa:  
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
            <input class="form-control fs-6 fw-bold text-center" type="datetime-local" value="{{ date('Y-m-d\TH:i') }}" readonly>
        </div>
        <div class="col-md-2 fw-bold mb-0">
             <!-- <label>ID</label> -->
            <input type="hidden" name="cliente_id">
            <label>Cliente</label>
            <input  class="form-control  fs-6 fw-bold text-center" name="nome">
            <input  type="hidden" name="cliente_id" value="{{  $clienteBalcao->id }}">
            <input  type="hidden" name="operador_id" value="{{  $operadorId }}">
            <input  type="hidden" name="terminal_id" value="{{  $terminal->id }}">
            <input  type="hidden" id="dataVenda"  type="datetime-local" value="{{ date('Y-m-d\TH:i') }}">
        </div>
         <div class="col-md-1 fw-bold mb-0">
            <label>Pessoa</label>
            <input class="form-control  fs-6 fw-bold text-center" name="pessoa" required readonly>
        </div>
        <div class="col-md-1 fw-bold mb-0">
            <label>Contato Local</label>
            <input class="form-control fs-6 fw-bold text-center" name="telefone" required >
        </div>
         <div class="col-md-5 fw-bold mb-0">
            <label>Endereço para entrega</label>
            <input id="endereco" class="form-control  fs-6 fw-bold text-center" name="endereco" required >
        </div>
        
    </div>

    <!-- CORPO PRINCIPAL -->
    <div class="row g-2 ">

        <!-- LADO ESQUERDO -->
        <div class="col-md-5 border border-1 p-1  " style="background:#3a3a3a; color:white; font: size 12px;">
            <div class="border p-1 mb-1 "style="font-size: 16px;">
                <label class="fw-bold ">Código Barras — (F3) Para pesquisar produtos</label>
                <input style="font-size: 16px !important;"
                    type="text" class="form-control bg-warning " id="codigo_barras" name="codigo_barras" autocomplete="off"
                    autofocus
                >
            </div>
                        <!-- descrição -->
            <div class="border p-1 mb-21">
                <label class="fw-bold">Descrição</label>
                <input style="font-size: 16px !important;"class="form-control form-control-sm fs-1 fw-bold " id="descricao" readonly>
                <small id="alerta-lote" class="fw-bold d-none"></small>

            </div>

           <div class="row mt-2 border ">
                <!-- Quantidade -->
                <div class="col-md-2 p-1">
                    <label for="quantidade" class="form-label">Quantidade</label>
                    <input id="quantidade" style="font-size: 16px !important;" class="form-control form-control-sm fw-bold"  type="number" name="quantidade"  value= "1" min="1" >
                    <small id="msgEstoque" class="text-danger fw-bold"></small>
                </div>

                <!-- Unidade -->
                <div class="col-md-2 p-1">
                    <label for="unidade" class="form-label">Unidade</label>
                    <input style="font-size: 16px !important;" type="text" name="unidade" id="unidade" readOnly class="form-control form-control-sm fs-1 fw-bold">
                </div>
                <!-- Preço venda -->
                <div class="col-md-3 p-1">  
                    <label for="preco_venda" class="form-label">Preço</label>
                    <input style="font-size: 16px !important;" id="preco_venda" name="preco_venda" class="form-control form-control-sm fw-bold bg-warning"readOnly>
                </div>
                 <!-- Quantidade -->   
                <div class="col-md-2 p-1">  
                    <label for="preco_venda" class="form-label">Qtd.Disp</label>
                    <input style="font-size: 16px !important;" class="form-control form-control-sm fw-bold"  name="qtd_disponivel" id="qtd_disponivel" type="text" min="1" step="1" readOnly>
                </div>
                <!-- Total Geral -->
                <div class="col-md-3 p-1">
                    <label for="total_geral" class="form-label">Sub Total</label>
                    <input style="font-size: 16px !important;" class="form-control form-control-sm fw-bold bg-warning"  name="total_geral" id="total_geral" type="text" readOnly>
                    
                </div>

            </div>
            <!-- CAMPO DE IMAGEM DO PRODUTO -->
            <div class="border bg-white mt-1" style="height: 250px; display:flex; align-items:center; justify-content:center;">
                <img id="produto-imagem" src="" alt="Imagem" style="max-width:100%; height:100%; object-fit:contain;">
            </div>
        </div>

        <!-- LADO DIREITO: LISTA DE ITENS -->
        <div id="pdv-area" class="pdv-area col-md-7" style="background:#3a3a3a; color:white;">
            <table class="table table-bordered table-sm bg-white" style="font-size: 18px !important;">
                <thead class="table-primary fw-bold text-center" style="font-size: 18px !important;">
                    <tr>
                        <td class="text-center" style="width:50px">Item</td>
                        <td class="text-center" style="width:90px">Lote</td>
                        <td class="text-center" style="width:200px;">Descrição</td>
                        <td class="text-center" style="width:50px">Qtde</td>
                        <td class="text-center" style="width:50px">Unid</td>
                        <td class="text-center" style="width:90px">Preço</td>
                        <td class="text-center" style="width:90px">SubTotal</td>
                        

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
            <button class="btn btn-warning fs-6 w-100 md-1 p-2" id="btnF2" >F2 - Cliente</button>
        </div>

        <div class="col">
            <button class="btn btn-danger fs-6 w-100 md-1 p-2">F3 - Produto</button>
        </div>

        <div class="col">
            <button  class="btn btn-primary fs-6 w-100 md-1 p-2">F4 - Orçamento</button>
        </div>

        <div class="col">
            <button class="btn btn-success fs-6 w-100 md-1 p-2">F5 - Inicio Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-warning fs-6 w-100 md-1 p-2">F6 - Final. Venda</button>
        </div>

        <div class="col">
            <button class="btn btn-danger fs-6  w-100 md-1 p-2">F8 Fecham. Caixa</button>
        </div> 
        <div class="col btn btn-dark w-100 md-1 p-1 fw-bold d-flex flex-column align-items-center justify-content-center">
            
            <div class="col btn btn-dark w-100 md-1 p-2 fw-bold d-flex flex-column align-items-center justify-content-center">
                <!-- Label que mostra o total na tela do PDV -->
                <label id="totalGeral" class="fw-bold text-warning" style="margin-top: -18px; margin-bottom: -15px; font-size: 32px !important;">R$ 0,00</label>
                <!-- Input escondido que armazena o valor numérico para JS -->
                <input type="hidden" id="inputTotalGeral" value="0,00">
            </div>


        </div>
    </div>
    
</div>


<!--  Verifica caixa aberto -->
<script>
    // console.log('🔍 Terminal recebido no PDV:', @json($terminal));
    // console.log('💰 Caixa aberto recebido no PDV:', @json($caixaAberto));

    // if (@json($caixaAberto) === null) {
        // console.warn('⚠️ Nenhum caixa aberto encontrado para este terminal');
    // } else {
        // console.info('✅ Caixa aberto válido carregado no PDV');
    // }
</script>

<!-- Modal de bloqueio do caixa -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('modalBloquearCaixa');

        // ⚠️ Estado real do caixa vindo do backend
        // const caixaFechado = @json(is_null($caixaAberto));
        const caixaFechado = @json($caixaAberto?->status !== 'aberto');

        // console.log('🔍 Caixa fechado:', caixaFechado, 'Caixa:', @json($caixaAberto));

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

<!--caixas esquecidos abertos acima de 12 horas-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/pdv/caixas-esquecidos')
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lista = document.getElementById('listaCaixasEsquecidos');
                    lista.innerHTML = ''; // limpa antes

                    data.forEach(caixa => {
                        const item = document.createElement('li');
                        item.style.padding = '2px 0';
                        item.style.borderBottom = '1px solid #fff';
                       item.textContent =
                        `Terminal: ${caixa.terminal_id} | ` +
                        `Caixa ID: ${caixa.id} | ` +
                        `Aberto em: ${caixa.data_abertura_br} | ` +
                        `Média horas pdv aberto: ${caixa.pdv_horas_aberto}h | ` +
                        // `Fechado em: ${caixa.data_fechamento_br ?? '-'} | ` +
                        `Operador: ${caixa.usuario.name}`;

                        lista.appendChild(item);
                    });

                    // Exibe o overlay
                     document.getElementById('modalBloquearCaixa').style.display = 'flex';
                    //  document.getElementById('listaCaixasEsquecidos').style.display = 'flex';
                }
            })
            .catch(error => console.error('Erro ao verificar caixas esquecidos:', error));
    });
    
</script>

<!-- oculta ou mostra a div de caixas esquecidos -->
<script>
    
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

<!-controle dos lotes vencidos-!>
<script>
    const data = @json($data ?? []);
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

<!--carrega o usuario padrao, "VENDA BALCAO"--!>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const clienteBalcao = @json($clienteBalcao);

        if (!clienteBalcao) return;

        document.querySelector('input[name="cliente_id"]').value = clienteBalcao.id ?? '';
        document.querySelector('input[name="nome"]').value       = clienteBalcao.nome ?? '';
        document.querySelector('input[name="pessoa"]').value     = clienteBalcao.tipo ?? '';
        document.querySelector('input[name="telefone"]').value   = clienteBalcao.telefone ?? '';
        document.querySelector('input[name="endereco"]').value   = clienteBalcao.endereco_entrega 
                                                                    ?? clienteBalcao.endereco 
                                                                    ?? '';

    });
</script>

<!-- Armazena total da venda globalmente e passa para view de finalizar -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnFinalizar = document.getElementById("btnF6"); // botão que abre o modal
        const totalInput = document.getElementById("inputTotalGeral");
        const modalTotal = document.getElementById("total-venda-modal"); // input do modal

        btnFinalizar.addEventListener("click", function() {
            if(totalInput && modalTotal){
                modalTotal.value = totalInput.value; // preenche o modal
            } else {
                console.warn("Elemento de total não encontrado!");
            }

            // abre o modal
            const modalEl = document.getElementById('modalFinalizar');
            if(modalEl && typeof bootstrap !== 'undefined'){
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    });

</script>


@endsection

<!-- Modals atahos -->
@include('pdv.modals.modal_cliente_pdv')
@include('pdv.modals.modal_produto_pdv')
@include('pdv.modals.modal_orcamento')
@include('pdv.modals.modal_finalizar')

@vite([
    'resources/js/pdv/app.js',
])

<!-- Fim view completa do PDV -->