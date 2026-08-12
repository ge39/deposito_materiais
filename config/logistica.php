<?php

return [
    'deposito' => [
        /*
         * Nome e endereço são obtidos da primeira empresa com ativo = 1.
         * Estes valores servem apenas como fallback quando não existir
         * uma empresa ativa cadastrada.
         */
        'nome' => env(
            'LOGISTICA_DEPOSITO_NOME',
            env('APP_NAME', 'Empresa')
        ),

        'endereco' => env(
            'LOGISTICA_DEPOSITO_ENDERECO',
            'Endereço do estabelecimento não configurado'
        ),

        /*
         * A tabela empresas não possui latitude e longitude.
         * Portanto, a posição fixa do estabelecimento permanece no .env.
         */
        'latitude' => (float) env(
            'LOGISTICA_DEPOSITO_LATITUDE',
            config('openstreetmap.center.lat')
        ),

        'longitude' => (float) env(
            'LOGISTICA_DEPOSITO_LONGITUDE',
            config('openstreetmap.center.lng')
        ),

        'zoom' => (int) env(
            'LOGISTICA_DEPOSITO_ZOOM',
            16
        ),

        'raio_patio_metros' => (int) env(
            'LOGISTICA_RAIO_PATIO_METROS',
            18
        ),
    ],
];