# Performance

The kernel constraint is **minimum overhead**. Composer packages and PSR interfaces are allowed. Framework wrappers that tax every request are not.

Two numbers, not one.

| Target | What it measures | Budget |
| --- | --- | --- |
| Kernel overhead | CPU time in Webkernel with no I/O (capture, boot of already-dumped maps, dispatch, emit) | **under 1 ms** (aim 0.1–1 ms under OPcache) |
| Application response | Full TTFB including application work and I/O (database, disk, network) | **under 10 ms** on the hot path |

Sub-10 ms as a *kernel* claim is too weak for a native OPcache kernel. Sub-10 ms is the application budget once I/O is in the picture.

## Measured local baselines

From `platform/bootstrap/app.php`, localhost, PHP 8.4, OPcache enabled, JIT disabled:

| Path | Time |
| --- | --- |
| Hello World (no WebApp configure body) | ~0.021 ms |
| Dashboard render via `request_lifetime()` | ~0.33 ms |

These are kernel-side numbers, not database round-trips. Production (Nginx / Apache / FPM) differs from `php -S`.

## How the budget is kept

- No directory walking on the request path. Composer already computed install paths; dump-autoload wrote them.
- Static process caches. Generated PHP that OPcache can intern.
- One router strategy (MarkBased). Path matching uses strings; PSR HTTP lives at the kernel boundary, not inside the dispatcher.
- Views compile to PHP (`*.view.php.compiled`). JIT, when enabled, compiles hot compiled-view files to machine code.
- Route and View classes are not booted on requests that do not call them.
- PSR interfaces (`ContainerInterface`, `LoggerInterface`, HTTP message) are cheap contracts. Illuminate/Symfony service providers and request-time package discovery are not.

## OPcache and JIT

```bash
php -r "echo opcache_get_status()['opcache_enabled'] ? 'OPcache Active' : 'OPcache Disabled';"
```

JIT is a process-start flag (`php.ini` or `php -d`). It cannot be switched with `ini_set()`.

```bash
php webkernel server --with-jit
```

The child `cli-server` process is started with the JIT engine args. Zend OPcache must be loaded or the flag is a no-op.

`--profile-lifecycle` prints include run/read cost, newly declared classes, and peak memory on stderr. Use it to hunt files that miss OPcache (read cost) or work that does not belong on the request path (run cost).

## What is not in the kernel budget

Database queries, outbound HTTP (`psr/http-client`), filesystem writes to `platform/telemetry/` and `platform/storage/`, and template compile on first miss. Those belong to the under-10 ms application budget, or they miss it and get an explicit exception in the hot path — they do not get folded into a “kernel is 10 ms” story.
