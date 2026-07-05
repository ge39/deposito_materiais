@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">
                Atribuir motorista e veículo
            </h1>
            <small class="text-muted">
                Entrega {{ $entrega->codigo_entrega ?? '#' . $entrega->id }}
            </small>
        </div>

        <a href="{{ route('entregas.show', $entrega->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Corrija os campos abaixo.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Equipe da entrega</strong>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('entregas.salvar-equipe', $entrega->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="motorista_id" class="form-label">Motorista</label>
                        <select name="motorista_id" id="motorista_id" class="form-select" required>
                            <option value="">Selecione o motorista</option>

                            @foreach ($motoristas as $motorista)
                                <option value="{{ $motorista->id }}"
                                    @selected(old('motorista_id', $entrega->motorista_id) == $motorista->id)>
                                    {{ $motorista->nome }} — {{ $motorista->telefone ?? 'sem telefone' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="veiculo_id" class="form-label">Veículo</label>
                        <select name="veiculo_id" id="veiculo_id" class="form-select" required>
                            <option value="">Selecione o veículo</option>

                            @foreach ($veiculos as $veiculo)
                                <option value="{{ $veiculo->id }}"
                                    @selected(old('veiculo_id', $entrega->veiculo_id) == $veiculo->id)>
                                    {{ $veiculo->placa }}
                                    {{ $veiculo->modelo ? ' — ' . $veiculo->modelo : '' }}
                                    {{ $veiculo->capacidade_kg ? ' — ' . number_format($veiculo->capacidade_kg, 2, ',', '.') . ' kg' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('entregas.show', $entrega->id) }}" class="btn btn-outline-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Salvar equipe
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection