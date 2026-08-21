<?php declare(strict_types=1);

namespace Webkernel\Composables;

use Webkernel\Panel\AdminPanel;

final class PanelComposable implements ComposableContract
{
    private ?string $id = null;

    /** @var array<string, AdminPanel> */
    private static array $registry = [];

    private static ?string $active_id = null;

    public static function flush(): void
    {
        self::$registry = [];
        self::$active_id = null;
    }

    public static function api_name(): string
    {
        return 'panel';
    }

    public static function container_lifetime(): string
    {
        return 'scoped';
    }

    public function __invoke(?string $id = null): self
    {
        return $id === null ? $this : $this->for_id($id);
    }

    public function for_id(?string $id): self
    {
        $clone = clone $this;
        $clone->id = $id === null ? null : $this->qualify_id($id);

        return $clone;
    }

    /**
     * @param 'platform'|'module' $scope
     */
    public function register(string $id, string $scope, ?string $module_name = null, array $clusters = []): void
    {
        if ($scope !== 'platform' && $scope !== 'module') {
            throw new \InvalidArgumentException('Panel ['.$id.'] must declare scope platform|module.');
        }
        if ($scope === 'module' && ($module_name === null || $module_name === '')) {
            throw new \InvalidArgumentException('Module panel ['.$id.'] must declare a module name.');
        }
        self::$registry[$id] = new AdminPanel($id, $scope, $module_name, $clusters);
        self::$active_id ??= $id;
    }

    public function activate(string $id): void
    {
        $resolved = $this->qualify_id($id);
        if (! isset(self::$registry[$resolved])) {
            throw new \InvalidArgumentException('Unknown panel ['.$id.'].');
        }
        self::$active_id = $resolved;
        $this->id = $resolved;
    }

    public function type(): string
    {
        return $this->current()->scope;
    }

    public function is_platform_panel(): bool
    {
        return $this->type() === 'platform';
    }

    public function is_module_panel(): bool
    {
        return $this->type() === 'module';
    }

    public function module_name(): ?string
    {
        return $this->current()->module_name;
    }

    public function current(): AdminPanel
    {
        $id = $this->id ?? self::$active_id;
        if ($id !== null && isset(self::$registry[$id])) {
            return self::$registry[$id];
        }
        if (self::$registry !== []) {
            return reset(self::$registry);
        }

        $this->register('platform.system_admin', 'platform');
        self::$active_id = 'platform.system_admin';

        return self::$registry['platform.system_admin'];
    }

    /**
     * @return list<string>
     */
    public function clusters(): array
    {
        return $this->current()->clusters;
    }

    private function qualify_id(string $id): string
    {
        if (str_contains($id, '.')) {
            return $id;
        }
        $module = self::$active_id !== null && isset(self::$registry[self::$active_id])
            ? self::$registry[self::$active_id]->module_name
            : null;
        if (is_string($module) && $module !== '') {
            return $module.'.'.$id;
        }

        return $id;
    }
}
