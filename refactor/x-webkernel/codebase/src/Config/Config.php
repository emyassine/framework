<?php declare(strict_types=1);

namespace Webkernel\Config;

/**
 * In-memory config after boot. Runtime writes go to platform/platform-runtime.php.
 *
 * @method mixed get(string $key, mixed $default = null)
 */
final class Config
{
    private static ?self $instance = null;

    /** @var array<string, mixed> */
    private array $tree = [];

    private bool $booted = false;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function boot(?string $root = null): self
    {
        return self::instance()->do_boot($root);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::instance()->do_get($key, $default);
    }

    public static function set(string $key, mixed $value): self
    {
        return self::instance()->do_set($key, $value);
    }

    public static function flush(): void
    {
        if (self::$instance === null) {
            return;
        }
        self::$instance->tree = [];
        self::$instance->booted = false;
        self::$instance = null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        self::instance()->ensure_booted();

        return self::instance()->tree;
    }

    /** @param $arguments */
    public function __call(string $name, array $arguments): mixed
    {
        if ($name === 'get') {
            return self::get(...$arguments);
        }
        if ($name === 'set') {
            return self::set(...$arguments);
        }
        if ($name === 'all') {
            return self::all();
        }

        throw new \BadMethodCallException('Config::'.$name.' is not defined.');
    }

    private function do_boot(?string $root = null): self
    {
        $root ??= webapp_path();
        $tree = [];
        foreach (['config/platform.php', 'config/app.php'] as $rel) {
            $tree = \array_replace_recursive($tree, self::require_array($root.'/'.$rel));
        }
        $tree = \array_replace_recursive($tree, self::require_array($root.'/platform/platform-runtime.php'));
        $this->tree = $tree;
        $this->booted = true;

        return $this;
    }

    private function do_get(string $key, mixed $default = null): mixed
    {
        $this->ensure_booted();
        $cursor = $this->tree;
        foreach (\explode('.', $key) as $part) {
            if (! \is_array($cursor) || ! \array_key_exists($part, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$part];
        }

        return $cursor;
    }

    private function do_set(string $key, mixed $value): self
    {
        $this->ensure_booted();
        $runtime = webapp_path('platform/platform-runtime.php');
        $current = self::require_array($runtime);
        $next = \array_replace_recursive($current, self::dot_to_tree($key, $value));
        ConfigWriter::write($runtime, $next);
        $this->set_dot($this->tree, $key, $value);

        return $this;
    }

    private function ensure_booted(): void
    {
        if (! $this->booted) {
            $this->do_boot();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function require_array(string $file): array
    {
        if (! \is_file($file)) {
            return [];
        }
        $loaded = require $file;

        return \is_array($loaded) ? $loaded : [];
    }

    /**
     * @param array<string, mixed> $tree
     */
    private function set_dot(array &$tree, string $key, mixed $value): void
    {
        $parts = \explode('.', $key);
        $cursor = &$tree;
        $last = \array_key_last($parts);
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

    /**
     * @return array<string, mixed>
     */
    private static function dot_to_tree(string $key, mixed $value): array
    {
        $tree = $value;
        foreach (\array_reverse(\explode('.', $key)) as $part) {
            $tree = [$part => $tree];
        }

        /** @var array<string, mixed> $tree */
        return $tree;
    }
}
