<?php declare(strict_types=1);

namespace Webkernel\App;

use Webkernel\Container\Container;
use Webkernel\Cache\CompilationStore;

/**
 * Webkernel Application singleton.
 * Provides global access to the application instance and compiled configuration.
 */
final class Application
{
    private static ?self $instance = null;

    private Container $container;

    /** @var array<mixed> */
    private array $compiled_config = [];

    /**
     * Create a new Application instance.
     */
    private function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get or create the singleton application instance.
     */
    public static function get_instance(): self
    {
        if (self::$instance === null) {
            $container = Container::get_instance();
            self::$instance = new self($container);
        }
        return self::$instance;
    }

    /**
     * Set the application instance (useful for testing).
     */
    public static function set_instance(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Get the container.
     */
    public function get_container(): Container
    {
        return $this->container;
    }

    /**
     * Get configuration value by dot notation key.
     * If key is null, returns entire config array.
     */
    public function config(string $key = null, mixed $default = null): mixed
    {
        if ($this->compiled_config === []) {
            $this->compiled_config = CompilationStore::get(
                'webkernel.global.config',
                $this->container
            );
        }

        if ($key === null) {
            return $this->compiled_config;
        }

        return $this->resolve_dot($this->compiled_config, $key, $default);
    }

    /**
     * Resolve a dot-notation key in the config array.
     */
    private function resolve_dot(array $data, string $key, mixed $default): mixed
    {
        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }
}
