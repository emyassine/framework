<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

/**
 * Dumped WDS stylesheet. Layouts link this file; request does not inline tokens/shell.
 *
 * //> Source of truth is `resources/views/wds/*.view.php`. dump-autoload writes public/wds.css.
 */
final class Wds
{
    public const CSS = 'wds.css';

    /**
     * @return string
     */
    public static function css_path(): string
    {
        return \webapp_path('public/'.self::CSS);
    }

    /**
     * Cache-busted public href. Empty query when the dump is missing.
     *
     * @return string
     */
    public static function css_href(): string
    {
        $path = self::css_path();
        $mtime = \is_file($path) ? (\filemtime($path) ?: 0) : 0;

        return '/'.self::CSS.($mtime > 0 ? '?v='.$mtime : '');
    }
}
