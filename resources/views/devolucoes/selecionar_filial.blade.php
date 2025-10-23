@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Gerar Cupom de Devolução</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('devolucao.gerar_cupom') }}" method="GET">
        <div class="mb-3">
            <label>Selecione a Filial / Empresa</label>
            <select name="empresa_id" class="form-control" required>
                <option value="">-- Selecione --</option>
                @foreach($empresas as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nome }} - {{ $empresa->cidade }}/{{ $empresa->estado }}</option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="devolucao_id" value="{{ $devolucao->id }}">

        <button type="submit" class="btn btn-success">Gerar Cupom PDF</button>
    </form>
</div>
@endsection
