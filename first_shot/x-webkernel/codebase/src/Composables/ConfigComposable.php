<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Webkernel\Config\ConfigWriter;
use Webkernel\Instance\InstanceId;

/**
 * First primitive resolved by webapp(). Plain PHP array, merged once at load.
 * Reads are in-memory key lookups. Writes go through ConfigWriter.
 */
final class ConfigComposable implements ComposableContract
{
    /**
     * @param array<string, mixed> $tree
     */
    public function __construct(
        private array $tree,
        private readonly string $platform_file,
    ) {
    }

    public static function api_name(): string
    {
        return 'config';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    public static function load(?string $webapp_root = null): self
    {
        $root = $webapp_root ?? webapp_path();
        $platform_file = $root.'/config/platform.php';
        $tree = self::require_array($platform_file);

        foreach (glob($root.'/config/platform/*.php') ?: [] as $extra) {
            $tree = array_replace_recursive($tree, self::require_array($extra));
        }

        $overrides = $root.'/platform/platform-overrides.php';
        if (is_file($overrides)) {
            $tree = array_replace_recursive($tree, self::require_array($overrides));
        }

        $modules_rel = $tree['modules']['path'] ?? 'modules';
        if (is_string($modules_rel) && $modules_rel !== '' && ! str_contains($modules_rel, '..')) {
            $modules_dir = $root.'/'.$modules_rel;
            if (is_dir($modules_dir)) {
                $entries = scandir($modules_dir);
                if (is_array($entries)) {
                    foreach ($entries as $name) {
                        if ($name === '.' || $name === '..' || ! is_dir($modules_dir.'/'.$name)) {
                            continue;
                        }
                        $file = $modules_dir.'/'.$name.'/config/'.$name.'.php';
                        $chunk = self::require_array($file);
                        if ($chunk !== []) {
                            $tree = array_replace_recursive($tree, ['modules' => [$name => $chunk]]);
                        }
                    }
                }
            }
        }

        return new self($tree, $platform_file);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $cursor = $this->tree;
        foreach (explode('.', $key) as $part) {
            if (! is_array($cursor) || ! array_key_exists($part, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$part];
        }

        return $cursor;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->tree;
    }

    public function has(string $key): bool
    {
        $cursor = $this->tree;
        foreach (explode('.', $key) as $part) {
            if (! is_array($cursor) || ! array_key_exists($part, $cursor)) {
                return false;
            }
            $cursor = $cursor[$part];
        }

        return true;
    }

    public function set(string $key, mixed $value): void
    {
        $this->guard_module_write($key);
        $this->set_dot($this->tree, $key, $value);
        ConfigWriter::atomic_rewrite($this->platform_file, $this->dot_to_tree($key, $value));
    }

    /**
     * @param array<string, mixed> $values
     */
    public function set_many(array $values): void
    {
        $writes = [];
        foreach ($values as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            $this->guard_module_write($key);
            $this->set_dot($this->tree, $key, $value);
            $writes = array_replace_recursive($writes, $this->dot_to_tree($key, $value));
        }
        if ($writes !== []) {
            ConfigWriter::atomic_rewrite($this->platform_file, $writes);
        }
    }

    public function reload(): void
    {
        $fresh = self::load(dirname($this->platform_file, 2));
        $this->tree = $fresh->tree;
    }

    /** CLI / dump-autoload only. Never on the HTTP hot path. */
    public function stamp_identity(): void
    {
        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            return;
        }
        $host = (string) gethostname();
        $stored = (string) $this->get('hostname', '');
        if ($stored !== '' && $stored === $host) {
            return;
        }
        $parts = InstanceId::parts(webapp_path());
        $writes = [
            'hostname' => $parts['host'],
            'ip' => $parts['ip'],
            'uuid' => $parts['machine_uuid'],
            'macs' => $parts['macs'],
            'instance_file_path' => 'platform/storage/instance',
        ];
        $id = $this->get('id');
        if (! is_string($id) || $id === '') {
            $writes['id'] = $parts['fingerprint'];
        }
        $created = $this->get('created');
        if (! is_string($created) || $created === '') {
            $writes['created'] = gmdate('c');
        }
        $this->set_many($writes);
    }

    /**
     * @return array<string, mixed>
     */
    private static function require_array(string $file): array
    {
        if (! is_file($file)) {
            return [];
        }
        $loaded = require $file;

        return is_array($loaded) ? $loaded : [];
    }

    private function guard_module_write(string $key): void
    {
        if (str_starts_with($key, 'modules.')) {
            throw new \RuntimeException('Module config is read-only at runtime.');
        }
    }

    /**
     * @param array<string, mixed> $tree
     */
    private function set_dot(array &$tree, string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $cursor = &$tree;
        $last = array_key_last($parts);
        foreach ($parts as $i => $part) {
            if ($i === $last) {
                $cursor[$part] = $value;

                return;
            }
            if (! isset($cursor[$part]) || ! is_array($cursor[$part])) {
                $cursor[$part] = [];
            }
            $cursor = &$cursor[$part];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function dot_to_tree(string $key, mixed $value): array
    {
        $parts = array_reverse(explode('.', $key));
        $tree = $value;
        foreach ($parts as $part) {
            $tree = [$part => $tree];
        }

        /** @var array<string, mixed> $tree */
        return $tree;
    }
}
