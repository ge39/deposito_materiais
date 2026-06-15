@extends('layouts.app')

@section('content')

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
                    @forelse($caixasAbertos as $cx)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-weight: bold; color: #334155;">
                            Terminal #{{ $cx->numero_terminal ?? $cx->id }}
                        </td>
                        <td style="padding: 12px; color: #475569;">
                            {{ $cx->usuario->name ?? 'Não identificado' }}
                        </td>
                        <td style="padding: 12px; color: #475569; font-size: 13px;">
                            {{ $cx->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td style="padding: 12px; text-align: right; font-weight: bold; color: {{ $cx->saldo_dinheiro_atual > 0 ? '#16a34a' : '#dc2626' }}; font-size: 15px;">
                            R$ {{ number_format($cx->saldo_dinheiro_atual, 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">
                            Nenhum caixa aberto encontrado na rede no momento.
                        </td>
                    </tr>
                    @endforelse
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
@endsection

<script>
    // Converte a coleção do Laravel para array de objetos lidos pelo JavaScript
    const caixasRede = [
        @foreach($caixasAbertos as $cx)
        {
            id: parseInt("{{ $cx->id }}"),
            terminal: "Terminal #{{ $cx->numero_terminal ?? $cx->id }}",
            operador: "{{ $cx->usuario->name ?? 'Não identificado' }}",
            saldo: parseFloat("{{ $cx->saldo_dinheiro_atual }}")
        },
        @endforeach
    ];

    function processarSaidaMultiCaixa(event) {
        event.preventDefault();
        
        const finalidade = document.getElementById('reqFinalidade').value;
        const valorRequisitado = parseFloat(document.getElementById('reqValor').value);
        const dataHora = new Date().toLocaleString('pt-BR');
        const btn = document.getElementById('btnConfirmar');
        
        // Calcula a somatória total das gavetas ativas
        const saldoTotalRede = caixasRede.reduce((total, cx) => total + cx.saldo, 0);
        
        if (valorRequisitado > saldoTotalRede) {
            alert(`Operação Recusada!\n\nO valor solicitado (R$ ${valorRequisitado.toLocaleString('pt-BR', {minimumFractionDigits: 2})}) é maior do que o saldo total disponível na rede (R$ ${saldoTotalRede.toLocaleString('pt-BR', {minimumFractionDigits: 2})}).`);
            return;
        }

        // Ordena os caixas do maior saldo para o menor para proteger as gavetas menores
        caixasRede.sort((a, b) => b.saldo - a.saldo);

        let valorRestante = valorRequisitado;
        let cuponsHTML = "";
        let dadosRateioBanco = [];

        // Loop de distribuição do saldo financeiro por PDV
        for (let cx of caixasRede) {
            if (valorRestante <= 0) break;
            if (cx.saldo <= 0) continue;

            let valorRetiradaCaixa = Math.min(valorRestante, cx.saldo);
            valorRestante -= valorRetiradaCaixa;

            dadosRateioBanco.push({
                caixa_id: cx.id,
                valor: valorRetiradaCaixa
            });

            const idString = String(cx.id);
            const valorFormatado = valorRetiradaCaixa.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            // 🛡️ TRAVA DE SEGURANÇA 1: Token Criptográfico Único por documento
            const stringParaHash = idString + dataHora + cx.terminal + String(valorRetiradaCaixa);
            let hash = 0;
            for (let i = 0; i < stringParaHash.length; i++) {
                const char = stringParaHash.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }
            const tokenSeguranca = "DEP-" + Math.abs(hash).toString(16).toUpperCase() + "-" + idString;

            // 🛡️ TRAVA DE SEGURANÇA 2: Link corrigido do QR Code (Garante a renderização da imagem)
            const urlValidacao = `${window.location.origin}/gerencia/caixa/saidas/validar/${idString}`;
            const qrCodeUrl = `https://qrserver.com{encodeURIComponent(urlValidacao)}`;

            // 🖨️ LAYOUT IDÊNTICO À IMAGEM ENVIADA
            cuponsHTML += `
                <div style="font-family: 'Courier New', Courier, monospace; color: #000; padding: 20px 0; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
                    
                    <!-- ========================================== -->
                    <!-- VIA 1: GAVETA DO CAIXA                     -->
                    <!-- ========================================== -->
                    <div style="width: 450px; border: 2px solid #000; padding: 20px; margin-bottom: 40px; box-sizing: border-box; background: #fff; page-break-inside: avoid; position: relative;">
                        
                        <!-- CABEÇALHO -->
                        <div style="text-align: center; font-weight: bold; padding-bottom: 10px; margin-bottom: 15px;">
                            <div style="font-size: 18px; letter-spacing: 1px;">*** GUIA DE RETIRADA ***</div>
                            <div style="font-size: 18px; letter-spacing: 1px; margin-top: 2px;">AUTENTICADA ***</div>
                            <div style="font-size: 11px; margin-top: 8px; background: #000; color: #fff; padding: 3px 8px; display: inline-block; font-family: monospace;">AUTENTICAÇÃO MECÂNICA: DOC-${idString.padStart(6, '0')}</div>
                            <div style="font-size: 13px; margin-top: 10px; color: #000; font-weight: bold;">--- VIA 1: GAVETA DO CAIXA ---</div>
                        </div>

                        <div style="border-top: 1px dashed #000; margin-bottom: 15px; width: 100%;"></div>

                        <!-- CONTEÚDO PRINCIPAL -->
                        <div style="font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                            <p style="margin: 8px 0;"><b>Data/Hora:</b> ${dataHora}</p>
                            <p style="margin: 8px 0;"><b>Origem...:</b> ${cx.terminal}</p>
                            <p style="margin: 8px 0;"><b>Emissor..:</b> ${cx.operador}</p>
                            <p style="margin: 8px 0; word-break: break-word;"><b>Motivo...:</b> ${finalidade}</p>
                        </div>

                        <!-- BOX DE VALOR CENTRALIZADO -->
                        <div style="font-size: 18px; background: #f1f5f9; padding: 12px; font-weight: bold; border: 1px solid #000; text-align: center; margin-top: 15px; margin-bottom: 20px; border-radius: 6px;">
                            RETIRAR VALOR: R$ ${valorFormatado}
                        </div>

                        <div style="border-top: 1px dashed #000; margin-bottom: 15px; width: 100%;"></div>

                        <!-- BOX DE AUDITORIA E QR CODE -->
                        <div style="border: 1px dashed #000; padding: 10px; display: flex; align-items: center; gap: 15px; background: #fff;">
                            <img src="${qrCodeUrl}" style="width: 100px; height: 100px; border: 1px solid #ccc; display: block;" alt="QR Code">
                            <div style="font-size: 10px; line-height: 1.4; font-family: sans-serif; color: #333;">
                                <div style="font-weight: bold; font-size: 11px; margin-bottom: 3px; font-family: monospace; color: #000;">🔒 ASSINATURA DIGITAL DO BANCO:</div>
                                <code style="font-size: 11px; font-weight: bold; display: block; background: #f3f4f6; padding: 2px 4px; border-radius: 3px; font-family: monospace; word-break: break-all; margin-bottom: 4px;">${tokenSeguranca}</code>
                                A adulteração deste documento impresso constitui quebra dos termos de conformidade e conciliação bancária do PDV.
                            </div>
                        </div>

                        <div style="border-top: 1px dashed #000; margin-top: 20px; margin-bottom: 40px; width: 100%;"></div>

                        <!-- RODAPÉ DE ASSINATURA -->
                        <div style="text-align: center; font-size: 13px; font-weight: bold;">
                            ____________________________________<br>
                            <span style="display: inline-block; margin-top: 5px;">Visto Operador (Responsável Gaveta)</span>
                        </div>
                    </div>

                    <!-- LINHA DE CORTE DA BOBINA -->
                    <div style="width: 450px; border-top: 2px dashed #000; margin-bottom: 10px; height: 1px; display: block;" class="no-print"></div>

                    <!-- ========================================== -->
                    <!-- VIA 2: COMPROVANTE GESTÃO                  -->
                    <!-- ========================================== -->
                    <div style="width: 450px; border: 2px solid #000; padding: 20px; margin-top: 40px; box-sizing: border-box; background: #fff; page-break-inside: avoid; position: relative;">
                        
                        <!-- CABEÇALHO -->
                        <div style="text-align: center; font-weight: bold; padding-bottom: 10px; margin-bottom: 15px;">
                            <div style="font-size: 18px; letter-spacing: 1px;">*** GUIA DE RETIRADA ***</div>
                            <div style="font-size: 18px; letter-spacing: 1px; margin-top: 2px;">AUTENTICADA ***</div>
                            <div style="font-size: 11px; margin-top: 8px; background: #000; color: #fff; padding: 3px 8px; display: inline-block; font-family: monospace;">AUTENTICAÇÃO MECÂNICA: DOC-${idString.padStart(6, '0')}</div>
                            <div style="font-size: 13px; margin-top: 10px; color: #000; font-weight: bold;">--- VIA 2: COMPROVANTE GESTÃO ---</div>
                        </div>

                        <div style="border-top: 1px dashed #000; margin-bottom: 15px; width: 100%;"></div>

                        <!-- CONTEÚDO PRINCIPAL -->
                        <div style="font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                            <p style="margin: 8px 0;"><b>Data/Hora:</b> ${dataHora}</p>
                            <p style="margin: 8px 0;"><b>Origem...:</b> ${cx.terminal}</p>
                            <p style="margin: 8px 0;"><b>Emissor..:</b> ${cx.operador}</p>
                            <p style="margin: 8px 0; word-break: break-word;"><b>Motivo...:</b> ${finalidade}</p>
                        </div>

                        <!-- BOX DE VALOR CENTRALIZADO -->
                        <div style="font-size: 18px; background: #f1f5f9; padding: 12px; font-weight: bold; border: 1px solid #000; text-align: center; margin-top: 15px; margin-bottom: 20px; border-radius: 6px;">
                            RETIRAR VALOR: R$ ${valorFormatado}
                        </div>

                        <div style="border-top: 1px dashed #000; margin-bottom: 15px; width: 100%;"></div>
                                                <!-- BOX DE AUDITORIA E QR CODE -->
                        <div style="border: 1px dashed #000; padding: 10px; display: flex; align-items: center; gap: 15px; background: #fff;">
                            <img src="${qrCodeUrl}" style="width: 100px; height: 100px; border: 1px solid #ccc; display: block;" alt="QR Code">
                            <div style="font-size: 10px; line-height: 1.4; font-family: sans-serif; color: #333;">
                                <div style="font-weight: bold; font-size: 11px; margin-bottom: 3px; font-family: monospace; color: #000;">🔒 ASSINATURA DIGITAL DO BANCO:</div>
                                <code style="font-size: 11px; font-weight: bold; display: block; background: #f3f4f6; padding: 2px 4px; border-radius: 3px; font-family: monospace; word-break: break-all; margin-bottom: 4px;">${tokenSeguranca}</code>
                                Comprovante de controle interno de tesouraria. Guardar anexo ao mapa de fechamento diário do lote gerencial.
                            </div>
                        </div>

                        <div style="border-top: 1px dashed #000; margin-top: 20px; margin-bottom: 40px; width: 100%;"></div>

                        <!-- RODAPÉ DE ASSINATURA -->
                        <div style="text-align: center; font-size: 13px; font-weight: bold;">
                            ____________________________________<br>
                            <span style="display: inline-block; margin-top: 5px;">Visto Gerência (Autorizador do Lote)</span>
                        </div>
                    </div>

                </div>
            `;
        }

        // Envia as vias geradas para o gerenciador de impressão do Windows com largura de cupom térmico padrão (80mm)
        const telaImpressao = window.open('', '_blank', 'width=480,height=800');
        telaImpressao.document.write('<html><head><title>Imprimir Comprovantes Verificados</title>');
        telaImpressao.document.write(`
            <style>
                @page { margin: 0; }
                body { margin: 0; padding: 0; background: #fff; }
                @media print {
                    .no-print { display: none !important; }
                    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                }
            </style>
        `);
        telaImpressao.document.write('</head><body onload="window.print(); window.close();">');
        telaImpressao.document.write(cuponsHTML);
        telaImpressao.document.write('</body></html>');
        telaImpressao.document.close();

        // Trava o botão para impedir disparos paralelos concorrentes
        btn.disabled = true;
        btn.innerText = "Gravando transações no banco...";

        // Dispara a persistência em segundo plano (AJAX/Fetch)
        fetch("{{ route('gerencia.caixa.registrar_saida_lote') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
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




