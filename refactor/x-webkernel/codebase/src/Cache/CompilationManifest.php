<?php declare(strict_types=1);

namespace Webkernel\Cache;

use Webkernel\Provider\ProviderRegistry;

/**
 * Tracks modification times for all compilable sources.
 * Used to detect staleness and trigger recompilation.
 */
final class CompilationManifest
{
    private static function watched_files(): array
    {
        return [
            ProviderRegistry::file(),
            ...self::module_provider_files(),
            ...self::module_route_files(),
            ...self::config_files(),
        ];
    }

    /**
     * Check if the compiled artifacts are stale.
     * Returns true if any watched file has been modified since last compilation.
     */
    public static function is_stale(): bool
    {
        $compiled_at = apcu_fetch('webkernel.compiled_at');

        if ($compiled_at === false) {
            return true;
        }

        foreach (self::watched_files() as $file) {
            if (!file_exists($file)) {
                continue;
            }
            if (filemtime($file) > $compiled_at) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all module provider file paths.
     */
    private static function module_provider_files(): array
    {
        $refactorDir = __DIR__ . '/../../../../..';
        $modulesDir = $refactorDir . '/modules';

        if (!is_dir($modulesDir)) {
            return [];
        }

        return glob($modulesDir . '/*/*Provider.php') ?: [];
    }

    /**
     * Get all module route file paths.
     */
    private static function module_route_files(): array
    {
        $refactorDir = __DIR__ . '/../../../../..';
        $modulesDir = $refactorDir . '/modules';

        if (!is_dir($modulesDir)) {
            return [];
        }

        return glob($modulesDir . '/*/routes.php') ?: [];
    }

    /**
     * Get all config file paths.
     */
    private static function config_files(): array
    {
        $refactorDir = __DIR__ . '/../../../../..';
        $configDir = $refactorDir . '/config';

        return [
            $configDir . '/app.php',
            $configDir . '/app.dev.php',
            $configDir . '/app.prod.php',
        ];
    }
}
