

<?php $__env->startSection('content'); ?>

    <div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9; display: flex; gap: 20px;">

        <!-- LADO ESQUERDO: Monitoramento dos Caixas da Rede em Tempo Real -->
        <div style="flex: 2; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h2 style="color: #1e3a8a; margin-top: 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                <span>🔴</span> Monitoramento de Caixas Abertos na Rede
            </h2>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px; text-align: left;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px; color: #64748b; font-size: 14px;">PDV / Terminal</th>
                        <th style="padding: 12px; color: #64748b; font-size: 14px;">Operador do Caixa</th>
                        <th style="padding: 12px; color: #64748b; font-size: 14px;">Data Abertura</th>
                        <th style="padding: 12px; text-align: right; color: #64748b; font-size: 14px;">Dinheiro em Gaveta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $caixasAbertos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-weight: bold; color: #334155;">
                            Terminal #<?php echo e($cx->numero_terminal ?? $cx->id); ?>

                        </td>
                        <td style="padding: 12px; color: #475569;">
                            <?php echo e($cx->usuario->name ?? 'Não identificado'); ?>

                        </td>
                        <td style="padding: 12px; color: #475569; font-size: 13px;">
                            <?php echo e($cx->created_at->format('d/m/Y H:i')); ?>

                        </td>
                        <td style="padding: 12px; text-align: right; font-weight: bold; color: <?php echo e($cx->saldo_dinheiro_atual > 0 ? '#16a34a' : '#dc2626'); ?>; font-size: 15px;">
                            R$ <?php echo e(number_format($cx->saldo_dinheiro_atual, 2, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">
                            Nenhum caixa aberto encontrado na rede no momento.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- LADO DIREITO: Emissor de Guia de Requerimento com Rateio Automatizado -->
        <div style="flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 400px; height: fit-content;">
            <h3 style="color: #dc2626; margin-top: 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span>📄</span> Nova Requisição de Saída
            </h3>
            
            <form id="formRequisicao" onsubmit="processarSaidaMultiCaixa(event)">
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-weight:bold; margin-bottom: 5px; color:#475569; font-size: 14px;">Finalidade / Motivo:</label>
                    <input type="text" id="reqFinalidade" placeholder="Ex: Pagar Fornecedor XYZ, Marmitex" required 
                        style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom: 5px; color:#475569; font-size: 14px;">Valor Total Necessário (R$):</label>
                    <input type="number" step="0.01" min="0.01" id="reqValor" placeholder="0,00" required 
                        style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <button type="submit" id="btnConfirmar" style="width:100%; background-color:#dc2626; color:white; font-weight:bold; border:none; padding:12px; border-radius:6px; cursor:pointer; font-size: 14px;">
                    🖨️ Ratear, Imprimir e Gravar Saídas
                </button>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<script>
    // Transforma os caixas vindos do Eloquent para leitura do Javascript
    const caixasRede = [
        <?php $__currentLoopData = $caixasAbertos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        {
            id: parseInt("<?php echo e($cx->id); ?>"),
            terminal: "Terminal #<?php echo e($cx->numero_terminal ?? $cx->id); ?>",
            operador: "<?php echo e($cx->usuario->name ?? 'Não identificado'); ?>",
            saldo: parseFloat("<?php echo e($cx->saldo_dinheiro_atual); ?>")
        },
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    ];

    function processarSaidaMultiCaixa(event) {
        event.preventDefault();
        
        const finalidade = document.getElementById('reqFinalidade').value;
        const valorRequisitado = parseFloat(document.getElementById('reqValor').value);
        const dataHora = new Date().toLocaleString('pt-BR');
        const btn = document.getElementById('btnConfirmar');
        
        const saldoTotalRede = caixasRede.reduce((total, cx) => total + cx.saldo, 0);
        
        if (valorRequisitado > saldoTotalRede) {
            alert(`Operação Recusada!\n\nO valor solicitado (R$ ${valorRequisitado.toLocaleString('pt-BR', {minimumFractionDigits: 2})}) é maior do que o saldo total disponível na rede (R$ ${saldoTotalRede.toLocaleString('pt-BR', {minimumFractionDigits: 2})}).`);
            return;
        }

        let valorRestante = valorRequisitado;
        let cuponsHTML = "";
        let dadosRateioBanco = []; // Guardará os IDs e valores para enviar ao Laravel

        // 1. Executa o algoritmo de fracionamento do dinheiro físico
        for (let cx of caixasRede) {
            if (valorRestante <= 0) break;
            if (cx.saldo <= 0) continue;

            let valorRetiradaCaixa = Math.min(valorRestante, cx.saldo);
            valorRestante -= valorRetiradaCaixa;

            // Guarda as informações de inserção do banco de dados
            dadosRateioBanco.push({
                caixa_id: cx.id,
                valor: valorRetiradaCaixa
            });

            // Monta o template estético das guias de impressão
            cuponsHTML += `
                <div style="width: 280px; font-family: 'Courier New', Courier, monospace; font-size: 11px; padding: 10px; line-height: 1.4; margin-bottom: 40px; page-break-after: always; color: #000;">
                    <div style="text-align:center; font-weight:bold; font-size:12px;">*** GUIA DE RETIRADA DE CAIXA ***</div>
                    <div style="text-align:center; font-weight:bold; margin-bottom:10px;">--- VIA 1: GAVETA DO CAIXA ---</div>
                    <p><b>Data/Hora:</b> ${dataHora}</p>
                    <p><b>Identificação:</b> ${cx.terminal}</p>
                    <p><b>Operador do PDV:</b> ${cx.operador}</p>
                    <p><b>Motivo Original:</b> ${finalidade}</p>
                    <div style="font-size:13px; background:#e2e8f0; padding:5px; font-weight:bold; border:1px solid #000;">
                        RETIRAR DESTE CAIXA: R$ ${valorRetiradaCaixa.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </div>
                    <br><br>
                    <div style="border-top: 1px dashed #000; text-align:center; padding-top:5px; margin-top:10px;">Assinatura do Operador</div>
                    <br><br>
                    <div style="border-top: 2px dashed #000; margin: 20px 0;"></div>
                    <div style="text-align:center; font-weight:bold; font-size:12px;">*** GUIA DE RETIRADA DE CAIXA ***</div>
                    <div style="text-align:center; font-weight:bold; margin-bottom:10px;">--- VIA 2: COMPROVANTE GESTÃO ---</div>
                    <p><b>Data/Hora:</b> ${dataHora}</p>
                    <p><b>Identificação:</b> ${cx.terminal}</p>
                    <p><b>Operador do PDV:</b> ${cx.operador}</p>
                    <p><b>Motivo Original:</b> ${finalidade}</p>
                    <div style="font-size:13px; background:#e2e8f0; padding:5px; font-weight:bold; border:1px solid #000;">
                        RETIRAR DESTE CAIXA: R$ ${valorRetiradaCaixa.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </div>
                    <br><br>
                    <div style="border-top: 1px dashed #000; text-align:center; padding-top:5px; margin-top:10px;">Assinatura do Gerente Responsável</div>
                </div>
            `;
        }

        // 2. Dispara a impressão física das guias térmicas no navegador
        const telaImpressao = window.open('', '_blank', 'width=340,height=600');
        telaImpressao.document.write('<html><head><title>Imprimir Comprovantes</title></head><body style="margin:0;" onload="window.print(); window.close();">');
        telaImpressao.document.write(cuponsHTML);
        telaImpressao.document.write('</body></html>');
        telaImpressao.document.close();

        // Bloqueia o botão para evitar cliques duplos durante o processamento do AJAX
        btn.disabled = true;
        btn.innerText = "Gravando transações no banco...";

            // 3. Comunicação via Fetch API (AJAX) para persistir os dados no Laravel sob padrão ACID
        fetch("<?php echo e(route('gerencia.caixa.registrar_saida_lote')); ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
            },
            body: JSON.stringify({
                finalidade: finalidade,
                rateio: dadosRateioBanco
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert("Sucesso! Guia impressa e movimentações gravadas de forma segura no banco de dados.");
                window.location.reload(); 
            } else {
                alert("Erro no processamento ACID: " + data.message);
                btn.disabled = false;
                btn.innerText = "🖨️ Ratear, Imprimir e Gravar Saídas";
            }
        })
        .catch(error => {
            alert("Falha crítica de comunicação com o servidor de banco de dados.");
            console.error("Erro técnico capturado:", error);
            btn.disabled = false;
            btn.innerText = "🖨️ Ratear, Imprimir e Gravar Saídas";
        });
    }
</script>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/caixa/painel_saidas.blade.php ENDPATH**/ ?>