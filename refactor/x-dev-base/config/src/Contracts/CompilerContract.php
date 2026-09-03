<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Contracts;

/**
 * Interface contract for compiling configuration sources into an optimized cache.
 */
interface CompilerContract
{
    /**
     * Compiles configuration source arrays into a flattened dictionary.
     *
     * @param $sources array<string, array<string, mixed>> Source arrays keyed by section stem.
     * @param $runtime_overrides array<string, mixed> Runtime modifications to apply over sources.
     *
     * @return array{flat: array<string, mixed>, tree: array<string, mixed>}
     */
    public function compile(array $sources, array $runtime_overrides = []): array;

    /**
     * Writes the compiled flat array to a cache file.
     *
     * @param $cache_file string Path to destination cache file.
     * @param $flat array<string, mixed> Flattened configuration dictionary.
     *
     * @return void
     */
    public function write_cache(string $cache_file, array $flat): void;
}
