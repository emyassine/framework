<?php declare(strict_types=1);

use Webkernel\Console\Server\IncludeClock;

if (PHP_SAPI !== 'cli-server') {
    return;
}

require_once __DIR__.'/IncludeClock.php';

$parsed = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode(is_string($parsed) && $parsed !== '' ? $parsed : '/');
$docroot = $_SERVER['DOCUMENT_ROOT'] ?? getcwd().'/public';
$file = $docroot.$uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

$root = str_replace('\\', '/', dirname($docroot));
$profile = ($_SERVER['WEBKERNEL_PROFILE_LIFECYCLE'] ?? getenv('WEBKERNEL_PROFILE_LIFECYCLE')) === '1';

$is_api = str_starts_with($uri, '/api/') || (isset($_SERVER['HTTP_ACCEPT']) && str_contains((string) $_SERVER['HTTP_ACCEPT'], 'application/json'));

$files_before = $profile ? get_included_files() : [];
$classes_before = $profile ? get_declared_classes() : [];

if ($profile) {
    IncludeClock::start();
}

require $docroot.'/index.php';

$status = http_response_code();
if (! is_int($status) || $status < 100) {
    $status = 200;
}

$client = ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1').':'.($_SERVER['REMOTE_PORT'] ?? '0');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = $_SERVER['REQUEST_URI'] ?? $uri;
$render = defined('START_REQUEST')
    ? sprintf(' render=%.2f', (hrtime(true) - START_REQUEST) / 1e6)
    : '';

file_put_contents('php://stderr', sprintf(
    "%s [%d]: %s %s%s\n",
    $client,
    $status,
    $method,
    $path,
    $render,
));

if ($profile) {
    file_put_contents('php://stderr', 'webkernel-type '.($is_api ? 'API' : 'WEB')."\n");

    $prefix = $root.'/';
    $to_rel = static function (string $abs) use ($prefix): string {
        $abs = str_replace('\\', '/', $abs);

        return str_starts_with($abs, $prefix) ? substr($abs, strlen($prefix)) : $abs;
    };

    $timed = IncludeClock::stop();
    $seen = [];

    foreach (get_included_files() as $included) {
        if (in_array($included, $files_before, true)) {
            continue;
        }
        $rel = $to_rel($included);
        $seen[$rel] = true;
        $row = $timed[$included] ?? null;

        if ($row === null) {
            file_put_contents('php://stderr', 'webkernel-include ? ? ? '.$rel."\n");
            continue;
        }

        file_put_contents('php://stderr', sprintf(
            "webkernel-include %.3f %.3f %.3f %s\n",
            $row['ms'],
            $row['run_ms'],
            $row['read_ms'],
            $rel,
        ));
    }

    foreach ($timed as $abs => $row) {
        $rel = $to_rel($abs);
        if (isset($seen[$rel])) {
            continue;
        }
        file_put_contents('php://stderr', sprintf(
            "webkernel-include %.3f %.3f %.3f %s\n",
            $row['ms'],
            $row['run_ms'],
            $row['read_ms'],
            $rel,
        ));
    }

    foreach (get_declared_classes() as $class) {
        if (in_array($class, $classes_before, true)) {
            continue;
        }
        file_put_contents('php://stderr', 'webkernel-class '.$class."\n");
    }

    file_put_contents('php://stderr', 'webkernel-mem '.memory_get_usage(true)."\n");
}

return true;
