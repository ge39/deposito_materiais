@extends('layouts.app')

@php
    $novaValidade = \Carbon\Carbon::now()->addDays(7)->format('Y/m/d');
@endphp

@section('content')
<div class="container">
    <h2 class="mb-1">Emitir Orçamento - Editar </h2>
    <span class="text-muted d-block mb-4">Código: #{{ $orcamento->codigo_orcamento }}</span>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erro!</strong> Verifique os campos obrigatórios.
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($orcamento->status === 'Expirado')
        <div class="alert alert-danger text-center fw-bold" style="font-size: 18px;">
            ⚠️ ATENÇÃO! ESTE ORÇAMENTO ESTÁ <u>EXPIRADO</u>.<br>
            AO SALVAR, VIRA <span class="text-danger">AGUARDANDO APROVAÇÃO</span>.<br>
            Nova validade: 
            <span class="text-primary">{{ \Carbon\Carbon::now()->addDays(7)->format('d/m/Y') }}</span>.
        </div>
    @endif

    <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" id="formOrcamento">
        @csrf
        @method('PUT')

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <!-- Seção Cliente e Datas -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cliente *</label>
                        <div class="form-control bg-light">
                            {{ $orcamento->cliente->nome ?? 'Cliente não encontrado' }}
                        </div>
                        <input type="hidden" name="cliente_id" id="clienteSelect" value="{{ $orcamento->cliente_id }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Data *</label>
                        <input type="date" name="data_orcamento" class="form-control"
                               value="{{ old('data_orcamento', $orcamento->data_orcamento->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-danger fw-bold">Validade até *</label>
                        @php
                            $validade = $orcamento->status === 'Expirado'
                                ? now()->addDays(7)->format('Y-m-d')
                                : (old('validade') ?? \Carbon\Carbon::parse($orcamento->validade)->format('Y-m-d'));
                        @endphp
                        <input type="date" name="validade" class="form-control" value="{{ $validade }}" required>
                    </div>
                </div>

                <hr>

                <!-- Cabeçalho da Tabela de Itens -->
                <div class="row fw-bold border-bottom pb-2 mb-2 bg-light p-2 rounded">
                    <div class="col-md-3 text-primary">ID - Produto</div>
                    <div class="col-md-3">Lote</div>
                    <div class="col-md-1">Qtd</div>
                    <div class="col-md-2">Unidade</div>
                    <div class="col-md-1">Preço</div>
                    <div class="col-md-1">Subtotal</div>
                    <div class="col-md-1 text-center">Ação</div>
                </div>

                <!-- Container de Itens Dinâmicos -->
                <!-- <div id="itensContainer" class="p-1">
                    @php
                        $oldProdutos = old('produtos', $orcamento->itens->map(function($item){
                            return [
                                'id' => $item->produto_id,
                                'quantidade' => $item->quantidade_solicitada,
                                'preco_unitario' => $item->preco_liquido,
                                'unidade' => $item->produto->unidadeMedida->nome ?? '',
                                'lote_id'=> $item->lote->id ?? 'Sem lote',
                                'lote_label' => $item->lote->numero_lote ?? 'Sem lote',
                            ];
                        })->toArray());
                    @endphp

                    @foreach($oldProdutos as $i => $oldItem)
                        <div class="row align-items-center mb-2 item-row border-bottom pb-2">
                            <div class="col-md-3">
                                <select name="produtos[{{ $i }}][id]" class="form-select produtoSelect" required>
                                    <option value="">Selecione...</option>
                                    @foreach($produtos as $produto)
                                        <option value="{{ $produto->id }}"
                                            data-preco="{{ $produto->preco_venda }}"
                                            data-unidade="{{ $produto->unidadeMedida->nome ?? '' }}"
                                            {{ (int)$oldItem['id'] === (int)$produto->id ? 'selected' : '' }}>
                                            {{ $produto->id }} - {{ $produto->nome }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="form-control bg-light loteLabel fw-bold text-secondary">
                                    {{ $oldItem['lote_label'] }}
                                </div>
                                <input type="hidden"
                                    name="produtos[{{ $i }}][lote_id]"
                                    class="loteInput"
                                    value="{{ $oldItem['lote_id'] }}" >
                            </div>

                            <div class="col-md-1">
                                <input type="number" name="produtos[{{ $i }}][quantidade]" class="form-control qtd text-center" min="1" value="{{ $oldItem['quantidade'] }}" required>
                            </div>

                            <div class="col-md-2 unidade text-muted">{{ $oldItem['unidade'] }}</div>

                            <div class="col-md-1 fw-bold text-dark">
                                <div class="precoLabel">R$ {{ number_format($oldItem['preco_unitario'], 2, ',', '.') }}</div>
                                <input type="hidden" name="produtos[{{ $i }}][preco_unitario]" class="preco" value="{{ $oldItem['preco_unitario'] }}">
                            </div>

                            <div class="col-md-1 fw-bold text-dark subtotal">
                                <span class="subtotalLabel">{{ number_format($oldItem['quantidade'] * $oldItem['preco_unitario'], 2, ',', '.') }}</span>
                            </div>

                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-sm btn-danger remover px-3 fw-bold">X</button>
                            </div>
                        </div>
                    @endforeach

                </div> -->
                

                <!-- Container de Itens Dinâmicos -->
                <div id="itensContainer" class="p-1">
                    @php
                        $oldProdutos = old('produtos', $orcamento->itens->map(function($item){
                            return [
                                'id' => $item->produto_id,
                                'quantidade' => $item->quantidade_solicitada,
                                // 🎯 CORREÇÃO: Usar o preço original de venda (bruto) para evitar o desconto cumulativo no JS
                                'preco_unitario' => $item->produto->preco_venda ?? 0, 
                                'unidade' => $item->produto->unidadeMedida->nome ?? '',
                                'lote_id'=> $item->lote->id ?? 'Sem lote',
                                'lote_label' => $item->lote->numero_lote ?? 'Sem lote',
                            ];
                        })->toArray());
                    @endphp

                    @foreach($oldProdutos as $i => $oldItem)
                        <div class="row align-items-center mb-2 item-row border-bottom pb-2">
                            <div class="col-md-3">
                                <select name="produtos[{{ $i }}][id]" class="form-select produtoSelect" required>
                                    <option value="">Selecione...</option>
                                    @foreach($produtos as $produto)
                                        <option value="{{ $produto->id }}"
                                            data-preco="{{ $produto->preco_venda }}"
                                            data-unidade="{{ $produto->unidadeMedida->nome ?? '' }}"
                                            {{ (int)$oldItem['id'] === (int)$produto->id ? 'selected' : '' }}>
                                            {{ $produto->id }} - {{ $produto->nome }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <div class="form-control bg-light loteLabel fw-bold text-secondary">
                                    {{ $oldItem['lote_label'] }}
                                </div>
                                <input type="hidden"
                                    name="produtos[{{ $i }}][lote_id]"
                                    class="loteInput"
                                    value="{{ $oldItem['lote_id'] }}" >
                            </div>

                            <div class="col-md-1">
                                <input type="number" name="produtos[{{ $i }}][quantidade]" class="form-control qtd text-center" min="1" value="{{ $oldItem['quantidade'] }}" required>
                            </div>

                            <div class="col-md-2 unidade text-muted">{{ $oldItem['unidade'] }}</div>

                            <div class="col-md-1 fw-bold text-dark">
                                @php
                                    // Aplica o percentual de desconto salvo se houver para a renderização inicial do Blade
                                    $descontoPercentualSalvo = $orcamento->itens->first()->desconto_percentual ?? 0;
                                    $precoLiquidoInicial = $oldItem['preco_unitario'] * (1 - ($descontoPercentualSalvo / 100));
                                @endphp
                                <div class="precoLabel">R$ {{ number_format($precoLiquidoInicial, 2, ',', '.') }}</div>
                                <!-- O input hidden armazena o preço bruto inicial para que o JS leia corretamente sem distorções -->
                                <input type="hidden" name="produtos[{{ $i }}][preco_unitario]" class="preco" value="{{ $oldItem['preco_unitario'] }}">
                            </div>

                           <div class="col-md-1 fw-bold text-dark subtotal">
                                R$ <span class="subtotalLabel">0,00</span>
                            </div>

                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-sm btn-danger remover px-3 fw-bold">X</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Botão Adicionar Produto posicionado alinhado à esquerda -->
                <div class="mb-4 col-12 d-flex justify-content-end pe-3"> 
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" id="addProduto">
                        + Adicionar Produto
                    </button>
                </div>
                <!-- 📑 INTEGRADO: NOVA ESTRUTURA EM DUAS COLUNAS EQUILIBRADAS -->
               <div class="row mt-4">
                    <!-- Label com atributos nativos do Bootstrap para controlar o colapso -->
                    <label id="toggleObservacoes" class="form-label text-dark d-block fw-bold mb-1" 
                        style="cursor: pointer;" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#containerObservacoes" 
                        aria-expanded="false" 
                        aria-controls="containerObservacoes">
                        + Observações
                    </label>
                    <!-- 2. Adicionada a classe d-none no container do bloco cinza para iniciar oculto -->
                    <div id="containerObservacoes" class="col-md-12 d-flex flex-column justify-content-end d-none">
                        <!-- Bloco Cinza de Observações -->
                        <div class="bg-secondary p-3 rounded mb-3">
                            <label class="form-label text-dark small d-block mb-2">
                                Insira aqui as informações que vão aparecer impressas no documento de entrega.
                            </label>
                            <textarea name="observacoes" class="form-control" rows="4">{{ old('observacoes', $orcamento->observacoes ?: 'Sem observações') }}</textarea>
                            <!-- <label class="form-label text-dark d-block small mt-2 mb-0">
                                Ex: melhor período para entrega: manhã ou tarde, nome da pessoa que vai receber?
                            </label> -->
                        </div>
                    </div>

                    <!-- 📊 Coluna Direita: Fechamento Financeiro com Desconto Global -->
                    <div class="col-md-6 d-flex flex-column justify-content-between text-end">
                        <div class="p-3 border rounded bg-light mb-3 shadow-sm text-start">
                            
                           <div class="p-3 border rounded bg-light mb-3 shadow-sm text-start">
                                    @php
                                        // 🧮 Soma o valor bruto real de todas as linhas (Quantidade x Preço Unitário original)
                                        $totalBrutoReal = $orcamento->itens->sum(function($item) {
                                            return $item->quantidade_solicitada * $item->preco_unitario;
                                        });

                                        // 🎯 Soma o valor total em R$ economizado que está gravado nas linhas
                                        $totalDescontoReal = $orcamento->itens->sum('valor_desconto');

                                        // 💰 Captura o percentual de desconto aplicado (Ex: 5)
                                        $descontoPercentualSalvo = $orcamento->itens->first()->desconto_percentual ?? 0;
                                    @endphp
                                    
                                <div class="p-3 border rounded bg-light mb-3 shadow-sm text-start">
                                    @php
                                        // 🧮 Soma o valor bruto real de todas as linhas (Quantidade x Preço Unitário original)
                                        $totalBrutoReal = $orcamento->itens->sum(function($item) {
                                            return $item->quantidade_solicitada * $item->preco_unitario;
                                        });

                                        // 🎯 Soma o valor total em R$ economizado que está gravado nas linhas
                                        $totalDescontoReal = $orcamento->itens->sum('valor_desconto');

                                        // 💰 Captura o percentual de desconto aplicado (Ex: 5)
                                        $descontoPercentualSalvo = $orcamento->itens->first()->desconto_percentual ?? 0;
                                    @endphp
                                    
                                    <!-- Total Bruto e Desconto Alinhados Horizontalmente -->
                                    <div class="row align-items-center mb-3">
                                        <!-- Bloco do Total Bruto -->
                                        <div class="col-6 d-flex justify-content-between align-items-center border-end pe-3">
                                            <span class="fw-bold text-secondary">Total Bruto:</span>
                                            <span class="fs-5 fw-bold text-dark">R$ <span id="totalBruto">{{ number_format($totalBrutoReal + $orcamento->itens->sum('valor_desconto'), 2, ',', '.') }}</span></span>
                                        </div>

                                        <!-- Bloco do Desconto Global -->
                                        <div class="col-6 d-flex justify-content-between align-items-center ps-3">
                                            <span class="fw-bold text-danger mb-0">Desconto (%):</span>
                                            <div style="width: 80px;">
                                                <input type="number" name="desconto_global" id="descontoGlobal" 
                                                    class="form-control text-end fw-bold text-danger py-1" 
                                                    min="0" max="100" step="1"
                                                    value="{{ $descontoPercentualSalvo }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Valor real do desconto total em R$ vindo do banco -->
                                    <div class="d-flex justify-content-between align-items-center mb-2 text-muted small px-1">
                                        <span>Total Desconto:</span>
                                        <span>R$ <span id="totalDesconto">{{ number_format($totalDescontoReal, 2, ',', '.') }}</span></span>
                                    </div>

                                    <hr class="my-2">

                                    <!-- Valor Líquido Final com Desconto (Soma dos subtotais líquidos ou total pai) -->
                                    <div class="d-flex justify-content-between align-items-center px-1">
                                        <span class="fw-bold text-success fs-5">Valor com Desconto:</span>
                                        <span class="fw-bold text-success fs-4">R$ <span id="totalComDesconto">{{ number_format($orcamento->total, 2, ',', '.') }}</span></span>
                                    </div>
                                    <!-- Botões de Ação Finais Alinhados no Rodapé Direito -->
                                    <div>
                                        <button type="submit" class="btn btn-success px-4 fw-bold me-2 shadow-sm" id="btnSalvar">
                                            Salvar Orçamento
                                        </button>
                                        <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary px-4 fw-bold shadow-sm">
                                            Voltar
                                        </a>
                                        
                                    </div>
                                </div>
                            </div>  
                        </div>  
                    </div>
                </div> <!-- Fim da row de duas colunas -->
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const produtos = @json($produtos);
        const tableBody = document.getElementById('itensContainer');
        const addBtn = document.getElementById('addProduto');
        const clienteSelect = document.getElementById('clienteSelect');
        
        // Elementos da seção de desconto global mapeados da Blade
        const totalBrutoSpan = document.getElementById('totalBruto');
        const descontoGlobalInput = document.getElementById('descontoGlobal');
        const totalDescontoSpan = document.getElementById('totalDesconto');
        const totalComDescontoSpan = document.getElementById('totalComDesconto');

        let index = tableBody.querySelectorAll('.item-row').length;

        // =========================================================================
        // ⏱️ TIMER DE INATIVIDADE (3 MINUTOS)
        // =========================================================================
        let tempoInatividade;
        function resetarTimerInatividade() {
            clearTimeout(tempoInatividade);
            tempoInatividade = setTimeout(() => {
                alert('Sessão de edição expirou por inatividade (3 minutos). Voltando para a listagem para liberar o registro.');
                window.location.href = "{{ route('orcamentos.index') }}";
            }, 180000);
        }

        ['mousemove', 'keypress', 'click', 'scroll', 'input'].forEach(evento => {
            document.addEventListener(evento, resetarTimerInatividade);
        });
        resetarTimerInatividade();

        // 🔥 PRODUTOS SELECIONADOS
        function getProdutosSelecionados() {
            const selecionados = [];
            tableBody.querySelectorAll('.produtoSelect').forEach(select => {
                if (select.value) {
                    selecionados.push(select.value);
                }
            });
            return selecionados;
        }

        // 🔥 ATUALIZA OPTIONS (OCULTA USADOS PARA EVITAR PRODUTO DUPLICADO)
        function atualizarOpcoesProdutos() {
            const selecionados = getProdutosSelecionados();

            tableBody.querySelectorAll('.produtoSelect').forEach(select => {
                const valorAtual = select.value;

                select.querySelectorAll('option').forEach(option => {
                    if (!option.value) return;

                    if (option.value === valorAtual) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = selecionados.includes(option.value);
                });
            });
        }

        // CRIAR ITEM DINÂMICO

        function criarItem() {
            const divRow = document.createElement('div');
            divRow.className = 'row align-items-center mb-2 item-row border-bottom pb-2';

            divRow.innerHTML = `
                <!-- Seleção do Produto -->
                <div class="col-md-3">
                    <select name="produtos[${index}][id]" class="form-select produtoSelect" required>
                        <option value="">Selecione...</option>
                        ${produtos.map(p => `
                            <option value="${p.id}"
                                data-preco="${p.preco_venda}"
                                data-unidade="${p.unidade_medida?.nome || ''}">
                                ${p.id} - ${p.nome}
                            </option>
                        `).join('')}
                    </select>
                </div>

                <!-- Seleção do Lote -->
                <div class="col-md-3">
                    <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
                        <option value="">Selecione o lote</option>
                    </select>
                </div>

                <!-- Quantidade -->
                <div class="col-md-1">
                    <input type="number" name="produtos[${index}][quantidade]" class="form-control qtd text-center" min="1" value="1" required>
                </div>

                <!-- Unidade de Medida -->
                <div class="col-md-2 unidade text-muted"></div>

                <!-- Preço Unitário (Atualizado dinamicamente via JS) -->
                <div class="col-md-1 fw-bold text-dark">
                    <div class="precoLabel">R$ 0,00</div>
                    <input type="hidden" name="produtos[${index}][preco_unitario]" class="preco" value="0">
                </div>

                <!-- Subtotal da Linha (Calculado dinamicamente via JS) -->
                <div class="col-md-1 fw-bold text-dark subtotal">
                    <span class="subtotalLabel">0,00</span>
                </div>

                <!-- Botão Remover Linha -->
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-danger remover px-3 fw-bold">X</button>
                </div>
            `;

            tableBody.appendChild(divRow);
            index++;

            atualizarOpcoesProdutos();
        }


        // =========================================================================
        // 📊 ATUALIZAR TOTAL (TRAVAS DINÂMICAS DESCONTO_MAX_1, 2 E 3)
        // =========================================================================
        function atualizarTotal() {
            let totalBruto = 0;
            let menorDescontoMaximoPermitido = 100;
            let produtoLimitanteNome = "";

            let percentualDesconto = parseFloat(descontoGlobalInput?.value) || 0;

            tableBody.querySelectorAll('.item-row').forEach(tr => {
                const produtoSelect = tr.querySelector('.produtoSelect');
                const qtd = parseFloat(tr.querySelector('.qtd').value) || 0;
                const precoCobrado = parseFloat(tr.querySelector('.preco').value) || 0;

                const subtotal = qtd * precoCobrado;
                const labelSub = tr.querySelector('.subtotalLabel');
                if(labelSub) {
                    labelSub.textContent = subtotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                totalBruto += subtotal;

                if (produtoSelect && produtoSelect.value) {
                    const produtoId = produtoSelect.value;
                    const produtoDados = produtos.find(p => p.id == produtoId);
                    
                    if (produtoDados) {
                        const pv1 = parseFloat(produtoDados.preco_venda) || 0;
                        const pv2 = parseFloat(produtoDados.preco_venda_2) || 0;
                        const pv3 = parseFloat(produtoDados.preco_venda_3) || 0;

                        const descMax1 = parseFloat(produtoDados.desconto_max_1) || 0;
                        const descMax2 = parseFloat(produtoDados.desconto_max_2) || 0;
                        const descMax3 = parseFloat(produtoDados.desconto_max_3) || 0;

                        let descMaxProduto = descMax1;

                        if (Math.abs(precoCobrado - pv2) < 0.01) {
                            descMaxProduto = descMax2;
                        } else if (Math.abs(precoCobrado - pv3) < 0.01) {
                            descMaxProduto = descMax3;
                        } else if (Math.abs(precoCobrado - pv1) >= 0.01) {
                            descMaxProduto = Math.min(descMax1, descMax2, descMax3);
                        }

                        if (descMaxProduto < menorDescontoMaximoPermitido) {
                            menorDescontoMaximoPermitido = descMaxProduto;
                            produtoLimitanteNome = produtoDados.nome || "";
                        }
                    }
                }
            });

            // Trava de segurança contra violação do Markup máximo
            if (percentualDesconto > menorDescontoMaximoPermitido) {
                alert(`Atenção! O desconto de ${percentualDesconto}% excede o limite permitido de ${menorDescontoMaximoPermitido}% definido pelo Markup para o produto: ${produtoLimitanteNome}.`);
                percentualDesconto = menorDescontoMaximoPermitido;
                if(descontoGlobalInput) {
                    descontoGlobalInput.value = menorDescontoMaximoPermitido;
                }
            }

            if(totalBrutoSpan) {
                totalBrutoSpan.textContent = totalBruto.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const valorDesconto = totalBruto * (percentualDesconto / 100);
            const totalComDesconto = totalBruto - valorDesconto;

            if(totalDescontoSpan) {
                totalDescontoSpan.textContent = valorDesconto.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            if(totalComDescontoSpan) {
                totalComDescontoSpan.textContent = totalComDesconto.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        // =========================================================================
        // 🔄 CARREGAMENTO DINÂMICO DE LOTES (AO ALTERAR PRODUTO)
        // =========================================================================
        tableBody.addEventListener('change', e => {
            if (!e.target.classList.contains('produtoSelect')) return;

            const produtoId = e.target.value;
            const produto = produtos.find(p => p.id == produtoId);
            const tr = e.target.closest('.item-row');

            const preco = parseFloat(produto?.preco_venda || 0);
            const unidade = produto?.unidade_medida?.nome || '';

            tr.querySelector('.preco').value = preco;
            tr.querySelector('.precoLabel').textContent = 'R$ ' + preco.toLocaleString('pt-BR', { minimumFractionDigits: 2 });

            const unidadeDiv = tr.querySelector('.unidade');
            if(unidadeDiv) unidadeDiv.textContent = unidade;

            const loteSelect = tr.querySelector('.loteSelect');
            if(loteSelect) {
                loteSelect.innerHTML = '<option value="">Selecione o lote</option>';
                if (produto && produto.lotes) {
                    const lotesValidos = produto.lotes.filter(l => {
                        const disponivel = (parseFloat(l.quantidade) || 0) - (parseFloat(l.quantidade_reservada) || 0);
                        return l.status == 1 && disponivel > 0;
                    });

                    if (lotesValidos.length === 0) {
                        loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
                    } else {
                        lotesValidos.forEach(l => {
                            const disponivel = (parseFloat(l.quantidade) || 0) - (parseFloat(l.quantidade_reservada) || 0);
                            loteSelect.innerHTML += `<option value="${l.id}">${l.numero_lote} | Qtd: ${disponivel}</option>`;
                        });
                    }
                }
            }

            // 🔥 ATUALIZA BLOQUEIO DE PRODUTOS DUPLICADOS
            atualizarOpcoesProdutos();

            atualizarTotal();
        });

        // =========================================================================
        // ⌨️ INPUTS DE QUANTIDADE E DESCONTO
        // =========================================================================
        tableBody.addEventListener('input', e => {
            if (e.target.classList.contains('qtd')) {
                atualizarTotal();
            }
        });

        if (descontoGlobalInput) {
            descontoGlobalInput.addEventListener('input', atualizarTotal);
        }

        // =========================================================================
        // 🗑️ REMOVER LINHA
        // =========================================================================
        tableBody.addEventListener('click', e => {
            if (e.target.classList.contains('remover')) {
                e.target.closest('.item-row').remove();
                atualizarOpcoesProdutos();
                atualizarTotal();
            }
        });

        // =========================================================================
        // ➕ GATILHO DO BOTÃO ADICIONAR PRODUTO
        // =========================================================================
              // GATILHO DO BOTÃO ADICIONAR PRODUTO
        addBtn.addEventListener('click', () => {
            const lastRow = tableBody.querySelector('.item-row:last-child');
            if (lastRow) {
                const produto = lastRow.querySelector('.produtoSelect')?.value;
                if (!produto) {
                    alert('Selecione um produto na linha anterior antes de abrir uma nova!');
                    return;
                }
            }
            criarItem();
        });

        // =========================================================================
        // 🔄 INICIALIZAÇÃO DE EDIÇÃO (CORRIGE O VALOR INICIAL DA TELA)
        // =========================================================================
        function inicializarValoresExistentes() {
            tableBody.querySelectorAll('.item-row').forEach(tr => {
                const select = tr.querySelector('.produtoSelect');
                if (select && select.value) {
                    const produtoId = select.value;
                    const produto = produtos.find(p => p.id == produtoId);
                    
                    if (produto) {
                        // Resgata e força as labels corretas nas linhas carregadas do backend
                        const preco = parseFloat(tr.querySelector('.preco').value) || parseFloat(produto.preco_venda) || 0;
                        const unidade = produto.unidade_medida?.nome || '';
                        
                        const unidadeDiv = tr.querySelector('.unidade');
                        if (unidadeDiv) unidadeDiv.textContent = unidade;
                        
                        const precoLabel = tr.querySelector('.precoLabel');
                        if (precoLabel) precoLabel.textContent = 'R$ ' + preco.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                    }
                }
            });

            // Roda os bloqueios de selects e calcula o total bruto e líquido automaticamente
            atualizarOpcoesProdutos();
            atualizarTotal();
        }

        // Executa a inicialização assim que o DOM estiver pronto
        inicializarValoresExistentes();
    });
</script>


<!-- modal observações abrir/fechar -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const label = document.getElementById("toggleObservacoes");
        const container = document.getElementById("containerObservacoes");

        label.addEventListener("click", function () {
            // Alterna a classe d-none do Bootstrap (exibe/oculta)
            container.classList.toggle("d-none");

            // Alterna o texto do label entre + e - conforme a visibilidade
            if (container.classList.contains("d-none")) {
                label.textContent = "+ Observações:";
            } else {
                label.textContent = "- Observações:";
            }
        });
    });
</script>

<script src="{{ asset('js/orcamento.js') }}"></script>
@endsection