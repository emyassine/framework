<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

/**
 * Gathers package JS and writes public/webapp.js.
 *
 * Order: html-attributes (htmx first), colocated view JS, resources/js.
 */
final class Js
{
    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return void
     */
    public static function dump(array $providers): void
    {
        $files = self::files($providers);
        $dest = Assets::js_path();
        $dir = \dirname($dest);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            return;
        }
        $header = "/*\n"
            .GeneratedFileHeader::header()."\n"
            ."//>\n"
            ."//> Generated. Do not edit.\n"
            ."//> Package JS. Source: html-attributes, colocated view JS, resources/js.\n"
            ."*/\n\n";
        $body = [];
        foreach ($files as $file) {
            $js = \file_get_contents($file);
            if (! \is_string($js) || $js === '') {
                continue;
            }
            $body[] = \rtrim($js);
        }
        \file_put_contents($dest, $header.\implode("\n", $body)."\n", \LOCK_EX);
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
        $attrs = Css::attribute_files($providers, 'js');
        \usort($attrs, static function (string $a, string $b): int {
            $ah = \str_contains($a, '/htmx/') ? 0 : 1;
            $bh = \str_contains($b, '/htmx/') ? 0 : 1;

            return $ah <=> $bh ?: \strcmp($a, $b);
        });
        foreach ($attrs as $file) {
            self::push($out, $seen, $file);
        }
        foreach (Css::colocated($providers, 'js') as $file) {
            self::push($out, $seen, $file);
        }
        foreach (self::package_js($providers) as $file) {
            self::push($out, $seen, $file);
        }

        return $out;
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return list<string>
     */
    private static function package_js(array $providers): array
    {
        $out = [];
        foreach ($providers as $row) {
            $dir = \rtrim(\str_replace('\\', '/', (string) $row['path']), '/').'/resources/js';
            if (! \is_dir($dir)) {
                continue;
            }
            $files = \glob($dir.'/*.js');
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
