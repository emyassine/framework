# webkernel/lifecycle
> Webkernel Composer plugin — custom installer, lifecycle hooks, environment checks,
> and action-based code generation.
---
## Global helpers — `load.lifecycle.functions.php`
Loaded automatically via `autoload.files`. No generation step needed.
| Function | Resolution strategy |
|---|---|
| `base_path($path)` | `InstalledVersions::getRootPackage()['install_path']` — exact project root, set by Composer |
| `vendor_path($path)` | `dirname(__DIR__, 2)` from lifecycle package — vendor-dir agnostic |
| `webkernel_package($package, $sub_path)` | `InstalledVersions::getInstallPath('webkernel/{$package}')` — immune to vendor-dir layout |

## Benchmark results
```bash
PHP Path Functions Benchmark
============================
Iterations : 10,000,000
base_path              : 695.31 ms | 69.53 ns | 0.069531 µs | 14,381,985 calls/s
  result: /home/yassine/Projects/framework/refactor/json.json
vendor_path            : 701.89 ms | 70.19 ns | 0.070189 µs | 14,247,310 calls/s
  result: /home/yassine/Projects/framework/refactor/json.json
webkernel_package      : 735.95 ms | 73.59 ns | 0.073595 µs | 13,587,895 calls/s
  result: /home/yassine/Projects/framework/refactor/x-dev-base/lifecycle/README.md
```
### Why `InstalledVersions` for `base_path()` and `webkernel_package()`?
```json
"config": { "vendor-dir": "933a2789/internal/dependencies/packagist" }
```
`dirname(__DIR__, N)` would need to know `N` — impossible with a non-standard vendor-dir.
`InstalledVersions::getRootPackage()['install_path']` always contains the exact project root, set by Composer itself.
`InstalledVersions::getInstallPath('webkernel/{$package}')` asks Composer directly for each package path; `realpath()` then resolves any traversal segments. Throws a `RuntimeException` attributed to the caller when the package is absent or its path does not exist on disk.

`vendor_path()` uses `dirname(__DIR__, 2)` because this file lives at `{vendor}/webkernel/lifecycle/` — two levels up is always `{vendor}`, regardless of what that directory is named.

All three helpers use `static` cache — computed once per process, zero overhead on repeat calls.

---
## extra.webkernel structure
```json
{
  "extra": {
    "webkernel": {
      "$schema": "./_schemas/schema.v1.json",
      "lifecycle": {
        "checks": [
          {
            "check": "PHP_VERSION_ID < 80500",
            "on-fail": { "type": "danger", "message": "PHP >= 8.5 required." },
            "fix": "sudo apt install php8.5"
          }
        ],
        "events": {
          "post-autoload-dump": "Webkernel\\Lifecycle\\ComposerScripts::post_autoload_dump"
        },
        "actions": [
          "Your\\Package\\Actions\\GenTailwindConfig",
          "Your\\Package\\Actions\\GenEnvStub"
        ]
      }
    }
  }
}
```
---
## Writing an Action
```php
namespace Your\Package\Actions;
use Composer\Script\Event;
use Webkernel\Lifecycle\Actions\Contracts\LifecycleActionContract;
final class GenTailwindConfig implements LifecycleActionContract
{
    public function key(): string  { return 'tailwind-config'; }
    public function name(): string { return 'Generate Tailwind config stub'; }
    public function handle(Event $event): void
    {
        $output = webkernel_package('lifecycle', 'generated/tailwind.config.js');
        file_put_contents($output, "module.exports = { content: [] };\n");
    }
}
```
Rules: idempotent · no external deps · output in `webkernel_package('lifecycle', 'generated/*')`.
---
## Environment checks
| `on-fail.type` | Behaviour |
|---|---|
| `info` | Blue notice, continues |
| `warning` | Yellow warning, continues |
| `danger` | Red error, **throws**, blocks Composer |
---
## Package types
| Type | Install path |
|---|---|
| `webkernel-business-module` | `modules/{vendor}/{name}/` |
| `webkernel-business-module-feature` | `modules/{parentVendor}/{parentName}/features/{vendor}-{name}/` |
| `webkernel-platform-module` | `modules/{vendor}/{name}/` |
| `webkernel-platform-module-feature` | `modules/{parentVendor}/{parentName}/features/{vendor}-{name}/` |
| `webkernel-ffi` | `ffi/{vendor}/{name}/` |
| everything else | standard vendor path |
Feature packages must declare `extra.webkernel.module: "vendor/parent-name"`.
---
## License
Proprietary — (c) 2025–2027 Numerimondes, El Moumen Yassine
