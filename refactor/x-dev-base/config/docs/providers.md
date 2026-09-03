# Package Provider Configuration & Asset Publishing

Packages interact with Webkernel Config via the `PlatformProvider::CONFIG` constant structure.

---

## Provider CONFIG Specification

Package providers declare configuration files using an associative array structure with path, publish destination, and optional tag metadata:

```php
namespace Acme\Telemetry;

use Webkernel\PlatformProvider;

class TelemetryProvider extends PlatformProvider
{
    public const CONFIG = [
        'telemetry' => [
            'path'    => __DIR__ . '/../config/telemetry.php',
            'publish' => \config_path('telemetry.php'),
            'tag'     => 'telemetry-config',
        ],
    ];
}
```

### Supported Array Formats

1. **Rich Metadata Array (Recommended)**:
   ```php
   public const CONFIG = [
       'courier' => [
           'path'    => __DIR__ . '/../config/courier.php',
           'publish' => \config_path('courier.php'),
           'tag'     => 'courier-config',
       ],
   ];
   ```

2. **Simple Keyed Path**:
   ```php
   public const CONFIG = [
       'courier' => __DIR__ . '/../config/courier.php',
   ];
   ```

---

## Asset Publishing Manifest

Tools, installers, and CLI commands publish package configuration files by querying `Config::publishables()`:

```php
use Webkernel\Config\Config;

// Get all publishable configurations across all registered providers
$publishables = Config::publishables();

foreach ($publishables as $item) {
    echo "Key     : {$item->key}\n";
    echo "Source  : {$item->source}\n";
    echo "Target  : {$item->target}\n";
    echo "Tag     : {$item->tag}\n";
    echo "Package : {$item->package}\n";
}
```

### Filtering by Tag

```php
// Retrieve only items matching the specified tag
$courier_items = Config::publishables('courier-config');
```
