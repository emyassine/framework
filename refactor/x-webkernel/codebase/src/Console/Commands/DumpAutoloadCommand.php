<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Webkernel\Composables\ComposableContract;
use Webkernel\Config\ConfigWriter;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\{ExitCode, Terminal};

use Webkernel\DevEnv\IdeHelper;
use Webkernel\Instance\InstanceId;

use Webkernel\PlatformProvider;

use Webkernel\Route\Compile\Cache;
use Webkernel\Route\Route;

use Webkernel\View\{Engine,View};

/**
 * Writes `{vendor}/composer/webkernel_*.php`. Composer `post-autoload-dump`
 * invokes this command; so does `php webkernel dump-autoload`.
 */
final readonly class DumpAutoloadCommand
{
    private const PANEL_CLASS = 'Webkernel\\Platform\\Panel';

    private const PANEL_PROVIDER_CLASS = 'Webkernel\\Platform\\PanelProvider';

    private const RESOURCE_CLASS = 'Webkernel\\Platform\\Resources\\Resource';

    public const BOOT_BASENAME = 'webkernel.php';
    public const CLASSMAP_BASENAME = 'webkernel_classmap.php';
    public const FILES_BASENAME = 'webkernel_files.php';
    public const VIEWS_BASENAME = 'webkernel_views.php';
    public const COMPONENTS_BASENAME = 'webkernel_components.php';
    public const ROUTES_BASENAME = 'webkernel_routes.php';
    public const COMPOSABLES_BASENAME = 'webkernel_composables.php';
    public const PROVIDERS_BASENAME = 'webkernel_providers.php';
    public const COMMANDS_BASENAME = 'webkernel_commands.php';
    public const PANELS_BASENAME = 'webkernel_panels.php';
    public const PANEL_ROUTES_BASENAME = 'webkernel_panel_routes.php';
    public const BRANDING_BASENAME = 'webkernel_branding.php';
    public const ICONS_BASENAME = 'webkernel_icons.php';

    #[ConsoleCommand(
        name: 'dump-autoload',
        description: 'Write Webkernel dump files (classmap, providers, views, routes)',
    )]
    public function __invoke(): ExitCode
    {
        $this->ensure_path_helpers();
        $root = $this->project_root();
        $vendor_dir = $this->vendor_dir($root);
        $composer_dir = $vendor_dir.DIRECTORY_SEPARATOR.'composer';

        if (! is_dir($composer_dir) && ! mkdir($composer_dir, 0775, true) && ! is_dir($composer_dir)) {
            $this->terminal()->warning('cannot create '.$composer_dir);

            return ExitCode::ERROR;
        }

        $instance_id = InstanceId::record($root);
        $vendor_rel = $this->relative($root, $vendor_dir) ?? basename($vendor_dir);
        $this->stamp_platform_config($root, str_replace('\\', '/', $vendor_rel), $instance_id);
        $packages = $this->packages($vendor_dir);

        $boot = [
            'instance_id' => $instance_id,
            'webapp_root' => $root,
            'vendor_dir' => $vendor_dir,
            'vendor_rel' => str_replace('\\', '/', $vendor_rel),
            'generated_at' => gmdate('c'),
        ];

        $classmap = $this->classmap($packages);
        $providers = $this->providers_meta($packages, $classmap);
        $composables = $this->composables_list($classmap);

        $this->write_php($composer_dir.DIRECTORY_SEPARATOR.self::BOOT_BASENAME, $boot);
        $this->write_classmap(
            $composer_dir.DIRECTORY_SEPARATOR.self::CLASSMAP_BASENAME,
            $classmap,
            $vendor_dir,
            $root,
        );
        $this->write_files(
            $composer_dir.DIRECTORY_SEPARATOR.self::FILES_BASENAME,
            $this->files_list($packages),
            $vendor_dir,
            $root,
        );
        $this->write_namespaced_paths(
            $composer_dir.DIRECTORY_SEPARATOR.self::VIEWS_BASENAME,
            $this->collect_provider_paths($providers, 'VIEWS'),
            $vendor_dir,
            $root,
        );
        $this->write_namespaced_paths(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMPONENTS_BASENAME,
            $this->collect_provider_paths($providers, 'COMPONENTS'),
            $vendor_dir,
            $root,
        );
        $this->write_path_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::ROUTES_BASENAME,
            $this->collect_provider_files($providers, 'ROUTES'),
            $vendor_dir,
            $root,
        );
        $this->write_composables(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMPOSABLES_BASENAME,
            $composables,
        );
        $this->write_webapp_ide($composables);
        $this->write_class_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::PROVIDERS_BASENAME,
            array_column($providers, 'class'),
        );
        $this->write_class_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMMANDS_BASENAME,
            $this->collect_provider_classes($providers, 'COMMANDS'),
        );
        $panels = $this->panels_dump($providers, $classmap, $root);
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::PANELS_BASENAME,
            $panels,
        );
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::PANEL_ROUTES_BASENAME,
            $this->panel_routes_dump($panels, $classmap),
        );
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::BRANDING_BASENAME,
            $this->branding_dump($packages, $root),
        );
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::ICONS_BASENAME,
            $this->icons_dump($providers),
        );
        $this->strip_dev_autoload_files($composer_dir);
        $this->ensure_path_helpers();
        $this->rebuild_compiled_routes($composer_dir);
        $this->compile_views($providers, $root);

        $io = $this->terminal();
        $io->success('wrote composer/'.self::BOOT_BASENAME.' (instance '.$instance_id.')');

        try {
            $ide = IdeHelper::generate($vendor_dir);
            $io->info(sprintf(
                'ide helper %s (%d classes, %d bytes%s)',
                $ide['path'],
                $ide['classes'],
                $ide['bytes'],
                $ide['skipped'] ? ', unchanged' : '',
            ));
        } catch (\Throwable $e) {
            $io->warning('ide helper: '.$e->getMessage());
        }

        return ExitCode::SUCCESS;
    }

    private function project_root(): string
    {
        $dir = getcwd() ?: '';
        $real = realpath($dir);
        if ($real !== false) {
            $dir = $real;
        }
        while ($dir !== '' && $dir !== '/') {
            if (is_file($dir.DIRECTORY_SEPARATOR.'composer.json')) {
                return $dir;
            }
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        throw new \RuntimeException('Cannot resolve project root.');
    }

    private function stamp_platform_config(string $root, string $vendor_rel, string $instance_id): void
    {
        $config_path = $root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'platform.php';
        if (! is_file($config_path)) {
            return;
        }
        $current = require $config_path;
        if (! is_array($current)) {
            $current = [];
        }
        $parts = InstanceId::parts($root);
        $writes = [
            'hostname' => $parts['host'],
            'ip' => $parts['ip'],
            'uuid' => $parts['machine_uuid'],
            'macs' => $parts['macs'],
            'instance_file_path' => 'platform/storage/instance',
            'autoload' => $vendor_rel.'/autoload.php',
        ];
        $id = $current['id'] ?? null;
        if (! is_string($id) || $id === '') {
            $writes['id'] = $instance_id;
        }
        $created = $current['created'] ?? null;
        if (! is_string($created) || $created === '') {
            $writes['created'] = gmdate('c');
        }
        ConfigWriter::atomic_rewrite($config_path, $writes);
    }

    private function vendor_dir(string $root): string
    {
        $raw = file_get_contents($root.DIRECTORY_SEPARATOR.'composer.json');
        $json = is_string($raw) ? json_decode($raw, true) : null;
        $rel = is_array($json) ? ($json['config']['vendor-dir'] ?? 'vendor') : 'vendor';

        return rtrim($root.DIRECTORY_SEPARATOR.str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $rel), DIRECTORY_SEPARATOR);
    }

    private function relative(string $from, string $to): ?string
    {
        $from = rtrim(str_replace('\\', '/', $from), '/');
        $to = str_replace('\\', '/', $to);
        if (str_starts_with($to, $from.'/')) {
            return substr($to, strlen($from) + 1);
        }

        return null;
    }

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
     * @param array<string, string> $classmap
     * @return list<array<string, mixed>>
     */
    private function panels_dump(array $providers, array $classmap, string $root): array
    {
        if (class_exists(\Webkernel\Config\Config::class, true)) {
            \Webkernel\Config\Config::boot($root);
        }
        $out = [];
        foreach ($providers as $row) {
            $provider = $row['class'];
            foreach ($provider::declaration('PANELS') as $panel_class) {
                if (! is_string($panel_class) || $panel_class === '') {
                    continue;
                }
                $this->ensure_class($panel_class, $classmap);
                if (
                    ! class_exists($panel_class)
                    || ! class_exists(self::PANEL_CLASS)
                    || ! class_exists(self::PANEL_PROVIDER_CLASS)
                    || ! is_a($panel_class, self::PANEL_PROVIDER_CLASS, true)
                ) {
                    continue;
                }
                $instance = new $panel_class();
                $panel = self::PANEL_CLASS;
                $snapshot = $instance->panel(new $panel())->to_array();
                $snapshot['provider'] = $panel_class;
                $snapshot['package_provider'] = $provider;
                $snapshot['prefix'] = $row['prefix'];
                $out[] = $snapshot;
            }
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $panels
     * @param array<string, string> $classmap
     * @return list<array{0: list<string>, 1: string, 2: class-string}>
     */
    private function panel_routes_dump(array $panels, array $classmap): array
    {
        $out = [];
        foreach ($panels as $panel) {
            $base = \trim((string) ($panel['path'] ?? ''), '/');
            $prefix = $base === '' ? '' : '/'.$base;
            foreach ($panel['pages'] ?? [] as $page) {
                if (! \is_string($page) || $page === '') {
                    continue;
                }
                $out[] = [['GET', 'HEAD'], $prefix === '' ? '/' : $prefix, $page];
            }
            foreach ($panel['resources'] ?? [] as $resource) {
                if (! \is_string($resource) || $resource === '') {
                    continue;
                }
                $this->ensure_class($resource, $classmap);
                if (! \class_exists($resource) || ! \is_a($resource, self::RESOURCE_CLASS, true)) {
                    continue;
                }
                $slug = $resource::$slug !== '' ? $resource::$slug : $this->resource_slug($resource);
                foreach ($resource::pages() as $def) {
                    $path = '/';
                    $class = $def;
                    $methods = ['GET', 'HEAD'];
                    if (\is_array($def)) {
                        $path = (string) ($def['path'] ?? '/');
                        $class = (string) ($def['class'] ?? '');
                        $methods = \is_array($def['methods'] ?? null) ? $def['methods'] : ['GET', 'HEAD'];
                    }
                    if ($class === '') {
                        continue;
                    }
                    $uri = $prefix.'/'.$slug.$path;
                    $uri = '/'.\trim(\str_replace('//', '/', $uri), '/');
                    if ($uri === '') {
                        $uri = '/';
                    }
                    /** @var list<string> $methods */
                    $out[] = [\array_values(\array_map(\strval(...), $methods)), $uri, $class];
                }
            }
        }

        return $out;
    }

    /**
     * @param class-string $resource
     */
    private function resource_slug(string $resource): string
    {
        $short = (new \ReflectionClass($resource))->getShortName();
        if (\str_ends_with($short, 'Resource')) {
            $short = \substr($short, 0, -8);
        }
        $kebab = \preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $short);

        return \strtolower(\is_string($kebab) ? $kebab : $short);
    }

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

    /**
     * Composer post-autoload-dump runs inside the phar ClassLoader, so
     * namespacer never required functions/paths.php. Load it from this package.
     *
     * @return void
     */
    private function ensure_path_helpers(): void
    {
        $file = \dirname(__DIR__, 3).'/functions/paths.php';
        if (\is_file($file)) {
            require_once $file;
        }
        if (\function_exists('webkernel_boot_flush')) {
            \webkernel_boot_flush();
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

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     */
    private function compile_views(array $providers, string $root): void
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
        $engine = View::engine();
        foreach (['VIEWS', 'COMPONENTS'] as $constant) {
            $components = $constant === 'COMPONENTS';
            foreach ($this->collect_provider_paths($providers, $constant) as $namespace => $dirs) {
                foreach ($dirs as $base) {
                    if ($components) {
                        $engine->add_component_namespace($namespace, $base);
                    } else {
                        $engine->add_view_namespace($namespace, $base);
                    }
                    $this->compile_tree($engine, $base, $namespace);
                }
            }
        }
    }

    private function compile_tree(Engine $engine, string $base, string $namespace): void
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
            } catch (\Throwable $e) {
                $this->terminal()->warning('view compile '.$view.': '.$e->getMessage());
            }
        }
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

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     * @return array<string, list<string>>
     */
    private function collect_provider_paths(array $providers, string $constant): array
    {
        $out = [];
        foreach ($providers as $row) {
            foreach ($row['class']::declaration($constant) as $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }
                $real = realpath($path) ?: $path;
                if (! is_dir($real)) {
                    continue;
                }
                $out[$row['prefix']][] = str_replace('\\', '/', $real);
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

    private function dump_path_prefix(string $vendor_dir, string $root): string
    {
        $vendor_rel = str_replace('\\', '/', $this->relative($root, $vendor_dir) ?? basename($vendor_dir));
        $up = substr_count($vendor_rel, '/') + 1;

        return '$v = dirname(__DIR__); // vendor_dir'."\n".'$b = dirname($v, '.$up.'); // webapp root';
    }

    /**
     * @param array<string, string> $map
     */
    private function write_classmap(string $path, array $map, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $lines = [];

        foreach ($map as $class => $file) {
            $file = str_replace('\\', '/', $file);
            $key = var_export($class, true);
            $lines[] = '    '.$key.' => '.$this->path_code($file, $vendor_dir, $root).',';
        }

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

return array(
PHP;
        $body .= ($lines === [] ? "\n" : "\n".implode("\n", $lines)."\n").");\n";
        file_put_contents($path, $body, LOCK_EX);
    }

    private function path_code(string $file, string $vendor_dir, string $root): string
    {
        if (str_starts_with($file, $vendor_dir.'/')) {
            return '$v . '.(string) var_export(substr($file, strlen($vendor_dir)), true);
        }
        if (str_starts_with($file, $root.'/')) {
            return '$b . '.(string) var_export(substr($file, strlen($root)), true);
        }

        return (string) var_export($file, true);
    }

    /**
     * @param list<string> $files
     */
    private function write_files(string $path, array $files, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $items = [];
        foreach ($files as $file) {
            $items[] = '    '.$this->path_code(str_replace('\\', '/', $file), $vendor_dir, $root).',';
        }

        $list = $items === [] ? '' : "\n".implode("\n", $items)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

\$files = [{$list}];

foreach (\$files as \$file) {
    \$loaded = \\function_exists('webkernel_include') ? \\webkernel_include(\$file) : @include \$file;
    if (\$loaded === false) {
        throw new \\RuntimeException('Unable to load required file: '.\$file);
    }
}

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param list<string> $paths
     */
    private function write_path_list(string $path, array $paths, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $items = [];
        foreach ($paths as $item) {
            $items[] = '    '.$this->path_code(str_replace('\\', '/', $item), $vendor_dir, $root).',';
        }
        $list = $items === [] ? '' : "\n".implode("\n", $items)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

return [{$list}];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param array<string, list<string>> $namespaces
     */
    private function write_namespaced_paths(string $path, array $namespaces, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $ns_lines = [];
        $dir_lines = [];
        foreach ($namespaces as $namespace => $dirs) {
            $items = [];
            foreach ($dirs as $dir) {
                $code = $this->path_code(str_replace('\\', '/', $dir), $vendor_dir, $root);
                $items[] = '            '.$code.',';
                $dir_lines[$dir] = '        '.$code.',';
            }
            $list = $items === [] ? '' : "\n".implode("\n", $items)."\n        ";
            $ns_lines[] = '        '.var_export($namespace, true).' => ['.$list.'],';
        }
        $ns_body = $ns_lines === [] ? '' : "\n".implode("\n", $ns_lines)."\n    ";
        $dirs_body = $dir_lines === [] ? '' : "\n".implode("\n", $dir_lines)."\n    ";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

{$this->dump_path_prefix($vendor_dir, $root)}

return [
    'dirs' => [{$dirs_body}],
    'namespaces' => [{$ns_body}],
];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param array<string, class-string> $map
     */
    private function write_webapp_ide(array $map): void
    {
        $path = dirname(__DIR__, 3).'/_ide_helpers/_ide_webapp.php';
        $header = IdeHelper::generated_header();
        $methods = [
            '     * @method \Webkernel\Composables\ConfigComposable|mixed config(?string $key = null, mixed $default = null)',
        ];
        ksort($map);
        foreach ($map as $name => $class) {
            if ($name === 'config') {
                continue;
            }
            $methods[] = '     * '.$this->composable_phpdoc($name, $class);
        }
        $block = implode("\n", $methods);
        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit. IDE / PHPStan stub for webapp() composables.

namespace Webkernel;

if (false) {
    /**
{$block}
     */
    final class WebApp
    {
    }
}

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param class-string $class
     */
    private function composable_phpdoc(string $name, string $class): string
    {
        if (method_exists($class, '__invoke')) {
            try {
                $ref = new \ReflectionMethod($class, '__invoke');
            } catch (\ReflectionException) {
                return '@method \\'.$class.' '.$name.'()';
            }
            $params = [];
            foreach ($ref->getParameters() as $parameter) {
                $params[] = $this->phpdoc_parameter($parameter, $class);
            }
            $return = $this->phpdoc_type($ref->getReturnType(), $class) ?? ('\\'.$class);

            return '@method '.$return.' '.$name.'('.implode(', ', $params).')';
        }

        return '@method \\'.$class.' '.$name.'()';
    }

    /**
     * @param class-string $class
     */
    private function phpdoc_parameter(\ReflectionParameter $parameter, string $class): string
    {
        $type = $this->phpdoc_type($parameter->getType(), $class);
        $piece = ($type !== null ? $type.' ' : '').'$'.$parameter->getName();
        if ($parameter->isDefaultValueAvailable()) {
            $default = $parameter->getDefaultValue();
            if ($default === []) {
                $piece .= ' = []';
            } elseif ($default === null) {
                $piece .= ' = null';
            } else {
                $piece .= ' = '.var_export($default, true);
            }
        } elseif ($parameter->isOptional() || $parameter->allowsNull()) {
            $piece .= ' = null';
        }

        return $piece;
    }

    /**
     * @param class-string $class
     */
    private function phpdoc_type(?\ReflectionType $type, string $class): ?string
    {
        if ($type instanceof \ReflectionNamedType) {
            $name = $type->getName();
            if ($name === 'self' || $name === 'static') {
                $name = '\\'.$class;
            } elseif (! $type->isBuiltin()) {
                $name = '\\'.$name;
            }
            if ($type->allowsNull() && $name !== 'mixed' && $name !== 'null') {
                return '?'.$name;
            }

            return $name;
        }

        return null;
    }

    /**
     * @param array<string, class-string> $map
     */
    private function write_composables(string $path, array $map): void
    {
        $header = IdeHelper::generated_header();
        $lines = [];
        foreach ($map as $name => $class) {
            $lines[] = '    '.var_export($name, true).' => \\'.$class.'::class,';
        }
        $list = $lines === [] ? '' : "\n".implode("\n", $lines)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

return [{$list}];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    /**
     * @param list<string> $classes
     */
    private function write_class_list(string $path, array $classes): void
    {
        $header = IdeHelper::generated_header();
        $lines = [];
        foreach ($classes as $class) {
            $lines[] = '    \\'.$class.'::class,';
        }
        $list = $lines === [] ? '' : "\n".implode("\n", $lines)."\n";

        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//>
//> Generated. Do not edit.

return [{$list}];

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    private function write_php(string $path, mixed $data): void
    {
        $export = var_export($data, true);
        $header = IdeHelper::generated_header();
        $body = <<<PHP
<?php declare(strict_types=1);
{$header}
//> Generated. Do not edit.
//> Host moved? Run: composer dump-autoload

return {$export};

PHP;
        file_put_contents($path, $body, LOCK_EX);
    }

    private function terminal(): Terminal
    {
        return new Terminal();
    }
}
