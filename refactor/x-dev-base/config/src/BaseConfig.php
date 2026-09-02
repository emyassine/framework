<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Config;

/**
 * Base key-value configuration tree container.
 *
 * Handles dot-notation retrieval, mutation, and file loading.
 * Contains zero path-resolution or boot logic — that belongs in PlatformConfig.
 */
class BaseConfig
{
    /** @var array<string, mixed> */
    protected array $tree = [];

    protected bool $booted = false;

    public function __construct(
        protected string $root_path = ''
    ) {
        if ($this->root_path !== '') {
            $this->root_path = \rtrim($this->root_path, '/\\');
        }
    }

    public function get_root_path(): string
    {
        return $this->root_path;
    }

    public function is_booted(): bool
    {
        return $this->booted;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $cursor = $this->tree;
        foreach (\explode('.', $key) as $part) {
            if (! \is_array($cursor) || ! \array_key_exists($part, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$part];
        }
        return $cursor;
    }

    public function has(string $key): bool
    {
        $cursor = $this->tree;
        foreach (\explode('.', $key) as $part) {
            if (! \is_array($cursor) || ! \array_key_exists($part, $cursor)) {
                return false;
            }
            $cursor = $cursor[$part];
        }
        return true;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->tree;
    }

    /** @param array<string, mixed> $tree */
    public function merge(array $tree): static
    {
        $this->tree = \array_replace_recursive($this->tree, $tree);
        return $this;
    }

    public function flush(): static
    {
        $this->tree  = [];
        $this->booted = false;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<string, mixed> */
    protected static function require_array(string $file_path): array
    {
        if (! \is_file($file_path)) {
            return [];
        }
        $loaded = require $file_path;
        return \is_array($loaded) ? $loaded : [];
    }

    /** @param array<string, mixed> $tree */
    protected function set_dot(array &$tree, string $key, mixed $value): void
    {
        $parts  = \explode('.', $key);
        $cursor = &$tree;
        $last   = \array_key_last($parts);

        foreach ($parts as $i => $part) {
            if ($i === $last) {
                $cursor[$part] = $value;
                return;
            }
            if (! isset($cursor[$part]) || ! \is_array($cursor[$part])) {
                $cursor[$part] = [];
            }
            $cursor = &$cursor[$part];
        }
    }

    /** @return array<string, mixed> */
    protected static function dot_to_tree(string $key, mixed $value): array
    {
        $tree = $value;
        foreach (\array_reverse(\explode('.', $key)) as $part) {
            $tree = [$part => $tree];
        }
        /** @var array<string, mixed> $tree */
        return $tree;
    }
}
