@extends('layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">📦 Relatório de Reposição de Estoque</h3>

    {{-- 🔍 FILTROS --}}
    <form method="GET" class="row mb-4">

        <div class="col-md-2">
            <label>Data início</label>
            <input type="date" name="data_inicio" class="form-control"
                value="{{ request('data_inicio') }}">
        </div>

        <div class="col-md-2">
            <label>Data fim</label>
            <input type="date" name="data_fim" class="form-control"
                value="{{ request('data_fim') }}">
        </div>

        <div class="col-md-3">
            <label>Produto (início do nome)</label>
            <input type="text" name="produto" class="form-control"
                value="{{ request('produto') }}"
                placeholder="Ex: Areia">
        </div>

        <div class="col-md-3">
            <label>Código de barras</label>
            <input type="text" name="codigo_barras" class="form-control"
                value="{{ request('codigo_barras') }}">
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button class="btn btn-primary w-50">Filtrar</button>
            <a href="{{ route('relatorio.reposicao') }}" class="btn btn-secondary w-50">Limpar</a>
        </div>

    </form>
    <!-- <a href="{{ route('relatorio.reposicao.pdf', request()->query->all()) }}" 
        class="btn btn-success mb-3" target="_blank">
        📄 Gerar PDF
    </a> -->

    <div class="mb-3 d-flex gap-2">

    <a href="{{ route('relatorio.reposicao.pdf', array_merge(request()->all(), ['orientacao' => 'portrait'])) }}"
       class="btn btn-outline-secondary"
       target="_blank">
        📄 PDF Retrato
    </a>

    <a href="{{ route('relatorio.reposicao.pdf', array_merge(request()->all(), ['orientacao' => 'landscape'])) }}"
       class="btn btn-outline-secondary"
       target="_blank">
        📄 PDF Paisagem
    </a>

</div>
    
    {{-- 💰 TOTAIS --}}
    <div class="row mb-4">

        <div class="col-md-6">
            <div class="card p-3">
                <strong>Total de itens Pendentes:</strong>
                <h4>{{ number_format($totais->total_pendente ?? 0, 2, ',', '.') }}</h4>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3">
                <strong>Valor Total - Orçamentos Pendentes:</strong>
                <h4>R$ {{ number_format($totais->valor_total ?? 0, 2, ',', '.') }}</h4>
            </div>
        </div>

    </div>

    {{-- 📊 RESUMO --}}
    <div class="card mb-4">
        <div class="card-header">
            🔥 Produtos com maior necessidade de compra
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Orcamento - Link gerador pdf</th>
                        <th>Unidade</th>
                        <th>Pendente</th>
                        <!-- <th>Estoque</th> -->
                        <th>à Comprar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumo as $r)
                        <tr>
                            <td>{{ $r->nome }}</td>
                            <td style="width: 600px; white-space: normal;">
                                @php
                                    $ids = explode(',', $r->ids_orcamento ?? '');
                                    $codigos = explode(',', $r->codigos_orcamento ?? '');
                                @endphp

                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($codigos as $index => $codigo)
                                        @php $id = $ids[$index] ?? null; @endphp
                                        @if($id)
                                            <a href="{{ url("/orcamentos/{$id}/pdf") }}" 
                                            class="badge bg-primary text-decoration-none" target="_blank" 
                                            rel="noopener noreferrer">
                                                {{ trim($codigo) }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $r->unidade ?? '-' }}</td>
                            <td>{{ number_format($r->total_pendente, 2, ',', '.') }}</td>
                            <!-- <td>{{ number_format($r->quantidade_estoque, 2, ',', '.') }}</td> -->
                             <!-- <td>{{ number_format($r->total_pendente, 2, ',', '.') }}</td> -->
                            <td>
                                <span class="badge bg-danger text-center">
                                    {{ number_format($r->total_pendente, 2, ',', '.') }} - {{ $r->unidade ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    {{-- 📋 LISTAGEM PRINCIPAL --}}
    <div class="card">
        <div class="card-header">
            📋 Detalhamento por Produto
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Cód. Barras</th>
                        <th>Unidade</th>
                        <th>Total Pedido</th>
                        <th>Atendido</th>
                        <th>Pendente</th>
                        <th>Prv.Entrega</th>
                        <th>Necessario</th>
                        <th>Valor Orcamento</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($dados as $item)
                        <tr>

                            <td>{{ $item->produto_nome }}</td>

                            <td>{{ $item->codigo_barras }}</td>

                            <td>{{ $item->unidade ?? '-' }}</td>

                            <td>{{ number_format($item->total_quantidade, 2, ',', '.') }}</td>

                            <td>{{ number_format($item->total_atendida, 2, ',', '.') }}</td>

                            <td>
                                <strong>
                                    {{ number_format($item->total_pendente, 2, ',', '.') }}
                                </strong>
                            </td>
            
                            <!-- <td>{{ number_format($item->estoque_disponivel, 2, ',', '.') }}</td> -->
                            <td>
                                @if($item->previsao_entrega)
                                    @php
                                        $dataEntrega = \Carbon\Carbon::parse($item->previsao_entrega);
                                        $diasRestantes = now()->diffInDays($dataEntrega, false); // negativo se já passou
                                        // Calcula cor: quanto menor o prazo, mais vermelho
                                        if ($diasRestantes <= 0) {
                                            $cor = '#ff4d4d'; // vermelho forte (atrasado ou hoje)
                                        } elseif ($diasRestantes <= 3) {
                                            $cor = '#ff9999'; // vermelho claro
                                        } elseif ($diasRestantes <= 7) {
                                            $cor = '#ffcc66'; // laranja
                                        } elseif ($diasRestantes <= 14) {
                                            $cor = '#ffff99'; // amarelo
                                        } else {
                                            $cor = '#99ff99'; // verde
                                        }
                                    @endphp
                                    <span style="background-color: {{ $cor }}; padding: 0.25em 0.5em; border-radius: 0.25rem;">
                                        {{ $dataEntrega->format('d/m/Y') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->necessidade_reposicao > 0)
                                    <span class="badge bg-danger">
                                        {{ number_format($item->necessidade_reposicao, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>

                            <td>
                                R$ {{ number_format($item->valor_total, 2, ',', '.') }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                Nenhum resultado encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="p-3">
            {{ $dados->links() }}
        </div>

    </div>

</div>
@endsection