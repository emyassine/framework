<?php declare(strict_types=1);

namespace Webkernel\Provider;

/**
 * Discovers module providers and always includes core providers.
 */
final class ProviderRegistry
{
    /** @var list<class-string>|null */
    private static ?array $providers = null;

    /**
     * @return list<class-string>
     */
    public static function providers(): array
    {
        if (self::$providers !== null) {
            return self::$providers;
        }

        $providers = [
            \Webkernel\Http\CoreProvider::class,
        ];

        $modules_dir = function_exists('webapp_path')
            ? webapp_path('modules')
            : dirname(__DIR__, 4).'/modules';

        if (is_dir($modules_dir)) {
            foreach (glob($modules_dir.'/*/*Provider.php') ?: [] as $file) {
                $module_name = basename(dirname($file));
                $class_name = 'Modules\\'.$module_name.'\\'.basename($file, '.php');
                if (class_exists($class_name)) {
                    $providers[] = $class_name;
                }
            }
        }

        self::$providers = $providers;

        return self::$providers;
    }

    public static function file(): string
    {
        return __FILE__;
    }

    public static function reset(): void
    {
        self::$providers = null;
    }
}
