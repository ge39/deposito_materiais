

<?php $__env->startSection('content'); ?>
<div class="container" style="max-width: 520px;">

    <h4 class="mb-3 fw-bold text-center">Finalizar Venda</h4>

    
    <div class="alert alert-secondary fs-5 text-center">
        Total a pagar: <strong id="total-venda">R$ 0,00</strong>
    </div>

    
    <div class="card shadow-sm">
        <div class="card-body">

            <?php
                $formas = [
                    'dinheiro' => 'Dinheiro',
                    'cartao_credito' => 'Cartão de Crédito',
                    'cartao_debito' => 'Cartão de Débito',
                    'pix' => 'PIX',
                    'carteira' => 'Carteira'
                ];
            ?>

            <?php $__currentLoopData = $formas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row mb-2 align-items-center">
                <div class="col-5">
                    <label class="form-label fw-semibold"><?php echo e($label); ?></label>
                </div>
                <div class="col-7">
                    <input type="number" step="0.01" class="form-control pagamento" data-forma="<?php echo e($key); ?>" placeholder="0,00">
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </div>

    
    <div class="mt-3 d-grid gap-2">
        <button type="button" class="btn btn-success btn-lg" id="btnFinalizarVenda">F6 - Finalizar Venda</button>
        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">Cancelar</button>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ====== Pega o total do PDV.index ======
    const totalVenda = window.carrinhoTotal || 0;
    const totalVendaEl = document.getElementById('total-venda');
    totalVendaEl.textContent = totalVenda.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    const btnFinalizarVenda = document.getElementById('btnFinalizarVenda');
    const inputsPagamento = document.querySelectorAll('.pagamento');

    // ====== Preenche automaticamente próximo input com restante ======
    inputsPagamento.forEach(input => {
        input.addEventListener('keydown', function(e){
            if(e.key === 'Enter'){
                e.preventDefault();

                // Soma dos valores já digitados
                let totalDigitado = 0;
                inputsPagamento.forEach(i => totalDigitado += parseFloat(i.value) || 0);

                const restante = totalVenda - totalDigitado;

                if(restante > 0){
                    // Próximo input vazio
                    const proximoVazio = Array.from(inputsPagamento).find(i => !i.value || parseFloat(i.value) === 0);
                    if(proximoVazio){
                        proximoVazio.value = restante.toFixed(2);
                        proximoVazio.focus();
                    }
                } else {
                    // Se não há saldo restante, move foco para finalizar
                    btnFinalizarVenda.focus();
                }
            }
        });
    });

    // ====== Botão Finalizar Venda ======
    btnFinalizarVenda.addEventListener('click', () => {

        const pagamentos = [];
        inputsPagamento.forEach(input => {
            const valor = parseFloat(input.value) || 0;
            if(valor > 0){
                pagamentos.push({
                    forma_pagamento: input.dataset.forma,
                    valor: valor
                });
            }
        });

        const somaPagamentos = pagamentos.reduce((a,b) => a + b.valor, 0);

        if(somaPagamentos < totalVenda){
            alert('O valor total dos pagamentos é menor que o total da venda');
            return;
        }

        const payload = {
            cliente_id: window.clienteId || null,
            total: totalVenda,
            itens: window.carrinhoItens || [],
            pagamentos: pagamentos
        };

        fetch('/pdv/vendas', {
            method: 'POST',
            headers: {
                'Content-Type':'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            if(res.success){
                alert('Venda finalizada: #' + res.venda_id);
                window.location.href = '/pdv';
            } else {
                alert('Erro: ' + res.erro);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro ao finalizar venda');
        });

    });

});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/finalizar.blade.php ENDPATH**/ ?>