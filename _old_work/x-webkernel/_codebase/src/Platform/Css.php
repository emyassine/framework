<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webkernel\Platform\Colors\Color;

/**
 * Gathers package CSS and writes public/webapp.css.
 *
 * Order: palettes, resources/css, html-attributes, colocated view CSS.
 */
final class Css
{
    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return void
     */
    public static function dump(array $providers): void
    {
        $parts = self::parts($providers);
        $dest = Assets::css_path();
        $dir = \dirname($dest);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            return;
        }
        $header = "/*\n"
            .GeneratedFileHeader::header()."\n"
            ."//>\n"
            ."//> Generated. Do not edit.\n"
            ."//> Palettes plus package CSS. Source: resources/css, html-attributes, colocated view CSS.\n"
            ."*/\n\n";
        $body = [];
        foreach ($parts as $part) {
            $min = self::minify($part);
            if ($min !== '') {
                $body[] = $min;
            }
        }
        \file_put_contents($dest, $header.\implode('', $body)."\n", \LOCK_EX);
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return list<string>
     */
    public static function parts(array $providers): array
    {
        $parts = [
            ':root{'.Color::root_css().'}',
            '[data-w-theme="dark"]{'.Color::dark_root_css().'}',
        ];
        foreach (self::files($providers) as $file) {
            $css = \file_get_contents($file);
            if (\is_string($css) && $css !== '') {
                $parts[] = $css;
            }
        }

        return $parts;
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return list<string>
     */
    public static function files(array $providers): array
    {
        /** @var array<string, true> $seen */
        $seen = [];
        $out = [];
        foreach (self::package_css($providers) as $file) {
            self::push($out, $seen, $file);
        }
        foreach (self::attribute_files($providers, 'css') as $file) {
            self::push($out, $seen, $file);
        }
        foreach (self::colocated($providers, 'css') as $file) {
            self::push($out, $seen, $file);
        }

        return $out;
    }

    /**
     * @param $css string
     *
     * @return string
     */
    public static function minify(string $css): string
    {
        $css = \preg_replace('/\/\*.*?\*\//s', '', $css) ?? $css;
        $css = \preg_replace('/\s+/', ' ', $css) ?? $css;
        $css = \str_replace([' {', '{ ', ' }', '} ', '; '], ['{', '{', '}', '}', ';'], $css);
        $css = \preg_replace('/\s*,\s*/', ',', $css) ?? $css;

        return \trim($css);
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return list<string>
     */
    private static function package_css(array $providers): array
    {
        $out = [];
        foreach ($providers as $row) {
            $dir = \rtrim(\str_replace('\\', '/', (string) $row['path']), '/').'/resources/css';
            $out = [...$out, ...self::ordered_dir($dir)];
        }

        return $out;
    }

    /**
     * @param $dir string
     *
     * @return list<string>
     */
    private static function ordered_dir(string $dir): array
    {
        if (! \is_dir($dir)) {
            return [];
        }
        /** @var array<string, string> $named */
        $named = [];
        $files = \glob($dir.'/*.css');
        if (\is_array($files)) {
            foreach ($files as $file) {
                if (! \is_string($file) || $file === '') {
                    continue;
                }
                $base = \basename($file);
                if ($base === 'wts.css') {
                    continue;
                }
                $named[$base] = \str_replace('\\', '/', $file);
            }
        }
        $out = [];
        foreach (['tokens.css', 'reset.css'] as $first) {
            if (isset($named[$first])) {
                $out[] = $named[$first];
                unset($named[$first]);
            }
        }
        \ksort($named);
        $out = [...$out, ...\array_values($named)];
        $util = $dir.'/utilities';
        if (\is_dir($util)) {
            $utils = \glob($util.'/*.css');
            if (\is_array($utils)) {
                \sort($utils);
                foreach (['tokens.css', 'elevate.css', 'hover.css', 'appear.css', 'media.css', 'group.css', 'focus.css', 'reduced-motion.css', 'grid.css'] as $prefer) {
                    foreach ($utils as $i => $file) {
                        if (\is_string($file) && \basename($file) === $prefer) {
                            $out[] = \str_replace('\\', '/', $file);
                            unset($utils[$i]);
                        }
                    }
                }
                foreach ($utils as $file) {
                    if (\is_string($file) && $file !== '') {
                        $out[] = \str_replace('\\', '/', $file);
                    }
                }
            }
        }

        return $out;
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     * @param $ext string
     *
     * @return list<string>
     */
    public static function attribute_files(array $providers, string $ext): array
    {
        $out = [];
        foreach ($providers as $row) {
            $dir = \rtrim(\str_replace('\\', '/', (string) $row['path']), '/').'/resources/html-attributes';
            if (! \is_dir($dir)) {
                continue;
            }
            $files = \glob($dir.'/*/*.'.$ext);
            if (! \is_array($files)) {
                continue;
            }
            \sort($files);
            foreach ($files as $file) {
                if (\is_string($file) && $file !== '') {
                    $out[] = \str_replace('\\', '/', $file);
                }
            }
        }

        return $out;
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     * @param $ext string
     *
     * @return list<string>
     */
    public static function colocated(array $providers, string $ext): array
    {
        $out = [];
        foreach ($providers as $row) {
            $class = $row['class'];
            if (! \is_string($class) || ! \class_exists($class)) {
                continue;
            }
            foreach (['VIEWS', 'COMPONENTS'] as $constant) {
                foreach ($class::declaration($constant) as $path) {
                    $dirs = \is_array($path) ? $path : [$path];
                    foreach ($dirs as $dir) {
                        if (! \is_string($dir) || $dir === '' || ! \is_dir($dir)) {
                            continue;
                        }
                        $out = [...$out, ...self::walk_ext($dir, $ext)];
                    }
                }
            }
        }

        return $out;
    }

    /**
     * @param $dir string
     * @param $ext string
     *
     * @return list<string>
     */
    private static function walk_ext(string $dir, string $ext): array
    {
        $dir = \rtrim(\str_replace('\\', '/', $dir), '/');
        $out = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($it as $file) {
            if (! $file->isFile() || ! \str_ends_with($file->getFilename(), '.'.$ext)) {
                continue;
            }
            $path = \str_replace('\\', '/', $file->getPathname());
            if (\str_contains($path, '/resources/css/') || \str_contains($path, '/html-attributes/')) {
                continue;
            }
            $out[] = $path;
        }
        \sort($out);

        return $out;
    }

    /**
     * @param list<string> $out
     * @param array<string, true> $seen
     * @param $file string
     *
     * @return void
     */
    private static function push(array &$out, array &$seen, string $file): void
    {
        $real = \realpath($file) ?: $file;
        $real = \str_replace('\\', '/', $real);
        if (isset($seen[$real])) {
            return;
        }
        $seen[$real] = true;
        $out[] = $real;
    }
}
