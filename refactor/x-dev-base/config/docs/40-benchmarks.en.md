# Benchmark & Performance Profiling

Webkernel is engineered for low-latency, zero-overhead execution. This document presents profiling results and micro-benchmark methodology comparing Webkernel Config with traditional dynamic explosion traversal.

---

## 10,000,000 Iteration Benchmark Results

Environment: PHP 8.5.9 (CLI NTS), Debian Linux.  
Target Key: `telemetry.telemetry.structure.metrics.counters.path`

```
========================================================================================
 WEBKERNEL CONFIG ARCHITECTURE BENCHMARK & PROFILER (10,000,000 ITERATIONS)
========================================================================================
Strategy / Pattern                         | Time (s)   | Throughput       | Comparison
----------------------------------------------------------------------------------------
1. Direct Static Memory Array (C-Limit)    | 0.5567 s   | 17,962,158 ops/s | 1.00x [9.06x vs dynamic]
2. Webkernel config() Helper               | 0.7663 s   | 13,049,720 ops/s | 1.38x [6.59x vs dynamic]
3. Webkernel Config::get() Façade         | 0.7481 s   | 13,367,392 ops/s | 1.34x [6.74x vs dynamic]
4. Dynamic Explode Traversal (Traditional) | 5.0463 s   |  1,981,642 ops/s | 9.06x [1.00x vs dynamic]
========================================================================================
```

---

## Performance Insights

1. **Sub-Nanosecond Latency**:
   - `config('key')` executes in **76.63 nanoseconds** per lookup.
   - Lookups hit the `$items` static memory array directly with zero string manipulation.

2. **6.59x Throughput Increase**:
   - Webkernel processes **13+ million config lookups per second** on a single thread.
   - Traditional dynamic explosion yields under **2 million lookups per second**.

3. **Reclaimed CPU Time**:
   - Eliminating dynamic `explode('.', $key)` string allocations reclaims **84.81% of CPU execution time**.

---

## Running the Benchmark Suite

Execute the benchmark profiler script at the project root:

```bash
php benchmark_config.php
```
