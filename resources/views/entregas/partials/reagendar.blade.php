@php
    $statusFinaisReagendamento = [
        'Entregue',
        'Entregue_finalizada_com_ocorrencia',
        'Devolvida',
        'Cancelada',
    ];

    $podeReagendar = ! in_array(
        $entrega->status,
        $statusFinaisReagendamento,
        true
    );

    $dataAtualEntrega =
        $entrega->data_prevista_entrega
        ?? $entrega->data_prevista;

    $dataAtualInput = $dataAtualEntrega
        ? \Carbon\Carbon::parse($dataAtualEntrega)->format('Y-m-d')
        : '';
@endphp

@if ($podeReagendar)
    <div class="card mt-3">
        <div class="card-header">
            <strong>Reagendar entrega</strong>
        </div>

        <div class="card-body">
            <form
                method="POST"
                action="{{ route('entregas.reagendar', $entrega) }}"
            >
                @csrf
                @method('PATCH')

                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label
                            for="data_prevista_entrega"
                            class="form-label"
                        >
                            Nova data de entrega
                        </label>

                        <input
                            type="date"
                            class="form-control @error('data_prevista_entrega') is-invalid @enderror"
                            id="data_prevista_entrega"
                            name="data_prevista_entrega"
                            value="{{ old('data_prevista_entrega', $dataAtualInput) }}"
                            required
                        >

                        @error('data_prevista_entrega')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label
                            for="motivo_reagendamento"
                            class="form-label"
                        >
                            Justificativa
                        </label>

                        <textarea
                            class="form-control @error('motivo_reagendamento') is-invalid @enderror"
                            id="motivo_reagendamento"
                            name="motivo_reagendamento"
                            rows="2"
                            required
                        >{{ old('motivo_reagendamento') }}</textarea>

                        @error('motivo_reagendamento')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Alterar data
                        </button>
                    </div>
                </div>

                <div class="mt-2 text-muted">
                    Data atual:
                    <strong>
                        {{ $dataAtualEntrega
                            ? \Carbon\Carbon::parse($dataAtualEntrega)->format('d/m/Y')
                            : '-' }}
                    </strong>
                </div>
            </form>
        </div>
    </div>
@endif