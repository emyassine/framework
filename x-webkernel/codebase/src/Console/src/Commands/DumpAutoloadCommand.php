<?php declare(strict_types=1);

namespace Webkernel\Console\Commands;

use Composer\Composer;
use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;
use Webkernel\Lifecycle\Boot\BootGenerator;

/**
 * Composer `post-autoload-dump` runs {@see run()}. CLI shells out to Composer
 * so the plugin still owns the Composer instance.
 */
final readonly class DumpAutoloadCommand
{
    #[ConsoleCommand(
        name: 'dump-autoload',
        description: 'Write Webkernel dump files (classmap, commands, composables)',
    )]
    public function __invoke(): ExitCode
    {
        $code = 0;
        passthru('composer dump-autoload --working-dir='.escapeshellarg(webapp_path()), $code);

        return $code === 0 ? ExitCode::SUCCESS : ExitCode::ERROR;
    }

    public static function run(Composer $composer): ExitCode
    {
        BootGenerator::write($composer);

        return ExitCode::SUCCESS;
    }
}
