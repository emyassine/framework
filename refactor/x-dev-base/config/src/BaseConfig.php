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
        $this->tree   = [];
        $this->booted = false;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Require a PHP file and return its array value, or [] if missing/invalid.
     *
     * @return array<string, mixed>
     */
    protected static function require_array(string $file_path): array
    {
        if (! \is_file($file_path)) {
            return [];
        }
        $loaded = require $file_path;
        return \is_array($loaded) ? $loaded : [];
    }

    /**
     * Require a PHP config file and wrap its contents under the file's stem key.
     *
     * e.g. "config/platform.php" → ['platform' => [...contents...]]
     *      "config/platform-paths.php" → ['platform-paths' => [...contents...]]
     *
     * This ensures dot-notation access always starts with the filename:
     *   config('platform.id'), config('app.name'), config('database.connections.mysql.host')
     *
     * Pass $stem explicitly to override the auto-derived key (useful for
     * runtime-override files that must be merged flat, like platform-runtime.php).
     *
     * @param  string      $file_path  Absolute path to the PHP config file.
     * @param  string|null $stem       Override key; null = derived from basename.
     * @return array<string, mixed>    Always a single-key array: [$stem => $contents].
     */
    protected static function require_file_under_key(string $file_path, ?string $stem = null): array
    {
        $contents = self::require_array($file_path);
        if (empty($contents)) {
            return [];
        }

        if ($stem === null) {
            // Derive stem: strip directory + all extensions
            // "config/platform.php"       → "platform"
            // "config/platform-paths.php" → "platform-paths"
            $basename = \basename($file_path);
            $stem     = \strstr($basename, '.', before_needle: true) ?: $basename;
        }

        return [$stem => $contents];
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
