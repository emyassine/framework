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
use Webkernel\Route\Compile\Cache;
use Webkernel\Route\Route;
use Webkernel\View\Compile\Mode;
use Webkernel\View\Engine;
use Webkernel\View\View;

trait CanCompileRuntime
{
    use _DumpAutoloadCommand;

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     * @param $root string
     * @param $vendor_dir string
     *
     * @return void
     */
    private function compile_views(array $providers, string $root, string $vendor_dir): void
    {
        $dir = $root.'/platform/storage/framework/views';
        if (\is_dir($dir)) {
            $compiled = \glob($dir.'/*.compiled');
            if (\is_array($compiled)) {
                foreach ($compiled as $file) {
                    if (\is_string($file) && \is_file($file)) {
                        @\unlink($file);
                    }
                }
            }
        }
        View::flush();
        $engine = View::engine(Mode::Auto);
        $map = [];
        foreach (['VIEWS', 'COMPONENTS'] as $constant) {
            $components = $constant === 'COMPONENTS';
            foreach ($this->collect_provider_paths($providers, $constant) as $namespace => $dirs) {
                foreach ($dirs as $base) {
                    if ($components) {
                        $engine->add_component_namespace($namespace, $base);
                    } else {
                        $engine->add_view_namespace($namespace, $base);
                    }
                    $this->compile_tree($engine, $base, $namespace, $map);
                }
            }
        }
        \ksort($map);
        $this->write_classmap(
            $vendor_dir.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.self::COMPILED_VIEWS_BASENAME,
            $map,
            $vendor_dir,
            $root,
        );
    }

    /**
     * @param $engine Engine
     * @param $base string
     * @param $namespace string
     * @param $map array<string, string>
     *
     * @return void
     */
    private function compile_tree(Engine $engine, string $base, string $namespace, array &$map): void
    {
        $base = \rtrim(\str_replace('\\', '/', $base), '/');
        if (! \is_dir($base)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($it as $file) {
            if (! $file->isFile() || ! \str_ends_with($file->getFilename(), '.view.php')) {
                continue;
            }
            $rel = \substr(\str_replace('\\', '/', $file->getPathname()), \strlen($base) + 1);
            if (! \is_string($rel) || ! \str_ends_with($rel, '.view.php')) {
                continue;
            }
            $name = \str_replace('/', '.', \substr($rel, 0, -9));
            if (\str_ends_with($name, '.index')) {
                $name = \substr($name, 0, -6);
            }
            $view = $namespace.'::'.$name;
            try {
                $engine->compile($view);
                $compiled = $engine->compiled_path($view);
                if ($compiled !== '') {
                    $map[$view] = $compiled;
                }
            } catch (\Throwable $e) {
                $this->terminal()->warning('view compile '.$view.': '.$e->getMessage());
            }
        }
    }

    /**
     * @param string $composer_dir
     *
     * @return void
     */
    private function rebuild_compiled_routes(string $composer_dir): void
    {
        Route::flush();
        Route::register_dumped_panel_routes($composer_dir.DIRECTORY_SEPARATOR.self::PANEL_ROUTES_BASENAME);
        $data = Route::compile_for_cache('');
        Cache::write(Cache::path(), $data, [
            'compiled_at' => \time(),
            'host' => '',
            'files' => Cache::fingerprints(),
        ]);
        Route::flush();
    }

    private function strip_dev_autoload_files(string $composer_dir): void
    {
        $deny = ['/phpunit/phpunit/', '/myclabs/deep-copy/'];
        $files_php = $composer_dir.'/autoload_files.php';
        if (\is_file($files_php)) {
            $src = \file_get_contents($files_php);
            if (\is_string($src) && $src !== '') {
                \file_put_contents($files_php, $this->filter_denied_lines($src, $deny), \LOCK_EX);
            }
        }
        $static_php = $composer_dir.'/autoload_static.php';
        if (! \is_file($static_php)) {
            return;
        }
        $src = \file_get_contents($static_php);
        if (! \is_string($src) || $src === '') {
            return;
        }
        $replaced = \preg_replace_callback(
            '/public static \$files = array \((.*?)\n    \);/s',
            function (array $m) use ($deny): string {
                return 'public static $files = array ('.$this->filter_denied_lines($m[1], $deny)."\n    );";
            },
            $src,
        );
        if (\is_string($replaced)) {
            \file_put_contents($static_php, $replaced, \LOCK_EX);
        }
    }

    /**
     * @param list<string> $deny
     */
    private function filter_denied_lines(string $src, array $deny): string
    {
        $kept = [];
        foreach (\explode("\n", $src) as $line) {
            $drop = false;
            foreach ($deny as $needle) {
                if (\str_contains($line, $needle)) {
                    $drop = true;
                    break;
                }
            }
            if (! $drop) {
                $kept[] = $line;
            }
        }

        return \implode("\n", $kept);
    }
}
