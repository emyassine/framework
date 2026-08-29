<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * `<x-ns::name>` and `<x-name>` become start_component / render_component.
 *
 * //> Literal `<x-webkernel::icon name="...">` folds to SVG at compile time.
 */
final class Components
{
    /**
     * @param $html string
     *
     * @return string
     */
    public static function compile(string $html): string
    {
        $html = self::replace(
            $html,
            '/<(?:x-)?([a-z0-9.-]+)::([a-z0-9.-]+)(\s[^>]*)?(>((?:(?!<\/(?:x-)?\1::\2>).)*)<\/(?:x-)?\1::\2>|\/>)/ms',
            static function (array $m): string {
                $inner = $m[5] ?? '';
                if ($inner !== '') {
                    $inner = self::compile($inner);
                }
                $folded = self::fold_icon($m[1], $m[2], $m[3] ?? '');
                if ($folded !== null) {
                    return $folded.$inner;
                }
                $call = "('".$m[1].'::'.$m[2]."',".self::params($m[3] ?? '').')';

                return Php::OPEN.' $this->start_component'.$call.'; ?>'.$inner.Php::echo('$this->render_component()');
            },
        );

        return self::replace(
            $html,
            '/<x-([a-z0-9.-]+)(\s[^>]*)?(>((?:(?!<\/x-\1>).)*)<\/x-\1>|\/>)/ms',
            static function (array $m): string {
                $inner = $m[4] ?? '';
                if ($inner !== '' && str_contains($m[0], 'x-')) {
                    $inner = self::compile($inner);
                }
                $call = "('components.".$m[1]."',".self::params($m[2] ?? '').')';

                return Php::OPEN.' $this->start_component'.$call.'; ?>'.$inner.Php::echo('$this->render_component()');
            },
        );
    }

    /**
     * @param $html string
     * @param $pattern string
     * @param $callback callable(array<int, string>): string
     *
     * @return string
     */
    private static function replace(string $html, string $pattern, callable $callback): string
    {
        $replaced = preg_replace_callback($pattern, $callback, $html);

        return is_string($replaced) ? $replaced : $html;
    }

    /**
     * @param $namespace string
     * @param $name string
     * @param $params string
     *
     * @return string|null
     */
    private static function fold_icon(string $namespace, string $name, string $params): ?string
    {
        if ($namespace !== 'webkernel' || $name !== 'icon') {
            return null;
        }
        if (preg_match('/(?:^|\s):[a-zA-Z]/', $params) === 1) {
            return null;
        }
        $icon = '';
        $set = 'lucide';
        if (preg_match('/\bname="([^"]+)"/', $params, $m) === 1) {
            $icon = $m[1];
        }
        if (preg_match('/\bset="([^"]+)"/', $params, $m) === 1) {
            $set = $m[1];
        }
        if ($icon === '' || ! function_exists('icon')) {
            return null;
        }
        $markup = icon($icon, 'wds-icon-svg', '', $set);
        if ($markup === '') {
            return null;
        }

        return '<span class="wds-icon">'.$markup.'</span>';
    }

    /**
     * @param $params string
     *
     * @return string
     */
    private static function params(string $params): string
    {
        $params = trim($params);
        if ($params === '' || $params === '/') {
            return '[]';
        }
        $out = [];
        $offset = 0;
        $len = strlen($params);
        while ($offset < $len) {
            if (preg_match('/\s+/A', $params, $ws, 0, $offset) === 1) {
                $offset += strlen($ws[0]);

                continue;
            }
            if ($params[$offset] === '/') {
                break;
            }
            if (preg_match('/:?[A-Za-z0-9:-]+/A', $params, $m, 0, $offset) !== 1) {
                break;
            }
            $raw = $m[0];
            $offset += strlen($raw);
            $bound = str_starts_with($raw, ':');
            $key = str_replace('-', '_', $bound ? substr($raw, 1) : $raw);
            if (preg_match('/\s*=\s*/A', $params, $eq, 0, $offset) === 1) {
                $offset += strlen($eq[0]);
                $quote = $params[$offset] ?? '';
                if ($quote === '"' || $quote === "'") {
                    $end = strpos($params, $quote, $offset + 1);
                    $value = $end === false ? '' : substr($params, $offset + 1, $end - $offset - 1);
                    $offset = $end === false ? $len : $end + 1;
                } else {
                    preg_match('/[^\s]+/A', $params, $vm, 0, $offset);
                    $value = $vm[0] ?? '';
                    $offset += strlen($value);
                }
                $out[] = $bound
                    ? var_export($key, true).'=>'.$value
                    : var_export($key, true).'=>'.var_export($value, true);

                continue;
            }
            $out[] = var_export($key, true).'=>true';
        }

        return '['.implode(',', $out).']';
    }
}
