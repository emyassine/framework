<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Tests\Feature;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Attribution is required on every Webkernel PHP source.
 */
final class AttributionTest extends TestCase
{
    private const NEEDLE = 'This file is part of Webkernel.';

    /**
     * @return void
     */
    public function test_php_sources_carry_webkernel_attribution(): void
    {
        $root = \dirname(__DIR__, 4);
        $missing = [];
        foreach ($this->php_sources($root) as $file) {
            $head = $this->head($file);
            if (! \str_contains($head, self::NEEDLE)) {
                $missing[] = \str_replace('\\', '/', \substr($file, \strlen($root) + 1));
            }
        }
        $this->assertSame([], $missing, 'PHP sources missing Webkernel attribution');
    }

    /**
     * @return list<string>
     */
    private function php_sources(string $root): array
    {
        $out = [];
        $trees = [
            $root.'/x-webkernel',
            $root.'/config',
            $root.'/public',
            $root.'/modules',
        ];
        foreach ($trees as $tree) {
            if (! \is_dir($tree)) {
                continue;
            }
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tree, FilesystemIterator::SKIP_DOTS),
            );
            foreach ($it as $file) {
                if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                    continue;
                }
                $path = $file->getPathname();
                if ($this->skip($path)) {
                    continue;
                }
                $out[] = $path;
            }
        }
        foreach (\glob($root.'/platform/*.php') ?: [] as $path) {
            $out[] = $path;
        }
        $bin = $root.'/webkernel';
        if (\is_file($bin)) {
            $out[] = $bin;
        }

        return $out;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function skip(string $path): bool
    {
        $norm = \str_replace('\\', '/', $path);
        if (\str_ends_with($norm, '.view.php')) {
            return true;
        }
        if (! \str_ends_with($norm, '.php') && ! \str_ends_with($norm, '/webkernel')) {
            return true;
        }

        return \str_contains($norm, '/node_modules/')
            || \str_contains($norm, '/packagist/')
            || \str_contains($norm, '/vendor/')
            || \str_contains($norm, '/dependencies/')
            || \str_contains($norm, '/storage/');
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function head(string $file): string
    {
        $fh = \fopen($file, 'rb');
        if ($fh === false) {
            return '';
        }
        $chunk = \fread($fh, 1024);
        \fclose($fh);

        return \is_string($chunk) ? $chunk : '';
    }
}
