<?php declare(strict_types=1);

namespace Webkernel\Cache;

use Webkernel\Route\Compile\Cache;

/**
 * Tracks modification times for compilable sources.
 */
final class CompilationManifest
{
    /**
     * @return array<string, int>
     */
    public static function fingerprints(): array
    {
        $out = [];
        foreach (self::watched_files() as $file) {
            if (! is_file($file)) {
                continue;
            }
            $out[$file] = (int) filemtime($file);
        }

        return $out;
    }

    /**
     * @param array{compiled_at?: int, host?: string, files?: array<string, int>} $payload
     */
    public static function is_stale_payload(array $payload): bool
    {
        $compiled_at = (int) ($payload['compiled_at'] ?? 0);
        if ($compiled_at <= 0) {
            return true;
        }

        $current_host = self::request_host();
        $cached_host = (string) ($payload['host'] ?? '');
        if ($cached_host !== '' && $current_host !== '' && $cached_host !== $current_host) {
            return true;
        }

        $files = $payload['files'] ?? [];
        foreach ($files as $file => $mtime) {
            if (! is_string($file) || $file === '') {
                continue;
            }
            if (! is_file($file)) {
                return true;
            }
            if ((int) filemtime($file) > $compiled_at) {
                return true;
            }
        }

        foreach (self::watched_files() as $file) {
            if (! is_file($file)) {
                continue;
            }
            if (! isset($files[$file]) && (int) filemtime($file) > $compiled_at) {
                return true;
            }
        }

        return false;
    }

    public static function is_stale(): bool
    {
        $payload = Cache::payload();
        if ($payload === null) {
            return true;
        }

        return self::is_stale_payload($payload);
    }

    /**
     * @return list<string>
     */
    public static function watched_files(): array
    {
        return [
            dirname(__DIR__).'/Provider/ProviderRegistry.php',
            ...self::module_provider_files(),
            ...self::module_route_files(),
            ...self::kernel_route_files(),
            ...self::config_files(),
        ];
    }

    /**
     * @return list<string>
     */
    private static function module_provider_files(): array
    {
        $modules_dir = self::host_path('modules');
        if (! is_dir($modules_dir)) {
            return [];
        }

        return glob($modules_dir.'/*/*Provider.php') ?: [];
    }

    /**
     * @return list<string>
     */
    private static function module_route_files(): array
    {
        $modules_dir = self::host_path('modules');
        if (! is_dir($modules_dir)) {
            return [];
        }

        return glob($modules_dir.'/*/routes.php') ?: [];
    }

    /**
     * @return list<string>
     */
    private static function kernel_route_files(): array
    {
        $file = dirname(__DIR__, 2).'/routes.php';

        return is_file($file) ? [$file] : [];
    }

    /**
     * @return list<string>
     */
    private static function config_files(): array
    {
        $config_dir = self::host_path('config');

        return [
            $config_dir.'/app.php',
            $config_dir.'/app.dev.php',
            $config_dir.'/app.prod.php',
        ];
    }

    private static function host_path(string $relative): string
    {
        if (function_exists('webapp_path')) {
            return webapp_path($relative);
        } else {
            throw new \RuntimeException('webapp_path function not available, host path unavailable');
        }
    }

    private static function request_host(): string
    {
        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '') {
            return '';
        }
        if (str_starts_with($host, '[')) {
            $end = strpos($host, ']');

            return $end === false ? $host : substr($host, 0, $end + 1);
        }
        $colon = strrpos($host, ':');
        if ($colon === false) {
            return $host;
        }

        return substr($host, 0, $colon);
    }
}
