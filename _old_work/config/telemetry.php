<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

return [
	'schema_version' => '1.0.1',

	'telemetry' => [
		'enabled'   => true,
		'logs_path' => 'platform/telemetry/logs',

		'structure' => [
			'logs' => [
				'access' => [
					'path'        => 'platform/telemetry/logs/access',
					'description' => 'High-throughput HTTP request/response logs',
				],
				'app' => [
					'path'        => 'platform/telemetry/logs/app',
					'description' => 'Application domain events and exceptions',
				],
				'system' => [
					'path'        => 'platform/telemetry/logs/system',
					'description' => 'Webkernel core lifecycle and low-level error logs',
				],
			],

			'metrics' => [
				'counters' => [
					'path'        => 'platform/telemetry/metrics/counters',
					'description' => 'Monotonically increasing values (total requests, error counts)',
				],
				'gauges' => [
					'path'        => 'platform/telemetry/metrics/gauges',
					'description' => 'Point-in-time metrics (memory usage, active workers)',
				],
				'histograms' => [
					'path'        => 'platform/telemetry/metrics/histograms',
					'description' => 'Statistical distributions (request latency, payload size)',
				],
			],

			'traces' => [
				'active' => [
					'path'        => 'platform/telemetry/traces/active',
					'description' => 'In-flight trace contexts and propagation headers',
				],
				'spans' => [
					'path'        => 'platform/telemetry/traces/spans',
					'description' => 'Completed span trees ready for collector export',
				],
			],

			'profiles' => [
				'cpu' => [
					'path'        => 'platform/telemetry/profiles/cpu',
					'description' => 'Execution clock sampling and call graphs',
				],
				'memory' => [
					'path'        => 'platform/telemetry/profiles/memory',
					'description' => 'Heap allocation snapshots and pointer tracking',
				],
			],

			'buffer' => [
				'shm' => [
					'path'        => 'platform/telemetry/buffer/shm',
					'description' => 'Shared memory segments (shmop, APCu, memory-mapped rings)',
				],
				'queue' => [
					'path'        => 'platform/telemetry/buffer/queue',
					'description' => 'Write-ahead log or disk-backed failover queues',
				],
			],
		],
	],
];
