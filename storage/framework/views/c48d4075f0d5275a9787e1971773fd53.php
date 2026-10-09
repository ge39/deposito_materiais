

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">
        Editar Funcionário: <?php echo e($funcionario->nome); ?>

    </h2>

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

    <form action="<?php echo e(route('funcionarios.update', $funcionario->id)); ?>"
          method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row mb-3 g-3">
            <div class="col-md-4">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text"
                       name="cpf"
                       id="cpf"
                       class="form-control"
                       value="<?php echo e(old('cpf', $funcionario->cpf)); ?>"
                       maxlength="14"
                       inputmode="numeric"
                       required>
            </div>

            <div class="col-md-4">
                <label for="nome" class="form-label">Nome</label>
                <input type="text"
                       name="nome"
                       id="nome"
                       class="form-control"
                       value="<?php echo e(old('nome', $funcionario->nome)); ?>"
                       maxlength="255"
                       required>
            </div>

            <div class="col-md-4">
                <label for="funcao" class="form-label">Função</label>
                <select name="funcao"
                        id="funcao"
                        class="form-select"
                        required>
                    <?php $__currentLoopData = \App\Models\Funcionario::FUNCOES_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($valor); ?>"
                                <?php if(old('funcao', $funcionario->funcao) === $valor): echo 'selected'; endif; ?>>
                            <?php echo e($rotulo); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div class="row mb-3 g-3">
            <div class="col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text"
                       name="telefone"
                       id="telefone"
                       class="form-control"
                       value="<?php echo e(old('telefone', $funcionario->telefone)); ?>"
                       maxlength="50">
            </div>

            <div class="col-md-4">
                <label for="email" class="form-label">E-mail</label>
                <input type="email"
                       name="email"
                       id="email"
                       class="form-control"
                       value="<?php echo e(old('email', $funcionario->email)); ?>"
                       maxlength="100">
            </div>

            <div class="col-md-4">
                <label for="data_admissao" class="form-label">
                    Data de Admissão
                </label>
                <input type="date"
                       name="data_admissao"
                       id="data_admissao"
                       class="form-control"
                       value="<?php echo e(old('data_admissao', $funcionario->data_admissao?->format('Y-m-d'))); ?>">
            </div>
        </div>

        <div class="row mb-3 g-3">
            <div class="col-md-3">
                <label for="endereco" class="form-label">Endereço</label>
                <input type="text"
                       name="endereco"
                       id="endereco"
                       class="form-control"
                       value="<?php echo e(old('endereco', $funcionario->endereco)); ?>"
                       maxlength="255">
            </div>

            <div class="col-md-2">
                <label for="numero" class="form-label">Número</label>
                <input type="text"
                       name="numero"
                       id="numero"
                       class="form-control"
                       value="<?php echo e(old('numero', $funcionario->numero)); ?>"
                       maxlength="12">
            </div>

            <div class="col-md-3">
                <label for="bairro" class="form-label">Bairro</label>
                <input type="text"
                       name="bairro"
                       id="bairro"
                       class="form-control"
                       value="<?php echo e(old('bairro', $funcionario->bairro)); ?>"
                       maxlength="255">
            </div>

            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text"
                       name="cidade"
                       id="cidade"
                       class="form-control"
                       value="<?php echo e(old('cidade', $funcionario->cidade)); ?>"
                       maxlength="255">
            </div>
        </div>

        <div class="row mb-3 g-3">
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <input type="text"
                       name="estado"
                       id="estado"
                       class="form-control text-uppercase"
                       value="<?php echo e(old('estado', $funcionario->estado)); ?>"
                       maxlength="2">
            </div>

            <div class="col-md-4 d-flex align-items-center">
                <div class="form-check mt-2">
                    <input type="checkbox"
                           name="ativo"
                           id="ativo"
                           class="form-check-input"
                           value="1"
                           <?php if(old('ativo', $funcionario->ativo)): echo 'checked'; endif; ?>>
                    <label for="ativo" class="form-check-label">Ativo</label>
                </div>
            </div>
        </div>

        <div class="mb-3"
             id="opcao-rastreamento"
             <?php if(old('funcao', $funcionario->funcao) !== 'motorista'): ?> hidden <?php endif; ?>>
            <div class="border rounded bg-light p-3">
                <div class="form-check">
                    <input type="checkbox"
                           name="rastreamento_habilitado"
                           id="rastreamento_habilitado"
                           class="form-check-input"
                           value="1"
                           <?php if(old('rastreamento_habilitado', $funcionario->rastreamento_habilitado)): echo 'checked'; endif; ?>>
                    <label for="rastreamento_habilitado"
                           class="form-check-label fw-semibold">
                        Permitir compartilhamento da localização durante viagens ativas
                    </label>
                </div>

                <div class="form-text mt-2">
                    Esta opção habilita o recurso no cadastro. A localização
                    somente será compartilhada após autorização no celular e
                    durante uma viagem ativa.
                </div>

                <div class="small mt-2">
                    <?php if($funcionario->localizacao_revogada_em): ?>
                        <span class="text-danger">
                            Consentimento revogado em
                            <?php echo e($funcionario->localizacao_revogada_em->format('d/m/Y H:i')); ?>.
                        </span>
                    <?php elseif($funcionario->localizacao_consentida_em): ?>
                        <span class="text-success">
                            Consentimento registrado em
                            <?php echo e($funcionario->localizacao_consentida_em->format('d/m/Y H:i')); ?>.
                        </span>
                    <?php else: ?>
                        <span class="text-muted">
                            Aguardando autorização pelo celular do motorista.
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes"
                      id="observacoes"
                      class="form-control"
                      rows="3"
                      maxlength="250"><?php echo e(old('observacoes', $funcionario->observacoes)); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Atualizar</button>
        <a href="<?php echo e(route('funcionarios.index')); ?>"
           class="btn btn-secondary">Voltar</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const campoFuncao = document.getElementById('funcao');
        const blocoRastreamento = document.getElementById(
            'opcao-rastreamento'
        );
        const campoRastreamento = document.getElementById(
            'rastreamento_habilitado'
        );
        const campoCpf = document.getElementById('cpf');
        const alerta = document.getElementById('alerta');

        function atualizarOpcaoRastreamento() {
            const motorista = campoFuncao.value === 'motorista';

            blocoRastreamento.hidden = ! motorista;
            campoRastreamento.disabled = ! motorista;

            if (! motorista) {
                campoRastreamento.checked = false;
            }
        }

        campoFuncao.addEventListener(
            'change',
            atualizarOpcaoRastreamento
        );

        campoCpf.addEventListener('input', function () {
            let cpf = this.value.replace(/\D/g, '').slice(0, 11);
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            this.value = cpf;
        });

        if (alerta) {
            window.setTimeout(function () {
                alerta.style.display = 'none';
            }, 5000);
        }

        atualizarOpcaoRastreamento();
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\funcionarios\edit.blade.php ENDPATH**/ ?>