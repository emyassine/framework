<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

define('START_REQUEST', hrtime(true));

(static function (): void {
    $webapp_path = dirname(__DIR__);
    $maint = $webapp_path.'/platform/maintenance.php';
    if (is_file($maint)) {
        require $maint;
        return;
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    $path = is_string($path) && $path !== '' ? $path : '/';
    if ($path === '/healthz' || $path === '/ready') {
        http_response_code(200);
        header('Content-Type: text/plain');
        echo 'OK';

        return;
    }

    /** @var \Webkernel\WebApp $webapp */
    $webapp = require $webapp_path.'/platform/bootstrap/app.php';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $handler = (new \Webkernel\Http\RequestClassifier())->classify($path, $method);
    $handler->handle([], $webapp->container())->emit();
})();
