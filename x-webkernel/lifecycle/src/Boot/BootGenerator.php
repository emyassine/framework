<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Boot;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webkernel\DevEnv\IdeHelper;
use Webkernel\Instance\InstanceId;
use Webkernel\WebAppApi\Composables\ComposableContract;

/**
 * Writes {vendor}/composer/webkernel.php and related dump files.
 * Composer-time only. Do not walk disks on the request path.
 */
final class BootGenerator
{
    public const BOOT_BASENAME = 'webkernel.php';

    public const CLASSMAP_BASENAME = 'webkernel_classmap.php';

    public const FILES_BASENAME = 'webkernel_files.php';

    public const VIEWS_BASENAME = 'webkernel_views.php';

    public const ROUTES_BASENAME = 'webkernel_routes.php';

    public const COMPOSABLES_BASENAME = 'webkernel_composables.php';

    public const PROVIDERS_BASENAME = 'webkernel_providers.php';

    public static function write(Composer $composer, ?IOInterface $io = null): void
    {
        $vendor_dir = rtrim(
            str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $composer->getConfig()->get('vendor-dir')),
            DIRECTORY_SEPARATOR,
        );
        $root = self::project_root($vendor_dir);
        $composer_dir = $vendor_dir.DIRECTORY_SEPARATOR.'composer';

        if (! is_dir($composer_dir) && ! mkdir($composer_dir, 0775, true) && ! is_dir($composer_dir)) {
            self::warn($io, 'cannot create '.$composer_dir);

            return;
        }

        $instance_id = InstanceId::record($root);
        $vendor_rel = self::relative($root, $vendor_dir) ?? basename($vendor_dir);

        $boot = [
            'instance_id' => $instance_id,
            'webapp_root' => $root,
            'vendor_dir' => $vendor_dir,
            'vendor_rel' => str_replace('\\', '/', $vendor_rel),
            'generated_at' => gmdate('c'),
        ];

        $classmap = self::classmap($composer);
        $composables = self::composables_list($classmap);

        self::write_php($composer_dir.DIRECTORY_SEPARATOR.self::BOOT_BASENAME, $boot);
        self::write_classmap(
            $composer_dir.DIRECTORY_SEPARATOR.self::CLASSMAP_BASENAME,
            $classmap,
            $vendor_dir,
            $root,
        );
        self::write_files(
            $composer_dir.DIRECTORY_SEPARATOR.self::FILES_BASENAME,
            self::files_list($composer),
            $vendor_dir,
            $root,
        );
        self::write_path_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::VIEWS_BASENAME,
            self::views_list($composer, $root),
            $vendor_dir,
            $root,
        );
        self::write_path_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::ROUTES_BASENAME,
            self::routes_list($composer, $root),
            $vendor_dir,
            $root,
        );
        self::write_composables(
            $composer_dir.DIRECTORY_SEPARATOR.self::COMPOSABLES_BASENAME,
            $composables,
        );
        self::write_class_list(
            $composer_dir.DIRECTORY_SEPARATOR.self::PROVIDERS_BASENAME,
            self::providers_list($composer),
        );
        self::info($io, 'wrote composer/'.self::BOOT_BASENAME.' (instance '.$instance_id.')');

        try {
            $ide = IdeHelper::generate($vendor_dir);
            self::info($io, sprintf(
                'ide helper %s (%d classes, %d bytes%s)',
                $ide['path'],
                $ide['classes'],
                $ide['bytes'],
                $ide['skipped'] ? ', unchanged' : '',
            ));
            $webapp_ide = IdeHelper::generate_webapp($composables);
            self::info($io, sprintf(
                'webapp ide helper %s (%d bytes%s)',
                $webapp_ide['path'],
                $webapp_ide['bytes'],
                $webapp_ide['skipped'] ? ', unchanged' : '',
            ));
        } catch (\Throwable $e) {
            self::warn($io, 'ide helper: '.$e->getMessage());
        }
    }

    private static function project_root(string $vendor_dir): string
    {
        $root = dirname($vendor_dir);
        if (is_file($root.DIRECTORY_SEPARATOR.'composer.json')) {
            $real = realpath($root);

            return $real !== false ? $real : $root;
        }

        throw new \RuntimeException('Cannot resolve project root from vendor-dir '.$vendor_dir);
    }

    private static function relative(string $from, string $to): ?string
    {
        $from = rtrim(str_replace('\\', '/', $from), '/');
        $to = str_replace('\\', '/', $to);
        if (str_starts_with($to, $from.'/')) {
            return substr($to, strlen($from) + 1);
        }

        return null;
    }

    private static function is_webkernel_package(PackageInterface $package): bool
    {
        $type = $package->getType();
        if (is_string($type) && str_starts_with($type, 'webkernel-')) {
            return true;
        }
        if (isset($package->getExtra()['webkernel'])) {
            return true;
        }

        return str_starts_with($package->getName(), 'webkernel/');
    }

    /**
     * @return array<string, mixed>
     */
    private static function extra(PackageInterface|array $package): array
    {
        if ($package instanceof PackageInterface) {
            $extra = $package->getExtra()['webkernel'] ?? null;
        } else {
            $extra = $package['extra']['webkernel'] ?? null;
        }

        return is_array($extra) ? $extra : [];
    }

    /**
     * Scan PSR-4 dirs of installed webkernel packages (Composer install paths).
     *
     * @return array<string, string>
     */
    private static function classmap(Composer $composer): array
    {
        $map = [];
        $installers = $composer->getInstallationManager();

        foreach ($composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages() as $package) {
            if (! self::is_webkernel_package($package)) {
                continue;
            }
            $install_path = $installers->getInstallPath($package);
            if (! is_string($install_path) || $install_path === '' || ! is_dir($install_path)) {
                continue;
            }
            $psr4 = $package->getAutoload()['psr-4'] ?? [];
            foreach ($psr4 as $namespace => $dirs) {
                foreach ((array) $dirs as $dir) {
                    $base = rtrim($install_path, '/\\').DIRECTORY_SEPARATOR.str_replace(['\\', '/'], DIRECTORY_SEPARATOR, (string) $dir);
                    $base = rtrim($base, '/\\');
                    if (! is_dir($base)) {
                        continue;
                    }
                    self::scan_psr4($map, (string) $namespace, $base);
                }
            }
        }

        ksort($map);

        return $map;
    }

    /**
     * Path/instance function files. Composable packages (extra.webkernel.provider)
     * keep helpers next to the class and are not dumped into webkernel_files.php.
     *
     * @return list<string>
     */
    private static function files_list(Composer $composer): array
    {
        $paths = [];
        $installers = $composer->getInstallationManager();

        foreach ($composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages() as $package) {
            if (! self::is_webkernel_package($package)) {
                continue;
            }
            $install_path = $installers->getInstallPath($package);
            if (! is_string($install_path) || $install_path === '' || ! is_dir($install_path)) {
                continue;
            }
            $install_path = rtrim(str_replace('\\', '/', $install_path), '/');

            if (! isset(self::extra($package)['provider'])) {
                self::collect_function_files($paths, $install_path);
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
                if (isset(self::extra($data)['provider'])) {
                    continue;
                }
                self::collect_function_files($paths, str_replace('\\', '/', dirname($json_file)));
            }
        }

        $list = array_keys($paths);
        sort($list, SORT_STRING);

        return $list;
    }

    /**
     * Dump-autoload map of ComposableContract implementors already in the classmap.
     * No glob on the request path.
     *
     * @param array<string, string> $classmap
     * @return array<string, class-string<ComposableContract>>
     */
    private static function composables_list(array $classmap): array
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
     * extra.webkernel.provider: one FQCN per package.
     *
     * @return list<class-string>
     */
    private static function providers_list(Composer $composer): array
    {
        $providers = [];
        foreach (self::package_paths($composer) as $extra) {
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
    private static function collect_function_files(array &$paths, string $dir): void
    {
        foreach (glob($dir.'/functions/*.php') ?: [] as $file) {
            if (is_file($file)) {
                $paths[str_replace('\\', '/', $file)] = true;
            }
        }
    }

    /**
     * Host resources/views first, then each package views dir.
     *
     * extra.webkernel.views: false | true | "views" | list<string>
     * Missing: include views/ and resources/views/ when those directories exist.
     *
     * @return list<string>
     */
    private static function views_list(Composer $composer, string $root): array
    {
        $dirs = [];
        $host = rtrim(str_replace('\\', '/', $root), '/').'/resources/views';
        $dirs[$host] = true;

        foreach (self::package_paths($composer) as $install_path => $extra) {
            foreach (self::declared_dirs($install_path, $extra['views'] ?? null, ['views', 'resources/views']) as $dir) {
                if (self::has_view_templates($dir)) {
                    $dirs[$dir] = true;
                }
            }
            foreach (glob($install_path.'/src/*/views') ?: [] as $nested) {
                $nested = str_replace('\\', '/', $nested);
                if (is_dir($nested) && self::has_view_templates($nested)) {
                    $dirs[$nested] = true;
                }
            }
        }

        return array_keys($dirs);
    }

    /**
     * extra.webkernel.routes: false | true | "routes/web.php" | list<string>
     * Missing: include routes.php and routes/web.php when those files exist.
     *
     * @return list<string>
     */
    private static function routes_list(Composer $composer, string $root): array
    {
        $files = [];
        $root = rtrim(str_replace('\\', '/', $root), '/');
        foreach ([$root.'/routes/web.php', $root.'/routes.php'] as $host) {
            if (is_file($host)) {
                $files[$host] = true;
            }
        }

        foreach (self::package_paths($composer) as $install_path => $extra) {
            foreach (self::declared_files($install_path, $extra['routes'] ?? null, ['routes/web.php', 'routes.php']) as $file) {
                $files[$file] = true;
            }
        }

        return array_keys($files);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function package_paths(Composer $composer): array
    {
        $out = [];
        $installers = $composer->getInstallationManager();

        foreach ($composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages() as $package) {
            if (! self::is_webkernel_package($package)) {
                continue;
            }
            $install_path = $installers->getInstallPath($package);
            if (! is_string($install_path) || $install_path === '' || ! is_dir($install_path)) {
                continue;
            }
            $install_path = rtrim(str_replace('\\', '/', $install_path), '/');
            $out[$install_path] = self::extra($package);

            foreach (glob($install_path.'/src/*/composer.json') ?: [] as $json_file) {
                $raw = file_get_contents($json_file);
                if ($raw === false) {
                    continue;
                }
                $data = json_decode($raw, true);
                if (! is_array($data)) {
                    continue;
                }
                $dir = str_replace('\\', '/', dirname($json_file));
                $out[$dir] = self::extra($data);
            }
        }

        return $out;
    }

    private static function has_view_templates(string $dir): bool
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
    private static function declared_dirs(string $install_path, mixed $declared, array $defaults): array
    {
        if ($declared === false) {
            return [];
        }
        $rels = self::declared_rels($declared, $defaults);
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
    private static function declared_files(string $install_path, mixed $declared, array $defaults): array
    {
        if ($declared === false) {
            return [];
        }
        $rels = self::declared_rels($declared, $defaults);
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
    private static function declared_rels(mixed $declared, array $defaults): array
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
    private static function scan_psr4(array &$map, string $namespace, string $base): void
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
     * Relocatable classmap: $v vendor_dir, $b base_dir. No absolute host paths.
     *
     * @param array<string, string> $map
     */
    private static function write_classmap(string $path, array $map, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $lines = [];

        foreach ($map as $class => $file) {
            $file = str_replace('\\', '/', $file);
            $key = var_export($class, true);
            $lines[] = '    '.$key.' => '.self::path_code($file, $vendor_dir, $root).',';
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

    /**
     * Same rule as Composer AutoloadGenerator::getPathCode().
     */
    private static function path_code(string $file, string $vendor_dir, string $root): string
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
    private static function write_files(string $path, array $files, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $items = [];
        foreach ($files as $file) {
            $items[] = '    '.self::path_code(str_replace('\\', '/', $file), $vendor_dir, $root).',';
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
    private static function write_path_list(string $path, array $paths, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $items = [];
        foreach ($paths as $item) {
            $items[] = '    '.self::path_code(str_replace('\\', '/', $item), $vendor_dir, $root).',';
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
    private static function write_composables(string $path, array $map): void
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
    private static function write_class_list(string $path, array $classes): void
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

    private static function write_php(string $path, mixed $data): void
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

    private static function info(?IOInterface $io, string $message): void
    {
        if ($io !== null) {
            $io->write('<info>webkernel:</info> '.$message);
        }
    }

    private static function warn(?IOInterface $io, string $message): void
    {
        if ($io !== null) {
            $io->writeError('<warning>webkernel:</warning> '.$message);
        }
    }
}
