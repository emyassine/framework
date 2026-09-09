<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View\Compile;

/**
 * Compile policy for Engine.
 *
 * Auto: compile when the source is newer than the compiled file.
 * Slow: compile every request.
 * Fast: never compile (production, dump-autoload already wrote the files).
 */
enum Mode: int
{
    case Auto = 0;
    case Slow = 1;
    case Fast = 2;
}
