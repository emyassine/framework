<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
define('START_REQUEST', hrtime(true));
(static function () {
    $webapp_path = dirname(__DIR__);
    $maint = "$webapp_path/platform/storage/maintenance.php";
    /** Send Maintenance page if set */
    if (is_file($maint)) { require $maint; }
    /** @var \Webkernel\WebApp $webapp is launched */
    (require $webapp = "$webapp_path/platform/bootstrap/app.php")
        ->handle_request(\Webkernel\Http\Request::capture());
})();
