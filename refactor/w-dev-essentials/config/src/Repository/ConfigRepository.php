<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Repository;

use Webkernel\Config\Compiler\ConfigCompiler;
use Webkernel\Config\Contracts\RepositoryContract;
use Webkernel\Config\Guards\ConfigGuard;

/**
 * High-performance dual-state configuration repository.
 *
 * Maintains a pre-flattened dictionary for O(1) hash table queries alongside
 * the canonical multi-dimensional tree for hierarchy inspection.
 */
class ConfigRepository implements RepositoryContract
{
    /** @var array<string, mixed> Pre-flattened key dictionary */
    protected array $items = [];

    /** @var array<string, mixed> Multi-dimensional configuration tree */
    protected array $tree = [];

    /**
     * @param $tree array<string, mixed> Initial multi-dimensional tree.
     * @param $items array<string, mixed> Optional pre-compiled flat dictionary.
     * @param $guard ConfigGuard|null Key protection validator.
     */
    public function __construct(
        array $tree = [],
        array $items = [],
        protected ?ConfigGuard $guard = null,
    ) {
        $this->tree = $tree;
        $this->items = $items !== [] ? $items : ConfigCompiler::flatten_tree($tree);
    }

    /**
     * Retrieves a configuration value by dot-notation key.
     *
     * @param $key string Dot-notation configuration key.
     * @param $default mixed Fallback value when key is absent.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        if (\array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * Determines whether a configuration key exists in the repository.
     *
     * @param $key string Dot-notation configuration key.
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->items[$key]) || \array_key_exists($key, $this->items);
    }

    /**
     * Sets a configuration value in both flat dictionary and nested tree.
     *
     * @param $key string Dot-notation configuration key.
     * @param $value mixed Value to assign.
     *
     * @return static
     *
     * @throws \Webkernel\Config\Exceptions\ConfigGuardException
     */
    public function set(string $key, mixed $value): static
    {
        $this->guard?->assert($key);

        $this->items[$key] = $value;

        // If the value is an array, flatten its children
        if (\is_array($value)) {
            $flattened_children = ConfigCompiler::flatten_tree($value, $key);
            foreach ($flattened_children as $child_key => $child_value) {
                $this->items[$child_key] = $child_value;
            }
        }

        // Update the multi-dimensional tree
        $this->set_tree_dot($key, $value);

        return $this;
    }

    /**
     * Returns the full multi-dimensional configuration tree.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->tree;
    }

    /**
     * Returns the compiled flat dot-notation dictionary.
     *
     * @return array<string, mixed>
     */
    public function all_flat(): array
    {
        return $this->items;
    }

    /**
     * Merges an array tree into the existing configuration state.
     *
     * @param $data array<string, mixed>
     *
     * @return static
     */
    public function merge(array $data): static
    {
        $this->tree = \array_replace_recursive($this->tree, $data);
        $this->items = ConfigCompiler::flatten_tree($this->tree);

        return $this;
    }

    /**
     * Replaces both the flat dictionary and tree atomically.
     *
     * @param $tree array<string, mixed>
     * @param $flat array<string, mixed>
     *
     * @return static
     */
    public function replace(array $tree, array $flat): static
    {
        $this->tree = $tree;
        $this->items = $flat;

        return $this;
    }

    /**
     * Attaches or removes a configuration guard.
     *
     * @param $guard ConfigGuard|null
     *
     * @return static
     */
    public function set_guard(?ConfigGuard $guard): static
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * Returns the active configuration guard instance.
     *
     * @return ConfigGuard|null
     */
    public function get_guard(): ?ConfigGuard
    {
        return $this->guard;
    }

    /**
     * Flushes all stored items and resets repository state.
     *
     * @return static
     */
    public function flush(): static
    {
        $this->items = [];
        $this->tree = [];

        return $this;
    }

    /**
     * Mutates the multi-dimensional tree using dot-notation segments.
     *
     * @param $key string
     * @param $value mixed
     *
     * @return void
     */
    protected function set_tree_dot(string $key, mixed $value): void
    {
        $parts = \explode('.', $key);
        $cursor = &$this->tree;
        $last_index = \count($parts) - 1;

        foreach ($parts as $i => $part) {
            if ($i === $last_index) {
                $cursor[$part] = $value;
                return;
            }

            if (! isset($cursor[$part]) || ! \is_array($cursor[$part])) {
                $cursor[$part] = [];
            }

            $cursor = &$cursor[$part];
        }
    }
}
