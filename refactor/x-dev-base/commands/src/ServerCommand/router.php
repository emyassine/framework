<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

use Webkernel\Console\IncludeClock;

if (PHP_SAPI !== 'cli-server') {
    return;
}

$parsed = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode(is_string($parsed) && $parsed !== '' ? $parsed : '/');
$docroot = $_SERVER['DOCUMENT_ROOT'] ?? getcwd().'/public';
$file = $docroot.$uri;

if ($uri !== '/' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $static = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'text/javascript; charset=UTF-8',
        'woff2' => 'font/woff2',
        'woff' => 'font/woff',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
    ];
    if (isset($static[$ext])) {
        header('Content-Type: '.$static[$ext]);
        header('Content-Length: '.(string) filesize($file));
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($file);

        return true;
    }

    return false;
}

$root = str_replace('\\', '/', dirname($docroot));
$profile = ($_SERVER['WEBKERNEL_PROFILE_LIFECYCLE'] ?? getenv('WEBKERNEL_PROFILE_LIFECYCLE')) === '1';

$is_api = str_starts_with($uri, '/api/') || (isset($_SERVER['HTTP_ACCEPT']) && str_contains((string) $_SERVER['HTTP_ACCEPT'], 'application/json'));

$classes_before = [];
if ($profile) {
    require_once __DIR__.'/../../IncludeClock.php';
    if (! function_exists('webkernel_include')) {
        function webkernel_include(string $file): mixed
        {
            return IncludeClock::run_file($file);
        }
        function webkernel_profile_enter(string $file): void
        {
            IncludeClock::enter($file);
        }
        function webkernel_profile_leave(): void
        {
            IncludeClock::leave();
        }
    }
    $classes_before = get_declared_classes();
    IncludeClock::start();
    $autoload = $root.'/platform/dependencies/packagist/autoload.php';
    if (is_file($autoload)) {
        IncludeClock::run_file($autoload);
        IncludeClock::hook_autoloader();
    }
    IncludeClock::run_file($docroot.'/index.php');
} else {
    require $docroot.'/index.php';
}

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
    $relative = [];
    foreach ($timed as $abs => $row) {
        $rel = $to_rel($abs);
        $relative[$rel] = $row;
        file_put_contents('php://stderr', sprintf(
            "webkernel-include %.3f %.3f %.3f %s\n",
            $row['ms'],
            $row['run_ms'],
            $row['read_ms'],
            $rel,
        ));
    }

    $classes = [];
    foreach (get_declared_classes() as $class) {
        if (in_array($class, $classes_before, true)) {
            continue;
        }
        $classes[] = $class;
        file_put_contents('php://stderr', 'webkernel-class '.$class."\n");
    }

    $mem = memory_get_usage(true);
    file_put_contents('php://stderr', 'webkernel-mem '.$mem."\n");
    $render_ms = defined('START_REQUEST')
        ? (hrtime(true) - START_REQUEST) / 1e6
        : null;
    $json = json_encode(
        IncludeClock::report($relative, $classes, $mem, $render_ms),
        JSON_UNESCAPED_SLASHES,
    );
    if (is_string($json)) {
        file_put_contents('php://stderr', 'webkernel-profile-json '.$json."\n");
    }
}

return true;
