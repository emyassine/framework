<?php declare(strict_types=1);

namespace Webkernel;

use Webkernel\Config\Config;
use Webkernel\Console\Input\ArgvInput;
use Webkernel\Console\Dispatcher;

/**
 * CLI door. Autoload is already done by fast-boot.php.
 */
final class Console
{
    /**
     * @param list<string> $argv
     *
     * @return never
     */
    public static function run(array $argv): never
    {
        Config::boot();

        exit((new Dispatcher())->handle(new ArgvInput($argv))->value);
    }
}
