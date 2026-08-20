<?php declare(strict_types=1);

namespace Webkernel\View;

use Webkernel\PlatformProvider;
use Webkernel\WebApp;

final class ViewProvider extends PlatformProvider
{
    public function register(WebApp $app): void
    {
        $views = dirname(__DIR__).'/views';
        $app->declare_view('', $views);
        $app->declare_view('webkernel', $views);
        $app->declare_component('webkernel', $views.'/components');
    }
}
