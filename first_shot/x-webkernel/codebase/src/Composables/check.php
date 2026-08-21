<?php declare(strict_types=1);

$__wk_autoload = dirname(__DIR__, 5).'/refactor/platform/dependencies/autoload.php';
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/platform/dependencies/autoload.php';
}
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/third_party/autoload.php';
}
require $__wk_autoload;

use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Webkernel\Auth\UserInterface;
use Webkernel\Composables\ConfigComposable;
use Webkernel\Config\ConfigWriter;
use Webkernel\WebApp;

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

if (! defined('START_REQUEST')) {
    define('START_REQUEST', hrtime(true));
}

WebApp::flush();
$app = webapp()->boot();

expect((new ReflectionClass(WebApp::class))->hasMethod('__call'), 'composables resolve via dump map');
expect(! (new ReflectionClass(WebApp::class))->hasMethod('platform'), 'no per-composable methods on WebApp');
expect($app->config() instanceof ConfigComposable, 'config composable');
expect(is_string($app->config('autoload')), 'config autoload string');
expect($app->config()->has('platform.storage_path'), 'config has nested');
expect($app->platform()->instance()->file_path() !== '', 'instance file_path');
expect($app->cache() instanceof CacheInterface, 'cache psr-16');
$app->cache()->set('wk.check', 'ok', 10);
expect($app->cache()->get('wk.check') === 'ok', 'cache set/get');
expect($app->cache()->remember('wk.remember', 10, static fn (): string => 'r') === 'r', 'cache remember');
expect($app->response()->json(['ok' => true]) instanceof ResponseInterface, 'response json');
expect($app->panel()->is_platform_panel(), 'default SAP is platform');
expect($app->panel()->type() === 'platform', 'panel type');

$threw = false;
try {
    $app->panel()->register('invoicing.sales', 'module');
} catch (InvalidArgumentException) {
    $threw = true;
}
expect($threw, 'module panel requires module name');
$app->panel()->register('invoicing.sales', 'module', 'invoicing');
expect($app->panel('invoicing.sales')->is_module_panel(), 'module panel');
expect($app->acl('invoicing')->can('export') === false || is_bool($app->acl('invoicing')->can('export')), 'acl can bool');

webterminal()->fake(['prod', true]);
expect(webterminal()->select('Environment', ['dev', 'prod']) === 'prod', 'terminal fake select');
expect(webterminal()->confirm('Enable telemetry?') === true, 'terminal fake confirm');
$exhausted = false;
try {
    webterminal()->text('Admin email');
} catch (RuntimeException $e) {
    $exhausted = str_contains($e->getMessage(), 'Admin email');
}
expect($exhausted, 'terminal fake exhaustion names prompt');

$user = new class implements UserInterface {
    public function id(): int|string
    {
        return 1;
    }

    public function has_role(string $role): bool
    {
        return $role === 'module_admin';
    }
};
$app->auth()->login($user);
expect($app->auth()->check(), 'auth check');
$app->acl()->enable_on_the_fly_creation(true);
$app->acl()->set_on_the_fly_fallback(static fn (string $permission, ?UserInterface $u): bool => $u !== null);
expect($app->acl()->can('knock_head'), 'acl inferred can');

$tmp = webapp_path('platform/temporary/wk_config_check.php');
ConfigWriter::write($tmp, ['hello' => 'world']);
$loaded = require $tmp;
expect(is_array($loaded) && ($loaded['hello'] ?? null) === 'world', 'config writer');
ConfigWriter::atomic_rewrite($tmp, ['hello' => 'there']);
$loaded = require $tmp;
expect(($loaded['hello'] ?? null) === 'there', 'config rewrite');
@unlink($tmp);

echo "ok\n";
