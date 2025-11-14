<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamento #<?php echo e($orcamento->codigo_orcamento); ?></title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 20px 40px;
            background-color: #25D366; /* verde WhatsApp */
            color: white;
            text-decoration: none;
            font-size: 18px;
            border-radius: 10px;
            text-align: center;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #128C7E;
        }
    </style>
</head>
<body>
    <a href="<?php echo e($linkPdf); ?>" class="btn" target="_blank">
        Clique aqui para abrir seu orçamento
    </a>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/orcamentos/visualizar.blade.php ENDPATH**/ ?>