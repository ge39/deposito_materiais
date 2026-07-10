<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-activity me-2 text-secondary"></i>
            Status Operacional
        </h6>
        <small class="text-muted">
            Situação atual do veículo para uso na expedição.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label for="status" class="form-label fw-semibold">
                    Status <span class="text-danger">*</span>
                </label>

                @php
                    $statusSelecionado = old('status', $veiculo->status ?? 'Ativo');
                @endphp

                <select
                    name="status"
                    id="status"
                    class="form-select @error('status') is-invalid @enderror"
                    required
                >
                    <option value="Ativo" {{ $statusSelecionado === 'Ativo' ? 'selected' : '' }}>Ativo</option>
                    <option value="Inativo" {{ $statusSelecionado === 'Inativo' ? 'selected' : '' }}>Inativo</option>
                    <option value="Manutenção" {{ $statusSelecionado === 'Manutenção' ? 'selected' : '' }}>Manutenção</option>
                    <option value="Indisponível" {{ $statusSelecionado === 'Indisponível' ? 'selected' : '' }}>Indisponível</option>
                </select>

                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-8">
                <label for="observacao" class="form-label fw-semibold">Observações</label>
                <textarea
                    name="observacao"
                    id="observacao"
                    rows="3"
                    class="form-control @error('observacao') is-invalid @enderror"
                    placeholder="Ex: veículo reservado para entregas pesadas, restrição de acesso, manutenção preventiva..."
                >{{ old('observacao', $veiculo->observacao ?? '') }}</textarea>

                @error('observacao')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>