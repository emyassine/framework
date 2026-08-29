<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n;

use Webkernel\Config\Config;

/**
 * Request locale for hot-path helpers (`fast_i18n`, `lang()`).
 *
 * //> Http::run() calls flush() so a worker does not leak the previous request.
 */
final class I18nContext
{
    public const COOKIE = 'webkernel_locale';

    private static string $locale = '';

    /**
     * @param $locale string
     *
     * @return void
     */
    public static function set_locale(string $locale): void
    {
        self::$locale = self::normalize($locale);
    }

    /**
     * Persist for following GET requests. Cookie only — never the query string.
     *
     * @param $locale string
     * @return bool
     */
    public static function persist(string $locale): bool
    {
        $normalized = self::normalize($locale);
        if ($normalized === '') {
            return false;
        }
        self::$locale = $normalized;
        if (! \headers_sent()) {
            \setcookie(self::COOKIE, $normalized, [
                'expires' => \time() + 365 * 86400,
                'path' => '/',
                'secure' => ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE[self::COOKIE] = $normalized;

        return true;
    }

    /**
     * @return string
     */
    public static function get_locale(): string
    {
        if (self::$locale !== '') {
            return self::$locale;
        }
        $from_cookie = $_COOKIE[self::COOKIE] ?? '';
        if (\is_string($from_cookie) && $from_cookie !== '') {
            $normalized = self::normalize($from_cookie);
            if ($normalized !== '') {
                return self::$locale = $normalized;
            }
        }
        if (\class_exists(Config::class, true)) {
            $from_config = Config::get('app.locale', '');
            if (\is_string($from_config) && $from_config !== '') {
                return self::normalize($from_config);
            }
        }

        return '';
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$locale = '';
    }

    /**
     * @param $locale string
     *
     * @return string
     */
    public static function normalize(string $locale): string
    {
        $base = \strtolower(\explode('-', \str_replace('_', '-', $locale), 2)[0]);

        return \preg_match('/^[a-z]{2,8}$/', $base) === 1 ? $base : '';
    }
}
