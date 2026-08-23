<?php declare(strict_types=1);

require __DIR__ . '/../platform/dependencies/packagist/autoload.php';

use Webkernel\Cache\CompilationStore;
use Webkernel\Container\Container;
use Webkernel\Http\RequestClassifier;

// Fast-path: Health checks bypass all framework overhead
$uri = $_SERVER['REQUEST_URI'] ?? '/';
if ($uri === '/healthz' || $uri === '/ready') {
    http_response_code(200);
    header('Content-Type: text/plain');
    exit('OK');
}

$container = Container::get_instance();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$handler = (new RequestClassifier())->classify($uri, $method);

// Compilation check — single call, same path for everything
$route_map = CompilationStore::get('webkernel.global.routes', $container);

$handler->handle($route_map, $container)->emit();
