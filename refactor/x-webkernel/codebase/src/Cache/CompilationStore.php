<?php declare(strict_types=1);

namespace Webkernel\Cache;

use Webkernel\Container\Container;
use Webkernel\PhpFileBuilder;
use Webkernel\Route\Compile\Cache;

/**
 * Compiled artifact store. PHP files (OPcache) plus APCu when available.
 * php -S has no APCu — the compiled PHP file is the source of truth.
 */
final class CompilationStore
{
    /** @var array<string, mixed> */
    private static array $memory = [];

    private static mixed $redis = null;

    public static function set_redis(object $redis): void
    {
        self::$redis = $redis;
    }

    /**
     * Get a compiled artifact. Recompiles when stale or missing.
     */
    public static function get(string $key, Container $container): mixed
    {
        if ($key === 'webkernel.global.routes') {
            return self::routes($container);
        }

        if (! CompilationManifest::is_stale()) {
            $value = self::fetch($key);
            if ($value !== false) {
                return $value;
            }
        }

        Compiler::compile($container);
        $value = self::fetch($key);
        if ($value === false) {
            throw new \RuntimeException('Failed to compile and retrieve artifact: '.$key);
        }

        return $value;
    }

    /**
     * @return array{0: mixed, 1: mixed}
     */
    private static function routes(Container $container): array
    {
        $payload = Cache::payload();
        if ($payload !== null && ! CompilationManifest::is_stale_payload($payload)) {
            return $payload['data'];
        }

        Compiler::compile($container);
        $payload = Cache::payload();
        if ($payload === null) {
            throw new \RuntimeException('Failed to compile and retrieve artifact: webkernel.global.routes');
        }

        return $payload['data'];
    }

    /**
     * @param array<string, mixed> $artifacts
     */
    public static function store_all(array $artifacts): void
    {
        foreach ($artifacts as $key => $value) {
            if ($key === 'webkernel.global.routes') {
                continue;
            }
            self::put($key, $value);
        }
    }

    public static function fetch(string $key): mixed
    {
        if (isset(self::$memory[$key])) {
            return self::$memory[$key];
        }
        if (self::apcu_available()) {
            $ok = false;
            $value = apcu_fetch($key, $ok);
            if ($ok) {
                self::$memory[$key] = $value;

                return $value;
            }
        }

        if ($key === 'webkernel.global.routes') {
            $data = Cache::read();
            if (is_array($data)) {
                self::$memory[$key] = $data;

                return $data;
            }

            return false;
        }

        $file = self::artifact_path($key);
        if (is_file($file)) {
            $value = include $file;
            self::$memory[$key] = $value;

            return $value;
        }

        if (self::$redis !== null && method_exists(self::$redis, 'get')) {
            try {
                $serialized = self::$redis->get('webkernel:'.$key);
                if (is_string($serialized) && $serialized !== '') {
                    $value = unserialize($serialized);
                    self::$memory[$key] = $value;

                    return $value;
                }
            } catch (\Throwable $e) {
                error_log('[Webkernel] Redis fallback failed: '.$e->getMessage());
            }
        }

        return false;
    }

    public static function put(string $key, mixed $value): void
    {
        self::$memory[$key] = $value;
        if (self::apcu_available()) {
            apcu_store($key, $value);
        }
        if ($key === 'webkernel.global.routes' || ! is_array($value) || ! self::is_exportable($value)) {
            return;
        }
        $file = self::artifact_path($key);
        $directory = dirname($file);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return;
        }
        PhpFileBuilder::make()
            ->with_header(['Compiled artifact. Do not edit.'], 'CompilationStore')
            ->with_return_array($value)
            ->save_to_atomic($file);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
        if (self::$redis !== null && method_exists(self::$redis, 'set')) {
            try {
                self::$redis->set('webkernel:'.$key, serialize($value));
            } catch (\Throwable $e) {
                error_log('[Webkernel] Redis store failed: '.$e->getMessage());
            }
        }
    }

    public static function artifact_path(string $key): string
    {
        $safe = preg_replace('/[^a-z0-9_]+/i', '_', $key) ?? 'artifact';

        return Cache::directory().'/'.$safe.'.php';
    }

    private static function is_exportable(mixed $value): bool
    {
        if (is_scalar($value) || $value === null) {
            return true;
        }
        if (! is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (! self::is_exportable($item)) {
                return false;
            }
        }

        return true;
    }

    private static function apcu_available(): bool
    {
        if (! function_exists('apcu_fetch') || ! function_exists('apcu_store')) {
            return false;
        }
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
            return filter_var(ini_get('apc.enable_cli'), FILTER_VALIDATE_BOOLEAN);
        }

        return true;
    }
}
