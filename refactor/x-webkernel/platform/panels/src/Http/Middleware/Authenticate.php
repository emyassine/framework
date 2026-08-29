<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Http\Middleware;

/**
 * First-cut auth. Always continues. Real session check is spec H.
 */
final class Authenticate
{
    public function handle(callable $next): mixed
    {
        return $next();
    }
}
