<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Composables;

/**
 * Current HTTP request. `webapp()->request()`.
 *
 * //> Reads $_SERVER. Not a PSR-7 message. Not a Container binding.
 */
final class RequestComposable implements ComposableContract
{
    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'request';
    }

    /**
     * @return string
     */
    public function user_agent(): string
    {
        return (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return \strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    /**
     * Path of the current request, or of `$uri` when given.
     *
     * @param $uri string|null
     * @return string
     */
    public function path(?string $uri = null): string
    {
        $raw = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path = \parse_url($raw, \PHP_URL_PATH);

        return \is_string($path) && $path !== '' ? $path : '/';
    }

    /**
     * @param $name string
     * @return string
     */
    public function header(string $name): string
    {
        $normalized = \strtoupper(\str_replace('-', '_', $name));
        $key = match ($normalized) {
            'CONTENT_TYPE', 'CONTENT_LENGTH' => $normalized,
            default => 'HTTP_'.$normalized,
        };

        return (string) ($_SERVER[$key] ?? '');
    }
}
