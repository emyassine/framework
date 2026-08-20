# webkernel/paths

- `webapp_path(?string $path = null)` — host root
- `vendor_dir(?string $path = null)` — Composer vendor-dir (`vendor`, `third_party`, …)

Vendor-dir comes from `Composer\InstalledVersions` (`dirname($file, 2)`). Lifecycle writes `{vendor}/composer/webkernel.php`; `webapp_path()` reads it.

If the host moves, run `composer dump-autoload`.

License: EPL-2.0.
