<?php declare(strict_types=1);

namespace Webkernel\Provider;

/**
 * Auto-discovers providers from the modules/ directory.
 * Eliminates the need to manually edit the registry when adding new modules.
 */
final class ProviderRegistry
{
    private static ?array $providers = null;

    /**
     * Get all registered provider class names.
     * Auto-discovers from modules/*/*Provider.php and includes core providers.
     */
    public static function providers(): array
    {
        if (self::$providers !== null) {
            return self::$providers;
        }

        $providers = [];

        // Auto-discover module providers
        $refactorDir = __DIR__ . '/../../../../..';
        $modulesDir = $refactorDir . '/modules';

        if (is_dir($modulesDir)) {
            foreach (glob($modulesDir . '/*/*Provider.php') ?: [] as $file) {
                $module_name = basename(dirname($file));
                $class_name = 'Modules\\' . $module_name . '\\' . basename($file, '.php');
                if (class_exists($class_name)) {
                    $providers[] = $class_name;
                }
            }
        }

        // Core providers (always included)
        $providers[] = \Webkernel\Http\CoreProvider::class;

        self::$providers = $providers;
        return $providers;
    }

    /**
     * Get the path to this file (used for staleness checking).
     */
    public static function file(): string
    {
        return __FILE__;
    }

    /**
     * Reset the cached provider list (useful for testing).
     */
    public static function reset(): void
    {
        self::$providers = null;
    }
}
