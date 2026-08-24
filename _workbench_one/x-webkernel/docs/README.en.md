# Webkernel documentation

Content tree. Order prefixes (`00-`, `01-`, …) sort on disk. URLs strip those prefixes. Filenames are `{name}.{lang}.md`. Language is always in the filename, never only in front matter.

```
docs/
  README.en.md
  guides/
    00-getting-started/getting-started.en.md
    01-project-layout/project-layout.en.md
    02-http-kernel/http-kernel.en.md
    03-domain-hierarchy/domain-hierarchy.en.md
    04-performance/performance.en.md
    05-telemetry/telemetry.en.md
```

| Order | URL slug | Topic |
| --- | --- | --- |
| 00 | `guides/getting-started` | Composer, PSR, install, CLI |
| 01 | `guides/project-layout` | Physical tree |
| 02 | `guides/http-kernel` | Request → Response lifecycle |
| 03 | `guides/domain-hierarchy` | Platform → Page model |
| 04 | `guides/performance` | Under 1 ms / under 10 ms |
| 05 | `guides/telemetry` | On-disk observability contract |
