# webkernel/instance

Host fingerprint (path + machine). Lifecycle writes `vendor/composer/webkernel.php` and `storage/instance/data/instance_id`.

`webkernel_instance_id()` reads the generated boot file. Do not recompute on the request path.
