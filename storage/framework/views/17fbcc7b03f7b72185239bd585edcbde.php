<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido de Compra #<?php echo e($pedido->id); ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            color: #333;
            position: relative;
        }

        /* Cabeçalho */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            text-align: center;
            font-size: 22px;
            text-transform: uppercase;
            flex-grow: 1;
            margin: 0;
        }

        .empresa-info {
            font-size: 12px;
            text-align: left;
        }

        .empresa-info p {
            margin: 2px 0;
        }

        /* Seções */
        .section {
            margin-bottom: 20px;
        }

        .section-title {
            background: #f2f2f2;
            padding: 6px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        /* Tabela de itens */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #e9ecef;
        }

        td.num {
            text-align: right;
        }

        /* Total geral */
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
            font-size: 14px;
            padding: 5px;
            background: #f8f9fa;
            border-top: 2px solid #333;
        }

        /* Rodapé */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        /* Carimbos */
        .stamp {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            font-weight: bold;
            padding: 20px 40px;
            text-align: center;
            z-index: 1000;
            opacity: 0.4;
            border: 4px solid;
        }

        .stamp-cancelado {
            color: red;
            border-color: rgba(255,0,0,0.5);
            font-size: 60px;
        }

        .stamp-aprovado {
            color: green;
            border-color: rgba(0,128,0,0.5);
            font-size: 60px;
        }

        .stamp-recebido {
            color: blue;
            border-color: rgba(0,0,255,0.5);
            font-size: 50px;
        }
    </style>
</head>
<body>

    <!-- Cabeçalho -->
    <div class="header">
        <?php if($empresa && $empresa->logo): ?>
            <img src="<?php echo e(public_path('storage/' . $empresa->logo)); ?>" alt="Logo" style="height:60px;">
        <?php endif; ?>
        <h1>Pedido de Compra #<?php echo e($pedido->id); ?></h1>
        <div class="empresa-info">
            <?php if($empresa): ?>
                <p><strong><?php echo e($empresa->nome ?? $empresa->razao_social); ?></strong></p>
                <p>CNPJ: <?php echo e($empresa->cnpj); ?></p>
                <p><?php echo e($empresa->endereco); ?> - <?php echo e($empresa->cidade); ?>/<?php echo e($empresa->estado); ?></p>
                <p>Telefone: <?php echo e($empresa->telefone); ?> | E-mail: <?php echo e($empresa->email); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dados do Pedido -->
    <div class="section">
        <div class="section-title">Dados do Pedido - <small>*Valor da última compra como referência*</small></div>
        <table>
            <tr>
                <td><strong>Data do Pedido:</strong> <?php echo e(\Carbon\Carbon::parse($pedido->data_pedido)->format('d/m/Y H:i:s')); ?></td>
                <td><strong>Fornecedor:</strong> <?php echo e($pedido->fornecedor->nome ?? $pedido->fornecedor->razao_social); ?></td>
            </tr>
            <tr>
                <td><strong>Telefone:</strong> <?php echo e($pedido->fornecedor->telefone ?? '-'); ?></td>
                <td><strong>E-mail:</strong> <?php echo e($pedido->fornecedor->email ?? '-'); ?></td>
            </tr>
            <tr>
                <td colspan="2"><strong>Status:</strong> <?php echo e(ucfirst($pedido->status)); ?></td>
            </tr>
        </table>
    </div>

    <!-- Itens do Pedido -->
    <div class="section">
        <div class="section-title">Itens do Pedido</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produto</th>
                    <th>Unidade</th>
                    <th class="num">Quantidade</th>
                    <th class="num">Valor referência</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $pedido->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><?php echo e($item->produto->nome); ?></td>
                        <td><?php echo e($item->produto->unidadeMedida->nome ?? '-'); ?></td>
                        <td class="num" style="text-align:left"><?php echo e(number_format($item->quantidade, 2, ',', '.')); ?></td>
                        <td class="num" style="text-align:left">R$ <?php echo e(number_format($item->valor_unitario, 2, ',', '.')); ?></td>
                        <td class="num" style="text-align:left">R$ <?php echo e(number_format($item->subtotal, 2, ',', '.')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Total Geral -->
    <div class="total">
        Total Geral: R$ <?php echo e(number_format($pedido->itens->sum('subtotal'), 2, ',', '.')); ?>

    </div>

    <!-- Carimbos -->
    <?php if($pedido->status === 'cancelado'): ?>
        <div class="stamp stamp-cancelado">Pedido Cancelado</div>
    <?php elseif($pedido->status === 'aprovado'): ?>
        <div class="stamp stamp-aprovado">Pedido Autorizado</div>
    <?php elseif($pedido->status === 'recebido'): ?>
        <div class="stamp stamp-recebido">
            Produtos Recebidos<br>
            <?php echo e(\Carbon\Carbon::parse($pedido->updated_at)->format('d/m/Y H:i:s')); ?>

        </div>
    <?php endif; ?>

    <!-- Rodapé -->
    <div class="footer">
        <p>Gerado automaticamente em <?php echo e(now()->format('d/m/Y H:i')); ?></p>
        <p><?php echo e($empresa->nome ?? $empresa->razao_social); ?> © Todos os direitos reservados</p>
    </div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pedidos/pdf.blade.php ENDPATH**/ ?>