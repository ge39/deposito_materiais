@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Unidades de Medida</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('unidades.create') }}" class="btn btn-primary mb-3">Nova Unidade</a>
    <a href="{{ route('unidades.inativos') }}" class="btn btn-secondary mb-3">Unidades Desativadas</a>

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
                    <a href="{{ route('unidades.edit', $unidade->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('unidades.destroy', $unidade->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Deseja desativar esta unidade?')">Desativar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">Nenhuma unidade encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $unidades->links() }}
</div>
@endsection
