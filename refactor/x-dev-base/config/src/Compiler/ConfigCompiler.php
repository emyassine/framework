<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Compiler;

use Webkernel\Config\Contracts\CompilerContract;
use Webkernel\Config\Exceptions\ConfigWriteException;

/**
 * Compiles configuration sources into an ultra-fast flat hash table cache.
 *
 * Traverses nested structures once at compile time, pre-calculating all dot-notation
 * paths so that runtime queries require zero string splitting or array iterations.
 */
class ConfigCompiler implements CompilerContract
{
    /**
     * Compiles configuration source files and overrides into flattened and tree structures.
     *
     * @param $sources array<string, string|array<string, mixed>> Stems mapped to file paths or arrays.
     * @param $runtime_overrides array<string, mixed> Runtime modifications to merge over sources.
     *
     * @return array{flat: array<string, mixed>, tree: array<string, mixed>}
     */
    public function compile(array $sources, array $runtime_overrides = []): array
    {
        $tree = [];

        foreach ($sources as $stem => $source) {
            $content = \is_array($source) ? $source : $this->load_source_file($source);

            if ($content === []) {
                continue;
            }

            // Expand dotted stem segments (e.g. "services.stripe" -> $tree['services']['stripe'])
            $expanded = $this->expand_stem($stem, $content);
            $tree = \array_replace_recursive($tree, $expanded);
        }

        if ($runtime_overrides !== []) {
            $tree = \array_replace_recursive($tree, $runtime_overrides);
        }

        $flat = self::flatten_tree($tree);

        return [
            'flat' => $flat,
            'tree' => $tree,
        ];
    }

    /**
     * Writes the compiled flat and tree arrays to a cache file atomically.
     *
     * @param $cache_file string Path to destination cache file.
     * @param $flat array<string, mixed> Flattened configuration dictionary.
     * @param $tree array<string, mixed> Canonical multi-dimensional configuration tree.
     *
     * @return void
     *
     * @throws ConfigWriteException
     */
    public function write_cache(string $cache_file, array $flat, array $tree = []): void
    {
        $dir = \dirname($cache_file);

        if (! \is_dir($dir) && ! @\mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new ConfigWriteException(\sprintf('Unable to create configuration cache directory: "%s"', $dir));
        }

        $payload = [
            'flat' => $flat,
            'tree' => $tree,
        ];

        $exported = \var_export($payload, true);
        $year_end = ((int) \date('Y')) + 2;

        $code = <<<PHP
<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - {$year_end} Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//
//> Auto-generated compiled configuration cache.
//> Pre-flattened O(1) hash table and tree cache. Generated automatically on change.

return {$exported};
PHP;

        $tmp = $cache_file . '.' . \bin2hex(\random_bytes(4)) . '.tmp';

        if (@\file_put_contents($tmp, $code, \LOCK_EX) === false) {
            throw new ConfigWriteException(\sprintf('Unable to write temporary cache file: "%s"', $tmp));
        }

        if (! @\rename($tmp, $cache_file)) {
            @\unlink($tmp);
            throw new ConfigWriteException(\sprintf('Unable to atomically rename cache file to: "%s"', $cache_file));
        }

        if (\function_exists('opcache_invalidate')) {
            @\opcache_invalidate($cache_file, true);
        }
    }

    /**
     * Recursively flattens an array tree into dot-notation paths while preserving branch arrays.
     *
     * @param $array array<string, mixed>
     * @param $prefix string
     *
     * @return array<string, mixed>
     */
    public static function flatten_tree(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $full_key = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (\is_array($value)) {
                $result[$full_key] = $value;
                $result += self::flatten_tree($value, $full_key);
            } else {
                $result[$full_key] = $value;
            }
        }

        return $result;
    }

    /**
     * Loads a PHP configuration file safely and returns its array content.
     *
     * @param $file_path string
     *
     * @return array<string, mixed>
     */
    protected function load_source_file(string $file_path): array
    {
        if (! \is_file($file_path)) {
            return [];
        }

        $content = require $file_path;

        return \is_array($content) ? $content : [];
    }

    /**
     * Wraps content under a dotted stem hierarchy.
     *
     * @param $stem string
     * @param $content array<string, mixed>
     *
     * @return array<string, mixed>
     */
    protected function expand_stem(string $stem, array $content): array
    {
        $parts = \explode('.', $stem);
        $tree = $content;

        for ($i = \count($parts) - 1; $i >= 0; $i--) {
            $tree = [$parts[$i] => $tree];
        }

        return $tree;
    }
}
