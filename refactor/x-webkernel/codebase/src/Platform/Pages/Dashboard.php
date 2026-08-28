<?php declare(strict_types=1);

namespace Webkernel\Platform\Pages;

use Webkernel\Config\Config;
use Webkernel\View\View;

final class Dashboard
{
    /**
     * @return array{class: class-string, path: string, methods: list<string>}
     */
    public static function route(string $path = '/'): array
    {
        return ['class' => self::class, 'path' => $path, 'methods' => ['GET']];
    }

    public function __invoke(): string
    {
        return View::make('webkernel::pages.dashboard', [
            'title' => Config::get('app.name', 'Webkernel'),
            'favicon' => Config::get('branding.favicon'),
            'logo' => Config::get('branding.logo_light'),
            'theme' => Config::get('ui.dark_mode', true) ? 'dark' : 'light',
        ])->render();
    }
}
