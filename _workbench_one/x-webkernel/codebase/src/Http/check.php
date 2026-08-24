<?php declare(strict_types=1);

$__wk_autoload = dirname(__DIR__, 5).'/refactor/platform/dependencies/autoload.php';
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/platform/dependencies/autoload.php';
}
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/third_party/autoload.php';
}
require $__wk_autoload;

use Webkernel\Http\Request;
use Webkernel\Platform\Exceptions;

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

$api = new Request('api/users', 'GET');
expect($api->uri() === '/api/users', 'uri slash');
expect((new Request('/'))->uri() === '/', 'root uri');
expect($api->is('api/*') === true, 'api/* match');
expect($api->is('admin/*') === false, 'admin/* miss');
expect((new Request('api'))->is('api/*') === false, 'api not api/*');
expect((new Request('/'))->is('*') === true, 'star');

$exceptions = new Exceptions();
$exceptions->should_render_json_when(fn (Request $request) => $request->is('api/*'));
expect($exceptions->renders_json($api) === true, 'json when api');
expect($exceptions->renders_json(new Request('dashboard')) === false, 'html when page');

echo "ok\n";
