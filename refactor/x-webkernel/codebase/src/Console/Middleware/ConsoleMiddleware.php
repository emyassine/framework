<?php declare(strict_types=1);

namespace Webkernel\Console\Middleware;

use Webkernel\Console\ExitCode;
use Webkernel\Console\Input\ArgvInput;

interface ConsoleMiddleware
{
    /**
     * @param ArgvInput            $input
     * @param callable(): ExitCode $next
     *
     * @return ExitCode
     */
    public function handle(ArgvInput $input, callable $next): ExitCode;
}
