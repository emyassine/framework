# Web Application & Webkernel Request Handling Strategy

## Overview

A high-performance enterprise application must handle diverse request contexts with predictable, sub-millisecond execution target efficiency (< 1 ms CPU budget for kernel execution). Traffic is categorized into distinct paths:

* **HTTP Web Requests** (Server-rendered HTML pages)
* **API Requests** (Stateless, programmatic JSON endpoints)
* **Machine & AI Endpoints** (`/llm.txt`, `/md` or `.md` variants for LLM crawlers and headless clients)
* **Syndication Endpoints** (`/rss`, `/atom` XML feeds)
* **Real-Time Protocols** (Persistent connections via WebSockets / SSE)
* **CLI Commands** (Local execution, cron tasks, queue workers)

Each category enforces dedicated routing, lazy memory allocation, and tailored security pipelines.

---

## Project Layout

```
platform/
├── dependencies/
│   └── packagist/          # All Composer dependencies (webkernel packages + third-party)
│       └── vendor/         # Composer autoloader lives here
modules/
├── Blog/                   # Example module — its own Composer package, own namespace
│   ├── BlogProvider.php    # Extends PlatformProvider
│   ├── routes.php
│   └── composer.json
├── Auth/
│   ├── AuthProvider.php
│   └── composer.json
src/
├── Http/
├── Router/
├── Provider/
├── Container/
├── Cache/
├── Config/
└── Cli/
bin/
└── webkernel
```

**Composer root `composer.json`** maps everything:

```json
{
  "require": {
    "php": "^8.4"
  },
  "autoload": {
    "psr-4": {
      "Webkernel\\App\\": "src/",
      "Modules\\Blog\\": "modules/Blog/src/",
      "Modules\\Auth\\": "modules/Auth/src/"
    }
  },
  "config": {
    "vendor-dir": "platform/dependencies/packagist/vendor"
  },
  "repositories": [
    { "type": "path", "url": "modules/*" }
  ]
}
```

Each module is a real Composer package with its own `composer.json` and namespace. The root autoloader resolves everything. No magic path hacks.

---

## PlatformProvider — The Module Contract

Every module (and the core app itself) registers its capabilities through a `PlatformProvider`. The webkernel compilation pipeline discovers all declared methods and constants, resolves their paths or class names, and compiles them into APCu. Providers never touch the filesystem at runtime — they just declare what they have.

```php
// src/Provider/PlatformProvider.php
namespace Webkernel\Provider;

abstract class PlatformProvider
{
    /**
     * Register bindings into the container before anything boots.
     * Always called, always first.
     * PSR-compatible naming intentional — override freely.
     */
    public function register(Container $container): void {}

    /**
     * Boot logic after all providers are registered.
     * Safe to resolve services here.
     * PSR-compatible naming intentional — override freely.
     */
    public function boot(Container $container): void {}

    // -------------------------------------------------------------------------
    // Declaration methods — all return arrays of class names, file paths, or both.
    // The compiler resolves everything. Providers just declare.
    // Return [] to opt out. All are optional — override only what you need.
    // -------------------------------------------------------------------------

    /** Blade/template composables: view composers, shared data, etc. */
    public function composables(): array { return []; }

    /** View template directories or individual view class paths. */
    public function views(): array { return []; }

    /** Nested providers this provider depends on or delegates to. */
    public function providers(): array { return []; }

    /** Route files or route class paths. */
    public function routes(): array { return []; }

    /** Arbitrary files to autoload or publish (migrations, stubs, assets). */
    public function files(): array { return []; }

    /** CLI command classes or paths to scan for commands. */
    public function commands(): array { return []; }

    /** Explicit class map entries: ['ClassName' => '/path/to/Class.php'] */
    public function classmap(): array { return []; }

    /** Admin panel definitions or panel provider class paths. */
    public function panels(): array { return []; }
}
```

### Constants as static declarations

For providers with no dynamic logic, declaration methods can be replaced by constants. The compiler checks for constants first — zero method call overhead, zero reflection on cold paths.

```php
final class BlogProvider extends PlatformProvider
{
    // Constants are resolved at compile time — no method call, no instantiation needed
    public const ROUTES    = [__DIR__ . '/routes.php'];
    public const VIEWS     = [__DIR__ . '/resources/views'];
    public const COMMANDS  = [__DIR__ . '/Console'];
    public const PANELS    = [\Modules\Blog\Panels\BlogPanel::class];

    // Dynamic logic still goes in methods — constants win only when they exist
    public function register(Container $container): void
    {
        $container->bind(BlogRepository::class, EloquentBlogRepository::class);
    }
}
```

The compiler resolution order per declaration type:

1. Check for a matching constant (`ROUTES`, `VIEWS`, `COMMANDS`, etc.) — use it directly if found.
2. Fall back to calling the method (`routes()`, `views()`, `commands()`, etc.).
3. Merge results into the compiled artifact.

---

## Module Fingerprinting

Every module gets a **fingerprint** — a short deterministic identifier derived from its package name and namespace. The fingerprint is the namespace key for all APCu entries owned by that module. No two modules can collide, even across monorepo and distributed installs.

```php
// src/Provider/ProviderFingerprint.php
namespace Webkernel\Provider;

final class ProviderFingerprint
{
    /**
     * Derive a stable fingerprint from the provider's fully-qualified class name.
     * Result: 8-char hex, e.g. "a3f2c801"
     * Stable across deploys as long as the class name doesn't change.
     */
    public static function for(string $providerClass): string
    {
        return substr(hash('xxh3', $providerClass), 0, 8);
    }

    /**
     * Build a namespaced APCu key for a given artifact type owned by a provider.
     * e.g. "webkernel.a3f2c801.routes"
     */
    public static function cache_key(string $providerClass, string $artifact): string
    {
        return 'webkernel.' . self::for($providerClass) . '.' . $artifact;
    }
}
```

Every compiled artifact — routes, views, panels, commands — is stored and retrieved via its fingerprinted key. The global route map is an index of fingerprint → route entries. No flat merging across modules means no silent overwrites.

---

## Config System

### Global `config()` helper

```php
// src/Config/config.php  (autoloaded via Composer files[])

function config(string $key = null, mixed $default = null): mixed
{
    return webapp()->config($key, $default);
}
```

### `webapp()` helper

```php
function webapp(): \Webkernel\App\Application
{
    return \Webkernel\App\Application::get_instance();
}
```

### `Application::config()` with dot notation

```php
// src/App/Application.php
namespace Webkernel\App;

final class Application
{
    private static self $instance;
    private array $compiled_config = [];

    public static function get_instance(): self
    {
        return self::$instance ??= new self();
    }

    public function config(string $key = null, mixed $default = null): mixed
    {
        if ($this->compiled_config === []) {
            $this->compiled_config = \Webkernel\Cache\CompilationStore::get(
                'webkernel.global.config',
                $this->container
            );
        }

        if ($key === null) {
            return $this->compiled_config;
        }

        return $this->resolve_dot($this->compiled_config, $key, $default);
    }

    private function resolve_dot(array $data, string $key, mixed $default): mixed
    {
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }
        return $data;
    }
}
```

Usage anywhere:

```php
config('blog.posts_per_page');          // 20
config('blog.cache_ttl', 60);           // 300, or 60 if unset
webapp()->config('auth.token_ttl');     // same mechanism, explicit app instance
```

---

## ProviderRegistry

```php
// src/Provider/ProviderRegistry.php
namespace Webkernel\Provider;

final class ProviderRegistry
{
    public static function providers(): array
    {
        return [
            \Webkernel\App\Http\CoreProvider::class,
            \Modules\Blog\BlogProvider::class,
            \Modules\Auth\AuthProvider::class,
        ];
    }

    public static function file(): string
    {
        return __FILE__;
    }
}
```

---

## Unified Compilation Pipeline

**One compilation strategy for everything.** Routes, providers, views, panels, commands, config, ACL — all compiled the same way, all stored in APCu under fingerprinted keys, all self-healing. No CLI commands. No deploy hooks. The app detects staleness and recompiles on the next request.

```
src/
└── Cache/
    ├── CompilationManifest.php   # Tracks mtimes for ALL compilable sources
    ├── Compiler.php              # Orchestrates all compilation passes
    └── CompilationStore.php      # APCu read/write for all compiled artifacts
```

### `CompilationManifest.php`

```php
// src/Cache/CompilationManifest.php
namespace Webkernel\Cache;

use Webkernel\Provider\ProviderRegistry;

final class CompilationManifest
{
    private static function watched_files(): array
    {
        return [
            ProviderRegistry::file(),
            ...self::module_provider_files(),
            ...self::module_route_files(),
        ];
    }

    public static function is_stale(): bool
    {
        $compiled_at = apcu_fetch('webkernel.compiled_at');

        if ($compiled_at === false) {
            return true;
        }

        foreach (self::watched_files() as $file) {
            if (filemtime($file) > $compiled_at) {
                return true;
            }
        }

        return false;
    }

    private static function module_provider_files(): array
    {
        return glob(__DIR__ . '/../../modules/*/*Provider.php') ?: [];
    }

    private static function module_route_files(): array
    {
        return glob(__DIR__ . '/../../modules/*/routes.php') ?: [];
    }
}
```

### `Compiler.php`

```php
// src/Cache/Compiler.php
namespace Webkernel\Cache;

use Webkernel\Container\Container;
use Webkernel\Provider\ProviderRegistry;
use Webkernel\Provider\ProviderFingerprint;
use Webkernel\Router\Router;

final class Compiler
{
    public static function compile(Container $container): void
    {
        $providers = self::boot_providers($container);

        $artifacts = [];

        // Pass 1: routes (merged global map + per-fingerprint entries)
        $artifacts['webkernel.global.routes'] = self::compile_routes($providers, $container);

        // Pass 2: config (dot-accessible, merged across providers)
        $artifacts['webkernel.global.config'] = self::compile_config($providers);

        // Pass 3: ACL
        $artifacts['webkernel.global.acl'] = self::compile_acl($providers);

        // Pass 4: views (per-fingerprint namespace → path map)
        $artifacts['webkernel.global.views'] = self::compile_views($providers);

        // Pass 5: commands (flat list of all command classes)
        $artifacts['webkernel.global.commands'] = self::compile_commands($providers);

        // Pass 6: panels
        $artifacts['webkernel.global.panels'] = self::compile_panels($providers);

        // Pass 7: composables
        $artifacts['webkernel.global.composables'] = self::compile_composables($providers);

        // Pass 8: classmap
        $artifacts['webkernel.global.classmap'] = self::compile_classmap($providers);

        CompilationStore::store_all($artifacts);
        apcu_store('webkernel.compiled_at', time());
    }

    private static function boot_providers(Container $container): array
    {
        $providers = [];

        foreach (ProviderRegistry::providers() as $class) {
            $provider = new $class();
            $provider->register($container);
            $providers[] = $provider;
        }

        foreach ($providers as $provider) {
            $provider->boot($container);
        }

        return $providers;
    }

    // -------------------------------------------------------------------------
    // Resolution helper: checks constant first, falls back to method call.
    // -------------------------------------------------------------------------

    private static function resolve_declaration(object $provider, string $constant, string $method): array
    {
        $class = get_class($provider);

        if (defined("{$class}::{$constant}")) {
            return constant("{$class}::{$constant}");
        }

        if (method_exists($provider, $method)) {
            return $provider->$method();
        }

        return [];
    }

    // -------------------------------------------------------------------------
    // Per-artifact compilation passes
    // -------------------------------------------------------------------------

    private static function compile_routes(array $providers, Container $container): array
    {
        $router = $container->get(Router::class);

        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'ROUTES', 'routes');

            foreach ($entries as $entry) {
                if (is_string($entry) && file_exists($entry)) {
                    // It's a path — include the route file, which registers on $router
                    (static function () use ($entry, $router) { require $entry; })();
                } elseif (is_string($entry) && class_exists($entry)) {
                    // It's a route class with a register() method
                    (new $entry())->register($router);
                }
            }
        }

        return $router->flat_map(); // [METHOD][URI] => [Controller, action]
    }

    private static function compile_config(array $providers): array
    {
        $config = [];

        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'CONFIG', 'config');
            $config  = array_merge($config, $entries);
        }

        return $config;
    }

    private static function compile_acl(array $providers): array
    {
        $acl = [];

        foreach ($providers as $provider) {
            if (method_exists($provider, 'acl')) {
                $acl = array_merge_recursive($acl, $provider->acl());
            }
        }

        return $acl;
    }

    private static function compile_views(array $providers): array
    {
        $map = [];

        foreach ($providers as $provider) {
            $entries     = self::resolve_declaration($provider, 'VIEWS', 'views');
            $fingerprint = ProviderFingerprint::for(get_class($provider));

            foreach ($entries as $entry) {
                $map[$fingerprint][] = $entry; // path or class
            }
        }

        return $map;
    }

    private static function compile_commands(array $providers): array
    {
        $commands = [];

        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'COMMANDS', 'commands');

            foreach ($entries as $entry) {
                if (is_dir($entry)) {
                    // Scan directory for command classes
                    array_push($commands, ...self::scan_command_dir($entry));
                } else {
                    $commands[] = $entry;
                }
            }
        }

        return array_unique($commands);
    }

    private static function compile_panels(array $providers): array
    {
        $panels = [];

        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'PANELS', 'panels');
            array_push($panels, ...$entries);
        }

        return $panels;
    }

    private static function compile_composables(array $providers): array
    {
        $composables = [];

        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'COMPOSABLES', 'composables');
            array_push($composables, ...$entries);
        }

        return $composables;
    }

    private static function compile_classmap(array $providers): array
    {
        $map = [];

        foreach ($providers as $provider) {
            $entries = self::resolve_declaration($provider, 'CLASSMAP', 'classmap');
            $map     = array_merge($map, $entries);
        }

        return $map;
    }

    private static function scan_command_dir(string $dir): array
    {
        $found = [];

        foreach (glob("{$dir}/*.php") ?: [] as $file) {
            $found[] = $file; // The autoloader resolves class from path at runtime
        }

        return $found;
    }
}
```

### `CompilationStore.php`

```php
// src/Cache/CompilationStore.php
namespace Webkernel\Cache;

use Webkernel\Container\Container;

final class CompilationStore
{
    public static function get(string $key, Container $container): mixed
    {
        if (CompilationManifest::is_stale()) {
            Compiler::compile($container);
        }

        $value = apcu_fetch($key);

        if ($value === false) {
            // Self-heal: shouldn't happen after compile, but handle it anyway
            Compiler::compile($container);
            $value = apcu_fetch($key);
        }

        return $value;
    }

    public static function store_all(array $artifacts): void
    {
        foreach ($artifacts as $key => $value) {
            apcu_store($key, $value);
        }
    }
}
```

---

## Module Example — `Modules\Blog\BlogProvider`

```php
// modules/Blog/BlogProvider.php
namespace Modules\Blog;

use Webkernel\Provider\PlatformProvider;
use Webkernel\Container\Container;

final class BlogProvider extends PlatformProvider
{
    // Static declarations — resolved at compile time, zero method overhead
    public const ROUTES      = [__DIR__ . '/routes.php'];
    public const VIEWS       = [__DIR__ . '/resources/views'];
    public const COMMANDS    = [__DIR__ . '/Console'];
    public const COMPOSABLES = [\Modules\Blog\Composables\BlogComposable::class];
    public const PANELS      = [\Modules\Blog\Panels\BlogPanel::class];

    public function register(Container $container): void
    {
        $container->bind(BlogRepository::class, EloquentBlogRepository::class);
    }

    public function boot(Container $container): void
    {
        // Runs after all providers are registered.
        // Safe to resolve BlogRepository here if needed.
    }

    // Dynamic declarations — override when values can't be known at definition time
    public function files(): array
    {
        return [
            __DIR__ . '/database/migrations',
            __DIR__ . '/resources/stubs',
        ];
    }

    public function config(): array
    {
        return [
            'blog.posts_per_page' => 20,
            'blog.cache_ttl'      => 300,
        ];
    }

    public function acl(): array
    {
        return [
            'blog.create' => ['admin', 'editor'],
            'blog.delete' => ['admin'],
        ];
    }
}
```

Add it to `ProviderRegistry::providers()`. Next request detects the `mtime` change on `ProviderRegistry.php`. Recompiles everything. Done.

**Provider constants reference:**

| Constant | Method fallback | Resolved content |
|---|---|---|
| `ROUTES` | `routes()` | File paths to `routes.php` or route class names |
| `VIEWS` | `views()` | Directory paths or view class names |
| `COMMANDS` | `commands()` | Directory paths to scan or command class names |
| `PANELS` | `panels()` | Panel class names or config paths |
| `COMPOSABLES` | `composables()` | Composable class names or paths |
| `CLASSMAP` | `classmap()` | `['ClassName' => '/path/to/Class.php']` |
| `CONFIG` | `config()` | Flat dot-key config array |
| `FILES` | `files()` | Arbitrary paths (migrations, stubs, assets) |

---

## Request Segmentation & Early-Exit Architecture

No Nginx/Apache config is ever touched. The PHP front-controller handles all classification internally using the standard one-liner:

```nginx
location / {
    try_files $uri $uri/ /index.php;
}
```

**`public/index.php`** — the only entry point:

```php
<?php
require __DIR__ . '/../platform/dependencies/packagist/vendor/autoload.php';

use Webkernel\Cache\CompilationStore;
use Webkernel\Container\Container;
use Webkernel\Http\RequestClassifier;

$container = Container::get_instance();
$uri       = $_SERVER['REQUEST_URI'];
$handler   = (new RequestClassifier())->classify($uri);

// Compilation check — single call, same path for everything
$route_map = CompilationStore::get('webkernel.global.routes', $container);

$handler->handle($route_map, $container)->emit();
```

### `RequestClassifier.php`

```php
// src/Http/RequestClassifier.php
namespace Webkernel\Http;

final class RequestClassifier
{
    public function classify(string $uri): HandlerInterface
    {
        if (str_ends_with($uri, '.md') || $uri === '/llm.txt') {
            return new MachineHandler();
        }

        if (str_starts_with($uri, '/api/')) {
            return new ApiHandler();
        }

        if (str_starts_with($uri, '/rss') || str_starts_with($uri, '/atom')) {
            return new SyndicationHandler();
        }

        return new WebHandler();
    }
}
```

Static assets are handled by `try_files` before PHP ever boots. PHP never sees those requests.

---

## Lazy Boot Principles

1. **Zero manual cache commands.** `CompilationManifest::is_stale()` watches all provider and route files. Any `mtime` change triggers silent recompilation on the very next request.
2. **Self-healing cache.** Cold APCu (fresh boot, cache flush, new deploy) recompiles on first request. The app always heals itself.
3. **Deferred PSR-7 instantiation.** Route matching runs against raw `$_SERVER['REQUEST_URI']`. Heavy PSR-7 objects are only instantiated *after* a route match is confirmed.
4. **Lazy provider boot.** View or platform providers never register unless the matched controller explicitly requests a rendering pipeline.
5. **Constants before methods.** Static provider declarations resolve at compile time via constant lookup — no object instantiation, no reflection.

---

## Pipeline & Middleware Lifecycle

Requests move through a strictly ordered pipeline:

1. **Request Identification:** Injection of a unique `X-Request-ID` header across HTTP, API, Machine, and worker logs for end-to-end tracing.
2. **Early Route Match (Flat Array):** Instant lookup via compiled route map in APCu. If unmatched, exit immediately with a lightweight 404 response (< 0.1 ms execution, ~5 files loaded).
3. **Payload & Input Filtering:** Global payload size restrictions. `Content-Type` validation.
4. **Context-Specific Middleware:**
   * *Web:* Session start, CSRF verification.
   * *API / Machine:* Token auth, CORS, strict rate-limiting.

### `Http/Pipeline.php`

```php
// src/Http/Pipeline.php
namespace Webkernel\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Pipeline
{
    // PSR method names kept as-is — this implements PSR-15 MiddlewareInterface contract
    public function run(
        ServerRequestInterface $request,
        array $middleware,
        callable $controller
    ): ResponseInterface {
        $next = $controller;

        foreach (array_reverse($middleware) as $mw) {
            $next = fn($req) => $mw->handle($req, $next);
        }

        return $next($request);
    }
}
```

### Context-specific middleware stacks

| Context | Middleware Chain |
|---|---|
| Web | RequestId → SessionStart → CsrfVerify → PayloadFilter → RouteMatch → Controller |
| API | RequestId → TokenAuth → Cors → RateLimit → PayloadFilter → RouteMatch → Controller |
| Machine | RequestId → RateLimit → PayloadFilter → RouteMatch → Controller |
| Syndication | RequestId → ApcCache → RouteMatch → Controller |

---

## Sub-Millisecond Caching & Memory Strategy

Network-bound caches (Redis/Memcached) are restricted to full I/O operations (< 10 ms budget). The hot path operates exclusively on local shared memory.

| Caching Tier | Technology | Target Data | Memory Overhead |
|---|---|---|---|
| **OPcache Bytecode** | Shared Memory | Compiled PHP scripts, config arrays | Zero file read overhead |
| **APCu (Local RAM)** | Shared Memory | Route maps, views, ACL, config, panels, commands — all via `CompilationStore` | Zero network/serialization cost |
| **HTTP / CDN** | Cloudflare / Nginx | Static assets, `/rss`, `/llm.txt`, public API responses | Zero application boot |

---

## Security Strategy & Threat Mitigation

### 1. CSRF & Session Isolation

* **Web Context (Stateful):** Requires a cryptographically secure token passed via `<meta name="csrf-token">` and sent in the `X-CSRF-TOKEN` header for mutative AJAX requests (`POST`, `PUT`, `DELETE`). Cookies enforce `SameSite=Lax/Strict` and `HttpOnly; Secure`.
* **API / Machine / RSS Contexts (Stateless):** Cookie authentication is completely disabled. Requests use `Authorization: Bearer <token>` or public access, eliminating CSRF vulnerabilities by design.

### 2. Injection Prevention (SQLi, XSS, Shell)

* **SQL Injection:** Mandatory prepared statements and parameterized queries via PDO/Query Builder.
* **XSS Protection:** Input data remains raw in the database. HTML escaping is performed strictly at render time. API and machine endpoints enforce explicit content types.
* **Shell Injections:** Direct execution functions (`exec`, `system`) are prohibited on user input. Arguments are passed strictly via array escaping primitives.

### 3. API & AI Endpoint Rate Limiting

Token Bucket algorithm stored in APCu/Redis per IP or API token. Strict limits applied to AI crawlers scraping `/llm.txt` or `/md`. Standard `429 Too Many Requests` responses with `Retry-After` headers.

### `Http/Middleware/CsrfMiddleware.php`

```php
// src/Http/Middleware/CsrfMiddleware.php
namespace Webkernel\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class CsrfMiddleware implements MiddlewareInterface
{
    // PSR-15 method name kept — this is an interface override
    public function handle(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $request->getHeaderLine('X-CSRF-TOKEN');

            if (!$this->validate_token($token)) {
                return new Response(403);
            }
        }

        return $next($request);
    }

    private function validate_token(string $token): bool
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
```

### `Http/Middleware/RateLimitMiddleware.php` — Token Bucket in APCu

```php
// src/Http/Middleware/RateLimitMiddleware.php
namespace Webkernel\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RateLimitMiddleware implements MiddlewareInterface
{
    // PSR-15 method name kept — interface override
    public function handle(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $key    = $this->resolve_key($request);
        $bucket = apcu_fetch("webkernel.rate:{$key}") ?: ['tokens' => 100, 'last' => time()];

        if ($bucket['tokens'] < 1) {
            return new Response(429, ['Retry-After' => '60']);
        }

        $bucket['tokens']--;
        apcu_store("webkernel.rate:{$key}", $bucket, 60);

        return $next($request);
    }

    private function resolve_key(ServerRequestInterface $request): string
    {
        return $request->getHeaderLine('X-API-TOKEN')
            ?: ($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
    }
}
```

---

## Asynchronous Processing & Task Offloading

Endpoints requiring heavy I/O must never hold the HTTP response thread.

```php
// src/Queue/Dispatcher.php
namespace Webkernel\Queue;

final class Dispatcher
{
    public function dispatch(string $job_class, array $payload): string
    {
        $job_id = uniqid('job_', true);
        $this->queue->push($job_class, $payload, $job_id);
        return $job_id;
    }
}

// Controller usage
public function generate_report(ServerRequestInterface $request): ResponseInterface
{
    $job_id = $this->dispatcher->dispatch(
        GenerateReportJob::class,
        ['user_id' => $request->getAttribute('user')->id]
    );

    return new Response(202, [], json_encode(['job_id' => $job_id]));
}
```

---

## Observability & Error Standards

**Health endpoints** — bypass everything, 2–3 files loaded:

```php
public function handle_health(): ResponseInterface
{
    return new Response(200, [], 'OK'); // ~0.01ms
}
```

**RFC 7807 Problem Details:**

```json
{
  "type": "https://webkernelphp.com/errors/route-not-found",
  "title": "Not Found",
  "status": 404,
  "detail": "The requested endpoint '/api/v1/missing' does not exist.",
  "instance": "/api/v1/missing"
}
```

```php
// src/Http/ProblemResponse.php
namespace Webkernel\Http;

final class ProblemResponse
{
    public static function not_found(string $path): ResponseInterface
    {
        return new Response(404, [
            'Content-Type' => 'application/problem+json'
        ], json_encode([
            'type'     => 'https://webkernelphp.com/errors/route-not-found',
            'title'    => 'Not Found',
            'status'   => 404,
            'detail'   => "The requested endpoint '{$path}' does not exist.",
            'instance' => $path,
        ]));
    }
}
```

---

## CLI Execution

```php
// bin/webkernel
#!/usr/bin/env php
<?php
require __DIR__ . '/../platform/dependencies/packagist/vendor/autoload.php';

$handler   = new \Webkernel\Cli\CliHandler();
$exit_code = $handler->handle($argv);
exit($exit_code);
```

```php
// src/Cli/CliHandler.php
namespace Webkernel\Cli;

final class CliHandler
{
    public function handle(array $argv): int
    {
        $command = $argv[1] ?? 'list';

        // No HTTP parsing, no middleware, no sessions.
        // Commands still resolve from the container — same providers, same bindings.
        return $this->commands[$command]->execute(array_slice($argv, 2));
    }
}
```

---

## Implementation Plan

### Design Principles (Non-Negotiable)

* **No touching the server.** Standard `try_files $uri $uri/ /index.php` is the only web server config.
* **No manual cache commands. Ever.** The compilation pipeline detects staleness and heals itself.
* **No namespace called `Kernel`.** Handlers are just handlers. Namespace root is `Webkernel\`.
* **One compilation strategy for everything.** Routes, views, panels, commands, ACL, config, classmap — all the same pipeline, all the same APCu store, all the same staleness check.
* **Modules are real Composer packages.** They live in `modules/`, have their own namespaces, and are distributed from any repository. The root `composer.json` wires them in via path or VCS repositories.
* **Providers are the extension point.** Constants first, method fallback always. The compiler resolves paths and classes — providers just declare.
* **snake_case everywhere webkernel owns it.** PSR interface methods (`handle`, `register`, `boot`) stay as-is because they are overrides. Every other webkernel function, method, and property is snake_case.
* **Fingerprints prevent collisions.** Every module's compiled artifacts live under its fingerprint key in APCu. No flat merging, no silent overwrites.
* **`config()` is always dot-accessible.** `config('blog.posts_per_page')` and `webapp()->config('blog.posts_per_page')` are identical. Both go through the same compiled APCu artifact.

---

### Phase 1: Project Structure & Autoloading (Week 1)

Wire up `platform/dependencies/packagist` as the Composer vendor directory. Register all module namespaces via PSR-4 in the root `composer.json` under `Webkernel\`. Each module's `composer.json` declares its own dependencies; the root requires the module as a path repository.

**Deliverable:** `composer install` resolves everything, all namespaces resolve, no custom autoload hacks.

---

### Phase 2: PlatformProvider & ProviderRegistry (Week 1)

Implement the abstract `PlatformProvider` base class with all declaration methods and constant resolution logic. Implement `ProviderRegistry` and `ProviderFingerprint`. Write providers for core (`CoreProvider`) and at least one module (`BlogProvider`) to validate the full contract including constants, method fallbacks, and fingerprinted APCu keys.

**Deliverable:** Adding a provider to `ProviderRegistry::providers()` and declaring `ROUTES` or `routes()` on it is the complete workflow for wiring in a new module.

---

### Phase 3: Unified Compilation Pipeline (Week 1–2)

Implement `CompilationManifest`, `Compiler`, and `CompilationStore`. All compilation passes — routes, views, panels, commands, composables, classmap, ACL, config — go through `Compiler::compile()`. All reads go through `CompilationStore::get()`. Implement `config()` helper and `webapp()->config()` with dot notation.

**Deliverable:** Edit any provider or route file. Next request detects the `mtime` change, recompiles all artifacts atomically under fingerprinted keys, stores them in APCu. `config('blog.posts_per_page')` returns `20`. Zero commands.

---

### Phase 4: Request Classification & Handlers (Week 2)

Implement `RequestClassifier` and all handlers: `WebHandler`, `ApiHandler`, `MachineHandler`, `SyndicationHandler`, `CliHandler`. Wire `public/index.php` to call `CompilationStore::get()` once on boot.

**Deliverable:** All traffic paths route correctly. Static assets never reach PHP.

---

### Phase 5: Middleware Pipeline (Week 2–3)

Implement `Pipeline` and all middleware: `RequestIdMiddleware`, `SessionMiddleware`, `CsrfMiddleware`, `TokenAuthMiddleware`, `CorsMiddleware`, `RateLimitMiddleware`, `PayloadFilterMiddleware`. All internal webkernel methods snake_case; PSR-15 `handle()` stays as the interface override.

**Deliverable:** Each handler applies its own middleware stack. Web context enforces CSRF. API context enforces bearer tokens. Machine/syndication are stateless.

---

### Phase 6: Container / DI (Week 3)

The container must support: `bind()`, `singleton()`, `get()`, and lazy resolution via `get_instance()` on `Application`. No autowiring by default — explicit bindings via `register()` in providers are sufficient and faster.

**Deliverable:** All provider `register()` calls work. Controllers resolve their dependencies. No magic. Fast.

---

### Phase 7: Security, Async, Observability (Week 3–4)

Security middleware done in Phase 5. This phase adds:

* `Queue\Dispatcher` for async job offloading
* `202 Accepted` response pattern in controllers
* `/healthz` and `/ready` endpoints (bypass all framework overhead)
* `ProblemResponse::not_found()` RFC 7807 factory

---

### Phase 8: CLI Handler (Week 4)

Implement `bin/webkernel` and `CliHandler`. CLI execution bypasses HTTP parsing, web server interfaces, and middleware entirely. Commands registered by providers via `COMMANDS` or `commands()` are discovered at compile time and available to `CliHandler` through the same container and the same compiled APCu artifact.

---

## Dependencies & Infrastructure

| Component | Technology | Notes |
|---|---|---|
| Web Server | Nginx/Apache | Standard `try_files` only — nothing custom |
| PHP | 8.4+ | Required for performance features |
| Composer vendor | `platform/dependencies/packagist/vendor` | All packages including webkernel itself |
| Module packages | `modules/*/` | Own namespaces, own `composer.json`, path or VCS repositories |
| OPcache | Enabled | Preload compiled scripts |
| APCu | Required | All compiled artifacts under `webkernel.*` fingerprinted keys — single store via `CompilationStore` |
| Queue | Redis/Beanstalkd | Async job processing |
| Cache (distributed) | Redis/Memcached | Network-bound cache, 10 ms budget |

---

## Performance Verification

```bash
# Measure 404 response time (target: < 0.1ms)
curl -w "@curl-format.txt" -o /dev/null -s http://localhost/nonexistent

# curl-format.txt:
time_namelookup:    %{time_namelookup}\n
time_connect:       %{time_connect}\n
time_appconnect:    %{time_appconnect}\n
time_pretransfer:   %{time_pretransfer}\n
time_redirect:      %{time_redirect}\n
time_starttransfer: %{time_starttransfer}\n
----------\n
time_total:         %{time_total}\n
```

**Target:** `time_starttransfer` < 0.1 ms for cached 404s.

---

## Summary

| Item | Status | Complexity | Impact |
|---|---|---|---|
| Request classification | Ready | Low | High |
| Unified compilation pipeline | Ready | Medium | Critical |
| PlatformProvider contract + constants | Ready | Low | Critical |
| Module/package layout | Ready | Low | High |
| Provider fingerprinting | Ready | Low | High |
| `config()` + `webapp()->config()` dot notation | Ready | Low | High |
| Lazy PSR-7 instantiation | Ready | Medium | High |
| Middleware pipeline | Ready | Medium | High |
| APCu — fingerprinted keys for all artifacts | Ready | Low | High |
| Security middleware | Ready | Medium | Critical |
| Container rewrite | Planned | Medium | High |
| Async offloading | Ready | Medium | Medium |
| Observability | Ready | Low | Medium |
| CLI handler | Ready | Low | Medium |

**Total estimated time:** 3–4 weeks for full implementation with proper testing.

**Key changes from the previous revision:**

- Namespace root is `Webkernel\` — no exceptions
- snake_case across all webkernel-owned functions, methods, properties, and helpers; PSR interface overrides (`handle`, `register`, `boot`) stay camelCase as required by the contract
- `PlatformProvider` now declares `composables()`, `views()`, `providers()`, `routes()`, `files()`, `commands()`, `classmap()`, `panels()` — all return arrays of paths, class names, or both; the compiler resolves everything
- Constants (`ROUTES`, `VIEWS`, `COMMANDS`, etc.) are checked before methods — zero call overhead for static declarations
- Module fingerprinting via `ProviderFingerprint` — every module's APCu keys are scoped to its fingerprint, collisions are impossible
- `config()` global helper and `webapp()->config()` with dot notation, both backed by the same `webkernel.global.config` APCu artifact
- All APCu keys prefixed `webkernel.` for namespace isolation in shared environments
