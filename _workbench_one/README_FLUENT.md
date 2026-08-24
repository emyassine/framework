# Webkernel — Fluent API Reference (`README_FLUENT.md`)
> **Status:** canonical reference for the `webapp()` / `webterminal()` fluent chain.
> To be added : `webapi()` for the apis
> All methods use strict `snake_case`. No camelCase anywhere — not in method names, not in config keys, not in array keys.
> This document is the single source of truth during the current refactor phase.

> **Performance contract:** The entire `webapp()` chain — boot, config resolution, routing, middleware, ACL/permission resolution, view directive expansion, and response dispatch — must complete in **under 1 ms kernel CPU with no I/O** and **under 10 ms for a full application response with I/O**. This is a hard target. Every design decision in this file is a consequence of it. No subsystem is exempt: auth, ACL, and permissions are inside the budget, not around it.

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
11. [Design rationale & performance](#11-design-rationale--performance)

---

## 1. ComposableContract — base structure
Every public API segment implements `ComposableContract`. Lazy-loading is driven by the `api_name => FQCN` map generated at `composer dump-autoload`. No magic methods (`__call`, `__callStatic`) are used anywhere.

**Why no magic methods:** `__call` / `__callStatic` add a dynamic dispatch step on every chain call. Banning them keeps the hot path to direct method calls resolvable by OPcache and statically analysable by PHPStan/Psalm without plugins. Every nanosecond on the hot path compounds across the request.

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
| Lifetime    | Behaviour                                                     | When to use                                          | Performance note |
|-------------|---------------------------------------------------------------|------------------------------------------------------|-----------------|
| `singleton` | Single instance for the entire process lifetime               | Platform, config, cache, router                      | Resolved once, read from memory on every subsequent call |
| `scoped`    | Single instance per HTTP request, reset between requests      | Auth session, current panel, request-scoped services | Resolved once per request |
| `bind`      | New instance on every `container()->get()` call               | Stateful builders, one-shot jobs                     | Allocates on every call — avoid on the hot path |

---

## 2. Config system — `webapp()->config()`

### 2.1 Overview
The config system is the **first thing resolved** by `webapp()`, before any composable. It reads layered PHP config files, merges them, and exposes a typed read/write API. No `.env` support yet — that is a future concern.

Config is a plain PHP array `require`d once and cached by OPcache. Reading a config key at request time is an array key lookup — no file I/O, no parsing, no reflection. This is intentional: config resolution must cost nothing on the hot path.

The platform config file (`config/platform.php`) is **platform-writable**: the platform itself rewrites specific keys at runtime (e.g. `instance_id` after a host migration, `autoload` after a `composer dump-autoload`, `hostname` after a host change). These writes are atomic (tmp → rename) and OPcache-invalidated immediately. Writes happen outside the request hot path — never during a live request.

### 2.2 Config file — `config/platform.php`
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
config/platform.php       			   <- base (platform-managed keys included)
platform/platform-overrides.php         <- optional local overrides (gitignored)
modules/{name}/config/{name}.php     <- module-level config (merged under ['modules']['{name}'])
```
The merge is a **deep recursive merge** (`array_replace_recursive`). Module configs are **namespaced** under their module key — they never pollute the root config namespace.

The merge runs once at boot and the result is held in memory for the lifetime of the request. Subsequent `webapp()->config('some.key')` calls are array lookups against that in-memory tree — no re-merge, no disk I/O.

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

Rewrites are atomic: the platform writes to a `.tmp` sibling, then `rename()`s it over the target. OPcache is invalidated immediately after rename. Rewrites never happen during a live request — only during boot or the fast-boot miss path.

```php
// Internal platform method (not public API — shown for clarity)
ConfigWriter::rewrite('config/platform.php', [
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

**Important:** `webapp()->config()->set()` writes to `config/platform.php` only. Module configs are read-only at runtime — modules declare their defaults in their own config file and override them via module settings (see section 6).

---

## 3. Application core — `webapp()`
`webapp()` is the single global entry point. It returns a `WebApp` singleton annotated with `@method` docblocks (generated at `composer dump-autoload`) for full IDE autocompletion — no magic, no `__call`.

```php
/**
 * @method \Webkernel\Composables\ConfigComposable     config(?string $key = null, mixed $default = null)
 * @method \Webkernel\Composables\PlatformComposable   platform()
 * @method \Webkernel\Composables\StorageComposable    storage()
 * @method \Webkernel\Composables\CacheComposable      cache()
 * @method \Webkernel\Composables\RouteComposable      route()
 * @method \Webkernel\Composables\RequestComposable    request()
 * @method \Webkernel\Composables\ResponseComposable   response()
 * @method \Webkernel\Composables\MiddlewareComposable middleware()
 * @method \Webkernel\Composables\ModuleComposable     module(?string $name = null)
 * @method \Webkernel\Composables\PanelComposable      panel(?string $id = null)
 * @method \Webkernel\Composables\ClusterComposable    cluster(string $name)
 * @method \Webkernel\Composables\ResourceComposable   resource(string $class)
 * @method \Webkernel\Composables\PageComposable       page()
 * @method \Webkernel\Composables\ViewComposable       view(?string $template = null, array $data = [])
 * @method \Webkernel\Composables\AuthComposable       auth()
 * @method \Webkernel\Composables\AclComposable        acl(?string $module = null)
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
webapp()->route()->query(string $uri, Closure|array $action): Route;
webapp()->route()->group(array $attributes, Closure $routes): void;
webapp()->route()->dispatch(ServerRequestInterface $request): ResponseInterface;
```

### HTTP request (PSR-7)

The IETF published RFC 10008 in June 2026, defining a new general-purpose HTTP method called QUERY.
It is the first new HTTP method added in over two decades.
It acts as a bridge between GET and POST, allowing safe, cacheable read requests to carry a complex payload in the request body.

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

Middleware evaluation happens inside the < 1 ms kernel CPU budget. The stack is recorded at boot and executed as a flat pipeline — no dynamic discovery, no service-locator lookups at pipeline time.

---

## 6. Business & UI hierarchy
The hierarchy is: **Platform → Modules → Admin Panels → Clusters → Resources → Pages → Components**.
The System Admin Panel sits *above* Modules (it administers them). Modules are *contained inside* the Platform. The SAP is not a Module.

### 6.1 Modules
```php
webapp()->module(): ModuleComposable;            // returns module registry
webapp()->module('invoicing'): ModuleComposable; // returns a specific module
webapp()->module()->all(): array;
webapp()->module()->is_installed(string $name): bool;
webapp()->module()->register(string $module_class): void;
webapp()->module('invoicing')->config(): array;           // module-scoped config
webapp()->module('invoicing')->config('vat_rate'): mixed; // dot-notation read
```

### 6.2 Panel taxonomy & hierarchy
Panels are explicitly categorized as either `platform` or `module` scope.
- **Platform panels** administer the core platform or cross-module concerns.
- **Module panels** administer domain capabilities strictly inside a module.

```php
// ---- Active panel ----------------------------------------------------------
webapp()->panel(): PanelComposable;                        // returns currently active panel
webapp()->panel()->type(): string;                         // returns 'platform' | 'module'
webapp()->panel()->is_platform_panel(): bool;              // true if platform-scoped panel
webapp()->panel()->is_module_panel(): bool;                // true if module-scoped panel
webapp()->panel()->module_name(): ?string;                 // returns module name or null
// ---- Referencing specific panels -------------------------------------------
webapp()->panel('platform.system_admin'): PanelComposable; // platform panel (explicit)
webapp()->panel('invoicing.sales'): PanelComposable;       // module panel (explicit)
webapp()->panel('sales'): PanelComposable;                 // module panel (inferred from current module context)
// ---- Panel navigation ------------------------------------------------------
webapp()->panel()->current(): AdminPanel;
webapp()->panel()->clusters(): array;
```

**Clarification — panel ID format:**
A fully qualified panel ID uses `{scope}.{panel_name}` notation. When the current module context is unambiguous (e.g. inside a module controller), the module prefix can be omitted and is inferred automatically. Always use the fully qualified form in cross-module or platform-level code to avoid silent scope confusion.

### 6.3 Clusters, resources, pages & components
```php
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

> **Performance note:** ACL resolution — including module inference from call site, permission name expansion, and view directive evaluation — is **inside the < 1 ms kernel CPU budget**. This is non-negotiable. The design choices below (compile-time expansion, static manifest, no flat global table) are direct consequences of that target.

### Design principle
Authorization in Webkernel is **module-scoped by default**. The platform knows the containment topology (`platform → module → panel → cluster → resource → page → component`) and resolves the correct permission namespace automatically from the call site.

You do **not** pass a module name to `webapp()->acl()` when you are already inside a module context — the platform infers it. You **can** pass a module name explicitly to check permissions cross-module (e.g. from the System Admin Panel).

There is no global flat permission table. Permissions are always namespaced: `{module}.{resource}.{action}` or `{module}.{panel}.{cluster}.{resource}.{action}` for fine-grained rules. This eliminates the class of bugs where two modules define a permission named `export` and one silently shadows the other — and keeps the lookup a single keyed array access rather than a table scan.

### 7.1 Authentication
```php
webapp()->auth()->user(): ?UserInterface;
webapp()->auth()->check(): bool;
webapp()->auth()->id(): string|int|null;
webapp()->auth()->login(UserInterface $user): void;
webapp()->auth()->logout(): void;
```

### 7.2 Module-scoped ACL (default — inferred from call site)
When called from inside a module context (controller, resource, page, component), the platform resolves the module automatically. The resolution uses the call-site topology already held in memory from boot — no additional I/O or reflection.

```php
// Inside InvoiceResource (module: invoicing) — module is inferred
webapp()->acl()->can('export'): bool;
webapp()->acl()->can('edit', $invoice): bool;
webapp()->acl()->cannot('delete', $invoice): bool;
webapp()->acl()->authorize('approve', $invoice): void;  // throws on denial
// Component-level enforcement (inferred module + component id)
webapp()->acl()->enforce_component_access(string $component_id): bool;
```

### 7.3 Explicit module scoping (cross-module checks)
When the caller is not inside a module context (e.g. System Admin Panel, CLI, telemetry):

```php
// Explicit module name
webapp()->acl('invoicing')->can('export'): bool;
webapp()->acl('invoicing')->can('edit', $invoice): bool;
webapp()->acl('invoicing')->authorize('approve', $invoice): void;
// Check a permission across ALL modules (SAP use case)
webapp()->acl()->can_any('invoicing.export', 'reporting.export'): bool;
```

### 7.4 Permission naming convention
```
{module_name}.{action}                         // simple:        invoicing.export
{module_name}.{resource}.{action}              // resource:      invoicing.invoice.delete
{module_name}.{panel}.{resource}.{action}      // panel-scoped:  invoicing.billing.invoice.approve
platform.{action}                              // platform-wide: platform.owner.add
```

The ACL layer resolves the full permission name from the call site context plus any explicit overrides. Static analysis can verify the full chain because no magic is involved.

### 7.5 View directives & on-the-fly ACL resolution

> **Compile-time, not run-time.** Permission name expansion in view directives happens at compile time. The compiled PHP output contains fully qualified `webapp()->acl()` calls. At render time, the name expansion has already happened — the cost is zero. This is how view-level ACL stays inside the kernel performance budget.

When using template directives (`@can`, `@cannot`, `@can_any`) in Webkernel views, the view engine auto-detects the active rendering context (`platform` or `module`) and passes short permission names directly to `webapp()->acl()`.

**Short names automatically inherit the context scope** — there is no need to prefix them with the module name inside a module view.

#### View directives syntax
```html
{{-- Short permission names automatically inherit context scope --}}
@can('knock_head')
    <button>Knock Head</button>
@endcan
@cannot('destroy_world')
    <p>Access Restricted</p>
@endcannot
@can_any(['export_csv', 'export_pdf'])
    <a href="/export">Export Data</a>
@endcan_any
```

#### Directives resolution algorithm
```
View Directives Engine
   |
   |-- 1. Identify View Context --> Module View ("invoicing") OR Platform View ("platform")
   |
   |-- 2. Expand Permission -----> "knock_head" becomes "invoicing.knock_head" or "platform.knock_head"
   |          (happens at COMPILE TIME — zero cost at render time)
   |
   |-- 3. Check ACL Manifest ----> Is "invoicing.knock_head" registered?
   |        |-- YES ------------> Evaluate active user gate/role bindings.
   |        `-- NO  ------------> Trigger auto-registration on the fly.
   |
   `-- 4. On-The-Fly Execution --> Register permission dynamically -> Apply fallback policy gate.
```

#### On-the-fly permission API
If a permission evaluated in a view or controller is not present in the static module manifest, `webapp()->acl()` creates and registers it at runtime instead of throwing an undefined-permission exception.

```php
// Toggle on-the-fly creation
webapp()->acl()->enable_on_the_fly_creation(bool $enabled = true): void;
webapp()->acl()->is_on_the_fly_enabled(): bool;
// Manually register an on-the-fly permission with a custom fallback gate
webapp()->acl()->register_on_the_fly(
    string $permission_name,
    ?Closure $fallback_evaluator = null
): void;
// Set a default evaluator for all dynamically created permissions
webapp()->acl()->set_on_the_fly_fallback(function (string $permission, ?UserInterface $user): bool {
    // Example: grant dynamically created permissions to platform owners by default
    if (webapp()->platform()->owners()->is_owner($user?->id())) {
        return true;
    }
    return $user?->has_role('module_admin') ?? false;
});
```

**Clarification — on-the-fly vs. static manifest:**
On-the-fly creation is a safety net, not the preferred path. The static module ACL manifest (declared in the module's registration) is the authoritative source. On-the-fly permissions are useful during development and for edge-case runtime directives, but they should be promoted to the static manifest before shipping to production. **Disable on-the-fly creation in production** — it adds a manifest-miss branch on the ACL hot path. Enable it only in development or staging environments where ACL manifests are still being iterated.

#### Compiled output
The view compiler replaces template directives with deterministic PHP execution calls. The compiled form is deterministic and statically analysable — no string evaluation, no `eval()`.

```php
// @can('knock_head') inside module 'invoicing' compiles to:
if (webapp()->acl()->can('knock_head')):
// Standard view compilation expansion under the hood:
if (webapp()->acl('invoicing')->can('invoicing.knock_head')):
```

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

**Clarification — fake exhaustion:**
`fake()` consumes answers in FIFO order. If the script requests more prompts than answers were supplied, `webterminal()` throws immediately with a clear message indicating which prompt was unanswered. There are no silent empty-string fallbacks.

---

## 9. Fast-boot & config rewrite rules

### Current fast-boot problem
`platform/bootstrap/fast-boot.php` currently hard-codes paths like `platform/storage/instance/data/autoload.php` and `platform/temporary`. These paths are also declared in `config/platform.php`. They are **duplicated** — a drift risk.

### Fix: fast-boot reads config
After the minimal bootstrap (before any composable is loaded), fast-boot reads `config/platform.php` directly via a raw `require` — no composable, no container, no overhead. This is the only place a raw `require` of the config file is acceptable.

The hot path — when the autoload path is already correct in config — exits after a single `require` of the autoload file. The entire fast-boot overhead on the hot path is: one `require` of the config array (OPcache hit) + one array key read + one `require` of the autoload file (OPcache hit). This is measured in microseconds, well inside the < 1 ms kernel budget.

```php
// platform/bootstrap/fast-boot.php — revised hot path
$webapp_path = dirname(__DIR__, 2); // adjust depth to actual tree
$config_path = $webapp_path . '/config/platform.php';
// Raw require — no composable, no container. Config must be a plain array.
$platform_config = is_file($config_path) ? require $config_path : [];
$autoload_rel    = $platform_config['autoload'] ?? 'vendor/autoload.php';
$autoload_abs    = $webapp_path . '/' . $autoload_rel;
if (
    is_string($autoload_rel) &&
    $autoload_rel !== '' &&
    !str_contains($autoload_rel, '..') &&
    is_file($autoload_abs)
) {
    require $autoload_abs;
    return; // <- hot path exits here
}
// Miss path: discover, stamp config, optionally run composer install
// (see full miss-path implementation below)
```

### Config rewrite from fast-boot (miss path)
When fast-boot resolves a new autoload path (after `composer install` or a vendor-dir change), it rewrites the `autoload` key in `config/platform.php` atomically:

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

Miss-path rewrites happen at boot, before any request is served. They do not run during live request handling.

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

All writes go through `ConfigWriter::atomic_rewrite()`. No key is ever written by ad-hoc `file_put_contents` calls scattered around the codebase. All writes happen outside the live request path.

---

## 10. Chaining reference table

| Entry point                   | Full chain example                                                          |
|-------------------------------|-----------------------------------------------------------------------------|
| Config read                   | `webapp()->config('platform.storage_path')`                                 |
| Config write                  | `webapp()->config()->set('hostname', gethostname())`                        |
| Host & identity               | `webapp()->platform()->instance()->machine_uuid()`                          |
| Telemetry logs                | `webapp()->platform()->telemetry()->access_log()`                           |
| System Admin Panel            | `webapp()->platform()->system_admin()->audit_logs()`                        |
| App owners                    | `webapp()->platform()->owners()->is_owner($user_id)`                        |
| Module config                 | `webapp()->module('invoicing')->config('vat_rate')`                         |
| Panel context check           | `webapp()->panel()->is_platform_panel()`                                    |
| Module panel lookup           | `webapp()->panel('invoicing.sales')->module_name()`                         |
| Panel clusters                | `webapp()->panel('sales')->clusters()`                                       |
| ACL inferred (in module)      | `webapp()->acl()->can('edit', $invoice)`                                    |
| ACL explicit (cross-module)   | `webapp()->acl('invoicing')->authorize('approve', $invoice)`                |
| View permission check         | `webapp()->acl()->can('knock_head')`                                        |
| Dynamic ACL fallback          | `webapp()->acl()->set_on_the_fly_fallback($callback)`                       |
| CLI prompt                    | `webterminal()->select('Environment', ['dev', 'prod'])`                     |
| View render                   | `webapp()->view()->render('admin/dashboard', $data)`                        |
| HTTP response                 | `webapp()->response()->json(['ok' => true])`                                |
| Cache remember                | `webapp()->cache()->remember('stats', 60, fn() => Stats::compute())`        |
| Storage read                  | `webapp()->storage()->read('platform/storage/instance/data/id.txt')`        |

---

## 11. Design rationale & performance

### The target, stated plainly
**< 1 ms kernel CPU with no I/O. < 10 ms full application response with I/O.**

These targets cover the full kernel path: boot, config resolution, routing, middleware evaluation, auth checks, ACL/permission resolution, view directive expansion, and response dispatch. Nothing is exempted. Every design decision below is a direct consequence of these numbers.

Measured baselines (PHP 8.4, OPcache on, JIT off, localhost): Hello World ~0.02 ms kernel path; dashboard render ~0.33 ms. The headroom is intentional — module code, view rendering, and I/O all land in the < 10 ms full-response budget, not the kernel budget.

### OPcache is mandatory
Without OPcache the targets are not achievable. `config/platform.php` is a plain PHP array — it is `require`d once per process and cached by OPcache thereafter. Config reads at request time are in-memory array key lookups. Config rewrites call `opcache_invalidate()` immediately after the atomic rename.

### Lazy composable loading
The `api_name => FQCN` map is generated at `composer dump-autoload`. A request that only calls `webapp()->response()` never loads `PlatformComposable`, `AuthComposable`, `AclComposable`, or any module. Zero allocation for unused composables on the hot path.

### Singleton / scoped caching
Composable instances are cached in the container after first resolution. Chaining `webapp()->platform()->instance()->machine_uuid()` costs three object-reference reads on every call after the first within the same request. No repeated container lookups, no GC pressure.

### No magic methods
`__call` / `__callStatic` add a dynamic dispatch step on every chain call. Banning them keeps the hot path to direct OPcache-resolvable method calls. PHPStan and Psalm analyse 100 % of the chain without third-party plugins. The `@method` docblocks on `WebApp` are generated — not hand-written — so they are always in sync with the registered composables.

### Compile-time ACL expansion
View directive permission names (`@can('export')`) are expanded to fully qualified `webapp()->acl()` calls at compile time. At render time, name expansion has already happened — the cost at render time is exactly one `webapp()->acl()->can('invoicing.export')` call, which is a keyed lookup against an in-memory permission map. Zero runtime name-resolution overhead.

### No global flat permission table
Permissions are always namespaced (`{module}.{resource}.{action}`). There is no global table to scan. A permission check is a keyed lookup in the module's ACL map, held in memory from boot. Two modules that both define `export` cannot shadow each other.

### Config as a first-class primitive
`webapp()->config()` is resolved before any other composable. It does not depend on the container. The container depends on it (service paths, lifetimes, and module manifests all come from config). This ordering is explicit and enforced, not emergent.

### Panel taxonomy as a first-class concept
Panels are not generic UI containers — they are scoped administrative workspaces with an explicit `platform` or `module` classification baked in. `is_platform_panel()` and `is_module_panel()` are typed boolean predicates. A panel that does not declare its scope fails at registration time, not silently at runtime.

### Config writability without drift
Config keys that the platform manages are declared in one place (`config/platform.php`) and written through one utility (`ConfigWriter::atomic_rewrite()`). Fast-boot reads them directly via `require` — no abstraction overhead on the hot path. The miss path rewrites them through the same utility. There is no second source of truth for these values. All writes happen outside the live request path.

### CLI testability
`webterminal()->fake([...])` injects predefined answers into the prompt layer. No heavy console kernel. No Symfony Command bus. Tests run headless in CI/CD with a single call before the script under test. The fake is consumed in FIFO order and panics loudly if the answer list is exhausted — no silent empty string defaults.

---

*Webkernel — zero magic, zero unnecessary overhead, total control over autoloading.*
*(c) 2025 – 2027 Numerimondes, El Moumen Yassine*
