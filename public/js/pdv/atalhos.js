// ==========================================
// 🛡️ ESCOPO DE MEMÓRIA GLOBAL DO PDV (COMPARTILHADO EM REDE)
// ==========================================
window.carrinho = window.carrinho || [];
window.cliente = window.cliente || null;
window.caixaBloqueado = false;
window.CAIXA_ID = window.CAIXA_ID || null;

document.addEventListener('DOMContentLoaded', function () {
    
   // ========================================================
    // ATALHO ALT + P CONSULTANDO O BANCO DE DADOS (CORRIGIDO)
    // ========================================================
    document.addEventListener('keydown', function(event) {
        
        // Detecta a combinação ALT + P de forma universal
        if (event.altKey && (event.key === 'p' || event.key === 'P' || event.code === 'KeyP')) {
            
            // Bloqueia com força total o comportamento nativo do navegador (Evita imprimir a tela)
            event.preventDefault();
            event.stopPropagation();

            // 🏪 Busca o ID do Caixa ativo na tela
            const inputCaixaHidden = document.querySelector('input[name="caixa_id"]') || document.getElementById('caixa_id');
            let idCaixaAtivo = parseInt(inputCaixaHidden?.value, 10) || parseInt(window.caixa_id, 10) || 0;

            if (idCaixaAtivo <= 0) {
                alert('Não foi possível identificar o caixa ativo para buscar a última venda.');
                return;
            }

            // Consulta expressa ao banco para pegar a última venda finalizada deste caixa
            fetch(`/pdv/ultima-venda-id?caixa_id=${idCaixaAtivo}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('A rota retornou um erro ou página inválida. Verifique a posição da rota no web.php.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.venda_id) {
                        
                        // Se achou o ID, monta o Iframe oculto para imprimir apenas o cupom térmico
                        const iframeGarantido = document.createElement('iframe');
                        iframeGarantido.style.display = 'none';
                        iframeGarantido.src = `/venda/${data.venda_id}/cupom`;
                        document.body.appendChild(iframeGarantido);
                        
                        iframeGarantido.onload = function() {
                            iframeGarantido.contentWindow.focus();
                            iframeGarantido.contentWindow.print();
                            
                            // Remove o elemento para limpar a memória da página
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
                    alert('Erro: O backend não respondeu com um JSON válido. Certifique-se de que subiu a linha da rota no arquivo web.php antes das rotas com {id}.');
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
            window.abrirModalFinalizar();
        }
    }, true);

    // ==========================================
    // 🛒 LEITOR DE CÓDIGO DE BARRAS / EVENTO ENTER BLINDADO
    // ==========================================
    const inputCodigoBarras = document.getElementById('codigo_barras') || document.querySelector('input[name="codigo_barras"]');
    if (inputCodigoBarras) {
        inputCodigoBarras.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault(); // Impede o Recarregamento/Submit indesejado da View no F5
                
                const valorBipado = this.value.trim();
                if (valorBipado === "") return;

                console.log("Código interceptado no escopo global tradicional:", valorBipado);
                
                // Dispara dinamicamente a função existente no seu arquivo produto.js ou carrinho.js
                if (typeof window.adicionarProdutoAoCarrinho === 'function') {
                    window.adicionarProdutoAoCarrinho(valorBipado);
                } else if (typeof adicionarProduto === 'function') {
                    adicionarProduto(valorBipado);
                }
            }
        });
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



});
  
