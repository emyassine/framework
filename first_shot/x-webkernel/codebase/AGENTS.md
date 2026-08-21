# Agent rules — webkernel/codebase

Zero runtime Composer package dependencies (PHP 8.4+ only; PSR interfaces allowed when a type is actually required). Target: render under 1 ms.

This tree is not Laravel. No Filament, no Livewire, no Illuminate, no Boost, no Pint, no Pest artisan.

Engines we own in-tree (fork, specialize — do not wrap as a vendor): FastRoute MarkBased → `Webkernel\Route`, BladeOne → `Webkernel\View\Compiler`. Drop strategies / extras we do not run.

`webkernel/lifecycle` is a **sibling** Composer plugin (`x-webkernel/lifecycle`), not nested here.

## PHP naming

| Kind | Rule | Example |
| --- | --- | --- |
| Webkernel methods / functions | `snake_case` | `webapp_path()`, `add_path()`, `where_number()` |
| Parameters / locals | `snake_case` | `$webapp_root`, `$cache_dir` |
| Classes / namespaces | `PascalCase` | `Engine`, `InstanceId` |
| Constants | `UPPER_SNAKE` | `WEBKERNEL_NS` |

Exceptions: Composer / PSR interface methods you do not own (`activate`, `getSubscribedEvents`, `getInstallPath`, `supports`, `get`, `has`).

Do not introduce `camelCase` on Webkernel surfaces. Do not keep dual APIs.

## Dependencies

- Runtime: PHP 8.4+ only, plus `psr/container` (PSR-11). No nikic/fast-route, no eftec/bladeone (copied and specialized under `src/Route`, `src/View`). Templates are `*.view.php`, never `*.blade.php`.
- PSR-11 is required: `Webkernel\Container\Container` implements `Psr\Container\ContainerInterface`.
- `composer-plugin-api` is Composer-time, in `webkernel/lifecycle` only.
- Do not add Laravel, Filament, or Symfony HTTP.

## Paths

- `webapp_path()` is independent of Laravel and of the Composer PHP library.
- Vendor-dir: `Composer\InstalledVersions` file location (`dirname($file, 2)`), never a hardcoded `vendor/`.
- Lifecycle writes `{vendor}/composer/webkernel.php`. Runtime reads it.
- Host moved (stored vendor path prefix mismatch): run `composer dump-autoload`. Do not walk disks on the request path.
- **Never `sys_get_temp_dir()`.** Shared hosting often blocks it (`open_basedir`). Transient files go under `platform/temporary/` (webapp-writable). Delete after use. Do not persist `composer.phar` in the tree.

## Instance

`Webkernel\Instance\InstanceId` — fingerprint of host path + machine. Lifecycle writes it. Do not recompute MAC / product uuid per request.

## Host API

`webapp()` is the eager helper. `WebApp` is the facade. `__call` resolves dump-autoload map `{vendor}/composer/webkernel_composables.php` (`api_name => class`) then `container()->make()`. Unknown segment throws. No request-time class glob.

Providers (`PlatformProvider`) declare view paths, component dirs, route files, extra bindings at boot. Composables are lazy fluent segments (`webapp()->view()`, `webapp()->route()`, later `webapp()->auth()`). Path helpers stay dumped; route/view function files load with the composable class.

Container: `Webkernel\Container\Container` — PSR-11 `get` / `has`, plus `singleton` / `bind` / `scoped` / `instance`. Unbound `get`/`make` throws `NotFound`. No reflection auto-wiring.

Host bootstrap (Laravel-shaped, platform-wide):

```php
return WebApp::configure()
    ->with_middleware(function (Middleware $middleware): void {})
    ->with_exceptions(function (Exceptions $exceptions): void {
        $exceptions->should_render_json_when(
            fn (Request $request) => $request->is('api/*'),
        );
    })
    ->with_routes()
    ->create();
```

`with_routes()` declares host `routes/web.php`. `create()` boots providers from dump + host `declare`.

Front controller:

```php
(require __DIR__.'/../bootstrap/app.php')
    ->handle_request(Request::capture());
```

Dump-autoload also writes `webkernel_providers.php` from `extra.webkernel.provider`. Merged with host `declare('providers', …)`.

## Route

`Webkernel\Route\Route` — `Route::get` / `post` / `view` return a `Binding` for fluent `name()`, `where()`, `where_number()`, `middleware()`, `panel()`, … Groups chain `prefix()` / `name()` / `domain()` / `middleware()` then `group()`. Extra keys still stored on the compiled route. Permission and middleware are recorded, not enforced, until auth exists.

One dispatcher: MarkBased. No CharCount / GroupCount / GroupPos. No PSR-7 URI objects.

`Route::view()` / `redirect()` use serialisable invokables. Closures skip the compile file and stay in-memory. Compile file: `storage/framework/cache/routes_{hash}.php`, hash/mtime over declared route files. No `artisan` cache:clear.

Layout under `src/Route/src/`: `Group/`, `Dispatch/`, `Compile/`, `Uri/`, `Exception/`, `Action/`. Facade and `Binding` stay at the package root of that tree.

## View

`view()` + `Webkernel\View\View`. Compiler is BladeOne in `Webkernel\View\Compiler`. Templates: `{name}.view.php`. Compiled: `storage/framework/views/{name}_{hash}.view.php.compiled`. `MODE_AUTO` recompiles when template mtime is newer. Hash is of the full template path. No `view:clear`.

`declare_view('webkernel', $dir)` → `@include('webkernel::layouts.page')`.
`declare_component('webkernel', $dir)` → `<webkernel::page />` / `<x-webkernel::page />`.
Un-namespaced `@extends('layouts.page')` stays (host `resources/views` first). `<x-foo />` is `components.foo`.

Dump `webkernel_views.php` / `webkernel_routes.php` are fallback until providers declare paths.

Layouts (Filament-shaped, CSS split by chrome):

| Layout | Loads |
| --- | --- |
| `layouts.base` | tokens only |
| `layouts.simple` | tokens + centered card (no sidebar) |
| `layouts.page` | tokens + shell (sidebar / topnav / horizontal) + components |

## Console

`Webkernel\Console`. Host binary is `webkernel` (Laravel artisan-shaped): `handle_command(new ArgvInput)`. Commands are `#[ConsoleCommand]` methods on plain classes — no parent, constructor is for DI. Method parameters are the signature (Tempest). `webterminal()` is the prompts composable (`text`, `select`, `confirm`, …). Dump `webkernel_commands.php` at dump-autoload. No Symfony Console, no Laravel Prompts package.

Do not boot Route or View classes on requests that do not call them. `class_alias` of `Route` / `View` / `Js` is lazy (autoload).

Platform tree (later, not this package): App owner → Platform (system panel) → Module → Panel → Cluster → Resource → Page → components. Do not invent a second templating stack.

## Package layout

Each subpackage:

```
composer.json
load.{prefix}.package-function.php   next to composer.json — the only files-autoload path (stable; never rename)
functions/*.php                      glob'd by the loader (this directory only, no subfolders)
src/                                 PHP classes (namespace Webkernel\{Pkg}\)
README.md
SECURITY.md
resources/{js,css,img,icons}/
views/components/

Composer lock records the loader path only. Do not list functions/*.php in autoload.files.
```

`extra.webkernel.prefix` is the package alias for `webkernel_package_root()`. `extra.webkernel.provider` is one FQCN dumped into `webkernel_providers.php`.

## Performance

- No directory walking on the request path that Composer already computed.
- Static process caches. Opcache-friendly generated PHP.
- Do not add work that is not required to boot or render.

## DevEnv / IDE

`Webkernel\DevEnv\IdeHelper` generates `src/DevEnv/_ide_helper.php` from Composer classmap so analyzers see `Composer\InstalledVersions` and the rest of vendor. Webkernel classes are skipped (source is already in-tree). No hardcoded class list, no directory walk. Lifecycle calls it on dump-autoload.
