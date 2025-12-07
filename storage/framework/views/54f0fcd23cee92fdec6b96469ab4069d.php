

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Editar Usuário</h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form action="<?php echo e(route('users.update', $user->id)); ?>" method="POST" >
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
            <label class="form-label">Funcionário</label>
            <input type="text" class="form-control" value="<?php echo e($user->funcionario->nome ?? '—'); ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="nivel_acesso" class="form-label">Nível de Acesso</label>
            <select name="nivel_acesso" id="nivel_acesso" class="form-select" required>
                <option value="admin" <?php echo e($user->nivel_acesso === 'admin' ? 'selected' : ''); ?>>Administrador</option>
                <option value="vendedor" <?php echo e($user->nivel_acesso === 'vendedor' ? 'selected' : ''); ?>>Vendedor</option>
                <option value="gerente" <?php echo e($user->nivel_acesso === 'gerente' ? 'selected' : ''); ?>>Gerente</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha (opcional)</label>
            <input type="password" name="password" required id="password" class="form-control" minlength="4">
            <small id="passwordError" class="text-danger"></small>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
            <input type="password" name="password_confirmation" required id="password_confirmation" class="form-control" minlength="4">
            <small id="confirmError" class="text-danger"></small>
        </div>

        <div class="mb-3">
            <label for="ativo" class="form-label">Status</label>
            <select name="ativo" id="ativo" class="form-select">
                <option value="1" <?php echo e($user->ativo ? 'selected' : ''); ?>>Ativo</option>
                <option value="0" <?php echo e(!$user->ativo ? 'selected' : ''); ?>>Inativo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formUsuario');
    const senha = document.getElementById('password');
    const confirmar = document.getElementById('password_confirmation');

    // Feedback visual
    let feedbackSenha = document.getElementById('feedbackSenha');
    if (!feedbackSenha) {
        feedbackSenha = document.createElement('div');
        feedbackSenha.id = 'feedbackSenha';
        feedbackSenha.style.marginTop = '6px';
        confirmar.parentNode.appendChild(feedbackSenha);
    }

    function mostrarAlerta(mensagem) {
        const alerta = document.getElementById('alerta');
        if (alerta) {
            alerta.style.display = 'block';
            alerta.textContent = mensagem;
            setTimeout(() => alerta.style.display = 'none', 4000);
        } else {
            alert(mensagem);
        }
    }

    function validarSenha() {
        const senhaVal = senha.value.trim();
        const confirmarVal = confirmar.value.trim();

        if (senhaVal.length < 4) {
            feedbackSenha.textContent = 'A senha deve ter no mínimo 4 caracteres!';
            feedbackSenha.style.color = 'red';
            return false;
        }

        if (senhaVal.length >= 4 && confirmarVal.length < 4) {
            feedbackSenha.textContent = 'Padrão mínimo atendido.';
            feedbackSenha.style.color = 'green';
            return false;
        }

        if (senhaVal !== confirmarVal) {
            feedbackSenha.textContent = 'As senhas não conferem!';
            feedbackSenha.style.color = 'red';
            return false;
        }

        feedbackSenha.textContent = 'As senhas conferem!';
        feedbackSenha.style.color = 'green';
        return true;
    }

    // Eventos em tempo real
    senha.addEventListener('input', validarSenha);
    confirmar.addEventListener('input', validarSenha);

    document.getElementById('formUsuario').addEventListener('submit', function(e) {
        if (!validarSenha()) {
            e.preventDefault();
            mostrarAlerta('Corrija a senha antes de enviar o formulário.');
        }
    });

});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/users/edit.blade.php ENDPATH**/ ?>