# Webkernel Config Documentation

Welcome to the **Webkernel Config** package documentation. Webkernel Config is a zero-overhead configuration kernel built for high-performance applications and enterprise platforms.

## Documentation Index

- [Architecture & Fingerprint Engine](./architecture.md)
  - Pre-compiled O(1) flat dictionary
  - Automatic change detection & fingerprinting
  - Atomic OPcache invalidation
- [Configuration & Options](./configuration.md)
  - Value retrieval & default handling
  - Nested branch extraction
  - Runtime mutation and persistence
- [Package Provider & Asset Publishing](./providers.md)
  - `PlatformProvider::CONFIG` structure
  - Publishing manifest & `Config::publishables()`
  - Package configuration overrides
- [Immutability & Guard System](./guards.md)
  - Key protection rules
  - Prefix tree matching
  - Exception handling
- [Benchmark & Profiling Guide](./benchmarks.md)
  - 10,000,000 iteration benchmark analysis
  - Sub-microsecond latency profiling
  - Zero request-path bloat rules

---

## Quick Reference

```php
use Webkernel\Config\Config;

// Get a configuration value
$name = config('app.app.name');

// Get with fallback
$timeout = config('database.timeout', 30);

// Set and persist runtime configuration
Config::set('app.maintenance', false);

// Protect key tree against runtime mutation
Config::protect(['platform', 'app.secret']);

// Inspect publishable package assets
$publishables = Config::publishables('courier-config');
```
