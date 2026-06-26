@extends('layouts.app')


<main class="container-fluid px-4 mt-4">
    @section('content')
</main>

<div class="container-fluid">

    <h4 class="mb-3">Divergências de Estoque</h4>

    <form method="GET" class="card card-body mb-3">
        <div class="row align-items-end">

            <div class="col-md-2 mb-2">
                <label>Produto</label>
                <input type="text" name="produto" class="form-control"
                       value="{{ request('produto') }}"
                       placeholder="Buscar por produto">
            </div>

            <div class="col-md-2 mb-2">
                <label>Tipo</label>
                <select name="tipo" class="form-control">
                    <option value="">Todos</option>
                    <option value="venda" {{ request('tipo') == 'venda' ? 'selected' : '' }}>Venda</option>
                    <option value="inventario" {{ request('tipo') == 'inventario' ? 'selected' : '' }}>Inventário</option>
                    <option value="ajuste_manual" {{ request('tipo') == 'ajuste_manual' ? 'selected' : '' }}>Ajuste Manual</option>
                </select>
            </div>

            <div class="col-md-2 mb-2">
                <label>Data Inicial</label>
               <input type="date"
                name="data_inicial"
                class="form-control"
                value="{{ request('data_inicial', now()->format('Y-m-d')) }}">
            </div>

            <div class="col-md-2 mb-2">
                <label>Data Final</label>
                <input type="date"
            name="data_final"
            class="form-control"
            value="{{ request('data_final', now()->format('Y-m-d')) }}">
            </div>

            <div class="col-md-3 mb-2 ms-auto d-flex align-items-end justify-content-end gap-2">

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-search"></i>
                    Filtrar
                </button>

                <a href="{{ route('estoque-divergencias.index') }}"
                class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-clockwise"></i>
                    Limpar
                </a>

                <a href="{{ route('estoque-divergencias.pdf', request()->query()) }}"
                target="_blank"
                class="btn btn-danger"
                style="width: 120px;">
                    <i class="bi bi-file-earmark-pdf"></i>
                    PDF
                </a>
            </div>

        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
    <thead class="thead-light">
        <tr>
            <th style="width: 40px;"></th>
            <th>Produto</th>
            <th>Ocorrências</th>
            <th>Total Solicitado</th>
            <th>Total Atendido</th>
            <th>Total Diferença</th>
            <th>Última Data</th>
            <th>Ações</th>
        </tr>
    </thead>

    <tbody>
        @forelse($divergencias->groupBy('produto_id') as $produtoId => $grupo)
            @php
                $primeira = $grupo->first();
                $produtoNome = $primeira->produto->nome ?? 'Produto não encontrado';

                $totalSolicitado = $grupo->sum('quantidade_solicitada');
                $totalAtendido = $grupo->sum('quantidade_atendida');
                $totalDiferenca = $grupo->sum('diferenca');

                $ultimaData = optional($grupo->max('created_at'))->format('d/m/Y H:i');
                $linhaId = 'detalhes-produto-' . $produtoId;
            @endphp

            <tr class="linha-resumo" data-target="{{ $linhaId }}">
                <td class="text-center fw-bold text-primary">
                    <span class="icone-toggle">+</span>
                </td>

                <td>
                    <strong>{{ $produtoNome }}</strong>
                </td>

                <td>{{ $grupo->count() }}</td>

                <td>{{ number_format($totalSolicitado, 3, ',', '.') }}</td>

                <td>{{ number_format($totalAtendido, 3, ',', '.') }}</td>

                <td class="text-danger fw-bold">
                    {{ number_format($totalDiferenca, 3, ',', '.') }}
                </td>

                <td>{{ $ultimaData }}</td>

                <td>
                    <button type="button" class="btn btn-sm btn-success" disabled>
                        Gerar Pedido
                    </button>
                </td>
            </tr>

            <tr id="{{ $linhaId }}" class="linha-detalhes">
                <td colspan="8" class="p-0">
                    <table class="table table-sm table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Venda</th>
                                <th>Caixa</th>
                                <th>Solicitado</th>
                                <th>Atendido</th>
                                <th>Diferença</th>
                                <th>Tipo</th>
                                <th>Usuário</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($grupo as $divergencia)
                                <tr>
                                    <td>{{ $divergencia->id }}</td>
                                    <td>{{ $divergencia->venda_id ?? '-' }}</td>
                                    <td>{{ $divergencia->caixa_id ?? '-' }}</td>
                                    <td>{{ number_format($divergencia->quantidade_solicitada, 3, ',', '.') }}</td>
                                    <td>{{ number_format($divergencia->quantidade_atendida, 3, ',', '.') }}</td>
                                    <td class="text-danger fw-bold">
                                        {{ number_format($divergencia->diferenca, 3, ',', '.') }}
                                    </td>
                                    <td>{{ ucfirst($divergencia->tipo) }}</td>
                                    <td>{{ $divergencia->usuario->name ?? '-' }}</td>
                                    <td>{{ optional($divergencia->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('estoque-divergencias.show', $divergencia->id) }}"
                                           class="btn btn-sm btn-info">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Nenhuma divergência encontrada.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<style>
    .linha-resumo {
        cursor: pointer;
    }

    .linha-resumo:hover {
        background-color: #f1f7ff;
    }

    .linha-detalhes {
        display: none;
        background-color: #fff;
    }

    .icone-toggle {
        display: inline-block;
        width: 18px;
        font-size: 18px;
        line-height: 1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.linha-resumo').forEach(function (linha) {
            linha.addEventListener('click', function () {
                const targetId = this.dataset.target;
                const detalhes = document.getElementById(targetId);
                const icone = this.querySelector('.icone-toggle');

                if (!detalhes) {
                    return;
                }

                if (detalhes.style.display === 'table-row') {
                    detalhes.style.display = 'none';
                    icone.textContent = '+';
                } else {
                    detalhes.style.display = 'table-row';
                    icone.textContent = '−';
                }
            });
        });
    });
</script>
        </div>
    </div>

    <div class="mt-3">
        {{ $divergencias->links() }}
    </div>

</div>
<style>
    .linha-resumo {
        cursor: pointer;
        transition: .2s;
    }

    .linha-resumo:hover {
        background: #eef5ff;
    }

    .linha-detalhes {
        display: none;
        background: #fcfcfc;
    }

    .icone-toggle{
        width:20px;
        display:inline-block;
        font-weight:bold;
        color:#0d6efd;
    }
</style>
@endsection