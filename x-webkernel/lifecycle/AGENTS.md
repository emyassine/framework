# Agent rules — webkernel/lifecycle

Composer plugin. Sibling of `webkernel/codebase`.

Follow https://getcomposer.org/doc/articles/custom-installers.md :

- Plugin implements `PluginInterface`, registers the installer in `activate()`.
- Installer extends `Composer\Installer\LibraryInstaller` (`supports()`, `getInstallPath()`). Path must not end with a slash.
- Require `composer-plugin-api` and `composer/installers`. `composer/composer` is require-dev.
- Packages with `webkernel-*` types must require this plugin.

snake_case on Webkernel methods (`on_post_autoload_dump`, `parent_module`). Keep Composer interface names (`activate`, `getSubscribedEvents`, `getInstallPath`, `supports`).

No Laravel, no Vite, no request-path module scanner. Dump-autoload writes `{vendor}/composer/webkernel.php`, classmap, eager files, `webkernel_views.php`, and `webkernel_routes.php`.

Packages declare themselves in `extra.webkernel`:

- `eager: true` — function files are required at boot (paths, instance). Default false.
- `views` — dir or list. Default: `views/`, `resources/views/` when they exist.
- `routes` — file or list. Default: `routes/web.php`, `routes.php` when they exist.

Scan Composer install paths (including `modules/` via the installer). Do not walk `modules/` at request time.
