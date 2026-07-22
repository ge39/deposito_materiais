<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota de Entrega <?php echo e($romaneio->codigo_romaneio ?? $romaneio->id); ?></title>

    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; font: 11px Arial, sans-serif; }
        .toolbar { display: flex; gap: 8px; margin-bottom: 10px; }
        .toolbar button { cursor: pointer; padding: 7px 12px; }
        .documento { min-height: 277mm; position: relative; padding-bottom: 24mm; }
        .cabecalho { display: grid; grid-template-columns: 1.25fr .75fr; border: 1.5px solid #111; }
        .empresa { padding: 10px; border-right: 1px solid #111; }
        .empresa-nome { font-size: 18px; font-weight: 800; text-transform: uppercase; }
        .empresa-dados { line-height: 1.45; margin-top: 5px; }
        .identificacao { text-align: center; padding: 10px; }
        .identificacao h1 { font-size: 17px; margin: 0 0 3px; }
        .nao-fiscal { border: 1px solid #111; display: inline-block; font-weight: 800; padding: 3px 7px; }
        .numero { font-size: 15px; font-weight: 800; margin-top: 8px; }
        .secao { border: 1px solid #111; margin-top: 8px; }
        .secao-titulo { background: #e9ecef; border-bottom: 1px solid #111; font-weight: 800; padding: 5px 7px; text-transform: uppercase; }
        .grade { display: grid; }
        .grade-2 { grid-template-columns: 1fr 1fr; }
        .grade-3 { grid-template-columns: 1fr 1fr 1fr; }
        .campo { min-height: 42px; padding: 6px 7px; border-right: 1px solid #111; }
        .campo:last-child { border-right: 0; }
        .rotulo { color: #444; font-size: 9px; font-weight: 800; text-transform: uppercase; }
        .valor { font-size: 11px; font-weight: 700; margin-top: 3px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #111; padding: 5px; }
        th { background: #e9ecef; font-size: 9px; text-transform: uppercase; }
        .centro { text-align: center; }
        .direita { text-align: right; }
        .observacao { min-height: 55px; padding: 7px; white-space: pre-wrap; }
        .declaracao { font-size: 10px; line-height: 1.45; padding: 7px; }
        .recebimento { display: grid; grid-template-columns: 1.5fr .7fr .7fr; }
        .assinatura { min-height: 72px; padding: 7px; border-right: 1px solid #111; position: relative; }
        .assinatura:last-child { border-right: 0; }
        .linha-assinatura { border-top: 1px solid #111; bottom: 7px; left: 7px; right: 7px; padding-top: 3px; position: absolute; text-align: center; }
        .rodape { bottom: 0; border-top: 1px solid #111; left: 0; padding-top: 5px; position: absolute; right: 0; text-align: center; font-size: 9px; }
        @media print { .toolbar { display: none; } }
    </style>
</head>
<body>
<?php
    $entrega = $romaneio->entrega;
    $orcamento = $entrega?->orcamento;
    $venda = $entrega?->venda;
    $cliente = $entrega?->cliente ?? $venda?->cliente ?? $orcamento?->cliente;

    $codigoRomaneio = $romaneio->codigo_romaneio ?? 'ROM-' . $romaneio->id;
    $codigoEntrega = $entrega?->codigo_entrega ?? ($entrega ? 'ENT-' . $entrega->id : '—');
    $codigoOrcamento = $orcamento?->codigo_orcamento ?? ($orcamento ? 'ORÇ-' . $orcamento->id : '—');
    $codigoVenda = $venda?->codigo_venda ?? ($venda ? 'VEN-' . $venda->id : '—');
    $numeroNota = 'NE-' . str_pad((string) ($entrega?->id ?? 0), 6, '0', STR_PAD_LEFT)
        . '-' . str_pad((string) $romaneio->id, 6, '0', STR_PAD_LEFT);

    $enderecoEmpresa = collect([
        $empresa?->endereco,
        $empresa?->numero,
        $empresa?->complemento,
        $empresa?->bairro,
        $empresa?->cidade,
        $empresa?->estado,
        $empresa?->cep,
    ])->filter()->implode(', ');

    $enderecoEntrega = $entrega?->endereco_entrega
        ?? $entrega?->endereco_entrega_concatenado
        ?? 'Endereço não informado';

    $observacao = $entrega?->observacao_entrega ?? $romaneio->observacao;
?>

<div class="toolbar">
    <button type="button" onclick="window.print()">Imprimir Nota de Entrega</button>
    <button type="button" onclick="window.close()">Fechar</button>
</div>

<main class="documento">
    <header class="cabecalho">
        <div class="empresa">
            <div class="empresa-nome"><?php echo e($empresa?->nome ?? config('app.name')); ?></div>
            <div class="empresa-dados">
                <?php if($empresa?->cnpj): ?><strong>CNPJ:</strong> <?php echo e($empresa->cnpj); ?><?php endif; ?>
                <?php if($empresa?->inscricao_estadual): ?> &nbsp; <strong>IE:</strong> <?php echo e($empresa->inscricao_estadual); ?><?php endif; ?>
                <?php if($enderecoEmpresa): ?><br><?php echo e($enderecoEmpresa); ?><?php endif; ?>
                <?php if($empresa?->telefone): ?><br><strong>Telefone:</strong> <?php echo e($empresa->telefone); ?><?php endif; ?>
                <?php if($empresa?->email): ?> &nbsp; <strong>E-mail:</strong> <?php echo e($empresa->email); ?><?php endif; ?>
            </div>
        </div>
        <div class="identificacao">
            <h1>NOTA DE ENTREGA</h1>
            <div class="nao-fiscal">DOCUMENTO NÃO FISCAL</div>
            <div class="numero"><?php echo e($numeroNota); ?></div>
            <div>Emissão: <?php echo e(now()->format('d/m/Y H:i')); ?></div>
        </div>
    </header>

    <section class="secao">
        <div class="secao-titulo">Documentos vinculados</div>
        <div class="grade grade-3">
            <div class="campo"><div class="rotulo">Entrega</div><div class="valor"><?php echo e($codigoEntrega); ?></div></div>
            <div class="campo"><div class="rotulo">Orçamento</div><div class="valor"><?php echo e($codigoOrcamento); ?></div></div>
            <div class="campo"><div class="rotulo">Venda</div><div class="valor"><?php echo e($codigoVenda); ?></div></div>
        </div>
        <div class="grade grade-2" style="border-top:1px solid #111">
            <div class="campo"><div class="rotulo">Romaneio</div><div class="valor"><?php echo e($codigoRomaneio); ?></div></div>
            <div class="campo"><div class="rotulo">Data prevista</div><div class="valor"><?php echo e(optional($entrega?->data_prevista)->format('d/m/Y') ?? optional($entrega?->data_entrega)->format('d/m/Y') ?? 'Não informada'); ?></div></div>
        </div>
    </section>

    <section class="secao">
        <div class="secao-titulo">Cliente e local de entrega</div>
        <div class="grade grade-2">
            <div class="campo"><div class="rotulo">Cliente</div><div class="valor"><?php echo e($cliente?->nome ?? $cliente?->razao_social ?? 'Não identificado'); ?></div></div>
            <div class="campo"><div class="rotulo">CPF/CNPJ</div><div class="valor"><?php echo e($cliente?->cpf_cnpj ?? $cliente?->cnpj ?? $cliente?->cpf ?? 'Não informado'); ?></div></div>
        </div>
        <div class="grade grade-2" style="border-top:1px solid #111">
            <div class="campo"><div class="rotulo">Endereço da entrega</div><div class="valor"><?php echo e($enderecoEntrega); ?></div></div>
            <div class="campo"><div class="rotulo">Contato</div><div class="valor"><?php echo e($cliente?->telefone ?? $entrega?->telefone_contato ?? 'Não informado'); ?></div></div>
        </div>
    </section>

    <section class="secao">
        <div class="secao-titulo">Transporte</div>
        <div class="grade grade-3">
            <div class="campo"><div class="rotulo">Motorista</div><div class="valor"><?php echo e($romaneio->motorista?->nome ?? $romaneio->motorista?->name ?? 'Não definido'); ?></div></div>
            <div class="campo"><div class="rotulo">Veículo / placa</div><div class="valor"><?php echo e($romaneio->veiculo?->placa ?? 'Não definido'); ?></div></div>
            <div class="campo"><div class="rotulo">Período</div><div class="valor"><?php echo e($entrega?->periodo_entrega ?? 'Não informado'); ?></div></div>
        </div>
    </section>

    <section class="secao">
        <div class="secao-titulo">Materiais enviados</div>
        <table>
            <thead><tr><th class="centro" style="width:7%">Item</th><th>Produto</th><th style="width:14%">Unidade</th><th class="direita" style="width:18%">Quantidade</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $romaneio->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indice => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $produto = $item->entregaItem?->vendaItem?->produto
                        ?? $item->entregaItem?->itemOrcamento?->produto;
                    $quantidade = (float) ($item->quantidade_conferida_saida ?? $item->quantidade_carregada ?? 0);
                ?>
                <tr>
                    <td class="centro"><?php echo e($indice + 1); ?></td>
                    <td><strong><?php echo e($produto?->nome ?? 'Produto não identificado'); ?></strong><?php if($produto?->id): ?><br><small>Código: <?php echo e($produto->id); ?></small><?php endif; ?></td>
                    <td><?php echo e($produto?->unidade_medida ?? $produto?->unidade ?? 'UN'); ?></td>
                    <td class="direita"><?php echo e(number_format($quantidade, 2, ',', '.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="centro">Nenhum material encontrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="secao">
        <div class="secao-titulo">Observações da entrega</div>
        <div class="observacao"><?php echo e(filled($observacao) ? $observacao : 'Sem observações.'); ?></div>
    </section>

    <section class="secao">
        <div class="secao-titulo">Comprovante de recebimento</div>
        <div class="declaracao">Declaro que recebi os materiais relacionados nesta Nota de Entrega, nas quantidades e condições verificadas no momento do recebimento. Eventuais ressalvas deverão ser registradas no campo de observações antes da assinatura.</div>
        <div class="recebimento" style="border-top:1px solid #111">
            <div class="assinatura"><div class="rotulo">Nome completo do recebedor</div><div class="linha-assinatura">Assinatura do recebedor</div></div>
            <div class="assinatura"><div class="rotulo">CPF ou documento</div><div class="linha-assinatura">Documento</div></div>
            <div class="assinatura"><div class="rotulo">Data e hora</div><div class="linha-assinatura">____/____/______ &nbsp; ____:____</div></div>
        </div>
    </section>

    <footer class="rodape"><?php echo e($numeroNota); ?> — <?php echo e($codigoRomaneio); ?> — Documento não fiscal destinado exclusivamente à comprovação da entrega de materiais.</footer>
</main>

<script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\romaneios\nota-entrega.blade.php ENDPATH**/ ?>