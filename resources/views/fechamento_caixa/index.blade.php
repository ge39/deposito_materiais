@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Fechamento / Auditoria de Caixa #{{ $caixa->id }}</h3>

    {{-- =======================
        CARDS DE RESUMO
    ======================== --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card p-2">
                <strong>Abertura:</strong> R$ {{ number_format($caixa->valor_abertura, 2, ',', '.') }}<br>
                <strong>Fundo de Troco:</strong> R$ {{ number_format($caixa->fundo_troco, 2, ',', '.') }}<br>
                <strong>Data Abertura:</strong> {{ $caixa->data_abertura->format('d/m/Y H:i') }}<br>
                <strong>Status:</strong> {{ ucfirst($caixa->status) }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <strong>Total Entradas:</strong> R$ {{ number_format($total_entradas, 2, ',', '.') }}<br>
                <strong>Total Saídas:</strong> R$ {{ number_format($total_saidas, 2, ',', '.') }}<br>
                <strong>Total Esperado:</strong> R$ {{ number_format($total_esperado, 2, ',', '.') }}<br>
                <strong>Divergência:</strong> 
                <span class="{{ $divergencia != 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                    R$ {{ number_format($divergencia, 2, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-2">
                <strong>Totais por Forma de Pagamento (Sistema):</strong>
                <ul class="list-unstyled mb-0">
                    @foreach(['dinheiro','pix','carteira','cartao_debito','cartao_credito'] as $forma)
                        <li>{{ ucfirst(str_replace('_',' ',$forma)) }}: 
                            R$ {{ number_format($totaisPorForma[$forma] ?? 0, 2, ',', '.') }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- =======================
        FORMULÁRIO DE FECHAMENTO
    ======================== --}}
    @if($caixa->estaAberto() && auth()->user()->can('fechar-caixa'))
    <form method="POST" action="{{ route('fechamento.fechar', $caixa->id) }}">
        @csrf
        <h5>Valores Físicos Conferidos</h5>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="dinheiro" class="form-label">Dinheiro</label>
                <input type="text" class="form-control" name="dinheiro" id="dinheiro" 
                       value="{{ number_format($totaisPorForma['dinheiro'] ?? 0, 2, ',', '.') }}">
            </div>
            <div class="col-md-4">
                <label for="pix" class="form-label">Pix</label>
                <input type="text" class="form-control" name="pix" id="pix" 
                       value="{{ number_format($totaisPorForma['pix'] ?? 0, 2, ',', '.') }}">
            </div>
            <div class="col-md-4">
                <label for="carteira" class="form-label">Carteira</label>
                <input type="text" class="form-control" name="carteira" id="carteira" 
                       value="{{ number_format($totaisPorForma['carteira'] ?? 0, 2, ',', '.') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="cartao_debito" class="form-label">Cartão Débito</label>
                <input type="text" class="form-control" name="cartao_debito" id="cartao_debito" 
                       value="{{ number_format($totaisPorForma['cartao_debito'] ?? 0, 2, ',', '.') }}">
            </div>
            <div class="col-md-6">
                <label for="cartao_credito" class="form-label">Cartão Crédito</label>
                <input type="text" class="form-control" name="cartao_credito" id="cartao_credito" 
                       value="{{ number_format($totaisPorForma['cartao_credito'] ?? 0, 2, ',', '.') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Fechar Caixa</button>
    </form>
    @endif

    {{-- =======================
        TABELA DE MOVIMENTAÇÕES
    ======================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <h5>Movimentações do Caixa</h5>
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Origem</th>
                        <th>Data</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($caixa->movimentacoes as $mov)
                    <tr>
                        <td>{{ $mov->id }}</td>
                        <td>{{ ucfirst($mov->tipo) }}</td>
                        <td>R$ {{ number_format($mov->valor, 2, ',', '.') }}</td>
                        <td>{{ $mov->origem_id ?? '-' }}</td>
                        <td>{{ $mov->data_movimentacao->format('d/m/Y H:i') }}</td>
                        <td>{{ $mov->observacao ?? '-' }}</td>
                    </tr>
                    
                    @endforeach
                </tbody>
                
            </table>
                <div class="mt-3">
                    <a href="{{ route('fechamento.lancar_valores', $caixa->id) }}"
                    class="btn btn-primary btn-sm">
                        Lançamento de Valores Manuais
                    </a>
                </div>

        </div>
    </div>

</div>
@endsection
