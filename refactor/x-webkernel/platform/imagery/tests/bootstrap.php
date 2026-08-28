<?php declare(strict_types=1);

if (! \defined('START_REQUEST')) {
    \define('START_REQUEST', \hrtime(true));
}

$root = \dirname(__DIR__, 4);
require $root.'/platform/fast-boot.php';
