<?php declare(strict_types=1);

namespace Webkernel\Views;

/**
 * Resolve + compile-once + require. Target: cached path is one require per view.
 */
final class Engine
{
    /** @var array<string, string> */
    private static array $paths = [];

    /** @var array<string, string> compiled file per view name (request memo) */
    private static array $compiled = [];

    public static function add_path(string $path): void
    {
        $path = rtrim($path, '/\\');
        if ($path !== '') {
            self::$paths[$path] = $path;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function render(string $name, array $data = []): string
    {
        $file = self::compiled_file($name);
        $level = ob_get_level();
        ob_start();
        try {
            (static function (string $__file, array $__data): void {
                extract($__data, EXTR_SKIP);
                require $__file;
            })($file, $data);
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return (string) ob_get_clean();
    }

    public static function flush(): void
    {
        self::$compiled = [];
        self::$paths = [];
    }

    private static function compiled_file(string $name): string
    {
        $name = str_replace('.', '/', trim($name, '/'));
        if (isset(self::$compiled[$name])) {
            return self::$compiled[$name];
        }

        $source = self::find($name);
        $cache_dir = webkernel_platform_dir('views');
        if (! is_dir($cache_dir) && ! mkdir($cache_dir, 0775, true) && ! is_dir($cache_dir)) {
            throw new \RuntimeException('Unable to create '.$cache_dir);
        }

        $cache = $cache_dir.'/'.hash('xxh3', $source).'.php';
        $src_mtime = (int) filemtime($source);
        if (! is_file($cache) || (int) filemtime($cache) < $src_mtime) {
            file_put_contents($cache, Compiler::compile($source), LOCK_EX);
        }

        return self::$compiled[$name] = $cache;
    }

    private static function find(string $name): string
    {
        if (self::$paths === []) {
            self::add_path(webapp_path('views'));
            self::add_path(dirname(__DIR__).'/views');
        }

        foreach (self::$paths as $dir) {
            foreach ([$name.'.php', $name.'.wk.php'] as $file) {
                $full = $dir.'/'.$file;
                if (is_file($full)) {
                    return $full;
                }
            }
        }

        throw new \RuntimeException('View not found: '.$name);
    }
}
