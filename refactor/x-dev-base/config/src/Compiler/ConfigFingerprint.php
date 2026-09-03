<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Compiler;

/**
 * Tracks file modification times and directory state for zero-overhead change detection.
 */
final class ConfigFingerprint
{
    /**
     * Determines whether the compiled configuration cache is stale relative to its sources.
     *
     * @param $cache_file string Path to compiled cache file.
     * @param $source_files list<string> Absolute paths to tracked configuration files.
     * @param $directories list<string> Monitored directory paths for addition/deletion detection.
     *
     * @return bool True when any source is newer, missing, or altered; false when valid.
     */
    public static function is_stale(string $cache_file, array $source_files, array $directories = []): bool
    {
        if (! \is_file($cache_file)) {
            return true;
        }

        $cache_mtime = @\filemtime($cache_file);

        if ($cache_mtime === false || $cache_mtime === 0) {
            return true;
        }

        // Check directory modification times (detects file additions, removals, renames)
        foreach ($directories as $dir) {
            if (\is_dir($dir)) {
                $dir_mtime = @\filemtime($dir);
                if ($dir_mtime === false || $dir_mtime > $cache_mtime) {
                    return true;
                }
            }
        }

        // Check individual file modification times
        foreach ($source_files as $file) {
            if (! \is_file($file)) {
                return true;
            }

            $file_mtime = @\filemtime($file);
            if ($file_mtime === false || $file_mtime > $cache_mtime) {
                return true;
            }
        }

        return false;
    }

    /**
     * Computes a cryptographic checksum of file sizes and modification times.
     *
     * @param $source_files list<string>
     *
     * @return string
     */
    public static function compute_hash(array $source_files): string
    {
        $payload = '';

        foreach ($source_files as $file) {
            if (\is_file($file)) {
                $payload .= $file . ':' . \filemtime($file) . ':' . \filesize($file) . ';';
            }
        }

        return \hash('xxh128', $payload);
    }
}
