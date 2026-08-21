# Agent rules — Webkernel host (refactor)

## Temp files

Never `sys_get_temp_dir()`. Shared hosting often blocks it (`open_basedir`). Transient files go under `platform/temporary/` (webapp-writable). Delete after use. Do not persist `composer.phar` in the tree.

## Fast-boot

Hot path: require `platform/storage/instance/data/autoload.php` (relative vendor autoload stamp) then return. Do not `json_decode` `composer.json` on the request path. Miss: read `config.vendor-dir`, optional CLI `composer install`, restamp. Composer phar, if downloaded, is a file under `platform/temporary/` and is unlinked in `finally`.
