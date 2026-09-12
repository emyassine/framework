<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * PHP fragments emitted into compiled views.
 */
final class Php
{
    public const OPEN = '<?php ';

    public const ECHO = '<?php echo ';

    /**
     * @param $code string
     *
     * @return string
     */
    public static function line(string $code): string
    {
        return self::OPEN.$code.' ?>';
    }

    /**
     * @param $code string
     *
     * @return string
     */
    public static function echo(string $code): string
    {
        return self::ECHO.$code.'; ?>';
    }

    /**
     * @param $expression string
     *
     * @return string
     */
    public static function strip_parentheses(string $expression): string
    {
        $expression = trim($expression);
        if (str_starts_with($expression, '(') && str_ends_with($expression, ')')) {
            return substr($expression, 1, -1);
        }

        return $expression;
    }
}
