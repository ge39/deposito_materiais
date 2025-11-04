@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Nova Promoção</h4>
            <a href="{{ route('promocoes.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Ops!</strong> Verifique os erros abaixo:<br><br>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="formPromocao" action="{{ route('promocoes.store') }}" method="POST">
                @csrf

                {{-- Tipo de Abrangência --}}
                <div class="mb-3">
                    <label for="tipo_abrangencia" class="form-label">Tipo de Abrangência</label>
                    <select name="tipo_abrangencia" id="tipo_abrangencia" class="form-select" required onchange="toggleCampos(this.value)">
                        <option value="">Selecione...</option>
                        <option value="produto">Por Produto</option>
                        <option value="categoria">Por Categoria</option>
                    </select>
                </div>

                {{-- Produto --}}
                <div class="mb-3 d-none" id="campo_produto">
                    <label for="produto_id" class="form-label">Produto</label>
                    <select name="produto_id" id="produto_id" class="form-select" onchange="atualizarPreco()">
                        <option value="">Selecione um produto...</option>
                        @foreach($produtos as $produto)
                            {{-- Adiciona data-preco com o valor correto do produto --}}
                            <option value="{{ $produto->id }}" data-preco="{{ $produto->preco_venda }}">{{ $produto->nome }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Categoria --}}
                <div class="mb-3 d-none" id="campo_categoria">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="form-select" onchange="atualizarPreco()">
                        <option value="">Selecione uma categoria...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <hr>

                {{-- Campos de valores --}}
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="preco_venda" class="form-label" style="color:green;font-weight:bold;">Preço Venda (R$)</label>
                        <input type="number" id="preco_venda" class="form-control" readonly style="color:#000;font-weight:bold;">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="desconto_percentual" class="form-label">Desconto (%)</label>
                        <input type="number" name="desconto_percentual" id="desconto_percentual" class="form-control" step="0.01" min="1" oninput="simularPreco()">
                        <div id="msg_desconto" class="text-danger small mt-1"></div>

                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="acrescimo_percentual" class="form-label">Acréscimo (%)</label>
                        <input type="number" name="acrescimo_percentual" id="acrescimo_percentual" class="form-control" step="0.01" min="1" oninput="simularPreco()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="acrescimo_valor" class="form-label">Acréscimo (R$)</label>
                        <input type="number" name="acrescimo_valor" id="acrescimo_valor" class="form-control" step="0.01" min="1" oninput="simularPreco()">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="preco_simulado" class="form-label" style="color:red;font-weight:bold;">Preço Simulado (R$)</label>
                    <input type="number" id="preco_simulado" class="form-control" readonly style="color:#000;font-weight:bold;">
                </div>

                <hr>

                {{-- Datas --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="promocao_inicio" class="form-label">Data de Início</label>
                        <input type="date" name="promocao_inicio" id="promocao_inicio" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="promocao_fim" class="form-label">Data de Fim</label>
                        <input type="date" name="promocao_fim" id="promocao_fim" class="form-control" value="{{ date('Y-m-d', strtotime('+2 days')) }}" required>
                    </div>
                </div>

                {{-- Status --}}
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="em_promocao" id="em_promocao" value="1" checked>
                    <label class="form-check-label" for="em_promocao">Ativar promoção imediatamente</label>
                </div>

                {{-- Botões --}}
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Salvar Promoção
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function toggleCampos(valor) {
        const campoProduto = document.getElementById('campo_produto');
        const campoCategoria = document.getElementById('campo_categoria');
        const msgDesconto = document.getElementById('msg_desconto');

        campoProduto.classList.add('d-none');
        campoCategoria.classList.add('d-none');

        // Resetar campos
        document.getElementById('preco_venda').value = '';
        document.getElementById('preco_simulado').value = '';
        document.getElementById('desconto_percentual').value = '';
        document.getElementById('acrescimo_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';

        // Desabilitar tudo por padrão
        document.getElementById('preco_venda').disabled = true;
        document.getElementById('preco_simulado').disabled = true;
        document.getElementById('desconto_percentual').disabled = true;
        document.getElementById('acrescimo_percentual').disabled = true;
        document.getElementById('acrescimo_valor').disabled = true;

        // Referência ao elemento de mensagem
        document.getElementById('desconto_percentual').addEventListener('input', function() {
        document.getElementById('acrescimo_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';
        
        // Validar desconto máximo
        if (parseFloat(this.value) > 10) {
            msgDesconto.textContent = 'O desconto não pode ser maior que 10%.';
            this.value = ''; // limpa o campo
        } else {
            msgDesconto.textContent = '';
        }
        
        simularPreco();
        });

        if(valor === 'produto') {
            campoProduto.classList.remove('d-none');
            document.getElementById('preco_venda').disabled = false; // habilitar para produto
        } else if(valor === 'categoria') {
            campoCategoria.classList.remove('d-none');
            // Habilitar somente os campos de desconto/acréscimo
            document.getElementById('desconto_percentual').disabled = false;
            document.getElementById('acrescimo_percentual').disabled = false;
            document.getElementById('acrescimo_valor').disabled = false;
        }

        atualizarPreco();
    }

    function atualizarPreco() {
        const tipo = document.getElementById('tipo_abrangencia').value;
        let preco = 0;

        if(tipo === 'produto') {
            const produto = document.getElementById('produto_id').selectedOptions[0];
            preco = parseFloat(produto?.dataset?.preco || 0);
        }

        const precoVendaInput = document.getElementById('preco_venda');
        const precoSimuladoInput = document.getElementById('preco_simulado');

        precoVendaInput.value = preco.toFixed(2);
        precoSimuladoInput.value = '';
        
        // habilitar desconto/acréscimo somente se houver preço para produto
        if(tipo === 'produto' && preco > 0){
            document.getElementById('desconto_percentual').disabled = false;
            document.getElementById('acrescimo_percentual').disabled = false;
            document.getElementById('acrescimo_valor').disabled = false;
        }
    }

    function simularPreco() {
        const tipo = document.getElementById('tipo_abrangencia').value;
        const precoVenda = parseFloat(document.getElementById('preco_venda').value || 0);

        let precoFinal = precoVenda;
        const desconto = parseFloat(document.getElementById('desconto_percentual').value) || 0;
        const acrescimoPercentual = parseFloat(document.getElementById('acrescimo_percentual').value) || 0;
        const acrescimoValor = parseFloat(document.getElementById('acrescimo_valor').value) || 0;

        if(tipo === 'produto') {
            if(desconto > 0) precoFinal -= precoVenda * (desconto / 100);
            else if(acrescimoPercentual > 0) precoFinal += precoVenda * (acrescimoPercentual / 100);
            else if(acrescimoValor > 0) precoFinal += acrescimoValor;
        } else if(tipo === 'categoria') {
            precoFinal = 0; // preço simulado não usado
        }

        document.getElementById('preco_simulado').value = precoFinal.toFixed(2);
    }

    // Limpar campos conflitantes
    document.getElementById('desconto_percentual').addEventListener('input', function() {
        document.getElementById('acrescimo_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';
        simularPreco();
    });
    document.getElementById('acrescimo_percentual').addEventListener('input', function() {
        document.getElementById('desconto_percentual').value = '';
        document.getElementById('acrescimo_valor').value = '';
        simularPreco();
    });
    document.getElementById('acrescimo_valor').addEventListener('input', function() {
        document.getElementById('desconto_percentual').value = '';
        document.getElementById('acrescimo_percentual').value = '';
        simularPreco();
    });

    // Validação antes de enviar
    document.getElementById('formPromocao').addEventListener('submit', function(e){
        const tipo = document.getElementById('tipo_abrangencia').value;
        const produto = document.getElementById('produto_id').value;
        const categoria = document.getElementById('categoria_id').value;
        const precoVenda = parseFloat(document.getElementById('preco_venda').value) || 0;
        const desconto = parseFloat(document.getElementById('desconto_percentual').value) || 0;
        const acrescimoPerc = parseFloat(document.getElementById('acrescimo_percentual').value) || 0;
        const acrescimoValor = parseFloat(document.getElementById('acrescimo_valor').value) || 0;

        if(tipo === '') {
            alert('Selecione o tipo de abrangência.');
            e.preventDefault(); return;
        }
        if(tipo === 'produto' && produto === '') {
            alert('Selecione um produto.');
            e.preventDefault(); return;
        }
        if(tipo === 'categoria' && categoria === '') {
            alert('Selecione uma categoria.');
            e.preventDefault(); return;
        }
        if(tipo === 'produto' && precoVenda < 1) {
            alert('O preço de venda deve ser maior que zero.');
            e.preventDefault(); return;
        }
        if(desconto < 1 && acrescimoPerc < 1 && acrescimoValor < 1 && tipo === 'produto') {
            alert('Informe pelo menos um valor válido de desconto ou acréscimo (maior que 0).');
            e.preventDefault(); return;
        }
        if(desconto > 50) {
            alert('O desconto não pode ser maior que 50%.');
            e.preventDefault(); return;
        }
    });
</script>



@endsection
