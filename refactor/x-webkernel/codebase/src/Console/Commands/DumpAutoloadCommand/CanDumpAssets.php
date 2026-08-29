<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands\DumpAutoloadCommand;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait CanDumpAssets
{
    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return array<string, array{brand: string, format: string, hash: string, source: string}>
     */
    private function branding_dump(array $packages, string $root): array
    {
        $out = [];
        foreach ($packages as $row) {
            $dir = $row['path'].'/res/brands';
            if (! \is_dir($dir)) {
                continue;
            }
            $files = \glob($dir.'/*/*.brand.php');
            if (! \is_array($files)) {
                continue;
            }
            foreach ($files as $file) {
                if (! \is_string($file) || $file === '') {
                    continue;
                }
                $asset = require $file;
                if (! \is_array($asset) || ! isset($asset['key'], $asset['format'], $asset['data'])) {
                    continue;
                }
                $rel = $this->relative($root, $file) ?? $file;
                $out[(string) $asset['key']] = [
                    'brand' => \basename(\dirname($file)),
                    'format' => (string) $asset['format'],
                    'hash' => \md5((string) $asset['data']),
                    'source' => \str_replace('\\', '/', $rel),
                ];
            }
        }

        return $out;
    }

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     * @return array<string, string>
     */
    private function icons_dump(array $providers): array
    {
        /** @var array<string, true> $names */
        $names = [];
        foreach (['VIEWS', 'COMPONENTS'] as $constant) {
            foreach ($this->collect_provider_paths($providers, $constant) as $dirs) {
                foreach ($dirs as $dir) {
                    $this->collect_icon_names($names, $dir);
                }
            }
        }
        $out = [];
        foreach (\array_keys($names) as $key) {
            $slash = \strpos($key, '/');
            if ($slash === false) {
                continue;
            }
            $set = \substr($key, 0, $slash);
            $name = \substr($key, $slash + 1);
            $file = \class_exists(\Webkernel\Imagery\Icon::class, true)
                ? \Webkernel\Imagery\Icon::path($name, $set)
                : '';
            if ($file === '' || ! \is_file($file)) {
                continue;
            }
            $svg = \file_get_contents($file);
            if (\is_string($svg) && $svg !== '') {
                $out[$key] = $svg;
            }
        }
        \ksort($out);

        return $out;
    }

    /**
     * @param array<string, true> $names
     */
    private function collect_icon_names(array &$names, string $dir): void
    {
        $dir = \rtrim(\str_replace('\\', '/', $dir), '/');
        if (! \is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($it as $file) {
            if (! $file->isFile() || ! \str_ends_with($file->getFilename(), '.view.php')) {
                continue;
            }
            $src = \file_get_contents($file->getPathname());
            if (! \is_string($src) || $src === '') {
                continue;
            }
            if (\preg_match_all('/<x-webkernel::icon\b([^>]*)>/', $src, $tags) === false) {
                continue;
            }
            foreach ($tags[1] as $attrs) {
                if (! \is_string($attrs) || \preg_match('/(?:^|\s):name\b/', $attrs) === 1) {
                    continue;
                }
                $name = '';
                $set = 'lucide';
                if (\preg_match('/\bname="([^"]+)"/', $attrs, $m) === 1) {
                    $name = $m[1];
                }
                if (\preg_match('/\bset="([^"]+)"/', $attrs, $m) === 1) {
                    $set = $m[1];
                }
                if ($name !== '') {
                    $names[$set.'/'.$name] = true;
                }
            }
        }
    }
}
