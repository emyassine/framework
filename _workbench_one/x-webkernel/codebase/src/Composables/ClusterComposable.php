<?php declare(strict_types=1);

namespace Webkernel\Composables;

final class ClusterComposable implements ComposableContract
{
    private string $name = '';

    /** @var array<string, list<string>> */
    private array $resources = [];

    public static function api_name(): string
    {
        return 'cluster';
    }

    public static function container_lifetime(): string
    {
        return 'bind';
    }

    public function __invoke(string $name): self
    {
        return $this->for_name($name);
    }

    public function for_name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function resources(): array
    {
        return $this->resources[$this->name] ?? [];
    }

    public function register_resource(string $class): void
    {
        $this->resources[$this->name] ??= [];
        if (! in_array($class, $this->resources[$this->name], true)) {
            $this->resources[$this->name][] = $class;
        }
    }
}
