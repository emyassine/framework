<?php declare(strict_types=1);

namespace Webkernel\Http;

use Webkernel\Container\Container;
use Webkernel\Provider\PlatformProvider;

/**
 * Core HTTP provider. Declares kernel route files; modules own their own routes.
 */
final class CoreProvider extends PlatformProvider
{
    public function routes(): array
    {
        $file = dirname(__DIR__, 2).'/routes.php';

        return is_file($file) ? [$file] : [];
    }

    public function register(Container $container): void
    {
    }
}
