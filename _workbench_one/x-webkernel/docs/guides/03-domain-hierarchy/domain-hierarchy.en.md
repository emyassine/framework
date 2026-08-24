# Domain hierarchy

The HTTP kernel routes a URI. The domain tree names *what* that URI belongs to. Route bindings already store `_panel`, `_cluster`, `_resource`, `_page`, and `_permission`. The UI builder materializes that tree.

## Ownership

```
App Owner(s)
 └── Platform
      ├── System Admin Panel     ← special panel, platform-wide management
      └── Modules (1..N)         ← contained domains
           └── Admin Panels (1..N)
                └── Clusters
                     └── Resources
                          └── Pages
                               └── Components
```

The System Admin Panel (SAP) is **not** a module and not a peer of Modules under a shared “workspace” node. The Platform contains Modules. The SAP administers the Platform (owners, installed modules, instance, telemetry). A dashed administers edge is the correct relation, not `SAP --> Modules` as a parent-child in the domain model.

Modules are Composer packages (custom types via `webkernel/lifecycle`). If a module needs extra Composer dependencies, that is the module's graph.

## Levels

| Level | Role |
| --- | --- |
| **App Owner** | Human (or group) that owns the instance. At least one. |
| **Platform** | Root. Global config, instance id, installed modules. |
| **System Admin Panel** | Special panel. Platform-wide administration only. |
| **Module** | Functional domain inside the platform. One or more admin panels. |
| **Admin Panel** | Operational workspace (e.g. Accounting, HR). Contains clusters. |
| **Cluster** | Grouping of related resources inside a panel. |
| **Resource** | Business entity (Invoice, Employee). |
| **Page** | One functional view of a resource (List, Create, Edit, custom). |
| **Component** | Table, form, widget, or custom view inside a page. |

Authorization is a cross-cutting layer. Permissions are module-scoped (`webapp()->acl()` infers the module from the active panel; `webapp()->acl('invoicing')` is explicit). View directives (`@can`, `@cannot`, `@can_any`) expand to fully qualified `webapp()->acl()` calls at compile time.

## Routing attachment

```php
Route::get('/invoices/{id}', InvoicePage::class)
    ->name('invoices.show')
    ->where_number('id')
    ->panel('accounting')
    ->cluster('sales')
    ->resource('invoice')
    ->page('show')
    ->permission('invoice.view');
```

Those extras survive compilation. They are how a URI is later resolved back to the domain tree without scanning `platform/modules/` on the request path.

## What is not this tree

- HTTP Request / Router / Pipeline / Response / Telemetry — [HTTP kernel](../02-http-kernel/http-kernel.en.md)
- Composer, PSR, install — [Getting started](../00-getting-started/getting-started.en.md)
- On-disk telemetry sinks — [Telemetry](../05-telemetry/telemetry.en.md)
