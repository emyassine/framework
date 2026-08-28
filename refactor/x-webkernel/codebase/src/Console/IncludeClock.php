<?php declare(strict_types=1);

namespace Webkernel\Console;

/**
 * Times include/require execute cost with hrtime. Loaded only when
 * WEBKERNEL_PROFILE_LIFECYCLE=1 (webkernel server --profile-lifecycle).
 *
 * Exclusive nested time: a parent file is charged for its own code, not
 * for children it includes. Works with OPcache (no file stream).
 *
 * @phpstan-type FileStat array{ms: float, run_ms: float, read_ms: float, bytes: int}
 * @phpstan-type Report array{
 *   schema: string,
 *   files: list<array{path: string, total_ms: float, run_ms: float, read_ms: float}>,
 *   classes: list<string>,
 *   mem: int,
 *   render_ms: float|null
 * }
 */
final class IncludeClock
{
    public const SCHEMA = 'webkernel.profile.include/v1';

    /** @var array<string, array{run_ns: int, bytes: int}> */
    private static array $stats = [];

    private static bool $active = false;

    /** @var list<array{path: string, t: int, child: int}> */
    private static array $stack = [];

    /** @var \Closure(string): mixed|null */
    private static ?\Closure $isolated = null;

    /**
     * @return void
     */
    public static function start(): void
    {
        self::$stats = [];
        self::$stack = [];
        self::$active = true;
    }

    /**
     * @return bool
     */
    public static function active(): bool
    {
        return self::$active;
    }

    /**
     * @param string $path Absolute or relative file path being included
     *
     * @return void
     */
    public static function enter(string $path): void
    {
        if (! self::$active) {
            return;
        }
        $normalized = \str_replace('\\', '/', $path);
        $real = \realpath($normalized);
        self::$stack[] = [
            'path' => \is_string($real) ? \str_replace('\\', '/', $real) : $normalized,
            't' => (int) \hrtime(true),
            'child' => 0,
        ];
    }

    /**
     * @return void
     */
    public static function leave(): void
    {
        if (! self::$active || self::$stack === []) {
            return;
        }
        $frame = \array_pop(self::$stack);
        $dt = (int) \hrtime(true) - $frame['t'];
        $exclusive = \max(0, $dt - $frame['child']);
        $path = $frame['path'];
        if (! isset(self::$stats[$path])) {
            self::$stats[$path] = ['run_ns' => 0, 'bytes' => 0];
        }
        self::$stats[$path]['run_ns'] += $exclusive;
        if (self::$stack !== []) {
            self::$stack[\count(self::$stack) - 1]['child'] += $dt;
        }
    }

    /**
     * Scope-isolated include (Composer ClassLoader semantics).
     *
     * @param string $path File to include
     *
     * @return mixed Value returned by the included file
     */
    public static function run_file(string $path): mixed
    {
        $load = self::isolated();
        if (! self::$active) {
            return $load($path);
        }
        self::enter($path);
        try {
            return $load($path);
        } finally {
            self::leave();
        }
    }

    /**
     * Replace Composer ClassLoader include closure so autoloaded classes
     * are timed. Call after autoload.php has registered the loader.
     *
     * @return void
     */
    public static function hook_autoloader(): void
    {
        if (! self::$active || ! \class_exists(\Composer\Autoload\ClassLoader::class, false)) {
            return;
        }
        $ref = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        if (! $ref->hasProperty('includeFile')) {
            return;
        }
        $prop = $ref->getProperty('includeFile');
        $prop->setAccessible(true);
        $prop->setValue(null, static function (string $file): void {
            self::run_file($file);
        });
    }

    /**
     * @return array<string, FileStat>
     */
    public static function stop(): array
    {
        while (self::$stack !== []) {
            self::leave();
        }
        self::$active = false;
        $out = [];
        foreach (self::$stats as $path => $row) {
            $ms = $row['run_ns'] / 1e6;
            $out[$path] = [
                'ms' => $ms,
                'run_ms' => $ms,
                'read_ms' => 0.0,
                'bytes' => $row['bytes'],
            ];
        }
        \uasort($out, static fn (array $a, array $b): int => $b['ms'] <=> $a['ms']);

        return $out;
    }

    /**
     * Machine payload for a future panel / agent. Paths may be relative.
     *
     * @param array<string, FileStat> $files
     * @param list<string>            $classes
     * @param int                     $mem
     * @param float|null              $render_ms
     *
     * @return Report
     */
    public static function report(array $files, array $classes, int $mem, ?float $render_ms): array
    {
        $rows = [];
        foreach ($files as $path => $row) {
            $rows[] = [
                'path' => $path,
                'total_ms' => $row['ms'],
                'run_ms' => $row['run_ms'],
                'read_ms' => $row['read_ms'],
            ];
        }

        return [
            'schema' => self::SCHEMA,
            'files' => $rows,
            'classes' => \array_values($classes),
            'mem' => $mem,
            'render_ms' => $render_ms,
        ];
    }

    /**
     * @return \Closure(string): mixed
     */
    private static function isolated(): \Closure
    {
        return self::$isolated ??= \Closure::bind(static function (string $file): mixed {
            return include $file;
        }, null, null);
    }
}
