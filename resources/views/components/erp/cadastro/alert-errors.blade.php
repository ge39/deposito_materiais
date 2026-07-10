@if ($errors->any())
    <div
        class="erp-validation-alert"
        role="alert"
    >
        <h2 class="erp-validation-alert-title">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Verifique os campos informados
        </h2>

        <ul class="erp-validation-alert-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif