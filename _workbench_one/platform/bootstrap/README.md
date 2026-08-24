# Webkernel Platform bootstrap

`fast-boot.php` reads `config/platform.php` and requires the `autoload` path.
That is the hot path: one OPcache-cached config array, one key read, one autoload require.

`app.php` configures `WebApp` (middleware, exceptions, routes) and returns the host.

See the host [README](../../README.md) and [fluent API](../../README_FLUENT.md).
