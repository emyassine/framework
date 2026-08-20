<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Boot;

use Composer\Composer;
use Composer\IO\IOInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Webkernel\DevEnv\IdeHelper;
use Webkernel\Instance\InstanceId;

/**
 * Writes {vendor}/composer/webkernel.php and webkernel_classmap.php.
 * Composer-time only.
 */
final class BootGenerator
{
    public const BOOT_BASENAME = 'webkernel.php';

    public const CLASSMAP_BASENAME = 'webkernel_classmap.php';

    public const FILES_BASENAME = 'webkernel_files.php';

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

        self::write_php($composer_dir.DIRECTORY_SEPARATOR.self::BOOT_BASENAME, $boot);
        self::write_classmap(
            $composer_dir.DIRECTORY_SEPARATOR.self::CLASSMAP_BASENAME,
            self::classmap($composer),
            $vendor_dir,
            $root,
        );
        self::write_files(
            $composer_dir.DIRECTORY_SEPARATOR.self::FILES_BASENAME,
            self::files_list($composer),
            $vendor_dir,
            $root,
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

    /**
     * Scan PSR-4 dirs of installed webkernel/* packages (Composer install paths).
     *
     * @return array<string, string>
     */
    private static function classmap(Composer $composer): array
    {
        $map = [];
        $installers = $composer->getInstallationManager();

        foreach ($composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages() as $package) {
            if (! str_starts_with($package->getName(), 'webkernel/')) {
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
     * autoload.files from nested subpackage composer.json (not the root loaders Composer already runs).
     *
     * @return list<string>
     */
    private static function files_list(Composer $composer): array
    {
        $paths = [];
        $skip = [];
        $installers = $composer->getInstallationManager();

        foreach ($composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages() as $package) {
            if (! str_starts_with($package->getName(), 'webkernel/')) {
                continue;
            }
            $install_path = $installers->getInstallPath($package);
            if (! is_string($install_path) || $install_path === '' || ! is_dir($install_path)) {
                continue;
            }
            $install_path = rtrim(str_replace('\\', '/', $install_path), '/');

            foreach ($package->getAutoload()['files'] ?? [] as $rel) {
                $skip[$install_path.'/'.ltrim(str_replace('\\', '/', (string) $rel), '/')] = true;
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
                $dir = str_replace('\\', '/', dirname($json_file));
                foreach ($data['autoload']['files'] ?? [] as $rel) {
                    if (! is_string($rel) || $rel === '') {
                        continue;
                    }
                    $abs = $dir.'/'.ltrim(str_replace('\\', '/', $rel), '/');
                    if (is_file($abs) && ! isset($skip[$abs])) {
                        $paths[$abs] = true;
                    }
                }
            }
        }

        $list = array_keys($paths);
        sort($list, SORT_STRING);

        return $list;
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
            return '$v . '.var_export(substr($file, strlen($vendor_dir)), true);
        }
        if (str_starts_with($file, $root.'/')) {
            return '$b . '.var_export(substr($file, strlen($root)), true);
        }

        return var_export($file, true);
    }

    /**
     * @param list<string> $files
     */
    private static function write_files(string $path, array $files, string $vendor_dir, string $root): void
    {
        $vendor_dir = rtrim(str_replace('\\', '/', $vendor_dir), '/');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $header = IdeHelper::generated_header();
        $requires = [];
        foreach ($files as $file) {
            $requires[] = 'require '.self::path_code(str_replace('\\', '/', $file), $vendor_dir, $root).';';
        }

        $body = <<<PHP
<?php declare(strict_types=1);

{$header}
//>
//> Generated. Do not edit.

\$v = dirname(__DIR__); // vendor_dir
\$b = dirname(\$v); // base_dir

PHP;
        $body .= ($requires === [] ? '' : implode("\n", $requires)."\n");
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
