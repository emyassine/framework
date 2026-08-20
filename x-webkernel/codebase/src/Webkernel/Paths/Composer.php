<?php declare(strict_types=1);

namespace Webkernel\Paths;

use Composer\Config;
use Composer\Factory;
use Composer\Json\JsonFile;

/**
 * Early-boot Composer path resolution (before Laravel).
 *
 * Only what is used: root + vendor-dir. No auth, no composer.json mutation,
 * no package inventory (use ComposerModuleScanner / InstalledVersions for that).
 */
final class Composer
{
    private static ?string $resolved_root = null;

    private static ?string $resolved_vendor = null;

    /** @var array<string, mixed>|null */
    private static ?array $raw = null;

    private static ?int $mtime = null;

    private function __construct()
    {
    }

    /**
     * Absolute directory that owns the host composer.json.
     *
     * Resolution order (first hit wins):
     *  1. Composer Factory (COMPOSER env / cwd-relative composer.json)
     *  2. Walk up from getcwd() — covers `php artisan serve` where cwd is public/
     *  3. Walk up from SCRIPT_FILENAME (public/index.php → project root)
     *  4. Walk up from this package file (path repo / vendor install)
     */
    public static function root(): string
    {
        if (self::$resolved_root !== null) {
            return self::$resolved_root;
        }

        if (class_exists(Factory::class)) {
            try {
                $file = Factory::getComposerFile();
                if (is_string($file) && $file !== '' && is_file($file)) {
                    $dir = dirname($file);
                    $real = realpath($dir);

                    return self::$resolved_root = $real !== false ? $real : $dir;
                }
            } catch (\Throwable) {
                // fall through
            }
        }

        $candidates = [];

        $cwd = getcwd();
        if (is_string($cwd) && $cwd !== '') {
            $candidates[] = $cwd;
        }

        $script = $_SERVER['SCRIPT_FILENAME'] ?? null;
        if (is_string($script) && $script !== '') {
            $candidates[] = $script;
        }

        // software/lifecycle/src/paths → … → host root (path repo)
        // or third_party/.../lifecycle/src/paths → host root
        $candidates[] = __DIR__;

        foreach ($candidates as $start) {
            $found = self::find_composer_root($start);
            if ($found !== null) {
                return self::$resolved_root = $found;
            }
        }

        throw new \RuntimeException('Cannot resolve project root: no composer.json found.');
    }

    /**
     * Walk up from $start until a directory containing composer.json is found.
     */
    private static function find_composer_root(string $start): ?string
    {
        $dir = $start;
        if (is_file($dir)) {
            $dir = dirname($dir);
        }

        $real = realpath($dir);
        if ($real !== false) {
            $dir = $real;
        }

        $seen = [];
        while ($dir !== '' && ! isset($seen[$dir])) {
            $seen[$dir] = true;

            if (is_file($dir.DIRECTORY_SEPARATOR.'composer.json')) {
                return $dir;
            }

            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        return null;
    }

    /**
     * Absolute Composer vendor-dir (config vendor-dir, default vendor/).
     */
    public static function vendor_dir(): string
    {
        if (self::$resolved_vendor !== null) {
            return self::$resolved_vendor;
        }

        return self::$resolved_vendor = self::config_dir('vendor-dir', 'vendor');
    }

    /**
     * Clear process memo (tests).
     */
    public static function flush(): void
    {
        self::$resolved_root = null;
        self::$resolved_vendor = null;
        self::$raw = null;
        self::$mtime = null;
    }

    /**
     * @param  string  $key               Config key (e.g. vendor-dir).
     * @param  string  $default_relative  Relative default under root.
     */
    private static function config_dir(string $key, string $default_relative): string
    {
        $root = self::root();
        $cfg = new Config(true, $root);
        $cfg->merge(['config' => self::read_raw($root)['config'] ?? []]);
        $dir = (string) ($cfg->get($key) ?: $default_relative);

        if ($dir !== '' && $dir[0] !== '/' && ! preg_match('#^[A-Za-z]:[\\\\/]#', $dir)) {
            $dir = $root . DIRECTORY_SEPARATOR . $dir;
        }

        return rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $dir), DIRECTORY_SEPARATOR);
    }

    /**
     * @return array<string, mixed>
     */
    private static function read_raw(string $root): array
    {
        $path = $root . '/composer.json';
        $mtime = filemtime($path);
        if ($mtime === false) {
            throw new \RuntimeException("Cannot stat [{$path}].");
        }
        if (self::$raw !== null && self::$mtime === $mtime) {
            return self::$raw;
        }
        self::$raw = (new JsonFile($path))->read();
        self::$mtime = $mtime;

        return self::$raw;
    }
}
