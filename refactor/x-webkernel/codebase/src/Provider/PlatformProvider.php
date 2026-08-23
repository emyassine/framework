<?php declare(strict_types=1);

namespace Webkernel\Provider;

use Webkernel\Container\Container;

/**
 * Abstract base class for all Webkernel providers.
 * Providers declare their capabilities through constants or methods.
 * The compiler resolves these declarations at compile time.
 */
abstract class PlatformProvider
{
    /**
     * Register bindings into the container before anything boots.
     * Always called, always first.
     */
    public function register(Container $container): void
    {
    }

    /**
     * Boot logic after all providers are registered.
     * Safe to resolve services here.
     */
    public function boot(Container $container): void
    {
    }

    // -------------------------------------------------------------------------
    // Declaration methods — all return arrays of class names, file paths, or both.
    // The compiler resolves everything. Providers just declare.
    // Return [] to opt out. All are optional — override only what you need.
    // -------------------------------------------------------------------------

    /**
     * Blade/template composables: view composers, shared data, etc.
     */
    public function composables(): array
    {
        return [];
    }

    /**
     * View template directories or individual view class paths.
     */
    public function views(): array
    {
        return [];
    }

    /**
     * Nested providers this provider depends on or delegates to.
     */
    public function providers(): array
    {
        return [];
    }

    /**
     * Route files or route class paths.
     */
    public function routes(): array
    {
        return [];
    }

    /**
     * Arbitrary files to autoload or publish (migrations, stubs, assets).
     */
    public function files(): array
    {
        return [];
    }

    /**
     * CLI command classes or paths to scan for commands.
     */
    public function commands(): array
    {
        return [];
    }

    /**
     * Explicit class map entries: ['ClassName' => '/path/to/Class.php']
     */
    public function classmap(): array
    {
        return [];
    }

    /**
     * Admin panel definitions or panel provider class paths.
     */
    public function panels(): array
    {
        return [];
    }

    /**
     * Configuration array (dot-notation keys).
     */
    public function config(): array
    {
        return [];
    }

    /**
     * Access control list rules.
     */
    public function acl(): array
    {
        return [];
    }
}
