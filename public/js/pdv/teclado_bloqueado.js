// 🎯 REGISTRA O OUVINTE SEMPRE (O "if" foi movido para dentro do evento)
window.addEventListener('keydown', function (event) {

    // Se o modal de sangria estiver aberto, deixa o Bootstrap tratar o ESC
    const modalSangria = document.getElementById('modalSangria');

    if (
        event.key === 'Escape' &&
        modalSangria &&
        modalSangria.classList.contains('show')
    ) {
        return;
    }

    if (window.PDV_BLOQUEADO !== true) {
        return;
    }

    const atalhosBloqueados = [
        'F1', 'F2', 'F3', 'F4', 'F5', 'F6',
        'F7', 'F8', 'F9', 'F10', 'F11', 'F12',
        'ArrowUp', 'ArrowDown', 'ArrowLeft',
        'ArrowRight', 'Enter', 'Escape', 'Tab'
    ];

}, true);

// --- Funções Auxiliares e Temporizadores mantidos abaixo ---

function atualizarEstiloPorFoco() {
    // Só executa a verificação visual se o PDV realmente estiver bloqueado na memória
    if (window.PDV_BLOQUEADO !== true) return;

    const btnFechar = document.getElementById('btnFecharCaixaImediato');
    const btnSair = document.getElementById('btnSairPdvImediato');
    
    if (!btnFechar || !btnSair) return;

    if (document.activeElement === btnSair) {
        btnFechar.classList.remove('foco-ativo-pdv');
        btnSair.classList.add('foco-ativo-pdv');
    } else {
        btnSair.classList.remove('foco-ativo-pdv');
        btnFechar.classList.add('foco-ativo-pdv');
        if (document.activeElement !== btnFechar && document.activeElement !== btnSair) {
            btnFechar.focus();
        }
    }
}

// O Temporizador de foco só deve incomodar se o caixa continuar de fato bloqueado
const persistirFocoInicial = setInterval(() => {
    if (window.PDV_BLOQUEADO !== true) {
        clearInterval(persistirFocoInicial);
        return;
    }
    
    const btnFechar = document.getElementById('btnFecharCaixaImediato');
    const btnSair = document.getElementById('btnSairPdvImediato');
    if (btnFechar && btnSair) {
        if (document.activeElement !== btnSair) {
            btnFechar.focus();
        }
        atualizarEstiloPorFoco();
    }
}, 50);

setTimeout(() => clearInterval(persistirFocoInicial), 2000);
