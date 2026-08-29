# Webkernel refactor — standing decisions

Durable architecture. Follow on every change in this repository. Clarity first: one job per package and class, Laminas/Laravel shape, no spaghetti.

## Package map

Each Composer package has one job. Do not mix runtime, UI chrome, system admin, and developer tools.

| Path | Package | Job |
|---|---|---|
| `refactor/x-webkernel/codebase` | `webkernel/codebase` | Runtime: config, route, view, console, HTTP door |
| `refactor/x-webkernel/lifecycle` | `webkernel/lifecycle` | Composer plugin and install paths |
| `refactor/x-webkernel/devtools` | `webkernel/devtools` | Developer tools (IDE helper, dump hooks). Not production runtime. |
| `refactor/x-webkernel/system` | `webkernel/system` | System admin panel and its resources |
| `refactor/x-webkernel/platform` | `webkernel/platform` | Metapackage only |
| `refactor/x-webkernel/platform/components` | `webkernel/components` | UI atoms (views + dumpable PHP declarations) |
| `refactor/x-webkernel/platform/i18n` | `webkernel/i18n` | Locale, catalog, translations |
| `refactor/x-webkernel/platform/imagery` | `webkernel/imagery` | Icons, brands, pixmaps |
| `refactor/x-webkernel/platform/panels` | `webkernel/panels` | Panel chrome: Panel, Resource, Page, Table, Wds. Not the System panel. |
| `refactor/x-webkernel/platform/schemas` | `webkernel/schemas` | Schema tree for forms and readonly views |
| `refactor/modules/*/*` | business modules | Domain modules. Module-scoped panels live here. |

Moves still pending (do them when implementing, not later as afterthoughts):

- System panel and its resources leave `webkernel/panels` and live in `refactor/x-webkernel/system` (`webkernel/system`).
- `refactor/x-webkernel/codebase/src/DevEnv` leaves codebase and lives in `refactor/x-webkernel/devtools` (`Webkernel\DevTools\…`).

## Page layout

Four regions. Do not nest the right aside inside the left group.

1. Icon rail — `x-webkernel::page.panels.rail` — narrow left bar, module/panel icons only.
2. Submenu drawer — `x-webkernel::page.menu.drawer` — collapsible, hover or click, menus of the active panel.
3. Main content — `x-webkernel::page.main`.
4. Right aside — `x-webkernel::page.aside` — inspection / widgets, after `page.main`.

Left rail + drawer sit in `x-webkernel::page.sidebar.group` (`position="left"`). State is Alpine (`active_panel`, `drawer_open`): `@mouseenter` opens the matching drawer, `@click` toggles it.

Valid HTML only. No orphan tags, no duplicate wrappers.

## Components carry their CSS

- CSS lives with the component (same folder / same view). Do not dump component styles into a distant global sheet as the source of truth.
- Prefer inline CSS when the rule is local, using `:root` tokens already provided (`--color-red-50: oklch(…)`, and the semantic palettes `primary`, `gray`, `warning`, `danger`, `info`).
- Do not invent a second color system.

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

## Tabs have a view and a class

`<x-webkernel::tabs>`, `tabs.item`, `tabs.panel` have views AND PHP classes (`Tabs`, `TabsItem`, `TabsPanel`). Same dual-use pattern as Button.

## No spaghetti

One package, one job. One class, one job. Public API as clear as Laminas and Laravel. No god objects, no cross-package shortcuts, no second tag syntax (`<x-webkernel::…>` only).


## Page chrome

Only three page components, in `webkernel/components`: `page` (panel chrome), `page.base` (document), `page.simple` (centered). No `layouts/` in codebase. Title and breadcrumbs come from Page::get_header() / get_breadcrumbs().
