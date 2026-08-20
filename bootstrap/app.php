<?php declare(strict_types=1);

require __DIR__.'/../third_party/autoload.php';

webapp()->declare('providers', [
    Webkernel\View\ViewProvider::class,
    Webkernel\Route\RouteProvider::class,
]);

webapp()->boot();

webapp()->route()->view('/', 'dashboard', [
    'title' => 'Webkernel — Dashboard',
])->name('dashboard');
