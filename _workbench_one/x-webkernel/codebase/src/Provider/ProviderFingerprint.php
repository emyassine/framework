<?php declare(strict_types=1);

namespace Webkernel\Provider;

/**
 * Generates stable fingerprints for providers.
 * Each module gets a fingerprint — a deterministic identifier derived from its
 * fully-qualified class name. The fingerprint is the namespace key for all APCu
 * entries owned by that module.
 */
final class ProviderFingerprint
{
    /**
     * Derive a stable fingerprint from the provider's fully-qualified class name.
     * Result: 12-char hex, e.g. "a3f2c801b4d5"
     * Uses namespace + class name to minimize collision risk.
     * Stable across deploys as long as the class name doesn't change.
     */
    public static function for(string $providerClass): string
    {
        return substr(hash('xxh3', $providerClass), 0, 12);
    }

    /**
     * Build a namespaced APCu key for a given artifact type owned by a provider.
     * e.g. "webkernel.a3f2c801b4d5.routes"
     */
    public static function cache_key(string $providerClass, string $artifact): string
    {
        return 'webkernel.' . self::for($providerClass) . '.' . $artifact;
    }

    /**
     * Build a global APCu key for shared artifacts.
     */
    public static function global_key(string $artifact): string
    {
        return 'webkernel.global.' . $artifact;
    }
}
