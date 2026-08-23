<?php declare(strict_types=1);

namespace Webkernel\Cache;

use Webkernel\Provider\ProviderRegistry;

/**
 * Tracks modification times for compilable sources.
 */
final class CompilationManifest
{
    /**
     * @return list<string>
     */
    private static function watched_files(): array
    {
        return [
            ProviderRegistry::file(),
            ...self::module_provider_files(),
            ...self::module_route_files(),
            ...self::config_files(),
        ];
    }

    public static function is_stale(): bool
    {
        $compiled_at = CompilationStore::fetch('webkernel.compiled_at');
        if ($compiled_at === false) {
            return true;
        }

        foreach (self::watched_files() as $file) {
            if (! file_exists($file)) {
                continue;
            }
            if (filemtime($file) > (int) $compiled_at) {
                return true;
            }
        }

        return false;
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
        }

        return dirname(__DIR__, 4).'/'.$relative;
    }
}
