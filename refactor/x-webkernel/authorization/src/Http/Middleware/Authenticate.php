<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth\Http\Middleware;

use Webkernel\Auth\Auth;

/**
 * Redirect guests to login.
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
        if (Auth::get()->check()) {
            return $next();
        }
        $path = Auth::get()->login_path();
        \http_response_code(302);
        if (! \headers_sent()) {
            \header('Location: '.$path, true, 302);
        }

        return '';
    }
}
