<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

/**
 * Invision ACP chrome dumped to public/ips/. Layout links these files.
 */
final class Ips
{
    /**
     * @return list<string>
     */
    public static function css_hrefs(): array
    {
        $files = [
            'fontawesome/css/all.min.css',
            'css/framework.css',
            'css/admin.css',
            'css/glue.css',
        ];
        $out = [];
        foreach ($files as $rel) {
            $path = \webapp_path('public/ips/'.$rel);
            $mtime = \is_file($path) ? (\filemtime($path) ?: 0) : 0;
            $out[] = '/ips/'.$rel.($mtime > 0 ? '?v='.$mtime : '');
        }

        return $out;
    }
}
