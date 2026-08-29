# Rules — webkernel/lifecycle

Composer plugin. Sibling of `webkernel/codebase`.

Follow https://getcomposer.org/doc/articles/custom-installers.md :

- Plugin implements `PluginInterface`, registers the installer in `activate()`.
- Installer extends `Composer\Installer\LibraryInstaller` (`supports()`, `getInstallPath()`). Path must not end with a slash.
- Require `composer-plugin-api` and `composer/installers`. `composer/composer` is require-dev.
- Packages with `webkernel-*` types must require this plugin.

snake_case on Webkernel methods (`on_script_event`, `parent_module`). Keep Composer interface names (`activate`, `getSubscribedEvents`, `getInstallPath`, `supports`).

No Laravel, no Vite, no request-path module scanner.

Never `sys_get_temp_dir()`. Shared hosting often blocks it (`open_basedir`). Transient files go under `platform/temporary/`. Delete after use. Do not persist `composer.phar` in the tree.

`DumpAutoloadCommand` writes `{vendor}/composer/webkernel.php`, classmap, path/instance function files, `webkernel_views.php`, `webkernel_routes.php`, `webkernel_composables.php`, `webkernel_providers.php`, and `webkernel_commands.php`. It runs because `webkernel/codebase` declares `extra.webkernel.post_autoload_dump`. The plugin does not hardcode that class.

Packages declare themselves in `extra.webkernel`:

- `prefix` — package alias for `webkernel_package_root()`.
- `provider` — one FQCN per package, dumped into `webkernel_providers.php`.
- `package_repo` — used by webkernel/x-monorepo to split the dev tree.
- `post_autoload_dump` (and the other Composer script events, hyphens to underscores) — FQCN or `Class::method`. `webkernel/codebase` runs first.

ComposableContract implementors already in the classmap are mapped `api_name => class` into `webkernel_composables.php`.
Avoiding glob's on the request path.

Path/instance function files stay dumped.
Packages with `provider` do not dump `functions/*.php` (those load with the composable).

Scan Composer install paths (including `modules/` via the installer). Do not walk `modules/` at request time.

> QueryModules coming soon as a composable as well `webapp()->query(list: 'modules')->...`
