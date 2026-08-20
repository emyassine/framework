# Agent rules — webkernel/codebase

Zero runtime Composer package dependencies (PHP 8.4+ only; PSR interfaces allowed when a type is actually required). Target: render under 1 ms.

This tree is not Laravel. No Filament, no Livewire, no Illuminate, no Boost, no Pint, no Pest artisan.

Engines we own in-tree (fork, specialize — do not wrap as a vendor): FastRoute MarkBased → `Webkernel\Route`, BladeOne → `Webkernel\View\Compiler`. Drop strategies / extras we do not run.

`webkernel/lifecycle` is a **sibling** Composer plugin (`x-webkernel/lifecycle`), not nested here.

## PHP naming

| Kind | Rule | Example |
| --- | --- | --- |
| Webkernel methods / functions | `snake_case` | `webapp_path()`, `add_path()` |
| Parameters / locals | `snake_case` | `$webapp_root`, `$cache_dir` |
| Classes / namespaces | `PascalCase` | `Engine`, `InstanceId` |
| Constants | `UPPER_SNAKE` | `WEBKERNEL_NS` |

Exceptions: Composer / PSR interface methods you do not own (`activate`, `getSubscribedEvents`, `getInstallPath`, `supports`); HTTP verbs and Blade helpers that match Laravel usage (`Route::get`, `view()`, `View::make`).

Do not introduce `camelCase` on Webkernel surfaces. Do not keep dual APIs.

## Dependencies

- Runtime: PHP 8.4+ only. No nikic/fast-route, no eftec/bladeone (copied and specialized under `src/Route`, `src/View`).
- PSR allowed only when a type we expose needs it. This slice does not.
- `composer-plugin-api` is Composer-time, in `webkernel/lifecycle` only.
- Do not add Laravel, Filament, Symfony HTTP, or a container.

## Paths

- `webapp_path()` is independent of Laravel and of the Composer PHP library.
- Vendor-dir: `Composer\InstalledVersions` file location (`dirname($file, 2)`), never a hardcoded `vendor/`.
- Lifecycle writes `{vendor}/composer/webkernel.php`. Runtime reads it.
- Host moved (stored vendor path prefix mismatch): run `composer dump-autoload`. Do not walk disks on the request path.

## Instance

`Webkernel\Instance\InstanceId` — fingerprint of host path + machine. Lifecycle writes it. Do not recompute MAC / product uuid per request.

## Route

`Webkernel\Route\Route` — `Route::get` / `post` / `dispatch`. Extra keys `NAME`, `PANEL`, `CLUSTER`, `RESOURCE`, `PAGE`, `PERMISSION` bind a URI to the platform tree. Permission is recorded, not enforced, until auth exists.

One dispatcher: MarkBased. No CharCount / GroupCount / GroupPos. No PSR-7 URI objects.

## View

`view()` + `Webkernel\View\View`. Compiler is BladeOne in `Webkernel\View\Compiler`. Templates: `resources/views/*.blade.php`. Compiled: `storage/framework/views`.

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

## Performance

- No directory walking on the request path that Composer already computed.
- Static process caches. Opcache-friendly generated PHP.
- Do not add work that is not required to boot or render.

## DevEnv / IDE

`Webkernel\DevEnv\IdeHelper` generates `src/DevEnv/_ide_helper.php` from Composer classmap so analyzers see `Composer\InstalledVersions` and the rest of vendor. No hardcoded class list, no directory walk. Lifecycle calls it on dump-autoload.

## Host API

Do not add a service container, composable discovery, or `webapp()` facade until something actually needs it.
