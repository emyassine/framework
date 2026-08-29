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

trait HasPackages
{
    /**
     * @return list<array{path: string, package: array<string, mixed>}>
     */
    private function packages(string $vendor_dir): array
    {
        $file = $vendor_dir.'/composer/installed.json';
        $raw = is_file($file) ? file_get_contents($file) : false;
        $data = is_string($raw) ? json_decode($raw, true) : null;
        if (! is_array($data)) {
            return [];
        }
        $list = $data['packages'] ?? (array_is_list($data) ? $data : []);
        $composer_dir = $vendor_dir.'/composer';
        $out = [];
        foreach ($list as $package) {
            if (! is_array($package) || ! $this->is_webkernel_package($package)) {
                continue;
            }
            $rel = $package['install-path'] ?? null;
            $name = $package['name'] ?? '';
            $candidates = [];
            if (is_string($rel) && $rel !== '') {
                $candidates[] = $composer_dir.'/'.$rel;
            }
            if (is_string($name) && $name !== '') {
                $candidates[] = $vendor_dir.'/'.$name;
            }
            $install_path = null;
            foreach ($candidates as $candidate) {
                $real = realpath($candidate);
                if ($real !== false && is_dir($real)) {
                    $install_path = $real;
                    break;
                }
            }
            if ($install_path === null) {
                continue;
            }
            $out[] = ['path' => str_replace('\\', '/', $install_path), 'package' => $package];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $package
     */
    private function is_webkernel_package(array $package): bool
    {
        $type = $package['type'] ?? '';
        $name = $package['name'] ?? '';
        if (is_string($type) && str_starts_with($type, 'webkernel-')) {
            return true;
        }
        if (isset($package['extra']['webkernel']) && is_array($package['extra']['webkernel'])) {
            return true;
        }

        return is_string($name) && str_starts_with($name, 'webkernel/');
    }

    /**
     * @param array<string, mixed> $package
     * @return array<string, mixed>
     */
    private function extra(array $package): array
    {
        $extra = $package['extra']['webkernel'] ?? null;

        return is_array($extra) ? $extra : [];
    }

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return array<string, string>
     */
    private function classmap(array $packages): array
    {
        $map = [];
        foreach ($packages as $row) {
            $psr4 = $row['package']['autoload']['psr-4'] ?? [];
            if (! is_array($psr4)) {
                continue;
            }
            foreach ($psr4 as $namespace => $dirs) {
                foreach ((array) $dirs as $dir) {
                    $base = rtrim($row['path'], '/\\').DIRECTORY_SEPARATOR.str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $dir);
                    $base = rtrim($base, '/\\');
                    if (! is_dir($base)) {
                        continue;
                    }
                    $this->scan_psr4($map, (string) $namespace, $base);
                }
            }
        }
        ksort($map);

        return $map;
    }

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return list<string>
     */
    private function files_list(array $packages): array
    {
        /** @var array<string, true> $paths */
        $paths = [];
        foreach ($packages as $row) {
            $this->collect_function_files($paths, $row['path']);
        }
        $list = array_keys($paths);
        sort($list, SORT_STRING);

        return $list;
    }

    /**
     * @param array<string, true> $paths
     */
    private function collect_function_files(array &$paths, string $dir): void
    {
        foreach ($this->glob_paths($dir.'/functions/*.php') as $file) {
            if (is_file($file)) {
                $paths[str_replace('\\', '/', $file)] = true;
            }
        }
    }

    /**
     * @return list<string>
     */
    private function glob_paths(string $pattern): array
    {
        $found = glob($pattern);
        if (! is_array($found)) {
            return [];
        }
        $out = [];
        foreach ($found as $path) {
            if (is_string($path) && $path !== '') {
                $out[] = $path;
            }
        }

        return $out;
    }

    /**
     * @param array<string, string> $map
     */
    private function scan_psr4(array &$map, string $namespace, string $base): void
    {
        $prefix_len = strlen($base) + 1;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($it as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $rel = substr($file->getPathname(), $prefix_len);
            if ($rel === false) {
                continue;
            }
            $rel = str_replace('\\', '/', $rel);
            $base_name = basename($rel);
            if (
                str_contains($rel, '/functions/')
                || str_starts_with($rel, 'functions/')
                || $base_name === 'check.php'
                || $base_name === 'router.php'
                || $base_name === 'config.php'
                || str_starts_with($base_name, 'load.')
                || str_starts_with($base_name, '_')
            ) {
                continue;
            }
            $src = file_get_contents($file->getPathname());
            if (! is_string($src) || preg_match('/\b(?:class|interface|enum|trait)\s+/', $src) !== 1) {
                continue;
            }
            $class = $namespace.str_replace('/', '\\', substr($rel, 0, -4));
            $map[$class] = str_replace('\\', '/', $file->getPathname());
        }
    }
}
