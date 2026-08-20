<?php declare(strict_types=1);

require __DIR__.'/../third_party/autoload.php';

Route::view('/', 'dashboard', [
    'title' => 'Webkernel — Dashboard',
])->name('dashboard');
