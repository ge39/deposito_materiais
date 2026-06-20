<style>
    /* Alvo: A div pai que está envelopando o conteúdo nesta tela */
    body > div.container.mt-4 {
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 20px !important;
        padding-right: 20px !important;
        
    }
    .imagem-produto {
    max-width: 100px;
    max-height: 100px;
    border: 1px solid #ccc;
    padding: 3px;
    cursor: zoom-in;
    transition: transform 0.3s ease;
}

.imagem-produto:hover {
    transform: scale(5); /* aumenta 100% */
    z-index: 9999;
    position: relative;
}
</style>

@extends('layouts.app') {{-- Ou o seu layout padrão --}}

@section('content')
<div class="container-fluid px-2 my-4 text-dark">
    <div class="card shadow-sm bg-light text-dark fw-bold">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📦 Editar Produto - {{ $produto->nome ?? "Não disponivel"}}</h5>
            <a href="{{ route('produtos.index') }}" class="btn btn-sm btn-light">Voltar</a>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('produtos.update', $produto->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- SEÇÃO 1: DADOS BÁSICOS -->
                <h6 class="text-primary border-bottom pb-2 mb-3">1. Informações Básicas</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome do Produto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $produto->nome) }}" required>
                        @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku', $produto->sku) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="codigo_barras" class="form-label">Código de Barras <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" value="{{ old('codigo_barras', $produto->codigo_barras) }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="categoria_id" class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Selecione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}"  {{ old('categoria_id', $produto->categoria_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="fornecedor_id" class="form-label">Fornecedor <span class="text-danger">*</span></label>
                        <select class="form-select" id="fornecedor_id" name="fornecedor_id" required>
                            <option value="">Selecione...</option>
                           @foreach($fornecedores as $forn)
                                <option value="{{ $forn->id }}"
                                    {{ old('fornecedor_id', $produto->fornecedor_id ?? '') == $forn->id ? 'selected' : '' }}>
                                    {{ $forn->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="marca_id" class="form-label">Marca <span class="text-danger">*</span></label>
                        <select class="form-select" id="marca_id" name="marca_id">
                            <option value="">Selecione...</option>
                            @foreach($marcas as $marca)
                                <option value="{{ $marca->id }}" {{ old('marca_id', $produto->marca_id ?? '') == $marca->id ? 'selected' : '' }}>
                                    {{ $marca->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="unidade_medida_id" class="form-label">Unidade de Medida <span class="text-danger">*</span></label>
                        <select class="form-select" id="unidade_medida_id" name="unidade_medida_id">
                            <option value="">Selecione...</option>
                             @foreach($unidades as $unidade)
                                <option value="{{ $unidade->id }}" {{ old('unidade_medida_id', $produto->unidade_medida_id) == $unidade->id ? 'selected' : '' }}>
                                    {{ $unidade->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- SEÇÃO 2: ENGENHARIA DE CUSTOS -->
                <h6 class="text-primary border-bottom pb-2 mb-3">2. Custos e Despesas Operacionais</h6>
                <div class="row g-3 mb-4 p-3 bg-light rounded border">
                    <div class="col-md-3">
                        <label for="preco_compra_atual" class="form-label">Preço de Compra (Nota) (R$)</label>
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            class="form-control calc-trigger"
                            id="preco_compra_atual"
                            name="preco_compra_atual"
                            value="{{ old('preco_compra_atual', $produto->preco_compra_atual ?? '0') }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="custo_frete_unidade" class="form-label">Frete Rateado/Unid. (R$)</label>
                        <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="custo_frete_unidade" name="custo_frete_unidade"  value="{{ old('custo_frete_unidade', $produto->custo_frete_unidade ?? '0.00') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="custo_imposto_entrada" class="form-label">Imposto Entrada/ST (R$)</label>
                        <input type="number" min="0" min="0"  step="0.01" class="form-control calc-trigger" id="custo_imposto_entrada" name="custo_imposto_entrada" value="{{old('custo_imposto_entrada', $produto->custo_imposto_entrada ?? '0.00') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="custo_real_entrada" class="form-label text-danger font-weight-bold">＝ Custo Real de Entrada (R$)</label>
                        <input type="number" min="0"  step="0.01" class="form-control bg-white text-danger fw-bold" id="custo_real_entrada" name="custo_real_entrada" readonly value="0.00">
                    </div>
                    
                    <div class="col-md-4 mt-3">
                        <label for="percentual_imposto_saida" class="form-label">Imposto sobre Venda (%)</label>
                        <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="percentual_imposto_saida" name="percentual_imposto_saida" value="{{ old('percentual_imposto_saida', '0.00') }}">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="percentual_comissao" class="form-label">Comissão do Vendedor (%)</label>
                        <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="percentual_comissao" name="percentual_comissao" value="{{ old('percentual_comissao',$produto->percentual_comissao ?? '0.00') }}">
                    </div>
                    <div class="col-md-4 mt-3">
                        <label for="percentual_taxa_cartao" class="form-label">Taxa Administrativa Cartão (%)</label>
                        <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="percentual_taxa_cartao" name="percentual_taxa_cartao" value="{{ old('percentual_taxa_cartao',$produto->percentual_taxa_cartao ?? '0.00') }}">
                    </div>
                </div>

                <!-- SEÇÃO 3: FORMAÇÃO DAS 3 TABELAS DE PREÇO -->
                                <!-- SEÇÃO 3: FORMAÇÃO DAS 3 TABELAS DE PREÇO -->
                <h6 class="text-primary border-bottom pb-2 mb-3">3. Definição das Tabelas de Venda (Markup por Dentro)</h6>
                <div class="row g-3 mb-4">
                    <!-- Tabela 1: Varejo -->
                    <div class="col-md-4">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white py-1">Tabela 1: Varejo / Balcão</div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label for="markup_1" class="form-label mb-1">Lucro Desejado (%)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="markup_1" name="markup_1" value="{{ old('markup_1', $produto->markup_1 ?? '0.00') }}">
                                </div>
                                <div class="mb-2">
                                    <label for="desconto_max_1" class="form-label mb-1">Limite Desconto (%)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="desconto_max_1" name="desconto_max_1" value="{{ old('desconto_max_1',$produto->desconto_max_1 ?? '0.00') }}">
                                </div>
                                <div>
                                    <label for="preco_venda" class="form-label mb-1 fw-bold text-primary">Preço de Venda (R$)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control fw-bold border-primary text-primary" id="preco_venda" name="preco_venda" value="{{ old('preco_venda',$produto->preco_venda ?? '0.00') }}">
                                </div>
                            </div>
                        </div>
                    </div> {{-- FIM DA TABELA 1 --}}

                    <!-- Tabela 2: Profissional -->
                    <div class="col-md-4">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white py-1">Tabela 2: Profissional / Empreiteiro</div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label for="markup_2" class="form-label mb-1">Lucro Desejado (%)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="markup_2" name="markup_2" value="{{ old('markup_2',$produto->markup_2 ?? '0.00') }}">
                                </div>
                                <div class="mb-2">
                                    <label for="desconto_max_2" class="form-label mb-1">Limite Desconto (%)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="desconto_max_2" name="desconto_max_2" value="{{ old('desconto_max_2',$produto->desconto_max_2 ?? '0.00') }}">
                                </div>
                                <div>
                                    <label for="preco_venda_2" class="form-label mb-1 fw-bold text-info">Preço de Venda 2 (R$)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control fw-bold border-info text-info" id="preco_venda_2" name="preco_venda_2" value="{{ old('preco_venda_2',$produto->preco_venda_2 ?? '0.00') }}">
                                </div>
                            </div>
                        </div>
                    </div> {{-- FIM DA TABELA 2 --}}

                    <!-- Tabela 3: Atacado -->
                    <div class="col-md-4">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white py-1">Tabela 3: Atacado / Carga Fechada</div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label for="markup_3" class="form-label mb-1">Lucro Desejado (%)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="markup_3" name="markup_3" value="{{ old('markup_3',$produto->markup_3 ?? '0.00') }}">
                                </div>
                                <div class="mb-2">
                                    <label for="desconto_max_3" class="form-label mb-1">Limite Desconto (%)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control calc-trigger" id="desconto_max_3" name="desconto_max_3" value="{{ old('desconto_max_3',$produto->desconto_max_3 ?? '0.00') }}">
                                </div>
                                <div>
                                    <label for="preco_venda_3" class="form-label mb-1 fw-bold text-success">Preço de Venda 3 (R$)</label>
                                    <input type="number" min="0"  step="0.01" class="form-control fw-bold border-success text-success" id="preco_venda_3" name="preco_venda_3" value="{{ old('preco_venda_3', $produto->preco_venda ?? '0.00') }}">
                                </div>
                            </div>
                        </div>
                    </div> {{-- FIM DA TABELA 3 --}}
                </div> {{-- FIM DA LINHA ROW DA SEÇÃO 3 --}}

                
                <!-- SEÇÃO 4: ESTOQUE, LOGÍSTICA E IMAGEM -->
                <h6 class="text-primary border-bottom pb-2 mb-3">4. Controle Físico e Imagem</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="quantidade_estoque" class="form-label">Qtd. em Estoque</label>
                        <input type="number" min="0" class="form-control" id="quantidade_estoque" name="quantidade_estoque" value="{{ old('quantidade_estoque',$produto->quantidade_estoque ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                        <input type="number"  min="0" class="form-control" id="estoque_minimo" name="estoque_minimo" value="{{ $produto->estoque_minimo ?? 0}}"required>
                    </div>
                    <div class="col-md-3">
                        <label for="localizacao_estoque" class="form-label">Localização no Depósito</label>
                        <input type="text" class="form-control" id="localizacao_estoque" name="localizacao_estoque" placeholder="Ex: Corredor B - Prateleira 4" value="{{ old('localizacao_estoque',$produto->localizacao_estoque ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="peso" class="form-label">Peso (kg)</label>
                        <input type="number" min="0"  step="0.01" class="form-control" id="peso" name="peso" value="{{ old('peso', $produto->peso ?? '0.00') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="largura" class="form-label">Largura (cm)</label>
                        <input type="number" min="0"  step="0.01" class="form-control" id="largura" name="largura" value="{{ old('largura', $produto->largura ?? '0.00') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="altura" class="form-label">Altura (cm)</label>
                        <input type="number" min="0"  step="0.01" class="form-control" id="altura" name="altura" value="{{ old('altura',$produto->altura ?? '0.00') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="profundidade" class="form-label">Profundidade (cm)</label>
                        <input type="number" min="0"  step="0.01" class="form-control" id="profundidade" name="profundidade" value="{{ old('profundidade', $produto->profundidade ?? '0.00') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="imagem" class="form-label">Imagem do Produto</label>

                        <input type="file"
                            class="form-control mb-1"
                            id="imagem"
                            name="imagem"
                            accept="image/*"
                            onchange="previewImage(event)">

                        <img id="imagemPreview"
                            class="imagem-produto mt-1"
                            src="{{ asset($produto->imagem ?? 'image/produtos/produto-sem-imagem.PNG') }}"
                            onerror="this.onerror=null;this.src='{{ asset('image/produtos/produto-sem-imagem.PNG') }}';"
                            alt="Prévia"
                            style="max-width:100px; max-height:100px; border:1px solid #ccc; padding:3px;"
                            class="mt-1">
                    </div>
                </div>

                <!-- SEÇÃO 5: FISCAL E DIRETRIZES -->
                <h6 class="text-primary border-bottom pb-2 mb-3">5. Atributos Fiscais e Controle</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label for="ncm" class="form-label">NCM</label>
                        <input type="text" class="form-control" id="ncm" name="ncm" maxlength="8" value="{{ old('ncm',$produto->ncm ?? '0') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="cest" class="form-label">CEST</label>
                        <input type="text" class="form-control" id="cest" name="cest" maxlength="7" value="{{ old('cest', $produto->cest ?? '0') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="cfop" class="form-label">CFOP Padrão</label>
                        <input type="text" class="form-control" id="cfop" name="cfop" maxlength="4" value="{{ old('cfop', $produto->cfop ?? '0') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="icms_csosn" class="form-label">ICMS / CSOSN</label>
                        <input type="text" class="form-control" id="icms_csosn" name="icms_csosn" maxlength="4" value="{{ old('icms_csosn', $produto->icms_cson ?? '0') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="origem" class="form-label">Origem da Mercadoria</label>

                        <select class="form-select" id="origem" name="origem">
                            <option value="0" {{ old('origem', $produto->origem ?? 0) == 0 ? 'selected' : '' }}>
                                0 - Nacional
                            </option>

                            <option value="1" {{ old('origem', $produto->origem ?? 0) == 1 ? 'selected' : '' }}>
                                1 - Estrangeira - Importação Direta
                            </option>

                            <option value="2" {{ old('origem', $produto->origem ?? 0) == 2 ? 'selected' : '' }}>
                                2 - Estrangeira - Adquirida no Mercado Interno
                            </option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="descricao" class="form-label">Descrição Longa / Observações</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2">{{ old('descricao', $produto->descricao ?? 'sem descricao') }}</textarea>
                    </div>
                </div>

                    <!-- CHECKBOXES DE CONFIGURAÇÃO -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    id="ativo"
                                    name="ativo"
                                    value="1"
                                    {{ old('ativo', $produto->ativo ?? 1) ? 'checked' : '' }}>

                                <label class="form-check-label" for="ativo">
                                    Produto Ativo para Vendas
                                </label>
                            </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    id="em_promocao"
                                    name="em_promocao"
                                    value="1"
                                    {{ old('em_promocao', $produto->em_promocao ?? 1) ? 'checked' : '' }}>

                                <label class="form-check-label" for="em_promocao">Destacar em Promoção</label>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                type="checkbox"
                                id="controla_validade"
                                name="controla_validade"
                                value="1"
                                {{ old('controla_validade', $produto->controla_validade ?? false) ? 'checked' : '' }}
                                onchange="toggleValidade(this)">

                           <label class="form-check-label d-block" for="controla_validade">
                                Controlar Validade
                                <span class="text-secondary fs-6 d-block fw-normal">
                                    determina quando vence o produto
                                </span>
                            </label>
                        </div>
                    </div>
                   <div class="col-md-3" id="validade_container">
                        <label for="validade_produto" class="form-label mb-1">
                            Data de Validade
                        </label>

                        <input type="date"
                            class="form-control form-control-sm"
                            id="validade_produto"
                            name="validade_produto"
                            value="{{ old('validade_produto', isset($produto) && $produto->validade_produto ? $produto->validade_produto->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <!-- BOTÕES DE ENVIO -->
                <div class="text-end border-top pt-3">
                    <button type="reset" class="btn btn-secondary me-2">Limpar Campos</button>
                    <button type="submit" class="btn btn-success px-4">Salvar Cadastro</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- SCRIPTS JAVASCRIPT DE ENGENHARIA DE PREÇOS -->
<script>
    function calcularTabelasMarkup() {
        // 1. Captura as variáveis de custo de entrada
        const compra = parseFloat(document.getElementById('preco_compra_atual').value) || 0;
        const frete = parseFloat(document.getElementById('custo_frete_unidade').value) || 0;
        const impEntrada = parseFloat(document.getElementById('custo_imposto_entrada').value) || 0;

        // Soma as despesas e atualiza o campo Custo Real na tela
        const custoReal = compra + frete + impEntrada;
        document.getElementById('custo_real_entrada').value = custoReal.toFixed(2);

        if (custoReal === 0) return;

        // 2. Captura as taxas incidentes sobre a venda (saída)
        const impSaida = parseFloat(document.getElementById('percentual_imposto_saida').value) || 0;
        const comissao = parseFloat(document.getElementById('percentual_comissao').value) || 0;
        const cartao = parseFloat(document.getElementById('percentual_taxa_cartao').value) || 0;
        const totalCustosSaida = impSaida + comissao + cartao;

        // 3. Loop inteligente para compor os preços por dentro das 3 tabelas
        for (let i = 1; i <= 3; i++) {
            const markup = parseFloat(document.getElementById('markup_' + i).value) || 0;
            const desconto = parseFloat(document.getElementById('desconto_max_' + i).value) || 0;

            // Formula por dentro: Custo / (1 - Soma das deduções)
            const totalDeducoes = totalCustosSaida + markup + desconto;
            const divisor = 1 - (totalDeducoes / 100);

            let precoFinal = 0;
            if (divisor > 0) {
                precoFinal = custoReal / divisor;
            } else {
                // Margem de segurança caso a somatória chegue ou passe de 100%
                precoFinal = custoReal * (1 + (markup / 100));
            }

            // Distribui os resultados nos inputs de preço correspondentes
            const inputId = i === 1 ? 'preco_venda' : 'preco_venda_' + i;
            document.getElementById(inputId).value = precoFinal.toFixed(2);
        }
    }

    // Vincula o recálculo dinâmico em lote em todos os inputs de valor/porcentagem
    document.querySelectorAll('.calc-trigger').forEach(input => {
        input.addEventListener('input', calcularTabelasMarkup);
    });

    // Controla a exibição em tempo real da prévia da imagem selecionada
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('imagemPreview');
            output.src = reader.result;
        };
        if(event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    // Esconde ou exibe a caixa de data de validade com base no switch
    function toggleValidade(checkbox) {
        const container = document.getElementById('validade_container');
        container.style.display = checkbox.checked ? 'block' : 'none';
    }

    // Executa o gatilho na primeira inicialização para alinhar os campos ocultos
    document.addEventListener('DOMContentLoaded', function() {
        toggleValidade(document.getElementById('controla_validade'));
    });
</script>
@endsection
