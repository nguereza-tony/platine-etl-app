<?php

    return [
        'default' => env('PL_DB_DRIVER', 'mysql'),

        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'database' => env('PL_DB_NAME', 'db_etl'),
                'hostname' => env('PL_DB_HOST', '127.0.0.1'),
                'port' => env('PL_DB_PORT', 3307, 'int'),
                'username' => env('PL_DB_USER', 'root'),
                'password' => env('PL_DB_PASSWORD', ''),
                'persistent' => false,
            ]
        ],

        'migration' => [
            'table' => 'migration',
            'path' => env('PL_MIGRATION_PATH', __DIR__ . '/../storage/migrations'),
            'seed_path' => env('PL_SEED_PATH', __DIR__ . '/../storage/migrations/seeds'),
        ]

    ];
