<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

/**
 * Double-submit CSRF token. Meta + hidden field for every page by default.
 */
final class Csrf
{
    public const COOKIE = 'XSRF-TOKEN';

    public const FIELD = '_token';

    public const HEADER = 'X-CSRF-TOKEN';

    /**
     * @return string
     */
    public static function token(): string
    {
        $existing = $_COOKIE[self::COOKIE] ?? '';
        if (\is_string($existing) && \strlen($existing) === 64 && \ctype_xdigit($existing)) {
            return $existing;
        }
        $token = \bin2hex(\random_bytes(32));
        if (! \headers_sent()) {
            \setcookie(self::COOKIE, $token, [
                'expires' => 0,
                'path' => '/',
                'secure' => ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE[self::COOKIE] = $token;

        return $token;
    }

    /**
     * @return string
     */
    public static function field(): string
    {
        return '<input type="hidden" name="'.self::FIELD.'" value="'.\htmlspecialchars(self::token(), \ENT_QUOTES).'" autocomplete="off">';
    }

    /**
     * @return string
     */
    public static function meta(): string
    {
        return '<meta name="csrf-token" content="'.\htmlspecialchars(self::token(), \ENT_QUOTES).'">';
    }
}
