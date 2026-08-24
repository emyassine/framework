<?php declare(strict_types=1);

$root = dirname(__DIR__, 4);
require $root.'/platform/bootstrap/fast-boot.php';

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
http_response_code(200);

$container = \Webkernel\Container\Container::get_instance();
$map = \Webkernel\Cache\CompilationStore::get('webkernel.global.routes', $container);
expect(is_array($map) && isset($map[0]['GET']['/']), 'compiled static GET /');

$handler = $map[0]['GET']['/'][0];
expect($handler instanceof Closure, 'compiled GET / is a closure');
expect($handler() === 'Webkernel', 'compiled GET / invokes');

$compiled = \Webkernel\Route\Compile\Cache::path();
expect(is_file($compiled), 'compiled_routes.php exists');
$payload = include $compiled;
expect(is_array($payload) && isset($payload['data'][0]['GET']['/']), 'payload data');
expect(\Webkernel\Cache\CompilationManifest::is_stale() === false, 'manifest fresh after compile');

http_response_code(200);
ob_start();
\Webkernel\Http\Handler\RouteResponse::from_map($map, 'GET')->emit();
$body = ob_get_clean();
expect($body === 'Webkernel', 'from_map GET /');

$_SERVER['REQUEST_URI'] = '/blog/posts/42';
http_response_code(200);
ob_start();
\Webkernel\Http\Handler\RouteResponse::from_map($map, 'GET')->emit();
$blog = ob_get_clean();
expect($blog === 'Blog post detail', 'from_map dynamic blog post');

$views = \Webkernel\Cache\CompilationStore::fetch('webkernel.global.views');
expect(is_array($views) && isset($views['dirs']), 'compiled views artifact');

echo "ok\n";
