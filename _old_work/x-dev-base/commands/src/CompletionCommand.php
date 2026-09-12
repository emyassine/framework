<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Commands;

use Webkernel\Console\Attribute\ConsoleCommand;
use Webkernel\Console\CommandsDiscovery;
use Webkernel\Console\ExitCode;

/**
 * Shell completion script and candidate dump.
 */
final readonly class CompletionCommand
{
    /**
     * @param CommandsDiscovery $discovery
     */
    public function __construct(
        private CommandsDiscovery $discovery,
    ) {
    }

    /**
     * @param string $shell
     *
     * @return ExitCode
     */
    #[ConsoleCommand(
        name: 'completion',
        description: 'Dump shell autocompletion script (bash|zsh)',
    )]
    public function script(string $shell = 'bash'): ExitCode
    {
        $shell = \strtolower($shell);
        echo match ($shell) {
            'bash' => $this->bash_script(),
            'zsh' => $this->zsh_script(),
            default => throw new \InvalidArgumentException('Unsupported shell ['.$shell.']. Use bash or zsh.'),
        };

        return ExitCode::SUCCESS;
    }

    /**
     * Prints completion candidates (one per line). Used by the dumped scripts.
     *
     * @param string $input
     *
     * @return ExitCode
     */
    #[ConsoleCommand(
        name: '_complete',
        description: 'Internal completion candidates',
        hidden: true,
    )]
    public function complete(string $input = ''): ExitCode
    {
        $definitions = $this->discovery->definitions($this->discovery->classes_from_dump());
        $names = $this->discovery->completion_names($definitions);
        $prefix = $input;
        if ($prefix !== '' && \str_contains($prefix, ' ')) {
            $parts = \preg_split('/\s+/', \trim($prefix)) ?: [];
            $prefix = (string) (\end($parts) ?: '');
            if (\count($parts) > 1) {
                return ExitCode::SUCCESS;
            }
        }
        foreach ($names as $name) {
            if ($prefix === '' || \str_starts_with($name, $prefix)) {
                echo $name."\n";
            }
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @return string
     */
    private function bash_script(): string
    {
        return <<<'BASH'
# Webkernel bash completion — eval "$(./webkernel completion bash)"
_webkernel_completion() {
    local cur="${COMP_WORDS[COMP_CWORD]}"
    local cmd="${COMP_WORDS[0]}"
    local input="${COMP_LINE}"
    COMPREPLY=($(compgen -W "$("${cmd}" _complete --input="${input}" 2>/dev/null)" -- "${cur}"))
}
complete -F _webkernel_completion webkernel
complete -F _webkernel_completion ./webkernel

BASH;
    }

    /**
     * @return string
     */
    private function zsh_script(): string
    {
        return <<<'ZSH'
# Webkernel zsh completion — eval "$(./webkernel completion zsh)"
_webkernel_completion() {
    local -a commands
    local cmd="${words[1]}"
    local input="${words[*]}"
    commands=(${(f)"$("${cmd}" _complete --input="${input}" 2>/dev/null)"})
    _describe 'command' commands
}
compdef _webkernel_completion webkernel
compdef _webkernel_completion ./webkernel

ZSH;
    }
}
