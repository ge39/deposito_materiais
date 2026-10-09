

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Editar Cliente: <?php echo e($cliente->nome); ?></h2>

    <?php if(session('success')): ?>
        <div class="alert alert-success" id="alerta">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger" id="alerta">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form
        action="<?php echo e(route('clientes.update', $cliente->id)); ?>"
        method="POST"
        id="formCliente"
        novalidate
    >
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <h4 class="mb-3">Dados Pessoais</h4>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="nome" class="form-label">Nome</label>
                <input
                    type="text"
                    name="nome"
                    id="nome"
                    class="form-control"
                    value="<?php echo e(old('nome', $cliente->nome)); ?>"
                    required
                >
                <?php $__errorArgs = ['nome'];
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

            <div class="col-md-4">
                <label for="tipo" class="form-label">Tipo</label>
                <?php
                    $tipoSelecionado = old('tipo', $cliente->tipo);
                ?>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="fisica" <?php echo e($tipoSelecionado === 'fisica' ? 'selected' : ''); ?>>
                        Pessoa Física
                    </option>
                    <option value="juridica" <?php echo e($tipoSelecionado === 'juridica' ? 'selected' : ''); ?>>
                        Pessoa Jurídica
                    </option>
                </select>
                <?php $__errorArgs = ['tipo'];
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

            <div class="col-md-4 mb-3">
                <label for="tipo_cliente" class="form-label font-weight-bold">
                    Perfil de Preço / Tabela de Markup
                </label>

                <?php
                    $tipoClienteSelecionado = old(
                        'tipo_cliente',
                        $cliente->tipo_cliente ?? 'markup_1'
                    );
                ?>

                <select
                    name="tipo_cliente"
                    id="tipo_cliente"
                    class="form-select form-control"
                >
                    <option value="markup_1" <?php echo e($tipoClienteSelecionado === 'markup_1' ? 'selected' : ''); ?>>
                        Varejo (Markup 1 - Padrão)
                    </option>
                    <option value="markup_2" <?php echo e($tipoClienteSelecionado === 'markup_2' ? 'selected' : ''); ?>>
                        Empresa / Empreiteiro (Markup 2)
                    </option>
                    <option value="markup_3" <?php echo e($tipoClienteSelecionado === 'markup_3' ? 'selected' : ''); ?>>
                        Atacado (Markup 3)
                    </option>
                </select>

                <div
                    class="mt-2 p-2 rounded border bg-light text-muted small"
                    id="box_explicativo_perfil"
                    style="min-height: 50px; line-height: 1.4;"
                ></div>

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

            <div class="col-md-4">
                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                <input
                    type="date"
                    name="data_nascimento"
                    id="data_nascimento"
                    class="form-control"
                    value="<?php echo e(old('data_nascimento', $cliente->data_nascimento?->format('Y-m-d'))); ?>"
                >
                <?php $__errorArgs = ['data_nascimento'];
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
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="sexo" class="form-label">Sexo</label>
                <?php
                    $sexoSelecionado = old('sexo', $cliente->sexo);
                ?>
                <select name="sexo" id="sexo" class="form-select">
                    <option value="masculino" <?php echo e($sexoSelecionado === 'masculino' ? 'selected' : ''); ?>>
                        Masculino
                    </option>
                    <option value="feminino" <?php echo e($sexoSelecionado === 'feminino' ? 'selected' : ''); ?>>
                        Feminino
                    </option>
                    <option value="outro" <?php echo e($sexoSelecionado === 'outro' ? 'selected' : ''); ?>>
                        Outro
                    </option>
                </select>
                <?php $__errorArgs = ['sexo'];
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

            <div class="col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input
                    type="text"
                    name="telefone"
                    id="telefone"
                    class="form-control"
                    value="<?php echo e(old('telefone', $cliente->telefone)); ?>"
                >
                <?php $__errorArgs = ['telefone'];
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

            <div class="col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="<?php echo e(old('email', $cliente->email)); ?>"
                >
                <?php $__errorArgs = ['email'];
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
        </div>

        <h4 class="mb-3">Endereço</h4>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="cep" class="form-label">CEP</label>
                <div class="input-group">
                    <span class="input-group-text text-primary">
                        <i class="bi bi-geo-alt"></i>
                    </span>
                    <input
                        type="text"
                        name="cep"
                        id="cep"
                        class="form-control"
                        value="<?php echo e(old('cep', $cliente->cep)); ?>"
                        placeholder="00000-000"
                        maxlength="9"
                        pattern="[0-9]{5}-[0-9]{3}"
                        title="Informe o CEP no formato 00000-000"
                        inputmode="numeric"
                        autocomplete="postal-code"
                    >
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        id="btnBuscarCep"
                        onclick="consultarCepCliente()"
                    >
                        <i class="bi bi-search me-1"></i>
                        Buscar
                    </button>
                </div>
                <div class="form-text">
                    Digite os oito números do CEP e clique em Buscar.
                </div>
                <?php $__errorArgs = ['cep'];
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

            <div class="col-md-4">
                <label for="endereco" class="form-label">Endereço</label>
                <input
                    type="text"
                    name="endereco"
                    id="endereco"
                    class="form-control"
                    value="<?php echo e(old('endereco', $cliente->endereco)); ?>"
                    autocomplete="address-line1"
                >
                <?php $__errorArgs = ['endereco'];
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

            <div class="col-md-4">
                <label for="numero" class="form-label">Número</label>
                <input
                    type="text"
                    name="numero"
                    id="numero"
                    class="form-control"
                    value="<?php echo e(old('numero', $cliente->numero)); ?>"
                    autocomplete="address-line2"
                >
                <?php $__errorArgs = ['numero'];
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
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="bairro" class="form-label">Bairro</label>
                <input
                    type="text"
                    name="bairro"
                    id="bairro"
                    class="form-control"
                    value="<?php echo e(old('bairro', $cliente->bairro)); ?>"
                >
                <?php $__errorArgs = ['bairro'];
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

            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input
                    type="text"
                    name="cidade"
                    id="cidade"
                    class="form-control"
                    value="<?php echo e(old('cidade', $cliente->cidade)); ?>"
                    autocomplete="address-level2"
                >
                <?php $__errorArgs = ['cidade'];
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

            <div class="col-md-4">
                <label for="uf" class="form-label">Estado</label>
                <input
                    type="text"
                    name="estado"
                    id="uf"
                    class="form-control text-uppercase"
                    value="<?php echo e(old('estado', $cliente->estado)); ?>"
                    maxlength="2"
                    autocomplete="address-level1"
                >
                <?php $__errorArgs = ['estado'];
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
        </div>

        <h4 class="mb-3">Documentos</h4>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                <input
                    type="text"
                    name="cpf_cnpj"
                    id="cpf_cnpj"
                    class="form-control"
                    value="<?php echo e(old('cpf_cnpj', $cliente->cpf_cnpj)); ?>"
                >
                <?php $__errorArgs = ['cpf_cnpj'];
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

            <div class="col-md-4">
                <label for="rg_ie" class="form-label">RG/Inscrição Estadual</label>
                <input
                    type="text"
                    name="rg_ie"
                    id="rg_ie"
                    class="form-control"
                    value="<?php echo e(old('rg_ie', $cliente->rg_ie)); ?>"
                >
                <?php $__errorArgs = ['rg_ie'];
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

            <div class="col-md-4">
                <label for="orgao_emissor" class="form-label">Órgão Emissor</label>
                <input
                    type="text"
                    name="orgao_emissor"
                    id="orgao_emissor"
                    class="form-control"
                    value="<?php echo e(old('orgao_emissor', $cliente->orgao_emissor)); ?>"
                >
                <?php $__errorArgs = ['orgao_emissor'];
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
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="data_emissao" class="form-label">Data de Emissão</label>
                <input
                    type="date"
                    name="data_emissao"
                    id="data_emissao"
                    class="form-control"
                    value="<?php echo e(old('data_emissao', $cliente->data_emissao?->format('Y-m-d'))); ?>"
                >
                <?php $__errorArgs = ['data_emissao'];
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

            <div class="col-md-4">
                <label for="observacoes" class="form-label">Observações</label>
                <textarea
                    name="observacoes"
                    id="observacoes"
                    rows="1"
                    class="form-control"
                ><?php echo e(old('observacoes', $cliente->observacoes)); ?></textarea>
                <?php $__errorArgs = ['observacoes'];
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
        </div>

        <div class="col-md-2 form-check mt-2">
            <input
                type="checkbox"
                name="ativo"
                id="ativo"
                class="form-check-input"
                value="1"
                <?php echo e(old('ativo', $cliente->ativo) ? 'checked' : ''); ?>

            >
            <label for="ativo" class="form-check-label">Ativo</label>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-success">
                Atualizar
            </button>
            <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-secondary">
                Voltar
            </a>
        </div>
    </form>
</div>

<script>
    window.consultarCepCliente = function () {
        const cepInput = document.getElementById('cep');

        if (!cepInput || typeof window.buscarCep !== 'function') {
            return;
        }

        window.buscarCep(
            cepInput,
            '#endereco',
            '#bairro',
            '#cidade',
            '#uf'
        );
    };

    function atualizarDicaPerfil() {
        const select = document.getElementById('tipo_cliente');
        const box = document.getElementById('box_explicativo_perfil');

        if (!select || !box) {
            return;
        }

        const descricoes = {
            markup_1:
                '🛒 <strong>Varejo (Novo):</strong> Aplica a margem padrão (Markup 1) e limite de desconto 1. Ideal para consumidores finais esporádicos.',
            markup_2:
                '🏗️ <strong>Empresa / Empreiteiro:</strong> Preços diferenciados (Markup 2) para construtoras, empreiteiros e prestadores de serviço parceiros.',
            markup_3:
                '📦 <strong>Atacado:</strong> Margem mínima de lucro (Markup 3) para grandes volumes de compra ou faturamento corporativo estrito.'
        };

        box.innerHTML =
            descricoes[select.value]
            || '💡 Selecione um perfil para ver as diretrizes de preço.';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const formulario = document.getElementById('formCliente');
        const cepInput = document.getElementById('cep');
        const alerta = document.getElementById('alerta');
        const tipoCliente = document.getElementById('tipo_cliente');
        const uf = document.getElementById('uf');

        atualizarDicaPerfil();

        tipoCliente?.addEventListener(
            'change',
            atualizarDicaPerfil
        );

        uf?.addEventListener('input', function () {
            uf.value = uf.value
                .replace(/[^a-zA-Z]/g, '')
                .slice(0, 2)
                .toUpperCase();
        });

        cepInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                consultarCepCliente();
            }
        });

        formulario?.addEventListener('submit', function (event) {
            const cepNumerico = String(cepInput?.value || '')
                .replace(/\D/g, '');

            if (cepNumerico !== '' && cepNumerico.length !== 8) {
                event.preventDefault();
                event.stopPropagation();

                cepInput.setCustomValidity(
                    'O CEP deve possuir exatamente oito números.'
                );
                cepInput.classList.add('is-invalid');
                cepInput.focus();
                cepInput.reportValidity();
                return;
            }

            if (cepInput && cepNumerico.length === 8) {
                cepInput.value = cepNumerico.replace(
                    /^(\d{5})(\d{3})$/,
                    '$1-$2'
                );
                cepInput.setCustomValidity('');
                cepInput.classList.remove('is-invalid');
            }

            if (!formulario.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                const primeiroCampoInvalido =
                    formulario.querySelector(':invalid');

                primeiroCampoInvalido?.focus();
                primeiroCampoInvalido?.reportValidity();
            }
        });

        if (alerta) {
            window.setTimeout(function () {
                alerta.style.display = 'none';
            }, 5000);
        }
    });
</script>

<script src="<?php echo e(asset('js/form-masks.js')); ?>"></script>
<script src="<?php echo e(asset('js/cep.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\clientes\edit.blade.php ENDPATH**/ ?>