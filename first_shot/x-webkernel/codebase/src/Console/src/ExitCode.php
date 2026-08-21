<?php declare(strict_types=1);

namespace Webkernel\Console;

enum ExitCode: int
{
    case SUCCESS = 0;
    case ERROR = 1;
    case INVALID = 2;
}
