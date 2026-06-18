<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Pagamento</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #222;
            background: #f4f4f4;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px; /* Espaço entre as duas vias na tela */
        }
        
        .ticket {
            background: #fff;
            width: 340px;
            padding: 20px;
            border: 1px solid #555;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            box-sizing: border-box;
            position: relative;
        }
        
        .txt-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        .titulo { font-size: 15px; margin-bottom: 6px; letter-spacing: 0.5px; }
        .subtitulo { font-size: 12px; font-weight: bold; margin-bottom: 12px; }
        
        hr { 
            border: 0; 
            border-top: 1px solid #bbb; 
            margin: 12px 0; 
        }
        
        .item-linha { 
            margin-bottom: 8px; 
            line-height: 1.4;
        }
        
        .bloco-assinatura { 
            margin-top: 20px; 
            text-align: center; 
        }
        
        .linha-assinatura { 
            width: 85%; 
            border-top: 1px solid #000; 
            margin: 25px auto 5px auto; 
        }

        /* Indicador de corte entre as vias */
        .corte-via {
            width: 340px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px dashed #999;
            margin: 10px 0;
            padding-top: 5px;
        }
        
        /* Botão fixo na tela para não atrapalhar o design */
        .btn-container {
            margin-bottom: 10px;
        }
        .btn-imprimir {
            background-color: #0d6efd;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            font-weight: normal;
            font-family: Arial, sans-serif;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-imprimir:hover { background-color: #0b5ed7; }

        @media print {
            body { 
                background: #fff; 
                padding: 0; 
                gap: 0;
            }
            .ticket { 
                border: 1px solid #000; /* Mantém a borda fina do modelo na impressão */
                box-shadow: none; 
                margin: 0 auto 20px auto; /* Margem para separar na folha */
                page-break-inside: avoid; /* Evita que uma via quebre no meio se o papel for curto */
            }
            .btn-container, .corte-via { 
                display: none; 
            }
        }
    </style>
</head>
<body>

    <!-- Botão de impressão isolado no topo -->
    <div class="btn-container">
        <button class="btn-imprimir" onclick="window.print()">Imprimir Comprovante</button>
    </div>

    <!-- ================= VIA 1: CLIENTE ================= -->
    <div class="ticket">
        <div class="txt-center titulo">DEPÓSITO DE MATERIAIS</div>
        <div class="txt-center subtitulo">COMPROVANTE DE PAGAMENTO CARTEIRA</div>
        <div class="txt-center item-linha" style="font-size: 11px; font-weight: bold; color: #555;">[ VIA DO CLIENTE ]</div>
        
        <hr>
        
        <div class="txt-center item-linha">Código: CC-<?php echo e(str_pad($pagamento->codigo_mov, 6, '0', STR_PAD_LEFT)); ?></div>
        <div class="txt-center item-linha text-uppercase">Cliente: <?php echo e($pagamento->cliente_nome); ?></div>
        <div class="txt-center item-linha">Data: <?php echo e(\Carbon\Carbon::parse($pagamento->created_at)->format('d/m/Y H:i')); ?></div>
        
        <hr>
        
        <div class="txt-center item-linha"><b>SALDO CARTEIRA - DETALHES</b></div>
        <div class="txt-center item-linha">Valor Pago: R$ <?php echo e(number_format($pagamento->valor, 2, ',', '.')); ?></div>
        <div class="txt-center item-linha text-uppercase">Forma Pgto: <?php echo e($pagamento->tipo); ?></div>
        <div class="txt-center item-linha text-uppercase">Via Meio: <?php echo e($pagamento->origem ?: 'DINHEIRO'); ?></div>
        <div class="txt-center item-linha">Saldo Posterior: R$ <?php echo e(number_format($pagamento->saldo_apos, 2, ',', '.')); ?></div>
        
        <hr>
        
        <div class="bloco-assinatura">
            <div class="txt-center item-linha">Assinatura do Cliente:</div>
            <div class="linha-assinatura"></div>
        </div>
    </div>

    <!-- Linha indicativa visual no navegador -->
    <div class="corte-via">tesoura aqui para separar as vias</div>

    <!-- ================= VIA 2: FECHAMENTO DE CAIXA ================= -->
    <div class="ticket">
        <div class="txt-center titulo">DEPÓSITO DE MATERIAIS</div>
        <div class="txt-center subtitulo">COMPROVANTE DE PAGAMENTO CARTEIRA</div>
        <div class="txt-center item-linha" style="font-size: 11px; font-weight: bold; color: #555;">[ VIA DO CAIXA / CONTROLE INTERNO ]</div>
        
        <hr>
        
        <div class="txt-center item-linha">Código: CC-<?php echo e(str_pad($pagamento->codigo_mov, 6, '0', STR_PAD_LEFT)); ?></div>
        <div class="txt-center item-linha text-uppercase">Cliente: <?php echo e($pagamento->cliente_nome); ?></div>
        <div class="txt-center item-linha">Data: <?php echo e(\Carbon\Carbon::parse($pagamento->created_at)->format('d/m/Y H:i')); ?></div>
        
        <hr>
        
        <div class="txt-center item-linha"><b>SALDO CARTEIRA - DETALHES</b></div>
        <div class="txt-center item-linha">Valor Pago: R$ <?php echo e(number_format($pagamento->valor, 2, ',', '.')); ?></div>
        <div class="txt-center item-linha text-uppercase">Forma Pgto: <?php echo e($pagamento->tipo); ?></div>
        <div class="txt-center item-linha text-uppercase">Via Meio: <?php echo e($pagamento->origem ?: 'DINHEIRO'); ?></div>
        <div class="txt-center item-linha">Saldo Posterior: R$ <?php echo e(number_format($pagamento->saldo_apos, 2, ',', '.')); ?></div>
        
        <hr>
        
        <div class="bloco-assinatura">
            <div class="txt-center item-linha">Autenticação do Caixa:</div>
            <div class="linha-assinatura"></div>
        </div>
    </div>


    <script>
        window.onload = function() {
            // 🚀 Dispara a impressão imediatamente (O Chrome vai enviar direto para a impressora padrão)
            window.print();
            
            // 🛡️ Fecha a aba do navegador logo após o envio para o spooler da impressora
            setTimeout(function() {
                window.close();
            }, 500); // Meio segundo de respiro para o hardware processar
        };
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/comprovantes/carteira.blade.php ENDPATH**/ ?>