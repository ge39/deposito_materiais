

<?php $__env->startSection('content'); ?>
<div class="alert alert-info">
    <strong>Como corrigir as divergências:</strong>
    <ol class="mb-0">
        <li>Compare o <strong>Valor Sistema</strong> com o <strong>Valor Informado</strong>.</li>
        <li>Ajuste o campo <strong>Ajuste Corrigido</strong> até que ele fique <strong>igual ao Valor Sistema</strong>.</li>
        <li>O botão <strong>Salvar Ajustes</strong> será liberado automaticamente quando todos os valores estiverem corretos.</li>
    </ol>
</div>

<h2>Correção de Divergências - Caixa #<?php echo e($caixa->id); ?></h2>


<table class="table table-sm">
    <tr><th>Status:</th><td><?php echo e($caixa->status); ?></td></tr>
    <tr><th>Operador:</th><td><?php echo e($caixa->usuario->name ?? ''); ?></td></tr>
    <tr><th>Fundo de Troco:</th><td>R$ <?php echo e(number_format($caixa->fundo_troco, 2, ',', '.')); ?></td></tr>
    <tr><th>Total Entradas:</th><td>R$ <?php echo e(number_format($total_entradas,2,',','.')); ?></td></tr>
    <tr><th>Total Saídas:</th><td>R$ <?php echo e(number_format($total_saidas,2,',','.')); ?></td></tr>
    <tr><th>Total Sistema:</th><td>R$ <?php echo e(number_format($totalGeralSistema,2,',','.')); ?></td></tr>
    <tr><th>Divergência Total:</th><td>R$ <?php echo e(number_format($divergencia,2,',','.')); ?></td></tr>
</table>


<h3>Divergências</h3>
<!-- <form action="<?php echo e(route('fechamento.ajustar', $caixa->id)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Forma de Pagamento</th>
                <th>Valor Sistema</th>
                <th>Valor Informado</th>
                <th>Diferença</th>
                <th>Ajuste Corrigido</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $divergencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forma => $dif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(ucfirst($forma)); ?></td>
                    <td>R$ <?php echo e(number_format($totaisPorForma[$forma] ?? 0,2,',','.')); ?></td>
                    <td>R$ <?php echo e(number_format(($totaisPorForma[$forma] ?? 0) + $dif,2,',','.')); ?></td>
                    <td>R$ <?php echo e(number_format($dif,2,',','.')); ?></td>
                    <td>
                        <td>
                            <input type="hidden"
                                class="valor-sistema"
                                value="<?php echo e($totaisPorForma[$forma] ?? 0); ?>">

                            <input type="text"
                                class="form-control ajuste-corrigido"
                                name="formas[<?php echo e($forma); ?>]"
                                value="<?php echo e(number_format(($totaisPorForma[$forma] ?? 0) + $dif, 2, ',', '.')); ?>">
                        </td>

                    </td>

                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <div id="mensagemValidacao" class="alert alert-warning">
        Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.
    </div>

    <button type="submit" class="btn btn-primary" id="btnSalvar" disabled>
        Salvar Ajustes
    </button>

</form> -->

<form action="<?php echo e(route('fechamento.ajustar', $caixa->id)); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="row mb-3 font-weight-bold border-bottom pb-2">
        <div class="col-2">Forma de Pagamento</div>
        <div class="col-2">Valor Sistema</div>
        <div class="col-2">Valor Informado</div>
        <div class="col-2">Diferença</div>
        <div class="col-4">Ajuste Corrigido</div>
    </div>

    <?php $__currentLoopData = $divergencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forma => $dif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $valorSistema = $totaisPorForma[$forma] ?? 0;
            $valorInformado = $valorSistema + $dif;
            $classeDif = $dif == 0 ? 'text-success' : 'text-danger';
        ?>

        <div class="row row-forma">
            <div class="col forma-nome">
                <?php echo e(ucfirst(str_replace('_',' ', $forma))); ?>

            </div>
            <div class="col valor-sistema-container">
                <input type="hidden" class="valor-sistema" value="<?php echo e($valorSistema); ?>">
                R$ <?php echo e(number_format($valorSistema,2,',','.')); ?>

            </div>
            <div class="col valor-informado">
                R$ <?php echo e(number_format($valorInformado,2,',','.')); ?>

            </div>
            <div class="col diferenca <?php echo e($dif == 0 ? 'text-success' : 'text-danger'); ?>">
                R$ <?php echo e(number_format($dif,2,',','.')); ?>

            </div>
            <div class="col ajuste-corrigido-container">
                <input type="text" class="form-control ajuste-corrigido"
                    name="formas[<?php echo e($forma); ?>]"
                    value="<?php echo e(number_format($valorInformado, 2, ',', '.')); ?>">
            </div>
        </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


    <div id="mensagemValidacao" class="alert alert-warning">
        Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.
    </div>

    <button type="submit"
            class="btn btn-primary"
            id="btnSalvar"
            disabled>
        Salvar Ajustes
    </button>
</form>

<!-- Botão fora do form -->
<button type="button" id="btnTestarHTML" class="btn btn-secondary mb-3">
    Testar Ajustes no HTML
</button>



<h3>Movimentações do Caixa</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Data</th>
            <th>Observação</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $caixa->movimentacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($mov->tipo); ?></td>
                <td>R$ <?php echo e(number_format($mov->valor,2,',','.')); ?></td>
                <td><?php echo e($mov->data_movimentacao); ?></td>
                <td><?php echo e($mov->observacao); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {

        const botao    = document.getElementById('btnSalvar');
        const mensagem = document.getElementById('mensagemValidacao');

        function parseBR(valor) {
            if (!valor) return NaN;

            valor = valor.replace(/[^\d,.-]/g, '');

            if (valor.indexOf(',') !== -1) {
                valor = valor.replace(/\./g, '').replace(',', '.');
            }

            return parseFloat(valor);
        }

        function formatBR(valor) {
            return valor.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        function validarAjustes() {
            let todosIguais = true;

            document.querySelectorAll('.ajuste-corrigido').forEach(input => {
                const td = input.closest('td');

                const valorSistema = Number(
                    td.querySelector('.valor-sistema').value
                );

                const valorAjustado = parseBR(input.value);

                if (isNaN(valorSistema) || isNaN(valorAjustado)) {
                    todosIguais = false;
                    return;
                }

                if (Math.abs(valorSistema - valorAjustado) > 0.009) {
                    todosIguais = false;
                }
            });

            botao.disabled = !todosIguais;

            if (todosIguais) {
                mensagem.classList.remove('alert-warning');
                mensagem.classList.add('alert-success');
                mensagem.innerText =
                    'Todos os valores conferem com o sistema. Você já pode salvar os ajustes.';
            } else {
                mensagem.classList.remove('alert-success');
                mensagem.classList.add('alert-warning');
                mensagem.innerText =
                    'Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.';
            }
        }

        document.querySelectorAll('.ajuste-corrigido').forEach(input => {

            input.addEventListener('input', validarAjustes);

            input.addEventListener('focus', function () {
                const valor = parseBR(input.value);
                if (!isNaN(valor)) {
                    input.value = valor.toFixed(2).replace('.', ',');
                }
            });

            input.addEventListener('blur', function () {
                const valor = parseBR(input.value);
                if (!isNaN(valor)) {
                    input.value = formatBR(valor);
                }
            });

        });

        validarAjustes();
    });
</script> -->

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const botao = document.getElementById('btnSalvar');
        const mensagem = document.getElementById('mensagemValidacao');

        // Converte string BR para float
        function parseBR(valor) {
            if (!valor) return NaN;
            valor = valor.replace(/[^\d,.-]/g, '');
            if (valor.indexOf(',') !== -1) {
                valor = valor.replace(/\./g, '').replace(',', '.');
            }
            return parseFloat(valor);
        }

        // Formata float para R$ BR
        function formatBR(valor) {
            return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function validarAjustes() {
            let todosIguais = true;

            // percorre todas as linhas de forma de pagamento
            document.querySelectorAll('.row-forma').forEach(row => {
                const valorSistemaInput = row.querySelector('.valor-sistema');
                const ajusteInput = row.querySelector('.ajuste-corrigido');

                const valorSistema = parseFloat(valorSistemaInput.value);
                const valorAjustado = parseBR(ajusteInput.value);

                if (isNaN(valorSistema) || isNaN(valorAjustado)) {
                    todosIguais = false;
                    return;
                }

                if (Math.abs(valorSistema - valorAjustado) > 0.009) {
                    todosIguais = false;
                }
            });

            botao.disabled = !todosIguais;

            if (todosIguais) {
                mensagem.classList.remove('alert-warning');
                mensagem.classList.add('alert-success');
                mensagem.innerText =
                    'Todos os valores conferem com o sistema. Você já pode salvar os ajustes.';
            } else {
                mensagem.classList.remove('alert-success');
                mensagem.classList.add('alert-warning');
                mensagem.innerText =
                    'Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.';
            }
        }

        // Formata campos e adiciona validação em tempo real
        document.querySelectorAll('.ajuste-corrigido').forEach(input => {

            input.addEventListener('input', validarAjustes);

            input.addEventListener('focus', function () {
                const valor = parseBR(input.value);
                if (!isNaN(valor)) input.value = valor.toFixed(2).replace('.', ',');
            });

            input.addEventListener('blur', function () {
                const valor = parseBR(input.value);
                if (!isNaN(valor)) input.value = formatBR(valor);
            });

        });

        // Valida tudo na inicialização
        validarAjustes();
    });

</script>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {

        const form = document.querySelector('form'); // seleciona o form principal

        form.addEventListener('submit', function (e) {
            e.preventDefault(); // impede o envio real para o servidor

            const dados = {};

            // percorre todos os inputs de ajuste corrigido
            document.querySelectorAll('.ajuste-corrigido').forEach(input => {
                // extrai o nome da forma, ex: "formas[dinheiro]" → "dinheiro"
                const forma = input.name.match(/formas\[(.+)\]/)[1];

                // converte valor BR "1.234,56" → 1234.56
                const valor = parseFloat(input.value.replace(/\./g,'').replace(',', '.')) || 0;

                dados[forma] = valor;
            });

            console.log('Dados capturados do form:', dados);
            alert('Confira o console do navegador (F12) para ver os dados.');
        });

    });
</script> -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const botao = document.getElementById('btnSalvar');
    const mensagem = document.getElementById('mensagemValidacao');

    function parseBR(valor) {
        if (!valor) return NaN;
        valor = valor.replace(/[^\d,.-]/g, '');
        if (valor.indexOf(',') !== -1) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        }
        return parseFloat(valor);
    }

    function formatBR(valor) {
        return valor.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    function validarAjustes() {
        let todosIguais = true;
        document.querySelectorAll('.row-forma').forEach(row => {
            const input = row.querySelector('.ajuste-corrigido');
            const valorSistema = parseBR(row.querySelector('.valor-sistema').value);
            const valorAjustado = parseBR(input.value);

            if (isNaN(valorSistema) || isNaN(valorAjustado)) {
                todosIguais = false;
                return;
            }

            if (Math.abs(valorSistema - valorAjustado) > 0.009) {
                todosIguais = false;
            }
        });

        botao.disabled = !todosIguais;

        if (todosIguais) {
            mensagem.classList.remove('alert-warning');
            mensagem.classList.add('alert-success');
            mensagem.innerText = 'Todos os valores conferem com o sistema. Você já pode salvar os ajustes.';
        } else {
            mensagem.classList.remove('alert-success');
            mensagem.classList.add('alert-warning');
            mensagem.innerText = 'Ainda existem divergências. Ajuste todos os campos para que fiquem iguais ao valor do sistema.';
        }
    }

    document.querySelectorAll('.ajuste-corrigido').forEach(input => {
        input.addEventListener('input', validarAjustes);

        input.addEventListener('focus', function () {
            const valor = parseBR(input.value);
            if (!isNaN(valor)) input.value = valor.toFixed(2).replace('.', ',');
        });

        input.addEventListener('blur', function () {
            const valor = parseBR(input.value);
            if (!isNaN(valor)) input.value = formatBR(valor);
        });
    });

    validarAjustes();
});

</script>

<!-- //botao teste html --!-->

<script>
document.addEventListener('DOMContentLoaded', function () {

    function parseBR(valor) {
        if (!valor) return 0;
        // remove tudo que não seja número, vírgula ou ponto
        valor = valor.replace(/[^\d,.-]/g, '');
        if (valor.indexOf(',') !== -1) {
            valor = valor.replace(/\./g, '').replace(',', '.');
        }
        return parseFloat(valor) || 0;
    }

    const btnTestar = document.getElementById('btnTestarHTML');

    btnTestar.addEventListener('click', function () {

        const dados = {};

        // percorre todos os inputs ajuste-corrigido
        document.querySelectorAll('.ajuste-corrigido').forEach(input => {
            const match = input.name.match(/formas\[(.+)\]/);
            if (!match) return;
            const forma = match[1];

            const valor = parseBR(input.value);

            dados[forma] = valor;
        });

        console.log('Dados capturados do HTML (sem enviar form):', dados);
        alert('Confira o console do navegador (F12) para ver os dados.');
    });
});
</script>








<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/fechamento_caixa/corrigir_divergencias.blade.php ENDPATH**/ ?>