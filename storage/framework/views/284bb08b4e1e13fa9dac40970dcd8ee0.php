<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card p-4 shadow-sm" style="width: 350px;">
    <h4 class="mb-3 text-center">Login</h4>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

        
    <?php if(session('login_session_error')): ?>
        <div class="alert alert-warning" role="alert">
            <?php echo e(session('login_session_error')); ?>

        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('login.post')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" name="email" id="email" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
</div>

<!-- LOGIN419_V1 - Renovacao preventiva do CSRF -->
<script>
(function () {
    'use strict';

    const form = document.querySelector('form[method="POST"]');

    if (!form) {
        return;
    }

    const csrfInput = form.querySelector('input[name="_token"]');
    const submitButton = form.querySelector('button[type="submit"]');

    if (!csrfInput || !submitButton) {
        return;
    }

    let processing = false;

    function showError(message) {
        let alert = document.getElementById('login419-alert');

        if (!alert) {
            alert = document.createElement('div');
            alert.id = 'login419-alert';
            alert.className = 'alert alert-danger';
            alert.setAttribute('role', 'alert');
            form.parentNode.insertBefore(alert, form);
        }

        alert.textContent = message;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (processing) {
            return;
        }

        processing = true;
        submitButton.disabled = true;

        const originalText = submitButton.textContent;
        submitButton.textContent = 'Autenticando...';

        try {
            const loginUrl = new URL(form.action);

            const response = await fetch(loginUrl.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                redirect: 'follow',
                headers: {
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) {
                throw new Error('Falha ao renovar a sessao.');
            }

            const finalUrl = new URL(response.url);

            if (finalUrl.origin !== loginUrl.origin ||
                finalUrl.pathname !== loginUrl.pathname) {
                throw new Error('Resposta inesperada da autenticacao.');
            }

            const html = await response.text();

            const documentResult = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            const newToken = documentResult.querySelector(
                'form input[name="_token"]'
            );

            if (!newToken || !newToken.value) {
                throw new Error('Token CSRF nao encontrado.');
            }

            csrfInput.value = newToken.value;

            // Submete somente depois da renovacao CSRF.
            HTMLFormElement.prototype.submit.call(form);

        } catch (error) {
            console.error('LOGIN419_V1:', error.message);

            showError(
                'Nao foi possivel atualizar a sessao de login. ' +
                'Verifique a conexao e tente novamente.'
            );

            submitButton.disabled = false;
            submitButton.textContent = originalText;
            processing = false;
        }
    });
})();
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\auth\login.blade.php ENDPATH**/ ?>