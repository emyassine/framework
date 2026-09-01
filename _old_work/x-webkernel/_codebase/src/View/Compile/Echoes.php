<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * `{!! raw !!}` then `{{ escaped }}`. `@{{` is left as `{{`.
 */
final class Echoes
{
    /**
     * @param $html string
     * @param $echo_format string sprintf template, one `%s`
     *
     * @return string
     */
    public static function compile(string $html, string $echo_format): string
    {
        $html = self::tags($html, '/(@)?\{\!!\s*(.+?)\s*!!\}(\r?\n)?/s', static function (array $m): string {
            $nl = ($m[3] ?? '') === '' ? '' : $m[3].$m[3];

            return ($m[1] ?? '') !== ''
                ? substr($m[0], 1)
                : Php::echo(self::defaults($m[2])).$nl;
        });

        return self::tags($html, '/(@)?\{\{\s*(.+?)\s*\}\}(\r?\n)?/s', static function (array $m) use ($echo_format): string {
            $nl = ($m[3] ?? '') === '' ? '' : $m[3].$m[3];
            if (($m[1] ?? '') !== '') {
                return substr($m[0], 1);
            }

            return Php::ECHO.sprintf($echo_format, self::defaults($m[2])).'; ?>'.$nl;
        });
    }

    /**
     * @param $html string
     * @param $pattern string
     * @param $callback callable(array<int, string>): string
     *
     * @return string
     */
    private static function tags(string $html, string $pattern, callable $callback): string
    {
        $replaced = preg_replace_callback($pattern, $callback, $html);

        return is_string($replaced) ? $replaced : $html;
    }

    /**
     * @param $value string
     *
     * @return string
     */
    private static function defaults(string $value): string
    {
        $replaced = preg_replace(
            '/^(\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\s+or\s+(.+?)$/s',
            'isset($1) ? $1 : $2',
            $value,
        );

        return is_string($replaced) ? $replaced : $value;
    }
}
