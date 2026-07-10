from pathlib import Path

content = r'''<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <?php
        $entrega = $romaneio->entrega;
        $orcamento = $entrega?->orcamento;
        $venda = $entrega?->venda;
        $cliente = $entrega?->cliente ?? $orcamento?->cliente ?? $venda?->cliente;

        $codigoRomaneio = $romaneio->codigo_romaneio ?? 'ROM-' . $romaneio->id;

        $statusSeparacao = [
            'Gerado',
            'Em_separacao',
            'Separado',
            'Na_doca',
        ];

        $documentoSeparacao = in_array($romaneio->status, $statusSeparacao, true);

        $tituloDocumento = $documentoSeparacao
            ? 'ROMANEIO DE SEPARAÇÃO'
            : 'ROMANEIO DE ENTREGA';

        $subtituloDocumento = $documentoSeparacao
            ? 'DOCUMENTO INTERNO DE APOIO À EXPEDIÇÃO'
            : 'DOCUMENTO DE ACOMPANHAMENTO DA ENTREGA';

        $vias = $documentoSeparacao
            ? ['VIA INTERNA - SEPARAÇÃO / CONFERÊNCIA']
            : [
                'VIA 1 - EXPEDIÇÃO',
                'VIA 2 - MOTORISTA / CLIENTE',
            ];

        $statusLabels = [
            'Gerado'            => 'Gerado',
            'Em_separacao'      => 'Em separação',
            'Separado'          => 'Separado',
            'Na_doca'           => 'Na doca',
            'Carregando'        => 'Carregando',
            'Carregado'         => 'Carregado',
            'Saiu_para_entrega' => 'Saiu para entrega',
            'Entregue'          => 'Entregue',
            'Parcial'           => 'Parcial',
            'Devolvido'         => 'Devolvido',
            'Cancelado'         => 'Cancelado',
        ];

        $statusFormatado = $statusLabels[$romaneio->status]
            ?? str_replace('_', ' ', $romaneio->status ?? 'Gerado');

        $telefoneCliente = data_get($cliente, 'telefone')
            ?? data_get($cliente, 'celular')
            ?? $entrega?->telefone_recebimento
            ?? 'Não informado';

        $documentoCliente = data_get($cliente, 'cpf_cnpj')
            ?? data_get($cliente, 'documento')
            ?? data_get($cliente, 'cpf')
            ?? data_get($cliente, 'cnpj')
            ?? 'Não informado';

        $enderecoEntrega = $entrega?->endereco_entrega
            ?? $entrega?->endereco_entrega_concatenado
            ?? 'Endereço não informado';

        $dataPrevista = $entrega?->data_prevista_entrega
            ?? $entrega?->data_prevista;

        $observacaoEntrega = $entrega?->observacao_entrega
            ?? $entrega?->observacao
            ?? '—';

        $observacaoOrcamento = $orcamento?->observacoes ?? '—';
    ?>

    <title><?php echo e($tituloDocumento); ?> - <?php echo e($codigoRomaneio); ?></title>

    <style>
        @page {
            size: A4 portrait;
            margin: 9mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #000;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            line-height: 1.25;
        }

        .no-print {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            border-bottom: 1px solid #d0d0d0;
            background: #f5f5f5;
        }

        .no-print button {
            padding: 8px 14px;
            border: 1px solid #222;
            border-radius: 4px;
            color: #fff;
            background: #222;
            cursor: pointer;
            font-weight: 700;
        }

        .pagina-romaneio {
            position: relative;
            min-height: 277mm;
            padding-bottom: 14mm;
            page-break-after: always;
        }

        .pagina-romaneio:last-child {
            page-break-after: auto;
        }

        .cabecalho {
            margin-bottom: 7px;
            padding: 8px;
            border: 2px solid #000;
        }

        .titulo {
            text-align: center;
            font-size: 18px;
            line-height: 1.05;
            font-weight: 800;
        }

        .subtitulo {
            margin-top: 3px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .via {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 11px;
            font-weight: 800;
        }

        .grid {
            display: grid;
            gap: 6px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .campo {
            min-width: 0;
        }

        .rotulo {
            display: block;
            margin-bottom: 1px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .valor {
            overflow-wrap: anywhere;
            font-weight: 600;
        }

        .box {
            margin-bottom: 7px;
            padding: 7px;
            border: 1px solid #000;
            page-break-inside: avoid;
        }

        .box-title {
            margin: -7px -7px 7px;
            padding: 4px 7px;
            border-bottom: 1px solid #000;
            background: #e9e9e9;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .texto-observacao {
            min-height: 26px;
            white-space: pre-wrap;
        }

        table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 4px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        th {
            background: #e9e9e9;
            font-size: 8.8px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }

        td {
            font-size: 9px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .produto-nome {
            font-weight: 800;
        }

        .produto-codigo {
            display: block;
            margin-top: 2px;
            font-size: 8px;
        }

        .campo-manual {
            min-height: 24px;
        }

        .motivo-divergencia {
            min-height: 28px;
        }

        .checklist {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5px 10px;
        }

        .check-item {
            min-height: 18px;
            font-weight: 700;
        }

        .checkbox {
            display: inline-block;
            width: 13px;
            height: 13px;
            margin-right: 4px;
            border: 1px solid #000;
            vertical-align: -2px;
        }

        .linha-manual {
            display: inline-block;
            min-width: 100px;
            min-height: 15px;
            border-bottom: 1px solid #000;
        }

        .avaliacao {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .assinaturas {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 27px;
            page-break-inside: avoid;
        }

        .assinatura {
            padding-top: 4px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 9px;
            font-weight: 800;
        }

        .rodape {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding-top: 4px;
            border-top: 1px solid #000;
            font-size: 8.5px;
        }

        .alerta-interno {
            margin-bottom: 7px;
            padding: 6px;
            border: 1px dashed #000;
            text-align: center;
            font-weight: 800;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

<div class="no-print">
    <button type="button" onclick="window.print()">
        Imprimir documento
    </button>
</div>

<?php $__currentLoopData = $vias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indexVia => $via): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <section class="pagina-romaneio">

        <div class="cabecalho">
            <div class="titulo"><?php echo e($tituloDocumento); ?></div>
            <div class="subtitulo"><?php echo e($subtituloDocumento); ?></div>
            <div class="via"><?php echo e($via); ?></div>

            <div class="grid grid-4" style="margin-top: 7px;">
                <div class="campo">
                    <span class="rotulo">Romaneio</span>
                    <span class="valor"><?php echo e($codigoRomaneio); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Status</span>
                    <span class="valor"><?php echo e($statusFormatado); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Emissão</span>
                    <span class="valor">
                        <?php echo e(optional($romaneio->data_emissao ?? $romaneio->created_at)->format('d/m/Y H:i') ?? '—'); ?>

                    </span>
                </div>

                <div class="campo">
                    <span class="rotulo">Via</span>
                    <span class="valor"><?php echo e($indexVia + 1); ?> de <?php echo e(count($vias)); ?></span>
                </div>
            </div>
        </div>

        <?php if($documentoSeparacao): ?>
            <div class="alerta-interno">
                Documento interno para separação, contagem física, conferência e avaliação da carga.
                Não representa liberação definitiva do veículo.
            </div>
        <?php endif; ?>

        <div class="box">
            <div class="box-title">Documentos vinculados</div>

            <div class="grid grid-4">
                <div class="campo">
                    <span class="rotulo">Entrega</span>
                    <span class="valor"><?php echo e($entrega?->codigo_entrega ?? ($entrega ? 'ENT-' . $entrega->id : '—')); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Venda</span>
                    <span class="valor"><?php echo e($entrega?->venda_id ? 'VEN-' . $entrega->venda_id : '—'); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Orçamento</span>
                    <span class="valor">
                        <?php echo e($orcamento?->codigo_orcamento
                            ? 'ORÇ-' . $orcamento->codigo_orcamento
                            : ($orcamento?->id ? 'ORÇ-' . $orcamento->id : '—')); ?>

                    </span>
                </div>

                <div class="campo">
                    <span class="rotulo">Data do orçamento</span>
                    <span class="valor">
                        <?php echo e($orcamento?->data_orcamento
                            ? \Carbon\Carbon::parse($orcamento->data_orcamento)->format('d/m/Y')
                            : '—'); ?>

                    </span>
                </div>
            </div>

            <div class="grid grid-4" style="margin-top: 6px;">
                <div class="campo">
                    <span class="rotulo">Situação do orçamento</span>
                    <span class="valor"><?php echo e($orcamento?->status ?? '—'); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Tipo de entrega</span>
                    <span class="valor"><?php echo e($orcamento?->tipo_entrega ?? $entrega?->tipo_entrega ?? '—'); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Valor total</span>
                    <span class="valor">
                        <?php echo e($orcamento
                            ? 'R$ ' . number_format((float) $orcamento->total, 2, ',', '.')
                            : '—'); ?>

                    </span>
                </div>

                <div class="campo">
                    <span class="rotulo">Frete</span>
                    <span class="valor">
                        <?php echo e($orcamento
                            ? 'R$ ' . number_format((float) $orcamento->valor_frete, 2, ',', '.')
                            : '—'); ?>

                    </span>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">Cliente e destino</div>

            <div class="grid grid-3">
                <div class="campo">
                    <span class="rotulo">Cliente</span>
                    <span class="valor"><?php echo e($cliente?->nome ?? 'Cliente não informado'); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Documento</span>
                    <span class="valor"><?php echo e($documentoCliente); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Telefone</span>
                    <span class="valor"><?php echo e($telefoneCliente); ?></span>
                </div>
            </div>

            <div class="grid grid-3" style="margin-top: 6px;">
                <div class="campo" style="grid-column: span 2;">
                    <span class="rotulo">Endereço de entrega</span>
                    <span class="valor"><?php echo e($enderecoEntrega); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Responsável pelo recebimento</span>
                    <span class="valor"><?php echo e($entrega?->responsavel_recebimento ?? 'Não informado'); ?></span>
                </div>
            </div>

            <div class="grid grid-3" style="margin-top: 6px;">
                <div class="campo">
                    <span class="rotulo">Data prevista</span>
                    <span class="valor">
                        <?php echo e($dataPrevista
                            ? \Carbon\Carbon::parse($dataPrevista)->format('d/m/Y')
                            : 'Não informada'); ?>

                    </span>
                </div>

                <div class="campo">
                    <span class="rotulo">Período</span>
                    <span class="valor"><?php echo e($entrega?->periodo_entrega ?? 'Não informado'); ?></span>
                </div>

                <div class="campo">
                    <span class="rotulo">Contato no recebimento</span>
                    <span class="valor"><?php echo e($entrega?->telefone_recebimento ?? $telefoneCliente); ?></span>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">Observações comerciais e logísticas</div>

            <div class="grid grid-2">
                <div class="campo">
                    <span class="rotulo">Observações do orçamento</span>
                    <div class="texto-observacao"><?php echo e($observacaoOrcamento); ?></div>
                </div>

                <div class="campo">
                    <span class="rotulo">Observações da entrega / romaneio</span>
                    <div class="texto-observacao">
                        <?php echo e($observacaoEntrega); ?>

                        <?php if(!empty($romaneio->observacao)): ?>
                            <?php echo e("\n" . $romaneio->observacao); ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-title">Produtos e controle da separação</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 27%;">Produto</th>
                        <th style="width: 13%;">Localização</th>
                        <th style="width: 9%;">Prevista</th>
                        <th style="width: 10%;">Encontrada</th>
                        <th style="width: 9%;">Divergência</th>
                        <th style="width: 10%;">Conferida</th>
                        <th style="width: 18%;">Motivo / observação</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $romaneio->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $entregaItem = $item->entregaItem;

                            $produto = $entregaItem?->produto
                                ?? $entregaItem?->vendaItem?->produto
                                ?? $entregaItem?->itemOrcamento?->produto;

                            $localizacao = $produto?->localizacao_estoque ?? '—';

                            $quantidadePrevista = (float) ($item->quantidade_prevista ?? 0);
                            $quantidadeSeparada = (float) ($item->quantidade_separada ?? 0);
                            $quantidadeConferida = (float) ($item->quantidade_conferida ?? 0);

                            $divergenciaRegistrada = max(
                                $quantidadePrevista - $quantidadeSeparada,
                                0
                            );
                        ?>

                        <tr>
                            <td class="text-center"><?php echo e($i + 1); ?></td>

                            <td>
                                <span class="produto-nome">
                                    <?php echo e($produto?->nome ?? 'Produto não identificado'); ?>

                                </span>

                                <span class="produto-codigo">
                                    Cód.: <?php echo e($produto?->codigo ?? $produto?->id ?? '—'); ?>

                                </span>
                            </td>

                            <td><?php echo e($localizacao); ?></td>

                            <td class="text-end">
                                <?php echo e(number_format($quantidadePrevista, 2, ',', '.')); ?>

                            </td>

                            <td class="campo-manual text-center">
                                <?php if(!$documentoSeparacao && $quantidadeSeparada > 0): ?>
                                    <?php echo e(number_format($quantidadeSeparada, 2, ',', '.')); ?>

                                <?php endif; ?>
                            </td>

                            <td class="campo-manual text-center">
                                <?php if(!$documentoSeparacao && $divergenciaRegistrada > 0): ?>
                                    <?php echo e(number_format($divergenciaRegistrada, 2, ',', '.')); ?>

                                <?php endif; ?>
                            </td>

                            <td class="campo-manual text-center">
                                <?php if(!$documentoSeparacao && $quantidadeConferida > 0): ?>
                                    <?php echo e(number_format($quantidadeConferida, 2, ',', '.')); ?>

                                <?php endif; ?>
                            </td>

                            <td class="motivo-divergencia">
                                <?php echo e($item->observacao ?? ''); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                Nenhum item encontrado para este romaneio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($documentoSeparacao): ?>
            <div class="box">
                <div class="box-title">Avaliação operacional da carga</div>

                <div class="avaliacao">
                    <div class="checklist">
                        <div class="check-item"><span class="checkbox"></span>Carga completa</div>
                        <div class="check-item"><span class="checkbox"></span>Carga com divergência</div>
                        <div class="check-item"><span class="checkbox"></span>Cabe em um caminhão</div>
                        <div class="check-item"><span class="checkbox"></span>Necessita mais de um caminhão</div>
                        <div class="check-item"><span class="checkbox"></span>Necessita mais de uma viagem</div>
                        <div class="check-item"><span class="checkbox"></span>Utilizar o mesmo veículo em outra viagem</div>
                        <div class="check-item"><span class="checkbox"></span>Agrupar com outra entrega</div>
                        <div class="check-item"><span class="checkbox"></span>Requer nova programação</div>
                    </div>

                    <div>
                        <div style="margin-bottom: 6px;">
                            <strong>Quantidade estimada de veículos:</strong>
                            <span class="linha-manual"></span>
                        </div>

                        <div style="margin-bottom: 6px;">
                            <strong>Quantidade estimada de viagens:</strong>
                            <span class="linha-manual"></span>
                        </div>

                        <div>
                            <strong>Providência para divergências:</strong>
                            <div style="height: 32px; margin-top: 4px; border: 1px solid #000;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box">
                <div class="box-title">Observações da expedição</div>
                <div style="height: 52px;"></div>
            </div>
        <?php else: ?>
            <div class="box">
                <div class="box-title">Veículo e responsáveis</div>

                <div class="grid grid-4">
                    <div class="campo">
                        <span class="rotulo">Motorista</span>
                        <span class="valor">
                            <?php echo e($romaneio->motorista?->nome
                                ?? $romaneio->motorista?->name
                                ?? 'Não definido'); ?>

                        </span>
                    </div>

                    <div class="campo">
                        <span class="rotulo">Veículo</span>
                        <span class="valor">
                            <?php echo e($romaneio->veiculo?->placa
                                ?? $romaneio->veiculo?->descricao
                                ?? 'Não definido'); ?>

                        </span>
                    </div>

                    <div class="campo">
                        <span class="rotulo">Início do carregamento</span>
                        <span class="valor">
                            <?php echo e(optional($romaneio->data_inicio_carregamento)->format('d/m/Y H:i') ?? '—'); ?>

                        </span>
                    </div>

                    <div class="campo">
                        <span class="rotulo">Saída</span>
                        <span class="valor">
                            <?php echo e(optional($romaneio->data_saida)->format('d/m/Y H:i') ?? '—'); ?>

                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="assinaturas">
            <?php if($documentoSeparacao): ?>
                <div class="assinatura">Responsável pela separação</div>
                <div class="assinatura">Responsável pela conferência</div>
                <div class="assinatura">Responsável pela expedição</div>
            <?php else: ?>
                <div class="assinatura">Expedição</div>
                <div class="assinatura">Motorista</div>
                <div class="assinatura">Cliente / recebedor</div>
            <?php endif; ?>
        </div>

        <div class="rodape">
            <span><?php echo e($codigoRomaneio); ?></span>
            <span><?php echo e($via); ?></span>
            <span>Impresso em <?php echo e(now()->format('d/m/Y H:i')); ?></span>
        </div>

    </section>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>

</body>
</html>
'''

path = Path('/mnt/data/imprimir.blade.php')
path.write_text(content, encoding='utf-8')
print(path)
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/romaneios/separacao.blade.php ENDPATH**/ ?>