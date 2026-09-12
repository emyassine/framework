<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

return [
    'name' => 'Webkernel',
    'locale' => 'en',
    'debug' => false,
    'timezone' => 'Africa/Casablanca',
    'env' => 'prod',
    'branding' => [
        'favicon' => '/favicon.ico',
        'logo_light' => null,
        'logo_dark' => null,
        'logo_height' => '2rem',
        'colors' => [
            'primary' => 'blue',
        ],
    ],
    'ui' => [
        'dark_mode' => true,
        'sidebar_customizable' => true,
    ],
    'database' => [
        'default' => 'sqlite',
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => 'platform/storage/database.sqlite',
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'webkernel',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host' => '127.0.0.1',
                'port' => 5432,
                'database' => 'webkernel',
                'username' => 'webkernel',
                'password' => '',
            ],
        ],
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
