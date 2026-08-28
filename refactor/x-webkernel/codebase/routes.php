<?php declare(strict_types=1);

use Webkernel\Config\Config;
use Webkernel\Route\Route;
use Webkernel\View\View;

Route::get('/', static function (): string {
    return View::make('webkernel::pages.home', [
        'title' => Config::get('app.name', 'Webkernel'),
        'theme' => Config::get('ui.dark_mode', true) ? 'dark' : 'light',
    ])->render();
});
