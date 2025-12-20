// resources/js/pdv/app.js

// Import dos módulos
import './bootstrap';
import './pdv/orcamento'; // <-- nosso script do PDV
import Produto from './produto';
import './atalhos';
import './ui'; // mantendo conforme o seu projeto

// Inicializa tudo quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    // Inicializa o módulo Produto
    Produto.init();

    // Se houver outros módulos com init, inicialize aqui
    console.log('App PDV iniciado');
});
