<?php declare(strict_types=1);

use Webkernel\Route\Router as Route;

// Routage fluide comme Laravel - SUPER RAPIDE

// Page d'accueil
Route::get('/', fn() => 'Bienvenue sur Webkernel');

// Health checks - déjà gérés dans public/index.php mais déclarés ici aussi
Route::get('/healthz', fn() => 'OK');
Route::get('/ready', fn() => 'OK');

// API
Route::get('/api', fn() => json_encode(['status' => 'ok', 'version' => '1.0']));
Route::get('/api/v1', fn() => json_encode(['version' => '1.0']));
Route::get('/api/posts', fn() => json_encode(['posts' => []]));

// Syndication
Route::get('/rss', fn() => 'RSS Feed');
Route::get('/atom', fn() => 'Atom Feed');

// Machine/AI endpoints
Route::get('/llm.txt', fn() => 'LLM Content');
