<?php declare(strict_types=1);

$__wk_autoload = dirname(__DIR__, 5).'/refactor/platform/dependencies/autoload.php';
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/platform/dependencies/autoload.php';
}
if (! is_file($__wk_autoload)) {
    $__wk_autoload = dirname(__DIR__, 4).'/third_party/autoload.php';
}
require $__wk_autoload;

use Webkernel\Performance\Actions\EnableJitPerfAction;
use Webkernel\Performance\Performance;
use Webkernel\Performance\Status;

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

webapp()->flush();
webapp()->boot();
expect(! class_exists(Performance::class, false), 'performance not loaded at boot');
expect(webapp()->performance() instanceof Performance, 'webapp()->performance()');
expect(webapp()->performance() === webapp()->performance(), 'singleton');

$status = Status::inspect();
expect($status->php_version === PHP_VERSION, 'php version');
expect(is_bool($status->opcache), 'opcache bool');
expect(is_bool($status->jit), 'jit bool');
expect($status->jit === false || $status->opcache === true, 'jit implies opcache');

$child = Status::inspect('cli-server');
expect($child->php_version === PHP_VERSION, 'cli-server php version');
expect($child->jit === false || $child->opcache === true, 'cli-server jit implies opcache');

$args = Performance::jit_engine_args();
expect(in_array('opcache.enable_cli=1', $args, true), 'enable_cli directive');
expect(in_array('opcache.jit='.Performance::JIT_MODE, $args, true), 'jit mode directive');
expect(in_array('opcache.jit_buffer_size='.Performance::JIT_BUFFER, $args, true), 'jit buffer directive');
expect(Performance::jit_disable_args() === ['-d', 'opcache.jit=disable'], 'disable args');

$tmp = sys_get_temp_dir().'/wk_perf_'.getmypid().'.php';
Performance::write_preference(true, $tmp);
expect(Performance::wants_jit($tmp) === true, 'preference on');
Performance::write_preference(false, $tmp);
expect(Performance::wants_jit($tmp) === false, 'preference off');
unlink($tmp);

expect(webapp()->performance()->is_jit() === $status->jit, 'composable jit');
expect(webapp()->performance()->is_opcache() === $status->opcache, 'composable opcache');
expect(new EnableJitPerfAction() instanceof EnableJitPerfAction, 'enable action');

echo "ok\n";
