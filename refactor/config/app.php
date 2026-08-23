<?php declare(strict_types=1);

return [
    'app' => [
        'name' => 'Webkernel App',
        'debug' => false,
        'timezone' => 'UTC',
        'env' => 'prod',
    ],
    'database' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'webkernel',
        'username' => 'root',
        'password' => '',
    ],
    'cache' => [
        'driver' => 'apcu',
        'fallback' => 'redis',
    ],
    'logging' => [
        'level' => 'info',
        'channel' => 'single',
    ],
];
