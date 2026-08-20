<?php declare(strict_types=1);

require dirname(__DIR__, 4).'/third_party/autoload.php';

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

webapp()->boot();

expect(\Webkernel\View\View::exists('layouts.page'), 'un-namespaced layouts.page');
expect(\Webkernel\View\View::exists('webkernel::layouts.page'), 'namespaced layouts.page');

$tmp = sys_get_temp_dir().'/wk_comp_'.getmypid();
if (! is_dir($tmp) && ! mkdir($tmp, 0775, true) && ! is_dir($tmp)) {
    fwrite(STDERR, 'FAIL: cannot create '.$tmp."\n");
    exit(1);
}
file_put_contents($tmp.'/hello.view.php', '<b>{{ $slot }}</b>');
file_put_contents($tmp.'/smoke.view.php', '<webkernel::hello>ns</webkernel::hello> <x-webkernel::hello>x</x-webkernel::hello>');
webapp()->declare_component('webkernel', $tmp);
webapp()->declare_view('webkernel', $tmp);
\Webkernel\View\View::flush();

$html = (string) \Webkernel\View\View::make('webkernel::smoke');
expect(str_contains($html, '<b>ns</b>'), 'paired webkernel::hello');
expect(str_contains($html, '<b>x</b>'), 'paired x-webkernel::hello');

echo "ok\n";
