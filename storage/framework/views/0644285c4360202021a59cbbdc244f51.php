

<?php $__env->startSection('content'); ?>

<div class="container py-4">
    <?php if(session('error')): ?>
        <div class="alert alert-danger shadow-sm mb-4">
            <?php echo session('error'); ?>

        </div>
    <?php endif; ?>

    <div class="mb-4">
        <h2 class="mb-1 text-dark font-weight-bold">Registrar Devolução / Troca - Venda #<?php echo e($venda->id); ?></h2>
        <h4 class="text-secondary mb-0">Cliente: <?php echo e($venda->cliente->nome); ?></h4>
    </div>

    <div class="row">
        <?php $__currentLoopData = $venda->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemVenda): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $qtdDevolvida = $itemVenda->devolucoes
                    ->whereIn('status', ['aprovada', 'concluida'])
                    ->sum('quantidade');

                $qtdDisponivel = $itemVenda->quantidade - $qtdDevolvida;
                $valorExtornado = $qtdDevolvida * $itemVenda->preco_unitario;
                $jaDevolvido = $qtdDisponivel <= 0;
                $devolucoes = $itemVenda->devolucoes ?? collect();
            ?>

            
            <div class="col-12 mb-4">
                <div class="card shadow-sm border border-secondary rounded-3 overflow-hidden position-relative">

                    <?php if($jaDevolvido): ?>
                        <div class="bg-danger text-white text-center py-2 font-weight-bold tracking-wider small">
                            PRODUTO JÁ DEVOLVIDO
                        </div>
                    <?php endif; ?>

                    <div class="card-body" style="padding: 0 !important; display: flex !important; flex-direction: column !important;">
                           
                        <div style="background-color: #f0fdf4 !important; border-bottom: 3px solid #bbf7d0 !important; padding: 20px 25px !important; width: 100% !important; display: flex !important; align-items: center !important; justify-content: space-between !important; box-sizing: border-box !important;">
                            <div style="display: flex !important; align-items: center !important;">
                                <?php if($itemVenda->produto->imagem): ?>
                                    <img src="<?php echo e(asset('storage/' . $itemVenda->produto->imagem)); ?>" class="rounded border bg-white shadow-sm" style="width: 65px !important; height: 65px !important; object-fit: cover !important;" />
                                <?php else: ?>
                                    <div class="bg-white rounded border shadow-sm d-flex align-items-center justify-content-center text-muted small font-weight-bold" style="width: 65px !important; height: 65px !important;">S/F</div>
                                <?php endif; ?>
                                <div style="margin-left: 15px !important;">
                                    <span style="color: #16a34a !important;color:snow;padding:10px; font-size: 0.8rem !important; font-weight: 700 !important; text-transform: uppercase !important; tracking-wide: 0.5px !important; display: block !important; margin-bottom: 4px !important;">
                                        Dados do Cliente & Identificação
                                    </span>
                                    
                                    <!-- DADOS DO CLIENTE EXPANDIDOS -->
                                    <h5 style="color: #0f172a !important; font-weight: 700 !important; margin-bottom: 4px !important; margin-top: 0 !important; font-size: 1.25rem !important;">
                                        Comprador: <?php echo e($venda->cliente->nome); ?>

                                    </h5>
                                    
                                    <!-- Sublinha com dados de contato e documento do cliente -->
                                    <div class="d-flex flex-wrap gap-3 mb-2 text-secondary small font-weight-medium" style="font-size: 0.85rem !important; color: #475569 !important;">
                                        <?php if(!empty($venda->cliente->cpf_cnpj) || !empty($venda->cliente->cpf)): ?>
                                            <span><strong>Doc:</strong> <?php echo e($venda->cliente->cpf_cnpj ?? $venda->cliente->cpf); ?></span>
                                        <?php endif; ?>
                                        <?php if(!empty($venda->cliente->telefone) || !empty($venda->cliente->celular)): ?>
                                            <span><span class="text-muted">|</span> <strong>Tel:</strong> <?php echo e($venda->cliente->telefone ?? $venda->cliente->celular); ?></span>
                                        <?php endif; ?>
                                        <?php if(!empty($venda->cliente->email)): ?>
                                            <span><span class="text-muted">|</span> <strong>E-mail:</strong> <?php echo e($venda->cliente->email); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <strong style="color: #1e293b !important; font-size: 1.05rem !important; font-weight: 600 !important; display: block !important;">
                                        Item para Análise: <span class="text-primary"><?php echo e($itemVenda->produto->nome); ?></span>
                                    </strong>
                                </div>
                            </div>
                            <div style="text-align: right !important; min-width: 140px !important;">
                                <span style="color: #64748b !important; font-size: 0.75rem !important; font-weight: 700 !important; text-transform: uppercase !important; display: block !important; margin-bottom: 2px !important;">Venda / Produto</span>
                                <strong style="color: #0f172a !important; font-size: 1.1rem !important; font-weight: 700 !important; display: block !important;">
                                    Venda #<?php echo e($venda->id); ?>

                                </strong>
                                <strong style="color: #16a34a !important; font-size: 0.9rem !important; font-weight: 700 !important; display: block !important;">
                                    ID SKU: 000<?php echo e($itemVenda->produto_id); ?>

                                </strong>
                            </div>
                        </div>

                        
                        <div style="background-color: #f0fdf4 !important; border-bottom: 3px solid #bbf7d0 !important; padding: 20px 25px !important; width: 100% !important; display: flex !important; align-items: center !important; justify-content: space-between !important; box-sizing: border-box !important;">
                          
                        <div style="display: flex !important; align-items: center !important;">
                                <?php if($itemVenda->produto->imagem): ?>
                                    <img src="<?php echo e(asset('storage/' . $itemVenda->produto->imagem)); ?>" class="rounded border bg-white shadow-sm" style="width: 65px !important; height: 65px !important; object-fit: cover !important;" />
                                <?php else: ?>
                                    <div class="bg-white rounded border shadow-sm d-flex align-items-center justify-content-center text-muted small font-weight-bold" style="width: 65px !important; height: 65px !important;">S/F</div>
                                <?php endif; ?>
                                <div style="margin-left: 15px !important;">
                                    <span style="color:#16a34a  !important; color: snow; padding: 10px; font-size: 0.8rem !important; font-weight: 700 !important; text-transform: uppercase !important; tracking-wide: 0.5px !important; display: block !important; margin-bottom: 2px !important;">Nome do Produto</span>
                                    <strong style="color: #0f172a !important; font-size: 1.3rem !important; font-weight: 700 !important; display: block !important;"><?php echo e($itemVenda->produto->nome); ?></strong>
                                </div>
                            </div>
                            <div style="text-align: right !important;">
                                <span style="color: #64748b !important; font-size: 0.75rem !important; font-weight: 700 !important; text-transform: uppercase !important; display: block !important; margin-bottom: 2px !important;">Produto ID</span>
                                <strong style="color: #0f172a !important; font-size: 1.1rem !important; font-weight: 700 !important; display: block !important;">#000<?php echo e($itemVenda->produto_id); ?></strong>
                            </div>
                        </div>

                        
                        <div style="background-color: #f8fafc !important; border-bottom: 2px solid #e2e8f0 !important; padding: 25px !important; width: 100% !important; box-sizing: border-box !important;">
                           
                            <span style="color:snow;padding: 10px;background-color: #475569 !important; font-size: 0.8rem !important; font-weight: 700 !important; text-transform: uppercase !important; tracking-wide: 0.5px !important; display: block !important; margin-bottom: 15px !important;">Item da Venda & Especificações</span>
                            
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Lote Comercial</span>
                                        <strong class="text-dark font-weight-bold h5 mb-0 d-block">#000<?php echo e($itemVenda->lote_id); ?></strong>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Preço Unitário</span>
                                        <strong class="text-primary font-weight-bold h5 mb-0 d-block">R$ <?php echo e(number_format($itemVenda->preco_unitario, 2, ',', '.')); ?></strong>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Qtde Comprada</span>
                                        <strong class="text-dark font-weight-bold h5 mb-0 d-block"><?php echo e($itemVenda->quantidade); ?> un</strong>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Valor Compra</span>
                                        <strong class="text-dark font-weight-bold h5 mb-0 d-block">R$ <?php echo e(number_format($itemVenda->subtotal, 2, ',', '.')); ?></strong>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Saldo Disponível</span>
                                        <strong class="text-success font-weight-bold h5 mb-0 d-block"><?php echo e($qtdDisponivel); ?> un</strong>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Já Devolvido</span>
                                        <strong class="text-warning font-weight-bold h5 mb-0 d-block"><?php echo e($qtdDevolvida); ?> <?php echo e($itemVenda->produto->unidadeMedida->sigla); ?></strong>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Data da Venda</span>
                                        <strong class="text-dark font-weight-bold h5 mb-0 d-block"><?php echo e(\Carbon\Carbon::parse($itemVenda->venda->data_venda)->format('d/m/Y')); ?></strong>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3 bg-white border rounded shadow-sm h-100">
                                        <span class="text-muted small d-block mb-1">Última Devolução</span>
                                        <strong class="text-dark font-weight-bold h5 mb-0 d-block">
                                            <?php echo e($devolucoes->count() > 0 ? $devolucoes->last()->created_at->format('d/m/Y') : '—'); ?>

                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div style="background-color: #ffffff !important; padding: 25px !important; width: 100% !important; box-sizing: border-box !important;">
                            <?php if(!$jaDevolvido): ?>
                                <form action="<?php echo e(route('devolucoes.salvar')); ?>" method="POST" enctype="multipart/form-data" class="m-0">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="item_id" value="<?php echo e($itemVenda->id); ?>">

                                    <span style="color:snow;background-color: rgb(80, 58, 2) !important; font-size: 0.8rem !important; font-weight: 700 !important; text-transform: uppercase !important; tracking-wide: 0.5px !important; display: block !important; margin-bottom: 15px !important; padding: 10px !important;">Seção Estornos & Devoluções</span>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label small font-weight-bold text-dark mb-2">À Devolver</label>
                                            <input type="number" name="quantidade" min="1" max="<?php echo e($qtdDisponivel); ?>" class="form-control" style="border: 2px solid #cbd5e0 !important; height: 42px !important; font-weight: 600 !important;" required placeholder="0">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label small font-weight-bold text-dark mb-2">Motivo Logístico</label>
                                            <select name="motivo" class="form-control motivo-select" style="border: 2px solid #cbd5e0 !important; height: 42px !important; font-weight: 600 !important;"></select>
                                            <input type="text" name="motivo_outro" class="form-control mt-2 d-none outro-motivo-input" style="border: 2px solid #cbd5e0 !important; height: 42px !important;" placeholder="Descreva detalhadamente o motivo">
                                        </div>
                                    </div>

                                    
                                    <div class="mb-4">
                                        <label class="small font-weight-bold text-dark d-block mb-2">Evidências Visuais (Até 4 imagens)</label>
                                        <div class="d-flex flex-wrap gap-3">
                                            
                                            <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                                <input type="file" name="imagem1" id="imagem-<?php echo e($itemVenda->id); ?>-1" class="image-input" accept="image/*" hidden>
                                                <label for="imagem-<?php echo e($itemVenda->id); ?>-1" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center cursor-pointer m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                                    ➕ FOTO 1
                                                    <img id="preview-<?php echo e($itemVenda->id); ?>-1" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                                </label>
                                            </div>

                                            <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                                <input type="file" name="imagem2" id="imagem-<?php echo e($itemVenda->id); ?>-2" class="image-input" accept="image/*" hidden>
                                                <label for="imagem-<?php echo e($itemVenda->id); ?>-2" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center cursor-pointer m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                                    ➕ FOTO 2
                                                    <img id="preview-<?php echo e($itemVenda->id); ?>-2" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                                </label>
                                            </div>



                                                <!-- Foto 3 -->
                                                <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                                    <input type="file" name="imagem3" id="imagem-<?php echo e($itemVenda->id); ?>-3" class="image-input" accept="image/*" hidden>
                                                    <label for="imagem-<?php echo e($itemVenda->id); ?>-3" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                                        ➕ FOTO 3
                                                        <img id="preview-<?php echo e($itemVenda->id); ?>-3" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                                    </label>
                                                </div>

                                                <!-- Foto 4 -->
                                                <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                                    <input type="file" name="imagem4" id="imagem-<?php echo e($itemVenda->id); ?>-4" class="image-input" accept="image/*" hidden>
                                                    <label for="imagem-<?php echo e($itemVenda->id); ?>-4" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                                        ➕ FOTO 4
                                                        <img id="preview-<?php echo e($itemVenda->id); ?>-4" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                                    </label>
                                                </div>


                                            </div>
                                        </div>

                                        
                                        <div class="border-top pt-3 mt-3 d-flex align-items-center justify-content-between">
                                            <div>
                                                <span class="text-muted small d-block">Previsão de Estorno Comercial</span>
                                                <strong class="text-danger h4 mb-0 font-weight-bold">R$ <?php echo e(number_format($valorExtornado, 2, ',', '.')); ?></strong>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary px-4 font-weight-bold">Voltar</a>
                                                <button type="submit" class="btn btn-danger px-4 font-weight-bold text-uppercase">Confirmar</button>
                                            </div>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center p-4 text-center border border-dashed rounded bg-light">
                                        <span class="h2 mb-2">✅</span>
                                        <strong class="text-dark d-block">Processamento Finalizado</strong>
                                        <p class="text-muted small mb-0">Esse item não possui saldos disponíveis para estorno ou novos trâmites.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

                                            
                                        


<script>
    document.querySelectorAll('.image-input').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            const previewId = 'preview-' + this.id.split('-')[1] + '-' + this.id.split('-')[2];
            const preview = document.getElementById(previewId);

            if (file) {
                const reader = new FileReader();
                reader.onload = ev => {
                    preview.src = ev.target.result;
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.classList.remove('has-image');
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Seleciona todos os inputs de arquivo que estão dentro das caixas de imagem
        const imageInputs = document.querySelectorAll('.image-input');

        imageInputs.forEach(input => {
            input.addEventListener('change', function (e) {
                const file = e.target.files[0];
                
                // Extrai o ID do item e o número da foto a partir do ID do input (ex: imagem-12-1)
                const inputIdParts = this.id.split('-');
                const itemId = inputIdParts[1];
                const photoIndex = inputIdParts[2];
                
                // Localiza a tag img correspondente para o preview
                const previewImg = document.getElementById(`preview-${itemId}-${photoIndex}`);

                if (file && previewImg) {
                    const reader = new FileReader();

                    reader.onload = function (event) {
                        // Injeta a imagem lida no atributo src e força a exibição do elemento
                        previewImg.src = event.target.result;
                        previewImg.style.display = 'block';
                    };

                    // Lê o arquivo local selecionado pelo usuário
                    reader.readAsDataURL(file);
                }
            });
        });
    });
</script>



<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const motivos = [
        "Atraso na obra",
        "Bloqueio no acesso à obra",
        "Carga incompleta na separação",
        "Cliente comprou a mais",
        "Cliente mudou de ideia",
        "Cliente recusou a receber",
        "Cor ou tonalidade divergente",
        "Defeito de fabricação",
        "Descrição incorreta do produto",
        "Desistência após orçamento",
        "Embalagem danificada",
        "Entrega fora do prazo",
        "Erro de cadastro no sistema",
        "Erro na conferência da mercadoria",
        "Erro na separação do pedido",
        "Erro no lançamento da venda",
        "Estoque desatualizado",
        "Fornecedor enviou produto errado",
        "Item faltando no pedido",
        "Material com defeito",
        "Material incompatível com o projeto",
        "Medida ou especificação incorreta",
        "Pedido duplicado",
        "Perda de material na obra",
        "Preço divergente na compra",
        "Problema no transporte",
        "Produto avariado no transporte",
        "Produto quebrado ou avariado",
        "Produto diferente do solicitado",
        "Produto excedente na obra",
        "Produto não serviu para a obra",
        "Quantidade incorreta",
        "Tamanho ou medida incompatível",
        "Troca por preferência do cliente",
        "Variação de lote não aceita",
        "Vencimento próximo do material",
    ];

        document.querySelectorAll('.motivo-select').forEach(select => {
            motivos.forEach(m => {
                let op = new Option(m, m, false, false);
                select.appendChild(op);
            });

            $(select).select2({
                placeholder: "Selecione ou digite o motivo",
                allowClear: true,
                width: 'resolve'
            });
        });

    });
</script>


<style>
    /* Força o ponteiro de mãozinha no container e no label de clique */
    .image-container,
    .image-label,
    .img-preview {
        cursor: pointer !important;
    }
    .compact-card {
    border-radius: 10px;
    padding: 10px;
    }

    .product-img {
        width: 100px; 
        height: 100px; 
        object-fit: cover; 
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .product-name {
        font-size: 1.1rem;
    }

    .info-block {
        font-size: 0.85rem;
        line-height: 1.2rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 15px;
    }

    .form-compact .form-label {
        margin-bottom: 2px;
    }

    .image-container {
        width: 70px;
        height: 70px;
        position: relative;
    }

    .image-label {
        width: 100%;
        height: 100%;
        border: 1px dashed #ccc;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        cursor: pointer;
    }

    .img-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 6px;
    }

    .img-preview:not(.has-image)::before {
        content: "+";
        font-size: 1.6rem;
        color: #aaa;
        position: absolute;
    }

    .stamped {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
        background: rgba(255, 0, 0, 0.75);
        color: white;
        font-weight: bold;
        padding: 6px 15px;
        border-radius: 6px;
        font-size: 0.9rem;
        z-index: 20;
    }
</style>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/devolucoes/registrar.blade.php ENDPATH**/ ?>