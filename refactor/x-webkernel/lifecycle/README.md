# webkernel/lifecycle

Composer plugin (type `composer-plugin`). Sibling of `webkernel/codebase`.

Follows [Custom Installers](https://getcomposer.org/doc/articles/custom-installers.md):

- `LCInstaller` implements `PluginInterface` and registers the installer in `activate()`
- `LCBaseInstaller` extends `Composer\Installer\LibraryInstaller` (`supports()`, `getInstallPath()`)
- `composer-plugin-api` + `composer/installers` required; `composer/composer` is require-dev

Webkernel packages that use a custom type (`webkernel-business-module`, …) must require this plugin so it is present at install time.

Packages hook Composer script events through `extra.webkernel.{event}` (hyphens become underscores):

```json
"extra": {
  "webkernel": {
    "post_autoload_dump": "Vendor\\Package\\OnDump"
  }
}
```

`OnDump` is `new Class()->__invoke()` or `Class::method`. Zero-arg callables are allowed. `webkernel/codebase` declares `DumpAutoloadCommand` and runs first.

On `composer dump-autoload` (and `php webkernel dump-autoload`) `DumpAutoloadCommand` writes:

- `{vendor}/composer/webkernel.php` — `webapp_root`, `vendor_dir`, `instance_id`
- `{vendor}/composer/webkernel_classmap.php`
- `{vendor}/composer/webkernel_files.php` — path/instance helpers
- `{vendor}/composer/webkernel_composables.php` — `api_name => class`
- `{vendor}/composer/webkernel_providers.php`
- `{vendor}/composer/webkernel_commands.php` — `#[ConsoleCommand]` classes
- `{vendor}/composer/webkernel_views.php` / `webkernel_routes.php` — fallback lists
- `storage/instance/data/instance_id`

If the app is moved to another host, run `composer dump-autoload`.
