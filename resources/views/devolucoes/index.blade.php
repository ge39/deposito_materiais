@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Rastrear Devoluções Venda</h2>

    <!-- Formulário de busca unificado -->
    <form action="{{ route('devolucoes.buscar') }}" method="GET" class="mb-4">
        <div class="row g-4">
            <div class="col-md-12">
                <label for="search" class="form-label fw-bold">Pesquisar Venda ou Cliente</label>
                <input type="text" id="search" name="search" class="form-control" 
                placeholder="Digite ID da venda ou nome do cliente" value="{{ request('search') }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('devolucoes.index') }}" class="btn btn-secondary">Limpar</a>
                <a href="{{ route('devolucoes.pendentes') }}" class="btn btn-warning">Devoluções Pendentes</a>
            </div>
        </div>
    </form>

    <!-- Cards de resultados -->
    @if(isset($vendas) && $vendas->isNotEmpty())
        
        <div class="row row-cols-1 g-4">

        @foreach($vendas as $item)
                    @php
                        // Alinhado com a propriedade definida no laço foreach do Controller
                        $qtdDisponivel = $item->quantidade_disponivel;
                        @endphp
                        <div class="col">
                            <div class="card h-100 shadow-sm @if($qtdDisponivel == 0) border-success @endif">
                                <!-- Cabeçalho do Card com informações da Venda -->
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center gap-2">
                                            <h5 class="card-title mb-0 fw-bold text-dark">
                                                Venda #{{ $item->venda_id }}
                                            </h5>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cupomModal"
                                                data-cupom-url="{{ url('/vendas/venda/'.$item->venda_id.'/cupom') }}">
                                                <i class="bi bi-receipt"></i> Ver Cupom
                                            </button>
                                        </div>

                                        <span class="badge bg-secondary p-2">
                                            <i class="bi bi-calendar3"></i>
                                            {{ $item->data_venda ? \Carbon\Carbon::parse($item->data_venda)->format('d/m/Y') : '---' }}
                                        </span>
                                    </div>

                                <div class="card-body">
                                    <!-- Informações do Cliente -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <p class="card-text mb-1"><strong>Cliente:</strong> {{ $item->cliente_nome }}</p>
                                            <p class="card-text mb-1"><strong>Tipo de Pessoa:</strong> {{ $item->cliente_tipo }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <p class="card-text mb-1"><strong>Documento (CPF/CNPJ):</strong> {{ $item->cliente_cpf_cnpj }}</p>
                                            <p class="card-text mb-1"><strong>Valor Total da Venda:</strong> R$ {{ number_format($item->valor_total, 2, ',', '.') }}</p>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <p class="card-text mb-1"><strong>Total Estornado:</strong> <span class="text-danger fw-bold">R$ {{ number_format($item->valor_extornado, 2, ',', '.') }}</span></p>
                                            <p class="card-text mb-1"><strong>Status Geral:</strong> 
                                                @if($qtdDisponivel == 0)
                                                    <span class="text-success fw-bold">Totalmente devolvido</span>
                                                @else
                                                    <span class="text-primary fw-bold">Disponível para devolução</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-3">Itens desta Venda</h6>
                                    
                                    <!-- Seção de listagem completa dos itens do cupom -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped align-middle text-center mb-0">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th class="text-start" style="width: 20%;">Produto</th>
                                                    <th>Qtd Comprada</th>
                                                    <th>Preço Unit.</th>
                                                    <th>Subtotal</th>
                                                    <th class="table-danger text-danger">Lote Retorno</th>
                                                    <th class="table-danger text-danger">Qtd Devolvida</th>
                                                    <th class="table-success text-success">Qtd Disponível</th>
                                                    <th class="table-success text-success">Valor Disponível</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <!-- Descrição do Produto -->
                                                    <td class="text-start fw-bold text-primary">
                                                        {{ $item->produto_nome ?? 'Verifique os produtos no formulário' }}
                                                    </td>
                                                    
                                                    <!-- Qtd Comprada -->
                                                    <td>{{ number_format($item->quantidade_comprada, 0, ',', '.') }}</td>
                                                    
                                                    <!-- Preço Unitário -->
                                                    <td>R$ {{ number_format($item->preco_unitario ?? 0, 2, ',', '.') }}</td>
                                                    
                                                    <!-- Subtotal original -->
                                                    <td class="fw-bold">R$ {{ number_format($item->subtotal ?? 0, 2, ',', '.') }}</td>
                                                    
                                                    <!-- Histórico de Lote de Reentrada -->
                                                    <td class="table-danger fw-bold">
                                                        <span class="badge @if(($item->numero_lote ?? 'Nenhum') == 'Nenhum') bg-light text-muted border @else bg-danger @endif">
                                                            {{ $item->numero_lote ?? 'Nenhum' }}
                                                        </span>
                                                    </td>
                                                    
                                                    <!-- Qtd Já Devolvida -->
                                                    <td class="table-danger text-danger fw-bold">{{ number_format($item->quantidade_devolvida, 0, ',', '.') }}</td>
                                                    
                                                    <!-- Qtd Disponível Restante -->
                                                    <td class="table-success text-success fw-bold fs-5">{{ number_format($qtdDisponivel, 0, ',', '.') }}</td>
                                                    
                                                    <!-- Valor Financeiro ainda disponível para estornar -->
                                                    <td class="table-success text-success fw-bold">R$ {{ number_format($item->valor_disponivel ?? 0, 2, ',', '.') }}</td>
                                                    
                                                    <!-- Botão de Ação -->
                                                    <td>
                                                        @if($qtdDisponivel > 0)
                                                            <a href="{{ route('devolucoes.registrar', ['venda_id' => $item->venda_id]) }}"
                                                            class="btn btn-sm btn-danger px-3 shadow-sm">
                                                                <i class="bi bi-x-circle"></i> Devolver
                                                            </a>
                                                        @else
                                                            <button class="btn btn-sm btn-light border text-muted" disabled>Sem Saldo</button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Botões de navegação inferiores do card -->
                                    <div class="mt-4 d-flex gap-2 justify-content-end">
                                        <a href="{{ route('devolucoes.index') }}" class="btn btn-sm btn-secondary px-4">Voltar</a>
                                    </div>
                                     <!-- Carimbo de Devolução Total (Só aparece se a quantidade disponível for realmente zero) -->
                                    @if(isset($qtdDisponivel) && $qtdDisponivel <= 0)
                                        <div style="
                                                position: absolute;
                                                top: 50%;
                                                left: 50%;
                                                transform: translate(-50%, -50%) rotate(-5deg);
                                                z-index: 333;
                                                pointer-events: none; /* Permite clicar no que estiver por baixo */
                                                opacity: 0.9; /* Dá um efeito sutil de carimbo real */
                                            ">
                                            <div style="
                                                display: inline-block;
                                                padding: 15px 35px;
                                                border: 4px solid green;
                                                color: green;
                                                background-color: rgba(191, 186, 186, 0.9); /* Fundo branco com leve transparência */
                                                font-weight: bold;
                                                font-size: 1.4rem;
                                                text-align: center;
                                                border-radius: 8px;
                                                text-transform: uppercase;
                                                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                                            ">
                                               <strong>O Produto  <span class="text-primary"> </span> <span class="text-danger"> {{ $item->produto_nome ?? 'Verifique os produtos no formulário' }}</span>, foi devolvido ao estoque</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Links de paginação estável -->
                <div class="mt-4 d-flex justify-content-center">
                    {{ $vendas->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="alert alert-warning text-center py-4 shadow-sm" style="background-color: #f0d791; color: #664d03; border-color: #f5e1a4;">
                    <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2"></i>
                    Nenhuma venda ou cliente encontrado com os termos digitados.
                </div>
        @endif
   <!-- <div class="card h-100 shadow-sm @if(($qtdDisponivel ?? 0) == 0) border-success @endif"> -->
</div>

    
</div>

<!-- Modal Cupom -->
<div class="modal fade" id="cupomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-receipt"></i> Cupom da Venda
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar">
                </button>
            </div>

            <div class="modal-body p-0">
                <iframe
                    id="cupomFrame"
                    src=""
                    width="100%"
                    height="700"
                    frameborder="0">
                </iframe>
            </div>

        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const cupomModal = document.getElementById('cupomModal');
        const cupomFrame = document.getElementById('cupomFrame');

        if (cupomModal) {

            cupomModal.addEventListener('show.bs.modal', function (event) {

                const button = event.relatedTarget;

                if (!button) {
                    return;
                }

                const url = button.getAttribute('data-cupom-url');

                console.log('Abrindo cupom:', url);

                cupomFrame.src = url;
            });

            cupomModal.addEventListener('hidden.bs.modal', function () {

                cupomFrame.src = '';
            });
        }

    });
</script>
@endsection
