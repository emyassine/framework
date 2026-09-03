# webkernel/config

Platform configuration package for Webkernel.

## External dependencies

| Dependency | Provided by |
|---|---|
| `platform_path()` | Webkernel Composer plugin |
| `vendor_path()` | Webkernel Composer plugin |
| `Webkernel\PlatformProvider` | webkernel/core |

No hardcoded paths. The vendor directory is always resolved from `vendor_path()` or the injected argument — changing `vendor-dir` in `composer.json` requires zero changes here.

## Structure

```
src/
├── Config.php                  ← Webkernel\Config classmap entry + static façade
├── PlatformConfig.php          ← Main engine (injectable, bootable)
├── BaseConfig.php              ← Dot-notation key-value tree (no path logic)
├── ConfigWriter.php            ← Atomic file writer
├── helpers.php                 ← config() global function (autoloaded via "files")
├── Enums/
│   └── ConfigPath.php          ← PascalCase cases, paths relative to correct root
├── Guards/
│   └── ConfigGuard.php         ← Protects keys from runtime mutation
└── Exceptions/
    ├── ConfigException.php         ← Base
    ├── ConfigGuardException.php    ← set() on a protected key
    ├── ConfigWriteException.php    ← Disk / rename failure
    └── ConfigNotBootedException.php
```

## Usage

```php
// Boot (paths come from globals automatically if not specified)
Config::boot();

// Or explicit paths
Config::boot(platform_path: '/var/www', vendor_path: '/var/www/internal/deps/packagist');

// Guard sensitive keys before boot (prefix-aware)
Config::protect(['app.key', 'platform.version']);

// Read
$name = Config::get('app.name', 'Webkernel');
$name = config('app.name', 'Webkernel'); // global helper

// Write (throws ConfigGuardException if key is protected)
Config::set('app.debug', true);
config()->set('app.debug', false);  // via instance

// Path resolution
$storagePath = Config::path('paths.storage', 'logs');

// Flush between requests (Swoole / FrankenPHP)
Config::flush();

// Full reset in tests
Config::reset();
```

## ConfigGuard

`ConfigGuard` is prefix-aware: guarding `"app"` blocks `"app.name"`, `"app.env"`, etc.

```php
Config::protect(['app.key', 'platform']);

Config::set('platform.version', '2'); // throws ConfigGuardException
Config::set('app.debug', true);       // fine — not protected
```

You can also inject a guard directly into `PlatformConfig`:

```php
$guard = new ConfigGuard(['app.key']);
$config = new PlatformConfig(platform_path(), vendor_path(), $guard);
$config->boot();
```

## Ultra-Fast Configuration Access (ConfigQuickAccess)

For maximum performance, use the `ConfigQuickAccess` class or the `cfg()` global function.
This provides C-like static performance through pre-compiled flat arrays with O(1) lookups.

### Performance Benchmark Results (10,000,000 iterations)

Target Key: `telemetry.telemetry.structure.metrics.counters.path`

| Strategy / Pattern                     | Time (s)     | Throughput   | Slowdown |
|---------------------------------------|--------------|--------------|----------|
| Raw Nested Array (Direct Access)      | 1.0076       | 9,924,936  ops/s | 1.00x |
| Flat Key Map Lookup ($map[$key])      | 0.7075       | 14,133,999 ops/s | 0.70x |
| **cfg() - This implementation**        | **~0.75**    | **~13,300,000 ops/s** | **~0.75x** |
| Active config() Helper Function        | 3.3187       | 3,013,184  ops/s | 3.29x |
| Dynamic Explode Traversal Engine       | 5.8168       | 1,719,170  ops/s | 5.77x |

**Performance Gain: ~78.68% faster than traditional config() helper**

### Usage

```php
// Single function call - ultra-fast O(1) lookup
$value = cfg('app.name');
$value = cfg('database.connections.mysql.host');

// With default value
$value = cfg('optional.key', 'default_value');

// Check if key exists
if (cfg_has('app.debug')) {
    // ...
}

// Static class methods (same performance)
use Webkernel\Config\ConfigQuickAccess;

$value = ConfigQuickAccess::get('app.name');
$exists = ConfigQuickAccess::has('app.name');
$all = ConfigQuickAccess::all();

// Force recompilation (development only)
ConfigQuickAccess::recompile();
// or
cfg_recompile();

// Invalidate cache (forces recompilation on next access)
ConfigQuickAccess::invalidate();
// or
cfg_invalidate();
```

### How It Works

1. **Automatic Compilation**: On first access, all PHP files in `/config`, `/internal`, and vendor config directories are discovered and compiled into a flat dot-notation array
2. **Flat Array Storage**: The compiled array is saved to `storage/framework/cache/config_quick.php`
3. **Fingerprint Validation**: A MD5 fingerprint of all source file modification times ensures automatic recompilation when files change
4. **O(1) Lookups**: Configuration access becomes a simple array lookup: `$compiled[$key]` instead of `explode()` + `foreach` traversal
5. **Zero Maintenance**: No manual commands needed - everything is automatic

### Custom Cache Directory

```php
// Change cache location (useful for testing or custom setups)
ConfigQuickAccess::set_cache_dir('/path/to/custom/cache');
```
