<?php $__env->startSection('content'); ?>

<style>

    /*
    |--------------------------------------------------------------------------
    | HOME INSTITUCIONAL FULL VIEWPORT
    |--------------------------------------------------------------------------
    */

    html,
    body {
        width: 100%;
        height: 100%;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    /*
    |--------------------------------------------------------------------------
    | HOME ESCAPA COMPLETAMENTE DO CONTAINER DO LAYOUT
    |--------------------------------------------------------------------------
    */

    .home-institucional-fullscreen {
        position: fixed;

        left: 0;
        right: 0;
        bottom: 0;

        width: 100vw;
        max-width: none !important;

        margin: 0 !important;
        padding: 0 !important;

        overflow: hidden;

        background: #000;
        z-index: 1;
    }

    /*
    |--------------------------------------------------------------------------
    | IMAGEM
    |--------------------------------------------------------------------------
    */

    .home-institucional-fullscreen img {
        position: absolute;

        top: 0;
        left: 0;

        width: 100%;
        height: 100%;

        max-width: none !important;

        margin: 0 !important;
        padding: 0 !important;

        display: block;

        border: 0 !important;
        border-radius: 0 !important;

        box-shadow: none !important;

        object-fit: cover;
        object-position: center center;
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE FOOTER SOMENTE DA HOME
    |--------------------------------------------------------------------------
    */

    footer {
        display: none !important;
    }

</style>


<div
    id="homeInstitucional"
    class="home-institucional-fullscreen"
>
    <img
        src="<?php echo e(asset('images/deposito-materiais-erp-home.png')); ?>"
        alt="Depósito Materiais ERP"
    >
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const hero = document.getElementById('homeInstitucional');

    function ajustarHome() {

        /*
         * Localiza a navbar real do sistema.
         */
        const navbar =
            document.querySelector('nav.navbar')
            || document.querySelector('.navbar')
            || document.querySelector('header');

        let topo = 0;

        if (navbar) {

            const rect = navbar.getBoundingClientRect();

            topo = Math.max(
                0,
                Math.round(rect.bottom)
            );
        }

        /*
         * Hero começa exatamente abaixo do menu
         * e termina no final do viewport.
         */
        hero.style.top = topo + 'px';
    }

    ajustarHome();

    window.addEventListener(
        'resize',
        ajustarHome
    );

});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\deposito_materiais\resources\views/dashboard/index.blade.php ENDPATH**/ ?>