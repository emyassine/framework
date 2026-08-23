<?php declare(strict_types=1);

namespace Webkernel\Http;

use Webkernel\Provider\PlatformProvider;
use Webkernel\Container\Container;

/**
 * Core HTTP provider for Webkernel.
 * Registers essential HTTP services like the router.
 */
final class CoreProvider extends PlatformProvider
{
    /**
     * Core routes file.
     */
    public const ROUTES = [__DIR__ . '/../../../x-webkernel/codebase/routes.php'];

    /**
     * Register container bindings.
     */
    public function register(Container $container): void
    {
        // Register the router
        $container->singleton(\Webkernel\Router\Router::class, function () {
            return new \Webkernel\Router\Router();
        });
    }

    /**
     * Boot the provider.
     */
    public function boot(Container $container): void
    {
        // Nothing to do on boot for now
    }
}
