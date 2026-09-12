<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\PlatformProvider\Actions;

use Webkernel\PlatformProvider\ProviderDumpContext;

final readonly class CommandsFileAction
{
    public function handle(ProviderDumpContext $context): void
    {
        $commands = [];
        foreach ($context->providers as $provider) {
            foreach ($provider::declaration('COMMANDS') as $command) {
                if (! is_string($command) || $command === '') {
                    continue;
                }
                ProviderDumpContext::ensure_class($command, $context->classmap);
                if (class_exists($command) && ! in_array($command, $commands, true)) {
                    $commands[] = $command;
                }
            }
        }
        sort($commands, SORT_STRING);

        $this->write_class_list($context->composer_dir.'/webkernel_commands.php', $commands);
    }

    /**
     * @param list<class-string> $classes
     */
    private function write_class_list(string $path, array $classes): void
    {
        $lines = array_map(static fn (string $class): string => '    \\'.$class.'::class,', $classes);
        $body = "<?php declare(strict_types=1);\n\nreturn [\n".implode("\n", $lines)."\n];\n";

        file_put_contents($path, $body, LOCK_EX);
    }
}
