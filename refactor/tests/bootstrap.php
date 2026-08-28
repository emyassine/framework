<?php declare(strict_types=1);

if (! defined('START_REQUEST')) {
    define('START_REQUEST', hrtime(true));
}

require dirname(__DIR__).'/platform/fast-boot.php';
