<?php declare(strict_types=1);

namespace Webkernel\Paths;

/**
 * Host Composer paths. Thin wrapper over webapp_path() / vendor_dir().
 */
final class Composer
{
    private function __construct()
    {
    }

    public static function root(): string
    {
        return webapp_path();
    }

    public static function vendor_dir(): string
    {
        return vendor_dir();
    }

    public static function flush(): void
    {
        webkernel_boot_flush();
    }
}
