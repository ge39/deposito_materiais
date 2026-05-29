

    if (window.PDV_BLOQUEADO === true) {
        
        // Função para manter o estado visual sempre sincronizado com o foco real do DOM
        function atualizarEstiloPorFoco() {
            const btnFechar = document.getElementById('btnFecharCaixaImediato');
            const btnSair = document.getElementById('btnSairPdvImediato');
            
            if (!btnFechar || !btnSair) return;

            if (document.activeElement === btnSair) {
                btnFechar.classList.remove('foco-ativo-pdv');
                btnSair.classList.add('foco-ativo-pdv');
            } else {
                // Padrão ou quando o botão fechar está focado
                btnSair.classList.remove('foco-ativo-pdv');
                btnFechar.classList.add('foco-ativo-pdv');
                // Se o foco vazou para o body ou outro elemento, força de volta para o Fechar
                if (document.activeElement !== btnFechar) {
                    btnFechar.focus();
                }
            }
        }

        // Interceptador global com prioridade máxima
        window.addEventListener('keydown', function (event) {
            const atalhosBloqueados = [
                'F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8', 'F9', 'F10', 'F11', 'F12',
                'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter', 'Escape', 'Tab'
            ];

            if (atalhosBloqueados.includes(event.key)) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                
                const btnFechar = document.getElementById('btnFecharCaixaImediato');
                const btnSair = document.getElementById('btnSairPdvImediato');

                // 🔄 TAB: Alternância Cíclica Exclusiva entre os dois botões
                if (event.key === 'Tab') {
                    if (document.activeElement === btnFechar) {
                        btnSair?.focus();
                    } else {
                        btnFechar?.focus();
                    }
                    atualizarEstiloPorFoco();
                }

                // ↩️ ENTER: Executa o botão ativo no milissegundo atual
                if (event.key === 'Enter') {
                    document.activeElement?.click();
                }
                
                return false;
            }
        }, true);

        // 🛡️ Garante o foco inicial e combate a perda de estilo pós-carregamento dos outros scripts
        const persistirFocoInicial = setInterval(() => {
            const btnFechar = document.getElementById('btnFecharCaixaImediato');
            if (btnFechar) {
                // Se o operador ainda não mudou manualmente para o botão Sair, força e mantém o foco no Fechar Caixa
                if (document.activeElement !== document.getElementById('btnSairPdvImediato')) {
                    btnFechar.focus();
                }
                atualizarEstiloPorFoco();
            }
        }, 50);

        // Finaliza o loop de persistência após 2 segundos para liberar o ciclo natural do Tab
        setTimeout(() => clearInterval(persistirFocoInicial), 2000);
    }
