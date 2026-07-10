<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-rulers me-2 text-info"></i>
            Dimensões do Veículo
        </h6>
        <small class="text-muted">
            Medidas úteis para carga, acesso, altura máxima e futuras restrições logísticas.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label for="comprimento_m" class="form-label fw-semibold">Comprimento (m)</label>
                <input type="number" step="0.01" min="0" name="comprimento_m" id="comprimento_m"
                       class="form-control @error('comprimento_m') is-invalid @enderror"
                       value="{{ old('comprimento_m', $veiculo->comprimento_m ?? '') }}"
                       placeholder="Ex: 6.50">
                @error('comprimento_m')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="largura_m" class="form-label fw-semibold">Largura (m)</label>
                <input type="number" step="0.01" min="0" name="largura_m" id="largura_m"
                       class="form-control @error('largura_m') is-invalid @enderror"
                       value="{{ old('largura_m', $veiculo->largura_m ?? '') }}"
                       placeholder="Ex: 2.20">
                @error('largura_m')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="altura_m" class="form-label fw-semibold">Altura (m)</label>
                <input type="number" step="0.01" min="0" name="altura_m" id="altura_m"
                       class="form-control @error('altura_m') is-invalid @enderror"
                       value="{{ old('altura_m', $veiculo->altura_m ?? '') }}"
                       placeholder="Ex: 3.10">
                @error('altura_m')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>