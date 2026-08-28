<?php declare(strict_types=1);

namespace Webkernel\Console\Input;

/**
 * Parsed `$argv`. Script name is discarded. `--help` / `-h` / `help`
 * set {@see wants_help()}; `help server` leaves command `server`.
 *
 * `--foo` → true, `--no-foo` → false, `--foo=bar` → `"bar"`.
 */
final readonly class ArgvInput
{
    /** @var list<string> */
    private array $arguments;

    /** @var array<string, string|bool> */
    private array $options;

    private ?string $command;

    private bool $wants_help;

    /**
     * @param list<string>|null $argv
     */
    public function __construct(?array $argv = null)
    {
        $argv ??= $_SERVER['argv'] ?? [];
        if (! \is_array($argv)) {
            $argv = [];
        }

        $command = null;
        $arguments = [];
        $options = [];
        $wants_help = false;

        foreach (\array_slice(\array_values($argv), 1) as $token) {
            if (! \is_string($token) || $token === '') {
                continue;
            }
            if ($token === '--help' || $token === '-h') {
                $wants_help = true;
                continue;
            }
            if (\str_starts_with($token, '--no-') && \strlen($token) > 5 && ! \str_contains($token, '=')) {
                $options[\substr($token, 5)] = false;
                continue;
            }
            if (\str_starts_with($token, '--') && \strlen($token) > 2) {
                $body = \substr($token, 2);
                $eq = \strpos($body, '=');
                if ($eq === false) {
                    $options[$body] = true;
                } else {
                    $options[\substr($body, 0, $eq)] = \substr($body, $eq + 1);
                }
                continue;
            }
            if ($command === null) {
                $command = $token;
                continue;
            }
            $arguments[] = $token;
        }

        if ($command === 'help') {
            $wants_help = true;
            $command = $arguments[0] ?? null;
            $arguments = \array_slice($arguments, 1);
        }

        $this->command = $command;
        $this->arguments = $arguments;
        $this->options = $options;
        $this->wants_help = $wants_help;
    }

    /**
     * @return string|null
     */
    public function command(): ?string
    {
        return $this->command;
    }

    /**
     * @return list<string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return array<string, string|bool>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @param string $name
     *
     * @return string|bool|null
     */
    public function option(string $name): string|bool|null
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has_option(string $name): bool
    {
        return \array_key_exists($name, $this->options);
    }

    /**
     * @return bool
     */
    public function wants_help(): bool
    {
        return $this->wants_help;
    }
}
