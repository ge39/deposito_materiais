@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Unidades de Medida Desativadas</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('unidades.index') }}" class="btn btn-secondary mb-3">Voltar</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Sigla</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unidades as $unidade)
            <tr>
                <td>{{ $unidade->nome }}</td>
                <td>{{ $unidade->sigla }}</td>
                <td>
                    <form action="{{ route('unidades.reativar', $unidade->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('PUT')
                        <button class="btn btn-sm btn-success" onclick="return confirm('Deseja reativar esta unidade?')">Reativar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">Nenhuma unidade desativada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $unidades->links() }}
</div>
@endsection
