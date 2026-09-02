<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Config;

use Webkernel\Config\Exceptions\ConfigWriteException;

/**
 * Standalone atomic config rewriter for persistent runtime configuration updates.
 *
 * Writes are atomic (tmp → rename) to avoid half-written files under high
 * concurrency (Swoole, FrankenPHP). OPcache is invalidated after each write.
 */
final class ConfigWriter
{
    /**
     * Merge $keys into the existing file at $path and write atomically.
     *
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
     * Serialise $tree to a PHP file at $path, atomically.
     *
     * @param array<string, mixed> $tree
     *
     * @throws ConfigWriteException
     */
    public static function write(string $path, array $tree): void
    {
        $dir = \dirname($path);

        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new ConfigWriteException('Unable to create directory: ' . $dir);
        }

        $exported = \var_export($tree, true);
        $year_end = ((int) \date('Y')) + 1;

        $body = <<<PHP
<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - {$year_end} Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
// WARNING: Some keys in this file are written by the platform itself.
// Do not edit platform-managed keys by hand — they will be overwritten on next boot.
return {$exported};
PHP;

        $tmp = $path . '.' . \bin2hex(\random_bytes(4)) . '.tmp';

        if (\file_put_contents($tmp, $body, \LOCK_EX) === false) {
            throw new ConfigWriteException('Unable to write temporary file: ' . $tmp);
        }

        if (! \rename($tmp, $path)) {
            @\unlink($tmp);
            throw new ConfigWriteException('Unable to atomically rename config file to: ' . $path);
        }

        if (\function_exists('opcache_invalidate')) {
            \opcache_invalidate($path, true);
        }
    }
}
