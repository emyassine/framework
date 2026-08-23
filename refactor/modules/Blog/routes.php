<?php declare(strict_types=1);

use Webkernel\Route\Router as Route;

// Blog routes - style Laravel
Route::get('/blog', fn() => 'Blog index');
Route::get('/blog/posts', fn() => 'Blog posts list')->name('blog.posts.index');
Route::get('/blog/posts/{id}', fn() => 'Blog post detail')->name('blog.posts.show');
Route::post('/blog/posts', fn() => 'Create blog post')->name('blog.posts.store');
