# Project layout

Physical tree of the refactor host. Runtime caches live under `platform/storage/`. Observability sinks live under `platform/telemetry/`. First-party source lives under `x-webkernel/`. Composer vendor-dir is `platform/dependencies/`. Canonical platform config is `config/platform.php`.

```
refactor/
├── composer.json                      # Install + dependency graph (PSR, first-party, extensions)
├── webkernel                          # Host CLI (`php webkernel …`)
├── config/
│   └── platform.php                   # Canonical platform config (autoload + identity)
├── modules/                           # Installed business modules
├── public/
│   ├── index.php                      # Front controller
│   ├── favicon.ico
│   └── robots.txt
├── platform/
│   ├── bootstrap/
│   │   ├── app.php                    # WebApp::configure() → create()
│   │   └── fast-boot.php              # Config autoload (hot) / CLI composer install (miss)
│   ├── dependencies/                  # Composer vendor-dir
│   ├── temporary/                     # Host-writable scratch (never sys_get_temp_dir)
│   ├── storage/
│   │   ├── app/
│   │   ├── backups/
│   │   ├── framework/
│   │   │   ├── cache/                 # Compiled routes (`routes_{hash}.php`)
│   │   │   └── views/                 # Compiled templates (`*.view.php.compiled`)
│   │   └── instance/data/instance_id
│   └── telemetry/                     # See telemetry guide
│       ├── logs/{access,app,system}/
│       ├── metrics/{counters,gauges,histograms}/
│       ├── traces/{active,spans}/
│       ├── profiles/{cpu,memory}/
│       └── buffer/{shm,queue}/
└── x-webkernel/
    ├── docs/guides/                   # Numbered folders, `{name}.{lang}.md`
    ├── codebase/                      # Kernel runtime (path repo)
    └── lifecycle/                     # Composer plugin (install paths, dump-autoload)
```

## What belongs where

| Path | Role |
| --- | --- |
| `public/` | Only web-reachable files. Document root for `php -S`. |
| `config/platform.php` | Canonical platform config. Fast-boot reads `autoload` here. Platform-managed keys are rewritten through `ConfigWriter`. |
| `platform/bootstrap/` | Process boot. `fast-boot.php` requires the autoload path from config. `app.php` wires middleware, exceptions, routes. |
| `modules/` | Business modules installed by `webkernel/lifecycle` (custom Composer types). Not scanned on the request path. A module may declare its own Composer dependencies. |
| `platform/storage/` | Generated artifacts. Safe to wipe; dump-autoload and the compilers rebuild them. |
| `platform/telemetry/` | Observability contract. See [Telemetry](../05-telemetry/telemetry.en.md). |
| `x-webkernel/` | First-party packages. Path-repo source; Composer symlinks them into `platform/dependencies/webkernel/`. |
| `platform/dependencies/` | Composer vendor-dir. PSR packages, first-party code, and whatever modules required. |

## Compared to `first_shot`

The refactor folds host `bootstrap/`, `storage/`, `routes/`, and `resources/` into `platform/`. The front controller becomes:

```php
(require "$webapp_path/platform/bootstrap/app.php")
    ->handle_request(\Webkernel\Http\Request::capture());
```

`webkernel/codebase` is the kernel runtime (router, view, console, container, fluent composables). The HTTP cycle is documented in [HTTP kernel](../02-http-kernel/http-kernel.en.md).
