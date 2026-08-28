<?php declare(strict_types=1);

namespace Webkernel\Console\Middleware;

use Webkernel\Console\ExitCode;
use Webkernel\Console\Input\ArgvInput;

interface ConsoleMiddleware
{
    /**
     * @param callable(): ExitCode $next
     */
    public function handle(ArgvInput $input, callable $next): ExitCode;
}
