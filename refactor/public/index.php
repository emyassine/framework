<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

define('START_REQUEST', hrtime(true));
$webapp_path = dirname(__DIR__);

if (is_file($maint = $webapp_path.'/platform/maintenance.php')) { require $maint; return; }

$uri  = $_SERVER['REQUEST_URI'] ?? '/';
$route = parse_url($uri, PHP_URL_PATH);
$route = is_string($route) && $route !== '' ? $route : '/';

if ($route === '/healthz' || $route === '/ready') {
    http_response_code(200);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Platform is Ready'; return;
}

require $webapp_path.'/platform/fast-boot.php'; \Webkernel\Http::run();
