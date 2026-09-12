# Immutability & Guard System

To guarantee enterprise application reliability, Webkernel Config includes a key protection system that prevents accidental or unauthorized runtime mutations of critical configuration trees.

---

## Defining Guards

Protect configuration keys by passing an array of exact keys or prefix segments to `Config::protect()`:

```php
use Webkernel\Config\Config;

Config::protect([
    'app.secret',
    'database.connections',
    'platform',
]);
```

---

## Match Rules & Prefix Protection

When a key or prefix is protected:
- **Exact Match**: Protecting `'app.secret'` blocks `Config::set('app.secret', 'value')`.
- **Prefix Tree Match**: Protecting `'platform'` blocks any key starting with `'platform.'`, such as `'platform.id'`, `'platform.debug'`, or `'platform.internal.cache_path'`.

### Guard Exceptions

Attempting to mutate a protected key throws a `Webkernel\Config\Exceptions\ConfigGuardException`:

```php
use Webkernel\Config\Config;
use Webkernel\Config\Exceptions\ConfigGuardException;

try {
    Config::set('platform.id', 'malicious-override');
} catch (ConfigGuardException $e) {
    // Config key "platform.id" is protected and cannot be mutated at runtime.
    echo $e->getMessage();
}
```
