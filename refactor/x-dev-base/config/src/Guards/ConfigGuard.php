<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Config\Guards;

use Webkernel\Config\Exceptions\ConfigGuardException;

/**
 * Maintains a set of key patterns that must never be mutated at runtime.
 *
 * Guards are matched with dot-notation exact keys OR dot-notation prefixes
 * (a guarded prefix "app" blocks "app.name", "app.env", etc.).
 *
 * Usage:
 *   $guard = new ConfigGuard(['app.key', 'platform.version']);
 *   $guard->assert('app.debug');   // fine
 *   $guard->assert('app.key');     // throws ConfigGuardException
 *
 * Designed to be injected into PlatformConfig — never instantiated globally.
 */
final class ConfigGuard
{
    /** @var list<string> */
    private array $protected_keys;

    /**
     * @param list<string> $protected_keys Exact keys or prefix segments to protect.
     */
    public function __construct(array $protected_keys = [])
    {
        $this->protected_keys = $protected_keys;
    }

    /**
     * Returns a new guard with additional keys appended (immutable composition).
     *
     * @param list<string> $keys
     */
    public function with_keys(array $keys): static
    {
        $clone = clone $this;
        $clone->protected_keys = \array_values(\array_unique([...$this->protected_keys, ...$keys]));
        return $clone;
    }

    /**
     * Asserts the given key is NOT protected, throws if it is.
     *
     * @throws ConfigGuardException
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
     * Returns true when the key is protected (non-throwing variant).
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

    /** @return list<string> */
    public function get_protected_keys(): array
    {
        return $this->protected_keys;
    }
}
