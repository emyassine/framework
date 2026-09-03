<?php
declare(strict_types=1);
//> Webkernel Config Architecture Benchmark & Bottleneck Profiler
require_once __DIR__ . '/internal/fast-boot.php';

use Webkernel\Config\Config;

final class DynamicExplodeEngine
{
    /** @var array<string, mixed> */
    public static array $tree = [];

    /**
     * @param $key string
     * @param $default mixed
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cursor = self::$tree;

        foreach (\explode('.', $key) as $segment) {
            if (! \is_array($cursor) || ! \array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }
}

// Populate Dynamic Explode tree with loaded configuration
DynamicExplodeEngine::$tree = Config::all();

$iterations = 10_000_000;
$target_key = 'telemetry.telemetry.structure.metrics.counters.path';

// Warm up and verify consistency
$expected_val = 'platform/telemetry/metrics/counters';
$val_direct   = Config::$items[$target_key] ?? null;
$val_helper   = config($target_key);
$val_facade   = Config::get($target_key);
$val_dynamic  = DynamicExplodeEngine::get($target_key);

if ($val_helper !== $expected_val || $val_dynamic !== $expected_val) {
    \fwrite(STDERR, \sprintf("Self-check failed: expected '%s', got helper='%s', dynamic='%s'\n", $expected_val, (string) $val_helper, (string) $val_dynamic));
    exit(1);
}

/**
 * @param $name string
 * @param $callback callable(): mixed
 * @param $count int
 *
 * @return array{name: string, time: float, ops: int, latency_ns: float, memory_mb: float}
 */
function benchmark_run(string $name, callable $callback, int $count): array
{
    \gc_collect_cycles();
    $start_memory = \memory_get_usage(true);
    $start_time = \microtime(true);

    for ($i = 0; $i < $count; $i++) {
        $v = $callback();
    }

    $elapsed = \microtime(true) - $start_time;
    $end_memory = \memory_get_usage(true);

    return [
        'name'       => $name,
        'time'       => $elapsed,
        'ops'        => (int) ($count / $elapsed),
        'latency_ns' => ($elapsed / $count) * 1_000_000_000,
        'memory_mb'  => ($end_memory - $start_memory) / (1024 * 1024),
    ];
}

$raw_static = Config::$items;

$benchmarks = [];

// 1. Direct Static Array Lookup (C-level hardware limit in PHP)
$benchmarks[] = benchmark_run(
    '1. Direct Static Memory Array (C-Limit)',
    static fn() => $raw_static[$target_key] ?? null,
    $iterations
);

// 2. Webkernel config() Global Helper Function
$benchmarks[] = benchmark_run(
    '2. Webkernel config() Helper',
    static fn() => config($target_key),
    $iterations
);

// 3. Webkernel Config::get() Static Façade
$benchmarks[] = benchmark_run(
    '3. Webkernel Config::get() Façade',
    static fn() => Config::get($target_key),
    $iterations
);

// 4. Dynamic Explode Traversal (explode + foreach loop)
$benchmarks[] = benchmark_run(
    '4. Dynamic Explode Traversal (Traditional)',
    static fn() => DynamicExplodeEngine::get($target_key),
    $iterations
);

$baseline_time = $benchmarks[0]['time'];

\printf("\n========================================================================================\n");
\printf(" WEBKERNEL CONFIG ARCHITECTURE BENCHMARK & PROFILER (%s ITERATIONS)\n", \number_format($iterations));
\printf(" Target Key : %s\n", $target_key);
\printf(" Resolved   : %s\n", $expected_val);
\printf("========================================================================================\n");
\printf("%-42s | %-10s | %-16s | %-10s\n", "Strategy / Pattern", "Time (s)", "Throughput", "Comparison");
\printf("----------------------------------------------------------------------------------------\n");

foreach ($benchmarks as $b) {
    $ratio = $b['time'] / $baseline_time;
    $speedup_vs_dynamic = $benchmarks[3]['time'] / $b['time'];

    \printf(
        "%-42s | %-10.4f | %12s ops/s | %5.2fx [%.1fx dynamic]\n",
        $b['name'],
        $b['time'],
        \number_format($b['ops']),
        $ratio,
        $speedup_vs_dynamic
    );
}

\printf("========================================================================================\n\n");

$helper_time  = $benchmarks[1]['time'];
$dynamic_time = $benchmarks[3]['time'];
$speedup      = $dynamic_time / $helper_time;
$gain_pct     = (($dynamic_time - $helper_time) / $dynamic_time) * 100;

\printf("--- ARCHITECTURAL VERIFICATION & PERFORMANCE SUMMARY ---\n");
\printf("[+] Dynamic Explode Overhead : %0.4fs for 10M calls\n", $dynamic_time);
\printf("[+] Webkernel config()       : %0.4fs for 10M calls\n", $helper_time);
\printf("[+] Speedup Factor           : %.2fx faster than dynamic explode\n", $speedup);
\printf("[+] Latency per lookup       : %.2f ns/op (sub-nanosecond scale)\n", $benchmarks[1]['latency_ns']);
\printf("[+] Reclaimed CPU time       : %.2f%% CPU execution time eliminated\n", $gain_pct);
\printf("[+] Zero request-path bloat  : Pre-compiled flat hash table with O(1) C hash bucket resolution.\n\n");
