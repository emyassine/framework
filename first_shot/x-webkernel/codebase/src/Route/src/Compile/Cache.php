<?php declare(strict_types=1);

namespace Webkernel\Route\Compile;

/**
 * Opcache-friendly compiled dispatcher file. Rewritten when the hash changes
 * (declared route-file mtimes + binding fingerprint). No artisan clear.
 *
 * @internal
 *
 * @phpstan-import-type RouteData from Generator
 */
final class Cache
{
    private const DIRECTORY_PERMISSIONS = 0775;

    private const FILE_PERMISSIONS = 0664;

    public static function path(string $hash): string
    {
        $rel = 'platform/storage/framework/cache';
        $configured = webapp()->config('platform.storage_path');
        if (is_string($configured) && $configured !== '') {
            $rel = $configured.'/framework/cache';
        }

        return webapp_path($rel).'/routes_'.$hash.'.php';
    }

    /**
     * @return RouteData|null
     */
    public static function read(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }
        $value = include $path;

        return is_array($value) ? $value : null;
    }

    /**
     * @param RouteData $data
     */
    public static function write(string $path, array $data): void
    {
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, self::DIRECTORY_PERMISSIONS, true) && ! is_dir($directory)) {
            return;
        }
        if (! is_writable($directory)) {
            return;
        }

        $tmp = $path.'.'.getmypid().'.tmp';
        $body = "<?php declare(strict_types=1);\n\nreturn ".var_export($data, true).";\n";
        if (file_put_contents($tmp, $body) === false) {
            return;
        }
        chmod($tmp, self::FILE_PERMISSIONS);
        if (! rename($tmp, $path)) {
            unlink($tmp);
        }
    }
}
