<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Data;

/**
 * Insertion-ordered, duplicate-free string collection.
 *
 * Replaces the `array<string, true>` "hash set" pattern with a typed,
 * self-explanatory collection instead of relying on array_keys() everywhere.
 */
final class EntrySet
{
    /** @var array<string, true> */
    private array $seen = [];

    public function add(string $value): void
    {
        if ($value === '') {
            return;
        }
        $this->seen[$value] = true;
    }

    public function has(string $value): bool
    {
        return isset($this->seen[$value]);
    }

    /** @return list<string> */
    public function values(): array
    {
        return array_keys($this->seen);
    }
}
