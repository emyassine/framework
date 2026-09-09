<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

if (! \defined('START_REQUEST')) {
    \define('START_REQUEST', \hrtime(true));
}

$root = \dirname(__DIR__, 3);
require $root.'/platform/fast-boot.php';
