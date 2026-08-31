<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform;

/**
 * Utility class for generating file headers for dumped/auto-generated files.
 */
final class GeneratedFileHeader
{
    /**
     * Attribution block for generated dumps.
     *
     * @return string
     */
    public static function header(): string
    {
        $end = ((int) \date('Y')) + 1;

        return \implode("\n", [
            '//> This file is part of Webkernel.',
            '//> (c) 2025 - '.$end.' Numerimondes, El Moumen Yassine',
            '//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>',
            '//> For the full copyright and license information, please view the LICENSE',
            '//> file that was distributed with this source code.',
        ]);
    }
}
