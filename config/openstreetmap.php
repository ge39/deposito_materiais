<?php

return [
    'tile_url' => env(
        'OPENSTREETMAP_TILE_URL',
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
    ),

    'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors',

    'geocoding_url' => env(
        'OPENSTREETMAP_GEOCODING_URL',
        'https://nominatim.openstreetmap.org/search'
    ),

    'user_agent' => env(
        'OPENSTREETMAP_USER_AGENT',
        config('app.name', 'deposito_materiais') . '/1.0'
    ),

    'center' => [
        'lat' => (float) env(
            'OPENSTREETMAP_CENTER_LAT',
            -23.54417
        ),
        'lng' => (float) env(
            'OPENSTREETMAP_CENTER_LNG',
            -46.31157
        ),
    ],

    'zoom' => (int) env(
        'OPENSTREETMAP_ZOOM',
        11
    ),

    'max_zoom' => (int) env(
        'OPENSTREETMAP_MAX_ZOOM',
        19
    ),

    'geocoding_interval_ms' => (int) env(
        'OPENSTREETMAP_GEOCODING_INTERVAL_MS',
        1100
    ),

    'geocoding_cache_days' => (int) env(
        'OPENSTREETMAP_GEOCODING_CACHE_DAYS',
        30
    ),
];