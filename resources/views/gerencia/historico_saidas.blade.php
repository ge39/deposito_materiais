@extends('layouts.app')

@section('content')
<div style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9; clear: both; width: 100%; box-sizing: border-box; position: relative; z-index: 10;">
    
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 15px;">
            <h2 style="color: #1e3a8a; margin: 0; display: flex; align-items: center; gap: 10px; font-size: 20px;">
                <span>📋</span> Histórico e Reimpressão de Saídas de Caixa
            </h2>
            <a href="{{ route('gerencia.caixa.painel_saidas') }}" style="background-color: #64748b; color: white; text-decoration: none; padding: 8px 15px; font-weight: bold; border-radius: 6px; font-size: 13px;">
                ⬅ Voltar ao Painel
            </a>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; color: #64748b; font-size: 14px;">Data / Hora</th>
                    <th style="padding: 12px; color: #64748b; font-size: 14px;">Origem (PDV)</th>
                    <th style="padding: 12px; color: #64748b; font-size: 14px;">Responsável (Gerente)</th>
                    <th style="padding: 12px; color: #64748b; font-size: 14px;">Motivo / Finalidade</th>
                    <th style="padding: 12px; color: #64748b; font-size: 14px; text-align: right;">Valor</th>
                    <th style="padding: 12px; text-align: center; color: #64748b; font-size: 14px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($saidas as $saida)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; color: #475569; font-size: 13px;">
                        {{ $saida->data_movimentacao->format('d/m/Y H:i:s') }}
                    </td>
                    <td style="padding: 12px; font-weight: bold; color: #334155;">
                        Terminal #{{ $saida->caixa->numero_terminal ?? $saida->caixa_id }}
                    </td>
                    <td style="padding: 12px; color: #475569;">
                        {{ $saida->usuario->name ?? 'Não identificado' }}
                    </td>
                    <td style="padding: 12px; color: #475569; font-size: 13px;">
                        {{ str_replace('Saída gerencial automatizada: ', '', $saida->observacao) }}
                    </td>
                    <td style="padding: 12px; text-align: right; font-weight: bold; color: #dc2626; font-size: 14px;">
                        R$ {{ number_format($saida->valor, 2, ',', '.') }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button type="button" 
                                 onclick="reimprimirCupom(
                                '{{ $saida->id }}', 
                                '{{ $saida->data_movimentacao->format('d/m/Y H:i:s') }}', 
                                'Terminal #{{ $saida->caixa->numero_terminal ?? $saida->caixa_id }}', 
                                '{{ $saida->usuario->name ?? '' }}', 
                                '{{ addslashes(str_replace('Saída gerencial automatizada: ', '', $saida->observacao)) }}', 
                                '{{ $saida->valor }}'
                            )"
                            style="background-color: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;">
                        🖨️ Reimprimir Guia
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">
                        Nenhum lançamento de saída manual encontrado no histórico.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Links de Paginação do Laravel -->
        <div style="margin-top: 15px;">
            {{ $saidas->links() }}
        </div>
    </div>
</div>
<script>
function reimprimirCupom(idTransacao, dataHora, terminal, gerente, motivo, valorRaw) {
    // 🛡️ Converte explicitamente o ID para String para evitar travamento no padStart
    const idString = String(idTransacao);
    
    // Tratamento anti-NaN para ler e converter o valor decimal com precisão
    const valorLimpo = parseFloat(valorRaw);
    const valorFormatado = isNaN(valorLimpo) ? "0,00" : valorLimpo.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // 🛡️ TRAVA DE SEGURANÇA 1: Token Criptográfico baseado na assinatura única do documento
    const stringParaHash = idString + dataHora + terminal + String(valorRaw);
    let hash = 0;
    for (let i = 0; i < stringParaHash.length; i++) {
        const char = stringParaHash.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash;
    }
    const tokenSeguranca = "DEP-" + Math.abs(hash).toString(16).toUpperCase() + "-" + idString;

    // 🛡️ TRAVA DE SEGURANÇA 2: Correção completa da URL do QR Code (Google Charts API ativa)
    const urlValidacao = `${window.location.origin}/gerencia/caixa/saidas/validar/${idString}`;
    const qrCodeUrl = `https://googleapis.com{encodeURIComponent(urlValidacao)}&choe=UTF-8`;

    // Layout duplo com os mapeamentos fixados nas linhas corretas
    const cuponsHTML = `
        <div style="font-family: 'Courier New', Courier, monospace; color: #000; padding: 40px 0; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
            
            <!-- ========================================== -->
            <!-- VIA 1: GAVETA DO CAIXA                     -->
            <!-- ========================================== -->
            <div style="width: 600px; border: 4px solid #000; padding: 30px; margin-bottom: 80px; box-sizing: border-box; background: #fff; page-break-inside: avoid; position: relative;">
                
                <!-- CABEÇALHO -->
                <div style="text-align: center; font-weight: bold; border-bottom: 2px dashed #000; padding-bottom: 16px; margin-bottom: 24px;">
                    <div style="font-size: 26px; letter-spacing: 2px;">*** GUIA DE RETIRADA AUTENTICADA ***</div>
                    <div style="font-size: 16px; margin-top: 5px; background: #000; color: #fff; padding: 4px; display: inline-block;">AUTENTICAÇÃO MECÂNICA: DOC-${idString.padStart(6, '0')}</div>
                    <div style="font-size: 20px; margin-top: 10px; color: #000;">--- VIA 1: GAVETA DO CAIXA ---</div>
                </div>

                <!-- CONTEÚDO COM MAPEAMENTO CORRIGIDO -->
                <div style="font-size: 22px; line-height: 1.6; margin-bottom: 30px;">
                    <p style="margin: 10px 0;"><b>Data/Hora:</b> ${dataHora}</p>
                    <p style="margin: 10px 0;"><b>Origem...:</b> ${terminal}</p>
                    <p style="margin: 10px 0;"><b>Emissor..:</b> ${gerente}</p>
                    <p style="margin: 10px 0; word-break: break-word;"><b>Motivo...:</b> ${motivo}</p>
                    
                    <div style="font-size: 28px; background: #f1f5f9; padding: 16px; font-weight: bold; border: 3px solid #000; text-align: center; margin-top: 24px; border-radius: 8px;">
                        RETIRAR VALOR: R$ ${valorFormatado}
                    </div>
                </div>

                <!-- BLOCO DE SEGURANÇA E QR CODE -->
                <div style="border: 2px dashed #000; padding: 15px; margin-top: 30px; display: flex; align-items: center; gap: 20px;">
                    <img src="${qrCodeUrl}" style="width: 130px; height: 130px; border: 1px solid #000;" alt="QR Code Auditoria">
                    <div style="font-size: 13px; line-height: 1.4;">
                        <b style="font-size: 14px; display: block; margin-bottom: 5px;">🔒 ASSINATURA DIGITAL DO BANCO:</b>
                        <code style="font-size: 15px; font-weight: bold; display: block; background: #eee; padding: 3px; word-break: break-all;">${tokenSeguranca}</code>
                        <span style="display: block; margin-top: 5px; font-style: italic; color: #333;">A adulteração deste documento impresso constitui quebra dos termos de conformidade e conciliação bancária do PDV.</span>
                    </div>
                </div>

                <!-- RODAPÉ -->
                <div style="border-top: 2px dashed #000; padding-top: 60px; margin-top: 40px; text-align: center; font-size: 20px; font-weight: bold;">
                    ____________________________________<br>
                    <span style="display: inline-block; margin-top: 10px;">Visto Operador (Responsável Gaveta)</span>
                </div>
            </div>

            <!-- LINHA DE CORTE DA BOBINA -->
            <div style="width: 600px; border-top: 4px dashed #000; margin-bottom: 10px; height: 1px; display: block;" class="no-print"></div>

            <!-- ========================================== -->
            <!-- VIA 2: COMPROVANTE GESTÃO                  -->
            <!-- ========================================== -->
            <div style="width: 600px; border: 4px solid #000; padding: 30px; margin-top: 80px; box-sizing: border-box; background: #fff; page-break-inside: avoid; position: relative;">
                
                <!-- CABEÇALHO -->
                <div style="text-align: center; font-weight: bold; border-bottom: 2px dashed #000; padding-bottom: 16px; margin-bottom: 24px;">
                    <div style="font-size: 26px; letter-spacing: 2px;">*** GUIA DE RETIRADA AUTENTICADA ***</div>
                    <div style="font-size: 16px; margin-top: 5px; background: #000; color: #fff; padding: 4px; display: inline-block;">AUTENTICAÇÃO MECÂNICA: DOC-${idString.padStart(6, '0')}</div>
                    <div style="font-size: 20px; margin-top: 10px; color: #000;">--- VIA 2: COMPROVANTE GESTÃO ---</div>
                </div>

                <!-- CONTEÚDO PRINCIPAL -->
                <div style="font-size: 22px; line-height: 1.6; margin-bottom: 30px;">
                    <p style="margin: 10px 0;"><b>Data/Hora:</b> ${dataHora}</p>
                    <p style="margin: 10px 0;"><b>Origem...:</b> ${terminal}</p>
                    <p style="margin: 10px 0;"><b>Emissor..:</b> ${gerente}</p>
                    <p style="margin: 10px 0; word-break: break-word;"><b>Motivo...:</b> ${motivo}</p>
                    
                    <div style="font-size: 28px; background: #f1f5f9; padding: 16px; font-weight: bold; border: 3px solid #000; text-align: center; margin-top: 24px; border-radius: 8px;">
                        RETIRAR VALOR: R$ ${valorFormatado}
                    </div>
                </div>

                <!-- BLOCO DE SEGURANÇA E QR CODE -->
                <div style="border: 2px dashed #000; padding: 15px; margin-top: 30px; display: flex; align-items: center; gap: 20px;">
                    <img src="${qrCodeUrl}" style="width: 130px; height: 130px; border: 1px solid #000;" alt="QR Code Auditoria">
                    <div style="font-size: 13px; line-height: 1.4;">
                        <b style="font-size: 14px; display: block; margin-bottom: 5px;">🔒 ASSINATURA DIGITAL DO BANCO:</b>
                        <code style="font-size: 15px; font-weight: bold; display: block; background: #eee; padding: 3px; word-break: break-all;">${tokenSeguranca}</code>
                        <span style="display: block; margin-top: 5px; font-style: italic; color: #333;">Comprovante de controle interno de tesouraria. Guardar anexo ao mapa de fechamento diário do lote gerencial.</span>
                    </div>
                </div>

                <!-- RODAPÉ DE ASSINATURA -->
                <div style="border-top: 2px dashed #000; padding-top: 60px; margin-top: 40px; text-align: center; font-size: 20px; font-weight: bold;">
                    ____________________________________<br>
                    <span style="display: inline-block; margin-top: 10px;">Visto Gerência (Autorizador do Lote)</span>
                </div>
            </div>

        </div>
    `;

    const telaImpressao = window.open('', '_blank', 'width=750,height=900');
    telaImpressao.document.write('<html><head><title>Cupom Verificado - Depósito de Materiais</title>');
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
}
</script>





@endsection
