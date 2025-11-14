<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento #<?php echo e($orcamento->id); ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 40px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
        }

        .info {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .info strong {
            width: 150px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        .total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .observacoes {
            margin-top: 30px;
            font-size: 0.9em;
        }

        .carimbo {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-size: 60px;
            color: rgba(255, 0, 0, 0.2);
            font-weight: bold;
            text-transform: uppercase;
            border: 5px solid rgba(255, 0, 0, 0.2);
            padding: 15px 40px;
            border-radius: 15px;
        }

        .carimbo.aprovado {
            color: rgba(0, 128, 0, 0.25);
            border-color: rgba(0, 128, 0, 0.25);
        }

        .footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.8em;
            color: #555;
        }
    </style>
</head>
<body>
    
    <?php if($orcamento->status === 'Aprovado'): ?>
        <div class="carimbo aprovado">APROVADO</div>
    <?php elseif($orcamento->status === 'Cancelado'): ?>
        <div class="carimbo">CANCELADO</div>
    <?php else: ?>
        <div class="carimbo" style="color:rgba(0,0,0,0.15); border-color:rgba(0,0,0,0.15);">Aguardando Aprovacao</div>
    <?php endif; ?>

    <div class="header">
        <h2>Orçamento de Cliente</h2>
        <p><strong>Depósito de Materiais - Sistema Interno</strong></p>
        <small class="text-muted">Gerado em: <?php echo e(now()->format('d/m/Y H:i')); ?></small>
    </div>
    <div class="info d-flex flex-wrap gap-3 ">
        <div class="w-33"><strong>Código:</strong> #<?php echo e($orcamento->id); ?></div>
        <div class="w-33"><strong>Cliente:</strong> <?php echo e($orcamento->cliente->nome); ?></div>
        <div class="w-33"><strong> Dt.Orçamento:</strong> <?php echo e(\Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y')); ?></div>
        <div class="w-50"><strong>Validade:</strong> <?php echo e(\Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y')); ?></div>
        <div class="w-50"><strong>Status:</strong> <?php echo e($orcamento->status); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 100px;font-size:12px;">Produto</th>
                <th style="width: 20px;font-size:12px;">Qtd</th>
                <th style="width: 50px;font-size:12px;">Unitário</th>
                <th style="width: 50px;font-size:12px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $orcamento->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr style="width: 100px;font-size:12px;">
                    <td style="width: 180px;font-size:12px;"><?php echo e($item->produto->nome ?? '-'); ?></td>
                    <td style="width: 20px;font-size:12px;"><?php echo e(number_format($item->quantidade, 2, ',', '.')); ?></td>
                    <td style="width: 50px;font-size:12px;">R$ <?php echo e(number_format($item->preco_unitario, 2, ',', '.')); ?></td>
                    <td style="width: 50px;font-size:12px;">R$ <?php echo e(number_format($item->subtotal, 2, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <p class="total" style="font-size:12px;">Total: R$ <?php echo e(number_format($orcamento->total, 2, ',', '.')); ?></p>

    <?php if($orcamento->observacoes): ?>
        <div class="observacoes">
            <strong>Observações:</strong>
            <p><?php echo e($orcamento->observacoes); ?></p>
        </div>
    <?php endif; ?>

    <div class="footer">
        Documento gerado automaticamente pelo sistema - <?php echo e(config('app.name', 'Depósito de Materiais')); ?>

    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/pdf.blade.php ENDPATH**/ ?>