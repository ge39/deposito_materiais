<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>
        <?php echo e($possuiOcorrencias
                ? 'Relatório de ocorrências'
                : 'Comprovante de retorno'); ?>

        -
        <?php echo e($romaneio->codigo_romaneio); ?>

    </title>

    <style>
        @page {
            margin: 18mm 14mm 18mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: #212529;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .cabecalho {
            border-bottom: 2px solid #343a40;
            margin-bottom: 14px;
            padding-bottom: 10px;
        }

        .cabecalho-tabela {
            border-collapse: collapse;
            width: 100%;
        }

        .cabecalho-tabela td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .titulo {
            color: #212529;
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .subtitulo {
            color: #6c757d;
            font-size: 9px;
        }

        .documento-identificacao {
            text-align: right;
        }

        .codigo-romaneio {
            background: #343a40;
            border-radius: 3px;
            color: #fff;
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            padding: 5px 8px;
        }

        .data-impressao {
            color: #6c757d;
            font-size: 8px;
            margin-top: 5px;
        }

        .resultado-geral {
            border: 1px solid;
            border-radius: 4px;
            margin-bottom: 14px;
            padding: 10px;
        }

        .resultado-normal {
            background: #eaf7ef;
            border-color: #75b798;
        }

        .resultado-ocorrencia {
            background: #fff5df;
            border-color: #ffca2c;
        }

        .resultado-titulo {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .resultado-normal .resultado-titulo {
            color: #146c43;
        }

        .resultado-ocorrencia .resultado-titulo {
            color: #997404;
        }

        .resultado-texto {
            color: #495057;
            font-size: 9px;
        }

        .secao {
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .secao-titulo {
            background: #6c757d;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 7px 9px;
        }

        .secao-conteudo {
            padding: 9px;
        }

        .dados-tabela {
            border-collapse: collapse;
            width: 100%;
        }

        .dados-tabela td {
            border: 0;
            padding: 4px 8px 4px 0;
            vertical-align: top;
            width: 25%;
        }

        .dados-label {
            color: #6c757d;
            display: block;
            font-size: 7px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .dados-valor {
            color: #212529;
            font-size: 9px;
            font-weight: bold;
        }

        .produtos-tabela {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .produtos-tabela th {
            background: #343a40;
            border: 1px solid #495057;
            color: #fff;
            font-size: 7px;
            padding: 6px 3px;
            text-align: center;
            text-transform: uppercase;
        }

        .produtos-tabela td {
            border: 1px solid #ced4da;
            font-size: 8px;
            padding: 5px 3px;
            vertical-align: middle;
        }

        .produtos-tabela tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .col-produto {
            width: 24%;
        }

        .col-quantidade {
            text-align: right;
            width: 9%;
        }

        .col-observacao {
            width: 22%;
        }

        .produto-nome {
            font-weight: bold;
        }

        .produto-codigo {
            color: #6c757d;
            font-size: 7px;
            margin-top: 2px;
        }

        .quantidade-entregue {
            color: #146c43;
            font-weight: bold;
        }

        .quantidade-ocorrencia {
            color: #b02a37;
            font-weight: bold;
        }

        .sem-ocorrencia {
            color: #6c757d;
        }

        .observacao-geral {
            background: #f8f9fa;
            border-left: 3px solid #6c757d;
            min-height: 45px;
            padding: 8px;
            white-space: pre-wrap;
        }

        .historico-tabela {
            border-collapse: collapse;
            width: 100%;
        }

        .historico-tabela th {
            background: #e9ecef;
            border: 1px solid #ced4da;
            color: #495057;
            font-size: 7px;
            padding: 5px;
            text-align: left;
            text-transform: uppercase;
        }

        .historico-tabela td {
            border: 1px solid #ced4da;
            font-size: 8px;
            padding: 5px;
            vertical-align: top;
        }

        .historico-data {
            white-space: nowrap;
            width: 18%;
        }

        .historico-evento {
            width: 28%;
        }

        .historico-responsavel {
            width: 22%;
        }

        .historico-observacao {
            width: 32%;
        }

        .resumo-tabela {
            border-collapse: collapse;
            margin-left: auto;
            width: 50%;
        }

        .resumo-tabela td {
            border: 1px solid #ced4da;
            padding: 5px 7px;
        }

        .resumo-label {
            background: #f1f3f5;
            color: #495057;
            font-weight: bold;
        }

        .resumo-valor {
            font-weight: bold;
            text-align: right;
        }

        .assinaturas {
            margin-top: 35px;
            page-break-inside: avoid;
        }

        .assinaturas-tabela {
            border-collapse: collapse;
            width: 100%;
        }

        .assinaturas-tabela td {
            border: 0;
            padding: 0 12px;
            text-align: center;
            vertical-align: bottom;
            width: 50%;
        }

        .linha-assinatura {
            border-top: 1px solid #343a40;
            margin-top: 35px;
            padding-top: 5px;
        }

        .assinatura-nome {
            font-size: 9px;
            font-weight: bold;
        }

        .assinatura-funcao {
            color: #6c757d;
            font-size: 8px;
        }

        .rodape {
            bottom: -10mm;
            color: #6c757d;
            font-size: 7px;
            left: 0;
            position: fixed;
            right: 0;
            text-align: center;
        }

        .quebra-evitar {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

<?php
    $nomeCliente =
        $cliente?->nome
        ?? $cliente?->razao_social
        ?? 'Cliente não identificado';

    $motorista =
        $romaneio->motorista?->nome
        ?? $entrega->motorista?->nome
        ?? 'Não informado';

    $placa =
        $romaneio->veiculo?->placa
        ?? $entrega->veiculo?->placa
        ?? 'Não informada';

    $totalSaida = $romaneio->itens->sum(
        fn ($item) =>
            (float) $item->quantidade_conferida_saida
    );

    $totalEntregue = $romaneio->itens->sum(
        fn ($item) =>
            (float) $item->quantidade_entregue
    );

    $totalDevolvido = $romaneio->itens->sum(
        fn ($item) =>
            (float) $item->quantidade_devolvida
    );

    $totalRecusado = $romaneio->itens->sum(
        fn ($item) =>
            (float) $item->quantidade_recusada
    );

    $totalAvariado = $romaneio->itens->sum(
        fn ($item) =>
            (float) $item->quantidade_avariada
    );

    $totalPerdido = $romaneio->itens->sum(
        fn ($item) =>
            (float) $item->quantidade_perdida
    );

    $totalOcorrencias =
        $totalDevolvido
        + $totalRecusado
        + $totalAvariado
        + $totalPerdido;

    $responsavelRetorno =
        $romaneio->usuarioRegistroRetorno?->name
        ?? $romaneio->usuarioRegistroRetorno?->nome
        ?? $eventoRetorno?->usuario?->name
        ?? $eventoRetorno?->usuario?->nome
        ?? 'Não identificado';

    $conferenteRetorno = $romaneio->itens
        ->pluck('conferenteRetorno.nome')
        ->filter()
        ->unique()
        ->implode(', ');

    if ($conferenteRetorno === '') {
        $conferenteRetorno = 'Conferência ainda não identificada';
    }
?>

<div class="cabecalho">
    <table class="cabecalho-tabela">
        <tr>
            <td>
                <div class="titulo">
                    <?php echo e($possuiOcorrencias
                            ? 'Relatório de ocorrências da entrega'
                            : 'Comprovante de retorno sem ocorrências'); ?>

                </div>

                <div class="subtitulo">
                    Controle operacional do retorno e da conferência dos
                    materiais transportados.
                </div>
            </td>

            <td class="documento-identificacao">
                <div class="codigo-romaneio">
                    <?php echo e($romaneio->codigo_romaneio); ?>

                </div>

                <div class="data-impressao">
                    Impresso em:
                    <?php echo e(now()->format('d/m/Y H:i')); ?>

                </div>
            </td>
        </tr>
    </table>
</div>

<div class="resultado-geral <?php echo e($possuiOcorrencias
        ? 'resultado-ocorrencia'
        : 'resultado-normal'); ?>">
    <div class="resultado-titulo">
        <?php if($possuiOcorrencias): ?>
            Retorno registrado com ocorrência
        <?php else: ?>
            Entrega realizada normalmente
        <?php endif; ?>
    </div>

    <div class="resultado-texto">
        <?php if($possuiOcorrencias): ?>
            Foram registradas diferenças nos produtos ou fatos relevantes
            ocorridos durante o trajeto ou no atendimento ao cliente.
        <?php else: ?>
            Todos os materiais foram declarados como entregues, sem
            devoluções, recusas, avarias, perdas ou ocorrências no trajeto.
        <?php endif; ?>
    </div>
</div>

<div class="secao quebra-evitar">
    <div class="secao-titulo">
        Identificação da entrega
    </div>

    <div class="secao-conteudo">
        <table class="dados-tabela">
            <tr>
                <td>
                    <span class="dados-label">
                        Entrega
                    </span>

                    <span class="dados-valor">
                        <?php echo e($entrega->codigo_entrega
                            ?? "#{$entrega->id}"); ?>

                    </span>
                </td>

                <td>
                    <span class="dados-label">
                        Romaneio
                    </span>

                    <span class="dados-valor">
                        <?php echo e($romaneio->codigo_romaneio); ?>

                    </span>
                </td>

                <td>
                    <span class="dados-label">
                        Cliente
                    </span>

                    <span class="dados-valor">
                        <?php echo e($nomeCliente); ?>

                    </span>
                </td>

                <td>
                    <span class="dados-label">
                        Status
                    </span>

                    <span class="dados-valor">
                        <?php echo e(str_replace(
                                '_',
                                ' ',
                                $romaneio->status
                            )); ?>

                    </span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="dados-label">
                        Motorista
                    </span>

                    <span class="dados-valor">
                        <?php echo e($motorista); ?>

                    </span>
                </td>

                <td>
                    <span class="dados-label">
                        Veículo
                    </span>

                    <span class="dados-valor">
                        <?php echo e($placa); ?>

                    </span>
                </td>

                <td>
                    <span class="dados-label">
                        Saída
                    </span>

                    <span class="dados-valor">
                        <?php echo e($romaneio->data_saida
                                ? $romaneio->data_saida
                                    ->format('d/m/Y H:i')
                                : 'Não registrada'); ?>

                    </span>
                </td>

                <td>
                    <span class="dados-label">
                        Retorno
                    </span>

                    <span class="dados-valor">
                        <?php echo e($romaneio->data_retorno
                                ? $romaneio->data_retorno
                                    ->format('d/m/Y H:i')
                                : 'Não registrado'); ?>

                    </span>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="dados-label">
                        Retorno registrado por
                    </span>

                    <span class="dados-valor">
                        <?php echo e($responsavelRetorno); ?>

                    </span>
                </td>

                <td colspan="2">
                    <span class="dados-label">
                        Conferente do retorno
                    </span>

                    <span class="dados-valor">
                        <?php echo e($conferenteRetorno); ?>

                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="secao">
    <div class="secao-titulo">
        Resultado por produto
    </div>

    <div class="secao-conteudo">
        <table class="produtos-tabela">
            <thead>
                <tr>
                    <th class="col-produto">
                        Produto
                    </th>

                    <th class="col-quantidade">
                        Saída
                    </th>

                    <th class="col-quantidade">
                        Entregue
                    </th>

                    <th class="col-quantidade">
                        Devolvida
                    </th>

                    <th class="col-quantidade">
                        Recusada
                    </th>

                    <th class="col-quantidade">
                        Avariada
                    </th>

                    <th class="col-quantidade">
                        Perdida
                    </th>

                    <th class="col-observacao">
                        Observação
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $romaneio->itens
                        ->sortBy('ordem'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $romaneioItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $entregaItem =
                            $romaneioItem->entregaItem;

                        $produto =
                            $entregaItem?->produto
                            ?? $entregaItem?->vendaItem?->produto
                            ?? $entregaItem?->itemOrcamento?->produto;

                        $nomeProduto =
                            $produto?->nome
                            ?? $produto?->descricao
                            ?? "Produto #{$romaneioItem->entrega_item_id}";

                        $codigoProduto =
                            $produto?->codigo
                            ?? $produto?->id
                            ?? '-';

                        $possuiOcorrenciaItem =
                            (float) $romaneioItem->quantidade_devolvida > 0
                            || (float) $romaneioItem->quantidade_recusada > 0
                            || (float) $romaneioItem->quantidade_avariada > 0
                            || (float) $romaneioItem->quantidade_perdida > 0
                            || trim(
                                (string) $romaneioItem->observacao
                            ) !== '';
                    ?>

                    <tr>
                        <td>
                            <div class="produto-nome">
                                <?php echo e($nomeProduto); ?>

                            </div>

                            <div class="produto-codigo">
                                Código:
                                <?php echo e($codigoProduto); ?>

                            </div>
                        </td>

                        <td class="col-quantidade">
                            <?php echo e(number_format(
                                    (float) $romaneioItem
                                        ->quantidade_conferida_saida,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                        </td>

                        <td class="col-quantidade quantidade-entregue">
                            <?php echo e(number_format(
                                    (float) $romaneioItem
                                        ->quantidade_entregue,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                        </td>

                        <td class="col-quantidade <?php echo e((float) $romaneioItem->quantidade_devolvida > 0
                                ? 'quantidade-ocorrencia'
                                : 'sem-ocorrencia'); ?>">
                            <?php echo e(number_format(
                                    (float) $romaneioItem
                                        ->quantidade_devolvida,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                        </td>

                        <td class="col-quantidade <?php echo e((float) $romaneioItem->quantidade_recusada > 0
                                ? 'quantidade-ocorrencia'
                                : 'sem-ocorrencia'); ?>">
                            <?php echo e(number_format(
                                    (float) $romaneioItem
                                        ->quantidade_recusada,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                        </td>

                        <td class="col-quantidade <?php echo e((float) $romaneioItem->quantidade_avariada > 0
                                ? 'quantidade-ocorrencia'
                                : 'sem-ocorrencia'); ?>">
                            <?php echo e(number_format(
                                    (float) $romaneioItem
                                        ->quantidade_avariada,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                        </td>

                        <td class="col-quantidade <?php echo e((float) $romaneioItem->quantidade_perdida > 0
                                ? 'quantidade-ocorrencia'
                                : 'sem-ocorrencia'); ?>">
                            <?php echo e(number_format(
                                    (float) $romaneioItem
                                        ->quantidade_perdida,
                                    2,
                                    ',',
                                    '.'
                                )); ?>

                        </td>

                        <td>
                            <?php if($possuiOcorrenciaItem): ?>
                                <?php echo e($romaneioItem->observacao
                                    ?: 'Ocorrência quantitativa registrada.'); ?>

                            <?php else: ?>
                                <span class="sem-ocorrencia">
                                    Sem ocorrência
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td
                            colspan="8"
                            style="text-align: center;"
                        >
                            Nenhum produto encontrado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="secao quebra-evitar">
    <div class="secao-titulo">
        Resumo quantitativo
    </div>

    <div class="secao-conteudo">
        <table class="resumo-tabela">
            <tr>
                <td class="resumo-label">
                    Quantidade de saída
                </td>

                <td class="resumo-valor">
                    <?php echo e(number_format(
                            $totalSaida,
                            2,
                            ',',
                            '.'
                        )); ?>

                </td>
            </tr>

            <tr>
                <td class="resumo-label">
                    Quantidade entregue
                </td>

                <td class="resumo-valor">
                    <?php echo e(number_format(
                            $totalEntregue,
                            2,
                            ',',
                            '.'
                        )); ?>

                </td>
            </tr>

            <tr>
                <td class="resumo-label">
                    Quantidade com ocorrência
                </td>

                <td class="resumo-valor">
                    <?php echo e(number_format(
                            $totalOcorrencias,
                            2,
                            ',',
                            '.'
                        )); ?>

                </td>
            </tr>
        </table>
    </div>
</div>

<?php if(
    $possuiOcorrencias
    || $observacaoRetorno !== ''
): ?>
    <div class="secao quebra-evitar">
        <div class="secao-titulo">
            Ocorrência geral do trajeto ou da entrega
        </div>

        <div class="secao-conteudo">
            <div class="observacao-geral">
                <?php echo e($observacaoRetorno !== ''
                        ? $observacaoRetorno
                        : 'Não foi registrada uma observação geral. Consulte as ocorrências informadas por produto.'); ?>

            </div>
        </div>
    </div>
<?php endif; ?>

<?php if($romaneio->eventos->isNotEmpty()): ?>
    <div class="secao">
        <div class="secao-titulo">
            Histórico do retorno
        </div>

        <div class="secao-conteudo">
            <table class="historico-tabela">
                <thead>
                    <tr>
                        <th class="historico-data">
                            Data e hora
                        </th>

                        <th class="historico-evento">
                            Evento
                        </th>

                        <th class="historico-responsavel">
                            Responsável
                        </th>

                        <th class="historico-observacao">
                            Observação
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__currentLoopData = $romaneio->eventos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $responsavelEvento =
                                $evento->funcionario?->nome
                                ?? $evento->usuario?->name
                                ?? $evento->usuario?->nome
                                ?? 'Sistema';
                        ?>

                        <tr>
                            <td>
                                <?php echo e($evento->ocorrido_em
                                        ? $evento->ocorrido_em
                                            ->format('d/m/Y H:i')
                                        : '-'); ?>

                            </td>

                            <td>
                                <?php echo e($evento->evento); ?>

                            </td>

                            <td>
                                <?php echo e($responsavelEvento); ?>

                            </td>

                            <td>
                                <?php echo e($evento->observacao ?: '-'); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="assinaturas">
    <table class="assinaturas-tabela">
        <tr>
            <td>
                <div class="linha-assinatura">
                    <div class="assinatura-nome">
                        <?php echo e($motorista); ?>

                    </div>

                    <div class="assinatura-funcao">
                        Motorista
                    </div>
                </div>
            </td>

            <td>
                <div class="linha-assinatura">
                    <div class="assinatura-nome">
                        <?php echo e($conferenteRetorno); ?>

                    </div>

                    <div class="assinatura-funcao">
                        Responsável pela conferência do retorno
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="rodape">
    Documento gerado pelo sistema em
    <?php echo e(now()->format('d/m/Y H:i:s')); ?>

    —
    Entrega
    <?php echo e($entrega->codigo_entrega
        ?? "#{$entrega->id}"); ?>

    —
    Romaneio
    <?php echo e($romaneio->codigo_romaneio); ?>

</div>

</body>
</html><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\entregas\relatorio-retorno.blade.php ENDPATH**/ ?>