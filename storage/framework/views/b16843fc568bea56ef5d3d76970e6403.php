<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviço Temporariamente Indisponível</title>
    <link href="https://jsdelivr.net" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 500px; padding: 40px; border-radius: 16px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; }
        .icon-db { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-db">⚠️</div>
        <h4 class="fw-bold text-dark mb-3">Conexão com o Servidor Interrompida</h4>
        <p class="text-secondary mb-4">
            Não foi possível estabelecer comunicação com o banco de dados do sistema. 
            Isso pode ser uma instabilidade temporária ou o serviço local foi reiniciado.
        </p>
        <div class="alert alert-light text-start small border">
            <strong>Dica Técnica:</strong> Certifique-se de que o serviço do MySQL/Laragon/XAMPP está ativo no painel de controle do servidor.
        </div>
        <button onclick="window.location.reload();" class="btn btn-primary w-100 py-2 fw-bold mt-2">
            Tentar Novamente
        </button>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/errors/database.blade.php ENDPATH**/ ?>