<?php declare(strict_types=1);

namespace Webkernel\Platform\Pages;

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
        return View::make('webkernel::panels.system.dashboard')->render();
    }
}
