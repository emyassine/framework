<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Auth;

use Webkernel\Auth\Console\UserCreateCommand;
use Webkernel\PlatformProvider;

final class AuthProvider extends PlatformProvider
{
    public const CONFIG = [__DIR__.'/../config/auth.php'];

    public const ROUTES = [__DIR__.'/../routes.php'];

    public const VIEWS = [
        'webkernel' => __DIR__.'/../resources/views',
    ];

    public const COMPONENTS = [
        'webkernel' => __DIR__.'/../resources/views',
    ];

    public const COMMANDS = [UserCreateCommand::class];

    public const MIGRATIONS = [__DIR__.'/../database/migrations'];
}
