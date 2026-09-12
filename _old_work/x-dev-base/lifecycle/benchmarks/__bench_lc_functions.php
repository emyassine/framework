<?php
require dirname(__DIR__, 2) . '/autoload.php';

$iterations = 10_000_000;

$benchmarks = [
    'base_path' => static fn (): string =>
        base_path('json.json'),

    'vendor_path' => static fn (): string =>
        vendor_path('json.json'),

    'webkernel_package' => static fn (): string =>
        webkernel_package('lifecycle', 'README.md'),
];

echo "PHP Path Functions Benchmark\n";
echo "============================\n";
echo "Iterations : " . number_format($iterations) . "\n\n";

foreach ($benchmarks as $name => $function) {
    $result = $function();

    $start = hrtime(true);

    for ($i = 0; $i < $iterations; ++$i) {
        $result = $function();
    }

    $elapsed = hrtime(true) - $start;

    $total_ms = $elapsed / 1_000_000;
    $per_call_ns = $elapsed / $iterations;
    $per_call_us = $per_call_ns / 1_000;
    $calls_per_second = 1_000_000_000 / $per_call_ns;

    echo str_pad($name, 22) . " : "
        . number_format($total_ms, 2) . " ms"
        . " | "
        . number_format($per_call_ns, 2) . " ns"
        . " | "
        . number_format($per_call_us, 6) . " µs"
        . " | "
        . number_format($calls_per_second, 0) . " calls/s"
        . "\n";

    echo "  result: " . $result . "\n\n";
}
