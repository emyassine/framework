# Agent rules — webkernel/codebase

Zero runtime package dependencies. Target: render under 1 ms.

This tree is not Laravel. No Filament, no Livewire, no Illuminate, no Boost, no Pint, no Pest artisan.

`webkernel/lifecycle` is a **sibling** Composer plugin (`x-webkernel/lifecycle`), not nested here.

## PHP naming

| Kind | Rule | Example |
| --- | --- | --- |
| Webkernel methods / functions | `snake_case` | `webapp_path()`, `add_path()` |
| Parameters / locals | `snake_case` | `$webapp_root`, `$cache_dir` |
| Classes / namespaces | `PascalCase` | `Engine`, `InstanceId` |
| Constants | `UPPER_SNAKE` | `WEBKERNEL_NS` |

Exceptions: Composer / PSR interface methods you do not own (`activate`, `getSubscribedEvents`, `getInstallPath`, `supports`).

Do not introduce `camelCase` on Webkernel surfaces. Do not keep dual APIs.

## Dependencies

- Runtime: PHP 8.4+ only.
- `composer-plugin-api` is Composer-time, in `webkernel/lifecycle` only.
- Do not add Laravel, Filament, Symfony HTTP, or a container.

## Paths

- `webapp_path()` is independent of Laravel and of the Composer PHP library.
- Vendor-dir: `Composer\InstalledVersions` file location (`dirname($file, 2)`), never a hardcoded `vendor/`.
- Lifecycle writes `{vendor}/composer/webkernel.php`. Runtime reads it.
- Host moved (stored vendor path prefix mismatch): run `composer dump-autoload`. Do not walk disks on the request path.

## Instance

`Webkernel\Instance\InstanceId` — fingerprint of host path + machine. Lifecycle writes it. Do not recompute MAC / product uuid per request.

## Views

`view('name', $data)` — compile once, `require` the cache.

Do **not** wrap HTML attributes in `@if` / `@endif` inside a tag. Compute locals, then always emit the attribute.

```php
$show = $open ? 'true' : 'false';
```

```
<span x-show="{{ $show }}" class="…">
```

`{{ }}` escapes. `{!! !!}` is raw.

## Package layout

Each subpackage:

```
composer.json
README.md
SECURITY.md
src/                 PHP (namespace Webkernel\{Pkg}\)
resources/{js,css,img,icons}/
views/components/
```

## Performance

- No directory walking on the request path that Composer already computed.
- Static process caches. Opcache-friendly generated PHP.
- Do not add work that is not required to boot or render.

## Host API

Do not add a service container, composable discovery, or `webapp()` facade until something actually needs it.
