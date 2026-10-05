@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h1 class="h3 mb-1">
                {{ $titulo }}
            </h1>

            <p class="text-muted mb-0">
                {{ $subtitulo }}
            </p>
        </div>

        <a
            href="{{ route(
                'bi.produtos.index',
                $filtros
            ) }}"
            class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Produtos & Estoque
        </a>

    </div>

    <div class="alert alert-light border">
        <strong>
            {{
                number_format(
                    $produtos->count(),
                    0,
                    ',',
                    '.'
                )
            }}
        </strong>
        produto(s) encontrado(s).
    </div>

    <div class="card shadow-sm">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Fornecedor</th>
                        <th>Marca</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Reservado</th>
                        <th class="text-end">Disponível</th>
                        <th class="text-end">Mínimo</th>
                        <th class="text-end">Vendido período</th>
                        <th class="text-end">Faturamento</th>
                        <th class="text-end">Valor estoque</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse ($produtos as $produto)

                        <tr>

                            <td>
                                <strong>
                                    {{ $produto->nome }}
                                </strong>
                            </td>

                            <td>
                                {{ $produto->categoria ?? '—' }}
                            </td>

                            <td>
                                {{ $produto->fornecedor ?? '—' }}
                            </td>

                            <td>
                                {{ $produto->marca ?? '—' }}
                            </td>

                            <td class="text-end">
                                {{
                                    number_format(
                                        $produto->estoque_total,
                                        3,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td class="text-end">
                                {{
                                    number_format(
                                        $produto->estoque_reservado,
                                        3,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td class="text-end fw-semibold">
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

                            <td class="text-end">
                                {{
                                    number_format(
                                        $produto->quantidade_vendida,
                                        3,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td class="text-end">
                                R$
                                {{
                                    number_format(
                                        $produto->faturamento,
                                        2,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                            <td class="text-end">
                                R$
                                {{
                                    number_format(
                                        $produto->valor_estoque,
                                        2,
                                        ',',
                                        '.'
                                    )
                                }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="11"
                                class="text-center text-muted py-5">

                                Nenhum produto encontrado.

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection