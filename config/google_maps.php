<?php

return [
    'browser_key' => env(
        'GOOGLE_MAPS_BROWSER_KEY'
    ),

    'map_id' => env(
        'GOOGLE_MAPS_MAP_ID'
    ) ?: 'DEMO_MAP_ID',

    'center' => [
        'lat' => (float) env(
            'GOOGLE_MAPS_CENTER_LAT',
            -23.54417
        ),
        'lng' => (float) env(
            'GOOGLE_MAPS_CENTER_LNG',
            -46.31157
        ),
    ],

    'zoom' => (int) env(
        'GOOGLE_MAPS_ZOOM',
        11
    ),

    'language' => env(
        'GOOGLE_MAPS_LANGUAGE',
        'pt-BR'
    ),

    'region' => env(
        'GOOGLE_MAPS_REGION',
        'BR'
    ),
];