// ==========================================
// 🛡️ ESCOPO DE MEMÓRIA GLOBAL DO PDV (COMPARTILHADO EM REDE)
// ==========================================
window.carrinho = window.carrinho || [];
window.cliente = window.cliente || null;
window.caixaBloqueado = false;
window.CAIXA_ID = window.CAIXA_ID || null;

document.addEventListener('DOMContentLoaded', function () {
    
  // ========================================================
    // ATALHO ALT + P ANTI-PANE (CONSULTA VIRTUAL DIRETA NO BANCO)
    // ========================================================
    document.addEventListener('keydown', function(event) {
        
        // Captura a combinação ALT + P
        if (event.altKey && (event.key === 'p' || event.key === 'P' || event.code === 'KeyP')) {
            
            // Bloqueia de forma absoluta a impressão da tela inteira do navegador
            event.preventDefault();
            event.stopPropagation();

            // 🏪 Coleta o caixa_id direto dos inputs nativos da sua página (Igual à sua função finalizarVenda)
            const inputCaixa = document.querySelector('input[name="caixa_id"]') || document.getElementById('input-caixa-id') || document.getElementById('caixa_id');
            let idCaixaAtivo = inputCaixa ? parseInt(inputCaixa.value, 10) : 0;

            if (!idCaixaAtivo || idCaixaAtivo <= 0) {
                alert('Atenção: O sistema não localizou o ID do Caixa ativo nesta página.');
                return;
            }

            // Consulta o banco para saber qual foi o último ID persistido no HD antes da queda de energia
            fetch(`/pdv/ultima-venda-id?caixa_id=${idCaixaAtivo}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro interno no servidor ou rota desalinhada.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.venda_id) {
                        
                        // Se o banco retornou o ID, abre o iframe térmico silencioso
                        const iframeGarantido = document.createElement('iframe');
                        iframeGarantido.style.display = 'none';
                        iframeGarantido.src = `/venda/${data.venda_id}/cupom`;
                        document.body.appendChild(iframeGarantido);
                        
                        iframeGarantido.onload = function() {
                            iframeGarantido.contentWindow.focus();
                            iframeGarantido.contentWindow.print();
                            
                            setTimeout(() => {
                                if (iframeGarantido.parentNode) {
                                    document.body.removeChild(iframeGarantido);
                                }
                            }, 1000);
                        };

                    } else {
                        alert(data.erro || 'Nenhuma venda registrada neste caixa para ser reimpressa.');
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Erro ao consultar banco de dados: ' + error.message);
                });
        }
    });


    // ==========================================
    // 🔍 CAPTURADOR AUTOMÁTICO DE CAIXA_ID DO DOM
    // ==========================================
    const inputCaixa = document.querySelector('input[name="caixa_id"]') || 
                       document.getElementById('input-caixa-id') || 
                       document.getElementById('caixa_id');
    
    if (inputCaixa && inputCaixa.value) {
        window.CAIXA_ID = inputCaixa.value;
    } else {
        console.warn('Aviso: Elemento HTML com o caixa_id não foi localizado na página atual do PDV.');
    }

    // =========================
    // ABRIR MODAL FINALIZAR
    // =========================
    window.abrirModalFinalizar = function () {
        const modal = document.getElementById('modalFinalizarVenda');
        if (!modal) return;
        const instancia = bootstrap.Modal.getOrCreateInstance(modal);
        instancia.show();
        atualizarModalFinalizarCliente();
    };

    function atualizarModalFinalizarCliente() {
        if (!window.cliente) return;
        const saldoEl = document.getElementById('saldo-cliente-finalizar');
        const limiteEl = document.getElementById('limite-cliente-finalizar');
        const saldo = Number(window.cliente.saldo_apos || 0);
        const limite = Number(window.cliente.limite_credito || 0);
        
        if (saldoEl) {
            saldoEl.textContent = `Saldo: R$ ${saldo.toFixed(2).replace('.', ',')}`;
        }
        if (limiteEl) {
            limiteEl.textContent = `Limite: R$ ${limite.toFixed(2).replace('.', ',')}`;
        }
    }

    // ==========================================
    // 🎹 CONTROLE DE ATALHOS GLOBAIS (CAPTURE: TRUE)
    // ==========================================
    document.addEventListener('keydown', function (e) {
        // 🔒 Trava total do sistema caso o caixa esteja sob bloqueio de sangria
        if (window.caixaBloqueado) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return;
        }

        if (e.repeat) return;

        // F2 - CLIENTE
        if (e.key === 'F2') {
            e.preventDefault();
            const modal = document.getElementById('modalCliente');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F3 - PRODUTO
        if (e.key === 'F3') {
            e.preventDefault();
            const modal = document.getElementById('modalProduto');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // F4 - ORÇAMENTO
        if (e.key === 'F4') {
            e.preventDefault();
            const modal = document.getElementById('modalOrcamento');
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }
        // F6 - FINALIZAR VENDA
        if (e.code === 'F6') {
            if (document.querySelector('.modal.show')) return;
            e.preventDefault();

            // PEGA O TOTAL (Substitua '.classe-do-total' pela classe real do seu HTML)
            const totalTexto = document.querySelector('.classe-do-total')?.innerText || '0';
            const totalValor = parseFloat(totalTexto.replace(/[^\d,.]/g, '').replace(',', '.'));

            if (!totalValor || totalValor <= 0) {
                const elementoModal = document.getElementById('modalCarrinhoVazio');
                const modalAviso = new bootstrap.Modal(elementoModal);
                
                // Elemento do seu input de código de barras (Ajuste o ID se necessário)
                const inputCodigoBarras = document.getElementById('codigo_barras');

                // EVENTO: Quando o modal terminar de sumir da tela
                elementoModal.addEventListener('hidden.bs.modal', function () {
                    if (inputCodigoBarras) {
                        inputCodigoBarras.focus();
                        inputCodigoBarras.select(); // Deixa o campo limpo/selecionado para o próximo bip
                    }
                }, { once: true }); // '{ once: true }' evita duplicar o evento na memória

                modalAviso.show();
                return;
            }

            window.abrirModalFinalizar();
        }

    }, true);

    // F6 - FINALIZAR VENDA
    // Certifique-se de que a função recebe o parâmetro (e)
    // document.addEventListener('keydown', function (e) {

    //     // F6 - FINALIZAR VENDA
    //     if (e.code === 'F6') {
    //         if (document.querySelector('.modal.show')) return;
    //         e.preventDefault();

    //         // VALIDAÇÃO: Altere para o seletor real da sua tabela de produtos
    //         const totalItens = document.querySelectorAll('#tabela-itens tbody tr').length;

    //         if (totalItens === 0) {
    //             const elementoModal = document.getElementById('modalCarrinhoVazio');
                
    //             if (elementoModal) {
    //                 const modalAviso = new bootstrap.Modal(elementoModal);
    //                 const inputCodigoBarras = document.getElementById('codigo_barras'); // ID do seu leitor

    //                 // EVENTO 1: Dispara ANTES do modal sumir (Evita o erro de aria-hidden)
    //                 elementoModal.addEventListener('hide.bs.modal', function () {
    //                     // Remove o foco do botão "Entendi" ativamente
    //                     if (document.activeElement) {
    //                         document.activeElement.blur();
    //                     }
    //                 }, { once: true });

    //                 // EVENTO 2: Dispara DEPOIS que o modal sumiu por completo
    //                 elementoModal.addEventListener('hidden.bs.modal', function () {
    //                     if (inputCodigoBarras) {
    //                         inputCodigoBarras.focus();
    //                         inputCodigoBarras.select();
    //                     }
    //                 }, { once: true });

    //                 modalAviso.show();
    //             } else {
    //                 alert('O carrinho está vazio! Adicione produtos antes de finalizar.');
    //             }
    //             return;
    //         }

    //         if (typeof window.abrirModalFinalizar === 'function') {
    //             window.abrirModalFinalizar();
    //         }
    //     }

    // });

     if (e.code === 'F6') {

            // Se já existe um modal aberto, NÃO faz nada
            if (document.querySelector('.modal.show')) {
                return;
            }

            e.preventDefault();
            abrirModalFinalizar();
        }


    // =========================
    // BOTÃO ABRIR CAIXA
    // =========================
    const btnAbrirCaixa = document.querySelector('.btn-abrir-caixa');
    if (btnAbrirCaixa) {
        btnAbrirCaixa.addEventListener('click', function () {
            window.caixaBloqueado = false;
            document.body.classList.remove('caixa-bloqueado');
            const overlay = document.getElementById('overlay-caixa-bloqueado');
            if (overlay) overlay.style.display = 'none';
        });
    }

    // ==========================================
    // FECHAMENTO CAIXA (F10) - SEGURO E CONFIRMADO
    // ==========================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F10') {
            e.preventDefault();

            if (!window.CAIXA_ID) {
                const inputCaixaReconferencia = document.querySelector('input[name="caixa_id"]') || document.getElementById('input-caixa-id');
                if (inputCaixaReconferencia && inputCaixaReconferencia.value) {
                    window.CAIXA_ID = inputCaixaReconferencia.value;
                }
            }

            if (!window.CAIXA_ID || window.CAIXA_ID === "") {
                alert('Nenhum caixa aberto ou localizado no HTML deste PDV.');
                return;
            }

            const itensNoCarrinho = window.carrinho || [];
            if (itensNoCarrinho.length > 0) {
                const prosseguir = confirm('Atenção: Há itens pendentes no carrinho! Deseja realmente descartar esta venda para prosseguir ao fechamento do caixa?');
                if (!prosseguir) return;
            }

            window.location.href = `/fechamento_caixa/fechamento/${window.CAIXA_ID}`;
        }
    });

    // ==========================================
    // ⏱️ MONITORAMENTO ASSÍNCRONO DOS CAIXAS ESQUECIDOS (2,5 SEGUNDOS APÓS F5)
    // ==========================================
    setTimeout(async function () {
        const listaDiv = document.getElementById('listaCaixasEsquecidos');
        const btnGatilho = document.getElementById('btnGatilhoModalCaixa');

        // Proteção contra views que não utilizam o modal de aviso
        if (!listaDiv || !btnGatilho) return;

        try {
            // Puxa o terminal ativo direto da sessão gerenciada pelo seu Middleware IdentificaTerminal
            const terminalAtualId = parseInt("{{ session('terminal_id') ?? cookie('terminal_id') ?? '' }}") || 10;

            // Executa a busca em segundo plano na rede local
            const response = await fetch(`/pdv/caixas-esquecidos?terminal_id=${terminalAtualId}`);
            if (!response.ok) throw new Error('Erro na comunicação com a API do PDV');

            const caixas = await response.json();

            // Se o terminal atual não possuir pendências, encerra sem abrir modal
            if (!caixas || caixas.length === 0) {
                listaDiv.style.display = 'none';
                return;
            }

            listaDiv.innerHTML = '';
            listaDiv.style.display = 'block';

            caixas.forEach(caixa => {
                const item = document.createElement('li');
                item.textContent =
                    `Terminal: ${caixa.terminal_id} | ` +
                    `Caixa ID: ${caixa.id} | ` +
                    `Aberto em: ${caixa.data_abertura_br} | ` +
                    `Média horas pdv aberto: ${caixa.pdv_horas_aberto}h | ` +
                    `Operador: ${caixa.nome_operador}`;
                listaDiv.appendChild(item);
            });

            // 🎯 DISPARO NATIVO À PROVA DE ERROS: Simula o clique no botão invisível que o Bootstrap monitora
            btnGatilho.click();

        } catch (err) {
            console.error("Erro interno no rastreador assíncrono de caixas:", err);
        }
    }, 2500);

   // CORREÇÃO GLOBAL AVANÇADA DE FOCO PARA MODAIS (Evita erro aria-hidden)
    document.addEventListener('hide.bs.modal', function (evento) {
        const modalQueVaiFechar = evento.target;
        
        // Se o elemento com foco atual estiver dentro do modal que está fechando
        if (document.activeElement && modalQueVaiFechar.contains(document.activeElement)) {
            // Remove o foco do input/botão imediatamente
            document.activeElement.blur();
        }
    });

    // Força o retorno do foco para o leitor de código de barras padrão do PDV
    document.addEventListener('hidden.bs.modal', function () {
        const inputCodigoBarras = document.getElementById('codigo_barras'); // Ajuste se seu ID for diferente
        if (inputCodigoBarras && !document.querySelector('.modal.show')) {
            inputCodigoBarras.focus();
            inputCodigoBarras.select();
        }
    });



});
  
