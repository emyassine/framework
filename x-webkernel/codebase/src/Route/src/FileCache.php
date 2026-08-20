<?php declare(strict_types=1);

namespace Webkernel\Route;

use Closure;

/**
 * Opcache-friendly compiled-route file cache (FastRoute FileCache).
 *
 * @internal
 *
 * @phpstan-import-type Processed from Route
 */
final class FileCache
{
    private const DIRECTORY_PERMISSIONS = 0775;

    private const FILE_PERMISSIONS = 0664;

    private static Closure $empty_error_handler;

    public function __construct()
    {
        self::$empty_error_handler ??= static function (): void {};
    }

    /**
     * @param callable(): Processed $loader
     *
     * @return Processed
     */
    public function get(string $key, callable $loader): array
    {
        $result = self::read($key);
        if ($result !== null) {
            return $result;
        }

        $data = $loader();
        self::write($key, '<?php return '.var_export($data, true).';');

        return $data;
    }

    /**
     * @return Processed|null
     */
    private static function read(string $path): ?array
    {
        set_error_handler(self::$empty_error_handler);
        $value = include $path;
        restore_error_handler();

        return is_array($value) ? $value : null;
    }

    private static function write(string $path, string $content): void
    {
        $directory = dirname($path);
        if (! self::ensure_dir($directory) || ! is_writable($directory)) {
            throw new \RuntimeException('The cache directory is not writable "'.$directory.'"');
        }

        set_error_handler(self::$empty_error_handler);
        $tmp = $path.'.tmp';
        if (file_put_contents($tmp, $content, LOCK_EX) === false) {
            restore_error_handler();

            return;
        }

        chmod($tmp, self::FILE_PERMISSIONS);
        if (! rename($tmp, $path)) {
            unlink($tmp);
        }
        restore_error_handler();
    }

    private static function ensure_dir(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        set_error_handler(self::$empty_error_handler);
        $created = mkdir($directory, self::DIRECTORY_PERMISSIONS, true);
        restore_error_handler();

        return $created !== false || is_dir($directory);
    }
}
