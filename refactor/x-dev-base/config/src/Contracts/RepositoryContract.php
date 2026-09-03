<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Contracts;

/**
 * Interface contract for configuration repositories.
 */
interface RepositoryContract
{
    /**
     * @param $key string Dot-notation configuration key.
     * @param $default mixed Fallback value when key is absent.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * @param $key string Dot-notation configuration key.
     * @param $value mixed Value to store.
     *
     * @return static
     */
    public function set(string $key, mixed $value): static;

    /**
     * @param $key string Dot-notation configuration key.
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Returns the full multi-dimensional configuration tree.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Returns the compiled flat dot-notation dictionary.
     *
     * @return array<string, mixed>
     */
    public function all_flat(): array;

    /**
     * Merges an array tree into the existing configuration.
     *
     * @param $data array<string, mixed>
     *
     * @return static
     */
    public function merge(array $data): static;

    /**
     * Flushes all stored items from memory.
     *
     * @return static
     */
    public function flush(): static;
}
