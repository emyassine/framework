<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

/**
 * Early-boot path helpers.
 */

if (! function_exists('resolve_filename')) {
    function resolve_filename(string $filename): string
    {
        $is_absolute = str_starts_with($filename, '/');
        $filename = preg_replace('#/+#', '/', $filename) ?? $filename;
        $parts = explode('/', $filename);
        $out = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($out);
                continue;
            }
            $out[] = $part;
        }

        $resolved = implode('/', $out);
        if ($is_absolute) {
            $resolved = '/'.$resolved;
        }

        return rtrim($resolved, '/');
    }
}
