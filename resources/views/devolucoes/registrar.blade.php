@extends('layouts.app')

@section('content')

<div class="container py-4 bg-info bg-opacity-10 rounded shadow-sm">
   @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm p-3 d-flex align-items-center mb-4" role="alert" style="border-left: 4px solid #dc3545 !important; border-radius: 8px;">
            <!-- Ícone de Atenção do Bootstrap Icons -->
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
            
            <div class="w-100 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <strong class="d-block text-dark" style="font-size: 0.95rem;">Operação Bloqueada</strong>
                    <span class="text-muted medium">Já existe uma devolução pendente para este item. Por favor, aguarde a análise antes de registrar uma nova solicitação.</span>
                </div>
                
                <!-- Botão de Ação Rápida para o usuário resolver o problema -->
                <a href="{{ route('devolucoes.pendentes') }}" class="btn btn-sm btn-danger fw-bold px-3 py-2 shadow-sm text-nowrap">
                    <i class="bi bi-clock-history me-1"></i> Ver Pendentes
                </a>
            </div>
        </div>
    @endif


    <div class="mb-4">
        <h2 class="mb-1 text-dark font-weight-bold">Registrar Devolução / Troca - Venda #{{ $venda->id }}</h2>
        <h4 class="text-secondary mb-0">Cliente: {{ $venda->cliente->nome }}</h4>
    </div>

    <div class="row">
        @foreach($itensVenda as $itemVenda)
            <!-- Cards Informativos -->
            <div class="row g-3 mb-4">
                <!-- Lote Comercial -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Lote Comercial</span>
                        <strong class="text-dark">#{{ $itemVenda->numero_lote ?? 'Sem Lote'  }}</strong>
                    </div>
                </div>

                <!-- Preço Unitário -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Preço Unitário</span>
                        <strong class="text-primary">R$ {{ number_format($itemVenda->preco_unitario_item, 2, ',', '.') }}</strong>
                    </div>
                </div>

                <!-- Qtde Comprada -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Qtde Comprada</span>
                        <strong class="text-dark">{{ number_format($itemVenda->qtd_comprada, 0, ',', '.') }} un</strong>
                    </div>
                </div>

                <!-- Valor Compra -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Valor Compra</span>
                        <strong class="text-dark">R$ {{ number_format($itemVenda->valor_compra, 2, ',', '.') }}</strong>
                    </div>
                </div>

                <!-- Saldo Disponível (Mostrará 9 un) -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Saldo Disponível</span>
                        <strong class="text-success">{{ number_format($itemVenda->quantidade_disponivel, 0, ',', '.') }} un</strong>
                    </div>
                </div>

                <!-- Já Devolvido (Mostrará 1 un) -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Já Devolvido</span>
                        <strong class="text-danger">{{ number_format($itemVenda->quantidade_devolvida, 0, ',', '.') }} un</strong>
                    </div>
                </div>

                <!-- Data da Venda -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Data da Venda</span>
                        <strong class="text-dark">{{ $venda->data_venda ? \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') : '---' }}</strong>
                    </div>
                </div>

                <!-- Última Devolução -->
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm bg-light">
                        <span class="text-muted medium d-block">Última Devolução</span>
                        <strong class="text-dark">
                            {{ $itemVenda->data_ultima_devolucao ? \Carbon\Carbon::parse($itemVenda->data_ultima_devolucao)->format('d/m/Y') : 'Nenhuma' }}
                        </strong>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 3: FORMULÁRIO DE ENVIO DA DEVOLUÇÃO -->
            <div style="background-color: #ffffff !important; padding: 25px !important; width: 100% !important; box-sizing: border-box !important;">
                @if($itemVenda->quantidade_disponivel > 0)
                    <form action="{{ route('devolucoes.salvar') }}" method="POST" enctype="multipart/form-data" class="m-0">
                        @csrf
                        <!-- Vincula o ID correto do item para o método salvar ler no request -->
                        <input type="hidden" name="item_id" value="{{ $itemVenda->item_venda_id }}">

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label medium font-weight-bold text-dark mb-2">À Devolver</label>
                                <!-- Usa a propriedade data-preco e o max atualizado para o cálculo em tempo real -->
                                <input type="number" 
                                    name="quantidade" 
                                    id="qtd-devolver-{{ $itemVenda->item_venda_id }}" 
                                    min="1" 
                                    max="{{ $itemVenda->quantidade_disponivel }}" 
                                    class="form-control input-calculo-estorno" 
                                    data-item-id="{{ $itemVenda->item_venda_id }}"
                                    data-preco="{{ $itemVenda->preco_unitario_item }}"
                                    required 
                                    placeholder="0">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label medium font-weight-bold text-dark mb-2">Motivo Logístico</label>
                                <select name="motivo" class="form-control motivo-select" required></select>
                                <input type="text" name="motivo_outro" class="form-control mt-2 d-none outro-motivo-input" placeholder="Descreva o motivo">
                            </div>
                        </div>
                        <!-- Input Único para Upload de Imagem -->
                        <div class="mb-4">
                            <label class="medium font-weight-bold text-dark d-block mb-2">Evidência Visual (4 imagens)</label>
                            <div class="d-flex flex-wrap gap-3">
                                <!-- Foto 1 -->
                                <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                    <input type="file" name="imagem1" id="imagem-{{ $itemVenda->item_venda_id }}-1" class="image-input" accept="image/*" hidden>
                                    <label for="imagem-{{ $itemVenda->item_venda_id }}-1" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center cursor-pointer m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                        ➕ FOTO 1
                                        <img id="preview-{{ $itemVenda->item_venda_id }}-1" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                    </label>
                                </div>

                                <!-- Foto 2 -->
                                <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                    <input type="file" name="imagem2" id="imagem-{{ $itemVenda->item_venda_id }}-2" class="image-input" accept="image/*" hidden>
                                    <label for="imagem-{{ $itemVenda->item_venda_id }}-2" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center cursor-pointer m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                        ➕ FOTO 2
                                        <img id="preview-{{ $itemVenda->item_venda_id }}-2" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                    </label>
                                </div>

                                <!-- Foto 3 -->
                                <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                    <input type="file" name="imagem3" id="imagem-{{ $itemVenda->item_venda_id }}-3" class="image-input" accept="image/*" hidden>
                                    <label for="imagem-{{ $itemVenda->item_venda_id }}-3" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                        ➕ FOTO 3
                                        <img id="preview-{{ $itemVenda->item_venda_id }}-3" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                    </label>
                                </div>

                                <!-- Foto 4 -->
                                <div class="position-relative bg-light border border-secondary rounded shadow-sm" style="width: 80px; height: 80px; border-style: dashed !important; cursor: pointer !important;">
                                    <input type="file" name="imagem4" id="imagem-{{ $itemVenda->item_venda_id }}-4" class="image-input" accept="image/*" hidden>
                                    <label for="imagem-{{ $itemVenda->item_venda_id }}-4" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center m-0 text-muted font-weight-bold" style="font-size: 0.65rem; cursor: pointer !important;">
                                        ➕ FOTO 4
                                        <img id="preview-{{ $itemVenda->item_venda_id }}-4" class="img-preview position-absolute top-0 start-0 w-100 h-100 rounded" style="object-fit: cover; display: none; cursor: pointer !important;" alt="">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- Barra de Totalização do Estorno -->
                        <div class="border-top pt-3 mt-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted medium d-block">Previsão de Estorno Comercial</span>
                                <strong class="text-danger h4 mb-0 font-weight-bold" id="preview-estorno-{{ $itemVenda->item_venda_id }}">
                                    R$ 0,00
                                </strong>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary px-4 font-weight-bold">Voltar</a>
                                <button type="submit" class="btn btn-danger px-4 font-weight-bold">Salvar Devolução</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="alert alert-success text-center fw-bold">
                        <i class="bi bi-check-circle-fill"></i> Este item já foi totalmente devolvido.
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
                                       

{{-- Preview Script --}}
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

 <!-- Monitora a digitação da quantidade para calcular o estorno em tempo real -->
<script>
    // Monitora a digitação da quantidade para calcular o estorno em tempo real
    document.querySelectorAll('.input-qtd-devolver').forEach(input => {
        input.addEventListener('input', function() {
            // Encontra o formulário ou container atual do item
            const container = this.closest('form') || this.closest('.row');
            
            // Busca o preço unitário guardado no atributo data-preco
            const precoElement = container.querySelector('.preco-unitario-item');
            const precoUnitario = precoElement ? parseFloat(precoElement.getAttribute('data-preco')) : 0;
            
            // Pega a quantidade digitada pelo usuário
            const quantidade = parseFloat(this.value) || 0;
            
            // Faz a multiplicação matemática real
            const totalEstorno = quantidade * precoUnitario;
            
            // Formata o resultado no padrão de moeda Real (R$ 0.000,00)
            const valorFormatado = totalEstorno.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            
            // Atualiza o texto da tarja de previsão na tela
            const previewElement = container.querySelector('.preview-valor-estorno');
            if (previewElement) {
                previewElement.textContent = valorFormatado;
            }
        });
    });

    // Monitora a digitação da quantidade para calcular o estorno
    document.querySelectorAll('.input-calculo-estorno').forEach(input => {
        input.addEventListener('input', function() {
            // Pega o ID do item e o preço unitário puro direto do input
            const itemId = this.getAttribute('data-item-id');
            const precoUnitario = parseFloat(this.getAttribute('data-preco')) || 0;
            
            // Pega a quantidade digitada pelo usuário
            const quantidade = parseFloat(this.value) || 0;
            
            // Realiza o cálculo
            const totalEstorno = quantidade * precoUnitario;
            
            // Formata para a moeda Real (R$)
            const valorFormatado = totalEstorno.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            
            // Atualiza a tag forte específica desse item
            const labelPrevisao = document.getElementById(`preview-estorno-${itemId}`);
            if (labelPrevisao) {
                labelPrevisao.textContent = valorFormatado;
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

{{-- Select2 + Motivos --}}
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
        "Data de validade vencida",
        "Data de validade próxima",
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

{{-- CSS --}}
<style>
    .body {
        background-color: lavender  !important;
    }
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
@endsection