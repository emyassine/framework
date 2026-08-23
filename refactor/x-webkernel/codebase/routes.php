<?php declare(strict_types=1);

use Webkernel\Route\Route;

Route::get('/', static function (): string {
    $elapsed = number_format((hrtime(true) - START_REQUEST) / 1e6, 2);

    return sprintf(<<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Webkernel</title>
</head>
<body>
    <h1>Bienvenue sur Webkernel</h1>
    <p>Ceci est une page HTML servie directement par ta closure.</p>
    <p>Temps de réponse: %s ms</p>
</body>
</html>
HTML, $elapsed);
});


Route::get('/healthz', static fn (): string => 'OK');
Route::get('/ready', static fn (): string => 'OK');
Route::get('/api', static fn (): string => json_encode(['status' => 'ok', 'version' => '1.0']));
Route::get('/api/v1', static fn (): string => json_encode(['version' => '1.0']));
Route::get('/api/posts', static fn (): string => json_encode(['posts' => []]));
Route::get('/rss', static fn (): string => 'RSS Feed');
Route::get('/atom', static fn (): string => 'Atom Feed');
Route::get('/llm.txt', static fn (): string => 'LLM Content');
