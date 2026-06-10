@extends('layouts.app') {{-- Ou o seu template padrão --}}

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- CARD 1: BUSCA DE CLIENTE -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-bold">
                    📇 Terminal de Recebimento - Carteira de Crédito
                </div>
                <div class="card-body">
                    <label for="buscar_cliente" class="form-label fw-bold">Selecione o Cliente para Recebimento:</label>
                    <select class="form-select form-select-lg" id="buscar_cliente" onchange="carregarDadosCliente(this.value)">
                        <option value="" selected disabled>Digite o nome ou CPF do cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" 
                                    data-nome="{{ $cliente->nome }}" 
                                    data-bloqueado="{{ $cliente->bloqueado_credito }}">
                                {{ $cliente->nome }} (CPF/CNPJ: {{ $cliente->cpf_cnpj ?? 'Não Informado' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- CARD 2: FORMULÁRIO DE QUITAÇÃO (FICA OCULTO ATÉ SELECIONAR UM CLIENTE) -->
            <div id="card_formulario_recebimento" class="card shadow-sm border-0 d-none">
                <div class="card-header bg-success text-white fw-bold">
                    💵 Informações de Débito e Entrada Financeira
                </div>
                <form id="formTerminalPagamento" onsubmit="enviarPagamentoTerminal(event)">
                    <div class="card-body">
                        <!-- Campos Ocultos de Controle -->
                        <input type="hidden" id="cliente_id_input">

                        <!-- Linha de Resumo de Saldos -->
                        <div class="row g-3 mb-4 bg-light p-3 rounded">
                            <div class="col-6 col-md-4">
                                <small class="text-muted d-block uppercase fw-bold">Limite Contratado</small>
                                <span class="fs-5 fw-bold text-secondary" id="txt_limite">R$ 0,00</span>
                            </div>
                            <div class="col-6 col-md-4">
                                <small class="text-muted d-block uppercase fw-bold">Saldo Disponível (CC)</small>
                                <span class="fs-5 fw-bold" id="txt_disponivel">R$ 0,00</span>
                            </div>
                            <div class="col-12 col-md-4">
                                <small class="text-muted d-block uppercase fw-bold">Total Utilizado / Dívida</small>
                                <span class="fs-5 fw-bold text-danger" id="txt_divida">R$ 0,00</span>
                            </div>
                        </div>

                        <!-- Inputs de Entrada de Valores -->
                        <div class="mb-4">
                            <label for="valor_pagamento" class="form-label fw-bold text-success fs-5">
                                Valor a ser Pago pelo Cliente (R$):
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text border-success bg-success-subtle fw-bold">R$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control border-success fw-bold text-success" id="valor_pagamento" required placeholder="0,00">
                                <button type="button" class="btn btn-outline-success fw-bold" onclick="quitarValorTotal()">
                                    Quitar Total
                                </button>
                            </div>
                            <div class="form-text text-muted">Você pode digitar valores parciais para amortização ou clicar em "Quitar Total".</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="meio_pagamento" class="form-label fw-bold">Meio de Captura:</label>
                                <select class="form-select" id="meio_pagamento" required>
                                    <option value="dinheiro" selected>Dinheiro em Espécie</option>
                                    <option value="pix">PIX (Transferência)</option>
                                    <option value="debito">Cartão de Débito</option>
                                    <option value="credito">Cartão de Crédito</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="observacao_interna" class="form-label fw-bold">Observação (Opcional):</label>
                                <input type="text" class="form-control" id="observacao_interna" placeholder="Ex: Pago pelo proprietário da obra">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between py-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetarTerminal()">Limpar Tela</button>
                        <button type="submit" class="btn btn-success btn-lg fw-bold px-5">Confirmar Recebimento</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
// Armazena temporariamente os dados calculados do cliente ativo na tela
let dadosClienteAtual = {
    id: null,
    limite: 0,
    disponivel: 0,
    divida: 0
};

// 1. Busca os saldos reais no ContaCorrenteService via API assíncrona assim que seleciona o cliente
function carregarDadosCliente(clienteId) {
    if (!clienteId) return;

    // Rota padrão do seu Laravel para obter dados básicos e saldo atual
    // Se preferir, crie uma rota rápida GET /credito/clientes/{id}/saldo que retorne essas propriedades
    let urlBuscaSaldo = `/api/credito/clientes/${clienteId}/transacoes`; 

    axios.get(urlBuscaSaldo)
        .then(response => {
            let dados = response.data.cliente;
            
            // Mapeia o estado interno
            dadosClienteAtual.id = dados.id;
            dadosClienteAtual.limite = parseFloat(dados.limite_atual ?? 500.00);
            dadosClienteAtual.disponivel = parseFloat(dados.saldo_disponivel);
            
            // Cálculo matemático da dívida real (Limite - Disponível)
            dadosClienteAtual.divida = Math.max(0, dadosClienteAtual.limite - dadosClienteAtual.disponivel);

            // Atualiza os componentes visuais da View
            document.getElementById('cliente_id_input').value = dados.id;
            document.getElementById('txt_limite').innerText = 'R$ ' + dadosClienteAtual.limite.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('txt_disponivel').innerText = 'R$ ' + dadosClienteAtual.disponivel.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('txt_divida').innerText = 'R$ ' + dadosClienteAtual.divida.toLocaleString('pt-BR', {minimumFractionDigits: 2});

            // Limpa inputs antigos e exibe o formulário de quitação
            document.getElementById('valor_pagamento').value = '';
            document.getElementById('card_formulario_recebimento').classList.remove('d-none');
        })
        .catch(error => {
            alert('Erro ao sincronizar saldo do cliente no banco de dados.');
            resetarTerminal();
        });
}

// Botão rápido para jogar o valor exato da dívida no input de quitação
function quitarValorTotal() {
    document.getElementById('valor_pagamento').value = dadosClienteAtual.divida.toFixed(2);
}

// Limpa o estado da tela caso o operador queira cancelar
function resetarTerminal() {
    document.getElementById('buscar_cliente').value = '';
    document.getElementById('card_formulario_recebimento').classList.add('d-none');
    dadosClienteAtual = { id: null, limite: 0, disponibilizado: 0, divida: 0 };
}

// 2. Envia os dados estruturados do recebimento direto para o seu Controller
function enviarPagamentoTerminal(event) {
    event.preventDefault();

    let clienteId = document.getElementById('cliente_id_input').value;
    let valorRecebido = document.getElementById('valor_pagamento').value;
    let meioCaptura = document.getElementById('meio_pagamento').value;
    let observacao = document.getElementById('observacao_interna').value;

    // Rota que chama o seu método ContaCorrenteService::adicionarCredito
    let urlPost = `/api/clientes/${clienteId}/credito/pagar`;

    axios.post(urlPost, {
        valor: valorRecebido,
        pagamento_venda_id: null, // Lançamento livre de quitação
        meio_captura: meioCaptura,
        observacao: observacao
    })
    .then(response => {
        alert('Recebimento registrado com sucesso! Saldo e limite da carteira atualizados.');
        resetarTerminal();
    })
    .catch(error => {
        let erroMsg = error.response?.data?.message || 'Falha crítica ao gravar transação.';
        alert('Erro operacional: ' + erroMsg);
    });
}
</script>
@endpush
