// Script de bloqueio botao 
   function atualizarBotaoSalvar() {
        let container = document.getElementById('itensContainer');
        let btn = document.getElementById('btnSalvar');

        btn.disabled = container.children.length === 0;
    }

    document.addEventListener('DOMContentLoaded', function() {
        let container = document.getElementById('itensContainer');

        atualizarBotaoSalvar();

        // 👇 Observa qualquer mudança dentro da div
        const observer = new MutationObserver(() => {
            atualizarBotaoSalvar();
        });

        observer.observe(container, {
            childList: true
        });
    });
