@extends('layouts.app')

    @section('content')
    <div class="container mt-4">
        <h2 class="mb-4">Lotes do Produto: 000{{ $produto->id }} </h2>

    <div class="justify-content-end gap-2 text-primary">
        Produto: <strong> {{ $produto->nome }}  - Estoque: {{ $produto->estoque ?? '0'}}  {{ $produto->unidadeMedida->nome }}(s)</strong>
    </div>

    <!-- 🎯 CORREÇÃO 1: Verifica a contagem usando a variável filtrada do Controller -->
    @if($lotes->isEmpty())
        <div class="alert alert-warning text-center py-3">
            Nenhum lote com saldo disponível para este produto.
        </div>
    @else
        <div class="row">
            <!-- 🎯 CORREÇÃO 2: Faz o loop usando $lotes em vez de $produto->lotes -->
            @foreach ($lotes as $lote)
                <div class="col-md-4 mb-3">
                    <div class="card border shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-success fw-bold">
                                <i class="bi bi-box-seam"></i> Lote #{{ $lote->numero_lote }} 
                            </h5>
                            <p class="card-text mb-1"><strong>Lote Criado por:</strong> {{ $lote->usuario->name ?? '-' }}</p>
                            
                            <p class="card-text mb-1">
                                <strong>Pedido Compra:</strong>
                                @if ($lote->pedido_compra_id)
                                    <a href="{{ route('pedidos.show', $lote->pedido_compra_id) }}">
                                        Pedido #{{ $lote->pedido_compra_id }}
                                    </a>
                                @else
                                    <span class="text-danger">Sem pedido</span>
                                @endif
                            </p>

                            <p class="card-text mb-1"><strong>Produto ID:</strong> 000{{ $lote->produto_id }}</p>
                            <p class="card-text mb-1"><strong>Qtd Comprada:</strong> {{ (int)$lote->quantidade }} {{ $produto->unidadeMedida->nome }}(s)</p>
                            <p class="card-text mb-1"><strong>Qtd Disponivel:</strong> {{ $lote->quantidade_disponivel }} {{ $produto->unidadeMedida->nome }}(s)</p>
                            <p class="card-text mb-1"><strong>Qtd Reservada p/ Orcamento:</strong> {{ $lote->quantidade_reservada }} {{ $produto->unidadeMedida->nome }}(s)</p>
                            <p class="card-text mb-1"><strong>Preço de Compra:</strong> R$ {{ number_format($lote->preco_compra, 2, ',', '.') }}</p>
                            <p class="card-text mb-1"><strong>Data da Compra:</strong> {{ \Carbon\Carbon::parse($lote->data_compra)->format('d/m/Y') }}</p>
                            <p class="card-text mb-1"><strong>Validade até::</strong> {{ \Carbon\Carbon::parse($lote->validade_lote)->format('d/m/Y') }}</p>
                            <p class="card-text text-muted small"><strong>Cadastrado em:</strong> {{ $lote->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 🎯 ADICIONADO: Links de paginação caso existam mais de 20 lotes ativos -->
        <div class="mt-3 d-flex justify-content-center">
            {{ $lotes->appends(request()->query())->links() }}
        </div>
    @endif


   <div class="container mt-4 d-flex justify-content-end gap-2">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Voltar</a>
        <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Início</a>
    </div>


</div>
@endsection
