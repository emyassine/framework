<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Database\Console;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;
use Webkernel\Database\Migrator;

final readonly class MigrateCommand
{
    /**
     * @param $migrator Migrator
     */
    public function __construct(
        private Migrator $migrator,
    ) {
    }

    /**
     * @return ExitCode
     */
    #[ConsoleCommand(
        name: 'migrate',
        description: 'Run pending database migrations',
    )]
    public function __invoke(): ExitCode
    {
        $ran = $this->migrator->run();
        if ($ran === []) {
            webterminal()->info('nothing to migrate');

            return ExitCode::SUCCESS;
        }
        foreach ($ran as $id) {
            webterminal()->info('migrated '.$id);
        }

        return ExitCode::SUCCESS;
    }
}
