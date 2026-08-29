<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel;

use Webkernel\Console\Commands\DumpAutoloadCommand;
use Webkernel\Console\Commands\ServerCommand;

final class CodebaseProvider extends PlatformProvider
{
    public const VIEW_NAMESPACE = 'webkernel';

    public const ROUTES = [__DIR__.'/../routes.php'];

    public const VIEWS = [__DIR__.'/../resources/views'];

    public const COMMANDS = [DumpAutoloadCommand::class, ServerCommand::class];
}
