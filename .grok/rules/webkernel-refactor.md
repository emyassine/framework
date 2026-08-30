# Webkernel refactor — standing decisions

Durable architecture. Follow on every change in this repository. Clarity first: one job per package and class, Laminas/Laravel shape, no spaghetti.

## Package map

Each Composer package has one job. Do not mix runtime, UI, system admin, and developer tools.

| Path | Package | Job |
|---|---|---|
| `refactor/x-webkernel/codebase` | `webkernel/codebase` | Runtime: config, route, view, console, HTTP door |
| `refactor/x-webkernel/database` | `webkernel/database` | Connections, query, schema, migrations. Drivers: sqlite, mysql, pgsql. ClickHouse later. |
| `refactor/x-webkernel/models` | `webkernel/models` | Active Record on `webkernel/database` |
| `refactor/x-webkernel/auth` | `webkernel/auth` | Session guard, User, login. Not the System panel. |
| `refactor/x-webkernel/lifecycle` | `webkernel/lifecycle` | Composer plugin and install paths |
| `refactor/x-webkernel/devtools` | `webkernel/devtools` | Developer tools (IDE helper, dump hooks). Not production runtime. |
| `refactor/x-webkernel/system` | `webkernel/system` | System admin panel and its resources |
| `refactor/x-webkernel/platform` | `webkernel/platform` | Metapackage only |
| `refactor/x-webkernel/platform/components` | `webkernel/components` | UI atoms (views + dumpable PHP declarations) |
| `refactor/x-webkernel/platform/i18n` | `webkernel/i18n` | Locale, catalog, translations |
| `refactor/x-webkernel/platform/imagery` | `webkernel/imagery` | Icons, brands, pixmaps |
| `refactor/x-webkernel/platform/panels` | `webkernel/panels` | Panel, Resource, Page, Table, Wds. Not the System panel. |
| `refactor/x-webkernel/platform/schemas` | `webkernel/schemas` | Schema tree for forms and readonly views |
| `refactor/modules/*/*` | business modules | Domain modules. Module-scoped panels live here. |

Moves still pending (do them when implementing, not later as afterthoughts):

- System panel and its resources leave `webkernel/panels` and live in `refactor/x-webkernel/system` (`webkernel/system`).
- `refactor/x-webkernel/codebase/src/DevEnv` leaves codebase and lives in `refactor/x-webkernel/devtools` (`Webkernel\DevTools\…`).

## Page layout

Four regions. Do not nest the right aside inside the left group. These are **not** `page.*` tags.

1. Icon rail — `x-webkernel::rail` / `x-webkernel::rail.button` — narrow left bar, module/panel icons only. Panel: `panel_sidebar()`.
2. Submenu drawer — `x-webkernel::sidebar` / `sidebar.header` / `sidebar.item` — collapsible menus of the active panel. Panel: `sidebar()`.
3. Main content — `x-webkernel::main`. Top bar inside it: `x-webkernel::topbar` (breadcrumbs via `x-webkernel::breadcrumbs`). Panel: `topbar()`.
4. Right aside — `x-webkernel::aside` — inspection / widgets, after `main`.

Left rail + drawer sit in `x-webkernel::sidebar.group` (`position="left"`). State is `data-wds-sidebar` on `<html>`.

Valid HTML only. No orphan tags, no duplicate wrappers.

## Views carry CSS and JS

- CSS and JS live with the View (same folder / same file). Do not dump them into a distant global sheet or `wds/script`.
- Prefer inline CSS when the rule is local, using `:root` tokens already provided (`--color-red-50: oklch(…)`, and the semantic palettes `primary`, `gray`, `warning`, `danger`, `info`).
- Do not invent a second color system.
- Webkernel has Views. Never say Blade.

## View folders (`webkernel/components`)

Three roots, registered separately so tags stay short (`x-webkernel::page`, not `x-webkernel::layout.page`):

1. `resources/views/layout` — page, page.base, page.simple, main, aside, typography.
2. `resources/views/navigation` — rail, sidebar, topbar, breadcrumbs, nav-item.
3. `resources/views/components` — UI atoms (button, input, tabs, …).

PHP classes that render a View use `Concerns\HasMethodMake` (`::make()`). Do not keep a hardcoded class list.

## Dark mode text

Application text adapts automatically in dark mode: it inverts for contrast. Do not hardcode light-only text colors on body copy.

## `post_autoload_dump` is an interface

`extra.webkernel.post_autoload_dump` is a FQCN (or list of FQCNs). Every listed class implements one interface with a single entry method (`run` / `to_run`). The dump command discovers those classes from packages and invokes them.

Do not hardcode dump side-effects (IDE helper, extra writers) inside `DumpAutoloadCommand`. Inject them through that interface.

## Console commands

`#[ConsoleCommand]` supports:

- `name`
- aliases
- `hidden` (optional)

Hidden commands exist and run, but they do not appear in default help.

## Tabs have a View and a class

`<x-webkernel::tabs>`, `tabs.item`, `tabs.panel` have Views AND PHP classes (`Tabs`, `TabsItem`, `TabsPanel`). Same dual-use pattern as Button. `::make()` comes from `HasMethodMake`.

## No spaghetti

One package, one job. One class, one job. Public API as clear as Laminas and Laravel. No god objects, no cross-package shortcuts, no second tag syntax (`<x-webkernel::…>` only).


## Page

Only three page Views, in `webkernel/components` `layout/`: `page` (panel page), `page.base` (document), `page.simple` (centered). No `layouts/` in codebase. Title and breadcrumbs come from Page::get_header() / get_breadcrumbs(). Do not call this "chrome".
