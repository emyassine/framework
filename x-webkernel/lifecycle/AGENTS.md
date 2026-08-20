# Agent rules — webkernel/lifecycle

Composer plugin. Sibling of `webkernel/codebase`.

Follow https://getcomposer.org/doc/articles/custom-installers.md :

- Plugin implements `PluginInterface`, registers the installer in `activate()`.
- Installer extends `Composer\Installer\LibraryInstaller` (`supports()`, `getInstallPath()`). Path must not end with a slash.
- Require `composer-plugin-api` and `composer/installers`. `composer/composer` is require-dev.
- Packages with `webkernel-*` types must require this plugin.

snake_case on Webkernel methods (`on_post_autoload_dump`, `parent_module`). Keep Composer interface names (`activate`, `getSubscribedEvents`, `getInstallPath`, `supports`).

No Laravel, no Vite, no module scanner. Generate `{vendor}/composer/webkernel.php` + classmap only.
