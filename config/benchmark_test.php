<?php
return [
    'app' => [
        'name' => 'Webkernel',
        'debug' => true,
        'timezone' => 'UTC',
    ],
    'database' => [
        'connections' => [
            'mysql' => [
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'test',
                'username' => 'root',
                'password' => 'secret',
            ],
        ],
    ],
    'telemetry' => [
        'telemetry' => [
            'structure' => [
                'metrics' => [
                    'counters' => [
                        'path' => 'platform/telemetry/metrics/counters',
                    ],
                ],
            ],
        ],
    ],
];