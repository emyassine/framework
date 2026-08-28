# Webkernel refactor — NOTES.md + decisions

This file **is** `NOTES.md`, plus the decisions you added after it (no Container, no global functions, one provider per package, `Config::get` / `Config::set`, two doors).

If something is in `NOTES.md`, it is in this file. If something is **not** in `NOTES.md`, it is labelled **Decision** (you said it) or **Not specified** (do not invent it).

`NOTES.md` stays the product shape. This file is the same shape with the boot made visible.

---

## How to read this

| Label | Meaning |
|---|---|
| **Spec** | From `NOTES.md`. We build toward it. |
| **Decision** | You said it after `NOTES.md`. It wins over `NOTES.md` where they clash. |
| **Not specified** | No design yet. Do not put a constant or a class for it. |

---

# Part I — What you said after NOTES.md (the clashes)

## Decision: no Container

`NOTES.md` §4 replaces Laravel-style DI with a `Registry` (string key → instance). You then said: there will be no Container.

**What that means:** do not copy `Webkernel\Container`. Do not `register($container)` on providers.

**What happens to `NOTES.md` Registry:** Config, View, and Route become static class aliases (`Config::get`, `View::make`, `Route::get`). Those three do not need a Registry. The Registry class from `NOTES.md` §4 is kept as a spec for *other* services later (JSON canonicaliser, event dispatcher, compilation store) if a static class is not enough. It is not a Container. It is not built in the first cut. See Part II §4.

## Decision: no global functions

But we maintaine the composables with `webapp()`, `view()` and `webapp_path()`.
`namespacer.php` is autoload + `class_alias` only.

```php
class_alias(\Webkernel\Config\Config::class, 'Config');
class_alias(\Webkernel\View\View::class, 'View');
class_alias(\Webkernel\Route\Route::class, 'Route');
```

## Decision: one provider per Composer package

`NOTES.md` §6 called it `extra.webkernel.declaration_class`. **One name now:** `extra.webkernel.provider`. Exactly one FQCN. The class extends `Webkernel\PlatformProvider`. It is a **declaration** (paths and class lists). It is not a service. It does not run on a request.

Dump-autoload reads it and writes PHP arrays. The request `require`s those arrays.

## Decision: Config is a class, not a composable

```php
Config::get('branding.logo', 'default.svg');
Config::set('branding.logo', $path)->get('branding.logo');
```

`set` writes `platform/platform-runtime.php` (atomic tmp → rename). It does **not** write `config/platform.php` (that file is identity + autoload, stamped by dump-autoload).

This **is** `NOTES.md` §5 (branding not hardcoded). The access path changes from `Registry::get('platform.config')` to `Config::get('branding.favicon')`.

## Decision: two doors, no Index, no WebApp

Old work (`_workbench_one`) has three boots that do not do the same thing. That is the spaghetti.

**HTTP today:**

```
public/index.php
  → fast-boot.php          (autoload)
  → Index::start_http()
      → Container::get_instance()
      → CompilationStore::get('webkernel.global.routes', $container)
      → RequestClassifier → Handler → emit
```

`WebApp` is not in this path. Providers are not in this path.

**CLI today:**

```
./webkernel
  → bootstrap/app.php
      → fast-boot.php
      → WebApp::configure()->create()
          → Container
          → glob modules/*/*Provider.php
          → new Provider; register($container); boot($container)
  → Index::start_terminal($webapp)
```

**HTTP after this file:**

```
public/index.php
  → platform/fast-boot.php     // autoload only
  → Webkernel\Http::run()
```

**CLI after this file:**

```
./webkernel
  → platform/fast-boot.php
  → Webkernel\Console::run($argv)
```

`fast-boot.php` does not start HTTP or CLI. It requires `autoload.php`. That is the whole job.

---

# Part II — NOTES.md specs (complete)

Foreword from `NOTES.md`:

- Old work is `_workbench_one`. Copy and adapt. Do not symlink that tree into refactor.
- Views from old work and their compilation stay. They are **not** hardcoded as a platform-wide bag. Views live in each module's `resources/views`, declared on that package's provider.
- Same for routes. A host `routes.php` is allowed for tests. Production routes live in packages and modules.
- No camelCase on Webkernel surfaces (`snake_case` methods, `PascalCase` classes).
- Composer path-repo `"symlink": true` for `x-webkernel/*` is Composer linking the package you edit. That is allowed. `ln -s _workbench_one` is not.

---

## 1. Namespace contract — Spec (`NOTES.md` §1)

Two root namespaces. No overlap. `Webkernel\Support\*` does not exist.

### `Webkernel\` — runtime and lifecycle

Everything that runs before a request is a page, or orthogonally to UI.

From `NOTES.md`:

- Registry (see §4 — not first cut)
- JSON Canonicalisation
- Event Dispatcher
- Lifecycle and Composer Installer
- View Engine (`Webkernel\View\View`)
- Cache and Compilation Store
- Console Commands

**Decision (added):**

- `Webkernel\Config\Config` — `get` / `set` / `boot` / `flush`
- `Webkernel\Http` — HTTP door
- `Webkernel\Console` — CLI door
- `Webkernel\PlatformProvider` — package declaration (dump-time). This is `declaration_class` from `NOTES.md` §6.

**Decision (removed from public API):** Composables / `webapp()`. The jobs they wrapped become the static classes above.

### `Webkernel\Platform\` — UI and panel system

Everything that touches the panel interface, rendering, and HTTP context.

From `NOTES.md`, all of these remain:

- `Webkernel\Platform\Panel`
- `Webkernel\Platform\PanelProvider`
- `Webkernel\Platform\Colors\Color`
- `Webkernel\Platform\Pages\Dashboard`
- `Webkernel\Platform\Widgets\AccountWidget`
- `Webkernel\Platform\Widgets\InfoWidget`
- `Webkernel\Platform\Tables\Table`
- `Webkernel\Platform\Schemas\Schema`
- `Webkernel\Platform\Resources\Resource`
- `Webkernel\Platform\RenderHooks\RenderHook`
- `Webkernel\Platform\Http\Middleware\Authenticate`
- `Webkernel\Platform\Http\Middleware\StartSession`
- `Webkernel\Platform\Http\Middleware\EncryptCookies`
- `Webkernel\Platform\Http\Middleware\PreventRequestForgery`
- `Webkernel\Platform\Http\Middleware\SubstituteBindings`
- `Webkernel\Platform\Http\Middleware\DisableIconComponents`
- `Webkernel\Platform\Http\Middleware\DispatchServingEvent`
- `Webkernel\Platform\Http\Middleware\AddQueuedCookiesToResponse`
- `Webkernel\Platform\Http\Middleware\AuthenticateSession`
- `Webkernel\Platform\Http\Middleware\ShareErrorsFromSession`

The eleven middleware are **spec**. They are not the first cut (one auth class is enough to render a panel). They are not deleted from the spec.

**Do not mix these two classes:**

| Class | From | Job | When it runs |
|---|---|---|---|
| `Webkernel\PlatformProvider` | Decision (was `declaration_class`) | Tells dump-autoload which route files, view dirs, panel classes this **package** has | Composer dump only |
| `Webkernel\Platform\PanelProvider` | `NOTES.md` §2 | Configures **one admin UI** (id, path, scope, pages, resources) | Dump reads `panel()`; request uses the dumped panel |

---

## 2. Panel scoping — Spec (`NOTES.md` §2)

Every panel is either `platform`-scoped or `module`-scoped. This is not a runtime tag. It is enforced when the panel is registered (dump-autoload), as part of the panel's declaration.

A **platform-scoped** panel manages cross-module or platform-wide concerns. The System Admin Panel is the canonical example. It administers modules. It does not own their domain models.

A **module-scoped** panel is declared inside a business module. Its disk location is determined by Composer type `webkernel-business-module` → `modules/{vendor}/{name}`. A module-scoped panel cannot promote itself to platform scope.

`NOTES.md` example, with **Decision** applied: snake_case, no request-time `discover_*`, branding from `Config` not hardcoded.

```php
<?php
declare(strict_types=1);

namespace Webkernel\Platform\System;

use Webkernel\Platform\Panel;
use Webkernel\Platform\PanelProvider;
use Webkernel\Platform\Pages\Dashboard;
use Webkernel\Platform\Widgets\AccountWidget;
use Webkernel\Platform\Widgets\InfoWidget;
use Webkernel\Platform\Http\Middleware\Authenticate;
use Webkernel\Platform\Http\Middleware\AuthenticateSession;
use Webkernel\Platform\Http\Middleware\DisableIconComponents;
use Webkernel\Platform\Http\Middleware\DispatchServingEvent;
use Webkernel\Platform\Http\Middleware\EncryptCookies;
use Webkernel\Platform\Http\Middleware\AddQueuedCookiesToResponse;
use Webkernel\Platform\Http\Middleware\PreventRequestForgery;
use Webkernel\Platform\Http\Middleware\SubstituteBindings;
use Webkernel\Platform\Http\Middleware\StartSession;
use Webkernel\Platform\Http\Middleware\ShareErrorsFromSession;

/**
 * System Admin Panel.
 * Scope: platform — manages modules and platform-wide configuration.
 * Branding is not in this method. See §5.
 */
final class SystemPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('system')
            ->path('system')
            ->scope('platform')
            ->default()
            ->pages([
                Dashboard::class, // standalone panel page — not CRUD, not a Resource
            ])
            ->widgets([
                AccountWidget::class,
                InfoWidget::class,
            ])
            ->resources([
                // listed explicitly, or listed on the package PlatformProvider
                // and dumped. Not globbed on the request.
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableIconComponents::class,
                DispatchServingEvent::class,
            ])
            ->auth_middleware([
                Authenticate::class,
                AuthenticateSession::class,
            ]);
    }
}
```

**Decision vs `NOTES.md` `discoverResources(in:, for:)`:** that API is a filesystem scan. `NOTES.md` §9 step 4 also says no filesystem traversal at runtime. Those two lines in `NOTES.md` contradict. **Resolution:** dump-autoload lists resources from the package provider / panel provider. The request does not scan `Presentation/Resources`. The word `discover_*` is not a request-time method.

Branding (logo, favicon, dark mode, brand logo height) is not hardcoded here. Spec §5.

---

## 3. Domain hierarchy — Spec (`NOTES.md` §3)

```
Platform
  App Owner(s)
  System Admin Panel              [platform-scoped]
  Module (1..N)
    Feature (0..N)                [extends / injects into module]
    Admin Panel (1..N)            [module-scoped]
      Page (0..N)                 [standalone — no Resource]
      Cluster (0..N)
        Page (0..N)               [standalone — grouped in nav]
        Resource (1..N)
          Page (1..N)             [CRUD screens owned by the Resource]
            Component (1..N)      [Table, Form, Widget, Custom View]

Granular Permission Layer         [cross-cutting: panel, resource, page, component, action]
```

A Page does not require a Resource. Dashboard, reports, wizards, and any screen that is not CRUD for a model hang directly on the Panel (or on a Cluster for navigation). A Resource is only the CRUD bundle: it exists when a model needs list/create/edit/view, and then it owns those pages.

Rules from `NOTES.md`:

- The System Admin Panel administers modules. It does not own module domain models.
- Features extend a module. They register additional resources and pages into existing module panels. They do not create new panels.
- Permissions are always namespaced by module. There is no global flat permission table.
- Permission resolution is part of the sub-millisecond boot budget.

### 3.1 Resource — Spec (`NOTES.md` §3)

A **Resource** is a static class that builds the CRUD interface for one model. It describes how administrators interact with that model's data through tables and forms. It is not a page, not a route file, and not a bag of settings.

The Resource **owns its pages**. List, create, edit, view, and any custom screen for that model are declared on the Resource and live next to it. A panel does not register those pages one by one. It registers the Resource; the Resource registers the pages.

```php
<?php
declare(strict_types=1);

namespace Acme\Billing\Presentation\Resources;

use Webkernel\Platform\Resources\Resource;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\Platform\Tables\Table;
use Acme\Billing\Domain\Invoice;
use Acme\Billing\Presentation\Resources\InvoiceResource\Pages;

final class InvoiceResource extends Resource
{
    protected static string $model = Invoice::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // fields
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            // columns
        ]);
    }

    /**
     * @return array<string, class-string>
     */
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

| Kind | Owned by | Required Resource? | Examples | Registered how |
|---|---|---|---|---|
| **Panel page** | The Panel (or a Cluster) | No | `Dashboard`, reports, settings, wizards | `Panel::pages()` |
| **Resource page** | The Resource | Yes | `ListInvoices`, `CreateInvoice`, `EditInvoice` | `Resource::pages()` |

A panel page is a first-class screen. It has a route, a view, components, and permissions. It does not get a table/form from a Resource because there is no model CRUD to describe.

A Resource is never a panel page. `Dashboard` is never a Resource. Branding, colors, and logos are platform **settings** (`Config`), not a Resource.

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

### 3.2 Permissions — Spec (`NOTES.md` §3) and what is **Not specified**

`NOTES.md` says:

- There is a permission layer across panel, resource, page, component, action.
- Namespaced by module. No global flat table.
- Must resolve inside the boot budget.

Old-work fluent docs name a permission like `{module}.{resource}.{action}` (`billing.invoice.delete`) and check it with `can('delete')` inside the module (module inferred) or `acl('billing')->can('delete')` from the System Admin Panel.

**Not specified (do not invent):**

- A `const ACL = ['billing.invoice.view' => ['admin', 'accountant']]` on the package provider.
- That array was copied from old `BlogProvider`. `NOTES.md` does not have it.
- It mixes two different things: (1) the **name** of a permission, (2) **which roles** hold it. Role assignment is data (users, App Owner). It is not a class constant dumped at Composer time.
- Which object is a "role". How a user gets a permission. The ACL class API without `webapp()`.

Until that is specified, a Resource/Page may **name** a permission (`invoice.delete`). The platform prefixes the module. Nobody assigns `'admin'` in a provider constant.

---

## 4. Registry — Spec (`NOTES.md` §4)

No dependency injection container. No reflection at runtime. No configuration parsing per request.

`NOTES.md` pattern: a static map, string key → factory or instance, resolve on first access, `swap` for tests.

```php
<?php
declare(strict_types=1);

namespace Webkernel;

final class Registry
{
    /** @var array<string, mixed> */
    private static array $services = [];

    public static function register(string $id, callable|object $service): void
    {
        self::$services[$id] = $service;
    }

    public static function get(string $id): mixed
    {
        $service = self::$services[$id] ?? null;

        if (is_callable($service)) {
            return self::$services[$id] = $service();
        }

        return $service;
    }

    public static function swap(string $id, callable|object $service): void
    {
        self::$services[$id] = $service;
    }
}
```

Named `Registry`, not `Kernel`. One job: map keys to resolved instances.

**Decision:** View is `View::…`. Config is `Config::…`. Route is `Route::…`. They do not go through Registry. Registry is not built until a fourth runtime service needs a swap and a static class is the wrong shape. It is still spec. It is not a Container.

---

## 5. Dynamic configuration — Spec (`NOTES.md` §5) + Decision (`Config`)

Branding, colors, dark mode, logos, and panel defaults are not hardcoded in `PanelProvider`. They live in the config layer. The App Owner edits them via the System Admin Panel. Permissions decide who can edit globally vs per module. Module panels override keys or inherit. This is **not** a Resource.

`NOTES.md` accessed it as `Registry::get('platform.config')`. **Decision:** `Config::get`.

```php
abstract class PanelProvider
{
    final protected function apply_platform_config(Panel $panel): Panel
    {
        return $panel
            ->favicon(Config::get('branding.favicon'))
            ->brand_logo(Config::get('branding.logo_light'))
            ->dark_mode_brand_logo(Config::get('branding.logo_dark'))
            ->brand_logo_height(Config::get('branding.logo_height', '2rem'))
            ->colors(Config::get('branding.colors', ['primary' => \Webkernel\Platform\Colors\Color::Blue]))
            ->dark_mode(Config::get('ui.dark_mode', true));
    }
}
```

### Config files — Decision

| File | Who writes it | Purpose |
|---|---|---|
| `config/platform.php` | dump-autoload | Identity, `autoload` path. Platform-managed. Not `Config::set`. |
| `config/app.php` | You | App defaults (including branding defaults). Read at boot. |
| `platform/platform-runtime.php` | `Config::set` | Runtime writes. Atomic tmp → rename (`ConfigWriter`, copy from old work). |

`Config::boot()` merges in that order. Runtime wins. After boot, `get('a.b.c')` is an in-memory walk on dots. No file I/O on `get`.

`Config::set('a.b', $value)`:

1. Write `platform/platform-runtime.php` atomically
2. Then update memory
3. Return `Config` so `->get('a.b')` is a read-back
4. If the write fails, memory is unchanged

### What is **not** config

A package provider does **not** have:

```php
public const CONFIG = [
    'billing.vat_rate' => 0.20,
];
```

That was copied from old `BlogProvider`. `NOTES.md` does not have it.

- `vat_rate` is **module data / module config**, not a Composer dump of the package declaration.
- If a module needs default keys, it is a PHP file (for example `modules/acme/billing/config.php`) that `Config::boot` can merge **when we specify that**. We have not specified it. Do not put business numbers in `const CONFIG`.

Dump-autoload needs **paths and class lists** from the provider (see §6.1). It does not need vat rates.

---

## 6. Package types and installer — Spec (`NOTES.md` §6)

Already implemented in `x-webkernel/lifecycle`. Keep.

```php
<?php
declare(strict_types=1);

namespace Webkernel\Lifecycle\Installer;

enum LCPackageType: string
{
    case Assets                 = 'webkernel-assets';
    case Component              = 'webkernel-component';
    case DevTool                = 'webkernel-devtool';
    case Stdlib                 = 'webkernel-stdlib';
    case Element                = 'webkernel-element';
    case Agent                  = 'webkernel-agent';
    case Ffi                    = 'webkernel-ffi';
    case BusinessModule         = 'webkernel-business-module';
    case BusinessModuleFeature  = 'webkernel-business-module-feature';
    case PlatformModule         = 'webkernel-platform-module';
    case PlatformModuleFeature  = 'webkernel-platform-module-feature';

    public function requires_parent_module(): bool
    {
        return match ($this) {
            self::BusinessModuleFeature,
            self::PlatformModuleFeature => true,
            default                     => false,
        };
    }
}
```

| Type | Destination |
|---|---|
| `webkernel-business-module` | `modules/{vendor}/{name}` |
| `webkernel-business-module-feature` | `modules/{parentVendor}/{parentName}/features/{vendor}-{name}` |
| `webkernel-ffi` | `ffi/{vendor}/{name}` |
| All others | `{vendor_dir}/{vendor}/{name}` |

A module-scoped panel is inside `modules/` because of the package type, not because of a comment.

### 6.1 Package declaration — Spec (`NOTES.md` §6) + Decision (one `provider`)

`NOTES.md`:

```json
{
  "name": "acme/billing",
  "type": "webkernel-business-module",
  "extra": {
    "webkernel": {
      "declaration_class": "Acme\\Billing\\BillingModuleDeclaration"
    }
  }
}
```

**Decision:** the key is `provider`. Same class. One FQCN.

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

`prefix` is the view/route namespace (`@include('billing::invoices.index')`).

At dump-autoload the plugin reads every installed package's `extra.webkernel.provider`, reads the constants below, writes manifests. At request, boot `require`s the manifests. No manual service-provider arrays. No glob.

### 6.2 What the package provider may declare — and why

Dump-autoload has to write **lists of files and classes** so the request does not walk the disk.

Those lists are constants on `Webkernel\PlatformProvider`. Each constant is one dump file.

| Constant | Type | Why it exists | Dump file |
|---|---|---|---|
| `ROUTES` | `list<string>` of PHP files | `NOTES.md` foreword + §7: routes live in the package. The router needs the file paths. | `webkernel_routes.php` |
| `VIEWS` | `list<string>` of directories | `NOTES.md` foreword + §7: views live in the package. The view engine needs the dirs. | `webkernel_views.php` |
| `COMPONENTS` | `list<string>` of directories | `NOTES.md` §7: `<webkernel::page />` component dirs | `webkernel_components.php` |
| `COMMANDS` | `list<class-string>` | `NOTES.md` §1: console commands | `webkernel_commands.php` |
| `PANELS` | `list<class-string>` of `PanelProvider` | `NOTES.md` §2 / §9: which admin UIs this package owns | `webkernel_panels.php` |

```php
<?php
declare(strict_types=1);

namespace Acme\Billing;

use Webkernel\PlatformProvider;
use Acme\Billing\Presentation\BillingPanelProvider;
use Acme\Billing\Console\GenerateSitemapCommand;

final class BillingProvider extends PlatformProvider
{
    public const ROUTES     = [__DIR__.'/routes.php'];
    public const VIEWS      = [__DIR__.'/resources/views'];
    public const COMPONENTS = [__DIR__.'/resources/views/components'];
    public const COMMANDS   = [GenerateSitemapCommand::class];
    public const PANELS     = [BillingPanelProvider::class];
}
```

That is the whole class. `register()` / `boot()` do not exist.

If a path cannot be a constant, a **static** method of the same name is allowed (`public static function views(): array`). Dump-autoload calls the static method. Still no instance on the request.

**Not on this class:** `CONFIG`, `ACL`. See §3.2 and §5.

The codebase package also has **exactly one** provider (`Webkernel\CodebaseProvider`) for kernel layouts, kernel commands (`dump-autoload`), System Admin Panel. Not `ViewProvider` + `CoreProvider` + …

---

## 7. View engine — Spec (`NOTES.md` §7)

Views use BladeOne **owned in this package**, not a Composer library. Extension: `.view.php`. Compiled output: `platform/storage/framework/views/`.

Namespace syntax: `@include('webkernel::layouts.page')` or `<webkernel::page />`.

`Webkernel\View\View` is the single entry point.

`NOTES.md` registered it in Registry as `view`. **Decision:** `View::make` (class alias). Same class.

```php
View::make('dashboard.index', ['user' => $user])->render();
View::share('app_name', 'Webkernel');
View::stringable(fn (Money $m): string => $m->format());
View::directive('money', fn ($e) => "<?php echo money($e); ?>");
```

View paths come from dumped `webkernel_views.php` (from `const VIEWS`). No filesystem scanning at runtime.

---

## 8. Project layout — Spec (`NOTES.md` §8) + Decision (config files)

```
refactor/
├── public/                          # Web root — index.php front controller
├── webkernel                        # Host CLI binary
├── composer.json
├── NOTES.md
├── NOTES_TODO.md
├── config/
│   ├── platform.php                 # identity + autoload (dump stamps)
│   └── app.php                      # app defaults, including branding defaults
├── modules/                         # Business modules (composer packages)
│   └── {vendor}/{name}/
│       ├── composer.json            # type: webkernel-business-module
│       │                            # extra.webkernel.provider = one class
│       ├── src/
│       └── features/
│           └── {vendor}-{name}/     # type: webkernel-business-module-feature
└── platform/
    ├── fast-boot.php                # autoload only
    ├── platform-runtime.php         # Config::set target
    ├── dependencies/                # packagist, node_modules
    ├── storage/
    │   └── framework/
    │       └── views/               # Compiled view cache
    └── telemetry/                   # Logs, metrics, traces, profiles, buffers
```

---

## 9. Server-driven UI — Spec (`NOTES.md` §9)

The goal is a Retool-equivalent in PHP: the interface is described as data and rendered by the engine, not hardcoded per panel.

### Step 1 — UI schema (not an AST)

An **AST** (Abstract Syntax Tree) is what a **parser** emits from source text. Webkernel already uses that word for languages (`Lexer → Parser → AST → Validator`). There is no UI language here.

The PHP classes **are** the schema: `Panel`, `Resource`, `Page`, `Table`, `Schema`. Immutable, walkable, optionally serialisable to JSON later if a visual builder needs a dump. Serialisation is not required to render.

```
Panel
  Page                            (standalone — no Resource)
  Cluster                         (optional navigation group)
    Page                          (standalone, grouped)
    Resource                      (CRUD class for one model)
      Page                        (List, Create, Edit, View, custom)
        Component                 (Table | Form | Widget | Custom)
```

A Resource schema carries the model, the table, the form, and the page map. A list page reads the table from the Resource. A create/edit page reads the form from the Resource. Pages decide which of those to show and which actions to expose.

### Step 2 — Platform settings

Spec §5. Built-in settings on every panel. App Owners edit them in the System Admin Panel. Not a Resource.

### Step 3 — Render engine (`.view.php`)

Spec §7. Each component type maps to a `.view.php` template. No Twig. BladeOne compiled once per template per change.

### Step 4 — Discovery and auto-registration

`NOTES.md`: each module's declaration class is invoked at boot; the dump-autoload manifest lists them; boot reads the manifest; no filesystem traversal at runtime.

**Decision:** "invoked at boot" does **not** mean `new BillingProvider` on every request. Dump-autoload already invoked it (constants / static methods). Boot `require`s `webkernel_panels.php`, `webkernel_routes.php`, `webkernel_views.php`.

---

## 10. Namespace summary — Spec (`NOTES.md` §10) + Decision

| Namespace | Responsibility |
|---|---|
| `Webkernel\Config\Config` | `get` / `set` / `boot` (Decision) |
| `Webkernel\Registry` | Spec §4. Not first cut. Not a Container. |
| `Webkernel\View\` | View engine, compiler, Blade directives |
| `Webkernel\Cache\` | Compilation store, manifest cache |
| `Webkernel\Lifecycle\` | Composer installer, plugin, package types |
| `Webkernel\Console` | CLI door + commands |
| `Webkernel\Http` | HTTP door (Decision) |
| `Webkernel\PlatformProvider` | Package declaration, dump-time (Decision) |
| `Webkernel\Platform\Panel` | Panel definition and fluent builder |
| `Webkernel\Platform\PanelProvider` | One admin UI; applies platform config (§5) |
| `Webkernel\Platform\Colors\` | Color definitions and palette |
| `Webkernel\Platform\Pages\` | Built-in panel pages (`Dashboard`). Not resource pages. |
| `Webkernel\Platform\Widgets\` | Built-in widgets |
| `Webkernel\Platform\Resources\` | Base Resource: model, table, form, owned pages |
| `Webkernel\Platform\Tables\` | Table schema and column definitions |
| `Webkernel\Platform\Schemas\` | Form and filter schema |
| `Webkernel\Platform\RenderHooks\` | Named render hook registry |
| `Webkernel\Platform\Http\Middleware\` | All HTTP middleware (spec; first cut is one auth class) |
| `Webkernel\Platform\System\` | System Admin Panel provider and internals |

JSON Canonicalisation and Event Dispatcher stay on the `Webkernel\` list from §1. Not first cut.

---

# Part III — Three moments (so the boot is visible)

| Moment | When | What you do | What the machine does |
|---|---|---|---|
| **Author** | You type PHP | Write one `PlatformProvider` per package (`const ROUTES`, `const VIEWS`, …) and Panel/Resource/Page classes | Nothing |
| **Composer** | `composer dump-autoload` | Nothing | Read each provider statically. Write `platform/dependencies/packagist/composer/webkernel_*.php` |
| **Request** | Browser or `./webkernel` | Nothing | `require` autoload, `require` those arrays, `Config::boot()`, dispatch. No `new Provider`. No Container. No glob. No global functions. |

Composer time may use Reflection. Request time may not.

If the provider is constants (and static methods) only, dump-autoload never instantiates it.

---

# Part IV — What we copy from old work

Copy and adapt (strip Container, strip `webapp()`):

- `View/View.php`, `Compiler.php`, `Engine.php`, `Js.php`, kernel `views/layouts/*`
- `Route/*`
- `Config/ConfigWriter.php`
- `Lifecycle/*` (already in refactor)
- `namespacer.php` (aliases, no functions)
- Console attribute + argv parsing

Do not copy:

- `Container/`
- `WebApp.php`
- `Index.php`
- `Composables/*` as the public API
- `Provider/ProviderRegistry.php` (the glob)
- `ViewProvider` + `CoreProvider` as two providers
- Host `resources/views` hardcoded in boot
- `BlogProvider::CONFIG` and `BlogProvider::ACL`

---

# Part V — Build order

Each step leaves the previous door working. Spec items not in A–G stay spec; they are not deleted.

### A — Doors

- [ ] `public/index.php` → `fast-boot.php` → `Webkernel\Http::run()`
- [ ] `./webkernel` → `fast-boot.php` → `Webkernel\Console::run($argv)`

### B — Config

- [ ] `namespacer.php`: aliases only
- [ ] `Config::boot` / `get` / `set` / `flush`
- [ ] Copy `ConfigWriter`
- [ ] `Config::set('x.y', 1)->get('x.y') === 1` writes `platform/platform-runtime.php`

### C — One provider, dump-autoload

- [ ] `Webkernel\PlatformProvider` with `ROUTES`, `VIEWS`, `COMPONENTS`, `COMMANDS`, `PANELS` only
- [ ] `Webkernel\CodebaseProvider` (kernel layouts)
- [ ] `extra.webkernel.provider` on codebase `composer.json`
- [ ] Lifecycle writes `webkernel_views.php`, `webkernel_providers.php`, `webkernel.php`

### D — View engine (copy) — `NOTES.md` §7

- [ ] Copy View / Compiler / Engine / layouts
- [ ] `Http::run()` renders `webkernel::layouts.simple`

### E — Router (copy)

- [ ] Copy Route
- [ ] Match `/` and render

### F — Panel page — `NOTES.md` §2–§3

- [ ] `Panel`, `PanelProvider`, `Page`
- [ ] `SystemPanelProvider` (`scope('platform')`) + standalone `Dashboard`
- [ ] Branding from `Config` (§5), even if the keys are still defaults

### G — Resource — `NOTES.md` §3

- [ ] `Resource`, `Table`, `Schema`
- [ ] One business module, one `PlatformProvider`, one panel, one Resource, List/Create/Edit
- [ ] Persistence: smallest thing that works. Not an ORM.

### H — Spec, after the app runs

- [ ] Middleware list from §1 / §2
- [ ] Permission names, module-namespaced (`NOTES.md` §3). Not a `const ACL` role map
- [ ] Features inject into existing panels (`NOTES.md` §3)
- [ ] Render hooks, widgets
- [ ] Registry if a fourth service needs it (`NOTES.md` §4)
- [ ] JSON dump of the UI schema (`NOTES.md` §9)
- [ ] JSON Canonicalisation, Event Dispatcher (`NOTES.md` §1)

---

# Part VI — Naming

| Kind | Rule | Example |
|---|---|---|
| Methods, parameters, config keys | `snake_case` | `Config::get()`, `pages()`, `brand_logo()` |
| Classes, namespaces | `PascalCase` | `PlatformProvider`, `InvoiceResource` |
| Constants | `UPPER_SNAKE` | `ROUTES`, `VIEWS` |
| Composer extra keys | `snake_case` | `extra.webkernel.provider` |

PSR / Composer plugin methods we do not own stay as the interface wrote them (`activate`, `getSubscribedEvents`).
