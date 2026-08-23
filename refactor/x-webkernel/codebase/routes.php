<?php declare(strict_types=1);

use Webkernel\Route\Route;

Route::get('/', static fn (): string => 'Webkernel');
Route::get('/healthz', static fn (): string => 'OK');
Route::get('/ready', static fn (): string => 'OK');
Route::get('/api', static fn (): string => json_encode(['status' => 'ok', 'version' => '1.0']));
Route::get('/api/v1', static fn (): string => json_encode(['version' => '1.0']));
Route::get('/api/posts', static fn (): string => json_encode(['posts' => []]));
Route::get('/rss', static fn (): string => 'RSS Feed');
Route::get('/atom', static fn (): string => 'Atom Feed');
Route::get('/llm.txt', static fn (): string => 'LLM Content');
