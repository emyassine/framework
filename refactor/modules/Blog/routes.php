<?php declare(strict_types=1);

use Webkernel\Route\Route;

Route::get('/blog', static fn (): string => 'Blog index');
Route::get('/blog/posts', static fn (): string => 'Blog posts list')->name('blog.posts.index');
Route::get('/blog/posts/{id}', static fn (): string => 'Blog post detail')->name('blog.posts.show');
Route::post('/blog/posts', static fn (): string => 'Create blog post')->name('blog.posts.store');
