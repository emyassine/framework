<?php declare(strict_types=1);

namespace Webkernel\Console;

/** Ctrl+C / EOF during an interactive prompt. */
final class Cancelled extends \RuntimeException
{
}
