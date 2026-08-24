<?php declare(strict_types=1);

namespace Webkernel\Composables;

final class ResourceComposable implements ComposableContract
{
    private string $class = '';

    /** @var array<string, list<string>> */
    private array $pages = [];

    public static function api_name(): string
    {
        return 'resource';
    }

    public static function container_lifetime(): string
    {
        return 'bind';
    }

    public function __invoke(string $class): self
    {
        return $this->for_class($class);
    }

    public function for_class(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function pages(): array
    {
        return $this->pages[$this->class] ?? [];
    }

    /**
     * @return \ArrayIterator<int, mixed>
     */
    public function query(): \ArrayIterator
    {
        // ponytail: no ORM — empty iterator until a module persistence layer exists
        return new \ArrayIterator([]);
    }
}
