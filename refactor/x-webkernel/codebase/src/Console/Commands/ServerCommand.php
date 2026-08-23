<?php declare(strict_types=1);

namespace Webkernel\Console\Commands;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\ExitCode;
use Webkernel\Console\Server\Engine;
use Webkernel\Performance\Performance;

final readonly class ServerCommand
{
    public function __construct(
        private Engine $engine,
    ) {
    }

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
        if ($jit === null && Performance::wants_jit()) {
            $jit = true;
        }

        return $this->engine->serve($host, $port, $profile_lifecycle, $jit);
    }
}
