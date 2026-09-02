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
