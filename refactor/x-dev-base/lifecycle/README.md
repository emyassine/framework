# webkernel/lifecycle

> Composer plugin for the [Webkernel](https://webkernelphp.org) framework —
> custom installer, lifecycle hooks, environment checks, and concern-based code generation.

---

## What it does

| Responsibility | Class |
|---|---|
| Custom installer for `webkernel-*` package types | `LCBaseInstaller` |
| Dispatch `extra.webkernel.lifecycle.events.{event}` callables | `LCHookDispatcher` |
| Run environment checks (`extra.webkernel.lifecycle.checks`) | `LCEnvChecker` |
| Run concern-based code generation | `LCConcernRunner` |
| Static entry-point for Composer scripts | `ComposerScripts` |

---

## extra.webkernel structure

```json
{
  "extra": {
    "webkernel": {
      "$schema": "./_schemas/schema.v1.json",
      "provider": "Vendor\\MyPackage\\MyProvider",
      "module": "vendor/parent-module",
      "lifecycle": {
        "checks": [
          {
            "check": "PHP_VERSION_ID < 80500",
            "on-fail": { "type": "danger", "message": "PHP >= 8.5 required." },
            "fix": "sudo apt install php8.5"
          }
        ],
        "events": {
          "post-autoload-dump": "Webkernel\\Lifecycle\\ComposerScripts::post_autoload_dump",
          "post-install-cmd": "",
          "post-update-cmd": ""
        },
        "concerns": [
          "Webkernel\\Lifecycle\\Concerns\\Builtin\\GenPathHelpers",
          "Webkernel\\Lifecycle\\Concerns\\Builtin\\GenVendorHelpers",
          "Your\\Package\\Concerns\\GenMyCustomFile"
        ]
      },
      "x-monorepo": {
        "package-repos": [
          "git@github.com:webkernelphp/config.git"
        ]
      }
    }
  }
}
```

---

## Concerns

A **Concern** is a self-contained code-generation unit. One concern = one responsibility.

### Built-in concerns

| FQCN | Generates | Output |
|---|---|---|
| `GenPathHelpers` | `platform_path()`, `base_path()`, `storage_path()` | `vendor/webkernel/path-helpers.php` |
| `GenVendorHelpers` | `vendor_path()`, `autoload_path()` | `vendor/webkernel/vendor-helpers.php` |

### Writing your own concern

```php
namespace Your\Package\Concerns;

use Composer\Script\Event;
use Webkernel\Lifecycle\Concerns\Contracts\LifecycleConcernContract;

final class GenMyConfig implements LifecycleConcernContract
{
    public function name(): string
    {
        return 'Generate my-config.php';
    }

    public function handle(Event $event): void
    {
        $vendor_dir = $event->getComposer()->getConfig()->get('vendor-dir');
        file_put_contents($vendor_dir . '/my-config.php', '<?php return [];');
    }
}
```

Then declare it in `extra.webkernel.lifecycle.concerns` of your `composer.json`.

**Rules:**
- Must implement `LifecycleConcernContract`.
- Must be **idempotent** (safe to re-run on every `composer dump-autoload`).
- Must NOT depend on any package outside `webkernel/lifecycle`.

---

## Environment checks

Checks run at `post-autoload-dump`. Each check is a PHP expression:

| `on-fail.type` | Behaviour |
|---|---|
| `info` | Blue notice, continues |
| `warning` | Yellow warning, continues |
| `danger` | Red error, **throws** and blocks Composer |

---

## Schema

The JSON Schema for `extra.webkernel` lives in `_schemas/schema.v1.json`.
Point your IDE to it with:

```json
"$schema": "./_schemas/schema.v1.json"
```

or reference the versioned path in the monorepo:

```
lifecycle/_schemas/schema.v1.json
```

---

## Package types

| Type | Install path |
|---|---|
| `webkernel-business-module` | `modules/{vendor}/{name}/` |
| `webkernel-business-module-feature` | `modules/{parentVendor}/{parentName}/features/{vendor}-{name}/` |
| `webkernel-platform-module` | `modules/{vendor}/{name}/` |
| `webkernel-platform-module-feature` | `modules/{parentVendor}/{parentName}/features/{vendor}-{name}/` |
| `webkernel-ffi` | `ffi/{vendor}/{name}/` |
| everything else | `vendor/{vendor}/{name}/` (standard) |

Feature packages must declare `extra.webkernel.module: "vendor/parent-name"`.

---

## License

Proprietary — (c) 2025–2027 Numerimondes, El Moumen Yassine
