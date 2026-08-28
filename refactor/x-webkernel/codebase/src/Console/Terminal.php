<?php declare(strict_types=1);

namespace Webkernel\Console;

use Webkernel\Composables\ComposableContract;

/**
 * CLI I/O and prompts. `webterminal()->text()`, `select()`, `confirm()`, …
 * ANSI helpers stay static for table formatting.
 */
final class Terminal implements ComposableContract
{
    public const RESET = "\033[0m";
    public const BOLD = "\033[1m";
    public const GRAY = "\033[90m";
    public const YELLOW = "\033[33m";
    public const GREEN = "\033[32m";
    public const RED = "\033[31m";
    public const CYAN = "\033[36m";

    /** @var list<mixed> */
    private array $fakes = [];

    private bool $faking = false;

    private ?Prompt $prompt = null;

    public static function api_name(): string
    {
        return 'terminal';
    }

    public static function muted(string $text): string
    {
        return self::GRAY.$text.self::RESET;
    }

    public static function status_color(int $code): string
    {
        return match (true) {
            $code >= 200 && $code < 300 => self::GREEN,
            $code >= 300 && $code < 400 => self::YELLOW,
            $code >= 400 && $code < 500 => "\033[33;1m",
            $code >= 500 => "\033[31;1m",
            default => "\033[37m",
        };
    }

    public static function badge(string $text, string $bg, string $fg): string
    {
        return "\033[{$bg};{$fg};1m {$text} \033[0m";
    }

    public static function columns(): int
    {
        $cols = (int) getenv('COLUMNS');
        if ($cols >= 40) {
            return $cols;
        }
        if (PHP_OS_FAMILY !== 'Windows') {
            $stty = [];
            exec('stty size 2>/dev/null', $stty);
            $line = $stty[0] ?? '';
            if (preg_match('/\d+\s+(\d+)/', $line, $m) === 1) {
                return max(40, (int) $m[1]);
            }
        }

        return 80;
    }

    /**
     * Queue answers for tests / non-interactive scripts.
     *
     * @param list<mixed> $answers
     */
    public function fake(array $answers = []): self
    {
        $this->faking = true;
        $this->fakes = array_values($answers);

        return $this;
    }

    public function is_faking(): bool
    {
        return $this->faking;
    }

    public function is_interactive(): bool
    {
        return ! $this->faking
            && PHP_OS_FAMILY !== 'Windows'
            && defined('STDIN')
            && is_resource(STDIN)
            && stream_isatty(STDIN);
    }

    public function next_fake(string $prompt = ''): mixed
    {
        if ($this->fakes === []) {
            throw new \RuntimeException(
                'webterminal()->fake() exhausted: no answer left for prompt'
                .($prompt !== '' ? ' ['.$prompt.']' : '').'.'
            );
        }

        return array_shift($this->fakes);
    }

    public function secret(string $label, bool $required = false): string
    {
        return $this->password($label, required: $required);
    }

    /**
     * @param array<int|string, string> $options
     * @param list<int|string> $default
     * @return list<mixed>
     */
    public function multi_select(string $label, array $options, array $default = []): array
    {
        return $this->multiselect($label, $options, $default);
    }

    /**
     * @param list<string> $headers
     * @param list<list<mixed>> $rows
     */
    public function table(array $headers, array $rows): void
    {
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = strlen((string) $header);
        }
        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen((string) $cell));
            }
        }
        $line = static function (array $cells) use ($widths): string {
            $out = [];
            foreach (array_values($cells) as $i => $cell) {
                $out[] = str_pad((string) $cell, $widths[$i] ?? 0);
            }

            return '  '.implode('  ', $out);
        };
        echo $line($headers).PHP_EOL;
        echo '  '.str_repeat('-', max(1, array_sum($widths) + 2 * max(0, count($widths) - 1))).PHP_EOL;
        foreach ($rows as $row) {
            echo $line(array_values($row)).PHP_EOL;
        }
    }

    public function spinner(\Closure $task, string $title = ''): mixed
    {
        if ($title !== '') {
            $this->info($title);
        }

        return $task();
    }

    public function progress(int $total_steps, \Closure $callback): void
    {
        for ($i = 1; $i <= $total_steps; $i++) {
            $callback($i, $total_steps);
        }
    }

    /**
     * @param callable(string): ?string|null $validate
     */
    public function text(
        string $label,
        string $placeholder = '',
        string $default = '',
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
    ): string {
        return $this->prompt()->line($label, $placeholder, $default, $hint, $required, $validate);
    }

    /**
     * @param callable(string): ?string|null $validate
     */
    public function textarea(
        string $label,
        string $placeholder = '',
        string $default = '',
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
    ): string {
        return $this->prompt()->textarea($label, $placeholder, $default, $hint, $required, $validate);
    }

    /**
     * @param callable(?int|?float): ?string|null $validate
     */
    public function number(
        string $label,
        string $placeholder = '',
        int|float|null $default = null,
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
        int|float|null $min = null,
        int|float|null $max = null,
    ): int|float|null {
        return $this->prompt()->number($label, $placeholder, $default, $hint, $required, $validate, $min, $max);
    }

    /**
     * @param callable(string): ?string|null $validate
     */
    public function password(
        string $label,
        string $placeholder = '',
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
    ): string {
        return $this->prompt()->line($label, $placeholder, '', $hint, $required, $validate, true);
    }

    /**
     * @param callable(bool): ?string|null $validate
     */
    public function confirm(
        string $label,
        bool $default = true,
        string $yes = 'Yes',
        string $no = 'No',
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
    ): bool {
        return $this->prompt()->confirm($label, $default, $yes, $no, $hint, $required, $validate);
    }

    /**
     * @param array<int|string, string> $options
     * @param callable(mixed): ?string|null $validate
     * @param string|callable(mixed): ?string|null $info
     */
    public function select(
        string $label,
        array $options,
        mixed $default = null,
        int $scroll = 5,
        string $hint = '',
        ?callable $validate = null,
        string|callable|null $info = null,
    ): mixed {
        return $this->prompt()->select($label, $options, $default, $scroll, $hint, $validate, $info);
    }

    /**
     * @param array<int|string, string> $options
     * @param list<int|string> $default
     * @param callable(list<mixed>): ?string|null $validate
     * @param string|callable(mixed): ?string|null $info
     * @return list<mixed>
     */
    public function multiselect(
        string $label,
        array $options,
        array $default = [],
        int $scroll = 5,
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
        string|callable|null $info = null,
    ): array {
        return $this->prompt()->multiselect($label, $options, $default, $scroll, $hint, $required, $validate, $info);
    }

    /**
     * @param array<int|string, string>|callable(string): array<int|string, string> $options
     * @param callable(string): ?string|null $validate
     */
    public function suggest(
        string $label,
        array|callable $options,
        string $placeholder = '',
        string $default = '',
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
    ): string {
        return $this->prompt()->line($label, $placeholder, $default, $hint, $required, $validate, false, true, $options);
    }

    /**
     * @param array<int|string, string> $options
     * @param callable(mixed): ?string|null $validate
     * @param string|callable(mixed): ?string|null $info
     */
    public function search(
        string $label,
        array $options,
        mixed $default = null,
        int $scroll = 5,
        string $hint = '',
        ?callable $validate = null,
        string|callable|null $info = null,
    ): mixed {
        return $this->prompt()->select($label, $options, $default, $scroll, $hint, $validate, $info, true);
    }

    public function pause(string $message = 'Press enter to continue...'): void
    {
        if ($this->faking) {
            return;
        }
        $this->prompt()->line($message, required: false);
    }

    public function intro(string $message): void
    {
        echo "\n  ".self::BOLD.$message.self::RESET."\n\n";
    }

    public function outro(string $message): void
    {
        echo "\n  ".self::GREEN.$message.self::RESET."\n\n";
    }

    public function note(string $message, string $type = 'NOTE'): void
    {
        $colors = match (strtoupper($type)) {
            'INFO' => ['44', '97'],
            'WARN', 'WARNING' => ['43', '30'],
            'ERROR' => ['41', '97'],
            'OK', 'SUCCESS' => ['42', '30'],
            'ALERT' => ['45', '97'],
            default => ['47', '30'],
        };
        echo '  '.self::badge($type, $colors[0], $colors[1]).' '.self::BOLD.$message.self::RESET."\n";
    }

    public function info(string $msg): void
    {
        $this->note($msg, 'INFO');
    }

    public function warning(string $msg): void
    {
        $this->note($msg, 'WARN');
    }

    public function success(string $msg): void
    {
        $this->note($msg, 'OK');
    }

    public function error(string $msg): void
    {
        fwrite(STDERR, '  '.self::badge('ERROR', '41', '97').' '.self::RED.$msg.self::RESET."\n");
    }

    public function alert(string $msg): void
    {
        $this->note($msg, 'ALERT');
    }

    public function clear(): void
    {
        echo "\033[2J\033[H";
    }

    public function title(string $title): void
    {
        echo "\033]0;".$title."\007";
    }

    private function prompt(): Prompt
    {
        return $this->prompt ??= new Prompt($this);
    }
}
