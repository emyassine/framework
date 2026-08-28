<?php declare(strict_types=1);

namespace Webkernel\Config;

/**
 * Standalone atomic config rewriter. No container, no composable.
 * Used by fast-boot miss path and by ConfigComposable::set().
 */
final class ConfigWriter
{
    /**
     * @param array<string, mixed> $keys
     */
    public static function atomic_rewrite(string $path, array $keys): void
    {
        $current = [];
        if (\is_file($path)) {
            $loaded = require $path;
            if (\is_array($loaded)) {
                $current = $loaded;
            }
        }

        self::write($path, \array_replace_recursive($current, $keys));
    }

    /**
     * @param array<string, mixed> $tree
     */
    public static function write(string $path, array $tree): void
    {
        $dir = \dirname($path);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }

        $exported = \var_export($tree, true);
        $end = ((int) \date('Y')) + 1;
        $body = <<<PHP
<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - {$end} Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
// WARNING: Some keys in this file are written by the platform itself (see
// "platform-managed" comments). Do not edit those keys by hand — your changes
// will be overwritten on next boot if the platform detects a drift.

return {$exported};

PHP;

        $tmp = $path.'.'.\bin2hex(\random_bytes(4)).'.tmp';
        if (\file_put_contents($tmp, $body, LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write '.$tmp);
        }
        if (! \rename($tmp, $path)) {
            @\unlink($tmp);
            throw new \RuntimeException('Unable to rename config over '.$path);
        }
        if (\function_exists('opcache_invalidate')) {
            \opcache_invalidate($path, true);
        }
    }
}
