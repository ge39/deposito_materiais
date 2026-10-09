<?php
    $regraAtual = $regra ?? null;
?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <strong>Não foi possível salvar.</strong>

        <ul class="mb-0 mt-2">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($erro); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">

        <div class="row g-3">

            <div class="col-md-6">
                <label
                    for="funcionario_id"
                    class="form-label"
                >
                    Vendedor
                </label>

                <select
                    name="funcionario_id"
                    id="funcionario_id"
                    class="form-select"
                    required
                >
                    <option value="">
                        Selecione...
                    </option>

                    <?php $__currentLoopData = $vendedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendedor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option
                            value="<?php echo e($vendedor->id); ?>"
                            <?php if(
                                old(
                                    'funcionario_id',
                                    $regraAtual?->funcionario_id
                                ) == $vendedor->id
                            ): echo 'selected'; endif; ?>
                        >
                            <?php echo e($vendedor->nome); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>


            <div class="col-md-3">
                <label
                    for="escopo"
                    class="form-label"
                >
                    Escopo
                </label>

                <select
                    name="escopo"
                    id="escopo"
                    class="form-select"
                    required
                >
                    <?php $__currentLoopData = [
                        'geral' => 'Geral',
                        'categoria' => 'Categoria',
                        'produto' => 'Produto',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <option
                            value="<?php echo e($valor); ?>"
                            <?php if(
                                old(
                                    'escopo',
                                    $regraAtual?->escopo ?? 'geral'
                                ) === $valor
                            ): echo 'selected'; endif; ?>
                        >
                            <?php echo e($rotulo); ?>

                        </option>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>


            <div class="col-md-3">
                <label
                    for="percentual"
                    class="form-label"
                >
                    Comissão (%)
                </label>

                <input
                    type="number"
                    name="percentual"
                    id="percentual"
                    class="form-control"
                    step="0.0001"
                    min="0"
                    max="100"
                    value="<?php echo e(old(
                        'percentual',
                        $regraAtual?->percentual
                    )); ?>"
                >
            </div>


            <div
                class="col-md-6"
                id="box_categoria"
            >
                <label
                    for="categoria_id"
                    class="form-label"
                >
                    Categoria
                </label>

                <select
                    name="categoria_id"
                    id="categoria_id"
                    class="form-select"
                >
                    <option value="">
                        Selecione...
                    </option>

                    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option
                            value="<?php echo e($categoria->id); ?>"
                            <?php if(
                                old(
                                    'categoria_id',
                                    $regraAtual?->categoria_id
                                ) == $categoria->id
                            ): echo 'selected'; endif; ?>
                        >
                            <?php echo e($categoria->nome); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>


            <div
                class="col-md-6"
                id="box_produto"
            >
                <label
                    for="produto_id"
                    class="form-label"
                >
                    Produto
                </label>

                <select
                    name="produto_id"
                    id="produto_id"
                    class="form-select"
                >
                    <option value="">
                        Selecione...
                    </option>

                    <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option
                            value="<?php echo e($produto->id); ?>"
                            <?php if(
                                old(
                                    'produto_id',
                                    $regraAtual?->produto_id
                                ) == $produto->id
                            ): echo 'selected'; endif; ?>
                        >
                            <?php echo e($produto->nome); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>


            <div
                class="col-12"
                id="box_percentual_produto"
            >
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="usar_percentual_produto"
                        id="usar_percentual_produto"
                        value="1"
                        class="form-check-input"
                        <?php if(
                            old(
                                'usar_percentual_produto',
                                $regraAtual?->usar_percentual_produto
                            )
                        ): echo 'checked'; endif; ?>
                    >

                    <label
                        class="form-check-label"
                        for="usar_percentual_produto"
                    >
                        Usar percentual cadastrado no produto
                    </label>
                </div>
            </div>


            <div class="col-md-3">
                <label
                    for="vigencia_inicio"
                    class="form-label"
                >
                    Início da vigência
                </label>

                <input
                    type="date"
                    name="vigencia_inicio"
                    id="vigencia_inicio"
                    class="form-control"
                    value="<?php echo e(old(
                        'vigencia_inicio',
                        $regraAtual?->vigencia_inicio?->format('Y-m-d')
                    )); ?>"
                >
            </div>


            <div class="col-md-3">
                <label
                    for="vigencia_fim"
                    class="form-label"
                >
                    Fim da vigência
                </label>

                <input
                    type="date"
                    name="vigencia_fim"
                    id="vigencia_fim"
                    class="form-control"
                    value="<?php echo e(old(
                        'vigencia_fim',
                        $regraAtual?->vigencia_fim?->format('Y-m-d')
                    )); ?>"
                >
            </div>


            <div class="col-md-6">
                <label
                    for="observacao"
                    class="form-label"
                >
                    Observação
                </label>

                <input
                    type="text"
                    name="observacao"
                    id="observacao"
                    maxlength="500"
                    class="form-control"
                    value="<?php echo e(old(
                        'observacao',
                        $regraAtual?->observacao
                    )); ?>"
                >
            </div>


            <div class="col-12">
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="ativo"
                        id="ativo"
                        value="1"
                        class="form-check-input"
                        <?php if(
                            old(
                                'ativo',
                                $regraAtual?->ativo ?? true
                            )
                        ): echo 'checked'; endif; ?>
                    >

                    <label
                        class="form-check-label"
                        for="ativo"
                    >
                        Regra ativa
                    </label>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="mt-3 d-flex gap-2">

    <button
        type="submit"
        class="btn btn-primary"
    >
        Salvar
    </button>

    <a
        href="<?php echo e(route('comissoes.regras.index')); ?>"
        class="btn btn-secondary"
    >
        Voltar
    </a>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const escopo = document.getElementById('escopo');

    const categoria =
        document.getElementById('box_categoria');

    const produto =
        document.getElementById('box_produto');

    const percentualProduto =
        document.getElementById('box_percentual_produto');

    const checkboxProduto =
        document.getElementById('usar_percentual_produto');

    const percentual =
        document.getElementById('percentual');


    function atualizarTela() {

        const valor = escopo.value;

        categoria.style.display =
            valor === 'categoria'
                ? ''
                : 'none';

        produto.style.display =
            valor === 'produto'
                ? ''
                : 'none';

        percentualProduto.style.display =
            valor === 'produto'
                ? ''
                : 'none';

        atualizarPercentual();
    }


    function atualizarPercentual() {

        const usarProduto =
            escopo.value === 'produto'
            && checkboxProduto.checked;

        percentual.disabled = usarProduto;

        if (usarProduto) {
            percentual.value = '';
        }
    }


    escopo.addEventListener(
        'change',
        atualizarTela
    );

    checkboxProduto.addEventListener(
        'change',
        atualizarPercentual
    );

    atualizarTela();
});
</script><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\comissoes\regras\_form.blade.php ENDPATH**/ ?>