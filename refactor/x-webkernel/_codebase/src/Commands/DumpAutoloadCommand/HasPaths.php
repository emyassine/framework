<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Commands\DumpAutoloadCommand;

use Webkernel\Console\Terminal;

trait HasPaths
{
    use _DumpAutoloadCommand;

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
     * @return string
     */
    private function codebase_root(): string
    {
        return \dirname(__DIR__, 4);
    }

    /**
     * Composer post-autoload-dump runs inside the phar ClassLoader, so
     * namespacer never required functions/paths.php. Load it from this package.
     *
     * @return void
     */
    private function ensure_path_helpers(): void
    {
        $file = $this->codebase_root().'/functions/paths.php';
        if (\is_file($file)) {
            require_once $file;
        }
        if (\function_exists('webkernel_boot_flush')) {
            \webkernel_boot_flush();
        }
    }

    private function dump_path_prefix(string $vendor_dir, string $root): string
    {
        $vendor_rel = str_replace('\\', '/', $this->relative($root, $vendor_dir) ?? basename($vendor_dir));
        $up = substr_count($vendor_rel, '/') + 1;

        return '$v = dirname(__DIR__); // vendor_dir'."\n".'$b = dirname($v, '.$up.'); // webapp root';
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

    private function terminal(): Terminal
    {
        return new Terminal();
    }
}
