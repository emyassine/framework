<?php declare(strict_types=1);

use Webkernel\Route\Route;

Route::group(['prefix' => '/blog'], static function () {

    Route::get('/', static fn (): string => 'Blog index');

    Route::get('/posts', static fn (): string => 'Blog posts list')->name('blog.posts.index');

    Route::get('/posts/{id}', static fn (string $id): string =>
        'Blog post ' . $id . ' detail in ' .
        number_format((hrtime(true) - START_REQUEST) / 1e6, 2) . ' ms'
    )->name('blog.posts.show');

});
