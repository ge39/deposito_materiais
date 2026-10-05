@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">Produtos do Fornecedor</h2>
            <div class="text-muted">
                {{ $fornecedor->nome }}
            </div>
        </div>

        <a href="{{ route('fornecedores.index') }}"
           class="btn btn-outline-secondary">
            Voltar
        </a>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">
                        Produtos cadastrados
                    </div>

                    <div class="fs-3 fw-bold">
                        {{ $totalProdutos }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">
                        Produtos ativos
                    </div>

                    <div class="fs-3 fw-bold">
                        {{ $produtosAtivos }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">

            <label for="buscaProduto"
                   class="form-label fw-semibold">
                Buscar produto
            </label>

            <input
                type="text"
                id="buscaProduto"
                class="form-control"
                placeholder="Nome ou categoria..."
                autocomplete="off"
            >

        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            @if($produtos->isEmpty())

                <div class="text-center text-muted py-4">
                    Nenhum produto cadastrado para este fornecedor.
                </div>

            @else

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody id="listaProdutos">

                            @foreach($produtos as $produto)

                                <tr
                                    class="produto-item"
                                    data-busca="{{ mb_strtolower(
                                        ($produto->nome ?? '') . ' ' .
                                        ($produto->categoria->nome ?? '')
                                    ) }}"
                                >
                                    <td class="fw-semibold">
                                        {{ $produto->nome }}
                                    </td>

                                    <td>
                                        {{ $produto->categoria->nome ?? '—' }}
                                    </td>

                                    <td>
                                        @if($produto->ativo)
                                            <span class="badge bg-success">
                                                Ativo
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                Inativo
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>
                </div>

            @endif

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const busca = document.getElementById('buscaProduto');

    if (!busca) {
        return;
    }

    busca.addEventListener('input', function () {

        const termo = this.value
            .toLowerCase()
            .trim();

        document
            .querySelectorAll('.produto-item')
            .forEach(function (linha) {

                const texto =
                    linha.dataset.busca || '';

                linha.style.display =
                    texto.includes(termo)
                        ? ''
                        : 'none';
            });
    });
});
</script>
@endsection