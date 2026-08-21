# Webkernel — Fluent API Reference (`README_FLUENT.md`)

> **Status:** canonical reference for the `webapp()` / `webterminal()` fluent chain.
> All methods use strict `snake_case`. No camelCase anywhere — not in method names, not in config keys, not in array keys.
> This document is the single source of truth during the current refactor phase.

---

## Table of Contents

1. [ComposableContract — base structure](#1-composablecontract--base-structure)
2. [Config system — `webapp()->config()`](#2-config-system--webappconfig)
3. [Application core — `webapp()`](#3-application-core--webapp)
4. [Platform, instance & identity — `webapp()->platform()`](#4-platform-instance--identity--webappplatform)
5. [HTTP kernel, routing & middleware](#5-http-kernel-routing--middleware)
6. [Business & UI hierarchy](#6-business--ui-hierarchy)
7. [Authorization & security — module-scoped ACL](#7-authorization--security--module-scoped-acl)
8. [Interactive CLI — `webterminal()`](#8-interactive-cli--webterminal)
9. [Fast-boot & config rewrite rules](#9-fast-boot--config-rewrite-rules)
10. [Chaining reference table](#10-chaining-reference-table)
11. [Design rationale](#11-design-rationale)

---

## 1. ComposableContract — base structure

Every public API segment implements `ComposableContract`. Lazy-loading is driven by the `api_name => FQCN` map generated at `composer dump-autoload`. No magic methods (`__call`, `__callStatic`) are used anywhere.

```php
namespace Webkernel\Composables;

interface ComposableContract
{
    public static function api_name(): string;
    public static function container_lifetime(): string; // 'singleton' | 'bind' | 'scoped'
}
```

Example implementation:

```php
namespace Webkernel\Composables;

final class PlatformComposable implements ComposableContract
{
    public static function api_name(): string
    {
        return 'platform';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    public function instance(): InstanceComposable
    {
        return webapp()->container()->get(InstanceComposable::class);
    }

    public function system_admin(): SystemAdminComposable
    {
        return webapp()->container()->get(SystemAdminComposable::class);
    }
}
```

**Container lifetime rules:**

| Lifetime    | Behaviour                                                     | When to use                                          |
|-------------|---------------------------------------------------------------|------------------------------------------------------|
| `singleton` | Single instance for the entire process lifetime               | Platform, config, cache, router                      |
| `scoped`    | Single instance per HTTP request, reset between requests      | Auth session, current panel, request-scoped services |
| `bind`      | New instance on every `container()->get()` call               | Stateful builders, one-shot jobs                     |

---

## 2. Config system — `webapp()->config()`

### 2.1 Overview

The config system is the **first thing resolved** by `webapp()`, before any composable. It reads layered PHP config files, merges them, and exposes a typed read/write API. No `.env` support yet — that is a future concern.

The platform config file (`platform/config/platform.php`) is **platform-writable**: the platform itself rewrites specific keys at runtime (e.g. `instance_id` after a host migration, `autoload` after a `composer dump-autoload`, `hostname` after a host change). These writes are atomic (tmp → rename) and OPcache-invalidated immediately.

### 2.2 Config file — `platform/config/platform.php`

This is the canonical platform config. Keys are `snake_case`. No PHP class references at the top level — the `Instance::` calls are resolved during the platform bootstrap and the result is stamped back into this file.

```php
<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
// WARNING: Some keys in this file are written by the platform itself (see
// "platform-managed" comments). Do not edit those keys by hand — your changes
// will be overwritten on next boot if the platform detects a drift.

return [

    // -------------------------------------------------------------------------
    // Identity — platform-managed. Rewritten on host migration or first boot.
    // -------------------------------------------------------------------------
    'id'       => 'bf0b7a6fe1dc0f33e62c091b9c7fe6e9',   // platform-managed
    'hostname' => 'my-host',                               // platform-managed
    'ip'       => '127.0.0.1',                             // platform-managed
    'uuid'     => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', // platform-managed
    'macs'     => '00:00:00:00:00:00',                     // platform-managed
    'instance_file_path' => 'platform/storage/instance',   // platform-managed
    'created'  => '2025-01-01T00:00:00+00:00',             // platform-managed (ISO 8601)

    // -------------------------------------------------------------------------
    // Autoload — platform-managed. Rewritten when composer.json vendor-dir
    // changes or after composer dump-autoload detects a new path.
    // -------------------------------------------------------------------------
    'autoload' => 'platform/dependencies/packagist/autoload.php', // platform-managed

    // -------------------------------------------------------------------------
    // Paths — edit freely. All paths are relative to the webapp root.
    // -------------------------------------------------------------------------
    'platform' => [
        'path'           => 'platform',
        'config_path'    => 'platform/config',
        'bootstrap_path' => 'platform/bootstrap',
        'settings_path'  => 'platform/settings',
        'storage_path'   => 'platform/storage',
        'telemetry_path' => 'platform/telemetry',
        'temporary_path' => 'platform/temporary',
    ],

    'dependencies' => [
        'path'             => 'platform/dependencies',
        'packagist_path'   => 'platform/dependencies/packagist',
        'node_modules_path'=> 'platform/dependencies/node_modules',
        'package_json'     => 'platform/dependencies/package.json',
    ],

    'modules' => [
        'path'          => 'modules',
        'manifest_path' => 'platform/temporary/modules_manifest.php',
    ],

    'public' => [
        'path'  => 'public',
        'index' => 'public/index.php',
    ],

    'js' => [
        'manager'          => 'npm',
        'package_json'     => 'platform/dependencies/package.json',
        'node_modules_path'=> 'platform/dependencies/node_modules',
    ],

    'telemetry' => [
        'enabled'   => true,
        'logs_path' => 'platform/telemetry/logs',
    ],

];
```

### 2.3 Config layering & merge

Config files are merged in this order (later layers win):

```
platform/config/platform.php          ← base (platform-managed keys included)
platform/config/local.php             ← optional local overrides (gitignored)
modules/{name}/config/{name}.php      ← module-level config (merged under ['modules']['{name}'])
```

The merge is a **deep recursive merge** (`array_replace_recursive`). Module configs are **namespaced** under their module key — they never pollute the root config namespace.

### 2.4 Platform-managed config rewrite

The platform rewrites config keys under these conditions:

| Key                  | Rewrite trigger                                           |
|----------------------|-----------------------------------------------------------|
| `hostname`           | Detected hostname differs from stored value on boot       |
| `ip`                 | Same as hostname                                          |
| `uuid`               | Machine UUID changed (e.g. VM cloned, host migrated)      |
| `macs`               | MAC addresses changed                                     |
| `id`                 | First boot only (generated once)                          |
| `autoload`           | `composer.json` vendor-dir differs from stamped value     |
| `instance_file_path` | Instance storage directory moved                          |

Rewrites are atomic: the platform writes to a `.tmp` sibling, then `rename()`s it over the target. OPcache is invalidated immediately after rename.

```php
// Internal platform method (not public API — shown for clarity)
ConfigWriter::rewrite('platform/config/platform.php', [
    'hostname' => gethostname(),
    'ip'       => gethostbyname(gethostname()),
    'uuid'     => InstanceFingerprint::machine_uuid(),
    'macs'     => InstanceFingerprint::macs(),
    'autoload' => $resolved_autoload_rel,
]);
```

### 2.5 `webapp()->config()` API

```php
// Read a key (dot-notation, returns mixed)
webapp()->config('platform.storage_path'): string;
webapp()->config('telemetry.enabled'): bool;
webapp()->config('autoload'): string;
webapp()->config(): array;  // returns the entire merged config tree

// Read with a default
webapp()->config('some.missing.key', 'default_value'): mixed;

// Write a key at runtime (persists to disk atomically, invalidates OPcache)
webapp()->config()->set('hostname', gethostname()): void;
webapp()->config()->set('autoload', $new_rel): void;

// Write multiple keys at once (single atomic write)
webapp()->config()->set_many([
    'hostname' => gethostname(),
    'ip'       => gethostbyname(gethostname()),
    'uuid'     => InstanceFingerprint::machine_uuid(),
]): void;

// Check if a key exists
webapp()->config()->has('telemetry.enabled'): bool;

// Reload from disk (after an external process rewrote the file)
webapp()->config()->reload(): void;
```

**Important:** `webapp()->config()->set()` writes to `platform/config/platform.php` only. Module configs are read-only at runtime — modules declare their defaults in their own config file and override them via module settings (see section 6).

---

## 3. Application core — `webapp()`

`webapp()` is the single global entry point. It returns a `WebApp` singleton annotated with `@method` docblocks (generated at `composer dump-autoload`) for full IDE autocompletion — no magic, no `__call`.

```php
/**
 * @method \Webkernel\Composables\ConfigComposable    config(?string $key = null, mixed $default = null)
 * @method \Webkernel\Composables\PlatformComposable  platform()
 * @method \Webkernel\Composables\StorageComposable   storage()
 * @method \Webkernel\Composables\CacheComposable     cache()
 * @method \Webkernel\Composables\RouteComposable     route()
 * @method \Webkernel\Composables\RequestComposable   request()
 * @method \Webkernel\Composables\ResponseComposable  response()
 * @method \Webkernel\Composables\MiddlewareComposable middleware()
 * @method \Webkernel\Composables\ModuleComposable    module(?string $name = null)
 * @method \Webkernel\Composables\PanelComposable     panel(?string $id = null)
 * @method \Webkernel\Composables\ClusterComposable   cluster(string $name)
 * @method \Webkernel\Composables\ResourceComposable  resource(string $class)
 * @method \Webkernel\Composables\PageComposable      page()
 * @method \Webkernel\Composables\ViewComposable      view(?string $template = null, array $data = [])
 * @method \Webkernel\Composables\AuthComposable      auth()
 * @method \Webkernel\Composables\AclComposable       acl(?string $module = null)
 */
final class WebApp { /* ... */ }
```

### Core methods

```php
webapp()->boot(): void;
webapp()->container(): Psr\Container\ContainerInterface;
webapp()->env(?string $key = null, mixed $default = null): mixed;  // future: .env support
webapp()->is_production(): bool;
webapp()->is_debug(): bool;
```

### Storage (PSR filesystem abstraction)

```php
webapp()->storage(): StorageComposable;
webapp()->storage()->file_path(string $relative_path): string;
webapp()->storage()->read(string $file): string;
webapp()->storage()->write(string $file, string $contents): bool;
webapp()->storage()->exists(string $file): bool;
webapp()->storage()->delete(string $file): bool;
```

### Cache (PSR-16 SimpleCache)

```php
webapp()->cache(): Psr\SimpleCache\CacheInterface;
webapp()->cache()->get(string $key, mixed $default = null): mixed;
webapp()->cache()->set(string $key, mixed $value, ?int $ttl = null): bool;
webapp()->cache()->remember(string $key, int $ttl, Closure $callback): mixed;
webapp()->cache()->delete(string $key): bool;
webapp()->cache()->clear(): bool;
```

---

## 4. Platform, instance & identity — `webapp()->platform()`

Manages the physical host, machine fingerprint, global administration, app owners, and telemetry.

```php
// ---- Machine fingerprint & instance files -----------------------------------
webapp()->platform()->instance()->machine_uuid(): string;
webapp()->platform()->instance()->macs(): string;
webapp()->platform()->instance()->file_path(): string;
webapp()->platform()->instance()->fingerprint(): string;
webapp()->platform()->instance()->parts(): array;

// ---- Global System Admin Panel ---------------------------------------------
// The SAP administers all modules; it is NOT a sibling of modules.
webapp()->platform()->system_admin()->is_active(): bool;
webapp()->platform()->system_admin()->audit_logs(): array;
webapp()->platform()->system_admin()->register_panel(string $panel_class): void;

// ---- App owners (root-level principals) ------------------------------------
webapp()->platform()->owners()->list(): array;
webapp()->platform()->owners()->current(): ?AppOwner;
webapp()->platform()->owners()->is_owner(int|string $user_id): bool;

// ---- Telemetry & observability ---------------------------------------------
webapp()->platform()->telemetry()->access_log(): AccessLogWriter;
webapp()->platform()->telemetry()->metrics(): MetricsCollector;
webapp()->platform()->telemetry()->profile(Closure $task): ProfileResult;
```

---

## 5. HTTP kernel, routing & middleware

### Routing (MarkBased dispatcher)

```php
webapp()->route()->get(string $uri, Closure|array $action): Route;
webapp()->route()->post(string $uri, Closure|array $action): Route;
webapp()->route()->put(string $uri, Closure|array $action): Route;
webapp()->route()->patch(string $uri, Closure|array $action): Route;
webapp()->route()->delete(string $uri, Closure|array $action): Route;
webapp()->route()->group(array $attributes, Closure $routes): void;
webapp()->route()->dispatch(ServerRequestInterface $request): ResponseInterface;
```

### HTTP request (PSR-7)

```php
webapp()->request()->uri(): string;
webapp()->request()->method(): string;
webapp()->request()->header(string $name): ?string;
webapp()->request()->input(?string $key = null, mixed $default = null): mixed;
webapp()->request()->file(string $key): ?UploadedFileInterface;
webapp()->request()->psr(): ServerRequestInterface;
```

### HTTP response (PSR-7)

```php
webapp()->response()->json(array $data, int $status = 200): ResponseInterface;
webapp()->response()->html(string $content, int $status = 200): ResponseInterface;
webapp()->response()->redirect(string $url, int $status = 302): ResponseInterface;
webapp()->response()->no_content(): ResponseInterface;
```

### Middleware pipeline

```php
webapp()->middleware()->with_middleware(array $middlewares): void;
webapp()->middleware()->stack(): array;
```

---

## 6. Business & UI hierarchy

The hierarchy is: **Platform → Modules → Admin Panels → Clusters → Resources → Pages → Components**.

The System Admin Panel sits *above* Modules (it administers them). Modules are *contained inside* the Platform. The SAP is not a Module.

```php
// ---- Modules ---------------------------------------------------------------
webapp()->module(): ModuleComposable;            // returns module registry
webapp()->module('invoicing'): ModuleComposable; // returns a specific module
webapp()->module()->all(): array;
webapp()->module()->is_installed(string $name): bool;
webapp()->module()->register(string $module_class): void;
webapp()->module('invoicing')->config(): array;           // module-scoped config
webapp()->module('invoicing')->config('vat_rate'): mixed; // dot-notation read

// ---- Admin panels ----------------------------------------------------------
webapp()->panel(): PanelComposable;
webapp()->panel('sales'): PanelComposable;
webapp()->panel()->current(): AdminPanel;
webapp()->panel()->clusters(): array;

// ---- Clusters --------------------------------------------------------------
webapp()->cluster(string $name): ClusterComposable;
webapp()->cluster('finance')->resources(): array;

// ---- Resources (business entities) ----------------------------------------
webapp()->resource(string $class): ResourceComposable;
webapp()->resource(InvoiceResource::class)->pages(): array;
webapp()->resource(InvoiceResource::class)->query(): Builder;

// ---- Pages -----------------------------------------------------------------
webapp()->page(): PageComposable;
webapp()->page()->components(): array;
webapp()->page()->render(): string;

// ---- View engine (zero-overhead) -------------------------------------------
webapp()->view(): ViewComposable;
webapp()->view('admin/dashboard', $data): ViewComposable;
webapp()->view()->render(string $template, array $data = []): string;
webapp()->view()->share(string $key, mixed $value): void;
webapp()->view()->exists(string $template): bool;
```

---

## 7. Authorization & security — module-scoped ACL

### Design principle

Authorization in Webkernel is **module-scoped by default**. The platform knows the containment topology (`platform → module → panel → cluster → resource → page → component`) and resolves the correct permission namespace automatically from the call site.

You do **not** pass a module name to `webapp()->acl()` when you are already inside a module context — the platform infers it. You **can** pass a module name explicitly to check permissions cross-module (e.g. from the System Admin Panel).

There is no global flat permission table. Permissions are always namespaced: `{module}.{resource}.{action}` or `{module}.{panel}.{cluster}.{resource}.{action}` for fine-grained rules.

### Authentication

```php
webapp()->auth()->user(): ?UserInterface;
webapp()->auth()->check(): bool;
webapp()->auth()->id(): string|int|null;
webapp()->auth()->login(UserInterface $user): void;
webapp()->auth()->logout(): void;
```

### Module-scoped ACL (default — inferred from call site)

When called from inside a module context (controller, resource, page, component), the platform resolves the module automatically:

```php
// Inside InvoiceResource (module: invoicing) — module is inferred
webapp()->acl()->can('export'): bool;
webapp()->acl()->can('edit', $invoice): bool;
webapp()->acl()->cannot('delete', $invoice): bool;
webapp()->acl()->authorize('approve', $invoice): void;  // throws on denial

// Component-level enforcement (inferred module + component id)
webapp()->acl()->enforce_component_access(string $component_id): bool;
```

### Explicit module scoping (cross-module checks)

When the caller is not inside a module context (e.g. System Admin Panel, CLI, telemetry):

```php
// Explicit module name
webapp()->acl('invoicing')->can('export'): bool;
webapp()->acl('invoicing')->can('edit', $invoice): bool;
webapp()->acl('invoicing')->authorize('approve', $invoice): void;

// Check a permission across ALL modules (SAP use case)
webapp()->acl()->can_any('invoicing.export', 'reporting.export'): bool;
```

### Permission naming convention

```
{module_name}.{action}                         // simple: invoicing.export
{module_name}.{resource}.{action}              // resource-scoped: invoicing.invoice.delete
{module_name}.{panel}.{resource}.{action}      // panel-scoped: invoicing.billing.invoice.approve
platform.{action}                              // platform-level: platform.owner.add
```

The ACL layer resolves the full permission name from the call site context + any explicit overrides. Static analysis can verify the full chain because no magic is involved.

---

## 8. Interactive CLI — `webterminal()`

Dedicated to command-line tools, installers, generators, and admin scripts. Not a console kernel — it is a thin, zero-dependency prompt and output layer designed to work in CI/CD via `fake()`.

### Interactive prompts

```php
webterminal()->text(string $label, ?string $placeholder = null, bool $required = false): string;
webterminal()->secret(string $label, bool $required = false): string;
webterminal()->select(string $label, array $options, mixed $default = null): mixed;
webterminal()->multi_select(string $label, array $options, array $default = []): array;
webterminal()->confirm(string $label, bool $default = false): bool;
```

### Terminal output

```php
webterminal()->info(string $message): void;
webterminal()->success(string $message): void;
webterminal()->warning(string $message): void;
webterminal()->error(string $message): void;
webterminal()->table(array $headers, array $rows): void;
webterminal()->spinner(Closure $task, string $title = ''): mixed;
webterminal()->progress(int $total_steps, Closure $callback): void;
```

### Test / CI automation (non-TTY)

```php
// Inject predefined answers — no TTY required, no heavy console kernel loaded
webterminal()->fake(array $answers): void;

// Example
webterminal()->fake(['prod', true, 'admin@example.com']);
$env  = webterminal()->select('Environment', ['dev', 'prod']); // returns 'prod'
$conf = webterminal()->confirm('Enable telemetry?');            // returns true
$mail = webterminal()->text('Admin email');                     // returns 'admin@example.com'
```

---

## 9. Fast-boot & config rewrite rules

### Current fast-boot problem

`platform/bootstrap/fast-boot.php` currently hard-codes paths like `platform/storage/instance/data/autoload.php` and `platform/temporary`. These paths are also declared in `platform/config/platform.php`. They are **duplicated** — a drift risk.

### Fix: fast-boot reads config

After the minimal bootstrap (before any composable is loaded), fast-boot reads `platform/config/platform.php` directly via a raw `require` — no composable, no container, no overhead. This is the only place a raw `require` of the config file is acceptable.

```php
// platform/bootstrap/fast-boot.php — revised hot path
$webapp_path = dirname(__DIR__, 2); // adjust depth to actual tree
$config_path = $webapp_path . '/platform/config/platform.php';

// Raw require — no composable, no container. Config must be a plain array.
$platform_config = is_file($config_path) ? require $config_path : [];

$autoload_rel = $platform_config['autoload'] ?? 'vendor/autoload.php';
$autoload_abs = $webapp_path . '/' . $autoload_rel;

if (
    is_string($autoload_rel) &&
    $autoload_rel !== '' &&
    !str_contains($autoload_rel, '..') &&
    is_file($autoload_abs)
) {
    require $autoload_abs;
    return; // ← hot path exits here
}

// Miss path: discover, stamp config, optionally run composer install
// (see full miss-path implementation below)
```

### Config rewrite from fast-boot (miss path)

When fast-boot resolves a new autoload path (after `composer install` or a vendor-dir change), it rewrites the `autoload` key in `platform/config/platform.php` atomically:

```php
// After $rel is resolved in the miss path:
ConfigWriter::atomic_rewrite(
    $config_path,
    ['autoload' => $rel]
);
// OPcache is invalidated inside ConfigWriter::atomic_rewrite()
```

`ConfigWriter::atomic_rewrite()` is a standalone static utility (no container, no composable) that:
1. Reads the current config array via `require`.
2. Merges the new keys with `array_replace_recursive`.
3. Exports the merged array with `var_export`.
4. Writes to a `.tmp` sibling file with `LOCK_EX`.
5. Renames the `.tmp` file over the target atomically.
6. Calls `opcache_invalidate($path, true)` if OPcache is available.

### Platform-writable keys — complete list

| Key                    | Written by              | Trigger                                               |
|------------------------|-------------------------|-------------------------------------------------------|
| `autoload`             | fast-boot miss path     | vendor-dir change or composer install                 |
| `hostname`             | boot identity check     | `gethostname()` differs from stored value             |
| `ip`                   | boot identity check     | IP differs from stored value                          |
| `uuid`                 | boot identity check     | Machine UUID changed (VM migration, host swap)        |
| `macs`                 | boot identity check     | MAC addresses changed                                 |
| `id`                   | first-boot initialiser  | File missing or `id` key absent                       |
| `created`              | first-boot initialiser  | File missing or `created` key absent                  |
| `instance_file_path`   | instance bootstrap      | Storage directory relocated                           |

All writes go through `ConfigWriter::atomic_rewrite()`. No key is ever written by ad-hoc `file_put_contents` calls scattered around the codebase.

---

## 10. Chaining reference table

| Entry point              | Full chain example                                                    |
|--------------------------|-----------------------------------------------------------------------|
| Config read              | `webapp()->config('platform.storage_path')`                          |
| Config write             | `webapp()->config()->set('hostname', gethostname())`                 |
| Host & identity          | `webapp()->platform()->instance()->machine_uuid()`                   |
| Telemetry logs           | `webapp()->platform()->telemetry()->access_log()`                    |
| System Admin Panel       | `webapp()->platform()->system_admin()->audit_logs()`                 |
| App owners               | `webapp()->platform()->owners()->is_owner($user_id)`                 |
| Module config            | `webapp()->module('invoicing')->config('vat_rate')`                  |
| Admin panels             | `webapp()->panel('sales')->cluster('finance')->resources()`          |
| ACL inferred (in module) | `webapp()->acl()->can('edit', $invoice)`                             |
| ACL explicit (cross)     | `webapp()->acl('invoicing')->authorize('approve', $invoice)`         |
| CLI prompt               | `webterminal()->select('Environment', ['dev', 'prod'])`              |
| View render              | `webapp()->view()->render('admin/dashboard', $data)`                 |
| HTTP response            | `webapp()->response()->json(['ok' => true])`                         |
| Cache remember           | `webapp()->cache()->remember('stats', 60, fn() => Stats::compute())` |
| Storage read             | `webapp()->storage()->read('platform/storage/instance/data/id.txt')` |

---

## 11. Design rationale

### Performance & OPcache

Lazy-loading via the `api_name => FQCN` map (generated at `composer dump-autoload`) means a request that only calls `webapp()->response()` never loads `PlatformComposable`, `AuthComposable`, or any module. Zero allocation for unused composables on the hot path.

All composable instances are cached in the container (`singleton` / `scoped`) after first resolution. Chaining `webapp()->platform()->instance()->machine_uuid()` allocates three object references on the first call — on subsequent calls within the same request it reads three already-resolved references from memory. No GC pressure.

### Static analysis & zero magic

Banning `__call` / `__callStatic` in favour of explicit `ComposableContract` implementations means PHPStan and Psalm can analyse 100% of the chain without third-party plugins. The `@method` docblocks on `WebApp` are generated — not hand-written — so they are always in sync with the registered composables.

### Config as a first-class primitive

`webapp()->config()` is resolved before any other composable. It does not depend on the container. The container depends on it (service paths, lifetimes, and module manifests all come from config). This ordering is explicit and enforced, not emergent.

### Module-scoped ACL by default

The platform knows the containment topology at boot (from the module manifest). When `webapp()->acl()->can('edit', $invoice)` is called from inside `InvoiceResource`, the platform resolves the caller's module from the call-site context — no annotation, no string argument required. Explicit module arguments (`webapp()->acl('invoicing')`) are only needed when crossing module boundaries (System Admin Panel, CLI scripts, cross-module policies).

Permission names are always fully qualified (`{module}.{resource}.{action}`). There is no global flat permission table. This eliminates the class of bugs where two modules define a permission named `export` and one silently shadows the other.

### CLI testability

`webterminal()->fake([...])` injects predefined answers into the prompt layer. No heavy console kernel. No Symfony Command bus. Tests run headless in CI/CD with a single call before the script under test. The fake is consumed in FIFO order and panics loudly if the answer list is exhausted — no silent empty string defaults.

### Config writability without drift

Config keys that the platform manages are declared in one place (`platform/config/platform.php`) and written through one utility (`ConfigWriter::atomic_rewrite()`). Fast-boot reads them directly via `require` — no abstraction overhead on the hot path. The miss path rewrites them through the same utility. There is no second source of truth for these values.

---

*Webkernel — zero magic, zero unnecessary overhead, total control over autoloading.*
*(c) 2025 – 2027 Numerimondes, El Moumen Yassine*
