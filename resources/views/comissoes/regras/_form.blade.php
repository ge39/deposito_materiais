@php
    $regraAtual = $regra ?? null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Não foi possível salvar.</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">

        <div class="row g-3">

            <div class="col-md-6">
                <label
                    for="funcionario_id"
                    class="form-label"
                >
                    Vendedor
                </label>

                <select
                    name="funcionario_id"
                    id="funcionario_id"
                    class="form-select"
                    required
                >
                    <option value="">
                        Selecione...
                    </option>

                    @foreach ($vendedores as $vendedor)
                        <option
                            value="{{ $vendedor->id }}"
                            @selected(
                                old(
                                    'funcionario_id',
                                    $regraAtual?->funcionario_id
                                ) == $vendedor->id
                            )
                        >
                            {{ $vendedor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div class="col-md-3">
                <label
                    for="escopo"
                    class="form-label"
                >
                    Escopo
                </label>

                <select
                    name="escopo"
                    id="escopo"
                    class="form-select"
                    required
                >
                    @foreach ([
                        'geral' => 'Geral',
                        'categoria' => 'Categoria',
                        'produto' => 'Produto',
                    ] as $valor => $rotulo)

                        <option
                            value="{{ $valor }}"
                            @selected(
                                old(
                                    'escopo',
                                    $regraAtual?->escopo ?? 'geral'
                                ) === $valor
                            )
                        >
                            {{ $rotulo }}
                        </option>

                    @endforeach
                </select>
            </div>


            <div class="col-md-3">
                <label
                    for="percentual"
                    class="form-label"
                >
                    Comissão (%)
                </label>

                <input
                    type="number"
                    name="percentual"
                    id="percentual"
                    class="form-control"
                    step="0.0001"
                    min="0"
                    max="100"
                    value="{{ old(
                        'percentual',
                        $regraAtual?->percentual
                    ) }}"
                >
            </div>


            <div
                class="col-md-6"
                id="box_categoria"
            >
                <label
                    for="categoria_id"
                    class="form-label"
                >
                    Categoria
                </label>

                <select
                    name="categoria_id"
                    id="categoria_id"
                    class="form-select"
                >
                    <option value="">
                        Selecione...
                    </option>

                    @foreach ($categorias as $categoria)
                        <option
                            value="{{ $categoria->id }}"
                            @selected(
                                old(
                                    'categoria_id',
                                    $regraAtual?->categoria_id
                                ) == $categoria->id
                            )
                        >
                            {{ $categoria->nome }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div
                class="col-md-6"
                id="box_produto"
            >
                <label
                    for="produto_id"
                    class="form-label"
                >
                    Produto
                </label>

                <select
                    name="produto_id"
                    id="produto_id"
                    class="form-select"
                >
                    <option value="">
                        Selecione...
                    </option>

                    @foreach ($produtos as $produto)
                        <option
                            value="{{ $produto->id }}"
                            @selected(
                                old(
                                    'produto_id',
                                    $regraAtual?->produto_id
                                ) == $produto->id
                            )
                        >
                            {{ $produto->nome }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div
                class="col-12"
                id="box_percentual_produto"
            >
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="usar_percentual_produto"
                        id="usar_percentual_produto"
                        value="1"
                        class="form-check-input"
                        @checked(
                            old(
                                'usar_percentual_produto',
                                $regraAtual?->usar_percentual_produto
                            )
                        )
                    >

                    <label
                        class="form-check-label"
                        for="usar_percentual_produto"
                    >
                        Usar percentual cadastrado no produto
                    </label>
                </div>
            </div>


            <div class="col-md-3">
                <label
                    for="vigencia_inicio"
                    class="form-label"
                >
                    Início da vigência
                </label>

                <input
                    type="date"
                    name="vigencia_inicio"
                    id="vigencia_inicio"
                    class="form-control"
                    value="{{ old(
                        'vigencia_inicio',
                        $regraAtual?->vigencia_inicio?->format('Y-m-d')
                    ) }}"
                >
            </div>


            <div class="col-md-3">
                <label
                    for="vigencia_fim"
                    class="form-label"
                >
                    Fim da vigência
                </label>

                <input
                    type="date"
                    name="vigencia_fim"
                    id="vigencia_fim"
                    class="form-control"
                    value="{{ old(
                        'vigencia_fim',
                        $regraAtual?->vigencia_fim?->format('Y-m-d')
                    ) }}"
                >
            </div>


            <div class="col-md-6">
                <label
                    for="observacao"
                    class="form-label"
                >
                    Observação
                </label>

                <input
                    type="text"
                    name="observacao"
                    id="observacao"
                    maxlength="500"
                    class="form-control"
                    value="{{ old(
                        'observacao',
                        $regraAtual?->observacao
                    ) }}"
                >
            </div>


            <div class="col-12">
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="ativo"
                        id="ativo"
                        value="1"
                        class="form-check-input"
                        @checked(
                            old(
                                'ativo',
                                $regraAtual?->ativo ?? true
                            )
                        )
                    >

                    <label
                        class="form-check-label"
                        for="ativo"
                    >
                        Regra ativa
                    </label>
                </div>
            </div>

        </div>
    </div>
</div>


<div class="mt-3 d-flex gap-2">

    <button
        type="submit"
        class="btn btn-primary"
    >
        Salvar
    </button>

    <a
        href="{{ route('comissoes.regras.index') }}"
        class="btn btn-secondary"
    >
        Voltar
    </a>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const escopo = document.getElementById('escopo');

    const categoria =
        document.getElementById('box_categoria');

    const produto =
        document.getElementById('box_produto');

    const percentualProduto =
        document.getElementById('box_percentual_produto');

    const checkboxProduto =
        document.getElementById('usar_percentual_produto');

    const percentual =
        document.getElementById('percentual');


    function atualizarTela() {

        const valor = escopo.value;

        categoria.style.display =
            valor === 'categoria'
                ? ''
                : 'none';

        produto.style.display =
            valor === 'produto'
                ? ''
                : 'none';

        percentualProduto.style.display =
            valor === 'produto'
                ? ''
                : 'none';

        atualizarPercentual();
    }


    function atualizarPercentual() {

        const usarProduto =
            escopo.value === 'produto'
            && checkboxProduto.checked;

        percentual.disabled = usarProduto;

        if (usarProduto) {
            percentual.value = '';
        }
    }


    escopo.addEventListener(
        'change',
        atualizarTela
    );

    checkboxProduto.addEventListener(
        'change',
        atualizarPercentual
    );

    atualizarTela();
});
</script>