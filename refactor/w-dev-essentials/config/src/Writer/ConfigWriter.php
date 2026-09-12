<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Writer;

use Webkernel\Config\Exceptions\ConfigWriteException;

/**
 * Performs atomic configuration file writes with OPcache invalidation.
 */
class ConfigWriter
{
    /**
     * Merges keys into an existing configuration file and writes atomically.
     *
     * @param $path string Target file path.
     * @param $keys array<string, mixed> Configuration updates to merge.
     *
     * @return void
     *
     * @throws ConfigWriteException
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
     * Serializes an array tree to a PHP configuration file atomically.
     *
     * @param $path string Target file path.
     * @param $tree array<string, mixed> Configuration array to serialize.
     *
     * @return void
     *
     * @throws ConfigWriteException
     */
    public static function write(string $path, array $tree): void
    {
        $dir = \dirname($path);

        if (! \is_dir($dir) && ! @\mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new ConfigWriteException(\sprintf('Unable to create configuration directory: "%s"', $dir));
        }

        $exported = \var_export($tree, true);
        $year_end = ((int) \date('Y')) + 2;

        $body = <<<PHP
<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - {$year_end} Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
// WARNING: Configuration persisted at runtime.
// Hand edits will be preserved unless explicitly overwritten by platform updates.

return {$exported};
PHP;

        $tmp = $path . '.' . \bin2hex(\random_bytes(4)) . '.tmp';

        if (@\file_put_contents($tmp, $body, \LOCK_EX) === false) {
            throw new ConfigWriteException(\sprintf('Unable to write temporary configuration file: "%s"', $tmp));
        }

        if (! @\rename($tmp, $path)) {
            @\unlink($tmp);
            throw new ConfigWriteException(\sprintf('Unable to atomically rename configuration file to: "%s"', $path));
        }

        if (\function_exists('opcache_invalidate')) {
            @\opcache_invalidate($path, true);
        }
    }
}
