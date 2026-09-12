# Configuration Access & Runtime Mutation

This document details value retrieval, tree branching, and runtime persistence within the Webkernel Config system.

---

## Accessing Configuration Values

### Dot-Notation Retrieval

Use `config('key')` or `Config::get('key')` to read configuration values:

```php
use Webkernel\Config\Config;

// Simple scalar lookup
$app_name = config('app.app.name');

// Default fallback when key does not exist
$port = config('database.port', 3306);

// Facade syntax
$debug = Config::get('app.debug', false);
```

### Retrieving Array Subtrees

Accessing a branch key returns the complete subtree array:

```php
// Returns ['metrics' => ['counters' => ['path' => '...']]]
$structure = config('telemetry.telemetry.structure');
```

---

## Runtime Mutations & Atomic Persistence

Webkernel Config permits setting configuration values dynamically at runtime. When `Config::set()` is invoked:
1. The key is validated against registered protection rules (`ConfigGuard`).
2. In-memory static dictionary (`Config::$items`) is updated instantly.
3. The mutation is saved to `internal/platform-runtime.php` via `ConfigWriter::write()`.

```php
use Webkernel\Config\Config;

// Update configuration at runtime
Config::set('app.maintenance_mode', true);

// Subsequent calls in the same or future requests receive the updated value
$is_down = config('app.maintenance_mode'); // true
```

---

## Path Resolution

The `Config::path()` method resolves configuration keys or raw strings into absolute filesystem paths relative to the platform root:

```php
use Webkernel\Config\Config;

// Key containing relative path "internal/cache" -> "/app/internal/cache"
$cache_dir = Config::path('platform.internal.cache_path');

// Raw path with appended subfolder -> "/app/storage/logs/custom.log"
$log_file = Config::path('storage/logs', 'custom.log');
```
