

<?php $__env->startSection('content'); ?>
<div class="container py-5 mt-0 pt-0">
    
    <div class="card shadow-lg border-0">
        
        <div class="card-header 
            <?php if($bloquearPDV): ?> bg-danger text-white 
            <?php else: ?> bg-warning text-dark 
            <?php endif; ?>">
            
            <h4 class="fw-bold mb-0">
                <?php if($bloquearPDV): ?>
                    🚫 BLOQUEIO DE CAIXA - <?php echo e($caixa->id); ?>

                <?php else: ?>
                    ⚠️ LIMITE DE SANGRIA ATINGIDO - <?php echo e($configSangria->valor_limite ?? 0); ?>

                <?php endif; ?>
            </h4>
        </div>

        <div class="card-body text-center">

            <h5 class="mb-3">
                Saldo Atual:
                <span class="fw-bold" id="saldoAtualTexto">
                    R$ <?php echo e(number_format($saldoAtual, 2, ',', '.')); ?>

                </span>
            </h5>

            <p class="mb-2">
                Limite configurado:
                <span class="fw-bold">
                    R$ <?php echo e(number_format($limite_sangria, 2, ',', '.')); ?>

                </span>
            </p>

            <?php if($bloquearPDV): ?>
                <div class="alert alert-danger fw-bold fs-2 shadow-sm">
                    PDV BLOQUEADO<br>
                    Realize a Sangria para continuar as vendas.
                </div>
            <?php else: ?>
                <div class="alert alert-warning fw-bold fs-2 shadow-sm">
                    Recomendado realizar Sangria.
                </div>
            <?php endif; ?>

            <hr>

            <h5 class="text-primary fw-bold">💰 Valor sugerido para Sangria:</h5>
            <h2 class="display-6 fw-bold text-success mb-4" id="valorSugeridoTexto">
                R$ <?php echo e(number_format($saldoAtual, 2, ',', '.')); ?>

            </h2>

            <p class="text-muted mb-4">
                Oriente a operadora a retirar este valor do caixa.
            </p>

            
            <div id="mensagemAjax"></div>

            <form id="formSangria"
                  action="<?php echo e(route('caixa.sangria.registrar', $caixa->id)); ?>"
                  method="POST"
                  class="w-50 mx-auto">
                <?php echo csrf_field(); ?>

                <div class="mb-3 text-start">
                    <label for="valor" class="form-label fw-bold">Valor da Sangria</label>
                    <input type="number"
                           name="valor"
                           id="valorInput"
                           class="form-control <?php $__errorArgs = ['valor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           step="0.01"
                           min="0"
                           value="<?php echo e(old('valor', $valorSugeridoSangria ?? $saldoAtual)); ?>"
                           <?php echo e($saldoAtual <= 0 ? 'disabled' : ''); ?>

                           required>
                    <?php $__errorArgs = ['valor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-3 text-start">
                    <label for="motivo" class="form-label fw-bold">Motivo</label>
                    <select name="motivo"
                            id="motivo"
                            class="form-select <?php $__errorArgs = ['motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            <?php echo e($saldoAtual <= 0 ? 'disabled' : ''); ?>

                            required>
                        <option value="">Selecione</option>
                        <option value="manual">Manual</option>
                        <option value="limite_excedido">Limite Excedido</option>
                        <option value="encerramento">Encerramento</option>
                    </select>
                    <?php $__errorArgs = ['motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="submit"
                            id="btnSangria"
                            class="btn btn-success fw-bold px-4"
                            <?php echo e($saldoAtual <= 0 ? 'disabled' : ''); ?>>
                        ✅ Efetuar Sangria
                    </button>

                    <a href="<?php echo e(route('pdv.index')); ?>" class="btn btn-secondary fw-bold px-4">
                        🔙 Voltar
                    </a>

                    <?php if($ultimaSangria): ?>
                        <a href="<?php echo e(route('sangria.imprimir', $ultimaSangria)); ?>"
                           id="btnImprimirSangria"
                           class="btn btn-primary fw-bold px-4 mt-2">
                            🖨 Imprimir
                        </a>
                    <?php else: ?>
                        <a href="#"
                           id="btnImprimirSangria"
                           class="btn btn-primary fw-bold px-4 mt-2 disabled">
                            🖨 Imprimir
                        </a>
                    <?php endif; ?>
                </div>
            </form>

        </div>
    </div>
</div>


<script>
function mostrarMensagem(tipo, mensagem) {
    const container = document.getElementById('mensagemAjax');

    container.innerHTML = `
        <div class="alert alert-${tipo} alert-dismissible fade show shadow-sm mt-3" role="alert">
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}

document.getElementById('formSangria')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('btnSangria');

    btn.disabled = true;
    btn.innerText = "Processando...";

    try {
        const response = await fetch(form.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Accept": "application/json"
            },
            body: new FormData(form)
        });

        const data = await response.json();

        if (data.success) {
            mostrarMensagem('success', '💰 Sangria realizada com sucesso!');

            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);

        } else {
            mostrarMensagem('danger', data.message || 'Erro ao realizar sangria.');
        }

    } catch (error) {
        console.error(error);
        mostrarMensagem('danger', 'Erro ao processar a requisição.');
    } finally {
        btn.disabled = false;
        btn.innerText = "✅ Efetuar Sangria";
    }
});

// foco automático
document.getElementById('valorInput')?.focus();
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp2\htdocs\deposito_materiais\resources\views/pdv/sangria_form.blade.php ENDPATH**/ ?>