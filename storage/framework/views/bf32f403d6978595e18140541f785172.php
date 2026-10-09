<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>
        <?php echo e(isset($valeCompra)
            ? 'Vale-troca '.$valeCompra->codigo
            : 'Comprovante de devolução #'.$devolucao->id); ?>

    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 7mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111111;
            background: #ffffff;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8px;
            line-height: 1.25;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .documento {
            width: 100%;
            border: 1px solid #222222;
            page-break-inside: avoid;
        }

        .cabecalho td {
            padding: 6px 8px;
            border: 1px solid #222222;
            vertical-align: middle;
        }

        .emitente {
            width: 62%;
        }

        .identificacao {
            width: 38%;
            text-align: center;
        }

        .empresa-nome {
            margin-bottom: 3px;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .empresa-dados {
            font-size: 7px;
            line-height: 1.4;
        }

        .tipo-documento {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .via {
            margin-top: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .codigo {
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px solid #555555;
        }

        .codigo-label {
            display: block;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .codigo-valor {
            display: block;
            margin-top: 2px;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: .5px;
        }

        .secao-titulo {
            padding: 3px 5px;
            border-top: 1px solid #222222;
            border-bottom: 1px solid #222222;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .campos td {
            padding: 4px 5px;
            border-right: 1px solid #777777;
            border-bottom: 1px solid #777777;
            vertical-align: top;
        }

        .campos td:last-child {
            border-right: 0;
        }

        .rotulo {
            display: block;
            margin-bottom: 2px;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .valor {
            display: block;
            min-height: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .produto th,
        .produto td {
            padding: 4px;
            border: 1px solid #555555;
        }

        .produto th {
            font-size: 6px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }

        .produto td {
            font-size: 8px;
            vertical-align: top;
        }

        .produto .centro {
            text-align: center;
        }

        .produto .direita {
            text-align: right;
        }

        .totais td {
            padding: 5px 7px;
            border-top: 1px solid #222222;
            border-left: 1px solid #222222;
            vertical-align: middle;
        }

        .totais td:first-child {
            border-left: 0;
        }

        .total-descricao {
            width: 65%;
            font-size: 7px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
        }

        .total-valor {
            width: 35%;
            font-size: 14px;
            font-weight: bold;
            text-align: right;
        }

        .observacoes {
            min-height: 31px;
            padding: 5px;
            border-top: 1px solid #222222;
        }

        .observacoes-titulo {
            margin-bottom: 3px;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .observacoes p {
            margin: 2px 0;
        }

        .assinaturas {
            border-top: 1px solid #222222;
        }

        .assinaturas td {
            width: 50%;
            padding: 15px 15px 4px;
            text-align: center;
        }

        .linha-assinatura {
            padding-top: 3px;
            border-top: 1px solid #333333;
            font-size: 7px;
        }

        .rodape {
            padding: 4px 6px;
            border-top: 1px solid #222222;
            font-size: 6px;
            text-align: center;
        }

        .linha-corte {
            position: relative;
            height: 9mm;
            border-bottom: 1px dashed #555555;
        }

        .linha-corte span {
            position: absolute;
            bottom: -4px;
            left: 45%;
            padding: 0 5px;
            background: #ffffff;
            font-size: 6px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
<?php
    $produto = $devolucao->produto
        ?? $devolucao->itemVenda?->produto;

    $possuiVale = isset($valeCompra) && $valeCompra;

    $codigoDocumento = $possuiVale
        ? $valeCompra->codigo
        : 'DEV-'.str_pad(
            (string) $devolucao->id,
            6,
            '0',
            STR_PAD_LEFT
        );

    $tituloDocumento = $possuiVale
        ? 'Vale-troca'
        : 'Comprovante de devolução';

    $dataEmissao = $possuiVale && $valeCompra->created_at
        ? \Carbon\Carbon::parse($valeCompra->created_at)
        : now();

    $dataValidade = $possuiVale && $valeCompra->validade_em
        ? \Carbon\Carbon::parse($valeCompra->validade_em)
        : $dataEmissao->copy()->addDays(7);

    $quantidade = (float) $devolucao->quantidade;

    $valorUnitario = (float) (
        $valorUnitarioPago
        ?? $produto?->preco_venda
        ?? 0
    );

    $valorTotal = (float) (
        $valeCompra->valor
        ?? $valorTotalEstornado
        ?? ($quantidade * $valorUnitario)
    );

    $documentoCliente = $cliente->cpf
        ?? $cliente->cnpj
        ?? 'Não informado';

    $enderecoEmpresa = collect([
        $empresa->endereco ?? null,
        $empresa->numero ?? null,
        $empresa->complemento ?? null,
        $empresa->bairro ?? null,
        $empresa->cidade ?? null,
        $empresa->estado ?? null,
        $empresa->cep ?? null,
    ])->filter()->implode(' - ');

    $enderecoCliente = collect([
        $cliente->endereco ?? null,
        $cliente->numero ?? null,
        $cliente->complemento ?? null,
        $cliente->bairro ?? null,
        $cliente->cidade ?? null,
        $cliente->estado ?? null,
    ])->filter()->implode(' - ');

    $vias = [
        'Via estabelecimento',
        'Via cliente',
    ];
?>

<?php $__currentLoopData = $vias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $via): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <section class="documento">
        <table class="cabecalho">
            <tr>
                <td class="emitente">
                    <div class="empresa-nome">
                        <?php echo e($empresa->nome ?? 'Empresa não informada'); ?>

                    </div>

                    <div class="empresa-dados">
                        <?php echo e($enderecoEmpresa ?: 'Endereço não informado'); ?>


                        <br>

                        Telefone:
                        <?php echo e($empresa->telefone ?? 'Não informado'); ?>


                        <?php if(! empty($empresa->email)): ?>
                            | E-mail: <?php echo e($empresa->email); ?>

                        <?php endif; ?>
                    </div>
                </td>

                <td class="identificacao">
                    <div class="tipo-documento">
                        <?php echo e($tituloDocumento); ?>

                    </div>

                    <div class="via"><?php echo e($via); ?></div>

                    <div class="codigo">
                        <span class="codigo-label">
                            Código de controle
                        </span>

                        <span class="codigo-valor">
                            <?php echo e($codigoDocumento); ?>

                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="secao-titulo">Identificação do documento</div>

        <table class="campos">
            <tr>
                <td style="width: 20%;">
                    <span class="rotulo">Devolução</span>
                    <span class="valor">#<?php echo e($devolucao->id); ?></span>
                </td>

                <td style="width: 20%;">
                    <span class="rotulo">Ocorrência</span>
                    <span class="valor">
                        <?php echo e($devolucao->romaneio_ocorrencia_id
                            ? '#'.$devolucao->romaneio_ocorrencia_id
                            : 'Não vinculada'); ?>

                    </span>
                </td>

                <td style="width: 30%;">
                    <span class="rotulo">Data de emissão</span>
                    <span class="valor">
                        <?php echo e($dataEmissao->format('d/m/Y H:i')); ?>

                    </span>
                </td>

                <td style="width: 30%;">
                    <span class="rotulo">Validade</span>
                    <span class="valor">
                        <?php echo e($dataValidade->format('d/m/Y')); ?>

                    </span>
                </td>
            </tr>
        </table>

        <div class="secao-titulo">Dados do cliente</div>

        <table class="campos">
            <tr>
                <td style="width: 45%;">
                    <span class="rotulo">Nome/Razão social</span>
                    <span class="valor">
                        <?php echo e($cliente->nome ?? 'Não informado'); ?>

                    </span>
                </td>

                <td style="width: 25%;">
                    <span class="rotulo">CPF/CNPJ</span>
                    <span class="valor"><?php echo e($documentoCliente); ?></span>
                </td>

                <td style="width: 30%;">
                    <span class="rotulo">Telefone</span>
                    <span class="valor">
                        <?php echo e($cliente->telefone ?? 'Não informado'); ?>

                    </span>
                </td>
            </tr>

            <tr>
                <td colspan="3">
                    <span class="rotulo">Endereço</span>
                    <span class="valor">
                        <?php echo e($enderecoCliente ?: 'Não informado'); ?>

                    </span>
                </td>
            </tr>
        </table>

        <div class="secao-titulo">Mercadoria devolvida</div>

        <table class="produto">
            <thead>
                <tr>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 42%;">Descrição do produto</th>
                    <th style="width: 13%;" class="centro">Quantidade</th>
                    <th style="width: 17%;" class="direita">Valor unitário</th>
                    <th style="width: 18%;" class="direita">Valor do crédito</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <?php echo e(str_pad(
                            (string) ($produto->id ?? 0),
                            5,
                            '0',
                            STR_PAD_LEFT
                        )); ?>

                    </td>

                    <td>
                        <strong>
                            <?php echo e($produto->nome ?? 'Produto não encontrado'); ?>

                        </strong>
                    </td>

                    <td class="centro">
                        <?php echo e(number_format($quantidade, 3, ',', '.')); ?>

                    </td>

                    <td class="direita">
                        R$ <?php echo e(number_format($valorUnitario, 2, ',', '.')); ?>

                    </td>

                    <td class="direita">
                        R$ <?php echo e(number_format($valorTotal, 2, ',', '.')); ?>

                    </td>
                </tr>
            </tbody>
        </table>

        <table class="totais">
            <tr>
                <td class="total-descricao">
                    Valor total do crédito
                </td>

                <td class="total-valor">
                    R$ <?php echo e(number_format($valorTotal, 2, ',', '.')); ?>

                </td>
            </tr>
        </table>

        <div class="observacoes">
            <div class="observacoes-titulo">
                Informações complementares
            </div>

            <p>
                <strong>Motivo:</strong>
                <?php echo e($devolucao->motivo); ?>

            </p>

            <?php if($possuiVale): ?>
                <p>
                    Voucher pessoal e vinculado ao cliente identificado.
                    Validade até
                    <strong><?php echo e($dataValidade->format('d/m/Y')); ?></strong>.
                    Status:
                    <strong><?php echo e(strtoupper($valeCompra->status)); ?></strong>.
                </p>
            <?php endif; ?>
        </div>

        <table class="assinaturas">
            <tr>
                <td>
                    <div class="linha-assinatura">
                        Assinatura do cliente
                    </div>
                </td>

                <td>
                    <div class="linha-assinatura">
                        Responsável pela emissão
                    </div>
                </td>
            </tr>
        </table>

        <footer class="rodape">
            Documento vinculado à devolução #<?php echo e($devolucao->id); ?>

            | Controle: <?php echo e($codigoDocumento); ?>

        </footer>
    </section>

    <?php if($indice === 0): ?>
        <div class="linha-corte">
            <span>Linha de corte</span>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\devolucoes\cupom.blade.php ENDPATH**/ ?>