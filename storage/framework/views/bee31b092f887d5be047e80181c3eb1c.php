<style>
    .tabela-movimentacoes {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    /* Overflow controlado e texto cortado */
    .tabela-movimentacoes th,
    .tabela-movimentacoes td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 10px 8px;
    }

    /* Larguras das colunas */
    .tabela-movimentacoes th:nth-child(1),
    .tabela-movimentacoes td:nth-child(1) { width: 50px; }   /* ID */
    .tabela-movimentacoes th:nth-child(2),
    .tabela-movimentacoes td:nth-child(2) { width: 150px; }  /* Tipo */
    .tabela-movimentacoes th:nth-child(3),
    .tabela-movimentacoes td:nth-child(3) { width: 180px; }  /* Valor */
    .tabela-movimentacoes th:nth-child(4),
    .tabela-movimentacoes td:nth-child(4) { width: 100px; }   /* Origem */
    .tabela-movimentacoes th:nth-child(5),
    .tabela-movimentacoes td:nth-child(5) { width: 150px; }  /* Data */
    .tabela-movimentacoes th:nth-child(6),
    .tabela-movimentacoes td:nth-child(6) { width: auto; }   /* Observação */

    /* Zebra striping suave */
    .tabela-movimentacoes tbody tr:nth-child(odd) {
        background-color: #f9f9f9; /* linha clara */
    }

    .tabela-movimentacoes tbody tr:nth-child(even) {
        background-color: #ffffff; /* linha branca */
    }

    /* Efeito hover */
    .tabela-movimentacoes tbody tr:hover {
        background-color: #e0f3ff; /* destaque suave */
    }

    .movimentacoes-container {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        font-size: 0.95rem;
    }

    .movimentacao-item:hover {
        background-color: #f8f9fa;
    }

    .movimentacoes-container .row {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
    }

    .movimentacoes-container .col-1,
    .movimentacoes-container .col-2 {
        padding: 0.5rem;
        border-right: 1px solid #dee2e6;
    }

    .movimentacoes-container .col-2:last-child,
    .movimentacoes-container .col-1:last-child {
        border-right: none;
    }

    .bg-light {
        background-color: #f1f3f5 !important;
    }

    .fw-bold {
        font-weight: 600;
    }
</style>



<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h3>Fechamento / Auditoria de Caixa #<?php echo e($caixa->id); ?> - Terminal <?php echo e($caixa->terminal_id); ?></h3>

    
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card p-2 ">
                <div class="card-header fs-5 bg-primary text-white fw-bold"> Abertura:</div>
                <strong>✅ Abertura:</strong> R$ <?php echo e(number_format($caixa->valor_abertura, 2, ',', '.')); ?><br>
                <strong>Fundo de Troco:</strong> R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?><br>
                <strong>Data Abertura:</strong> <?php echo e($caixa->data_abertura->format('d/m/Y H:i')); ?><br>
                <strong>Status:</strong> <?php echo e(ucfirst($caixa->status)); ?>

            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <div class="card-header fs-5 bg-primary text-white fw-bold"> Total Entradas / Saidas:</div>
                <strong>✅ Total Entradas:</strong> R$ <?php echo e(number_format($total_entradas, 2, ',', '.')); ?><br>
                <strong>Total Saídas:</strong> R$ <?php echo e(number_format($total_saidas, 2, ',', '.')); ?><br>
                <!-- <span class="text-primary fw-bold"> ✅ Total Esperado Dinheiro:</span> R$ <?php echo e(number_format($caixa->fundo_troco + ($totaisPorForma['dinheiro'] ?? 0), 2, ',', '.')); ?><br> -->
                <span class="text-primary fw-bold"> ✅ Total Esperado Dinheiro:</span> R$ <?php echo e(number_format($total_esperado, 2, ',', '.')); ?><br>

                <strong>Divergência:</strong> 
                <span class="<?php echo e($divergencia != 0 ? 'text-danger fw-bold' : 'text-success fw-bold'); ?>">
                    R$ <?php echo e(number_format($divergencia, 2, ',', '.')); ?>

                </span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <div class="card-header fs-5 bg-primary text-white fw-bold">Vendas PDV (Sistema):</div>
                <strong>✅  Sistema</strong>
                <ul class="list-unstyled mb-0">
                    <?php $__currentLoopData = ['dinheiro','pix','carteira','cartao_debito','cartao_credito']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forma): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e(ucfirst(str_replace('_',' ',$forma))); ?>: 
                            R$ <?php echo e(number_format($totaisPorForma[$forma] ?? 0, 2, ',', '.')); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <ul class="list-unstyled mb-0">
                     ✅ 
                       <strong>Total Sistema:</strong>
                        R$ <?php echo e(number_format($total_entradas, 2, ',', '.')); ?>

                        <div class="text-muted text-xs" style="font-size: 0.75rem;">
                            Pagamento <strong>Carteira</strong> não é contabilizado no fechamento do caixa
                        </div>
                </ul>
            </div>
        </div>
    </div>

    
    
    <?php if($caixa->estaAberto() && auth()->user()->can('fechar-caixa')): ?>
   

    <form method="POST" action="<?php echo e(route('fechamento.fechar', $caixa->id)); ?>">
        <?php echo csrf_field(); ?>
        <h5>Valores Físicos Conferidos caixa </h5>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="dinheiro" class="form-label">Dinheiro</label>
                <input type="text" class="form-control" name="dinheiro" id="dinheiro" 
                       value="<?php echo e(number_format($totaisPorForma['dinheiro'] ?? 0, 2, ',', '.')); ?>">
            </div>
            <div class="col-md-4">
                <label for="pix" class="form-label">Pix</label>
                <input type="text" class="form-control" name="pix" id="pix" 
                       value="<?php echo e(number_format($totaisPorForma['pix'] ?? 0, 2, ',', '.')); ?>">
            </div>
            
            <div class="col-md-4">
                <label for="carteira" class="form-label">Carteira</label>
                <input type="text" class="form-control" name="carteira" id="carteira" 
                       value="<?php echo e(number_format($totaisPorForma['carteira'] ?? 0, 2, ',', '.')); ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cartao_debito" class="form-label">Cartão Débito</label>
                <input type="text" class="form-control" name="cartao_debito" id="cartao_debito" 
                       value="<?php echo e(number_format($totaisPorForma['cartao_debito'] ?? 0, 2, ',', '.')); ?>">
            </div>
            <div class="col-md-6">
                <label for="cartao_credito" class="form-label">Cartão Crédito</label>
                <input type="text" class="form-control" name="cartao_credito" id="cartao_credito" 
                       value="<?php echo e(number_format($totaisPorForma['cartao_credito'] ?? 0, 2, ',', '.')); ?>">
            </div>
        </div>
           <?php if($vm->semMovimento): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Motivo do fechamento sem movimento
                    </label>
                    <textarea name="motivo_fechamento"
                            class="form-control"
                            required
                            placeholder="Ex.: falha no terminal, pinpad inoperante, abertura indevida..."></textarea>
                </div>
            <?php endif; ?>

        <button type="submit" class="btn btn-success">Fechar Caixa</button>
    </form>
    <?php endif; ?>

        
         
        
        
        <div class="col-12 mb-4">
            <div class="card-header fs-5 bg-success p-1 text-white fw-bold"> Movimentações - Recebimento Carteira</div>
            <div class="movimentacoes-container">

            
            <div class="row bg-light fw-bold py-2 px-3 border-bottom">
                <div class="col-2">Tipo</div>
                <div class="col-2">Forma</div>
                <div class="col-2">Valor</div>
                <div class="col-1">Origem</div>
                <div class="col-2">Data</div>
                <div class="col-3">Observação</div>
            </div>

           
            <?php
                // 1️⃣ Mantém o seu filtro original de recebimento de carteiras antigo intacto
                $carteiraMovimentacoes = $caixa->movimentacoes->filter(function($mov) {
                    return in_array($mov->tipo, ['entrada_pagto_carteira', 'entrada']);
                });

                $movimentacoesAgrupadasCarteira = $carteiraMovimentacoes->groupBy(function($mov) {
                    return strtolower(trim($mov->forma_pagamento));
                });

                // 2️⃣ INTEGRAÇÃO DOS TOTAIS BASEADO NA COLEÇÃO DE MOVIMENTAÇÕES EXISTENTE
                $valorAberturaFundo = (float) $caixa->fundo_troco;
                
                // Filtra todas as vendas brutas lançadas no PDV (R$ 1.942,00)
                $vendasBrutasPDV = (float) $caixa->movimentacoes
                    ->where('tipo', 'venda')
                    ->sum('valor');
                
                // Isola as vendas feitas em carteira (fiado) hoje para retirá-las (R$ 48,00)
                $vendasFiadoHoje = (float) $caixa->movimentacoes
                    ->where('tipo', 'venda')
                    ->where('forma_pagamento', 'carteira')
                    ->sum('valor');
                
                // Soma os recebimentos REAIS de parcelas pagas no dia
                $recebimentoCarteiraReal = (float) $carteiraMovimentacoes->sum('valor');

                // EQUAÇÃO COMPLETA: (Abertura + Vendas Brutas - Fiado Hoje + Recebimentos Reais) - Sangrias
                $totalMovimentadoComAbertura = ($valorAberturaFundo + $vendasBrutasPDV - $vendasFiadoHoje + $recebimentoCarteiraReal) - (float) $total_sangrias;
            ?>


            <?php $__empty_1 = true; $__currentLoopData = $movimentacoesAgrupadasCarteira; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formaGrupo => $itensDoGrupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $totalDoGrupo = $itensDoGrupo->sum('valor');
                    $primeiroItem = $itensDoGrupo->first();
                ?>
                <div class="row py-2 px-3 border-bottom align-items-center movimentacao-item">
                    <div class="col-2">
                        <?php echo e($itensDoGrupo->pluck('tipo')->unique()->count() > 1 ? 'Entradas' : ucfirst(str_replace('_', ' ', $primeiroItem->tipo))); ?>

                    </div>
                    
                    <div class="col-2 font-weight-bold">
                        <?php echo e(ucfirst(str_replace('_', ' ', $formaGrupo))); ?>

                    </div>
                    
                    <div class="col-2 text-success font-weight-bold">
                        R$ <?php echo e(number_format($totalDoGrupo, 2, ',', '.')); ?>

                    </div>
                    
                    <div class="col-1">
                         Caixa <?php echo e($caixa->id); ?>

                    </div>
                    
                    <div class="col-2">
                         <?php echo e($itensDoGrupo->max('data_movimentacao') ? \Carbon\Carbon::parse($itensDoGrupo->max('data_movimentacao'))->format('d/m/Y') : ''); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="row py-2 px-3 border-bottom text-muted justify-content-center">Nenhum recebimento de carteira neste turno.</div>
            <?php endif; ?>
            
            <strong>✅ Total Carteira:</strong> R$ <?php echo e(number_format($carteiraMovimentacoes->sum('valor'), 2, ',', '.')); ?><br>
            
            </div>
        </div>

        
        
        
        <div class="col-12">
            <div class="card-header fs-5 bg-primary p-1 text-white fw-bold"> Movimentações do Caixa - Gaveta</div>
            <div class="movimentacoes-container">

                
                <div class="row bg-light fw-bold py-2 px-3 border-bottom">
                    <div class="col-2">Tipo</div>
                    <div class="col-2">Forma</div>
                    <div class="col-2">Valor Total</div>
                    <div class="col-1">Origem</div>
                    <div class="col-2">Última Ação</div>
                    <div class="col-3">Detalhamento</div>
                </div>

                
                <?php
                    // 1️⃣ Pega tudo que não é recebimento de carteira antiga
                    $geralMovimentacoes = $caixa->movimentacoes->filter(function($mov) {
                        return !in_array($mov->tipo, ['entrada_pagto_carteira', 'entrada']);
                    });

                    // 2️⃣ Isola APENAS as vendas reais do dia para o cálculo correto do rodapé do bloco
                    $vendasReaisDoBloco = $geralMovimentacoes->filter(function($mov) {
                        return $mov->tipo === 'venda';
                    });

                    // 3️⃣ AGRUPAMENTO ARQUITETURAL: Junta centenas de registros por Tipo + Forma de Pagamento
                    $movimentacoesVisualmenteAgrupadas = $geralMovimentacoes->groupBy(function($mov) {
                        return $mov->tipo . '_' . strtolower(trim($mov->forma_pagamento));
                    });
                ?>

                
                <?php $__empty_1 = true; $__currentLoopData = $movimentacoesVisualmenteAgrupadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chaveGrupo => $grupoItens): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        // Extrai as propriedades base usando o primeiro item do lote agrupado
                        $primeiroItem = $grupoItens->first();
                        $totalDoGrupo = $grupoItens->sum('valor');
                        $quantidadeNoGrupo = $grupoItens->count();
                        $ultimaDataDoGrupo = $grupoItens->max('data_movimentacao');

                        $isSaida = in_array($primeiroItem->tipo, ['sangria', 'saida_manual', 'despesa', 'saida']);
                    ?>
                    <div class="row py-2 px-3 border-bottom align-items-center movimentacao-item">
                        <div class="col-2">
                            <?php echo e(ucfirst(str_replace('_', ' ', $primeiroItem->tipo))); ?>

                        </div>
                        
                        <div class="col-2 font-weight-bold">
                            <?php echo e(ucfirst(str_replace('_', ' ', $primeiroItem->forma_pagamento ?? 'N/A'))); ?>

                        </div>
                        
                        
                        <div class="col-2 font-weight-bold <?php echo e($isSaida ? 'text-danger' : 'text-success'); ?>">
                            <?php echo e($isSaida ? '-' : ''); ?> R$ <?php echo e(number_format(abs($totalDoGrupo), 2, ',', '.')); ?>

                        </div>
                        
                        <div class="col-1">
                            Caixa <?php echo e($caixa->id); ?>

                        </div>
                        
                        <div class="col-2">
                            <?php echo e($ultimaDataDoGrupo ? \Carbon\Carbon::parse($ultimaDataDoGrupo)->format('d/m/Y H:i') : ''); ?>

                        </div>
                        
                        <div class="col-3 text-muted" style="font-size: 0.9rem;">
                            Consolidado (<?php echo e($quantidadeNoGrupo); ?> registro(s) no turno)
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="row py-2 px-3 border-bottom text-muted justify-content-center">Nenhuma movimentação geral de caixa registrada.</div>
                <?php endif; ?>
                
                
                <div class="mt-2 px-3">
                    <strong>✅ Total Movimentações:</strong> R$ <?php echo e(number_format($vendasReaisDoBloco->sum('valor'), 2, ',', '.')); ?><br>
                
                </div>

                <div class="p-3 bg-light border rounded mt-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="fs-5 text-dark">Total Geral Movimentações:</strong>
                            <div class="text-muted small mt-1">
                                (Abertura + Total Vendas + Total Recebimentos Carteira - Total Saídas/Sangrias)
                            </div>
                        </div>
                       <div class="text-end">
                            
                            <?php
                                $valorAberturaFundo = (float) $caixa->fundo_troco;
                                $vendasBrutasPDV    = (float) $vendasReaisDoBloco->sum('valor');
                                $vendasFiadoHoje    = (float) $vendasReaisDoBloco->where('forma_pagamento', 'carteira')->sum('valor');
                                
                                // Captura o total líquido real recebido das contas de carteira no turno
                                $recebimentoCarteiraReal = (float) ($carteiraMovimentacoes ?? collect())->sum('valor');
                                
                                // 🎯 FÓRMULA DE AUDITORIA COMPLETA E PERFEITA (Subtraindo o Fiado do Dia):
                                $totalMovimentadoComAbertura = ($valorAberturaFundo + $vendasBrutasPDV + $recebimentoCarteiraReal) - $vendasFiadoHoje - (float) $total_sangrias;
                            ?>

                            
                            <span class="fs-4 fw-bold text-success">R$ <?php echo e(number_format($totalMovimentadoComAbertura, 2, ',', '.')); ?></span>

                            
                            <div class="text-muted text-xs" style="font-size: 0.75rem;">
                                (Abertura: R$ <?php echo e(number_format($valorAberturaFundo, 2, ',', '.')); ?> + 
                                Vendas Totais: R$ <?php echo e(number_format($vendasBrutasPDV, 2, ',', '.')); ?> + 
                                Recebimentos Carteira: R$ <?php echo e(number_format($recebimentoCarteiraReal, 2, ',', '.')); ?> - 
                                Vendas Carteira (Fiado): R$ <?php echo e(number_format($vendasFiadoHoje, 2, ',', '.')); ?> - 
                                Sangrias/Saídas: R$ <?php echo e(number_format($total_sangrias + $total_saidas, 2, ',', '.')); ?>)
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="mt-3">
                    <?php if($caixa->estaAberto()): ?>
                        <a href="<?php echo e(route('fechamento.view', $caixa->id)); ?>" class="btn btn-primary">
                            Lançamento de Valores Manuais
                        </a>
                    <?php else: ?>
                        <button class="btn btn-primary" disabled title="Caixa já fechado">
                            Lançamento de Valores Manuais
                        </button>
                    <?php endif; ?>

                    <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/index.blade.php ENDPATH**/ ?>