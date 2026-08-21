<?php declare(strict_types=1);

//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
//>
//> Generated. Do not edit.

$v = dirname(__DIR__); // vendor_dir
$b = dirname($v); // base_dir

$files = [
    '/home/yassine/Projects/framework/first_shot/x-webkernel/codebase/src/Console/functions/terminal.php',
    '/home/yassine/Projects/framework/first_shot/x-webkernel/codebase/src/Instance/functions/instance.php',
    '/home/yassine/Projects/framework/first_shot/x-webkernel/codebase/src/Paths/functions/paths.php',
    '/home/yassine/Projects/framework/first_shot/x-webkernel/codebase/src/Route/functions/route.php',
];

foreach ($files as $file) {
    if ((@include $file) === false) {
        throw new \RuntimeException('Unable to load required file: '.$file);
    }
}
