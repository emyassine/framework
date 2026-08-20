# webkernel/devenv

IDE stubs. `IdeHelper` reads Composer `autoload_classmap.php` (no vendor tree walk, no hardcoded class names) and writes `_ide_helper.php` (`if (false) { class … {} }`).

Lifecycle runs it on `composer dump-autoload`. Deterministic: sorted names + xxh3 hash, skip rewrite when unchanged.
