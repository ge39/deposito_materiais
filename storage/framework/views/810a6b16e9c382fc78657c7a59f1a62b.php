

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Novo Cliente</h2>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger" id="alerta">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('clientes.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="row g-3">

            <!-- Dados Pessoais -->
            <div class="col-md-4">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" value="<?php echo e(old('nome')); ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="fisica" <?php if(old('tipo')=='fisica'): ?> selected <?php endif; ?>>Pessoa Física</option>
                    <option value="juridica" <?php if(old('tipo')=='juridica'): ?> selected <?php endif; ?>>Pessoa Jurídica</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="tipo_cliente" class="form-label font-weight-bold">
                    Perfil de Preço / Tabela de Markup
                </label>
                
                <!-- Componente Select corrigido (sem a variável $cliente) -->
                <select name="tipo_cliente" id="tipo_cliente" class="form-select form-control" onchange="atualizarDicaPerfil()">
                    <option value="markup_1" <?php echo e(old('tipo_cliente', 'markup_1') === 'markup_1' ? 'selected' : ''); ?>>
                        Varejo (Markup 1 - Padrão)
                    </option>
                    <option value="markup_2" <?php echo e(old('tipo_cliente') === 'markup_2' ? 'selected' : ''); ?>>
                        Empresa / Empreiteiro (Markup 2)
                    </option>
                    <option value="markup_3" <?php echo e(old('tipo_cliente') === 'markup_3' ? 'selected' : ''); ?>>
                        Atacado (Markup 3)
                    </option>
                </select>

                <!-- Box Informativo Dinâmico -->
                <div class="mt-2 p-2 rounded border bg-light text-muted small" id="box_explicativo_perfil" style="min-height: 50px; line-height: 1.4;">
                    <!-- O texto explicativo será injetado aqui via JS -->
                </div>

                <?php $__errorArgs = ['tipo_cliente'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Executa imediatamente para exibir a dica do Markup 1 (Padrão)
                    atualizarDicaPerfil();
                });

                function atualizarDicaPerfil() {
                    const select = document.getElementById('tipo_cliente');
                    const box = document.getElementById('box_explicativo_perfil');
                    const valor = select.value;

                    const descricoes = {
                        'markup_1': '🛒 <strong>Varejo (Novo):</strong> Aplica a margem padrão (Markup 1) e limite de desconto 1. Ideal para consumidores finais esporádicos.',
                        'markup_2': '🏗️ <strong>Empresa / Empreiteiro:</strong> Preços diferenciados (Markup 2) para construtoras, empreiteiros e prestadores de serviço parceiros.',
                        'markup_3': '📦 <strong>Atacado:</strong> Margem mínima de lucro (Markup 3) para grandes volumes de compra ou faturamento corporativo estrito.'
                    };

                    box.innerHTML = descricoes[valor] || '💡 Selecione um perfil para ver as diretrizes de desconto de preço.';
                }
            </script>

            <div class="col-md-4">
                <label class="form-label">CPF/CNPJ</label>
                <input type="text" name="cpf_cnpj" class="form-control" value="<?php echo e(old('cpf_cnpj')); ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">RG / IE</label>
                <input type="text" name="rg_ie" class="form-control" value="<?php echo e(old('rg_ie')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Órgão Emissor</label>
                <input type="text" name="orgao_emissor" class="form-control" value="<?php echo e(old('orgao_emissor')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Data de Emissão</label>
                <input type="date" name="data_emissao" class="form-control" value="<?php echo e(old('data_emissao')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Data de Nascimento</label>
                <input type="date" name="data_nascimento" class="form-control" value="<?php echo e(old('data_nascimento')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Sexo</label>
                <select name="sexo" class="form-select">
                    <option value="">Selecione</option>
                    <option value="masculino" <?php if(old('sexo')=='masculino'): ?> selected <?php endif; ?>>Masculino</option>
                    <option value="feminino" <?php if(old('sexo')=='feminino'): ?> selected <?php endif; ?>>Feminino</option>
                    <option value="outro" <?php if(old('sexo')=='outro'): ?> selected <?php endif; ?>>Outro</option>
                </select>
            </div>

            <!-- Contato -->
            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control" value="<?php echo e(old('telefone')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>">
            </div>

            <!-- Cep -->
            <div class="col-md-4">
                <label class="form-label text-primary">BUSCAR CEP</label>
                <input type="text" name="cep" id="cep"
                    onblur="buscarCep(this, '#endereco', '#bairro', '#cidade', '#uf')"
                    class="form-control" value="<?php echo e(old('cep')); ?>" maxlength="9">
            </div>

            <div class="col-md-4">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" id="endereco" class="form-control" value="<?php echo e(old('endereco')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Número</label>
                <input type="text" name="numero" class="form-control" value="<?php echo e(old('numero')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Bairro</label>
                <input type="text" name="bairro" id="bairro" class="form-control" value="<?php echo e(old('bairro')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Cidade</label>
                <input type="text" name="cidade" id="cidade" class="form-control" value="<?php echo e(old('cidade')); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <input type="text" name="estado" id="uf" class="form-control" value="<?php echo e(old('estado')); ?>" maxlength="2">
            </div>

            <!-- Financeiro -->
            <div class="col-md-4">
                <label class="form-label">Limite de Crédito (R$)</label>
                <input type="number" step="0.01" name="limite_credito" class="form-control" value="<?php echo e(old('limite_credito')); ?>">
            </div>

            <div class="col-md-8">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" rows="3" class="form-control"><?php echo e(old('observacoes')); ?></textarea>
            </div>

            <div class="col-md-12 form-check mt-2">
                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" <?php echo e(old('ativo', 1) ? 'checked' : ''); ?>>
                <label for="ativo" class="form-check-label">Ativo</label>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Salvar</button>
            <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div>

<!-- <script>
    // Máscara simples para CPF/CNPJ
    const cpfCnpj = document.querySelector('[name="cpf_cnpj"]');
    cpfCnpj?.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        if(val.length <= 11) {
            val = val.replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        }
        this.value = val;
    });

    // Alerta automático desaparecendo
    const alerta = document.getElementById('alerta');
    if (alerta) {
        setTimeout(() => alerta.style.display='none', 3000);
    }
</script> -->
<script src="<?php echo e(asset('js/form-masks.js')); ?>"></script>
<script src="<?php echo e(asset('js/cep.js')); ?>"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/clientes/create.blade.php ENDPATH**/ ?>