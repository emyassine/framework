# Project layout

Physical tree of the refactor host. Runtime caches live under `platform/storage/`. Observability sinks live under `platform/telemetry/`. First-party source lives under `x-webkernel/`. Composer vendor-dir is `third_party/`.

```
refactor/
├── composer.json                      # Install + dependency graph (PSR, first-party, extensions)
├── webkernel                          # Host CLI (`php webkernel …`)
├── public/
│   ├── index.php                      # Front controller
│   ├── favicon.ico
│   └── robots.txt
├── platform/
│   ├── bootstrap/
│   │   ├── app.php                    # WebApp::configure() → create()
│   │   ├── fast-boot.php              # Autoload (hot) / CLI composer install (miss)
│   │   └── cache/
│   ├── modules/                       # Installed business modules (Composer types via lifecycle)
│   ├── temporary/                     # Host-writable scratch (never sys_get_temp_dir)
│   ├── storage/
│   │   ├── app/
│   │   ├── backup/
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
    └── lifecycle/                     # Composer plugin (install paths, dump-autoload)
```

## What belongs where

| Path | Role |
| --- | --- |
| `public/` | Only web-reachable files. Document root for `php -S`. |
| `platform/bootstrap/` | Process boot. `fast-boot.php` loads `third_party/autoload.php`. `app.php` wires middleware, exceptions, routes. |
| `platform/modules/` | Business modules installed by `webkernel/lifecycle` (custom Composer types). Not scanned on the request path. A module may declare its own Composer dependencies. |
| `platform/storage/` | Generated artifacts. Safe to wipe; dump-autoload and the compilers rebuild them. |
| `platform/telemetry/` | Observability contract. See [Telemetry](../05-telemetry/telemetry.en.md). |
| `x-webkernel/` | First-party packages. Path-repo source; Composer symlinks them into `third_party/webkernel/`. |
| `third_party/` | Composer vendor-dir. PSR packages, first-party code, and whatever modules required. |

## Compared to `first_shot`

The refactor folds host `bootstrap/`, `storage/`, `routes/`, and `resources/` into `platform/`. The front controller becomes:

```php
(require "$webapp_path/platform/bootstrap/app.php")
    ->handle_request(\Webkernel\Http\Request::capture());
```

`webkernel/codebase` (router, view, console, container) is not in this tree yet; the lifecycle plugin and the host layout are. The HTTP cycle documented in [HTTP kernel](../02-http-kernel/http-kernel.en.md) is the `first_shot` runtime this layout is being rebuilt around.
