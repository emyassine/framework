<?php declare(strict_types=1);

require dirname(__DIR__, 4).'/third_party/autoload.php';

use Psr\Container\ContainerInterface;
use Webkernel\Container\Container;
use Webkernel\Container\NotFound;

function expect(mixed $ok, string $msg): void
{
    if ($ok) {
        return;
    }
    fwrite(STDERR, 'FAIL: '.$msg."\n");
    exit(1);
}

$c = new Container();
expect($c instanceof ContainerInterface, 'psr-11');
expect($c->has(stdClass::class) === false, 'has unbound');
$missed = false;
try {
    $c->get(stdClass::class);
} catch (NotFound $e) {
    $missed = true;
}
expect($missed, 'get unbound throws');

$c->singleton(stdClass::class);
$a = $c->get(stdClass::class);
$b = $c->get(stdClass::class);
expect($a === $b, 'singleton get');
expect($c->has(stdClass::class), 'has bound');

$c->bind(ArrayObject::class);
$d = $c->make(ArrayObject::class);
$e = $c->make(ArrayObject::class);
expect($d !== $e, 'bind make');

$known = new stdClass();
$c->instance('fixed', $known);
expect($c->get('fixed') === $known, 'instance');

echo "ok\n";
