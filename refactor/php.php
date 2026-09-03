<?php

declare(strict_types=1);

//> Webkernel Config Architecture Benchmark & Bottleneck Profiler

require_once __DIR__ . '/internal/fast-boot.php';

final class ConfigBenchmarkRegistry
{
    /** @var array<string, mixed> */
    public static array $flattened = [];

    /** @var array<string, mixed> */
    public static array $nested = [];

    public static function load(array $config_tree): void
    {
        self::$nested = $config_tree;
        self::$flattened = self::flatten_array($config_tree);
    }

    /**
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    private static function flatten_array(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $full_key = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $result[$full_key] = $value;
                $result += self::flatten_array($value, $full_key);
            } else {
                $result[$full_key] = $value;
            }
        }
        return $result;
    }

    public static function get_flattened(string $key, mixed $default = null): mixed
    {
        return self::$flattened[$key] ?? $default;
    }

    public static function get_dynamic_traversal(string $key, mixed $default = null): mixed
    {
        $array = self::$nested;
        if (isset($array[$key])) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $default;
        }

        $segments = explode('.', $key);
        foreach ($segments as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }
}

// Load configurations into memory
$config_tree = [
    'app' => [
        'app' => ['name' => 'Webkernel'],
        'branding' => ['colors' => ['primary' => 'blue']],
    ],
    'platform' => [
        'internal' => ['cache_path' => 'internal/cache'],
    ],
    'telemetry' => [
        'telemetry' => [
            'structure' => [
                'metrics' => [
                    'counters' => ['path' => 'platform/telemetry/metrics/counters'],
                ],
            ],
        ],
    ],
];

ConfigBenchmarkRegistry::load($config_tree);

function measure_strategy(string $name, callable $callback, int $iterations): array
{
    gc_collect_cycles();
    $start_memory = memory_get_usage(true);
    $start_time = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $val = $callback();
    }

    $elapsed_time = microtime(true) - $start_time;
    $end_memory = memory_get_usage(true);

    return [
        'name' => $name,
        'time' => $elapsed_time,
        'ops' => (int) ($iterations / $elapsed_time),
        'latency_ns' => ($elapsed_time / $iterations) * 1_000_000_000,
        'memory_delta' => $end_memory - $start_memory,
    ];
}

$iterations = 10_000_000;
$target_key = 'telemetry.telemetry.structure.metrics.counters.path';

$results = [];

// 1. Baseline: Raw PHP Native Array (Hardware/Engine speed limit)
$raw_array = $config_tree;
$results[] = measure_strategy(
    '1. Raw Nested Array (Direct Access)',
    fn() => $raw_array['telemetry']['telemetry']['structure']['metrics']['counters']['path'] ?? null,
    $iterations
);

// 2. Flattened Cache Array ($flattened['key.path'])
$results[] = measure_strategy(
    '2. Flat Key Map Lookup ($map[$key])',
    fn() => ConfigBenchmarkRegistry::get_flattened($target_key),
    $iterations
);

// 3. Current config() function
$results[] = measure_strategy(
    '3. Active config() Helper Function',
    fn() => config($target_key),
    $iterations
);

// 4. Dynamic Traversal (explode + foreach loop)
$results[] = measure_strategy(
    '4. Dynamic Explode Traversal Engine',
    fn() => ConfigBenchmarkRegistry::get_dynamic_traversal($target_key),
    $iterations
);

// Format Results
$baseline_time = $results[0]['time'];

printf("\n========================================================================================\n");
printf(" WEBKERNEL CONFIG BENCHMARK & ARCHITECTURAL PROFILING (%s ITERATIONS)\n", number_format($iterations));
printf(" Target Key: %s\n", $target_key);
printf("========================================================================================\n");
printf("%-38s | %-12s | %-12s | %-10s\n", "Strategy / Pattern", "Time (s)", "Throughput", "Slowdown");
printf("----------------------------------------------------------------------------------------\n");

foreach ($results as $res) {
    $slowdown = $res['time'] / $baseline_time;
    printf(
        "%-38s | %-12.4f | %-10s ops/s | %.2fx\n",
        $res['name'],
        $res['time'],
        number_format($res['ops']),
        $slowdown
    );
}

printf("========================================================================================\n\n");

// Bottleneck Diagnosis Output
$current_time = $results[2]['time'];
$flat_time = $results[1]['time'];

printf("--- BOTTLENECK ANALYSIS & ACTIONABLE INSIGHTS ---\n");
if ($current_time > $flat_time * 1.5) {
    $potential_gain = (($current_time - $flat_time) / $current_time) * 100;
    printf(
        "[!] CAUSE: Overhead is coming from runtime key parsing (explode/loop traversal).\n" .
        "[=>] REFACTOR: Compile configuration files into a flat dot-notation cache array (config.compiled.php) on boot/build.\n" .
        "[=>] GAIN ESTIMATE: You will reclaim ~%.2f%% of execution time (Target: < 80ns/op).\n",
        $potential_gain
    );
} else {
    printf("[+] OPTIMAL: The helper is running close to static array hash table lookup limits.\n");
}
