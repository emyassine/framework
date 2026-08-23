<?php declare(strict_types=1);

namespace Webkernel\Cache;

use Webkernel\Container\Container;

/**
 * Compiled artifact store. APCu when available, process memory otherwise.
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
        if (CompilationManifest::is_stale()) {
            Compiler::compile($container);
        }

        $value = self::fetch($key);
        if ($value !== false) {
            return $value;
        }

        if (self::$redis !== null && method_exists(self::$redis, 'get')) {
            try {
                $serialized = self::$redis->get('webkernel:'.$key);
                if (is_string($serialized) && $serialized !== '') {
                    $value = unserialize($serialized);
                    self::put($key, $value);

                    return $value;
                }
            } catch (\Throwable $e) {
                error_log('[Webkernel] Redis fallback failed: '.$e->getMessage());
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
     * @param array<string, mixed> $artifacts
     */
    public static function store_all(array $artifacts): void
    {
        foreach ($artifacts as $key => $value) {
            self::put($key, $value);
            if (self::$redis !== null && method_exists(self::$redis, 'set')) {
                try {
                    self::$redis->set('webkernel:'.$key, serialize($value));
                } catch (\Throwable $e) {
                    error_log('[Webkernel] Redis store failed: '.$e->getMessage());
                }
            }
        }
    }

    public static function fetch(string $key): mixed
    {
        if (self::apcu_available()) {
            $ok = false;
            $value = apcu_fetch($key, $ok);
            if ($ok) {
                return $value;
            }
        }

        return self::$memory[$key] ?? false;
    }

    public static function put(string $key, mixed $value): void
    {
        self::$memory[$key] = $value;
        if (self::apcu_available()) {
            apcu_store($key, $value);
        }
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
