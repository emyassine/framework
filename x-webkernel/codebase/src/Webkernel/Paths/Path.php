<?php declare(strict_types=1);

namespace Webkernel\Paths;

/**
 * Tiny pure path helpers for early boot / Vite globs.
 * I/O → {@see \Illuminate\Filesystem\Filesystem} (ADD-0006 ladder).
 */
final class Path
{
    public static function posix(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /** Relative path from $from to $to when under $from; otherwise $to. */
    public static function relative(string $from, string $to): string
    {
        $from = rtrim(self::posix($from), '/');
        $to = self::posix($to);

        if (str_starts_with($to, $from . '/')) {
            return substr($to, strlen($from) + 1);
        }

        $from_real = realpath($from);
        $to_real = realpath($to);
        if ($from_real !== false && $to_real !== false) {
            $from_real = rtrim(self::posix($from_real), '/');
            $to_real = self::posix($to_real);
            if (str_starts_with($to_real, $from_real . '/')) {
                return substr($to_real, strlen($from_real) + 1);
            }
        }

        return $to;
    }

    /** Relative posix under $root, or null if outside. */
    public static function under(string $root, string $absolute): ?string
    {
        $rel = self::posix(self::relative($root, $absolute));
        if ($rel === '' || str_starts_with($rel, '..') || str_starts_with($rel, '/')) {
            return null;
        }

        return $rel;
    }

    /**
     * Relative symlink target from a directory to a file (portable if the tree moves).
     */
    public static function relative_link(string $from_dir, string $to_file): string
    {
        $from = array_values(array_filter(explode('/', self::posix($from_dir)), static fn (string $s): bool => $s !== ''));
        $to = array_values(array_filter(explode('/', self::posix($to_file)), static fn (string $s): bool => $s !== ''));

        while ($from !== [] && $to !== [] && $from[0] === $to[0]) {
            array_shift($from);
            array_shift($to);
        }

        return str_repeat('../', count($from)).implode('/', $to);
    }

    /** Directory prefix of a glob before the first '*'. */
    public static function glob_base(string $glob): string
    {
        $glob = self::posix($glob);
        $star = strpos($glob, '*');
        if ($star === false) {
            return $glob;
        }

        $prefix = substr($glob, 0, $star);
        $slash = strrpos($prefix, '/');

        return $slash === false ? '' : rtrim(substr($prefix, 0, $slash), '/');
    }
}
