<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Closure;
use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 cache. APCu when available (kernel budget). Process array otherwise.
 */
final class CacheComposable implements ComposableContract, CacheInterface
{
    private const PREFIX = 'wk.cache.';

    /** @var array<string, mixed> */
    private array $memory = [];

    public static function api_name(): string
    {
        return 'cache';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (function_exists('apcu_fetch')) {
            $ok = false;
            $value = apcu_fetch(self::PREFIX.$key, $ok);
            if ($ok) {
                return $value;
            }
        }

        return $this->memory[$key] ?? $default;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $seconds = $this->ttl_seconds($ttl);
        $this->memory[$key] = $value;
        if (function_exists('apcu_store')) {
            return apcu_store(self::PREFIX.$key, $value, $seconds);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->memory[$key]);
        if (function_exists('apcu_delete')) {
            apcu_delete(self::PREFIX.$key);
        }

        return true;
    }

    public function clear(): bool
    {
        $this->memory = [];
        if (function_exists('apcu_cache_info') && function_exists('apcu_delete')) {
            $info = apcu_cache_info();
            if (is_array($info) && isset($info['cache_list']) && is_array($info['cache_list'])) {
                foreach ($info['cache_list'] as $entry) {
                    $info_key = $entry['info'] ?? '';
                    if (is_string($info_key) && str_starts_with($info_key, self::PREFIX)) {
                        apcu_delete($info_key);
                    }
                }
            }
        }

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->get((string) $key, $default);
        }

        return $out;
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $ok = true;
        foreach ($values as $key => $value) {
            $ok = $this->set((string) $key, $value, $ttl) && $ok;
        }

        return $ok;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $ok = true;
        foreach ($keys as $key) {
            $ok = $this->delete((string) $key) && $ok;
        }

        return $ok;
    }

    public function has(string $key): bool
    {
        if (function_exists('apcu_exists') && apcu_exists(self::PREFIX.$key)) {
            return true;
        }

        return array_key_exists($key, $this->memory);
    }

    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    private function ttl_seconds(\DateInterval|int|null $ttl): int
    {
        if ($ttl === null) {
            return 0;
        }
        if (is_int($ttl)) {
            return max(0, $ttl);
        }

        return max(0, (new \DateTimeImmutable())->add($ttl)->getTimestamp() - time());
    }
}
