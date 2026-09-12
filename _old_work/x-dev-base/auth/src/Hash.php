<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth;

/**
 * Password hashes. Argon2id when the runtime has it.
 */
final class Hash
{
    /**
     * @param $value string
     *
     * @return string
     */
    public static function make(string $value): string
    {
        $algo = \defined('PASSWORD_ARGON2ID') ? \PASSWORD_ARGON2ID : \PASSWORD_DEFAULT;
        $hash = \password_hash($value, $algo);
        if (! \is_string($hash) || $hash === '') {
            throw new \RuntimeException('password_hash failed.');
        }

        return $hash;
    }

    /**
     * @param $value string
     * @param $hash string
     *
     * @return bool
     */
    public static function check(string $value, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        return \password_verify($value, $hash);
    }
}
