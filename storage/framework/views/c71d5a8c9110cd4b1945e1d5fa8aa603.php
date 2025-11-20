<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cupom de Devolução - Duas Vias</title>
    <style>
        @page {
            size: 215mm 315mm;
            margin: 4mm;
        }

        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 0;
            display: flex;
            justify-content: center;
            background-color: #fff;
            color: #000;
        }

        .sheet {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0;
            box-sizing: border-box;
        }

        .ticket {
            width: calc(100% - 8mm);
            background: #fff;
            border: 2px dashed #444;
            border-radius: 8px;
            padding: 8px;
            box-sizing: border-box;
            page-break-inside: avoid;
            margin: 0 auto;
        }

        .ticket-label {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header h1 {
            font-size: 1.1rem;
            margin: 0;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
        }

        hr {
            border: 1px dashed #444;
            margin: 5px 0;
        }

        .cliente, .devolucao {
            margin-top: 6px;
        }

        .cliente p, .devolucao p {
            margin: 3px 0;
        }

        .devolucao {
            background-color: #fff8f5;
            padding: 5px;
            border: 1px solid #e0a19a;
            border-radius: 5px;
        }

        .observacao {
            margin-top: 5px;
            font-size: 11px;
            color: #d9534f;
            font-weight: bold;
            padding: 3px;
            background-color: #fff0f0;
            border-radius: 4px;
        }

        .customer-sign, .signature {
            margin-top: 12px;
            text-align: center;
        }

        .small-text {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>
<?php
    // Seleciona a empresa/filial correta
    $empresa = $empresa ?? \App\Models\Empresa::first();
?>

<div class="sheet">

    <!-- VIA Loja -->
    <div class="ticket">
        <div class="ticket-label">VIA LOJA</div>

        <div class="header">
            <h1><?php echo e($empresa->nome ?? '---'); ?></h1>
            <p><strong>Data:</strong> <?php echo e(\Carbon\Carbon::now()->format('d/m/Y')); ?></p>
            <p><?php echo e($empresa->endereco ?? '---'); ?> <?php echo e($empresa->numero ?? ''); ?> <?php echo e($empresa->complemento ?? ''); ?></p>
            <p><?php echo e($empresa->bairro ?? '---'); ?> - <?php echo e($empresa->cidade ?? '---'); ?> - <?php echo e($empresa->estado ?? ''); ?> - CEP <?php echo e($empresa->cep ?? '---'); ?></p>
            <p>Tel: <?php echo e($empresa->telefone ?? '---'); ?> | Email: <?php echo e($empresa->email ?? '---'); ?></p>
            <hr>
        </div>
        <div class="cliente">
            <h3>Cliente:</h3>
            <p><strong>Nome:</strong> <?php echo e($cliente->nome ?? '---'); ?></p>
            <p><strong>CPF/CNPJ:</strong> <?php echo e($cliente->cpf ?? $cliente->cnpj ?? '---'); ?></p>
            <p><strong>Endereço:</strong> <?php echo e($cliente->endereco ?? '---'); ?></p>
            <p><strong>Telefone:</strong> <?php echo e($cliente->telefone ?? '---'); ?></p>
        </div>

        <div class="devolucao">
            <h3>Devolução - <?php echo e($devolucao->produto->nome ?? '---'); ?> - 000<?php echo e($devolucao->produto->id ?? '---'); ?></h3>
            <p><strong>Produto devolvido:</strong> <?php echo e($devolucao->produto->nome ?? '---'); ?></p>
            <p><strong>Quantidade:</strong> <?php echo e($devolucao->quantidade); ?></p>
            <p><strong>Motivo:</strong> <?php echo e($devolucao->motivo); ?></p>
            <p><strong>Status:</strong> <?php echo e(ucfirst($devolucao->status)); ?></p>
            <p><strong>Valor a ser restituído:</strong> 
                R$ <?php echo e(number_format($devolucao->quantidade * ($devolucao->produto->preco_venda ?? 0), 2, ',', '.')); ?>

            </p>
        </div>

        <div class="observacao">
            Observação: O cliente tem até <strong>7 dias</strong> para efetuar a troca do produto.
        </div>

        <div class="customer-sign">
            <p>Assinatura do Cliente: ____________________________</p>
            <p>Telefone para contato: ____________________________</p>
        </div>
    </div>

    <!-- VIA CLIENTE -->
    <div class="ticket">
        <div class="ticket-label">VIA CLIENTE</div>

        <div class="header">
            <h1><?php echo e($empresa->nome ?? '---'); ?></h1>
            <p><strong>Data:</strong> <?php echo e(\Carbon\Carbon::now()->format('d/m/Y')); ?></p>
            <p><?php echo e($empresa->endereco ?? '---'); ?> <?php echo e($empresa->numero ?? ''); ?> <?php echo e($empresa->complemento ?? ''); ?></p>
            <p><?php echo e($empresa->bairro ?? '---'); ?> - <?php echo e($empresa->cidade ?? '---'); ?> - <?php echo e($empresa->estado ?? ''); ?> - CEP <?php echo e($empresa->cep ?? '---'); ?></p>
            <p>Tel: <?php echo e($empresa->telefone ?? '---'); ?> | Email: <?php echo e($empresa->email ?? '---'); ?></p>
            <hr>
        </div>

        <div class="cliente">
            <h3>Cliente:</h3>
            <p><strong>Nome:</strong> <?php echo e($cliente->nome ?? '---'); ?></p>
            <p><strong>CPF/CNPJ:</strong> <?php echo e($cliente->cpf ?? $cliente->cnpj ?? '---'); ?></p>
            <p><strong>Endereço:</strong> <?php echo e($cliente->endereco ?? '---'); ?></p>
            <p><strong>Telefone:</strong> <?php echo e($cliente->telefone ?? '---'); ?></p>
        </div>

        <div class="devolucao">
            <h3>Devolução - <?php echo e($devolucao->produto->nome ?? '---'); ?> - 000<?php echo e($devolucao->produto->id ?? '---'); ?></h3>
            <p><strong>Produto devolvido:</strong> <?php echo e($devolucao->produto->nome ?? '---'); ?></p>
            <p><strong>Quantidade:</strong> <?php echo e($devolucao->quantidade); ?></p>
            <p><strong>Motivo:</strong> <?php echo e($devolucao->motivo); ?></p>
            <p><strong>Status:</strong> <?php echo e(ucfirst($devolucao->status)); ?></p>
            <p><strong>Valor a ser restituído:</strong> 
                R$ <?php echo e(number_format($devolucao->quantidade * ($devolucao->produto->preco_venda ?? 0), 2, ',', '.')); ?>

            </p>
        </div>
         <div class="observacao">
            Observação: O cliente tem até <strong>7 dias</strong> para efetuar a troca do produto.
        </div>
        <div class="signature">
            <p>___________________________________</p>
            <p>Assinatura do Responsável</p>
        </div>
    </div>

</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/cupom.blade.php ENDPATH**/ ?>