@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div
        class="d-flex justify-content-between
               align-items-center mb-4"
    >
        <div>
            <h1 class="h3 mb-1">
                Regras de Comissão
            </h1>

            <div class="text-muted">
                Configuração das regras comerciais
                dos vendedores.
            </div>
        </div>

        <a
            href="{{ route('comissoes.regras.create') }}"
            class="btn btn-primary"
        >
            Nova regra
        </a>
    </div>


    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Escopo</th>
                            <th>Referência</th>
                            <th>Percentual</th>
                            <th>Vigência</th>
                            <th>Status</th>
                            <th class="text-end">
                                Ações
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse ($regras as $regra)

                        <tr>

                            <td>
                                {{ $regra->funcionario?->nome ?? '-' }}
                            </td>

                            <td>
                                {{ ucfirst($regra->escopo) }}
                            </td>

                            <td>
                                @if ($regra->escopo === 'produto')
                                    {{ $regra->produto?->nome ?? '-' }}

                                @elseif ($regra->escopo === 'categoria')
                                    {{ $regra->categoria?->nome ?? '-' }}

                                @else
                                    Todos os produtos
                                @endif
                            </td>

                            <td>
                                @if (
                                    $regra->escopo === 'produto'
                                    && $regra->usar_percentual_produto
                                )
                                    Percentual do produto
                                @else
                                    {{
                                        number_format(
                                            (float) $regra->percentual,
                                            2,
                                            ',',
                                            '.'
                                        )
                                    }}%
                                @endif
                            </td>

                            <td>
                                {{
                                    $regra->vigencia_inicio
                                        ? $regra->vigencia_inicio
                                            ->format('d/m/Y')
                                        : 'Sem início'
                                }}

                                até

                                {{
                                    $regra->vigencia_fim
                                        ? $regra->vigencia_fim
                                            ->format('d/m/Y')
                                        : 'Sem fim'
                                }}
                            </td>

                            <td>
                                @if ($regra->ativo)
                                    <span class="badge bg-success">
                                        Ativa
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Inativa
                                    </span>
                                @endif
                            </td>

                            <td class="text-end">

                                <a
                                    href="{{
                                        route(
                                            'comissoes.regras.edit',
                                            $regra
                                        )
                                    }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Editar
                                </a>

                                <form
                                    method="POST"
                                    action="{{
                                        route(
                                            'comissoes.regras.destroy',
                                            $regra
                                        )
                                    }}"
                                    class="d-inline"
                                    onsubmit="
                                        return confirm(
                                            'Deseja excluir/desativar esta regra?'
                                        );
                                    "
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                    >
                                        Excluir
                                    </button>
                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-4 text-muted"
                            >
                                Nenhuma regra de comissão cadastrada.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>


    <div class="mt-3">
        {{ $regras->links() }}
    </div>

</div>

@endsection