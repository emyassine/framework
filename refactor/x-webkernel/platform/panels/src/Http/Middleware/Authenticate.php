<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Http\Middleware;

use Webkernel\Auth\Http\Middleware\Authenticate as AuthAuthenticate;

/**
 * Panel alias. The check lives in webkernel/auth.
 */
final class Authenticate
{
    /**
     * @param $next callable(): mixed
     *
     * @return mixed
     */
    public function handle(callable $next): mixed
    {
        return (new AuthAuthenticate())->handle($next);
    }
}
