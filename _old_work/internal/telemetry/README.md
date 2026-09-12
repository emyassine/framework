# Platform telemetry layout

On-disk sinks for Webkernel observability. Collectors and exporters are not wired yet; this tree operates as a physical contract.

Full guide: [`x-webkernel/docs/guides/05-telemetry/telemetry.en.md`](../../x-webkernel/docs/guides/05-telemetry/telemetry.en.md)

```
telemetry/
├── logs/             # Textual and structured event streams (JSON, Logfmt)
│   ├── access/       # High-throughput HTTP request/response logs
│   ├── app/          # Application domain events and exceptions
│   └── system/       # Webkernel core lifecycle and low-level error logs
├── metrics/          # Aggregated numerical data (OpenTelemetry / Prometheus aligned)
│   ├── counters/     # Monotonically increasing values
│   ├── gauges/       # Point-in-time metrics
│   └── histograms/   # Statistical distributions
├── traces/           # Distributed tracing context and span correlation
│   ├── active/       # In-flight trace contexts and propagation headers
│   └── spans/        # Completed span trees ready for collector export
├── profiles/         # Heavyweight performance sampling
│   ├── cpu/          # Execution clock sampling and call graphs
│   └── memory/       # Heap allocation snapshots
└── buffer/           # High-speed transient staging (async flush / ring buffer)
    ├── shm/          # Shared memory segments
    └── queue/        # Write-ahead log or disk-backed failover queues
```
