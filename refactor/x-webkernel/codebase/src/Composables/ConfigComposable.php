<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Webkernel\Config\Config;

/**
 * Same store as Config::get / Config::set.
 */
final class ConfigComposable implements ComposableContract
{
    public static function api_name(): string
    {
        return 'config';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }

    public function set(string $key, mixed $value): Config
    {
        return Config::set($key, $value);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Config::all();
    }
}
