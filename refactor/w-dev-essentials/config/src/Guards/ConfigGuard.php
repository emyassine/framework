<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Config\Guards;

use Webkernel\Config\Exceptions\ConfigGuardException;

/**
 * Enforces immutability on protected configuration keys and prefixes.
 *
 * Exact keys and prefix hierarchies are guarded against runtime mutations.
 * A guarded prefix "platform" blocks "platform", "platform.id", "platform.debug", etc.
 */
final class ConfigGuard
{
    /** @var list<string> */
    private array $protected_keys;

    /**
     * @param $protected_keys list<string> Exact keys or prefix segments to protect.
     */
    public function __construct(array $protected_keys = [])
    {
        $this->protected_keys = \array_values(\array_unique($protected_keys));
    }

    /**
     * Returns a new guard instance with additional keys merged immutably.
     *
     * @param $keys list<string>
     *
     * @return static
     */
    public function with_keys(array $keys): static
    {
        $clone = clone $this;
        $clone->protected_keys = \array_values(\array_unique([...$this->protected_keys, ...$keys]));

        return $clone;
    }

    /**
     * Asserts that the given key is permitted to be mutated.
     *
     * @param $key string Dot-notation configuration key.
     *
     * @return void
     *
     * @throws ConfigGuardException When the key or its prefix is protected.
     */
    public function assert(string $key): void
    {
        foreach ($this->protected_keys as $guard) {
            if ($key === $guard || \str_starts_with($key, $guard . '.')) {
                throw new ConfigGuardException($key);
            }
        }
    }

    /**
     * Determines whether a key matches any protected pattern.
     *
     * @param $key string Dot-notation configuration key.
     *
     * @return bool
     */
    public function is_protected(string $key): bool
    {
        foreach ($this->protected_keys as $guard) {
            if ($key === $guard || \str_starts_with($key, $guard . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the list of registered protection rules.
     *
     * @return list<string>
     */
    public function get_protected_keys(): array
    {
        return $this->protected_keys;
    }
}
