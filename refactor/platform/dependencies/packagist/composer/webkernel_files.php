<?php declare(strict_types=1);

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//>
//> Generated. Do not edit.

$v = dirname(__DIR__); // vendor_dir
$b = dirname($v, 3); // webapp root

$files = [
    $b . '/x-webkernel/codebase/functions/paths.php',
    $b . '/x-webkernel/codebase/functions/route.php',
    $b . '/x-webkernel/codebase/functions/terminal.php',
    $b . '/x-webkernel/codebase/functions/view.php',
    $b . '/x-webkernel/codebase/functions/webapp.php',
    $b . '/x-webkernel/platform/imagery/functions/branding.php',
    $b . '/x-webkernel/platform/imagery/functions/icon.php',
];

foreach ($files as $file) {
    if ((@include $file) === false) {
        throw new \RuntimeException('Unable to load required file: '.$file);
    }
}
