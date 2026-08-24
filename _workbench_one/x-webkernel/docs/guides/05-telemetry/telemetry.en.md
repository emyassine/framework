# Telemetry

On-disk contract for Webkernel observability. The tree exists under `platform/telemetry/`. Collectors, exporters, and the HTTP kernel hooks that write into it are **not implemented yet**.

Do not invent a second log path under `storage/logs`. Access, app, and system streams belong here.

PSR logging (`psr/log`) is the application-facing logger contract. This tree is where those events, plus metrics, traces, and profiles, land on disk.

## Tree

```
platform/telemetry/
├── logs/             # Textual and structured event streams (JSON, Logfmt)
│   ├── access/       # High-throughput HTTP request/response logs
│   ├── app/          # Application domain events and exceptions
│   └── system/       # Webkernel core lifecycle and low-level error logs
├── metrics/          # Aggregated numerical data (OpenTelemetry / Prometheus aligned)
│   ├── counters/     # Monotonically increasing values (total requests, error counts)
│   ├── gauges/       # Point-in-time metrics (memory usage, active workers)
│   └── histograms/   # Statistical distributions (request latency, payload size)
├── traces/           # Distributed tracing context and span correlation
│   ├── active/       # In-flight trace contexts and propagation headers
│   └── spans/        # Completed span trees ready for collector export
├── profiles/         # Heavyweight performance sampling (XHProf, OTel, flamegraphs)
│   ├── cpu/          # Execution clock sampling and call graphs
│   └── memory/       # Heap allocation snapshots and pointer tracking
└── buffer/           # High-speed transient staging (async flush / ring buffer)
    ├── shm/          # Shared memory segments (shmop, APCu, memory-mapped rings)
    └── queue/        # Write-ahead log or disk-backed failover queues
```

## Mapping to the HTTP cycle

| Kernel step | Sink |
| --- | --- |
| Request accepted | `logs/access/` (open line) · `metrics/counters/` (requests_total) · `traces/active/` |
| Dispatch / pipeline | `traces/spans/` (child spans) · `metrics/histograms/` (queue / handler time) |
| Response flushed | `logs/access/` (status, bytes, duration) · `metrics/histograms/` (latency) |
| Uncaught error | `logs/app/` or `logs/system/` · error counter |
| `--profile-lifecycle` / production sampler | `profiles/cpu/`, `profiles/memory/` |
| Burst under load | `buffer/shm/` then `buffer/queue/` if the shm ring is full |

Hot-path writes go through `buffer/` so a telemetry flush cannot steal the under-1 ms kernel budget. See [Performance](../04-performance/performance.en.md).

## Rules

- Align names and types with OpenTelemetry / Prometheus (counters, gauges, histograms). Do not invent a parallel metric vocabulary.
- Access logs are high-throughput. Keep them structured and append-only. Do not mix domain events into `logs/access/`.
- System logs are kernel lifecycle and low-level failures (boot, dump-autoload mismatch, OPcache). Application exceptions go to `logs/app/`.
- Profiles are opt-in and heavy. Never enable CPU/memory sampling on every request in production.
- Shared memory and WAL under `buffer/` are staging, not the source of truth. Exporters drain them into logs / metrics / traces / an external collector.

## Status

Directories and `.gitkeep` files are in the host tree. No writer, no rotator, no OTLP exporter, no Prometheus scrape endpoint. The local server today prints access lines and `--profile-lifecycle` traces on stderr; that output is the prototype of `logs/access/` and `profiles/`, not a substitute for this layout.
