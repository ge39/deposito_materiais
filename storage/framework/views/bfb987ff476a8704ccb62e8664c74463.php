<style>

    /*
    |--------------------------------------------------------------------------
    | JMF BI DESIGN SYSTEM
    |--------------------------------------------------------------------------
    | Este é o padrão visual oficial dos cards da área BI.
    | Não criar estilos independentes por dashboard.
    |--------------------------------------------------------------------------
    */


    .jmf-bi-card {
        border: 1px solid var(--bs-border-color);
        border-radius: .5rem;
        background-color: var(--bs-body-bg);
        box-shadow: var(--bs-box-shadow-sm);

        font-family:
            var(--bs-body-font-family),
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Helvetica,
            Arial,
            sans-serif;

        transition:
            box-shadow .15s ease,
            transform .15s ease;
    }


    .jmf-bi-card:hover {
        transform: translateY(-1px);
        box-shadow: var(--bs-box-shadow);
    }


    .jmf-bi-card .card-body {
        min-height: 180px;

        display: flex;
        flex-direction: column;

        padding: 1rem;
    }


    /*
    |--------------------------------------------------------------------------
    | CABEÇALHO
    |--------------------------------------------------------------------------
    */

    .jmf-bi-card-header {
        margin-bottom: .75rem;
    }


    .jmf-bi-card-title {
        display: flex;
        align-items: center;
        gap: .55rem;

        font-size: 1rem;
        font-weight: 600;
        line-height: 1.25;

        color: var(--bs-body-color);
    }


    .jmf-bi-card-title i {
        flex: 0 0 auto;
        font-size: 1.05rem;
    }


    /*
    |--------------------------------------------------------------------------
    | VALOR PRINCIPAL
    |--------------------------------------------------------------------------
    */

    .jmf-bi-card-value {
        min-height: 42px;

        display: flex;
        align-items: center;

        margin-bottom: .35rem;

        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.15;

        color: var(--bs-body-color);
    }


    /*
    |--------------------------------------------------------------------------
    | DESCRIÇÃO
    |--------------------------------------------------------------------------
    */

    .jmf-bi-card-description {
        font-size: .9rem;
        line-height: 1.45;

        color: var(--bs-secondary-color);

        margin-bottom: .75rem;
    }


    /*
    |--------------------------------------------------------------------------
    | RODAPÉ
    |--------------------------------------------------------------------------
    */

    .jmf-bi-card-footer {
        margin-top: auto;

        display: flex;
        justify-content: space-between;
        align-items: center;

        gap: .75rem;
    }


    .jmf-bi-card-footer .btn {
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | GRID OFICIAL
    |--------------------------------------------------------------------------
    */

    .jmf-bi-grid {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 1.5rem;
    }


    /*
    |--------------------------------------------------------------------------
    | MODAIS
    |--------------------------------------------------------------------------
    */

    .jmf-bi-modal .modal-header {
        background-color: var(--bs-primary);
        color: var(--bs-white);
    }


    .jmf-bi-modal .modal-header .btn-close {
        filter: var(--bs-btn-close-white-filter);
    }


    .jmf-bi-modal .table {
        margin-bottom: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVO
    |--------------------------------------------------------------------------
    */

    @media (max-width: 767.98px) {

        .jmf-bi-card .card-body {
            min-height: 165px;
        }

        .jmf-bi-card-value {
            font-size: 1.3rem;
        }

        .jmf-bi-card-footer {
            align-items: flex-end;
        }

    }

</style><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views\bi\partials\styles.blade.php ENDPATH**/ ?>