# Webkernel — Architecture Reference

## Foreword

- part of the code is under /home/yassine/Projects/framework/_workbench_one, here refered to as 'old work'
- This is the new refactor that will maintain :
	- View from old work and its compilation BUT it should not be fucking hardcoded and put as a fucking platform wide place
		- views are inside modules in resources/views (to be declared in the class_declaration mentionned class) so that its really modular
		- No fuking hardcodes
		- Same goes for routes ! we can have a route.php platform wide for tests etc but in real production it will live inside webkernel code packages and inside modules
	- Dont repeat thoses erros from old work
	- When pulling code from old work : dont fucking use symlinks, copy and adapt
	- Remember no fucking camelCase

---

## 1. Namespace Contract

Two root namespaces. No overlap, no exceptions.

### `Webkernel\` — Runtime and Lifecycle

Everything that runs before a request is handled, or orthogonally to UI concerns.

- Registry / Service Locator
- JSON Canonicalisation
- Event Dispatcher
- Lifecycle and Composer Installer
- View Engine (`Webkernel\View\View`)
- Cache and Compilation Store
- Console Commands
- Composables / Contracts

### `Webkernel\Platform\` — UI and Panel System

Everything that touches the panel interface, rendering, and HTTP context.

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

`Webkernel\Support\*` is abolished. Nothing lives there.

---

## 2. Panel Scoping

Every panel is either `platform`-scoped or `module`-scoped. This is not a runtime tag or a convention. It is enforced at registration time as part of the panel's declaration contract.

A **platform-scoped** panel manages cross-module or platform-wide concerns. The System Admin Panel is the canonical example. It administers modules but does not own their domain models.

A **module-scoped** panel is declared inside a business module. Its physical location is determined by the module's Composer type (`webkernel-business-module`), which places it under `modules/`. A module-scoped panel cannot promote itself to platform scope.

```php
<?php
declare(strict_types=1);

namespace Webkernel\Platform\System;

use Webkernel\Platform\Panel;
use Webkernel\Platform\PanelProvider;
use Webkernel\Platform\Colors\Color;
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
 * SystemPanelProvider configures the global administration panel.
 * Scope: platform — manages modules and platform-wide configuration.
 */
final class SystemPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('system')
            ->path('system')
            ->default()
            ->colors(['primary' => Color::Blue])
            ->pages([Dashboard::class]) // panel pages only — not CRUD
            ->widgets([AccountWidget::class, InfoWidget::class])
            ->discoverResources(
                in: __DIR__ . '/../Presentation/Resources',
                for: 'Webkernel\Platform\System\Presentation\Resources',
            ) // each Resource brings its own pages()
            ->discoverPages(
                in: __DIR__ . '/../Presentation/Pages',
                for: 'Webkernel\Platform\System\Presentation\Pages',
            ) // extra panel pages, not resource pages
            ->discoverWidgets(
                in: __DIR__ . '/../Presentation/Widgets',
                for: 'Webkernel\Platform\System\Presentation\Widgets',
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableIconComponents::class,
                DispatchServingEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                AuthenticateSession::class,
            ]);
    }
}
```

Branding (logo, favicon, dark mode, brand logo height) is not hardcoded here. It is resolved dynamically at boot from platform settings the App Owner controls. Those settings are injectable, panel-scoped or global, and editable via permissions. They are not a Resource. See section 5.

---

## 3. Domain Hierarchy

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

Rules:

- The System Admin Panel administers modules. It does not own module domain models.
- Features extend a module. They register additional resources and pages into existing module panels. They do not create new panels.
- Permissions are always namespaced by module. There is no global flat permission table.
- Permission resolution is part of the sub-millisecond boot budget.

### Resource — the CRUD unit

A **Resource** is a static class that builds the CRUD interface for one model. It describes how administrators interact with that model's data through tables and forms. It is not a page, not a route file, and not a bag of settings.

The Resource **owns its pages**. List, create, edit, view, and any custom screen for that model are declared on the Resource and live next to it. A panel does not register those pages one by one. It registers (or discovers) the Resource; the Resource registers the pages.

```php
<?php
declare(strict_types=1);

namespace Acme\Billing\Presentation\Resources\Invoices;

use Webkernel\Platform\Resources\Resource;
use Webkernel\Platform\Schemas\Schema;
use Webkernel\Platform\Tables\Table;
use Acme\Billing\Domain\Invoice;
use Acme\Billing\Presentation\Resources\Invoices\Pages;
use Acme\Billing\Presentation\Resources\Invoices\Schemas\InvoiceForm;
use Acme\Billing\Presentation\Resources\Invoices\Tables\InvoicesTable;

/**
 * CRUD interface for the Invoice model.
 * Pages are owned here. The panel never lists them individually.
 */
final class InvoiceResource extends Resource
{
    protected static string $model = Invoice::class;

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
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

Two kinds of page exist. Mixing them is how the old notes were unclear.

| Kind | Owned by | Required Resource? | Examples | Registered how |
|---|---|---|---|---|
| **Panel page** | The Panel (or a Cluster) | No | `Dashboard`, reports, settings, wizards | `Panel::pages()` / `discover_pages()` |
| **Resource page** | The Resource | Yes | `ListInvoices`, `CreateInvoice`, `EditInvoice`, custom resource screens | `Resource::pages()` |

A panel page is a first-class screen. It has a route, a view, components, and permissions. It does not get a table/form from a Resource because there is no model CRUD to describe.

A Resource is never a panel page. `Dashboard` is never a Resource. Branding, colors, and logos are platform **settings**, not a Resource — do not call that object a "configuration resource".

On disk the Resource is a folder. The class lives inside it. Pages, form schema, table, and relation managers are sibling folders (Filament-shaped, not named Filament):

```
Presentation/Resources/
  Invoices/
    InvoiceResource.php
    Pages/
      ListInvoices.php
      CreateInvoice.php
      EditInvoice.php
    Schemas/
      InvoiceForm.php
    Tables/
      InvoicesTable.php
    RelationManagers/
      PaymentsRelationManager.php
```

`discover_resources()` finds `InvoiceResource`. It does not find `ListInvoices`. Those pages exist only because `InvoiceResource::pages()` said so.

---

## 4. Architecture Pattern — Global Registry without DI

No dependency injection container. No reflection at runtime. No configuration parsing per request.

The pattern is a static Global Registry: register factories or instances by string key, resolve on first access (lazy singleton). This is the same pattern used by SQLite's extension registry and VS Code's service registry. It is testable by swapping keys directly.

```php
<?php
declare(strict_types=1);

namespace Webkernel;

/**
 * Global service registry.
 * Services are registered as callables (lazy) or as objects (eager).
 */
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

    /** Replace a binding — used in tests or for platform-wide overrides. */
    public static function swap(string $id, callable|object $service): void
    {
        self::$services[$id] = $service;
    }
}
```

The class is named `Registry`, not `Kernel`. It does one thing: map string keys to resolved instances.

---

## 5. Dynamic Configuration — No Hardcoding

Branding, colors, dark mode, logos, and panel defaults are not hardcoded in `PanelProvider`. They are resolved from platform settings stored in the config layer, managed by the App Owner via the platform panel.

```php
// PanelProvider base — applies platform config at boot
abstract class PanelProvider
{
    final protected function applyPlatformConfig(Panel $panel): Panel
    {
        $config = Registry::get('platform.config');

        return $panel
            ->favicon($config->get('branding.favicon'))
            ->brandLogo($config->get('branding.logo_light'))
            ->darkModeBrandLogo($config->get('branding.logo_dark'))
            ->brandLogoHeight($config->get('branding.logo_height', '2rem'))
            ->colors($config->get('branding.colors', ['primary' => Color::Blue]))
            ->darkMode($config->get('ui.dark_mode', true));
    }
}
```

Default platform settings are injected into all panels. The App Owner decides, via a platform-wide permission, who can edit them — globally or per module. Module panels can override specific keys or inherit the global config entirely.

---

## 6. Package Types and Installer

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
        return match($this) {
            self::BusinessModuleFeature,
            self::PlatformModuleFeature => true,
            default                     => false,
        };
    }
}
```

Install destinations:

| Type | Destination |
|---|---|
| `webkernel-business-module` | `modules/{vendor}/{name}` |
| `webkernel-business-module-feature` | `modules/{parentVendor}/{parentName}/features/{vendor}-{name}` |
| `webkernel-ffi` | `ffi/{vendor}/{name}` |
| All others | `{vendor_dir}/{vendor}/{name}` |

A module-scoped panel is, by definition, inside `modules/`. This is not a convention. It is determined by the package type declared in `composer.json`.

### Module Declaration Contract

Every `webkernel-business-module` must declare a `declaration_class` in its `composer.json`:

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

At boot, the platform reads the manifest generated during `composer dump-autoload` and registers each module's panels, resources, and permissions automatically. No manual registration. No service provider arrays.

---

## 7. View Engine

Views use `Webkernel\View`. Template extension is `.view.php`. Compiled output lands in `platform/storage/framework/views/`.

Namespace syntax for templates: `@include('webkernel::layouts.page')`. Components are Laravel `x-` only: `<x-webkernel::page />`.

The `Webkernel\View\View` class is the single entry point. It is registered in the Registry as a singleton under the key `view`.

```php
// Render a named view with data
View::make('dashboard.index', ['user' => $user])->render();

// Share a value across all views
View::share('appName', 'Webkernel');

// Register a custom stringable
View::stringable(fn (Money $m): string => $m->format());

// Register a Blade directive
View::directive('money', fn ($e) => "<?php echo money($e); ?>");
```

View paths are resolved from a compiled manifest (`webkernel.global.views`) produced during `dump-autoload`. Modules register their view directories and namespaces through their declaration class. No filesystem scanning at runtime.

---

## 8. Project Layout

```
refactor/
├── public/                          # Web root — index.php front controller
├── webkernel                        # Host CLI binary
├── composer.json
├── modules/                         # Business modules (composer packages)
│   └── {vendor}/{name}/
│       ├── composer.json            # type: webkernel-business-module
│       ├── src/
│       └── features/
│           └── {vendor}-{name}/     # type: webkernel-business-module-feature
└── platform/
    ├── fast-boot.php
    ├── dependencies/                # packagist, node_modules
    ├── storage/
    │   └── framework/
    │       └── views/               # Compiled view cache
    └── telemetry/                   # Logs, metrics, traces, profiles, buffers
```

---

## 9. Server-Driven UI — Build Order

The goal is a Retool-equivalent in PHP where the interface is described as data and rendered by the engine, not hardcoded per panel.

### Step 1 — UI schema (not an AST)

Do not call this an AST. An **AST** (Abstract Syntax Tree) is what a **parser** emits from source text: tokens in, tree out. Webkernel already uses that word correctly for languages (`Lexer → Parser → AST → Validator` in `Languages.md`). There is no UI language here, no tokens, no parser.

The UI is described by the PHP classes themselves: `Panel`, `Resource`, `Page`, `Table`, `Schema`. That object tree **is** the schema. Immutable, walkable, optionally serialisable to JSON later if a visual builder needs a dump. Serialisation is not required to render.

The tree matches the domain hierarchy:
	- a panel owns standalone pages and resources;
	- a resource owns its pages;
	- a page owns components.

```
Panel
  Page                            (standalone — no Resource)
  Cluster                         (optional navigation group)
    Page                          (standalone, grouped)
    Resource                      (CRUD class for one model)
      Page                        (List, Create, Edit, View, custom)
        Component                 (Table | Form | Widget | Custom)
```

A Resource schema carries the model, the table, the form, and the page map. Rendering a list page reads the table from the Resource, not from the page. Rendering a create/edit page reads the form from the Resource. Pages decide which of those schemas to show and which actions to expose.

### Step 2 — Platform settings

The platform injects built-in settings into every panel. Branding, colors, and layout defaults live there. App Owners edit them through the System Admin Panel. Permissions control who can edit globally versus per module. This is not a Resource.

### Step 3 — Render Engine (`.view.php`)

The existing `Webkernel\View\View` engine handles this. Each component type maps to a `.view.php` template. No Twig. Templates compile once per change.

### Step 4 — Discovery and Auto-Registration

Each module's `declaration_class` is invoked at boot. It declares: panels, clusters, resources, pages, components, and permissions. The platform manifest (written during `dump-autoload`) lists every active declaration class. Boot reads the manifest sequentially. No filesystem traversal at runtime.

---

## 10. Namespace Summary

| Namespace | Responsibility |
|---|---|
| `Webkernel\Registry` | Global service registry |
| `Webkernel\View\` | View engine, compiler, Blade directives |
| `Webkernel\Cache\` | Compilation store, manifest cache |
| `Webkernel\Lifecycle\` | Composer installer, plugin, package types |
| `Webkernel\Console\` | CLI commands |
| `Webkernel\Platform\Panel` | Panel definition and fluent builder |
| `Webkernel\Platform\PanelProvider` | Base provider — applies platform config |
| `Webkernel\Platform\Colors\` | Color definitions and palette |
| `Webkernel\Platform\Pages\` | Built-in panel pages (Dashboard). Not resource pages. |
| `Webkernel\Platform\Widgets\` | Built-in widgets |
| `Webkernel\Platform\Resources\` | Base Resource: model, table, form, owned pages |
| `Webkernel\Platform\Tables\` | Table schema and column definitions |
| `Webkernel\Platform\Schemas\` | Form and filter schema |
| `Webkernel\Platform\RenderHooks\` | Named render hook registry |
| `Webkernel\Platform\Http\Middleware\` | All HTTP middleware |
| `Webkernel\Platform\System\` | System Admin Panel provider and internals |
