<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Orçamento #<?php echo e($orcamento->id); ?></title>

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    margin: 30px;
    font-size: 12px;
    color: #333;
}

.header {
    text-align: center;
    border-bottom: 2px solid #000;
    margin-bottom: 20px;
    padding-bottom: 10px;
}

.section {
    margin-bottom: 20px;
}

.section-title {
    background: #f0f0f0;
    padding: 6px;
    font-weight: bold;
    border: 1px solid #ccc;
}

.box {
    border: 1px solid #ccc;
    padding: 10px;
}

.row {
    margin-bottom: 5px;
}

.label {
    display: inline-block;
    width: 120px;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
}

th, td {
    border: 1px solid #999;
    padding: 6px;
    font-size: 11px;
}

th {
    background: #eee;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.total {
    text-align: right;
    font-weight: bold;
    margin-top: 10px;
    font-size: 13px;
}

.page-break {
    page-break-before: always;
}

/* ASSINATURA */
.assinatura {
    margin-top: 60px;
    width: 100%;
}

.assinatura-box {
    width: 45%;
    display: inline-block;
    text-align: center;
}

.linha {
    border-top: 1px solid #000;
    margin-top: 40px;
    padding-top: 5px;
}

/* CONTATO */
.contato {
    margin-top: 20px;
    font-size: 11px;
    text-align: center;
    color: #555;
}

/* CARIMBO */
.carimbo {
    position: fixed;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-20deg);
    font-size: 60px;
    color: rgba(0,0,0,0.1);
    border: 4px solid rgba(0,0,0,0.1);
    padding: 10px 30px;
}
.aprovado {
    color: rgba(0,128,0,0.2);
    border-color: rgba(0,128,0,0.2);
}
.cancelado {
    color: rgba(255,0,0,0.2);
    border-color: rgba(255,0,0,0.2);
}

.footer {
    position: fixed;
    bottom: 10px;
    text-align: center;
    width: 100%;
    font-size: 10px;
    color: #777;
}
</style>
</head>

<body>


<?php if($orcamento->status === 'Aprovado'): ?>
    <div class="carimbo aprovado">APROVADO</div>
<?php elseif($orcamento->status === 'Cancelado'): ?>
    <div class="carimbo cancelado">CANCELADO</div>
<?php else: ?>
    <div class="carimbo">AGUARDANDO</div>
<?php endif; ?>

<!-- HEADER -->
<div class="header">
    <h2>Orçamento / Pedido</h2>
    <small>Gerado em: <?php echo e(now()->format('d/m/Y H:i')); ?></small>
</div>

<!-- 🔷 HEADER EMPRESA -->
<div class="header">
    <strong><?php echo e($orcamento->empresa->nome ?? 'EMPRESA NAO CADASTRADA'); ?></strong><br>
    CNPJ: <?php echo e($orcamento->empresa->cnpj ?? '-'); ?><br>
    <?php echo e($orcamento->empresa->endereco ?? '-'); ?>, <?php echo e($orcamento->empresa->numero ?? ''); ?><br>
    <?php echo e($orcamento->empresa->cidade ?? '-'); ?> - <?php echo e($orcamento->empresa->estado ?? '-'); ?><br>
    Tel: <?php echo e($orcamento->empresa->telefone ?? '-'); ?>

</div>

<?php
$itensAgrupados = $orcamento->itens->groupBy('produto_id');
?>

<?php
$totalVenda = $orcamento->itens->sum(fn($i) => $i->quantidade * $i->preco_unitario);

$totalEntregue = $orcamento->itens->sum(fn($i) => $i->quantidade_atendida * $i->preco_unitario);

$totalPendente = $orcamento->itens->sum(fn($i) => $i->quantidade_pendente * $i->preco_unitario);
?>

<!-- DADOS -->
<div class="section">
    <div class="box">
        <div class="row"><span class="label">Código:</span> #<?php echo e($orcamento->codigo_orcamento); ?></div>
        <div class="row"><span class="label">Cliente:</span> <?php echo e($orcamento->cliente->nome ?? '-'); ?></div>
        <div class="row"><span class="label">Telefone:</span> <?php echo e($orcamento->cliente->telefone ?? '-'); ?></div>
        <div class="row"><span class="label">Data:</span> <?php echo e(\Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y')); ?></div>
        <div class="row"><span class="label">Validade:</span> <?php echo e(\Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y')); ?></div>
    </div>
</div>

<!-- 📦 ITENS ENTREGUES -->
<div class="section">
    <div class="section-title">Itens Entregues</div>

    <table>
        <thead>
<tr>
    <th>Produto</th>
    <th>Solicitado</th>
    <th>Entregue</th>
    <th>Lote</th>
</tr>
</thead>

<tbody>
<?php $__currentLoopData = $itensAgrupados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itens): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

<?php
    $produto = $itens->first()->produto;

    $qtdSolicitada = $itens->sum('quantidade');
    $qtdEntregue = $itens->sum('quantidade_atendida');

    $lotes = $itens
        ->where('quantidade_atendida', '>', 0)
        ->map(fn($i) => $i->lote->numero_lote ?? '-')
        ->unique()
        ->implode(', ');
?>

<?php if($qtdEntregue > 0): ?>
<tr>
    <td><?php echo e($produto->descricao); ?></td>

    <td class="text-center">
        <?php echo e(number_format($qtdSolicitada, 2, ',', '.')); ?>

    </td>

    <td class="text-center">
        <?php echo e(number_format($qtdEntregue, 2, ',', '.')); ?>

    </td>

    <td class="text-center">
        <?php echo e($lotes ?: '-'); ?>

    </td>
</tr>
<?php endif; ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
    </table>

    <div class="total">

        <div>Total da Venda: 
            R$ <?php echo e(number_format($totalVenda, 2, ',', '.')); ?>

        </div>

        <div style="color: green;">
            Total Entregue: 
            R$ <?php echo e(number_format($totalEntregue, 2, ',', '.')); ?>

        </div>

        <div style="color: #aa0000;">
            Total Pendente: 
            R$ <?php echo e(number_format($totalPendente, 2, ',', '.')); ?>

        </div>

    </div>

        <p style="font-size:11px; color:#333;">
            Este documento refere-se à entrega parcial dos produtos.
            Os valores abaixo discriminam o total do pedido, o que está sendo entregue e
            neste momento e o Total pendente.
        </p>

    <!-- ASSINATURA ENTREGA -->
    <div class="assinatura">
        <div class="assinatura-box">
            <div class="linha">
                Assinatura do Cliente<br>
                Data: ____/____/____
            </div>
        </div>

        <div class="assinatura-box">
            <div class="linha">
                Responsável pela Entrega<br>
                Data: ____/____/____
            </div>
        </div>
    </div>
</div>

<!-- QUEBRA -->
<div class="page-break"></div>

<!-- ⏳ ITENS PENDENTES -->
<div class="section">
    <div class="section-title">Itens Pendentes / Não Entregues</div>

    <p style="color:#aa0000; font-weight:bold;">
        ⚠ Estes itens NÃO serão entregues neste pedido.<br>
        Serão fornecidos conforme a previsão de entrega estipulada neste documento..
    </p>
   
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Pendente</th>
                <th>Previsão</th>
            </tr>
        </thead>
       
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $orcamento->itens->where('quantidade_pendente', '>', 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($item->produto->descricao ?? '-'); ?></td>
                    <td class="text-center"><?php echo e(number_format($item->quantidade_pendente, 2, ',', '.')); ?></td>
                    <td class="text-center">
                        <?php echo e($item->previsao_entrega 
                            ? \Carbon\Carbon::parse($item->previsao_entrega)->format('d/m/Y')
                            : '-'); ?>

                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="3" class="text-center">Nenhum item pendente</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    

    <!-- ASSINATURA PENDENTE -->
    <div class="assinatura">
        <div class="assinatura-box">
            <div class="linha">
                Ciente do Cliente<br>
                Data: ____/____/____
            </div>
        </div>
    </div>

    <div class="contato">
        Em caso de dúvidas:<br>
        📞 (11) 99999-9999 | 📧 contato@empresa.com.br
    </div>

</div>

<!-- OBS -->
<?php if($orcamento->observacoes): ?>
<div class="section">
    <div class="section-title">Observações</div>
    <div class="box">
        <?php echo e($orcamento->observacoes); ?>

    </div>
</div>
<?php endif; ?>

<!-- FOOTER -->
<div class="footer">
    Documento gerado automaticamente - <?php echo e(config('app.name')); ?>

</div>

</body>
</html><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/orcamentos/pdf.blade.php ENDPATH**/ ?>