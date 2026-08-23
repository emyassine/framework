<?php declare(strict_types=1);

namespace Webkernel\Cache;

use Webkernel\Container\Container;
use Predis\Client as RedisClient;

/**
 * APCu read/write for all compiled artifacts with Redis fallback.
 * Primary storage is APCu, with Redis as fallback for distributed environments.
 */
final class CompilationStore
{
    private static ?RedisClient $redis = null;

    /**
     * Set Redis client for distributed environments.
     */
    public static function set_redis(RedisClient $redis): void
    {
        self::$redis = $redis;
    }

    /**
     * Get a compiled artifact from the store.
     * Checks staleness first, then tries APCu, then Redis fallback, then recompiles.
     */
    public static function get(string $key, Container $container): mixed
    {
        // Check if compilation is stale first
        if (CompilationManifest::is_stale()) {
            Compiler::compile($container);
        }

        // Try APCu first
        $value = apcu_fetch($key);

        if ($value === false) {
            // Self-heal: try Redis fallback in distributed environments
            if (self::$redis !== null) {
                try {
                    $serialized = self::$redis->get("webkernel:{$key}");
                    if ($serialized !== null) {
                        $value = unserialize($serialized);
                        apcu_store($key, $value);
                        return $value;
                    }
                } catch (\Throwable $e) {
                    error_log('[Webkernel] Redis fallback failed: ' . $e->getMessage());
                }
            }

            // If still not found, recompile
            Compiler::compile($container);
            $value = apcu_fetch($key);

            if ($value === false) {
                throw new \RuntimeException("Failed to compile and retrieve artifact: {$key}");
            }
        }

        return $value;
    }

    /**
     * Store all compiled artifacts.
     * Stores in APCu and optionally in Redis for distributed environments.
     */
    public static function store_all(array $artifacts): void
    {
        foreach ($artifacts as $key => $value) {
            apcu_store($key, $value);

            // Also store in Redis if configured (for distributed environments)
            if (self::$redis !== null) {
                try {
                    self::$redis->set("webkernel:{$key}", serialize($value));
                } catch (\Throwable $e) {
                    error_log('[Webkernel] Redis store failed: ' . $e->getMessage());
                }
            }
        }
    }
}
