

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="card shadow-lg border-0">
        <!-- 1️⃣ O Cabeçalho altera a cor de fundo dinamicamente de acordo com o nível de alerta -->
        <div class="card-header <?php echo e((isset($bloquearPDV) && $bloquearPDV) ? 'bg-danger text-white' : 'bg-warning text-dark'); ?>">
            <h4 class="fw-bold mb-0">
                <?php if(isset($bloquearPDV) && $bloquearPDV): ?>
                    🚫 BLOQUEIO DE CAIXA
                <?php else: ?>
                    ⚠️ LIMITE DE SANGRIA ATINGIDO
                <?php endif; ?>
            </h4>
        </div>

        <!-- 2️⃣ O alerta de texto explicativo fica logo abaixo do cabeçalho, de forma organizada -->
        <?php if(isset($bloquearPDV) && $bloquearPDV): ?>
            <div class="alert alert-danger shadow-sm border-0 rounded-0 mb-0">
                <strong>Atenção Operador:</strong> O limite de segurança do caixa foi atingido. O PDV permanecerá bloqueado até a realização desta sangria.
            </div>
        <?php endif; ?>

        <div class="card-body text-center">
            <!-- Bloco de Informações do Estado do Caixa -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="mb-2">
                    Saldo Atual em Dinheiro: 
                    <span class="fw-bold text-dark" id="saldoAtualTexto">
                        R$ <?php echo e(number_format($saldoAtual ?? $saldoDinheiroAtual ?? 0, 2, ',', '.')); ?>

                    </span>
                </h5>
                <p class="text-muted mb-0">
                    Limite de Alerta Configurado: 
                    <span class="fw-bold text-danger">
                        R$ <?php echo e(number_format($limiteSangria ?? 0, 2, ',', '.')); ?>

                    </span>
                </p>
            </div>

            <?php if(isset($bloquearPDV) && $bloquearPDV): ?>
                <div class="alert alert-danger fw-bold fs-5 shadow-sm mb-4">
                    PDV BLOQUEADO<br>
                    <span class="fs-6 fw-normal">Realize a sangria para continuar liberando novas vendas.</span>
                </div>
            <?php else: ?>
                <div class="alert alert-warning fw-bold fs-5 shadow-sm mb-4">
                    Recomendado realizar sangria preventiva.
                </div>
            <?php endif; ?>

            <hr>

            <h5 class="text-primary fw-bold mt-4">💰 Valor sugerido para retirada:</h5>
            <h2 class="display-6 fw-bold text-success mb-4" id="valorSugeridoTexto">
                R$ <?php echo e(number_format($saldoAtual ?? $saldoDinheiroAtual ?? 0, 2, ',', '.')); ?>

            </h2>
            <p class="text-muted mb-4">Oriente a operadora a retirar este valor físico do caixa.</p>

            <!-- Formulário de Transação -->
            <form id="formSangria" action="<?php echo e(route('caixa.sangria.registrar', $caixa->id)); ?>" method="POST" class="w-50 mx-auto text-start">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label for="valorInput" class="form-label fw-bold">Valor da Sangria (R$)</label>
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
                           min="0.01" 
                           max="<?php echo e($saldoAtual ?? $saldoDinheiroAtual ?? 0); ?>"
                           value="<?php echo e(old('valor', number_format($saldoAtual ?? $saldoDinheiroAtual ?? 0, 2, '.', ''))); ?>" 
                           <?php echo e(($saldoAtual ?? $saldoDinheiroAtual ?? 0) <= 0 ? 'disabled' : ''); ?> 
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

                <div class="mb-4">
                    <label for="motivo" class="form-label fw-bold">Motivo da Retirada</label>
                    <select name="motivo" id="motivo" class="form-select <?php $__errorArgs = ['motivo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" <?php echo e(($saldoAtual ?? $saldoDinheiroAtual ?? 0) <= 0 ? 'disabled' : ''); ?> required>
                        <option value="">Selecione um motivo...</option>
                        <option value="manual" <?php echo e(old('motivo') == 'manual' ? 'selected' : ''); ?>>Manual / Rotina</option>
                        <option value="limite_excedido" <?php echo e(old('motivo') == 'limite_excedido' || (isset($bloquearPDV) && $bloquearPDV) ? 'selected' : ''); ?>>Limite do Caixa Excedido</option>
                        <option value="encerramento" <?php echo e(old('motivo') == 'encerramento' ? 'selected' : ''); ?>>Encerramento de Turno</option>
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

                <!-- Painel de Ações e Botões Dinâmicos -->
                <div class="d-flex flex-column align-items-center gap-2 mt-4">
                    <div class="d-flex justify-content-center gap-3 w-100">
                        <button type="submit" id="btnSangria" class="btn btn-success fw-bold px-4" <?php echo e(($saldoAtual ?? $saldoDinheiroAtual ?? 0) <= 0 ? 'disabled' : ''); ?>>
                            ✅ Efetuar Sangria
                        </button>
                        <a href="<?php echo e(route('pdv.index')); ?>" class="btn btn-secondary fw-bold px-4">
                            🔙 Voltar ao PDV
                        </a>
                    </div>

                    <!-- Contêiner de Impressão de Cupom Térmico (Exibe dinamicamente via JS pós-registro) -->
                    <div id="imprimirContainer" class="mt-2 w-100 text-center d-none">
                        <a href="#" id="btnImprimirSangria" target="_blank" class="btn btn-primary fw-bold px-4 w-50">
                            🖨️ Imprimir Comprovante
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formSangria');
    const btn = document.getElementById('btnSangria');
    const saldoSpan = document.getElementById('saldoAtualTexto');
    const valorSugerido = document.getElementById('valorSugeridoTexto');
    const inputValor = document.getElementById('valorInput');
    const selectMotivo = document.getElementById('motivo');
    const imprimirContainer = document.getElementById('imprimirContainer');
    const btnImprimir = document.getElementById('btnImprimirSangria');

    if (!form || !btn) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        btn.disabled = true;
        btn.innerText = 'Processando...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            let data = null;
            try {
                data = await response.json();
            } catch (_) {
                throw new Error('Resposta inválida do servidor.');
            }

            if (!response.ok) {
                throw new Error(data.erro || data.message || 'Erro ao registrar sangria.');
            }

            if (data.success) {
                alert('Sangria realizada com sucesso.');
                
                const novoSaldo = parseFloat(data.saldo_atual ?? 0);

                // Atualiza elementos visuais da interface de forma síncrona
                if (saldoSpan) saldoSpan.innerText = novoSaldo.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                if (valorSugerido) valorSugerido.innerText = novoSaldo.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                
                // Trata exibição dinâmica do link do cupom térmico retornado pelo controller
                if (data.sangria_id && btnImprimir && imprimirContainer) {
                    btnImprimir.href = `/pdv/sangria/imprimir/${data.sangria_id}`;
                    imprimirContainer.classList.remove('d-none');
                }

                if (novoSaldo <= 0) {
                    btn.disabled = true;
                    btn.innerText = 'Saldo Zerado';
                    if (inputValor) inputValor.disabled = true;
                    if (selectMotivo) selectMotivo.disabled = true;
                } else {
                    btn.disabled = false;
                    btn.innerText = 'Efetuar Sangria';
                    if (inputValor) inputValor.value = novoSaldo.toFixed(2);
                }
                form.reset();
            }
        } catch (error) {
            alert(error.message || 'Erro inesperado.');
            btn.disabled = false;
            btn.innerText = 'Efetuar Sangria';
        }
    });

    /* Proteção contra cache do navegador (back button) */
    window.addEventListener("pageshow", function (event) {
        if (event.persisted && btn) {
            btn.disabled = false;
            btn.innerText = 'Efetuar Sangria';
        }
    });

    // Executa validação de carga útil na montagem da tela
    validarSaldoAoCarregar();
});

function validarSaldoAoCarregar() {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.ok ? response.json() : null)
        .then(data => {
            if (!data || typeof data.saldo_atual === 'undefined') return;

            let saldo = parseFloat(data.saldo_atual);
            const btn = document.getElementById('btnSangria');
            const input = document.getElementById('valorInput');
            const select = document.getElementById('motivo');
            const saldoTexto = document.getElementById('saldoAtualTexto');
            const valorSugerido = document.getElementById('valorSugeridoTexto');

            if (saldoTexto) saldoTexto.innerText = saldo.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            if (valorSugerido) valorSugerido.innerText = saldo.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            if (saldo <= 0) {
                if (btn) { btn.disabled = true; btn.innerText = 'Saldo Zerado'; }
                if (input) input.disabled = true;
                if (select) select.disabled = true;
            }
        }).catch(err => console.warn('Modo híbrido HTML ativo.'));
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/sangria_form.blade.php ENDPATH**/ ?>