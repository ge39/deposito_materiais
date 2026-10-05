@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>

            <h1 class="h3 mb-1">

                <i class="bi bi-exclamation-triangle me-2"></i>
                Produtos com estoque zero

            </h1>

            <p class="text-muted mb-0">

                Relação para análise e futura tomada de decisão.

            </p>

        </div>


        <div class="d-flex flex-wrap gap-2">

            <a
                href="{{ route(
                    'bi.produtos.estoque-zero.pdf',
                    request()->query()
                ) }}"
                class="btn btn-outline-danger">

                <i class="bi bi-file-earmark-pdf me-1"></i>
                Exportar PDF

            </a>


            <a
                href="{{ route(
                    'bi.produtos.estoque-zero.planilha',
                    request()->query()
                ) }}"
                class="btn btn-outline-success">

                <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                Exportar planilha

            </a>


            <a
                href="{{ route('bi.produtos.index') }}"
                class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-1"></i>
                Produtos & Estoque

            </a>

        </div>

    </div>


    <div class="alert alert-warning">

        <strong>
            {{
                number_format(
                    $produtos->total(),
                    0,
                    ',',
                    '.'
                )
            }}
            produto(s)
        </strong>

        com estoque zerado encontrados.

        Estes registros são exibidos para análise,
        porém não entram no valor financeiro do estoque.

    </div>


    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('bi.produtos.estoque-zero') }}"
                class="row g-3 align-items-end">

                <div class="col-lg-4">

                    <label
                        class="form-label"
                        for="q">

                        Produto / SKU / código

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="q"
                        name="q"
                        value="{{ $filtros['q'] ?? '' }}"
                        placeholder="Pesquisar produto">

                </div>


                <div class="col-lg-3">

                    <label
                        class="form-label"
                        for="categoria_id">

                        Categoria

                    </label>

                    <select
                        class="form-select"
                        id="categoria_id"
                        name="categoria_id">

                        <option value="">
                            Todas
                        </option>

                        @foreach ($categorias as $categoria)

                            <option
                                value="{{ $categoria->id }}"
                                @selected(
                                    (string) (
                                        $filtros['categoria_id']
                                        ?? ''
                                    )
                                    ===
                                    (string) $categoria->id
                                )>

                                {{ $categoria->nome }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-lg-3">

                    <label
                        class="form-label"
                        for="ordenacao">

                        Ordenação

                    </label>

                    <select
                        class="form-select"
                        id="ordenacao"
                        name="ordenacao">

                        <option
                            value="nome"
                            @selected(
                                ($filtros['ordenacao'] ?? 'nome')
                                === 'nome'
                            )>

                            Produto

                        </option>

                        <option
                            value="ultima_venda"
                            @selected(
                                ($filtros['ordenacao'] ?? '')
                                === 'ultima_venda'
                            )>

                            Última venda

                        </option>

                        <option
                            value="estoque_minimo"
                            @selected(
                                ($filtros['ordenacao'] ?? '')
                                === 'estoque_minimo'
                            )>

                            Maior estoque mínimo

                        </option>

                        <option
                            value="vendido"
                            @selected(
                                ($filtros['ordenacao'] ?? '')
                                === 'vendido'
                            )>

                            Mais vendido historicamente

                        </option>

                    </select>

                </div>


                <div class="col-lg-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100">

                        <i class="bi bi-funnel me-1"></i>
                        Filtrar

                    </button>

                </div>

            </form>

        </div>

    </div>


    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th class="text-end">Estoque</th>
                            <th class="text-end">Mínimo</th>
                            <th>Última venda</th>
                            <th class="text-end">Dias sem venda</th>
                            <th>Promoção</th>
                            <th>Situação</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($produtos as $produto)

                            <tr>

                                <td>

                                    <div class="fw-semibold">
                                        {{ $produto->nome }}
                                    </div>

                                    @if ($produto->sku)

                                        <div class="small text-muted">
                                            SKU: {{ $produto->sku }}
                                        </div>

                                    @endif

                                </td>


                                <td>
                                    {{
                                        $produto->categoria
                                        ?? 'Sem categoria'
                                    }}
                                </td>


                                <td class="text-end fw-semibold text-danger">

                                    {{
                                        number_format(
                                            $produto->estoque_disponivel,
                                            3,
                                            ',',
                                            '.'
                                        )
                                    }}

                                </td>


                                <td class="text-end">

                                    {{
                                        number_format(
                                            $produto->estoque_minimo,
                                            3,
                                            ',',
                                            '.'
                                        )
                                    }}

                                </td>


                                <td>

                                    @if ($produto->ultima_venda)

                                        {{
                                            \Carbon\Carbon::parse(
                                                $produto->ultima_venda
                                            )->format('d/m/Y')
                                        }}

                                    @else

                                        <span class="text-muted">
                                            Nunca
                                        </span>

                                    @endif

                                </td>


                                <td class="text-end">

                                    @if (
                                        $produto->dias_sem_venda
                                        === null
                                    )

                                        <span class="text-muted">
                                            —
                                        </span>

                                    @else

                                        {{
                                            number_format(
                                                $produto->dias_sem_venda,
                                                0,
                                                ',',
                                                '.'
                                            )
                                        }}

                                    @endif

                                </td>


                                <td>

                                    @if (
                                        (int) $produto->em_promocao
                                        === 1
                                    )

                                        <span class="badge text-bg-success">
                                            Sim
                                        </span>

                                    @else

                                        <span class="badge text-bg-secondary">
                                            Não
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    <span class="badge text-bg-warning">

                                        {{ $produto->situacao }}

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted py-5">

                                    Nenhum produto encontrado
                                    com os filtros informados.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        @if ($produtos->hasPages())

            <div class="card-footer">

                {{
                    $produtos->links()
                }}

            </div>

        @endif

    </div>

</div>

@endsection