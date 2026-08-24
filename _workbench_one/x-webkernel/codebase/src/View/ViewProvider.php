<?php declare(strict_types=1);

namespace Webkernel\View;

use Webkernel\Container\Container;
use Webkernel\Provider\PlatformProvider;
use Webkernel\WebApp;

final class ViewProvider extends PlatformProvider
{
    public function views(): array
    {
        return [__DIR__.'/views'];
    }

    public function register(Container $container): void
    {
        $app = WebApp::get();
        $views = __DIR__.'/views';
        $app->declare_view('', $views);
        $app->declare_view('webkernel', $views);
        $app->declare_component('webkernel', $views.'/components');
    }
}
