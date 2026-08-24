# Agent rules — Webkernel host (refactor)

## Temp files

Never `sys_get_temp_dir()`. Shared hosting often blocks it (`open_basedir`). Transient files go under `platform/temporary/` (webapp-writable). Delete after use. Do not persist `composer.phar` in the tree.

## Fast-boot

Hot path: `require config/platform.php` (OPcache array), read the `autoload` key, `require` that file, return. Do not `json_decode` `composer.json` on the request path. Miss: discover vendor-dir, optional CLI `composer install`, stamp `autoload` through `ConfigWriter::atomic_rewrite()`. Composer phar, if downloaded, is a file under `platform/temporary/` and is unlinked in `finally`.
