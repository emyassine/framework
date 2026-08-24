<?php declare(strict_types=1);

return [
    'app' => [
        'debug' => false,
        'env' => 'prod',
    ],
    'cache' => [
        'driver' => 'apcu',
        'fallback' => 'redis',
    ],
    'logging' => [
        'level' => 'warning',
    ],
];
