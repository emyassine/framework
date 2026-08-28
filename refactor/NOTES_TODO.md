# Webkernel refactor — build contract

This file is the map. `NOTES.md` is the product shape (panels, resources, pages). This file is what we actually build, in what order, and why the old work felt like spaghetti.

If a term is used, it is defined here. No AST. No hidden Container. No `webapp()`.

---

## 0. Why you could not see the system

In `_workbench_one` there are **three different boots**, and they do not do the same thing.

### HTTP today (`public/index.php`)

```
public/index.php
  → platform/bootstrap/fast-boot.php     (require autoload)
  → Webkernel\Index::start_http()
      → Container::get_instance()
      → CompilationStore::get('webkernel.global.routes', $container)
      → RequestClassifier → some Handler → emit
```

`Index` talks to `Container`. `Container` talks to a compilation store. The classifier picks a handler. `WebApp` is not in this path. Providers are not in this path. You cannot point at one file and say "this is a request."

### CLI today (`./webkernel`)

```
./webkernel
  → platform/bootstrap/app.php
      → fast-boot.php                    (autoload, again)
      → WebApp::configure()
            ->with_middleware(...)
            ->with_exceptions(...)
            ->create()                   (= boot)
                → ConfigComposable::load()
                → load dumped composable map
                → load dumped providers
                → ProviderRegistry::providers()
                      **globs** modules/*/*Provider.php at runtime
                → new Provider; register($container); boot($container)
  → Index::start_terminal($webapp)
      → $webapp->handle_command()
```

Now you have `WebApp` **and** `Index` **and** `Container` **and** `ProviderRegistry`. The HTTP path and the CLI path do not share a brain.

### Then, on top of that

- `webapp()` global function → `WebApp` singleton → `__call` → composable class from a dump map → `Container::make()`
- `view()` global, `webapp_path()` global, `vendor_dir()` global
- `WebApp::boot()` hardcodes `resources/views` as a platform-wide view path
- `BlogProvider::register(Container $container)` exists so the Container never dies
- Two meanings of "provider": dump list, plus a glob of `*Provider.php`

That is why it feels spaghetti. It is. The goal of this refactor is that you can **read two files** and understand a request.

---

## 1. The rule you can hold in your head

**Three moments. Never mix them.**

| Moment | When | What you do | What the machine does |
|---|---|---|---|
| **Author** | You type PHP | Write one `PlatformProvider` per package, with `const ROUTES`, `const VIEWS`, … | Nothing |
| **Composer** | `composer dump-autoload` (and again when a provider changes) | Nothing | Read each provider **statically**, write PHP arrays under `platform/dependencies/packagist/composer/webkernel_*.php` |
| **Request** | Browser or `./webkernel` | Nothing | `require` autoload, `require` those arrays, dispatch. No `new Provider`. No Container. No glob. No global functions. |

Composer time may use Reflection. Request time may not.

If a provider is **constants and static methods only**, dump-autoload never even instantiates it. That is the 100% static world. Rebuild on every dump. Dev can later re-dump when a provider file is newer than the dump file. Request path stays a `require`.

---

## 2. The two doors

There is no `Index`. There is no `WebApp`. There is no `Container`.

### HTTP — `public/index.php`

```php
<?php
declare(strict_types=1);

define('START_REQUEST', hrtime(true));

$webapp_path = dirname(__DIR__);

if (is_file($maint = $webapp_path.'/platform/temporary/maintenance.php')) {
    require $maint;
    return;
}

require $webapp_path.'/platform/fast-boot.php';   // autoload.php only

\Webkernel\Http::run();
```

`Http::run()`:

1. `Config::boot()` — load `config/platform.php` + `platform/platform-runtime.php` into memory
2. `require` dumped routes (`webkernel_routes.php`)
3. Match URI + method
4. Run the page (panel page or resource page)
5. Render `.view.php`
6. Emit

### CLI — `./webkernel`

```php
#!/usr/bin/env php
<?php
declare(strict_types=1);

define('START_REQUEST', hrtime(true));

require dirname(__DIR__).'/platform/fast-boot.php';

exit(\Webkernel\Console::run($argv));
```

`Console::run()`:

1. `Config::boot()`
2. `require` dumped commands (`webkernel_commands.php`)
3. Dispatch argv
4. Return exit code

Two doors. Two classes. Same autoload. Same config. Same dumps.

`fast-boot.php` does **one job**: make sure `platform/dependencies/packagist/autoload.php` is required. It does not start HTTP. It does not start CLI. It does not touch Container.

---

## 3. What is abolished

| Old | Why it dies | Replaced by |
|---|---|---|
| `Webkernel\Container\Container` | Hidden graph. You cannot see who creates what. | Nothing. Static classes + dumped arrays. Tests call `Config::flush()` / `View::flush()`. |
| `Webkernel\Registry` as a general service locator | Same smell as Container under another name | Do not build it. |
| `webapp()` | Global + god object | Class aliases: `Config`, `View`, `Route` |
| `view()`, `webapp_path()`, `vendor_dir()` | Global functions | `View::make()`, `Config::get('paths.webapp')`, dump file `webkernel.php` |
| `WebApp` | Boot + declare + `__call` + emit + CLI + HTTP in one class | `Http` and `Console` |
| `Index` | Third boot, talks to Container | Deleted |
| `ComposableContract` as the public API | `webapp()->foo()` requires a map, a container lifetime, and magic | Direct static API on the class that owns the job |
| `ProviderRegistry` glob `modules/*/*Provider.php` | Filesystem scan on the request path | Dump-autoload list from `extra.webkernel.provider` |
| `register(Container)` / `boot(Container)` | Keeps the Container alive | Providers are dump-time data. They do not run on a request. |
| `Webkernel\Support\*` | NOTES.md | Does not exist |
| Host `resources/views` hardcoded in boot | Breaks modularity | Views come from provider `VIEWS` only |
| `discover_resources()` / `discover_pages()` at request time | Filesystem scan | Listed in the dump from the provider |
| camelCase on Webkernel surfaces | Project law | `snake_case` methods, `PascalCase` classes |

`NOTES.md` copied a Filament-shaped `PanelProvider` with `discoverResources`, `brandLogo`, eleven middleware. That is the **shape of the UI**, not the PHP API. The PHP API is snake_case. Discovery is dump-autoload, not a boot-time glob.

---

## 4. Config — class alias, not a composable

`namespacer.php` registers the autoloader **and** class aliases. It does not define helper functions.

```php
class_alias(\Webkernel\Config\Config::class, 'Config');
class_alias(\Webkernel\View\View::class, 'View');
class_alias(\Webkernel\Route\Route::class, 'Route');
```

Usage:

```php
Config::get('branding.logo', 'default.svg');
Config::set('branding.logo', $path)->get('branding.logo');
```

### Files

| File | Who writes it | Purpose |
|---|---|---|
| `config/platform.php` | dump-autoload (identity, `autoload` path) | Platform-managed. Do not `Config::set` into this file. |
| `config/app.php` | You | App defaults. Read-only at request time. |
| `platform/platform-runtime.php` | `Config::set` | Runtime writes. Atomic tmp → rename (already exists as `ConfigWriter`). |

`Config::boot()` merges, in order: `config/platform.php`, `config/app.php`, then `platform/platform-runtime.php`. Runtime wins. In-memory tree after that. `get` is an array walk on dot notation. No file I/O on `get`.

`Config::set('a.b', $value)`:

1. Updates the in-memory tree
2. Writes `platform/platform-runtime.php` atomically
3. Returns `Config` so you can chain `->get('a.b')` as a read-back check
4. If the write fails, the in-memory tree is **not** left half-updated (write tmp, rename, then commit memory — or roll memory back). `ConfigWriter` already does tmp + rename + `opcache_invalidate`

Dump-autoload still stamps identity keys into `config/platform.php` (`id`, `hostname`, `autoload`, …). That is Composer time, not `Config::set`.

---

## 5. One provider per package

Every Webkernel package (codebase sub-area, business module, feature) declares **exactly one** class:

```json
{
  "name": "acme/billing",
  "type": "webkernel-business-module",
  "extra": {
    "webkernel": {
      "provider": "Acme\\Billing\\BillingProvider",
      "prefix": "billing"
    }
  }
}
```

- Key is `extra.webkernel.provider`
- Value is **one** FQCN, not an array
- That class extends `Webkernel\PlatformProvider`
- `declaration_class` from `NOTES.md` is this. One name, not two.

### What a provider is

A provider is a **declaration**. It is not a service. It is not constructed on a request.

```php
<?php
declare(strict_types=1);

namespace Acme\Billing;

use Webkernel\PlatformProvider;

final class BillingProvider extends PlatformProvider
{
    public const ROUTES      = [__DIR__.'/routes.php'];
    public const VIEWS       = [__DIR__.'/resources/views'];
    public const COMPONENTS  = [__DIR__.'/resources/views/components'];
    public const COMMANDS    = [\Acme\Billing\Console\GenerateSitemapCommand::class];
    public const PANELS      = [\Acme\Billing\Presentation\BillingPanelProvider::class];
    public const CONFIG      = [
        'billing.vat_rate' => 0.20,
    ];
    public const ACL         = [
        'billing.invoice.view'   => ['admin', 'accountant'],
        'billing.invoice.delete' => ['admin'],
    ];
}
```

Constants are the default. If a path cannot be a constant, a **static** method with the same name is allowed (`public static function views(): array`). Dump-autoload calls the static method. Still no instance.

`register()` and `boot()` do not exist.

### Dump-autoload (Composer plugin, already the right hook)

`webkernel/lifecycle` on `post-autoload-dump`:

1. Read `installed.json`
2. For each package with `extra.webkernel.provider`, load that class
3. Read constants (and static methods if present) **without** `new` if possible
4. Write:

```
platform/dependencies/packagist/composer/
  webkernel.php              # instance_id, webapp_root, vendor_dir
  webkernel_providers.php    # list of FQCNs (debug / IDE, not executed at request)
  webkernel_routes.php       # list of route files
  webkernel_views.php        # list of view dirs + namespaces
  webkernel_components.php   # component dirs
  webkernel_commands.php     # command classes
  webkernel_panels.php       # panel provider classes
  webkernel_acl.php          # merged ACL map
  webkernel_config.php       # merged provider CONFIG defaults
```

Request code only `require`s these files. It never looks at `extra.webkernel`. It never instantiates `BillingProvider`.

The codebase package itself has **one** provider too (`Webkernel\CodebaseProvider`) that declares kernel views (`layouts.page`, …), kernel routes if any, kernel commands (`dump-autoload`). Not four providers (`ViewProvider`, `CoreProvider`, …). One.

---

## 6. Namespaces

Two roots. No overlap. `Webkernel\Support` does not exist.

### `Webkernel\` — runtime (before a request is a page)

| Class | Job |
|---|---|
| `Webkernel\Config\Config` | `get` / `set` / `boot` / `flush` |
| `Webkernel\Config\ConfigWriter` | Atomic PHP array file write (already exists, copy it) |
| `Webkernel\View\View` | `make` / `share` / `directive` / `render` |
| `Webkernel\View\Compiler` | BladeOne, owned in-tree |
| `Webkernel\Route\Route` | Declare + dispatch |
| `Webkernel\Http` | HTTP door |
| `Webkernel\Console` | CLI door |
| `Webkernel\PlatformProvider` | Base declaration class (dump-time) |
| `Webkernel\Lifecycle\` | Composer plugin, package types, install paths |
| `Webkernel\Cache\CompilationStore` | Compiled views / compiled routes on disk |

### `Webkernel\Platform\` — UI (a request that is a panel)

| Class | Job |
|---|---|
| `Webkernel\Platform\Panel` | Panel definition (id, path, scope, pages, resources) |
| `Webkernel\Platform\PanelProvider` | Declares one panel (system or module). Distinct from `PlatformProvider`. |
| `Webkernel\Platform\Pages\Page` | Base page (standalone **or** resource-owned) |
| `Webkernel\Platform\Pages\Dashboard` | Built-in standalone panel page |
| `Webkernel\Platform\Resources\Resource` | CRUD class: model, `form()`, `table()`, `pages()` |
| `Webkernel\Platform\Tables\Table` | Table schema |
| `Webkernel\Platform\Schemas\Schema` | Form / filter schema |
| `Webkernel\Platform\Widgets\` | Widgets |
| `Webkernel\Platform\Colors\Color` | Palette |
| `Webkernel\Platform\System\SystemPanelProvider` | Platform-scoped System Admin Panel |

**Two provider types — do not mix the names:**

- `Webkernel\PlatformProvider` — package declaration (routes, views, which panels). One per Composer package. Dump-time only.
- `Webkernel\Platform\PanelProvider` — one admin UI (system panel, billing panel). Listed in the package provider's `PANELS` constant.

---

## 7. Domain tree (from NOTES.md, corrected)

```
Platform
  App Owner(s)
  System Admin Panel              [platform-scoped]
  Module (1..N)
    Feature (0..N)                [injects into the module, does not create a panel]
    Admin Panel (1..N)            [module-scoped]
      Page (0..N)                 [standalone — no Resource]
      Cluster (0..N)
        Page (0..N)               [standalone, grouped in nav]
        Resource (1..N)           [CRUD for one model]
          Page (1..N)             [List / Create / Edit / View / custom]
            Component (1..N)      [Table, Form, Widget, Custom View]
```

### Page without Resource

A page does not need a Resource. Dashboard, reports, wizards, settings: standalone pages on the Panel (or on a Cluster for navigation). They have a route, a view, permissions. They do not get a table/form from a Resource because there is no model CRUD.

### Resource

A Resource is a static class that builds the CRUD interface for **one model**. It describes how administrators interact with that data through a table and a form. It **owns its pages**. The panel registers the Resource. The Resource registers List/Create/Edit.

```php
final class InvoiceResource extends Resource
{
    protected static string $model = Invoice::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([ /* fields */ ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([ /* columns */ ]);
    }

    /** @return array<string, class-string> */
    public static function pages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
```

On disk:

```
Presentation/Resources/
  InvoiceResource.php
  InvoiceResource/
    Pages/
      ListInvoices.php
      CreateInvoice.php
      EditInvoice.php
```

Dump-autoload records `InvoiceResource`. It does not scan `Pages/`. Those pages exist because `pages()` said so.

| Kind | Owner | Needs a Resource? | Registered how |
|---|---|---|---|
| Panel page | Panel or Cluster | No | `Panel::pages()` listed on the PanelProvider, dumped |
| Resource page | Resource | Yes | `Resource::pages()`, dumped with the Resource |

`Dashboard` is never a Resource. Branding is never a Resource. Branding is `Config`.

### Panel scope

Every panel is `platform` or `module`. Enforced when the panel is dumped, not as a runtime tag.

- Platform-scoped: System Admin Panel. Administers modules. Does not own module models.
- Module-scoped: lives under `modules/{vendor}/{name}` because the Composer type is `webkernel-business-module`. Cannot promote itself to platform.

### UI schema (not an AST)

An AST is what a **parser** emits from source text. Webkernel already uses that word for languages (`Lexer → Parser → AST → Validator`). There is no UI language here.

The PHP classes **are** the schema: Panel, Resource, Page, Table, Schema. Walk them and render `.view.php`. JSON serialisation is optional later for a visual builder. Do not build a parser. Do not call this an AST.

---

## 8. Views and routes — modular, not hardcoded

Templates are `*.view.php`. Compiler is BladeOne **owned in this package**, not a Composer library. Compiled files: `platform/storage/framework/views/`.

A module declares:

```php
public const VIEWS  = [__DIR__.'/resources/views'];
public const ROUTES = [__DIR__.'/routes.php'];
```

Dump-autoload writes those paths into `webkernel_views.php` / `webkernel_routes.php` with a namespace derived from `extra.webkernel.prefix` (example: `billing`).

```
@include('billing::invoices.index')
@include('webkernel::layouts.page')
```

Kernel layouts stay in the codebase package provider (`webkernel` namespace). Host `platform/resources/views` is **not** a secret global bag. If the host needs a view, the host has a provider too — or it does not.

A platform-wide `routes.php` is allowed for tests only. Production routes live in packages and modules, dumped.

---

## 9. Package types (already in lifecycle — keep)

```php
enum LCPackageType: string
{
    case Assets                = 'webkernel-assets';
    case Component             = 'webkernel-component';
    case DevTool               = 'webkernel-devtool';
    case Stdlib                = 'webkernel-stdlib';
    case Element               = 'webkernel-element';
    case Agent                 = 'webkernel-agent';
    case Ffi                   = 'webkernel-ffi';
    case BusinessModule        = 'webkernel-business-module';
    case BusinessModuleFeature = 'webkernel-business-module-feature';
    case PlatformModule        = 'webkernel-platform-module';
    case PlatformModuleFeature = 'webkernel-platform-module-feature';
}
```

| Type | Installs to |
|---|---|
| `webkernel-business-module` | `modules/{vendor}/{name}` |
| `webkernel-business-module-feature` | `modules/{parentVendor}/{parentName}/features/{vendor}-{name}` |
| `webkernel-ffi` | `ffi/{vendor}/{name}` |
| All others | `{vendor_dir}/{vendor}/{name}` |

Features inject resources and pages into an **existing** module panel. They do not create panels. They still have exactly one `extra.webkernel.provider`.

Composer path repos for `x-webkernel/*` with `"symlink": true` are **fine** — that is Composer linking the package you are editing. Forbidden: `ln -s _workbench_one/...` into `refactor/`. Copy and adapt.

---

## 10. Layout (host)

```
refactor/
├── public/index.php                 # HTTP door
├── webkernel                        # CLI door
├── composer.json
├── NOTES.md                         # product shape
├── NOTES_TODO.md                    # this file
├── config/
│   ├── platform.php                 # identity + autoload (dump stamps)
│   └── app.php                      # app defaults
├── modules/{vendor}/{name}/         # business modules
├── platform/
│   ├── fast-boot.php                # autoload only
│   ├── platform-runtime.php         # Config::set target
│   ├── dependencies/                # packagist + node_modules
│   ├── storage/framework/views/     # compiled templates
│   ├── storage/framework/cache/     # compiled routes
│   ├── temporary/
│   └── telemetry/
└── x-webkernel/
    ├── codebase/                    # runtime + platform UI
    └── lifecycle/                   # Composer plugin
```

---

## 11. What we copy from old work vs what we leave

Copy (adapt to snake_case, strip Container):

- `View/View.php`, `View/Compiler.php`, `View/Engine.php`, `View/Js.php`, kernel `views/layouts/*`
- `Route/*` (MarkBased dispatcher, Binding, compile)
- `Config/ConfigWriter.php`
- `Lifecycle/*` (installer + package types) — already in refactor
- `namespacer.php` — strip `webapp()`, keep autoload + add class aliases
- Console attribute + argv parsing (not Symfony Console)

Do not copy:

- `Container/`
- `WebApp.php`
- `Index.php` as it exists (HTTP+CLI+Container)
- `Composables/*` as the public API
- `Provider/ProviderRegistry.php` (the glob)
- `Http/CoreProvider.php` + `View/ViewProvider.php` as two providers (fold into one `CodebaseProvider`)
- Host `resources/views` hardcoded in boot
- `webapp()` / `view()` / `webapp_path()` function files

---

## 12. What this implies (honest)

Applying `NOTES.md` as a Filament clone plus a kernel rewrite is **weeks**, not two days.

Applying **this file** means:

1. The boot becomes readable. You can follow a request without an agent.
2. Every package has one declaration class. Composer turns it into arrays. The request reads arrays.
3. Config is `Config::get` / `Config::set`. No composable, no container.
4. You still need the View compiler and the router — those are real code, copied from old work, not reinvented.
5. Panel / Resource / Page is **new** code, small if we only render: one dashboard page + one Resource with List/Create/Edit.
6. We do **not** build: Retool JSON builder, eleven Filament middleware, boot-time `discover_*`, a general Registry, granular ACL UI, feature packages, branding editor.

Container removal is not a rename. Old work is soaked in `Container`. The way to win is: **do not copy those files**. Copy View and Route. Write Http and Console as twenty-line doors.

Performance contract stays: no directory walk on the request path, no reflection on the request path, dumped PHP `require`, OPcache.

---

## 13. Build order (do in this order, stop when the app runs)

Each step must leave the previous door working.

### Step A — Doors

- [ ] `public/index.php` → `fast-boot.php` → `Webkernel\Http::run()` (can print a plain string)
- [ ] `./webkernel` → `fast-boot.php` → `Webkernel\Console::run($argv)` (can print help)
- [ ] Delete any use of `Index`, `WebApp`, `Container` in refactor (refactor barely has them; do not copy them in)

### Step B — Config + namespacer

- [ ] `namespacer.php`: autoload + `class_alias` for `Config`, `View`, `Route`. No functions.
- [ ] `Webkernel\Config\Config`: `boot`, `get`, `set` (writes `platform/platform-runtime.php`), `flush`
- [ ] Copy `ConfigWriter`
- [ ] One check: `Config::set('x.y', 1)->get('x.y') === 1` and the runtime file exists

### Step C — One provider, dump-autoload

- [ ] `Webkernel\PlatformProvider` base (constants only)
- [ ] `Webkernel\CodebaseProvider` — the single codebase provider (`VIEWS` = kernel layouts)
- [ ] `extra.webkernel.provider` on `x-webkernel/codebase/composer.json`
- [ ] Lifecycle dump writes `webkernel_views.php`, `webkernel_providers.php`, `webkernel.php`
- [ ] Request reads the dump. Dump does not instantiate the provider if constants suffice

### Step D — View engine (copy)

- [ ] Copy View / Compiler / Engine / kernel layouts from old work
- [ ] Strip Container from them
- [ ] `Http::run()` renders `webkernel::layouts.simple` with a hello string
- [ ] Compiled files land in `platform/storage/framework/views/`

### Step E — Router (copy)

- [ ] Copy Route engine
- [ ] Kernel or test `routes.php` dumped via the codebase provider
- [ ] `Http::run()` matches `/` and renders the view

### Step F — Panel page (no Resource)

- [ ] `Panel`, `PanelProvider`, `Page`
- [ ] `SystemPanelProvider` (platform scope) with `Dashboard` as a standalone page
- [ ] Dumped via `CodebaseProvider::PANELS`
- [ ] `/system` (or `/`) shows Dashboard through the view engine

### Step G — Resource (CRUD unit)

- [ ] `Resource`, `Table`, `Schema`
- [ ] One demo module (`modules/...`) with **one** `PlatformProvider`
- [ ] That provider declares `VIEWS`, `ROUTES`, `PANELS`
- [ ] One `InvoiceResource` (or whatever the app's first model is) with List / Create / Edit pages
- [ ] Panel registers the Resource; Resource owns the pages
- [ ] Persistence: the smallest thing that works (array / sqlite). Not an ORM.

### Step H — Only if the app needs it after G

- [ ] Auth middleware (one class, not eleven)
- [ ] Module-scoped ACL from dumped `webkernel_acl.php`
- [ ] Branding keys in Config, read by the panel layout (no settings Resource)
- [ ] Feature packages
- [ ] JSON dump of the UI schema for a visual builder

---

## 14. Naming law

| Kind | Rule | Example |
|---|---|---|
| Methods, functions, parameters, config keys | `snake_case` | `Config::get()`, `pages()`, `vat_rate` |
| Classes, namespaces | `PascalCase` | `PlatformProvider`, `InvoiceResource` |
| Constants | `UPPER_SNAKE` | `ROUTES`, `VIEWS` |
| Composer extra keys | `snake_case` | `extra.webkernel.provider` |

PSR / Composer plugin methods we do not own stay as the interface wrote them (`activate`, `getSubscribedEvents`).

---

## 15. How to know you understand it

You should be able to answer, without opening an agent:

1. A request arrives. Which file runs first? (`public/index.php`)
2. Who loads classes? (`fast-boot.php` → Composer autoload → `namespacer.php`)
3. Who knows the view directories? (dumped `webkernel_views.php`, written at dump-autoload from `const VIEWS`)
4. Who knows branding? (`Config::get`, files in section 4)
5. Where is a new CRUD screen declared? (a `Resource` class, `pages()`)
6. Where is a Dashboard declared? (a `PanelProvider`, standalone page, no Resource)
7. When does a Provider run? (**Never** on a request. At dump-autoload only, and only if a static method must be called.)

If you cannot answer one of those, this file is still missing a sentence. Add it. Do not add a Container to hide the answer.
