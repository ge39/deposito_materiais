@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Backup Automático</h3>
            <p class="text-muted mb-0">
                Configure até cinco execuções diárias no servidor.
            </p>
        </div>
        <a href="{{ route('backups.index') }}"
           class="btn btn-outline-secondary">
            Voltar ao Backup Manual
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <strong>Programação diária</strong>
        </div>

        <div class="card-body">
            <form method="POST"
                  action="{{ route('backups.automatico.salvar') }}">
                @csrf

                <input type="hidden" name="ativo" value="0">

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input"
                           type="checkbox"
                           id="backupAtivo"
                           name="ativo"
                           value="1"
                           @checked(old('ativo', $configuracao['ativo']))>

                    <label class="form-check-label" for="backupAtivo">
                        Habilitar backup automático
                    </label>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="quantidade" class="form-label">
                            Quantidade de backups por dia
                        </label>

                        <select class="form-select"
                                name="quantidade"
                                id="quantidade">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}"
                                    @selected((int) old('quantidade', $configuracao['quantidade']) === $i)>
                                    {{ $i }} {{ $i === 1 ? 'backup' : 'backups' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <h6 class="mb-3">Horários das execuções</h6>

                <div class="row g-3">
                    @for($i = 0; $i < 5; $i++)
                        <div class="col-md-4 horario-box"
                             data-indice="{{ $i }}">
                            <label class="form-label">
                                Backup {{ $i + 1 }}
                            </label>

                            <input type="time"
                                   class="form-control horario-input"
                                   name="horarios[]"
                                   value="{{ old('horarios.'.$i, $horarios[$i] ?? '') }}">
                        </div>
                    @endfor
                </div>

                <div class="alert alert-light border mt-4">
                    Cada backup será executado no horário informado.
                    Não é permitido repetir horários.
                </div>

                <button type="submit" class="btn btn-primary">
                    Salvar agendamento
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Horários programados</strong>
        </div>

        <div class="card-body">
            @forelse($horarios as $horario)
                <span class="badge bg-primary me-2 mb-2 p-2">
                    {{ $horario }}
                </span>
            @empty
                <span class="text-muted">Nenhum horário configurado.</span>
            @endforelse

            <div class="text-muted small mt-3">
                O servidor deve executar o Laravel Scheduler.
                Esta página configura os horários, não inicia o agendador.
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantidade = document.getElementById('quantidade');
    const caixas = document.querySelectorAll('.horario-box');

    function atualizar() {
        const limite = Number(quantidade.value);

        caixas.forEach(function (caixa, indice) {
            const ativo = indice < limite;
            const input = caixa.querySelector('input');

            caixa.classList.toggle('d-none', !ativo);
            input.disabled = !ativo;
            input.required = ativo;
        });
    }

    quantidade.addEventListener('change', atualizar);
    atualizar();
});
</script>
@endsection