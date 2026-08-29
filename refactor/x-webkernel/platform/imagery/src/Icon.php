<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Imagery;

/**
 * SVG icons from res/icons/{set}/{name}.svg
 */
final class Icon
{
    /** @var array<string, string>|null */
    private static ?array $dumped = null;

    /** @var array<string, string> */
    private static array $loaded = [];

    /**
     * @return list<string>
     */
    public static function sets(): array
    {
        return ['lucide', 'simple-icons', 'custom'];
    }

    public static function package_root(): string
    {
        $root = \dirname(__DIR__);

        return \realpath($root) ?: $root;
    }

    public static function path(string $name, string $set = 'lucide'): string
    {
        $name = \str_replace(['\\', '..'], '', $name);
        $set = \str_replace(['\\', '..'], '', $set);

        return self::package_root().'/res/icons/'.$set.'/'.$name.'.svg';
    }

    public static function svg(string $name, string $set = 'lucide'): string
    {
        $key = $set.'/'.$name;
        if (isset(self::$loaded[$key])) {
            return self::$loaded[$key];
        }
        $dumped = self::dumped();
        if (isset($dumped[$key]) && \is_string($dumped[$key]) && $dumped[$key] !== '') {
            return self::$loaded[$key] = $dumped[$key];
        }
        $file = self::path($name, $set);
        if (\is_file($file)) {
            $svg = \file_get_contents($file);

            return self::$loaded[$key] = \is_string($svg) ? $svg : '';
        }
        foreach (self::sets() as $fallback) {
            if ($fallback === $set) {
                continue;
            }
            $file = self::path($name, $fallback);
            if (\is_file($file)) {
                $svg = \file_get_contents($file);

                return self::$loaded[$key] = \is_string($svg) ? $svg : '';
            }
        }

        return self::$loaded[$key] = '';
    }

    public static function flush(): void
    {
        self::$dumped = null;
        self::$loaded = [];
    }

    /**
     * @return array<string, string>
     */
    private static function dumped(): array
    {
        if (self::$dumped !== null) {
            return self::$dumped;
        }
        $file = \function_exists('vendor_dir') ? \vendor_dir('composer/webkernel_icons.php') : '';
        if ($file === '' || ! \is_file($file)) {
            return self::$dumped = [];
        }
        $data = require $file;

        return self::$dumped = \is_array($data) ? $data : [];
    }
}
