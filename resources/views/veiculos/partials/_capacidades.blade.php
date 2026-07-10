<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-box-seam me-2 text-success"></i>
            Capacidades de Carga
        </h6>
        <small class="text-muted">
            Dados usados pela expedição na montagem dos romaneios.
        </small>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-3">
                <label for="capacidade_kg" class="form-label fw-semibold">Capacidade KG</label>
                <input type="number" step="0.01" min="0" name="capacidade_kg" id="capacidade_kg"
                       class="form-control @error('capacidade_kg') is-invalid @enderror"
                       value="{{ old('capacidade_kg', $veiculo->capacidade_kg ?? '') }}"
                       placeholder="Ex: 3500">
                @error('capacidade_kg')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="capacidade_m3" class="form-label fw-semibold">Capacidade m³</label>
                <input type="number" step="0.01" min="0" name="capacidade_m3" id="capacidade_m3"
                       class="form-control @error('capacidade_m3') is-invalid @enderror"
                       value="{{ old('capacidade_m3', $veiculo->capacidade_m3 ?? '') }}"
                       placeholder="Ex: 18">
                @error('capacidade_m3')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="capacidade_unidades" class="form-label fw-semibold">Capacidade unidades</label>
                <input type="number" min="0" name="capacidade_unidades" id="capacidade_unidades"
                       class="form-control @error('capacidade_unidades') is-invalid @enderror"
                       value="{{ old('capacidade_unidades', $veiculo->capacidade_unidades ?? '') }}"
                       placeholder="Ex: 500">
                @error('capacidade_unidades')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="capacidade_paletes" class="form-label fw-semibold">Capacidade paletes</label>
                <input type="number" min="0" name="capacidade_paletes" id="capacidade_paletes"
                       class="form-control @error('capacidade_paletes') is-invalid @enderror"
                       value="{{ old('capacidade_paletes', $veiculo->capacidade_paletes ?? '') }}"
                       placeholder="Ex: 8">
                @error('capacidade_paletes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>
</div>