<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth\Http;

use Webkernel\Auth\Auth;
use Webkernel\Route\Action\RedirectAction;

final class LogoutController
{
    /**
     * @return string
     */
    public function __invoke(): string
    {
        Auth::get()->logout();

        return (new RedirectAction(Auth::get()->login_path()))();
    }
}
