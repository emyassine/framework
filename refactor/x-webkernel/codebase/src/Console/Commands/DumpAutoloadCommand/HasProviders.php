<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands\DumpAutoloadCommand;

use Webkernel\Composables\ComposableContract;
use Webkernel\PlatformProvider;

trait HasProviders
{
    use _DumpAutoloadCommand;

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @param array<string, string> $classmap
     * @return list<array{class: class-string, prefix: string, path: string}>
     */
    private function providers_meta(array $packages, array $classmap): array
    {
        $out = [];
        foreach ($packages as $row) {
            $extra = $this->extra($row['package']);
            $provider = $extra['provider'] ?? null;
            if (! is_string($provider) || $provider === '') {
                continue;
            }
            $this->ensure_class($provider, $classmap);
            if (! class_exists($provider) || ! is_a($provider, PlatformProvider::class, true)) {
                continue;
            }
            $prefix = $extra['prefix'] ?? null;
            if (! is_string($prefix) || $prefix === '') {
                $name = $row['package']['name'] ?? 'app';
                $prefix = is_string($name) && str_contains($name, '/')
                    ? substr($name, strrpos($name, '/') + 1)
                    : (string) $name;
            }
            $out[] = [
                'class' => $provider,
                'prefix' => $prefix,
                'path' => $row['path'],
            ];
        }

        return $out;
    }

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     * @return array<string, list<string>>
     */
    private function collect_provider_paths(array $providers, string $constant): array
    {
        $out = [];
        foreach ($providers as $row) {
            foreach ($row['class']::declaration($constant) as $namespace => $path) {
                if (! is_string($namespace) || $namespace === '') {
                    continue;
                }
                $dirs = is_array($path) ? $path : [$path];
                foreach ($dirs as $dir) {
                    if (! is_string($dir) || $dir === '') {
                        continue;
                    }
                    $real = realpath($dir) ?: $dir;
                    if (! is_dir($real)) {
                        continue;
                    }
                    $norm = str_replace('\\', '/', $real);
                    if (! isset($out[$namespace]) || ! in_array($norm, $out[$namespace], true)) {
                        $out[$namespace][] = $norm;
                    }
                }
            }
        }

        return $out;
    }

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     * @return list<string>
     */
    private function collect_lang_paths(array $providers): array
    {
        $out = [];
        foreach ($providers as $row) {
            foreach ($row['class']::declaration('LANG_PATH') as $path) {
                $dirs = is_array($path) ? $path : [$path];
                foreach ($dirs as $dir) {
                    if (! is_string($dir) || $dir === '') {
                        continue;
                    }
                    $real = realpath($dir) ?: $dir;
                    if (! is_dir($real)) {
                        continue;
                    }
                    $norm = str_replace('\\', '/', $real);
                    if (! in_array($norm, $out, true)) {
                        $out[] = $norm;
                    }
                }
            }
        }

        return $out;
    }

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     * @return list<string>
     */
    private function collect_provider_files(array $providers, string $constant): array
    {
        $out = [];
        foreach ($providers as $row) {
            foreach ($row['class']::declaration($constant) as $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }
                $real = realpath($path) ?: $path;
                if (is_file($real) && ! in_array($real, $out, true)) {
                    $out[] = str_replace('\\', '/', $real);
                }
            }
        }

        return $out;
    }

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     * @return list<class-string>
     */
    private function collect_provider_classes(array $providers, string $constant): array
    {
        $out = [];
        foreach ($providers as $row) {
            foreach ($row['class']::declaration($constant) as $class) {
                if (is_string($class) && $class !== '' && ! in_array($class, $out, true)) {
                    $out[] = $class;
                }
            }
        }
        sort($out, SORT_STRING);

        return $out;
    }

    /**
     * @param array<string, string> $classmap
     */
    private function ensure_class(string $class, array $classmap): void
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return;
        }
        $file = $classmap[$class] ?? null;
        if (is_string($file) && is_file($file)) {
            require_once $file;
        }
        class_exists($class, true);
    }

    /**
     * @param array<string, string> $classmap
     * @return array<string, class-string<ComposableContract>>
     */
    private function composables_list(array $classmap): array
    {
        $contract_file = $classmap[ComposableContract::class] ?? null;
        if (is_string($contract_file) && is_file($contract_file)) {
            require_once $contract_file;
        }
        if (! interface_exists(ComposableContract::class, false)) {
            return [];
        }

        /** @var array<string, class-string<ComposableContract>> $map */
        $map = [];
        foreach ($classmap as $class => $file) {
            if (! is_string($file) || ! is_file($file)) {
                continue;
            }
            $src = file_get_contents($file);
            if ($src === false || ! str_contains($src, 'ComposableContract')) {
                continue;
            }
            require_once $file;
            if (! class_exists($class, false) || ! is_a($class, ComposableContract::class, true)) {
                continue;
            }
            $map[$class::api_name()] = $class;
        }
        ksort($map);

        return $map;
    }
}
