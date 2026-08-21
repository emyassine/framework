<?php declare(strict_types=1);

namespace Webkernel\Console\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webkernel\Composables\ComposableContract;
use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;
use Webkernel\Console\Terminal;
use Webkernel\DevEnv\IdeHelper;
use Webkernel\Instance\InstanceId;

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
    public const ROUTES_BASENAME = 'webkernel_routes.php';
    public const COMPOSABLES_BASENAME = 'webkernel_composables.php';
    public const PROVIDERS_BASENAME = 'webkernel_providers.php';
    public const COMMANDS_BASENAME = 'webkernel_commands.php';

    #[ConsoleCommand(
        name: 'dump-autoload',
        description: 'Write Webkernel dump files (classmap, commands, composables)',
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
        $packages = $this->packages($vendor_dir);

        $boot = [
            'instance_id' => $instance_id,
            'webapp_root' => $root,
            'vendor_dir' => $vendor_dir,
            'vendor_rel' => str_replace('\\', '/', $vendor_rel),
            'generated_at' => gmdate('c'),
        ];

        $classmap = $this->classmap($packages);
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
        $this->write_path_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::VIEWS_BASENAME,
            $this->views_list($packages, $root),
            $vendor_dir,
            $root,
        );
        $this->write_path_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::ROUTES_BASENAME,
            $this->routes_list($packages, $root),
            $vendor_dir,
            $root,
        );
        $this->write_composables(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMPOSABLES_BASENAME,
            $composables,
        );
        $this->write_class_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::PROVIDERS_BASENAME,
            $this->providers_list($packages),
        );
        $this->write_class_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMMANDS_BASENAME,
            $this->commands_list($classmap),
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

    /**
     * Composer dump runs inside the Composer phar — ClassLoader is not the
     * host vendor. Walk cwd for composer.json instead.
     */
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
        $out = [];
        foreach ($list as $package) {
            if (! is_array($package) || ! $this->is_webkernel_package($package)) {
                continue;
            }
            $rel = $package['install-path'] ?? null;
            if (! is_string($rel) || $rel === '') {
                continue;
            }
            $install_path = str_replace('\\', '/', $vendor_dir.'/composer/'.$rel);
            $real = realpath($install_path);
            $install_path = $real !== false ? str_replace('\\', '/', $real) : rtrim($install_path, '/');
            if (! is_dir($install_path)) {
                continue;
            }
            $out[] = ['path' => $install_path, 'package' => $package];
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
     * @param array<string, mixed>|array{extra?: array<string, mixed>} $package
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
        $paths = [];
        foreach ($packages as $row) {
            $install_path = $row['path'];
            if (! isset($this->extra($row['package'])['provider'])) {
                $this->collect_function_files($paths, $install_path);
            }
            foreach (glob($install_path.'/src/*/composer.json') ?: [] as $json_file) {
                $raw = file_get_contents($json_file);
                if ($raw === false) {
                    continue;
                }
                $data = json_decode($raw, true);
                if (! is_array($data)) {
                    continue;
                }
                if (isset($this->extra($data)['provider'])) {
                    continue;
                }
                $this->collect_function_files($paths, str_replace('\\', '/', dirname($json_file)));
            }
        }
        $list = array_keys($paths);
        sort($list, SORT_STRING);

        return $list;
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
     * @param array<string, string> $classmap
     * @return list<class-string>
     */
    private function commands_list(array $classmap): array
    {
        $attribute = ConsoleCommand::class;
        $attr_file = $classmap[$attribute] ?? null;
        if (is_string($attr_file) && is_file($attr_file)) {
            require_once $attr_file;
        }
        if (! class_exists($attribute, false)) {
            return [];
        }

        $out = [];
        foreach ($classmap as $class => $file) {
            if (! is_string($file) || ! is_file($file)) {
                continue;
            }
            $src = file_get_contents($file);
            if ($src === false || ! str_contains($src, 'ConsoleCommand')) {
                continue;
            }
            require_once $file;
            if (! class_exists($class, false)) {
                continue;
            }
            try {
                $ref = new \ReflectionClass($class);
            } catch (\Throwable) {
                continue;
            }
            foreach ($ref->getMethods() as $method) {
                if ($method->getAttributes($attribute) !== []) {
                    $out[] = $class;
                    break;
                }
            }
        }
        sort($out, SORT_STRING);

        return $out;
    }

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return list<class-string>
     */
    private function providers_list(array $packages): array
    {
        $providers = [];
        foreach ($this->package_paths($packages) as $extra) {
            $provider = $extra['provider'] ?? null;
            if (is_string($provider) && $provider !== '' && ! in_array($provider, $providers, true)) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * @param array<string, true> $paths
     */
    private function collect_function_files(array &$paths, string $dir): void
    {
        foreach (glob($dir.'/functions/*.php') ?: [] as $file) {
            if (is_file($file)) {
                $paths[str_replace('\\', '/', $file)] = true;
            }
        }
    }

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return list<string>
     */
    private function views_list(array $packages, string $root): array
    {
        $dirs = [];
        $host = rtrim(str_replace('\\', '/', $root), '/').'/resources/views';
        $dirs[$host] = true;

        foreach ($this->package_paths($packages) as $install_path => $extra) {
            foreach ($this->declared_dirs($install_path, $extra['views'] ?? null, ['views', 'resources/views']) as $dir) {
                if ($this->has_view_templates($dir)) {
                    $dirs[$dir] = true;
                }
            }
            foreach (glob($install_path.'/src/*/views') ?: [] as $nested) {
                $nested = str_replace('\\', '/', $nested);
                if (is_dir($nested) && $this->has_view_templates($nested)) {
                    $dirs[$nested] = true;
                }
            }
        }

        return array_keys($dirs);
    }

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return list<string>
     */
    private function routes_list(array $packages, string $root): array
    {
        $files = [];
        $root = rtrim(str_replace('\\', '/', $root), '/');
        foreach ([$root.'/routes/web.php', $root.'/routes.php'] as $host) {
            if (is_file($host)) {
                $files[$host] = true;
            }
        }

        foreach ($this->package_paths($packages) as $install_path => $extra) {
            foreach ($this->declared_files($install_path, $extra['routes'] ?? null, ['routes/web.php', 'routes.php']) as $file) {
                $files[$file] = true;
            }
        }

        return array_keys($files);
    }

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return array<string, array<string, mixed>>
     */
    private function package_paths(array $packages): array
    {
        $out = [];
        foreach ($packages as $row) {
            $out[$row['path']] = $this->extra($row['package']);
            foreach (glob($row['path'].'/src/*/composer.json') ?: [] as $json_file) {
                $raw = file_get_contents($json_file);
                if ($raw === false) {
                    continue;
                }
                $data = json_decode($raw, true);
                if (! is_array($data)) {
                    continue;
                }
                $dir = str_replace('\\', '/', dirname($json_file));
                $out[$dir] = $this->extra($data);
            }
        }

        return $out;
    }

    private function has_view_templates(string $dir): bool
    {
        if (! is_dir($dir)) {
            return false;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($it as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.view.php')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $declared
     * @param list<string> $defaults
     * @return list<string>
     */
    private function declared_dirs(string $install_path, mixed $declared, array $defaults): array
    {
        if ($declared === false) {
            return [];
        }
        $rels = $this->declared_rels($declared, $defaults);
        $dirs = [];
        foreach ($rels as $rel) {
            $dir = $install_path.'/'.$rel;
            if (is_dir($dir)) {
                $dirs[] = $dir;
            }
        }

        return $dirs;
    }

    /**
     * @param mixed $declared
     * @param list<string> $defaults
     * @return list<string>
     */
    private function declared_files(string $install_path, mixed $declared, array $defaults): array
    {
        if ($declared === false) {
            return [];
        }
        $rels = $this->declared_rels($declared, $defaults);
        $files = [];
        foreach ($rels as $rel) {
            $file = $install_path.'/'.$rel;
            if (is_file($file)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @param mixed $declared
     * @param list<string> $defaults
     * @return list<string>
     */
    private function declared_rels(mixed $declared, array $defaults): array
    {
        if ($declared === null || $declared === true) {
            return $defaults;
        }
        if (is_string($declared) && $declared !== '') {
            return [ltrim(str_replace('\\', '/', $declared), '/')];
        }
        if (! is_array($declared)) {
            return [];
        }
        $rels = [];
        foreach ($declared as $rel) {
            if (is_string($rel) && $rel !== '') {
                $rels[] = ltrim(str_replace('\\', '/', $rel), '/');
            }
        }

        return $rels;
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
            $class = $namespace.str_replace('/', '\\', substr($rel, 0, -4));
            $map[$class] = str_replace('\\', '/', $file->getPathname());
        }
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

\$v = dirname(__DIR__); // vendor_dir
\$b = dirname(\$v); // base_dir

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

\$v = dirname(__DIR__); // vendor_dir
\$b = dirname(\$v); // base_dir

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

\$v = dirname(__DIR__); // vendor_dir
\$b = dirname(\$v); // base_dir

return [{$list}];

PHP;
        file_put_contents($path, $body, LOCK_EX);
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
     * @param list<class-string> $classes
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
