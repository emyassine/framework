<?php declare(strict_types=1);

namespace Webkernel\Container\Manifest;

final readonly class ContainerManifest
{
    /**
     * @param array<string, array<string, mixed>> $modules
     * @param array<string, string> $bindings
     * @param array<string, string> $lazyProxies
     * @param array<string, int> $mtimes
     */
    public function __construct(
        public array $modules = [],
        public array $bindings = [],
        public array $lazyProxies = [],
        public array $mtimes = [],
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            modules: self::arrayOfArrays($payload['modules'] ?? []),
            bindings: self::stringMap($payload['bindings'] ?? []),
            lazyProxies: self::stringMap($payload['lazy_proxies'] ?? []),
            mtimes: self::intMap($payload['mtimes'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'modules' => $this->modules,
            'bindings' => $this->bindings,
            'lazy_proxies' => $this->lazyProxies,
            'mtimes' => $this->mtimes,
        ];
    }

    /**
     * @param mixed $value
     *
     * @return array<string, array<string, mixed>>
     */
    private static function arrayOfArrays(mixed $value): array
    {
        if (! \is_array($value)) {
            return [];
        }

        return \array_filter($value, static fn(mixed $item): bool => \is_array($item));
    }

    /**
     * @param mixed $value
     *
     * @return array<string, string>
     */
    private static function stringMap(mixed $value): array
    {
        if (! \is_array($value)) {
            return [];
        }

        $strings = [];

        foreach ($value as $key => $item) {
            if (\is_string($key) && \is_string($item)) {
                $strings[$key] = $item;
            }
        }

        return $strings;
    }

    /**
     * @param mixed $value
     *
     * @return array<string, int>
     */
    private static function intMap(mixed $value): array
    {
        if (! \is_array($value)) {
            return [];
        }

        $integers = [];

        foreach ($value as $key => $item) {
            if (\is_string($key) && \is_int($item)) {
                $integers[$key] = $item;
            }
        }

        return $integers;
    }
}
