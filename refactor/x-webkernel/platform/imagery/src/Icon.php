<?php declare(strict_types=1);

namespace Webkernel\Imagery;

/**
 * SVG icons from res/icons/{set}/{name}.svg
 */
final class Icon
{
    /**
     * @return list<string>
     */
    public static function sets(): array
    {
        return ['lucide', 'simple-icons', 'custom'];
    }

    public static function package_root(): string
    {
        $root = dirname(__DIR__);

        return realpath($root) ?: $root;
    }

    public static function path(string $name, string $set = 'lucide'): string
    {
        $name = str_replace(['\\', '..'], '', $name);
        $set = str_replace(['\\', '..'], '', $set);

        return self::package_root().'/res/icons/'.$set.'/'.$name.'.svg';
    }

    public static function svg(string $name, string $set = 'lucide'): string
    {
        $file = self::path($name, $set);
        if (is_file($file)) {
            $svg = file_get_contents($file);

            return is_string($svg) ? $svg : '';
        }
        foreach (self::sets() as $fallback) {
            if ($fallback === $set) {
                continue;
            }
            $file = self::path($name, $fallback);
            if (is_file($file)) {
                $svg = file_get_contents($file);

                return is_string($svg) ? $svg : '';
            }
        }

        return '';
    }
}
