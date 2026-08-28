<?php declare(strict_types=1);

require __DIR__.'/platform/fast-boot.php';

use Webkernel\Config\Config;

Config::boot();
Config::set('x.y', 1);
$ok = Config::get('x.y') === 1;
$runtime = is_file(__DIR__.'/platform/platform-runtime.php');

fwrite(STDOUT, 'config_set='.($ok ? 'ok' : 'fail').PHP_EOL);
fwrite(STDOUT, 'runtime_file='.($runtime ? 'ok' : 'fail').PHP_EOL);

if (! $ok || ! $runtime) {
    exit(1);
}
