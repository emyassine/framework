# webkernel/lifecycle

Composer plugin (type `composer-plugin`). Sibling of `webkernel/codebase`.

Follows [Custom Installers](https://getcomposer.org/doc/articles/custom-installers.md):

- `LCInstaller` implements `PluginInterface` and registers the installer in `activate()`
- `LCBaseInstaller` extends `Composer\Installer\LibraryInstaller` (`supports()`, `getInstallPath()`)
- `composer-plugin-api` + `composer/installers` required; `composer/composer` is require-dev

Webkernel packages that use a custom type (`webkernel-business-module`, …) must require this plugin so it is present at install time.

On `composer dump-autoload` writes:

- `{vendor}/composer/webkernel.php` — `webapp_root`, `vendor_dir`, `instance_id`
- `{vendor}/composer/webkernel_classmap.php`
- `storage/instance/data/instance_id`

If the app is moved to another host, run `composer dump-autoload`.

License: EPL-2.0.
