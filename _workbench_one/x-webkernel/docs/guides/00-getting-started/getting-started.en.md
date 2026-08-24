# Getting started

PHP 8.4+ and OPcache. Webkernel is an enterprise kernel with **minimum overhead** on the request path. It is not a Laravel or Symfony application with the labels peeled off.

Composer is how you install Webkernel and how you manage dependencies. PSR is the interoperability layer. Neither is rejected.

## Install

The usual path:

```bash
composer create-project webkernel/webkernel
```

From this tree (path repositories under `x-webkernel/`):

```bash
composer install
```

Regenerate classmaps and Webkernel dump files after a host move:

```bash
composer dump-autoload
```

Equivalent CLI: `php webkernel dump-autoload`. `vendor-dir` is `platform/dependencies`.

There is no `php webkernel setup` command.

## Composer is the dependency manager

Composer is not a shameful autoloader we hide. It is the installer and the dependency graph.

| Who | What Composer does |
| --- | --- |
| Kernel / platform | First-party packages, PSR interfaces, PHP extension constraints, dump-autoload maps |
| Module | Its own packages, if it needs them. That graph is the module's problem. |
| `require-dev` | Analyzers and test runners. Not on the request path. |

The kernel constraint is **minimum overhead**, not “zero Composer packages”. Do not pull Laravel, Symfony HTTP, or Illuminate wrappers onto the hot path. Do pull PSR. Do declare the PHP extensions the process actually needs.

## PSR is the enterprise baseline

A real enterprise framework speaks PSR. Interfaces, not framework SDKs:

```json
{
    "require": {
        "php": "^8.4",
        "psr/cache": "^3.0",
        "psr/clock": "^1.0",
        "psr/container": "^2.0",
        "psr/http-client": "^1.0",
        "psr/http-factory": "^1.0",
        "psr/http-message": "^1.0 || ^2.0",
        "psr/log": "^3.0",
        "psr-discovery/http-client-implementations": "^1.4",
        "psr-discovery/http-factory-implementations": "^1.2"
    }
}
```

PHP extensions the host will require (not a closed list):

```json
{
    "require": {
        "ext-dom": "*",
        "ext-fileinfo": "*",
        "ext-intl": "*",
        "ext-libxml": "*",
        "ext-mbstring": "*",
        "ext-pdo": "*",
        "ext-readline": "*",
        "ext-simplexml": "*"
    }
}
```

Discovery packages resolve a PSR HTTP client/factory implementation at install time. They do not license a second HTTP stack inside the kernel.

`webkernel/lifecycle` is a Composer plugin. It runs at dump-autoload time (install paths, `webkernel_*.php` maps). It does not run during an HTTP request.

## Local HTTP server

```bash
php webkernel server
```

Custom CLI process manager wrapping PHP's built-in development server (`php -S -t public` plus `Webkernel\Console\Server\router.php`). Not Swoole, Workerman, ReactPHP, or a Fiber event loop. Not production.

Production: Nginx, Apache, or PHP-FPM serving `public/`.

| Flag | Default | Role |
| --- | --- | --- |
| `--host=` | `127.0.0.1` | Bind address |
| `--port=` | `8000` | Bind port (auto-increments if in use) |
| `--profile-lifecycle` | off | Include-cost, file path, and memory tracing on stderr |
| `--with-jit` | inherit / `WEBKERNEL_JIT` | Child process started with Zend JIT |

The banner prints PHP version, OPcache, and JIT for the **child** `cli-server` SAPI. JIT cannot be toggled with `ini_set()`; it is a process-start flag.

## Host binary

`webkernel` at the project root is the artisan-shaped CLI:

```php
$webapp = require_once __DIR__.'/platform/bootstrap/app.php';
exit($webapp->handle_command(new ArgvInput));
```

Commands are `#[ConsoleCommand]` methods on plain classes. Dump-autoload writes `webkernel_commands.php`. No Symfony Console.

## Next

- [Project layout](../01-project-layout/project-layout.en.md)
- [HTTP kernel](../02-http-kernel/http-kernel.en.md)
- [Performance](../04-performance/performance.en.md)
