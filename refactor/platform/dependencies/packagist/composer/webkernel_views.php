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

return [
    'dirs' => [
        $b . '/modules/acme/billing/resources/views',
        $b . '/x-webkernel/codebase/resources/views',
        $b . '/x-webkernel/platform/components/resources/views',
        $b . '/x-webkernel/platform/i18n/resources/views',
        $b . '/x-webkernel/platform/panels/resources/views',
        $b . '/x-webkernel/system/resources/views',
    ],
    'namespaces' => [
        'billing' => [
            $b . '/modules/acme/billing/resources/views',
        ],
        'webkernel' => [
            $b . '/x-webkernel/codebase/resources/views',
            $b . '/x-webkernel/platform/components/resources/views',
            $b . '/x-webkernel/platform/i18n/resources/views',
            $b . '/x-webkernel/platform/panels/resources/views',
            $b . '/x-webkernel/system/resources/views',
        ],
    ],
];
