<?php
    $dataPath = __DIR__ . '/../storage/data/';
    return [
        'data_path' => env('PL_PLATFORM_DATA_DIR', $dataPath),
        'data_temp_path' => env('PL_PLATFORM_DATA_TEMP_DIR', $dataPath . 'tmp'),
        'data_export_path' => env('PL_PLATFORM_DATA_EXPORT_DIR', $dataPath . 'export'),
        'data_attachment_path' => env('PL_PLATFORM_DATA_ATTACHMENT_DIR', $dataPath . 'attachments'),
    ];
