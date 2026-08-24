<?php declare(strict_types=1);

$__wk_autoload = dirname(__DIR__, 5).'/refactor/platform/dependencies/autoload.php';
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/platform/dependencies/autoload.php';
}
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/third_party/autoload.php';
}
require $__wk_autoload;

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

webapp()->boot();
\Webkernel\View\View::flush();
expect(\Webkernel\View\View::exists('layouts.page'), 'un-namespaced layouts.page');
expect(\Webkernel\View\View::exists('webkernel::layouts.page'), 'namespaced layouts.page');
$html = (string) \Webkernel\View\View::make('webkernel::layouts.base');
expect($html !== '', 'base layout renders');

$tmp = webapp_path('platform/temporary/wk_comp_'.getmypid());
if (! is_dir($tmp) && ! mkdir($tmp, 0775, true) && ! is_dir($tmp)) {
    fwrite(STDERR, 'FAIL: cannot create '.$tmp."\n");
    exit(1);
}
file_put_contents($tmp.'/hello.view.php', '<b>{{ $slot }}</b>');
file_put_contents($tmp.'/smoke.view.php', '<webkernel::hello>ns</webkernel::hello> <x-webkernel::hello>x</x-webkernel::hello> <webkernel::hello />');
webapp()->declare_component('webkernel', $tmp);
webapp()->declare_view('webkernel', $tmp);
\Webkernel\View\View::flush();

$html = (string) \Webkernel\View\View::make('webkernel::smoke');
expect(str_contains($html, '<b>ns</b>'), 'paired webkernel::hello');
expect(str_contains($html, '<b>x</b>'), 'paired x-webkernel::hello');
expect(str_contains($html, '<b></b>'), 'self-closing webkernel::hello');

echo "ok\n";
