<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\Commands\ServerCommand\Engine;
use Webkernel\Console\ExitCode;
use Webkernel\Performance\Performance;

final readonly class ServerCommand
{
    /**
     * @param Engine $engine
     */
    public function __construct(
        private Engine $engine,
    ) {
    }

    /**
     * @param string      $host
     * @param int         $port
     * @param bool        $profile_lifecycle
     * @param bool|null   $with_jit
     *
     * @return ExitCode
     */
    #[ConsoleCommand(
        name: 'server',
        description: 'Local HTTP server (--host= --port= --profile-lifecycle --with-jit)',
    )]
    public function __invoke(
        string $host = '127.0.0.1',
        int $port = 8000,
        bool $profile_lifecycle = false,
        ?bool $with_jit = null,
    ): ExitCode {
        $jit = $with_jit;
        if ($jit === null) {
            $jit = \is_file(Performance::preference_path())
                ? Performance::wants_jit()
                : true;
        }

        return $this->engine->serve($host, $port, $profile_lifecycle, $jit);
    }
}
