<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Emissao de Orçamento #<?php echo e($orcamento->id); ?></title>
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

        /* 🔥 TABELA EM DIV (DOMPDF SAFE) */
        .table {
            display: table;
            width: 100%;
            margin-top: 5px;
        }

        .tr {
            display: table-row;
        }

        .th, .td {
            display: table-cell;
            border: 1px solid #999;
            padding: 6px;
            font-size: 11px;
        }

        .th {
            background: #eee;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
            font-size: 13px;
        }

        .page-break {
            page-break-before: always;
        }

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

        .contato {
            margin-top: 20px;
            font-size: 11px;
            text-align: center;
            color: #555;
        }

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

<!-- HEADER EMPRESA -->
<div class="header">
    <strong><?php echo e($orcamento->empresa->nome ?? 'EMPRESA NAO CADASTRADA'); ?></strong><br>
    CNPJ: <?php echo e($orcamento->empresa->cnpj ?? '-'); ?><br>
    <?php echo e($orcamento->empresa->endereco ?? '-'); ?>, <?php echo e($orcamento->empresa->numero ?? ''); ?><br>
    <?php echo e($orcamento->empresa->cidade ?? '-'); ?> - <?php echo e($orcamento->empresa->estado ?? '-'); ?><br>
    Tel: <?php echo e($orcamento->empresa->telefone ?? '-'); ?>

</div>

<?php
    $itensAgrupados = $orcamento->itens->groupBy('produto_id');

    $totalVenda = $orcamento->itens->sum(fn($i) => $i->quantidade_solicitada * $i->preco_unitario);
    $totalEntregue = $orcamento->itens->sum(fn($i) => $i->quantidade_atendida * $i->preco_unitario);
    $totalPendente = $orcamento->itens->sum(fn($i) => $i->quantidade_pendente * $i->preco_unitario);
?>

<!-- DADOS -->
<div class="section">
    
    <div class="box" style="font-size: 11px; line-height: 1.4;">
        <div style="border: 1px solid gray; padding: 10px;">
            <strong>
                <?php echo e($orcamento->status ?? 'SEM STATUS'); ?> 
                em: <?php echo e(\Carbon\Carbon::parse($orcamento->updated_at)->format('d/m/Y H:i')); ?>

            </strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <div>
                <strong>Cód:</strong> #<?php echo e($orcamento->codigo_orcamento); ?>  
                <strong>ID:</strong> <?php echo e($orcamento->id); ?>

            </div>
            <div>
                <strong>Orçamento:</strong> 
                <?php echo e(\Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y')); ?>

            </div>
        </div>

        <div>
            <strong>Cliente:</strong> <?php echo e($orcamento->cliente->nome ?? '-'); ?>  
            <strong>Tel:</strong> <?php echo e($orcamento->cliente->telefone ?? '-'); ?>

        </div>

        <div>
            <strong>Prev.Entrega:</strong> 
            <?php echo e($orcamento->itens->first()?->previsao_entrega
                ? \Carbon\Carbon::parse($orcamento->itens->first()->previsao_entrega)->format('d/m/Y H:i')
                : 'Não definido'); ?>

        </div>

        <div>
            <strong>End:</strong>
            <?php echo e($orcamento->cliente->endereco_entrega ?? $orcamento->cliente->endereco); ?>,
            <?php echo e($orcamento->cliente->numero); ?> -
            <?php echo e($orcamento->cliente->bairro); ?> -
            <?php echo e($orcamento->cliente->cidade); ?>/<?php echo e($orcamento->cliente->estado); ?> -
            <?php echo e($orcamento->cliente->cep); ?>

        </div>

    </div>
</div>

<!-- ITENS ENTREGUES -->
<div class="section">
    <div class="section-title">Itens Entregues</div>

    <div class="table">

        <!-- HEADER -->
        <div class="tr">
            <div class="th">ID</div>
            <div class="th">Produto</div>
            <div class="th text-center">Solicitado</div>
            <div class="th text-center">Entregue</div>
            <div class="th text-center">Lote</div>
        </div>

        <?php $__currentLoopData = $itensAgrupados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itens): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php
                $produto = $itens->first()->produto;

                $qtdSolicitada = $itens->sum('quantidade_solicitada');
                $qtdEntregue   = $itens->sum('quantidade_atendida');

                // 🔥 Lotes correto via pivot
                $lotesStr = $itens
                    ->flatMap(fn($item) => $item->lotes)
                    ->pluck('numero_lote')
                    ->filter()
                    ->unique()
                    ->implode(', ');
            ?>

            <?php if($qtdEntregue > 0): ?>
                <div class="tr">
                    <div class="td"><?php echo e($produto->id ?? '-'); ?></div>
                    <div class="td"><?php echo e($produto->descricao); ?></div>
                    <div class="td text-center"><?php echo e(number_format($qtdSolicitada, 2, ',', '.')); ?></div>
                    <div class="td text-center"><?php echo e(number_format($qtdEntregue, 2, ',', '.')); ?></div>
                    <div class="td text-center"><?php echo e($lotesStr ?: '-'); ?></div>
                </div>
            <?php endif; ?>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>
    <p class="total" style="font-size:12px;">Total: R$ <?php echo e(number_format($orcamento->total, 2, ',', '.')); ?></p>
         <!-- FIM DA TABELA DE ITENS ENTREGUES -->
</div>

<!-- ========================================================================= -->
<!-- 🔥 MOVIDO PARA A PRIMEIRA PÁGINA: RESUMO / OBS / ASSINATURA -->
<!-- ========================================================================= -->
<div style="margin-top: 15px; font-size: 11px;">
    <div class="section-title">Resumo de Atendimento por Produto</div>
    <!-- ...estrutura da tabela de resumo... -->
</div>

<!-- ... (observações, assinatura, contato) ... -->

<!-- ========================================================================= -->
<!-- 🎯 CONDICIONAL INTELIGENTE: SEGUNDA PÁGINA APENAS SE HOUVER PENDÊNCIA -->
<!-- ========================================================================= -->
<?php if($orcamento->itens->sum('quantidade_pendente') > 0): ?>
    <div class="page-break"></div>
    <div class="section">
        <div class="section-title">Itens Pendentes / Não Entregues</div>
        <!-- ...tabela de itens pendentes detalhada... -->
    </div>
<?php endif; ?>

 <!-- ASSINATURA -->
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

<!-- FOOTER FIXO -->
<div class="footer">
    Documento gerado automaticamente
</div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/pdf.blade.php ENDPATH**/ ?>