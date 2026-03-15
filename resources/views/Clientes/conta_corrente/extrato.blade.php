@extends('layouts.app')

@section('content')
<div class="container">

    <h3>Extrato - {{ $cliente->nome }}</h3>

    <div class="mb-3">
        <strong>Saldo Atual:</strong> 
        R$ {{ number_format($saldo, 2, ',', '.') }}
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Origem</th>
                <th>Valor</th>
                <th>Saldo Após</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimentacoes as $mov)
            <tr>
                <td>{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst($mov->tipo) }}</td>
                <td>{{ ucfirst($mov->origem) }}</td>
                <td>R$ {{ number_format($mov->valor, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($mov->saldo_apos, 2, ',', '.') }}</td>
                <td>{{ $mov->descricao }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $movimentacoes->links() }}

</div>
@endsection