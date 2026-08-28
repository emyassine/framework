<?php declare(strict_types=1);

namespace Webkernel\Console\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webkernel\Composables\ComposableContract;
use Webkernel\Config\ConfigWriter;
use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;
use Webkernel\Console\Terminal;
use Webkernel\DevEnv\IdeHelper;
use Webkernel\Instance\InstanceId;
use Webkernel\Platform\Panel;
use Webkernel\Platform\PanelProvider;
use Webkernel\PlatformProvider;

/**
 * Writes `{vendor}/composer/webkernel_*.php`. Composer `post-autoload-dump`
 * invokes this command; so does `php webkernel dump-autoload`.
 */
final readonly class DumpAutoloadCommand
{
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

    #[ConsoleCommand(
        name: 'dump-autoload',
        description: 'Write Webkernel dump files (classmap, providers, views, routes)',
    )]
    public function __invoke(): ExitCode
    {
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
        $this->write_php(
            $composer_dir.DIRECTORY_SEPARATOR.self::PANELS_BASENAME,
            $this->panels_dump($providers, $classmap, $root),
        );

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
                if (! class_exists($panel_class) || ! is_a($panel_class, PanelProvider::class, true)) {
                    continue;
                }
                /** @var PanelProvider $instance */
                $instance = new $panel_class();
                $snapshot = $instance->panel(new Panel())->to_array();
                $snapshot['provider'] = $panel_class;
                $snapshot['package_provider'] = $provider;
                $snapshot['prefix'] = $row['prefix'];
                $out[] = $snapshot;
            }
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
    if ((@include \$file) === false) {
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
        $path = dirname(IdeHelper::output_path()).'/_ide_webapp.php';
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
