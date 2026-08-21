<?php declare(strict_types=1);

namespace Webkernel\Route;

use Webkernel\PlatformProvider;
use Webkernel\WebApp;

final class RouteProvider extends PlatformProvider
{
    public function register(WebApp $app): void
    {
        // Host / extra.webkernel.routes declare route files. Inline bootstrap stays valid.
    }
}
