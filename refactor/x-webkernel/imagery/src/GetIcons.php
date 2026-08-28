<?php

declare(strict_types=1);

namespace Webkernel\Imagery;

/**
 * Icon collection directories for blade-icons and webkernel_grab_icon().
 */
final class GetIcons
{
    /**
     * Absolute package root (…/imagery).
     */
    public static function package_root(): string
    {
        $root = dirname(__DIR__);

        return realpath($root) ?: $root;
    }

    /**
     * Icon directories relative to the webapp root (blade-icons contract).
     *
     * @return list<string>
     */
    public static function paths(): array
    {
        $package_root = self::package_root();
        $webapp_root = self::webapp_root($package_root);
        $subs = [
            'res/icons/custom',
            'res/icons/lucide',
            'res/icons/simple-icons',
        ];

        $out = [];
        foreach ($subs as $sub) {
            $abs = $package_root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $sub);
            if (! is_dir($abs)) {
                continue;
            }

            if ($webapp_root !== null && str_starts_with($abs, $webapp_root)) {
                $rel = substr($abs, strlen($webapp_root));
                $out[] = ltrim(str_replace('\\', '/', $rel), '/');
            } else {
                $out[] = $abs;
            }
        }

        return $out;
    }

    /**
     * Paths with optional extras (deduped, order preserved).
     *
     * @param  list<string>  $extra
     * @return list<string>
     */
    public static function array(array $extra = []): array
    {
        $all = array_merge(self::paths(), $extra);
        $seen = [];
        $result = [];
        foreach ($all as $p) {
            $norm = rtrim(str_replace('\\', '/', (string) $p), '/');
            if ($norm !== '' && ! isset($seen[$norm])) {
                $seen[$norm] = true;
                $result[] = $norm;
            }
        }

        return $result;
    }

    private static function webapp_root(string $from): ?string
    {
        $dir = $from;
        for ($i = 0; $i < 10; $i++) {
            if (is_file($dir.DIRECTORY_SEPARATOR.'artisan')
                && is_file($dir.DIRECTORY_SEPARATOR.'composer.json')
            ) {
                return realpath($dir) ?: $dir;
            }
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        return null;
    }
}
