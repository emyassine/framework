<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Modules;

use Webkernel\Paths\Composer;

/**
 * Path + stamp for the modules scan cache (modules.json under platform dir).
 */
final class ModuleCache
{
    /**
     * Absolute path to modules.json; ensures the parent directory exists.
     */
    public static function path(): string
    {
        $dir = webkernel_platform_dir();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . DIRECTORY_SEPARATOR . 'modules.json';
    }

    /**
     * mtime of composer/installed.php (0 if missing).
     */
    public static function installed_stamp(): int
    {
        try {
            $file = Composer::vendor_dir() . '/composer/installed.php';
        } catch (\Throwable) {
            return 0;
        }

        return is_file($file) ? (int) filemtime($file) : 0;
    }
}
