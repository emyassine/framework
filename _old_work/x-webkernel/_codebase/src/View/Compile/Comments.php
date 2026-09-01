<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * `{{-- comment --}}` is dropped from compiled output.
 */
final class Comments
{
    /**
     * @param $html string
     *
     * @return string
     */
    public static function compile(string $html): string
    {
        $replaced = preg_replace('/\{\{--.*?--\}\}/s', '', $html);

        return is_string($replaced) ? $replaced : $html;
    }
}
