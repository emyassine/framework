<?php declare(strict_types=1);
use Webkernel\Route\Route;

Route::view('/', 'dashboard', [
    'title' => 'Webkernel — Dashboard',
    ])->name('dashboard');
