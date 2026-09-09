<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth\Http;

use Webkernel\Auth\Auth;
use Webkernel\Csrf;
use Webkernel\Route\Action\RedirectAction;
use Webkernel\View\View;

final class LoginController
{
    /**
     * @return string
     */
    public function show(): string
    {
        if (Auth::get()->check()) {
            return (new RedirectAction('/'))();
        }

        return View::make('webkernel::login', [
            'error' => '',
            'email' => '',
        ])->render();
    }

    /**
     * @return string
     */
    public function store(): string
    {
        if (! Csrf::check()) {
            \http_response_code(419);

            return 'Page expired';
        }
        $email = \trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($email !== '' && Auth::get()->attempt($email, $password)) {
            return (new RedirectAction('/'))();
        }

        return View::make('webkernel::login', [
            'error' => 'These credentials do not match our records.',
            'email' => $email,
        ])->render();
    }
}
