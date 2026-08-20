<?php declare(strict_types=1);

namespace Webkernel\DevEnv;

/**
 * IDE stubs from Composer classmap. No directory walk, no hardcoded class names.
 *
 * Input is autoload_classmap.php (Composer already scanned). Output is
 * deterministic (sorted names + content hash). Rewrite skipped when hash matches.
 */
final class IdeHelper
{
    public static function output_path(): string
    {
        return dirname(__DIR__).'/_ide_helper.php';
    }

    /**
     * Header for generated PHP files. End year is current year + 1.
     */
    public static function generated_header(): string
    {
        $end = ((int) date('Y')) + 1;

        return implode("\n", [
            '//> This file is part of Webkernel.',
            '//> (c) 2025 - '.$end.' Numerimondes, El Moumen Yassine',
            '//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>',
            '//> For the full copyright and license information, please view the LICENSE',
            '//> file that was distributed with this source code.',
        ]);
    }

    /**
     * @return array{path: string, classes: int, bytes: int, skipped: bool}
     */
    public static function generate(string $vendor_dir, ?string $output = null): array
    {
        $vendor_dir = rtrim($vendor_dir, '/\\');
        $output ??= self::output_path();
        $names = self::class_names($vendor_dir);
        sort($names, SORT_STRING);

        $ctx = hash_init('xxh3');
        foreach ($names as $name) {
            hash_update($ctx, $name."\n");
        }
        $hash = hash_final($ctx);

        if (is_file($output) && self::stored_hash($output) === $hash) {
            return ['path' => $output, 'classes' => count($names), 'bytes' => (int) filesize($output), 'skipped' => true];
        }

        $bytes = self::write($output, $names, $hash);

        return ['path' => $output, 'classes' => count($names), 'bytes' => $bytes, 'skipped' => false];
    }

    /**
     * @return list<string>
     */
    private static function class_names(string $vendor_dir): array
    {
        $classmap = $vendor_dir.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_classmap.php';
        $names = [];

        if (is_file($classmap)) {
            $map = require $classmap;
            if (is_array($map)) {
                foreach ($map as $class => $file) {
                    if (is_string($class) && self::is_class_name($class)) {
                        $names[$class] = true;
                    }
                }
            }
        }

        return array_keys($names);
    }

    private static function is_class_name(string $name): bool
    {
        if ($name === '' || str_contains($name, ' ') || str_contains($name, '$')) {
            return false;
        }

        foreach (explode('\\', $name) as $part) {
            if ($part === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $part) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<string> $names
     */
    private static function write(string $output, array $names, string $hash): int
    {
        $dir = dirname($output);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }

        $tmp = $output.'.tmp';
        $fh = fopen($tmp, 'wb');
        if ($fh === false) {
            throw new \RuntimeException('Unable to write '.$tmp);
        }

        fwrite($fh, "<?php declare(strict_types=1);\n\n");
        fwrite($fh, self::generated_header()."\n");
        fwrite($fh, "//> Generated. Do not edit.\n");
        fwrite($fh, '//> hash: '.$hash."\n\n");

        $current_ns = null;
        $open = false;

        foreach ($names as $fqcn) {
            $pos = strrpos($fqcn, '\\');
            if ($pos === false) {
                $ns = '';
                $short = $fqcn;
            } else {
                $ns = substr($fqcn, 0, $pos);
                $short = substr($fqcn, $pos + 1);
            }

            if ($ns !== $current_ns) {
                if ($open) {
                    fwrite($fh, "    }\n}\n");
                }
                fwrite($fh, $ns === '' ? "namespace {\n    if (false) {\n" : 'namespace '.$ns." {\n    if (false) {\n");
                $open = true;
                $current_ns = $ns;
            }

            fwrite($fh, '        class '.$short." {}\n");
        }

        if ($open) {
            fwrite($fh, "    }\n}\n");
        }

        fclose($fh);
        rename($tmp, $output);

        return (int) filesize($output);
    }

    /**
     * Stub so Intellephense sees webapp(): WebApp and WebApp::{composable}().
     *
     * @param array<string, class-string> $composables
     * @return array{path: string, bytes: int, skipped: bool}
     */
    public static function generate_webapp(array $composables, ?string $output = null): array
    {
        $output ??= dirname(__DIR__).'/_ide_helper_webapp.php';
        ksort($composables);
        $ctx = hash_init('xxh3');
        foreach ($composables as $name => $class) {
            hash_update($ctx, $name.'='.$class."\n");
        }
        $hash = hash_final($ctx);
        if (is_file($output) && self::stored_hash($output) === $hash) {
            return ['path' => $output, 'bytes' => (int) filesize($output), 'skipped' => true];
        }

        $methods = [];
        foreach ($composables as $name => $class) {
            $methods[] = '            public function '.$name.'(): \\'.$class.' {}';
        }
        $methods[] = '            public function container(): \\Webkernel\\WebAppApi\\Container {}';
        $methods[] = '            public function boot(): self {}';
        $method_block = implode("\n", $methods);
        $header = self::generated_header();

        $body = <<<PHP
<?php declare(strict_types=1);

{$header}
//> Generated. Do not edit.
//> hash: {$hash}

namespace Webkernel {
    if (false) {
        final class WebApp
        {
{$method_block}
        }
    }
}

namespace {
    if (false) {
        function webapp(): \\Webkernel\\WebApp
        {
        }
    }
}

PHP;
        $dir = dirname($output);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }
        file_put_contents($output, $body, LOCK_EX);

        return ['path' => $output, 'bytes' => (int) filesize($output), 'skipped' => false];
    }

    private static function stored_hash(string $file): ?string
    {
        $fh = fopen($file, 'rb');
        if ($fh === false) {
            return null;
        }
        $hash = null;
        for ($i = 0; $i < 16; $i++) {
            $line = fgets($fh);
            if ($line === false) {
                break;
            }
            $line = ltrim($line);
            if (str_starts_with($line, '//> hash: ') || str_starts_with($line, '// hash: ')) {
                $hash = trim(substr($line, strpos($line, ':') + 1));
                break;
            }
        }
        fclose($fh);

        return $hash !== '' ? $hash : null;
    }
}
