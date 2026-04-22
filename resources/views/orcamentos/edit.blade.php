@extends('layouts.app')

@php
    $novaValidade = \Carbon\Carbon::now()->addDays(7)->format('Y/m/d');
@endphp

@section('content')
<div class="container">
    <h2 class="mb-4">Editar Pedido/Orçamento #{{ $orcamento->codigo_orcamento }}  </h2>
    
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

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Cliente *</label>
                        <div class="form-control bg-light">
                            {{ $orcamento->cliente->nome ?? 'Cliente não encontrado' }}
                        </div>
                        <input type="hidden" name="cliente_id" id="clienteSelect" value="{{ $orcamento->cliente_id }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Data *</label>
                        <input type="date" name="data_orcamento" class="form-control"
                               value="{{ old('data_orcamento', $orcamento->data_orcamento->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-danger">Validade até</label>
                        @php
                            $validade = $orcamento->status === 'Expirado'
                                ? now()->addDays(7)->format('Y-m-d')
                                : (old('validade') ?? \Carbon\Carbon::parse($orcamento->validade)->format('Y-m-d'));
                        @endphp
                        <input type="date" name="validade" class="form-control" value="{{ $validade }}" required>
                    </div>
                </div>

                <hr>

                <div class="row fw-bold border-bottom pb-2 mb-2">
                    <div class="col-md-3 text-primary">ID - Produto</div>
                    <div class="col-md-3">Lote</div>
                    <div class="col-md-1">Qtd</div>
                    <div class="col-md-2">Unidade</div>
                    <div class="col-md-1">Preço</div>
                    <div class="col-md-1">Subtotal</div>
                    <div class="col-md-1 text-center">Ação</div>
                </div>

                <div id="itensContainer">

                    @php
                        $oldProdutos = old('produtos', $orcamento->itens->map(function($item){
                            return [
                                'id' => $item->produto_id,
                                'quantidade' => $item->quantidade_solicitada,
                                'preco_unitario' => $item->preco_unitario,
                                'unidade' => $item->produto->unidadeMedida->nome ?? '',
                                'lote_id'=> $item->lote_principal->numero_lote ?? 'Sem lote',
                                'lote_label' => $item->lote->numero_lote ?? 'Sem lote',
                            ];
                        })->toArray());
                    @endphp

                    @foreach($oldProdutos as $i => $oldItem)
                        <div class="row align-items-center mb-2 item-row">
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
                                <div class="form-control bg-light loteLabel">
                                    {{ $oldItem['lote_label'] }}
                                </div>
                                <input type="hidden"
                                    name="produtos[{{ $i }}][lote_id]"
                                    class="loteInput"
                                    value="{{ $oldItem['lote_id'] }}" >
                            </div>

                            <div class="col-md-1">
                                <input type="number" name="produtos[{{ $i }}][quantidade]" class="form-control qtd" min="1" value="{{ $oldItem['quantidade'] }}" required>
                            </div>

                            <div class="col-md-2 unidade">{{ $oldItem['unidade'] }}</div>

                            <div class="col-md-1">
                                <div class="preco">{{ number_format($oldItem['preco_unitario'], 2, ',', '.') }}</div>
                                <input type="hidden" name="produtos[{{ $i }}][preco_unitario]" value="{{ $oldItem['preco_unitario'] }}">
                            </div>

                            <div class="col-md-1 subtotal">
                                {{ number_format($oldItem['quantidade'] * $oldItem['preco_unitario'], 2, ',', '.') }}
                            </div>

                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-sm btn-danger remover">X</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-end mt-3">
                    <button type="button" class="btn btn-primary" id="addProduto">+ Produto</button>
                    <button type="submit" class="btn btn-success" id="btnSalvar" atualizarBotaoSalvar()>Salvar</button>
                    <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary">Voltar</a>
                </div>

                <div class="text-end mt-2">
                    <h5>Total: R$ <span id="total">{{ number_format($orcamento->total, 2, ',', '.') }}</span></h5>
                </div>

                <div class="mt-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $orcamento->observacoes ?: 'Sem observações') }}</textarea>
                </div>

            </div>
        </div>
    </form>
</div>

<!-- botao +Produto - Controle os itens do select options, sai da pagina inativa por 3 minutos -->
<!-- <script>
    document.addEventListener('DOMContentLoaded', () => {

        const produtos = @json($produtos);
        const container = document.getElementById('itensContainer');
        const totalSpan = document.getElementById('total');
        const addBtn = document.getElementById('addProduto');
        const clienteSelect = document.getElementById('clienteSelect');

        let index = container.querySelectorAll('.item-row').length;

        // ===============================
        // CRIAR ITEM
        // ===============================
        function criarItem() {
            const div = document.createElement('div');
            div.classList.add('row','align-items-center','mb-2','item-row');

            div.innerHTML = `
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

                <div class="col-md-3">
                    <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
                        <option value="">Selecione o lote</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <input type="number"
                        name="produtos[${index}][quantidade_solicitada]"
                        class="form-control qtd"
                        value="1" min="1" required>
                </div>

                <div class="col-md-2 unidade"></div>

                <div class="col-md-1">
                    <div class="preco">0,00</div>
                    <input type="hidden"
                        name="produtos[${index}][preco_unitario]"
                        value="0">
                </div>

                <div class="col-md-1 subtotal">0,00</div>

                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-danger remover">X</button>
                </div>
            `;

            container.appendChild(div);
            index++;

            clienteSelect.classList.add('readonly-select');
        }
    
        function getProdutosSelecionados() {
            const selecionados = [];

            document.querySelectorAll('.produtoSelect').forEach(select => {
                if (select.value) {
                    selecionados.push(select.value);
                }
            });

            return selecionados;
        }

        // ===============================
        // ATUALIZAR TOTAL
        // ===============================
        function atualizarTotal() {
            let total = 0;

            container.querySelectorAll('.item-row').forEach(row => {

                const qtd = parseFloat(row.querySelector('.qtd').value) || 0;
                const preco = parseFloat(row.querySelector('input[name*="[preco_unitario]"]').value) || 0;

                const subtotal = qtd * preco;

                row.querySelector('.subtotal').textContent =
                    subtotal.toFixed(2).replace('.', ',');

                total += subtotal;
            });

            totalSpan.textContent = total.toFixed(2).replace('.', ',');
        }

        // ===============================
        // SELECIONAR PRODUTO
        // ===============================
        container.addEventListener('change', e => {

            if (!e.target.classList.contains('produtoSelect')) return;

            if (!clienteSelect.value) {
                alert('Selecione o cliente primeiro!');
                e.target.value = '';
                return;
            }

            const produtoId = e.target.value;
            const produto = produtos.find(p => p.id == produtoId);
            const row = e.target.closest('.item-row');

            // PREÇO E UNIDADE
            const preco = parseFloat(produto?.preco_venda || 0);
            const unidade = produto?.unidade_medida?.nome || '';

            row.querySelector('.preco').textContent =
                preco.toFixed(2).replace('.', ',');

            row.querySelector('.unidade').textContent = unidade;

            row.querySelector('input[name*="[preco_unitario]"]').value = preco;

            // ===============================
            // LOTE (SEM FILTRO DE ESTOQUE)
            // ===============================
            // 🔥 CARREGAR LOTES
            const loteSelect = row.querySelector('.loteSelect');
            loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

            if (!produto || !produto.lotes) return;

            // const lotesValidos = produto.lotes.filter(l => {
            //     const disponivel =
            //         (parseFloat(l.quantidade_solicitada) || 0) -
            //         (parseFloat(l.quantidade_reservada) || 0);

            //     // return l.status == 1 && disponivel > 0;
            //     return l.status == 1 ;
            // });

           const lotesValidos = produto.lotes
            .map(l => {

                const quantidade = Number(l.quantidade ?? 0);
                const reservado = Number(l.quantidade_reservada ?? 0);

                const disponivel = quantidade - reservado;

                return {
                    ...l,
                    disponivel
                };
            })
            .filter(l => l.disponivel > 0 && l.status == 1);

            // 👉 SEM LOTE
            if (lotesValidos.length === 0) {
                loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
                return;
            }

            // 👉 COM VÁRIOS LOTES
            lotesValidos.forEach(l => {

                const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                loteSelect.innerHTML += `
                    <option value="${l.id}">
                        ${l.numero_lote} | Qtd: ${disponivel}
                    </option>
                `;
            });
            atualizarTotal();
        });

        // ===============================
        // ALTERAR QUANTIDADE
        // ===============================
        container.addEventListener('input', e => {
            if (e.target.classList.contains('qtd')) {
                atualizarTotal();
            }
        });

        // ===============================
        // REMOVER ITEM
        // ===============================
        container.addEventListener('click', e => {
            if (e.target.classList.contains('remover')) {

                e.target.closest('.item-row').remove();
                atualizarTotal();

                if (container.querySelectorAll('.item-row').length === 0) {
                    clienteSelect.classList.remove('readonly-select');
                }
            }
        });

        // ===============================
        // ADICIONAR ITEM
        // ===============================
        addBtn.addEventListener('click', () => {

            if (!clienteSelect.value) {
                alert('Selecione um cliente primeiro');
                return;
            }

            const last = container.querySelector('.item-row:last-child');

            if (last) {
                const produto = last.querySelector('.produtoSelect').value;
                const qtd = last.querySelector('.qtd').value;
                const preco = last.querySelector('input[name*="[preco_unitario]"]').value;

                if (!produto || !qtd || !preco) {
                    alert('Complete o item antes de adicionar outro');
                    return;
                }
            }

            criarItem();
        });

        // ===============================
        // INICIAL
        // ===============================
        atualizarTotal();

        // ===============================
        // LIMPAR EDIÇÃO AO SAIR
        // ===============================
        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon(
                "{{ route('orcamentos.limparEdicao', $orcamento->id) }}",
                new URLSearchParams({'_token': '{{ csrf_token() }}'})
            );
        });

        // ===============================
        // TIMER INATIVIDADE
        // ===============================
        let timeout;
        const tempoLimite = 30000;

        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                window.location.href = "{{ route('orcamentos.index') }}";
            }, tempoLimite);
        }

        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeydown = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;

    });
    
</script> -->



<script>
    document.addEventListener('DOMContentLoaded', () => {

        const produtos = @json($produtos);
        const container = document.getElementById('itensContainer');
        const totalSpan = document.getElementById('total');
        const addBtn = document.getElementById('addProduto');
        const clienteSelect = document.getElementById('clienteSelect');

        let index = container.querySelectorAll('.item-row').length;

        // ===============================
        // CRIAR ITEM
        // ===============================
        function criarItem() {

            const div = document.createElement('div');
            div.classList.add('row','align-items-center','mb-2','item-row');

            div.innerHTML = `
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

                <div class="col-md-3">
                    <select name="produtos[${index}][lote_id]" class="form-select loteSelect" required>
                        <option value="">Selecione o lote</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <input type="number"
                        name="produtos[${index}][quantidade_solicitada]"
                        class="form-control qtd"
                        value="1" min="1" required>
                </div>

                <div class="col-md-2 unidade"></div>

                <div class="col-md-1">
                    <div class="preco">0,00</div>
                    <input type="hidden"
                        name="produtos[${index}][preco_unitario]"
                        value="0">
                </div>

                <div class="col-md-1 subtotal">0,00</div>

                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-danger remover">X</button>
                </div>
            `;

            container.appendChild(div);
            index++;

            clienteSelect.classList.add('readonly-select');

            atualizarOpcoesProdutos();
        }

        // ===============================
        // PRODUTOS JÁ SELECIONADOS
        // ===============================
        function getProdutosSelecionados() {
            const selecionados = [];

            document.querySelectorAll('.produtoSelect').forEach(select => {
                if (select.value) {
                    selecionados.push(select.value);
                }
            });

            return selecionados;
        }

        // ===============================
        // ATUALIZAR OPTIONS
        // ===============================
        function atualizarOpcoesProdutos() {

            const selecionados = getProdutosSelecionados();

            document.querySelectorAll('.produtoSelect').forEach(select => {

                const valorAtual = select.value;

                select.querySelectorAll('option').forEach(option => {

                    if (!option.value) return;

                    // mantém selecionado atual
                    if (option.value === valorAtual) {
                        option.style.display = '';
                        return;
                    }

                    // oculta repetidos
                    option.style.display =
                        selecionados.includes(option.value) ? 'none' : '';
                });
            });
        }

        // ===============================
        // ATUALIZAR TOTAL
        // ===============================
        function atualizarTotal() {

            let total = 0;

            container.querySelectorAll('.item-row').forEach(row => {

                const qtd = parseFloat(row.querySelector('.qtd').value) || 0;
                const preco = parseFloat(row.querySelector('input[name*="[preco_unitario]"]').value) || 0;

                const subtotal = qtd * preco;

                row.querySelector('.subtotal').textContent =
                    subtotal.toFixed(2).replace('.', ',');

                total += subtotal;
            });

            totalSpan.textContent = total.toFixed(2).replace('.', ',');
        }

        // ===============================
        // SELECIONAR PRODUTO
        // ===============================
        container.addEventListener('change', e => {

            if (!e.target.classList.contains('produtoSelect')) return;

            if (!clienteSelect.value) {
                alert('Selecione o cliente primeiro!');
                e.target.value = '';
                return;
            }

            const produtoId = e.target.value;
            const produto = produtos.find(p => p.id == produtoId);
            const row = e.target.closest('.item-row');

            // PREÇO E UNIDADE
            const preco = parseFloat(produto?.preco_venda || 0);
            const unidade = produto?.unidade_medida?.nome || '';

            row.querySelector('.preco').textContent =
                preco.toFixed(2).replace('.', ',');

            row.querySelector('.unidade').textContent = unidade;

            row.querySelector('input[name*="[preco_unitario]"]').value = preco;

            // ===============================
            // LOTES
            // ===============================
            const loteSelect = row.querySelector('.loteSelect');
            loteSelect.innerHTML = '<option value="">Selecione o lote</option>';

            if (!produto || !produto.lotes) return;

            const lotesValidos = produto.lotes
                .map(l => {

                    const quantidade = Number(l.quantidade ?? 0);
                    const reservado = Number(l.quantidade_reservada ?? 0);

                    return {
                        ...l,
                        disponivel: quantidade - reservado
                    };
                })
                .filter(l => l.disponivel > 0 && l.status == 1);

            if (lotesValidos.length === 0) {
                loteSelect.innerHTML = '<option value="">Sem lote disponível</option>';
                return;
            }

            lotesValidos.forEach(l => {

                const disponivel =
                    (parseFloat(l.quantidade) || 0) -
                    (parseFloat(l.quantidade_reservada) || 0);

                loteSelect.innerHTML += `
                    <option value="${l.id}">
                        ${l.numero_lote} | Qtd: ${disponivel}
                    </option>
                `;
            });

            atualizarTotal();
            atualizarOpcoesProdutos();
        });

        // ===============================
        // ALTERAR QUANTIDADE
        // ===============================
        container.addEventListener('input', e => {
            if (e.target.classList.contains('qtd')) {
                atualizarTotal();
            }
        });

        // ===============================
        // REMOVER ITEM
        // ===============================
        container.addEventListener('click', e => {

            if (e.target.classList.contains('remover')) {

                e.target.closest('.item-row').remove();

                atualizarTotal();
                atualizarOpcoesProdutos();

                if (container.querySelectorAll('.item-row').length === 0) {
                    clienteSelect.classList.remove('readonly-select');
                }
            }
        });

        // ===============================
        // ADICIONAR ITEM
        // ===============================
        addBtn.addEventListener('click', () => {

            if (!clienteSelect.value) {
                alert('Selecione um cliente primeiro');
                return;
            }

            const last = container.querySelector('.item-row:last-child');

            if (last) {

                const produto = last.querySelector('.produtoSelect').value;
                const qtd = last.querySelector('.qtd').value;
                const preco = last.querySelector('input[name*="[preco_unitario]"]').value;

                if (!produto || !qtd || !preco) {
                    alert('Complete o item antes de adicionar outro');
                    return;
                }
            }

            criarItem();
        });

        // ===============================
        // INICIAL
        // ===============================
        atualizarTotal();
        atualizarOpcoesProdutos();

        // ===============================
        // LIMPAR EDIÇÃO AO SAIR
        // ===============================
        window.addEventListener('beforeunload', () => {
            navigator.sendBeacon(
                "{{ route('orcamentos.limparEdicao', $orcamento->id) }}",
                new URLSearchParams({'_token': '{{ csrf_token() }}'})
            );
        });

        // ===============================
        // TIMER INATIVIDADE
        // ===============================
        let timeout;
        const tempoLimite = 30000;

        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                window.location.href = "{{ route('orcamentos.index') }}";
            }, tempoLimite);
        }

        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeydown = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;

    });
</script>

<script src="{{ asset('js/orcamento.js') }}"></script>
@endsection