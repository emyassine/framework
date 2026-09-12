# Webkernel Config Package

The **Webkernel Config Package** provides an ultra-fast, zero-overhead, compiled configuration subsystem designed for high-performance enterprise applications. It replaces request-path framework overhead with pre-compiled $O(1)$ flat hash table lookups, operating at near-native C static array lookup speeds (<100 nanoseconds per call).

## Key Features

- **Sub-Microsecond Performance ($O(1)$ Hash Bucket Resolution)**: Eliminates dynamic `explode()` calls and runtime array tree traversals on the hot path.
- **Automatic Fingerprint Recompilation**: Zero manual build commands (`config:cache` is obsolete). Automatically detects source file changes across application directories (`config/*.php`), provider packages (`PlatformProvider::CONFIG`), and runtime overrides (`internal/platform-runtime.php`).
- **Dynamic Configuration Discovery**: Discovers configuration files dynamically without hardcoded path enums or filename assumptions.
- **Enhanced Provider Manifest Support**: Supports package `PlatformProvider::CONFIG` definitions with target path, publish destination, and tag metadata.
- **Publishable Assets Management**: Exposes `Config::publishables()` façade method for asset publishing tools.
- **Runtime Key Protection Guards**: Immutable configuration guards prevent accidental runtime mutation of platform-critical settings.
- **OPcache Friendly Atomic Writes**: Writes compiled configuration arrays atomically with immediate OPcache cache invalidation.

---

## Installation & Requirements

- **PHP Version**: `>= 8.5`
- **Dependencies**: `webkernel/lifecycle`

Included automatically within Webkernel core distribution:

```json
{
    "require": {
        "webkernel/config": "^0.12.0"
    }
}
```

---

## Basic Usage

### Reading Configuration Values

Access configuration values using the global `config()` helper function or the `Config` static façade:

```php
use Webkernel\Config\Config;

// Global helper access (sub-microsecond O(1) hash lookup)
$app_name = config('app.app.name');
$debug    = config('app.debug', false);

// Facade access
$database_host = Config::get('database.connections.mysql.host', '127.0.0.1');

// Tree branch access (returns subtree array)
$telemetry_config = config('telemetry.telemetry.structure');
```

### Path Resolution Helper

Resolve configuration-defined paths or application sub-paths:

```php
use Webkernel\Config\Config;

// Resolves relative configuration value or fallback to absolute path
$cache_path = Config::path('platform.internal.cache_path');
$custom_dir = Config::path('storage/custom');
$config_file = config_path('app.php');
```

---

## Architecture & Recompilation Engine

```
 ┌────────────────────────────────────────────────────────┐
 │ Source Configuration Files                             │
 │  ├── base_path('config/*.php')                         │
 │  ├── PlatformProvider::CONFIG (Packages)               │
 │  └── internal/platform-runtime.php (Overrides)         │
 └──────────────────────────┬─────────────────────────────┘
                            │
                            ▼
 ┌────────────────────────────────────────────────────────┐
 │ ConfigFingerprint (Automatic mtime + size check)       │
 └──────────────────────────┬─────────────────────────────┘
                            │
            ┌───────────────┴───────────────┐
            ▼                               ▼
     Cache Valid                      Cache Stale
  (Single require)             (Automatic Compile)
            │                               │
            ▼                               ▼
 ┌────────────────────────────────────────────────────────┐
 │ ConfigCompiler (Pre-flattening & Atomic Cache Write)   │
 └──────────────────────────┬─────────────────────────────┘
                            │
                            ▼
 ┌────────────────────────────────────────────────────────┐
 │ Config::$items Static Memory Dictionary (O(1) Access)  │
 └────────────────────────────────────────────────────────┘
```

### Change Detection & Fingerprinting

Webkernel Config tracks the state of all configuration sources across:
1. Application configuration directory (`base_path('config')`) and nested subdirectories.
2. Provider package configurations declared via `PlatformProvider::CONFIG`.
3. Runtime override file (`internal/platform-runtime.php`).

When any file is edited, added, or removed, `ConfigFingerprint::is_stale()` triggers automatic recompilation on the next request. Developer intervention or CLI compile commands are completely eliminated.

---

## Package Provider Configuration & Publishing

Package providers declare configuration files using the `PlatformProvider::CONFIG` array structure:

```php
namespace Acme\Courier;

use Webkernel\PlatformProvider;

class CourierProvider extends PlatformProvider
{
    public const CONFIG = [
        'courier' => [
            'path'    => __DIR__ . '/../config/courier.php',
            'publish' => \config_path('courier.php'),
            'tag'     => 'courier-config',
        ],
    ];
}
```

### Publishing Package Configurations

Asset management tools and CLI commands inspect publishable package assets via the static façade:

```php
use Webkernel\Config\Config;

// Retrieve all registered publishable configurations
$all_publishables = Config::publishables();

// Filter publishable configurations by tag
$courier_publishables = Config::publishables('courier-config');

foreach ($courier_publishables as $item) {
    echo $item->key;     // "courier"
    echo $item->source;  // "/path/to/vendor/courier/config/courier.php"
    echo $item->target;  // "/path/to/app/config/courier.php"
    echo $item->tag;     // "courier-config"
}
```

---

## Configuration Protection & Guards

Prevent accidental runtime mutations of sensitive platform values using `Config::protect()`:

```php
use Webkernel\Config\Config;
use Webkernel\Config\Exceptions\ConfigGuardException;

// Protect exact keys and prefix trees
Config::protect([
    'app.secret',
    'platform',
]);

// Permitted runtime update
Config::set('custom.feature_flag', true);

// Throws ConfigGuardException (exact key match)
Config::set('app.secret', 'new-secret');

// Throws ConfigGuardException (prefix match on "platform.*")
Config::set('platform.debug', false);
```

---

## Performance Benchmarks

In a 10,000,000 iteration benchmark on PHP 8.5 comparing traditional dynamic string splitting (`explode('.', $key)`) with Webkernel's pre-compiled flat hash table:

| Strategy / Pattern | Throughput (ops/s) | Latency | Speedup |
| :--- | :---: | :---: | :---: |
| **Direct Static Memory Array** | 17,962,158 ops/s | 55.67 ns | **9.06x** |
| **Webkernel `config()` Helper** | 13,049,720 ops/s | 76.63 ns | **6.59x** |
| **Webkernel `Config::get()`** | 13,367,392 ops/s | 74.81 ns | **6.74x** |
| **Dynamic Explode Traversal** | 1,981,642 ops/s | 504.63 ns | 1.00x |

> [!NOTE]
> Webkernel's pre-compiled flat dictionary reclaims **84.81% of CPU execution time** lost to string allocations and array traversals in generic framework configuration systems.

---

## Complete Documentation

For detailed architectural guides and API references, see the [`docs/`](./docs/index.md) directory:

- [Architecture & Fingerprint Engine](./docs/architecture.md)
- [Configuration & Options](./docs/configuration.md)
- [Package Provider & Asset Publishing](./docs/providers.md)
- [Immutability & Guard System](./docs/guards.md)
- [Benchmark & Profiling Guide](./docs/benchmarks.md)

---

## Copyright & License

(c) 2025 - 2027 Numerimondes, El Moumen Yassine.  
Released under the proprietary Webkernel license.
