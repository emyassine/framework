<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

/**
 * Dump orchestrator for CSS and JS. Vite later uses the same gather list.
 *
 * //> DumpAutoloadCommand calls dump(). It does not scrape views.
 * //> Never `wk-`. Public files are webapp.css and webapp.js.
 */
final class Assets
{
    public const CSS = 'webapp.css';

    public const JS = 'webapp.js';

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return void
     */
    public static function dump(array $providers): void
    {
        Css::dump($providers);
        Js::dump($providers);
    }

    /**
     * @return string
     */
    public static function css_path(): string
    {
        return \webapp_path('public/'.self::CSS);
    }

    /**
     * @return string
     */
    public static function js_path(): string
    {
        return \webapp_path('public/'.self::JS);
    }

    /**
     * Cache-busted public href. Empty query when the dump is missing.
     *
     * @return string
     */
    public static function css_href(): string
    {
        return self::href(self::CSS, self::css_path());
    }

    /**
     * @return string
     */
    public static function js_href(): string
    {
        return self::href(self::JS, self::js_path());
    }

    /**
     * @param $name string
     * @param $path string
     *
     * @return string
     */
    private static function href(string $name, string $path): string
    {
        $mtime = \is_file($path) ? (\filemtime($path) ?: 0) : 0;

        return '/'.$name.($mtime > 0 ? '?v='.$mtime : '');
    }
}
