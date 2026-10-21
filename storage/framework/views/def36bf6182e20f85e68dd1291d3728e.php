<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <h3 class="mb-1">Configuração do Backup</h3>
    <p class="text-muted mb-4">
        Parâmetros de armazenamento e segurança.
    </p>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Configuração atual</strong>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Driver</label>
                    <input class="form-control"
                           value="<?php echo e($driver); ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Disco Laravel</label>
                    <input class="form-control"
                           value="<?php echo e($disk); ?>" readonly>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Retenção atual</label>
                    <input class="form-control"
                           value="<?php echo e($retencao); ?> dias" readonly>
                </div>
            </div>

            <form method="POST"
                  action="<?php echo e(route('backups.configuracao.salvar')); ?>">
                <?php echo csrf_field(); ?>

                <label for="destinoSecundario" class="form-label">
                    Diretório secundário no servidor
                </label>

                <div class="input-group mb-3">
                    <input type="text"
                           id="destinoSecundario"
                           name="destino_secundario"
                           class="form-control"
                           maxlength="500"
                           value="<?php echo e(old('destino_secundario', $configuracao['destino_secundario'])); ?>"
                           placeholder="Selecione uma pasta do servidor">

                    <button class="btn btn-outline-primary"
                            id="abrirExplorador"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDiretorios">
                        <i class="bi bi-folder2-open"></i>
                        Procurar pasta
                    </button>
                </div>

                <div class="alert alert-info small">
                    Selecione preferencialmente um disco independente
                    do disco principal. A pasta deve existir no servidor.
                </div>

                <div class="alert alert-warning small">
                    Esta etapa configura o destino secundário.
                    A cópia automática para esse destino ainda depende
                    da integração do serviço de cópia.
                </div>

                <button type="submit" class="btn btn-primary">
                    Salvar configuração
                </button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDiretorios" tabindex="-1"
     aria-labelledby="tituloModalDiretorios" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalDiretorios">
                    Selecionar diretório do servidor
                </h5>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex gap-2 mb-3">
                    <button type="button" id="voltarDiretorio"
                            class="btn btn-outline-secondary btn-sm">
                        Voltar
                    </button>

                    <button type="button" id="listarUnidades"
                            class="btn btn-outline-secondary btn-sm">
                        Unidades
                    </button>
                </div>

                <div class="small text-muted mb-2">
                    Diretório atual
                </div>

                <div class="border rounded bg-light p-2 mb-3"
                     id="diretorioAtual">Unidades do servidor</div>

                <div class="list-group" id="listaDiretorios"></div>

                <div class="text-danger small mt-3 d-none"
                     id="erroExplorador"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="btn btn-primary"
                        id="selecionarDiretorio" disabled>
                    Selecionar esta pasta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const endpoint = <?php echo json_encode(route('backups.diretorios'), 15, 512) ?>;
    const modal = document.getElementById('modalDiretorios');
    const lista = document.getElementById('listaDiretorios');
    const atualLabel = document.getElementById('diretorioAtual');
    const erro = document.getElementById('erroExplorador');
    const selecionar = document.getElementById('selecionarDiretorio');
    const input = document.getElementById('destinoSecundario');

    let atual = '';
    let pai = '';

    async function carregar(caminho = '') {
        erro.classList.add('d-none');
        lista.replaceChildren();
        selecionar.disabled = true;

        const url = new URL(endpoint, window.location.origin);

        if (caminho) {
            url.searchParams.set('path', caminho);
        }

        try {
            const resposta = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            const dados = await resposta.json();

            if (!resposta.ok) {
                throw new Error(
                    dados.message || 'Erro ao consultar diretórios.'
                );
            }

            atual = dados.atual || '';
            pai = dados.pai || '';

            atualLabel.textContent =
                atual || 'Unidades do servidor';

            selecionar.disabled = !atual;

            if (!dados.pastas || dados.pastas.length === 0) {
                const aviso = document.createElement('div');
                aviso.className = 'text-muted p-3';
                aviso.textContent = 'Nenhuma subpasta encontrada.';
                lista.appendChild(aviso);
            }

            (dados.pastas || []).forEach(function (pasta) {
                const botao = document.createElement('button');

                botao.type = 'button';
                botao.className =
                    'list-group-item list-group-item-action text-start';

                botao.textContent = '📁 ' + pasta.nome;

                botao.addEventListener('click', function () {
                    carregar(pasta.caminho);
                });

                lista.appendChild(botao);
            });

        } catch (e) {
            erro.textContent = e.message;
            erro.classList.remove('d-none');
        }
    }

    modal.addEventListener('show.bs.modal', function () {
        carregar('');
    });

    document.getElementById('voltarDiretorio')
        .addEventListener('click', function () {
            carregar(pai || '');
        });

    document.getElementById('listarUnidades')
        .addEventListener('click', function () {
            carregar('');
        });

    selecionar.addEventListener('click', function () {
        if (!atual) return;

        input.value = atual;

        bootstrap.Modal.getInstance(modal).hide();
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/backups-admin/configuracao.blade.php ENDPATH**/ ?>