

<?php $__env->startSection('content'); ?>
<div style="font-family:Arial, sans-serif; background:#f0f0f0; padding:10px;">
    <!-- Header -->
    <div style="background:#004080; color:white; padding:10px; display:flex; align-items:center;">
        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="YZIDRO" style="height:40px; margin-right:10px;">
        <h2>YZIDRO - PDV</h2>
    </div>

    <div style="display:flex; gap:10px; margin-top:10px;">
        <!-- Lado esquerdo -->
        <div style="flex:1; background:#004080; color:white; padding:10px; display:flex; flex-direction:column; gap:10px;">
            <!-- Cliente -->
            <div>
                <label>Cliente</label>
                <input type="text" id="cliente" style="width:100%; padding:5px;">
                <small>Crédito disponível: R$ <span id="credito">0,00</span></small>
            </div>

            <!-- Desconto e Forma de Pagamento -->
            <div style="display:flex; gap:5px;">
                <div style="flex:1;">
                    <label>Desconto (R$)</label>
                    <input type="number" id="desconto" value="0,00" style="width:100%; padding:5px;">
                </div>
                <div style="flex:1;">
                    <label>Forma de Pagamento</label>
                    <select id="formaPagamento" style="width:100%; padding:5px;">
                        <option value="caixa">Caixa</option>
                        <option value="cartao">Cartão</option>
                    </select>
                </div>
            </div>

            <!-- Total -->
            <div>
                <h3>Total: R$ <span id="total">0,00</span></h3>
            </div>

            <!-- Pagamentos -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:5px;">
                <div>
                    <label>Dinheiro</label>
                    <input type="number" id="dinheiro" value="0,00" style="width:100%; padding:5px;">
                </div>
                <div>
                    <label>Cheque</label>
                    <input type="number" id="cheque" value="0,00" style="width:100%; padding:5px;">
                </div>
                <div>
                    <label>Cartão Crédito</label>
                    <input type="number" id="cartaoCredito" value="0,00" style="width:100%; padding:5px;">
                </div>
                <div>
                    <label>Cartão Débito</label>
                    <input type="number" id="cartaoDebito" value="0,00" style="width:100%; padding:5px;">
                </div>
            </div>

            <!-- Saldo -->
            <div>
                <h4>Saldo: R$ <span id="saldo">0,00</span></h4>
            </div>

            <!-- Botão finalizar -->
            <button id="finalizarVenda" style="background:#e74c3c; color:white; padding:10px; border:none; width:100%;">FINALIZAR VENDA</button>

            <!-- Atalhos -->
            <div style="font-size:12px; margin-top:10px; background:#00264d; padding:5px;">
                <p>F2 - Cliente | F10 - Emitir NF-e | F12 - Emitir CF-e-SAT</p>
                <p>Ctrl+U - Utilizar Crédito | Ctrl+C - Indicar Crédito</p>
                <p>S - Desconto(R$) | P - Desconto(%) | Q - Acréscimo(R$) | W - Acréscimo(%)</p>
                <p>Ctrl+D - CPF | D - Dinheiro | C - Cheque | X - Cartão Crédito | A - Cartão Débito | Esc - Sair</p>
            </div>
        </div>

        <!-- Lado direito - Lista de produtos -->
        <div style="flex:2; background:white; padding:10px; max-height:600px; overflow-y:auto;">
            <h3 style="border-bottom:2px solid #004080; padding-bottom:5px;">Lista de Produtos</h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#004080; color:white;">
                        <th>Item</th>
                        <th>Código de Barras</th>
                        <th>Descrição</th>
                        <th>Qtd</th>
                        <th>Vl. Unit</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="carrinho">
                    <tr><td colspan="6" style="text-align:center;">Nenhum produto adicionado</td></tr>
                </tbody>
            </table>
            <h3 style="text-align:right; margin-top:10px;">Subtotal: R$ <span id="subtotal">0,00</span></h3>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/pdv/index.blade.php ENDPATH**/ ?>