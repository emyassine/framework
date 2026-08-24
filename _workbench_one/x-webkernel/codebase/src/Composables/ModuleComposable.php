<?php declare(strict_types=1);

namespace Webkernel\Composables;

final class ModuleComposable implements ComposableContract
{
    private ?string $name = null;

    /** @var array<string, string> */
    private array $registered = [];

    public static function api_name(): string
    {
        return 'module';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    public function __invoke(?string $name = null): self
    {
        return $name === null ? $this : $this->for_name($name);
    }

    public function for_name(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->registered;
    }

    public function is_installed(string $name): bool
    {
        return isset($this->registered[$name]);
    }

    public function register(string $module_class): void
    {
        $name = $this->name;
        if ($name === null || $name === '') {
            $slash = strrpos($module_class, '\\');
            $name = strtolower($slash === false ? $module_class : substr($module_class, $slash + 1));
        }
        $this->registered[$name] = $module_class;
    }

    public function config(?string $key = null, mixed $default = null): mixed
    {
        $name = $this->name;
        if ($name === null || $name === '') {
            return $key === null ? [] : $default;
        }
        $prefix = 'modules.'.$name;
        if ($key === null) {
            $value = webapp()->config($prefix, []);

            return is_array($value) ? $value : [];
        }

        return webapp()->config($prefix.'.'.$key, $default);
    }
}
