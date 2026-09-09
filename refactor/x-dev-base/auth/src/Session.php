<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth;

/**
 * Native PHP session. One start per process.
 */
final class Session
{
    public const AUTH_ID = '_webkernel_auth_id';

    /**
     * @return void
     */
    public static function start(): void
    {
        if (\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg') {
            if (! isset($_SESSION) || ! \is_array($_SESSION)) {
                $_SESSION = [];
            }

            return;
        }
        if (\session_status() === \PHP_SESSION_ACTIVE) {
            return;
        }
        if (\headers_sent()) {
            if (! isset($_SESSION) || ! \is_array($_SESSION)) {
                $_SESSION = [];
            }

            return;
        }
        \session_start();
    }

    /**
     * @param $key string
     * @param $default mixed
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();

        return $_SESSION[$key] ?? $default;
    }

    /**
     * @param $key string
     * @param $value mixed
     *
     * @return void
     */
    public static function put(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key string
     *
     * @return void
     */
    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }
}
