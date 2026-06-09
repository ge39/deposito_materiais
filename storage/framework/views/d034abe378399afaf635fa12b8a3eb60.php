 <!-- window.CLIENTE_BALCAO = <?php echo json_encode($clienteBalcao, 15, 512) ?>; -->
<?php $__env->startSection('content'); ?>


<?php session(['terminal_id' => $terminal->id]); ?>


<style>
    /* estilo pra bloqueio de caixa */
    
        /* estilo pra bloqueio de caixa */
    :root {
        --bordo: #6b0f1a;
        --bordo-escuro: #4a0a12;
        --bordo-claro: #8b1c2b;
    }
    /* 🌟 MODIFICADO: Estados de Focus e Classe de Trava Visual */
       .btn-abrir-caixa:focus,
    .btn-abrir-caixa.foco-ativo-pdv {
        background: #198754 !important; /* Verde definitivo */
        color: #ffffff !important;
        border: 3px solid #ffffff !important;
        transform: translate(-50%, calc(-50% + 120px)) scale(1.05) !important;
        box-shadow: 0 0 15px rgba(25, 135, 84, 0.8) !important;
    }

    .btn-sair-caixa:focus,
    .btn-sair-caixa.foco-ativo-pdv {
        background: #dc3545 !important; /* Vermelho definitivo */
        color: #ffffff !important;
        border: 3px solid #ffffff !important;
        transform: translate(-50%, calc(-50% + 120px)) scale(1.05) !important;
        box-shadow: 0 0 15px rgba(220, 53, 69, 0.8) !important;
    }


    /* ===== OVERLAY CAIXA BLOQUEADO ===== */
    #modalBloquearCaixa {
        position: fixed;
        inset: 0;
        background: rgba(107, 15, 26, 0.42);
        z-index: 999999;
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
        top: 35%;
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
        top: 53%;
        left: 40%;
        transform: translate(-50%, calc(-50% + 120px));
        outline: none !important;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.25) !important;
        color: red;
        border-radius: 10px;
        padding: 14px 20px;
        width: 160px;
        gap: 30px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1;
        border: 3px solid transparent !important; /* Remove a borda preta nativa */
        transition: all 0.15s ease-in-out;
    }

    .btn-sair-caixa {
        position: absolute;
        top: 53%;
        left: 60%;
        border-radius: 10px;
        transform: translate(-50%, calc(-50% + 120px));
        outline: none !important;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.25) !important;
        color: red;
        padding: 14px 20px;
        width: 150px;
        gap: 30px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1;
        border: 3px solid transparent !important; /* Remove a borda preta nativa */
        transition: all 0.15s ease-in-out;
    }

    /* 🌟 ADICIONADO: ESTADOS DE FOCUS EM CIMA DO SEU PADRÃO (Para operação sem mouse) */
    .btn-abrir-caixa:focus {
        background: #198754 !important; /* Acende Verde ao focar pelo teclado */
        color: #ffffff !important;
        border-color: #ffffff !important;
        transform: translate(-50%, calc(-50% + 120px)) scale(1.1) !important; /* Mantém sua posição e cresce de leve */
        box-shadow: 0 0 15px rgba(25, 135, 84, 0.8) !important;
    }

    .btn-sair-caixa:focus {
        background: #dc3545 !important; /* Acende Vermelho ao focar pelo teclado */
        color: #ffffff !important;
        border-color: #ffffff !important;
        transform: translate(-50%, calc(-50% + 120px)) scale(1.1) !important; /* Mantém sua posição e cresce de leve */
        box-shadow: 0 0 15px rgba(220, 53, 69, 0.8) !important;
    }

    /* Mantém seus hovers originais ativos se alguém usar o mouse */
    .btn-abrir-caixa:hover {
        background: #60a7f2;
        color:snow;
    }
    .btn-sair-caixa:hover {
        background: #60a7f2;
        color:snow;
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
  
    .pdv-area {
    position: relative; /* cria o contexto do PDV */
    }

    /* 
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
    /* }  */
    */

        .linha-carrinho.selecionada {
        background-color: #dbeafe !important;
        outline: 3px solid #2563eb;
    }

    /* 🎯 CORRIGIDO: Removido o seletor duplicado e quebrado com vírgula */
    #acoes-carrinho {
        display: block;
        gap: 10px;
    }
  
    .pdv-area {
        position: relative; /* cria o contexto do PDV */
    }

    .acoes-carrinho {
        position: absolute; /* flutua DENTRO do PDV */
        width: 98.8%;
        display: none;
        cursor: move;
        background: #ffffff;
        border: 2px solid #ced4da;
        border-radius: 10px;
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
        margin: 570px 20px 0;
        list-style: none;
        padding: 10px; 
        font-size:18px; 
        font-weight:bold;
        color:snow;
        background-color: red;
       
    }
</style>

<div class="container-fluid p-0">   
     
    <!-- =======================================================================
     🎯 CONTROLADOR HIERÁRQUICO DE OVERLAYS DO PDV
     ======================================================================= -->

    <?php if(isset($bloquearPorTempo) && $bloquearPorTempo): ?>

        <!-- 🚨 1️⃣ MÓDULO EXCLUSIVO: BLOQUEIO POR TURNO EXPIRADO (+12H) -->
        <div id="modalBloquearCaixa" style="display: block;">
            <div class="carimbo-caixa">
                <span class="
                    <?php echo e($status === 'Aberto' ? 'status-aberto' : ''); ?>

                    <?php echo e($status === 'Fechado' ? 'status-fechado' : ''); ?>

                    <?php echo e($status === 'Pendente' ? 'status-pendente' : ''); ?>

                    <?php echo e($status === 'Inconsistente' ? 'status-inconsistente' : ''); ?>

                " style="padding: 5px 10px; border-radius: 5px; font-size: 30px; font-weight: bold; text-align: center; display: inline-block">
                    CAIXA BLOQUEADO
                </span>
                <p style="color:red; font-size: 24px;text-align: center; display: inline-column"> Caixa: <?php echo e($caixa->id); ?></p>
                 <p style="color:gray; font-size: 14px;text-align: center; display: inline-column"> Operador: <?php echo e($operador); ?></p>
            </div>

            <div class="listaCaixasEsquecidos list-group text-center" id="listaCaixasEsquecidos">
                <ul></ul>
            </div>

            <button id="btnFecharCaixaImediato" 
                    class="btn-abrir-caixa"
                    autofocus
                    onclick="window.location.href='/fechamento_caixa/fechamento/<?php echo e($caixa->id); ?>'">
                FECHAR CAIXA
            </button>
            
            <button id="btnSairPdvImediato" 
                    class="btn-sair-caixa"
                    onclick="window.location.href='<?php echo e(route('dashboard')); ?>'">
                SAIR
            </button>
        </div>
        <!-- Script de Teclado exclusivo para travar o Turno Expirado -->
        <script>
            window.PDV_BLOQUEADO = true;
            window.CAIXA_ID      = <?php echo json_encode($caixa->id ?? null, 15, 512) ?>;

            window.addEventListener('keydown', function (event) {
                const atalhosBloqueados = [
                    'F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8', 'F9', 'F10', 'F11', 'F12',
                    'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter', 'Escape', 'Tab'
                ];

                if (atalhosBloqueados.includes(event.key)) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    
                    const btnFechar = document.getElementById('btnFecharCaixaImediato');
                    const btnSair = document.getElementById('btnSairPdvImediato');

                    if (event.key === 'Tab') {
                        if (document.activeElement === btnSair) {
                            btnFechar?.focus();
                        } else {
                            btnSair?.focus();
                        }
                    }

                    if (event.key === 'Enter') {
                        document.activeElement?.click();
                    }
                    return false;
                }
            }, true);

            // Fixador de Foco Inicial do Turno Expirado
            setTimeout(() => {
                document.getElementById('btnFecharCaixaImediato')?.focus();
            }, 100);
        </script>
    <?php else: ?>
        <!-- 💰 2️⃣ MÓDULO EXCLUSIVO: OPERAÇÃO NORMAL OU ALERTA DE SANGRIA -->
        <!-- Modal Alerta Carrinho Vazio (Bootstrap) -->
        <div class="modal fade" id="modalCarrinhoVazio" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white border-0 py-3">
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                            ⚠️ Atenção operacional
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <p class="fs-5 text-secondary mb-0">
                            Não é possível ir para o fechamento. <br>
                            <strong>O carrinho atual está vazio!</strong>
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Entendi</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal verificar sangria (Bootstrap) -->
        <div class="modal fade" id="modalSangria" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header <?php if($bloquearPDV): ?> bg-danger text-white <?php else: ?> bg-warning text-dark <?php endif; ?>">
                        <h5 class="modal-title fw-bold">
                            <?php if($bloquearPDV): ?>
                                🚫 BLOQUEIO DE CAIXA
                            <?php else: ?>
                                ⚠️ LIMITE DE SANGRIA ATINGIDO
                            <?php endif; ?>
                        </h5>
                    </div>

                    <div class="modal-body text-center py-4">
                        <h4 class="fw-bold mb-3">
                            Saldo Atual:
                            <span class="text-dark">R$ <?php echo e(number_format($saldoAtual, 2, ',', '.')); ?></span>
                        </h4>

                        <p class="fs-5 mb-2">
                            Limite configurado: <strong>R$ <?php echo e(number_format($limiteSangria, 2, ',', '.')); ?></strong>
                        </p>

                        <?php if($bloquearPDV): ?>
                            <div class="alert alert-danger fw-bold fs-5 shadow-sm">
                                PDV BLOQUEADO<br>Realize sangria para continuar as vendas.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning fw-bold fs-5 shadow-sm">
                                Recomendado realizar sangria.
                            </div>
                        <?php endif; ?>

                        <hr>
                        <h3 class="fw-bold text-primary">💰 Valor sugerido para sangria:</h3>
                        <h2 class="display-6 fw-bold text-success">R$ <?php echo e(number_format($saldoAtual ?? 0, 2, ',', '.')); ?></h2>
                        <p class="text-muted">Oriente a operadora a retirar este valor do caixa.</p>
                    </div>

                    <div class="modal-footer justify-content-between">
                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('caixa.sangria.form', $caixa->id)); ?>" class="btn btn-success px-4 fw-bold">
                                ✅ Efetuar Sangria
                            </a>
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" onclick="window.PDV_BLOQUEADO = false; 
                                    window.caixaBloqueado = false;">
                                ❌ Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script de Inicialização da Sangria do Bootstrap -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
            var deveAvisar = <?php echo e($avisarSangria ? 'true' : 'false'); ?>;
            var deveBloquear = <?php echo e($bloquearPDV ? 'true' : 'false'); ?>;
            
            if (deveAvisar || deveBloquear) {
                var modalElement = document.getElementById('modalSangria');

                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: true
                    });

                    modal.show();
                }
            }
        });
        </script>

    <?php endif; ?>

    <!-- FIM OVERLAY -->
     <!-- Informações do status do Caixa -->
    <div class="container-fluid p-0" 
         style="background:#e6e6e6; margin-top:-40px; overflow-x:hidden">
       
        <div class="caixa-info mb-3 px-3 py-2 border rounded shadow-sm bg-light d-flex align-items-center">
             <span class="me-3">
                    <strong>Terminal:</strong> <?php echo e(str_pad($terminal->id, 2, '0', STR_PAD_LEFT)); ?>      
            </span>

            <!-- Botão invisível para disparar o modal de forma segura sem quebrar o JS -->
            <button id="btnGatilhoModalCaixa" type="button" class="d-none" data-bs-toggle="modal" data-bs-target="#modalBloquearCaixa"></button>


            <span><strong>Operador: <?php echo e($operador); ?></strong> </span>
            <span><strong>ID: <?php echo e($operadorId); ?></strong> </span>
            <span><strong>Caixa: <?php echo e($caixa_id); ?></strong> </span>
            <input type="hidden" name="caixa_id" value="<?php echo e($caixa_id); ?>">
            <span><strong>Status Caixa:  
                <span class="
                    <?php echo e($status === 'Aberto' ? 'status-aberto' : ''); ?>

                    <?php echo e($status === 'Fechado' ? 'status-fechado' : ''); ?>

                    <?php echo e($status === 'Pendente' ? 'status-pendente' : ''); ?>

                    <?php echo e($status === 'Inconsistente' ? 'status-inconsistente' : ''); ?>

                "><strong>
                    <?php echo e($status); ?>


                    <?php if($caixa): ?>
                       <?php echo e("em " . $caixaAberto->data_abertura->format('d/m/Y H:i')); ?>

                    <?php else: ?>
                        <span class="text-danger">Caixa não aberto</span>
                    <?php endif; ?>
                                        
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
            <input class="form-control fs-6 fw-bold text-center" type="datetime-local" value="<?php echo e(date('Y-m-d\TH:i')); ?>" readonly>
        </div>

        <div class="col-md-2 fw-bold mb-0">
             <!-- <label>ID</label> -->
               <!-- 👇 EXPORTAÇÃO PARA JS -->
              
            <input type="hidden" id="cliente_id" name="cliente_id" value="<?php echo e($clienteBalcao->id); ?>">
            <input  type="hidden" id="operador_id" name="operador_id" value="<?php echo e($operadorId); ?>">
            <input  type="hidden" id="caixa_id" name="caixa_id" value="<?php echo e($terminal->id); ?>">
            <input  type="hidden" id="dataVenda"  type="datetime-local" value="<?php echo e(date('Y-m-d\TH:i')); ?>">

            <label>Cliente</label>
            <input  type ="text" class="form-control  fs-6 fw-bold text-center" name = "nome" 
            value ="<?php echo e($clienteBalcao->nome); ?>">

        </div>
        
         <div class="col-md-1 fw-bold mb-0">
            <label>Pessoa</label>
            <input class="form-control  fs-6 fw-bold text-center" name="pessoa" 
             value="<?php echo e($clienteBalcao->tipo); ?>" required readonly>
        </div>

        <div class="col-md-2 fw-bold mb-0">
            <label>Contato Local</label>
            <input class="form-control fs-6 fw-bold text-center" name="telefone" 
            value="<?php echo e($clienteBalcao->telefone); ?>" required >
        </div>

        <div class="col-md-5 fw-bold mb-0">
            <label>Endereço para entrega</label>
            <input id="endereco" class="form-control  fs-6 fw-bold text-center" name="endereco" 
            value="<?php echo e($clienteBalcao->endereco); ?>" required >
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

           <!-- CAMPO DE IMAGEM DO PRODUTO (PREENCHIMENTO INTELIGENTE 200PX) -->
            <div class="border bg-white mt-1" style="height: 445px; width: 100%; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                
                <!-- 1️⃣ Camada de Fundo: Desfoca e preenche as laterais vazias se a foto for proporcionalmente diferente -->
                <img id="produto-imagem-bg" src="" alt="" style="position: absolute; width: 100%; height: 100%; object-fit: cover; filter: blur(15px) brightness(0.95); transform: scale(1.1); z-index: 1;">
                
                <!-- 2️⃣ Camada Principal: Mostra o produto centralizado, 100% visível, sem cortes e sem distorção -->
                <img id="produto-imagem" src="" alt="Imagem" style="max-width: 100%; max-height: 100%; object-fit: contain; position: relative; z-index: 2;">

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
                        <td class="text-center" style="width:90px">Preço</td>
                        <td class="text-center" style="width:50px">Qtde</td>
                        <td class="text-center" style="width:50px">Unid</td>
                        
                        <td class="text-center" style="width:90px">SubTotal</td>
                        

                    </tr>
                </thead>  
                <tbody id="lista-itens" ></tbody> 
            </table>

             <!-- BOTÕES DE AÇÃO DO ITEM SELECIONADO -->
            <div id="acoes-carrinho" class="acoes-carrinho mt-2 bg-dark d-none">
                <!-- 🚀 O flexbox garante o nome na esquerda e os botões agrupados na direita -->
                <div class="d-flex align-items-center justify-content-between px-3 w-100" style="height: 60px;">
                    
                    <!-- 🎯 RECUPERADO: Este elemento receberá a descrição dinamicamente via JS ao clicar na linha -->
                    <span id="modalNomeProduto" class="fw-bold text-light text-start me-auto m-0" style="font-size: 1.25rem;"></span>
                    
                    <!-- Bloco dos botões alinhados à direita -->
                    <div class="d-flex gap-2">
                        <button id="btnDiminuir" class="btn btn-warning btn-lg">− Diminuir</button>
                        <button id="btnRemover" class="btn btn-danger btn-lg">Remover</button>
                        <button id="btnOcultar" class="btn btn-secondary btn-lg">Ocultar</button>
                    </div>
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
            <button class="btn btn-danger fs-6  w-100 md-1 p-2">F10 Fecham. Caixa</button>
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
    <!-- 🎨 SEU MODAL DE ADVERTÊNCIA ATUAL -->
    <div class="modal fade" id="modalPdvRemover" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 25px; background-color: #fff9f9;">
                <div class="modal-body p-5">
                    <h5 class="fw-bold mb-4 text-dark" style="font-size: 1.25rem;">
                        ⚠️ ATENÇÃO: ADVERTÊNCIA!
                    </h5>
                    <p class="text-secondary mb-5" style="font-size: 1.05rem; line-height: 1.6;">
                        Você está prestes a REMOVER O PRODUTO:<br>
                        
                        <!-- 🎯 AJUSTE AQUI: O ID foi tornado único e alinhado à esquerda como você prefere -->
                        <span id="modalNomeProdutoRemover" class="fw-bold text-dark text-start d-block my-2" style="font-size: 1.15rem;"></span>
                        
                        Deseja confirmar?
                    </p>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="button" class="btn fw-bold px-4 rounded-pill" id="btnModalCancelar" data-bs-dismiss="modal" style="background-color: #ffd8d8; color: #5a2020;">Cancelar</button>
                        <button type="button" class="btn text-white fw-bold px-4 rounded-pill" id="btnModalConfirmar" data-bs-dismiss="modal" style="background-color: #804040; box-shadow: 0 4px 10px rgba(128, 64, 64, 0.3);">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BLINDAGEM DE INICIALIZAÇÃO: TRAVA CARTEIRA PARA VENDA BALCÃO -->
<script>
    // ========================================================
    // BLINDAGEM DE INICIALIZAÇÃO: TRAVA CARTEIRA PARA VENDA BALCÃO
    // ========================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Busca o elemento onde fica o nome do cliente na tela inicial do PDV
        // Nota: Substitua pelo ID ou Classe real do elemento que exibe "VENDA BALCAO" na sua tela
        const nomeClienteInicial = document.getElementById('nome-cliente-modal') || document.querySelector('.cliente-nome');
        const textoCliente = nomeClienteInicial ? nomeClienteInicial.textContent.toUpperCase() : '';

        // Se o PDV iniciar com a Venda Balcão pré-carregada, passa o cadeado na carteira na hora!
        if (textoCliente.includes('VENDA BALCAO')) {
            const inputCarteira = document.querySelector('.pagamento-modal[data-forma="carteira"]');
            if (inputCarteira) {
                inputCarteira.value = '';             // Remove qualquer valor injetado automaticamente
                inputCarteira.disabled = true;        // Bloqueia fisicamente o campo
                inputCarteira.tabIndex = -1;          // Remove da navegação por TAB
                inputCarteira.placeholder = 'Bloqueado';
            }
        }
    });

</script>
<!-- caixas esquecidos abertos acima de 12 horas -->
<script>
    document.addEventListener('DOMContentLoaded', async function () {

        const listaDiv = document.getElementById('listaCaixasEsquecidos');
        const modalEl  = document.getElementById('modalBloquearCaixa');

        if (!listaDiv || !modalEl) return;

        try {
            // 🎯 CAPTURA DINÂMICA: O Laravel injeta o terminal_id do operador logado em tempo real
            // Se o operador mudar de máquina (ex: Terminal 5), o Blade se atualiza sozinho
           // Procure por esta linha perto do início do script:
            const terminalAtualId = parseInt("<?php echo e(session('terminal_id') ?? cookie('terminal_id') ?? ''); ?>") || 10;

            // E APAGUE ou comente o bloco de "if" que vinha logo abaixo bloqueando o código:
             
            // if (!terminalAtualId || terminalAtualId === 0) {
            //     console.warn("Nenhum terminal_id associado ao usuário logado.");
            //     return;
            // } 
           
                // console.log(terminalAtualId );

            const response = await fetch('/pdv/caixas-esquecidos');

            if (!response.ok) throw new Error('Erro HTTP');

            const data = await response.json();
            const todosCaixas = Array.isArray(data) ? data : (data.data ?? []);

            // 🎯 FILTRO DINÂMICO: Compara o banco com o terminal logado na sessão
            const caixasDoTerminal = todosCaixas.filter(caixa => {
                return parseInt(caixa.terminal_id) === terminalAtualId;
            });

            // Se o terminal logado não tiver caixas antigos pendentes, encerra silenciosamente
            if (caixasDoTerminal.length === 0) {
                listaDiv.style.display = 'none';
                return;
            }

            listaDiv.innerHTML = '';
            listaDiv.style.display = 'block';

            caixasDoTerminal.forEach(caixa => {
                const item = document.createElement('li');

                // Mantém a exibição do terminal_id numérico que você definiu
                item.textContent =
                    `Terminal: ${caixa.terminal_id} | ` +
                    `Caixa ID: ${caixa.id} | ` +
                    `Aberto em: ${caixa.data_abertura_br} | ` +
                    `Média horas pdv aberto: ${caixa.pdv_horas_aberto}h | ` +
                    `Operador: ${caixa.nome_operador}`;

                listaDiv.appendChild(item);
            });

            // 🔥 Exibe o modal do Bootstrap na tela do operador logado
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: 'static',
                keyboard: false
            });

            modal.show();

        } catch (err) {
            console.error("Erro ao buscar caixas:", err);
            listaDiv.style.display = 'none';
        }
    });
</script>

<!-- Controle dos lotes vencidos -->
<script>
    const dataLote = <?php echo json_encode($data ?? [], 15, 512) ?>;
    const alertaLote = document.getElementById('alerta-lote');

    if (alertaLote) {
        alertaLote.classList.add('d-none');
        alertaLote.textContent = '';
        alertaLote.className = 'fw-bold';

        if (dataLote.lote_alerta?.tipo === 'vencido') {
            alertaLote.textContent = dataLote.lote_alerta.mensagem;
            alertaLote.classList.add('text-danger');
            alertaLote.classList.remove('d-none');
        }

        if (dataLote.lote_alerta?.tipo === 'a_vencer') {
            alertaLote.textContent = dataLote.lote_alerta.mensagem;
            alertaLote.classList.add('text-warning');
            alertaLote.classList.remove('d-none');
        }
    }
</script>

<!-- Armazena total da venda globalmente e passa para view de finalizar -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnFinalizar = document.getElementById("btnF6");
        const totalInput = document.getElementById("inputTotalGeral");
        const modalTotal = document.getElementById("total-venda-modal");
        const modalEl = document.getElementById('modalFinalizar');

        if (btnFinalizar) {
            btnFinalizar.addEventListener("click", async function() {
                const valorTexto = totalInput ? totalInput.value.replace('R$', '').replace(/\s/g, '').replace('.', '').replace(',', '.') : '0';
                const totalVenda = Number(valorTexto || 0);

                if (totalVenda <= 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Atenção operational', 'Não é possível ir para o fechamento. O carrinho atual está vazio!', 'warning');
                    } else {
                        alert('Atenção operacional: O carrinho atual está vazio!');
                    }
                    return;
                }

                const inputCliente = document.querySelector('input[name="cliente_id"]') || document.getElementById('input-cliente-id');
                const clienteId = inputCliente?.value;

                if (clienteId) {
                    try {
                        const response = await fetch(`/clientes/${clienteId}/financeiro`);
                        const data = await response.json();

                        if (data.success && window.cliente) {
                            window.cliente.saldo = Number(data.saldo || 0);
                            window.cliente.limite = Number(data.limite || 0);
                            window.cliente.status = data.status || 'bloqueado';

                            const saldoEl = document.getElementById('saldo-cliente-modal');
                            if (saldoEl) {
                                const statusBadge = data.status === 'ativo'
                                    ? '<span class="badge bg-success">Ativo</span>'
                                    : '<span class="badge bg-danger">Bloqueado</span>';

                                saldoEl.innerHTML = `
                                    Status: ${statusBadge}<br>
                                    Saldo: R$ ${Number(data.saldo).toFixed(2).replace('.', ',')}<br>
                                    Limite: R$ ${Number(data.limite).toFixed(2).replace('.', ',')}
                                `;
                            }
                        }
                    } catch (error) {
                        console.error('Erro ao atualizar financeiro do cliente:', error);
                    }
                }

                if (totalInput && modalTotal) {
                    modalTotal.value = totalInput.value;
                    if(modalTotal.tagName !== 'INPUT') {
                        modalTotal.textContent = totalInput.value;
                    }
                }

                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();

                    setTimeout(() => {
                        const inputsPagamento = document.querySelectorAll('.input-pagamento');
                        if (inputsPagamento && inputsPagamento.length > 0) {
                            inputsPagamento[0].focus();
                        }
                    }, 300);
                }
            });
        }
    });
</script>

<!-- Escuta a digitação para atualizar o botão de faturamento (Protegido contra Loops) -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Flag de controle para evitar execução infinita durante o limparPDV()
        window.PDV_EM_LIMPEZA = false; 
    
        function verificarRestanteSimples() {
            // Se a limpeza do PDV estiver rodando, ignora a re-validação dos inputs
            if (window.PDV_EM_LIMPEZA === true) return;

            let btnFinalizar = null;
            document.querySelectorAll('button').forEach(btn => {
                if (btn.innerText && btn.innerText.trim() === 'Finalizar Venda') {
                    btnFinalizar = btn;
                }
            });

            if (!btnFinalizar) return;

            let textoRestante = "";
            document.querySelectorAll('div, p, span, h5, h4, td').forEach(el => {
                if (el.innerText && el.innerText.includes('Restante:')) {
                    textoRestante = el.innerText.trim();
                }
            });

            if (textoRestante.includes('R$ 0,00')) {
                btnFinalizar.disabled = false;
                btnFinalizar.style.opacity = '1';
                btnFinalizar.style.backgroundColor = '#28a745'; 
                btnFinalizar.style.borderColor = '#28a745';
                btnFinalizar.style.cursor = 'pointer';
                btnFinalizar.style.pointerEvents = 'auto';
            } else {
                btnFinalizar.disabled = true;
                btnFinalizar.style.opacity = '0.4';
                btnFinalizar.style.backgroundColor = '#6c757d'; 
                btnFinalizar.style.borderColor = '#6c757d';
                btnFinalizar.style.cursor = 'not-allowed';
                btnFinalizar.style.pointerEvents = 'none';
            }
        }

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keyup', verificarRestanteSimples);
            input.addEventListener('change', verificarRestanteSimples);
            input.addEventListener('input', verificarRestanteSimples);
        });

        setTimeout(verificarRestanteSimples, 500);
    });
</script>


<!-- Modals atahos -->
<?php echo $__env->make('pdv.modals.modal_cliente_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_produto_pdv', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_orcamento', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('pdv.modals.modal_finalizar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<!-- =======================================================================
1️⃣ CENTRALIZAÇÃO ÚNICA DE VARIÁVEIS GLOBAIS DO LARAVEL (RODA IMEDIATAMENTE)
======================================================================= -->
<!-- <script>
    // Injeta os dados mestres do banco para o ecossistema Javascript ler
    window.CLIENTE_BALCAO      = <?php echo json_encode($clienteBalcao ?? null, 15, 512) ?>; 
    window.PDV_BLOQUEADO       = <?php echo json_encode($caixaBloqueado ?? false, 15, 512) ?>;
    window.CAIXA_ID            = <?php echo json_encode($caixa->id ?? null, 15, 512) ?>;
    window.CAIXA_POSSUI_VENDAS = <?php echo json_encode($caixa->possui_vendas ?? false, 15, 512) ?>;
    
    // Inicializadores obrigatórios de memória de escopo global
    window.carrinho            = [];
    window.orcamentoAtualId    = null; 

    window.PDV = {
        caixa_id: <?php echo e($caixa->id ?? 'null'); ?>,
        funcionario_id: <?php echo e(auth()->id()); ?>,
        dataVenda: "<?php echo e(now()); ?>"
    };
</script> -->

<!-- ⚡ INJECTOR AUTOMÁTICO REVISADO: CARREGA O CLIENTE BALCÃO AO FINALIZAR QUALQUER VENDA -->
<script>
    // 1️⃣ O Laravel extrai o registro do banco de dados e entrega pronto para a memória do JS
    window.CLIENTE_BALCAO = <?php echo json_encode($clienteBalcao, 15, 512) ?>;

    // 2️⃣ FUNÇÃO MESTRE: Aplica os dados do banco diretamente nas propriedades .value dos inputs
    window.forcarInjecaoClienteBalcao = function() {
        if (!window.CLIENTE_BALCAO) {
            console.warn("Aviso: window.CLIENTE_BALCAO não foi renderizado pelo Laravel.");
            return;
        }

        console.log("⚡ Executando injeção obrigatória pós-venda: Restaurando Cliente Balcão do Banco...");

        // Captura os inputs ocultos e visíveis utilizando os IDs e Names exatos do seu HTML
        const inputId       = document.getElementById('cliente_id');
        const inputNome     = document.querySelector('input[name="nome"]') || document.querySelector('input[name*="nome"]');
        const inputPessoa   = document.querySelector('input[name="pessoa"]');
        const inputTelefone = document.querySelector('input[name="telefone"]');
        const inputEndereco = document.getElementById('endereco');

        // Popular os inputs fisicamente com as strings extraídas do banco
        if (inputId)       inputId.value       = window.CLIENTE_BALCAO.id;       // ID extraído do banco (ex: 6)
        if (inputNome)     inputNome.value     = window.CLIENTE_BALCAO.nome;     // "VENDA BALCAO"
        if (inputPessoa)   inputPessoa.value   = window.CLIENTE_BALCAO.tipo;     // Tipo extraído do banco (ex: "fisica")
        if (inputTelefone) inputTelefone.value = window.CLIENTE_BALCAO.telefone; // Telefone extraído do banco
        if (inputEndereco) inputEndereco.value = window.CLIENTE_BALCAO.endereco; // Endereço extraído do banco

        // 3️⃣ Limpezas complementares da tela (Carrinho e Totais)
        const tbody = document.getElementById('lista-itens') 
                   || document.getElementById('lista-produtos') 
                   || document.querySelector('#tabelaItensPDV tbody');
        if (tbody) tbody.innerHTML = '';

        const totalGeral = document.getElementById('total_geral') || document.getElementById('totalGeral') || document.getElementById('inputTotalGeral');
        if (totalGeral) {
            if (totalGeral.tagName === 'INPUT') totalGeral.value = 'R$ 0,00';
            else totalGeral.textContent = 'R$ 0,00';
        }

        // Devolve o foco imediatamente para o leitor de código de barras
        setTimeout(() => {
            document.getElementById('codigo_barras')?.focus();
        }, 100);
    };

    // 4️⃣ GATILHO VIA ASSINATURA GLOBAL: Sobrescreve a função chamadora para garantir a execução
    window.limparPDV = function() {
        window.forcarInjecaoClienteBalcao();
    };

    // 5️⃣ GATILHO VIA MONITOR DE REQUISIÇÕES: Captura o sucesso do faturamento e vendas por segurança redundante
    (function() {
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            const response = await originalFetch.apply(this, args);
            const url = args[0];

            // Se a rota chamada foi a de faturar orçamento ou finalizar venda
            if (typeof url === 'string' && (url.includes('/pdv/faturar') || url.includes('/vendas/finalizar') || url.includes('/vendas'))) {
                // Aguarda as promessas resolverem e injeta os dados do banco na marra
                setTimeout(() => {
                    window.forcarInjecaoClienteBalcao();
                }, 150);
            }
            return response;
        };
    })();
</script>

<!-- 🎯 CARREGAMENTO SEQUENCIAL DOS ARQUIVOS (Módulos Base) -->
<script src="<?php echo e(asset('js/pdv/pdv_storage.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/carrinho.js')); ?>" defer></script>

<!-- Scripts de Regras e Comportamento do Sistema -->
<script src="<?php echo e(asset('js/pdv/app.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/regras.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/produto.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/orcamento.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/ui.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/pdv.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/form-masks.js')); ?>" defer></script>
<script src="<?php echo e(asset('js/pdv/atalhos.js')); ?>" defer></script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app2', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/index.blade.php ENDPATH**/ ?>