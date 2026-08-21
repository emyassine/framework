<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Closure;
use Webkernel\Acl\AuthorizationException;
use Webkernel\Auth\UserInterface;

final class AclComposable implements ComposableContract
{
    private ?string $module = null;

    private bool $on_the_fly;

    /** @var array<string, true> */
    private array $manifest = [];

    /** @var array<string, Closure> */
    private array $gates = [];

    private ?Closure $fallback = null;

    public function __construct()
    {
        $this->on_the_fly = ! webapp()->is_production();
    }

    public static function api_name(): string
    {
        return 'acl';
    }

    public static function container_lifetime(): string
    {
        return 'scoped';
    }

    public function __invoke(?string $module = null): self
    {
        return $module === null ? $this : $this->for_module($module);
    }

    public function for_module(?string $module): self
    {
        $clone = clone $this;
        $clone->module = $module;

        return $clone;
    }

    public function can(string $permission, mixed $resource = null): bool
    {
        $name = $this->qualify($permission);
        if (! isset($this->manifest[$name])) {
            if (! $this->on_the_fly) {
                return false;
            }
            $this->register_on_the_fly($name);
        }
        if (isset($this->gates[$name])) {
            return (bool) ($this->gates[$name])($name, webapp()->auth()->user(), $resource);
        }
        if ($this->fallback !== null) {
            return (bool) ($this->fallback)($name, webapp()->auth()->user());
        }

        return webapp()->auth()->check();
    }

    public function cannot(string $permission, mixed $resource = null): bool
    {
        return ! $this->can($permission, $resource);
    }

    public function authorize(string $permission, mixed $resource = null): void
    {
        if (! $this->can($permission, $resource)) {
            throw AuthorizationException::denied($this->qualify($permission));
        }
    }

    public function can_any(string|array ...$permissions): bool
    {
        $flat = [];
        foreach ($permissions as $permission) {
            if (is_array($permission)) {
                foreach ($permission as $item) {
                    if (is_string($item)) {
                        $flat[] = $item;
                    }
                }
                continue;
            }
            $flat[] = $permission;
        }
        foreach ($flat as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function enforce_component_access(string $component_id): bool
    {
        return $this->can($component_id);
    }

    public function enable_on_the_fly_creation(bool $enabled = true): void
    {
        $this->on_the_fly = $enabled;
    }

    public function is_on_the_fly_enabled(): bool
    {
        return $this->on_the_fly;
    }

    public function register_on_the_fly(string $permission_name, ?Closure $fallback_evaluator = null): void
    {
        $name = $this->qualify($permission_name);
        $this->manifest[$name] = true;
        if ($fallback_evaluator !== null) {
            $this->gates[$name] = $fallback_evaluator;
        }
    }

    public function set_on_the_fly_fallback(Closure $callback): void
    {
        $this->fallback = $callback;
    }

    public function register(string $permission_name): void
    {
        $this->manifest[$this->qualify($permission_name)] = true;
    }

    private function qualify(string $permission): string
    {
        if (str_contains($permission, '.')) {
            return $permission;
        }
        $module = $this->module ?? $this->inferred_module();

        return $module.'.'.$permission;
    }

    private function inferred_module(): string
    {
        try {
            $from_panel = webapp()->panel()->module_name();
            if (is_string($from_panel) && $from_panel !== '') {
                return $from_panel;
            }
        } catch (\Throwable) {
        }

        return 'platform';
    }
}
