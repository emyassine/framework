<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Composables;

use Webkernel\Config\Config;
use Webkernel\Config\Repository\ConfigRepository;

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

    public function set(string $key, mixed $value): ConfigRepository
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
