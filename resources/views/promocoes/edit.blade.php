@extends('layouts.app')

@section('content')
<div class="container">

    <!-- {{-- Mensagens contextuais dinâmicas --}}
    @php
        $alertType = 'success'; // padrão
        $message = session('success') ?? session('popup_message') ?? null;

        if (session('success') && str_contains(session('success'), 'excluída')) {
            $alertType = 'danger';
        } elseif (session('success') && str_contains(session('success'), 'encerrada')) {
            $alertType = 'warning';
        } elseif (session('popup_message')) {
            $alertType = 'info';
        }
    @endphp

    @if($message)
        <div class="alert alert-{{ $alertType }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif -->



    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Editar Promoção</h4>

        {{-- Botão para encerrar promoção --}}
        <form action="{{ route('promocoes.encerrar', $promocao->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja encerrar esta promoção?')">
            @csrf
            @method('PATCH')

            <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-x-circle"></i> Encerrar Promoção
            </button>
        </form>
    </div>


        <div class="card-body">

            <form action="{{ route('promocoes.update', $promocao->id) }}" method="POST">
                @csrf
                @method('PUT')

                <h5 class="mb-3">Dados do Produto - ID {{$promocao->produto_id}}</h5>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Produto </label>
                        <input type="text" class="form-control" value="{{ $promocao->produto->nome }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Marca</label>
                        <input type="text" class="form-control" value="{{ $promocao->produto->marca->nome }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Unidade</label>
                        <input type="text" class="form-control" value="{{ $promocao->produto->unidadeMedida->nome }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Fornecedor</label>
                        <input type="text" class="form-control" value="{{  $promocao->produto->fornecedor->nome ?? '---' }}" readonly>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Descrição</label>
                        <textarea class="form-control" rows="2" readonly>{{ $promocao->produto->descricao }}</textarea>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Dados da Promoção</h5>

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label>Preço Original</label>
                        <input type="text" name="preco_original" class="form-control" value="{{ $promocao->preco_original }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Desconto (%)</label>
                        <input type="number" name="desconto_percentual" class="form-control" min="{{ $promocao->desconto_percentual }}" value="{{ $promocao->desconto_percentual }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Preço Promocional</label>
                        <input type="text" name="preco_promocional" class="form-control" value="{{ $promocao->preco_promocional }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Status</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="status" id="status1" value="1" autocomplete="off" {{ $promocao->status == 1 ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="status1">Ativo</label>

                            <!-- <input type="radio" class="btn-check" name="status" id="status0" value="0" autocomplete="off" {{ $promocao->status == 0 ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="status0">Inativo</label> -->
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Data Início</label>
                        <input type="date" name="promocao_inicio" class="form-control" value="{{ $promocao->promocao_inicio }}" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Data Fim</label>
                        <input type="date" name="promocao_fim" class="form-control" value="{{ $promocao->promocao_fim }}" min="{{ $promocao->promocao_fim }}">
                    </div>

                </div>

                
                <div>
                  <button type="submit"class="btn btn-success mt-3" > Salvar Alterações </button>
                 <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Voltar</a>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    const precoOriginal = parseFloat('{{ $promocao->preco_original }}');
    const descontoInput = document.querySelector('[name="desconto_percentual"]');
    const precoPromocionalInput = document.querySelector('[name="preco_promocional"]');

    descontoInput.addEventListener('input', function() {
        let descontoAtual = parseFloat(this.value);
        if(descontoAtual < {{ $promocao->desconto_percentual }}) {
            descontoAtual = {{ $promocao->desconto_percentual }};
            this.value = descontoAtual;
        }
        const precoPromocional = precoOriginal - (precoOriginal * descontoAtual / 100);
        precoPromocionalInput.value = precoPromocional.toFixed(2);
    });
</script>

@endsection
