<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\DevTools;

use Webkernel\Console\Commands\DumpAutoloadCommand;
use Webkernel\Console\DumpHook;

/**
 * IDE stubs from Composer classmap. No directory walk, no hardcoded class names.
 *
 * Input is autoload_classmap.php (Composer already scanned). Output is
 * deterministic (sorted names + content hash). Rewrite skipped when hash matches.
 */
final class IdeHelper implements DumpHook
{
    /**
     * @param $vendor_dir string
     *
     * @return void
     */
    public function run(string $vendor_dir): void
    {
        $ide = self::generate($vendor_dir);
        if (\function_exists('webterminal')) {
            webterminal()->info(\sprintf(
                'ide helper %s (%d classes, %d bytes%s)',
                $ide['path'],
                $ide['classes'],
                $ide['bytes'],
                $ide['skipped'] ? ', unchanged' : '',
            ));
        }
    }

    /**
     * @return string
     */
    public static function output_path(): string
    {
        return webkernel_package('codebase', '_ide_helpers').'/_ide_helper.php';
    }

    /**
     * @return string
     */
    public static function generated_header(): string
    {
        return DumpAutoloadCommand::generated_header();
    }

    /**
     * @return array{path: string, classes: int, bytes: int, skipped: bool}
     */
    public static function generate(string $vendor_dir, ?string $output = null): array
    {
        $vendor_dir = \rtrim($vendor_dir, '/\\');
        $output ??= self::output_path();
        $catalog = self::catalog($vendor_dir);

        $ctx = \hash_init('xxh3');
        foreach ($catalog as $name => $kind) {
            \hash_update($ctx, $kind.' '.$name."\n");
        }
        $hash = \hash_final($ctx);

        if (\is_file($output) && self::stored_hash($output) === $hash) {
            return ['path' => $output, 'classes' => \count($catalog), 'bytes' => (int) \filesize($output), 'skipped' => true];
        }

        $bytes = self::write($output, $catalog, $hash);

        return ['path' => $output, 'classes' => \count($catalog), 'bytes' => $bytes, 'skipped' => false];
    }

    /**
     * FQCN => class|interface|trait|enum, from Composer classmap + file tokens.
     *
     * @return array<string, 'class'|'interface'|'trait'|'enum'>
     */
    private static function catalog(string $vendor_dir): array
    {
        $classmap = $vendor_dir.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'autoload_classmap.php';
        $catalog = [];

        if (\is_file($classmap)) {
            $map = require $classmap;
            if (\is_array($map)) {
                foreach ($map as $class => $file) {
                    if (! \is_string($class) || \str_starts_with($class, 'Webkernel\\') || ! self::is_class_name($class)) {
                        continue;
                    }
                    $slash = \strrpos($class, '\\');
                    $short = $slash === false ? $class : \substr($class, $slash + 1);
                    $catalog[$class] = \is_string($file) && \is_file($file)
                        ? self::kind($file, $short)
                        : 'class';
                }
            }
        }
        \ksort($catalog, SORT_STRING);

        return $catalog;
    }

    /**
     * @return 'class'|'interface'|'trait'|'enum'
     */
    private static function kind(string $file, string $short): string
    {
        $src = \file_get_contents($file);
        if (! \is_string($src) || $src === '') {
            return 'class';
        }
        if (! \str_contains($src, 'interface') && ! \str_contains($src, 'trait') && ! \str_contains($src, 'enum')) {
            return 'class';
        }

        $tokens = \token_get_all($src);
        $prev = 0;
        $count = \count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (! \is_array($token)) {
                $prev = 0;
                continue;
            }
            $id = $token[0];
            if ($id === T_WHITESPACE || $id === T_COMMENT || $id === T_DOC_COMMENT) {
                continue;
            }
            $kind = match ($id) {
                T_INTERFACE => 'interface',
                T_TRAIT => 'trait',
                T_ENUM => 'enum',
                T_CLASS => $prev === T_DOUBLE_COLON ? null : 'class',
                default => null,
            };
            $prev = $id;
            if ($kind === null) {
                continue;
            }
            for ($j = $i + 1; $j < $count; $j++) {
                $next = $tokens[$j];
                if (\is_array($next) && ($next[0] === T_WHITESPACE || $next[0] === T_COMMENT || $next[0] === T_DOC_COMMENT)) {
                    continue;
                }
                if (\is_array($next) && $next[0] === T_STRING && $next[1] === $short) {
                    return $kind;
                }
                break;
            }
        }

        return 'class';
    }

    private static function is_class_name(string $name): bool
    {
        if ($name === '' || \str_contains($name, ' ') || \str_contains($name, '$')) {
            return false;
        }

        foreach (\explode('\\', $name) as $part) {
            if ($part === '' || \preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $part) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, 'class'|'interface'|'trait'|'enum'> $catalog
     */
    private static function write(string $output, array $catalog, string $hash): int
    {
        $dir = \dirname($output);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }

        $tmp = $output.'.tmp';
        $fh = \fopen($tmp, 'wb');
        if ($fh === false) {
            throw new \RuntimeException('Unable to write '.$tmp);
        }

        \fwrite($fh, "<?php declare(strict_types=1);\n");
        \fwrite($fh, self::generated_header()."\n");
        \fwrite($fh, "//> Generated. Do not edit.\n");
        \fwrite($fh, '//> hash: '.$hash."\n\n");

        $current_ns = null;
        $open = false;

        foreach ($catalog as $fqcn => $kind) {
            $pos = \strrpos($fqcn, '\\');
            if ($pos === false) {
                $ns = '';
                $short = $fqcn;
            } else {
                $ns = \substr($fqcn, 0, $pos);
                $short = \substr($fqcn, $pos + 1);
            }

            if ($ns !== $current_ns) {
                if ($open) {
                    \fwrite($fh, "    }\n}\n");
                }
                \fwrite($fh, $ns === '' ? "namespace {\n    if (false) {\n" : 'namespace '.$ns." {\n    if (false) {\n");
                $open = true;
                $current_ns = $ns;
            }

            \fwrite($fh, '        '.$kind.' '.$short." {}\n");
        }

        if ($open) {
            \fwrite($fh, "    }\n}\n");
        }

        \fclose($fh);
        \rename($tmp, $output);

        return (int) \filesize($output);
    }

    private static function stored_hash(string $file): ?string
    {
        $fh = \fopen($file, 'rb');
        if ($fh === false) {
            return null;
        }
        $hash = null;
        for ($i = 0; $i < 16; $i++) {
            $line = \fgets($fh);
            if ($line === false) {
                break;
            }
            $line = \ltrim($line);
            if (\str_starts_with($line, '//> hash: ') || \str_starts_with($line, '// hash: ')) {
                $hash = \trim(\substr($line, \strpos($line, ':') + 1));
                break;
            }
        }
        \fclose($fh);

        return $hash !== '' ? $hash : null;
    }
}
