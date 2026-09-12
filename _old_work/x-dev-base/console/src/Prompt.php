<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console;

/**
 * Interactive reader for {@see Terminal}. Fake answers skip the TTY.
 * Non-TTY falls back to fgets / defaults — no Symfony helper.
 */
final class Prompt
{
    public function __construct(
        private readonly Terminal $io,
    ) {
    }

    /**
     * @param callable(string): ?string|null $validate
     */
    public function line(
        string $label,
        string $placeholder = '',
        string $default = '',
        string $hint = '',
        bool|string $required = false,
        ?callable $validate = null,
        bool $hidden = false,
        bool $suggest = false,
        array|callable $options = [],
    ): string {
        return $this->until_valid(
            function () use ($label, $placeholder, $default, $hint, $hidden, $suggest, $options): string {
                if ($this->io->is_faking()) {
                    $fake = $this->io->next_fake($label);

                    return \is_scalar($fake) ? (string) $fake : $default;
                }
                if (! $this->io->is_interactive()) {
                    return $this->fallback_line($label, $default, $hidden);
                }

                return $this->interactive_line($label, $placeholder, $default, $hint, $hidden, $suggest, $options);
            },
            fn (string $value): ?string => $this->line_error($value, $required, $validate),
        );
    }

    /**
     * @param callable(?int): ?string|null $validate
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
        $default_s = $default === null ? '' : (string) $default;

        return $this->until_valid(
            function () use ($label, $placeholder, $default_s, $hint, $default): string {
                if ($this->io->is_faking()) {
                    $fake = $this->io->next_fake($label);
                    if ($fake === null) {
                        return $default_s;
                    }

                    return \is_scalar($fake) ? (string) $fake : $default_s;
                }
                if (! $this->io->is_interactive()) {
                    return $this->fallback_line($label, $default_s, false);
                }

                return $this->interactive_line($label, $placeholder, $default_s, $hint, false, false, [], true);
            },
            function (string $raw) use ($required, $validate, $min, $max): ?string {
                if ($raw === '') {
                    return $this->required_message($required);
                }
                if (! \is_numeric($raw)) {
                    return 'Must be a number.';
                }
                $n = \str_contains($raw, '.') ? (float) $raw : (int) $raw;
                if ($min !== null && $n < $min) {
                    return 'Must be at least '.$min.'.';
                }
                if ($max !== null && $n > $max) {
                    return 'Must be at most '.$max.'.';
                }
                if ($validate !== null) {
                    $err = $validate($n);

                    return \is_string($err) ? $err : null;
                }

                return null;
            },
            function (string $raw): int|float|null {
                if ($raw === '') {
                    return null;
                }

                return \str_contains($raw, '.') ? (float) $raw : (int) $raw;
            },
        );
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
        return $this->until_valid(
            function () use ($label, $placeholder, $default, $hint): string {
                if ($this->io->is_faking()) {
                    $fake = $this->io->next_fake($label);

                    return \is_scalar($fake) ? (string) $fake : $default;
                }
                if (! $this->io->is_interactive()) {
                    return $this->fallback_line($label, $default, false);
                }

                return $this->interactive_textarea($label, $placeholder, $default, $hint);
            },
            fn (string $value): ?string => $this->line_error($value, $required, $validate),
        );
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
        return $this->until_valid(
            function () use ($label, $default, $yes, $no, $hint): bool {
                if ($this->io->is_faking()) {
                    $fake = $this->io->next_fake($label);
                    if (\is_bool($fake)) {
                        return $fake;
                    }
                    if (\is_string($fake)) {
                        return \in_array(\strtolower($fake), ['y', 'yes', '1', 'true'], true);
                    }

                    return (bool) $fake;
                }
                if (! $this->io->is_interactive()) {
                    return $default;
                }

                return $this->interactive_confirm($label, $default, $yes, $no, $hint);
            },
            function (bool $value) use ($required, $validate): ?string {
                if ($required !== false && $value !== true) {
                    return \is_string($required) ? $required : 'Required.';
                }
                if ($validate !== null) {
                    $err = $validate($value);

                    return \is_string($err) ? $err : null;
                }

                return null;
            },
        );
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
        bool $search = false,
    ): mixed {
        if ($options === [] && ! $search) {
            throw new \InvalidArgumentException('select() needs at least one option.');
        }

        return $this->until_valid(
            function () use ($label, $options, $default, $scroll, $hint, $info, $search): mixed {
                if ($this->io->is_faking()) {
                    return $this->fake_choice($options, $this->io->next_fake($label), false);
                }
                if (! $this->io->is_interactive()) {
                    return $this->default_choice($options, $default);
                }

                return $this->interactive_select($label, $options, $default, $scroll, $hint, $info, false, $search);
            },
            function (mixed $value) use ($validate): ?string {
                if ($validate !== null) {
                    $err = $validate($value);

                    return \is_string($err) ? $err : null;
                }

                return null;
            },
        );
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
        return $this->until_valid(
            function () use ($label, $options, $default, $scroll, $hint, $info): array {
                if ($this->io->is_faking()) {
                    $fake = $this->io->next_fake($label);
                    if (! \is_array($fake)) {
                        $fake = $fake === null || $fake === '' ? [] : [$fake];
                    }

                    return $this->fake_choice($options, $fake, true);
                }
                if (! $this->io->is_interactive()) {
                    return $this->default_multi($options, $default);
                }

                return $this->interactive_select($label, $options, $default, $scroll, $hint, $info, true, false);
            },
            function (array $value) use ($required, $validate): ?string {
                if ($required !== false && $value === []) {
                    return \is_string($required) ? $required : 'Required.';
                }
                if ($validate !== null) {
                    $err = $validate($value);

                    return \is_string($err) ? $err : null;
                }

                return null;
            },
        );
    }

    /**
     * @template T
     * @param callable(): T $read
     * @param callable(T): ?string $error
     * @param callable(T): mixed|null $cast
     * @return mixed
     */
    private function until_valid(callable $read, callable $error, ?callable $cast = null): mixed
    {
        while (true) {
            $value = $read();
            $message = $error($value);
            if ($message === null) {
                return $cast !== null ? $cast($value) : $value;
            }
            if ($this->io->is_faking()) {
                continue;
            }
            if (! $this->io->is_interactive()) {
                throw new \InvalidArgumentException($message);
            }
            $this->io->error($message);
        }
    }

    /**
     * @param callable(string): ?string|null $validate
     */
    private function line_error(string $value, bool|string $required, ?callable $validate): ?string
    {
        if ($value === '') {
            return $this->required_message($required);
        }
        if ($validate !== null) {
            $err = $validate($value);

            return \is_string($err) ? $err : null;
        }

        return null;
    }

    private function required_message(bool|string $required): ?string
    {
        if ($required === false) {
            return null;
        }

        return \is_string($required) ? $required : 'Required.';
    }

    private function fallback_line(string $label, string $default, bool $hidden): string
    {
        $suffix = $default !== '' ? ' ['.$default.']' : '';
        \fwrite(STDOUT, '  '.$label.$suffix.': ');
        if ($hidden) {
            $this->stty(['-echo']);
        }
        $line = \defined('STDIN') && \is_resource(STDIN) ? \fgets(STDIN) : false;
        if ($hidden) {
            $this->stty(['echo']);
            \fwrite(STDOUT, "\n");
        }
        if ($line === false) {
            throw new Cancelled('Cancelled.');
        }
        $line = \trim($line);

        return $line === '' ? $default : $line;
    }

    /**
     * @param array<int|string, string>|callable $options
     */
    private function interactive_line(
        string $label,
        string $placeholder,
        string $default,
        string $hint,
        bool $hidden,
        bool $suggest,
        array|callable $options,
        bool $numeric = false,
    ): string {
        $value = $default;
        $prev = 0;
        $this->show_cursor(true);

        return $this->with_raw(function () use (&$value, &$prev, $label, $placeholder, $hint, $hidden, $suggest, $options, $numeric): string {
            while (true) {
                $matches = $suggest ? $this->suggest_matches($value, $options) : [];
                $this->redraw($this->frame_line($label, $value, $placeholder, $hint, $hidden, $matches), $prev);
                $key = $this->read_key();
                if ($key === 'enter') {
                    $this->clear_frame($prev);
                    $this->show_cursor(true);
                    $this->write_submitted($label, $hidden ? \str_repeat('*', \strlen($value)) : $value);

                    return $value;
                }
                if ($key === 'backspace') {
                    $value = \mb_substr($value, 0, \max(0, \mb_strlen($value) - 1));
                    continue;
                }
                if ($key === 'up' && $numeric && \is_numeric($value === '' ? '0' : $value)) {
                    $value = (string) ((int) $value + 1);
                    continue;
                }
                if ($key === 'down' && $numeric && \is_numeric($value === '' ? '0' : $value)) {
                    $value = (string) ((int) $value - 1);
                    continue;
                }
                if ($key === 'tab' && $matches !== []) {
                    $value = (string) \array_values($matches)[0];
                    continue;
                }
                if (\strlen($key) === 1 && $key >= ' ') {
                    $value .= $key;
                }
            }
        });
    }

    private function interactive_textarea(string $label, string $placeholder, string $default, string $hint): string
    {
        $value = $default;
        $prev = 0;
        $this->show_cursor(true);

        return $this->with_raw(function () use (&$value, &$prev, $label, $placeholder, $hint): string {
            while (true) {
                $shown = $value === '' ? $placeholder : $value;
                $body = '  '.$label."\n  ".$shown."\n".($hint !== '' ? '  '.Terminal::muted($hint)."\n" : '').Terminal::muted('  Ctrl+D to submit')."\n";
                $this->redraw($body, $prev);
                $key = $this->read_key();
                if ($key === 'eof') {
                    $this->clear_frame($prev);
                    $this->write_submitted($label, $value);

                    return $value;
                }
                if ($key === 'enter') {
                    $value .= "\n";
                    continue;
                }
                if ($key === 'backspace') {
                    $value = \mb_substr($value, 0, \max(0, \mb_strlen($value) - 1));
                    continue;
                }
                if (\strlen($key) === 1 && $key >= ' ') {
                    $value .= $key;
                }
            }
        });
    }

    private function interactive_confirm(string $label, bool $default, string $yes, string $no, string $hint): bool
    {
        $choice = $default;
        $prev = 0;
        $this->show_cursor(false);

        try {
            return $this->with_raw(function () use (&$choice, &$prev, $label, $yes, $no, $hint): bool {
                while (true) {
                    $yes_l = $choice ? Terminal::CYAN.$yes.Terminal::RESET : Terminal::muted($yes);
                    $no_l = ! $choice ? Terminal::CYAN.$no.Terminal::RESET : Terminal::muted($no);
                    $frame = '  '.$label.'  '.$yes_l.' / '.$no_l."\n";
                    if ($hint !== '') {
                        $frame .= '  '.Terminal::muted($hint)."\n";
                    }
                    $this->redraw($frame, $prev);
                    $key = $this->read_key();
                    if ($key === 'enter') {
                        $this->clear_frame($prev);
                        $this->write_submitted($label, $choice ? $yes : $no);

                        return $choice;
                    }
                    if ($key === 'left' || $key === 'right' || $key === 'up' || $key === 'down' || $key === 'tab') {
                        $choice = ! $choice;
                        continue;
                    }
                    if ($key === 'y' || $key === 'Y') {
                        $choice = true;
                        continue;
                    }
                    if ($key === 'n' || $key === 'N') {
                        $choice = false;
                    }
                }
            });
        } finally {
            $this->show_cursor(true);
        }
    }

    /**
     * @param array<int|string, string> $options
     * @param mixed $default
     * @param string|callable(mixed): ?string|null $info
     * @return mixed
     */
    private function interactive_select(
        string $label,
        array $options,
        mixed $default,
        int $scroll,
        string $hint,
        string|callable|null $info,
        bool $multi,
        bool $search,
    ): mixed {
        $assoc = ! \array_is_list($options);
        $query = '';
        $keys = \array_keys($options);
        $index = $this->default_index($keys, $options, $default);
        $checked = [];
        if ($multi) {
            foreach ((array) $default as $item) {
                $checked[(string) $item] = true;
            }
        }
        $prev = 0;
        $this->show_cursor($search);

        try {
            return $this->with_raw(function () use (
                &$index, &$query, &$checked, &$prev, &$keys, $label, $options, $scroll, $hint, $info, $multi, $search, $assoc,
            ): mixed {
                while (true) {
                    $visible = $this->filtered($options, $query, $search);
                    $keys = \array_keys($visible);
                    if ($keys === []) {
                        $index = 0;
                    } else {
                        $index = \max(0, \min($index, \count($keys) - 1));
                    }
                    $this->redraw($this->frame_select($label, $visible, $keys, $index, $scroll, $hint, $info, $multi, $checked, $query, $search), $prev);
                    $key = $this->read_key();
                    if ($key === 'enter') {
                        $this->clear_frame($prev);
                        if ($multi) {
                            $picked = $this->checked_values($options, $checked, $assoc);
                            $this->write_submitted($label, $picked === [] ? 'none' : \implode(', ', \array_map('strval', $picked)));

                            return $picked;
                        }
                        if ($keys === []) {
                            continue;
                        }
                        $picked_key = $keys[$index];
                        $this->write_submitted($label, $visible[$picked_key]);

                        return $assoc ? $picked_key : $visible[$picked_key];
                    }
                    if ($key === 'up') {
                        $index = $index > 0 ? $index - 1 : \max(0, \count($keys) - 1);
                        continue;
                    }
                    if ($key === 'down') {
                        $index = $keys === [] ? 0 : ($index + 1) % \count($keys);
                        continue;
                    }
                    if ($key === 'space' && $multi && $keys !== []) {
                        $k = (string) $keys[$index];
                        if (isset($checked[$k])) {
                            unset($checked[$k]);
                        } else {
                            $checked[$k] = true;
                        }
                        continue;
                    }
                    if ($search && $key === 'backspace') {
                        $query = \mb_substr($query, 0, \max(0, \mb_strlen($query) - 1));
                        $index = 0;
                        continue;
                    }
                    if ($search && \strlen($key) === 1 && $key >= ' ') {
                        $query .= $key;
                        $index = 0;
                    }
                }
            });
        } finally {
            $this->show_cursor(true);
        }
    }

    /**
     * @param array<int|string, string> $options
     * @return array<int|string, string>
     */
    private function filtered(array $options, string $query, bool $search): array
    {
        if (! $search || $query === '') {
            return $options;
        }
        $q = \mb_strtolower($query);
        $out = [];
        foreach ($options as $key => $label) {
            if (\str_contains(\mb_strtolower((string) $label), $q) || \str_contains(\mb_strtolower((string) $key), $q)) {
                $out[$key] = $label;
            }
        }

        return $out;
    }

    /**
     * @param array<int|string, string> $options
     * @param array<string, true> $checked
     * @return list<mixed>
     */
    private function checked_values(array $options, array $checked, bool $assoc): array
    {
        $out = [];
        foreach ($options as $key => $label) {
            if (! isset($checked[(string) $key])) {
                continue;
            }
            $out[] = $assoc ? $key : $label;
        }

        return $out;
    }

    /**
     * @param array<int|string, string> $options
     */
    private function default_index(array $keys, array $options, mixed $default): int
    {
        if ($default === null || \is_array($default)) {
            return 0;
        }
        foreach ($keys as $i => $key) {
            if ($key === $default || $options[$key] === $default) {
                return $i;
            }
        }

        return 0;
    }

    /**
     * @param array<int|string, string> $options
     */
    private function default_choice(array $options, mixed $default): mixed
    {
        if ($default !== null && (\array_key_exists($default, $options) || \in_array($default, $options, true))) {
            return \array_is_list($options) ? (\in_array($default, $options, true) ? $default : $options[$default] ?? $default) : $default;
        }
        $first_key = \array_key_first($options);

        return \array_is_list($options) ? $options[$first_key] : $first_key;
    }

    /**
     * @param array<int|string, string> $options
     * @param list<mixed> $default
     * @return list<mixed>
     */
    private function default_multi(array $options, array $default): array
    {
        $assoc = ! \array_is_list($options);
        $out = [];
        foreach ($default as $item) {
            if ($assoc && \array_key_exists($item, $options)) {
                $out[] = $item;
            } elseif (! $assoc && \in_array($item, $options, true)) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * @param array<int|string, string> $options
     */
    private function fake_choice(array $options, mixed $fake, bool $multi): mixed
    {
        $assoc = ! \array_is_list($options);
        if ($multi) {
            $out = [];
            foreach ((array) $fake as $item) {
                if ($assoc && \array_key_exists($item, $options)) {
                    $out[] = $item;
                } elseif (! $assoc && \in_array($item, $options, true)) {
                    $out[] = $item;
                } elseif ($assoc && \in_array($item, $options, true)) {
                    $out[] = \array_search($item, $options, true);
                }
            }

            return $out;
        }
        if ($assoc && \array_key_exists($fake, $options)) {
            return $fake;
        }
        if (\in_array($fake, $options, true)) {
            return $assoc ? \array_search($fake, $options, true) : $fake;
        }
        $first = \array_key_first($options);

        return $assoc ? $first : $options[$first];
    }

    /**
     * @param array<int|string, string>|callable $options
     * @return list<string>
     */
    private function suggest_matches(string $value, array|callable $options): array
    {
        $list = \is_callable($options) ? $options($value) : $options;
        if (! \is_array($list)) {
            return [];
        }
        if ($value === '') {
            return \array_values(\array_map('strval', $list));
        }
        $q = \mb_strtolower($value);
        $out = [];
        foreach ($list as $item) {
            $s = (string) $item;
            if (\str_contains(\mb_strtolower($s), $q)) {
                $out[] = $s;
            }
        }

        return $out;
    }

    /**
     * @param list<string> $matches
     */
    private function frame_line(string $label, string $value, string $placeholder, string $hint, bool $hidden, array $matches): string
    {
        $shown = $value === '' ? Terminal::muted($placeholder) : ($hidden ? \str_repeat('*', \strlen($value)) : $value);
        $frame = '  '.$label."\n  ".$shown."\n";
        if ($hint !== '') {
            $frame .= '  '.Terminal::muted($hint)."\n";
        }
        foreach (\array_slice($matches, 0, 5) as $match) {
            $frame .= '  '.Terminal::muted($match)."\n";
        }

        return $frame;
    }

    /**
     * @param array<int|string, string> $visible
     * @param list<int|string> $keys
     * @param array<string, true> $checked
     * @param string|callable(mixed): ?string|null $info
     */
    private function frame_select(
        string $label,
        array $visible,
        array $keys,
        int $index,
        int $scroll,
        string $hint,
        string|callable|null $info,
        bool $multi,
        array $checked,
        string $query,
        bool $search,
    ): string {
        $scroll = \max(1, $scroll);
        $count = \count($keys);
        $start = 0;
        if ($count > $scroll) {
            $start = \min(\max(0, $index - \intdiv($scroll, 2)), $count - $scroll);
        }
        $frame = '  '.$label.($search ? ' '.Terminal::muted($query === '' ? '/' : '/'.$query) : '')."\n";
        if ($hint !== '') {
            $frame .= '  '.Terminal::muted($hint)."\n";
        }
        $slice = \array_slice($keys, $start, $scroll);
        foreach ($slice as $i => $key) {
            $pos = $start + $i;
            $active = $pos === $index;
            $mark = $active ? Terminal::CYAN.'> '.Terminal::RESET : '  ';
            $box = '';
            if ($multi) {
                $on = isset($checked[(string) $key]);
                $box = ($on ? '[x] ' : '[ ] ');
            }
            $text = $visible[$key];
            $frame .= '  '.$mark.$box.($active ? Terminal::CYAN.$text.Terminal::RESET : $text)."\n";
        }
        $info_text = $this->info_text($info, $keys[$index] ?? null, $visible);
        if ($info_text !== null && $info_text !== '') {
            $frame .= '  '.Terminal::muted($info_text)."\n";
        }

        return $frame;
    }

    /**
     * @param string|callable(mixed): ?string|null $info
     * @param array<int|string, string> $visible
     */
    private function info_text(string|callable|null $info, mixed $key, array $visible): ?string
    {
        if ($info === null || $key === null) {
            return null;
        }
        if (\is_string($info)) {
            return $info;
        }
        $value = \array_is_list($visible) ? ($visible[$key] ?? $key) : $key;
        $text = $info($value);

        return \is_string($text) ? $text : null;
    }

    private function write_submitted(string $label, string $value): void
    {
        echo '  '.Terminal::GREEN.'*'.Terminal::RESET.' '.$label."\n";
        if ($value !== '') {
            echo '    '.$value."\n";
        }
    }

    private function redraw(string $frame, int &$prev): void
    {
        if ($prev > 0) {
            echo "\033[{$prev}A\033[J";
        }
        echo $frame;
        $prev = \substr_count($frame, "\n");
    }

    private function clear_frame(int $prev): void
    {
        if ($prev > 0) {
            echo "\033[{$prev}A\033[J";
        }
    }

    private function show_cursor(bool $on): void
    {
        echo $on ? "\033[?25h" : "\033[?25l";
    }

    /**
     * @template T
     * @param callable(): T $fn
     * @return T
     */
    private function with_raw(callable $fn): mixed
    {
        $saved = $this->stty_save();
        $this->stty(['-echo', '-icanon', 'min', '1', 'time', '0']);
        try {
            return $fn();
        } finally {
            $this->stty_restore($saved);
        }
    }

    private function read_key(): string
    {
        if (! \defined('STDIN') || ! \is_resource(STDIN)) {
            throw new Cancelled('Cancelled.');
        }
        $c = \fread(STDIN, 1);
        if (! \is_string($c) || $c === '') {
            throw new Cancelled('Cancelled.');
        }
        if ($c === "\033") {
            \stream_set_blocking(STDIN, false);
            $rest = \fread(STDIN, 3);
            \stream_set_blocking(STDIN, true);
            $seq = $c.(\is_string($rest) ? $rest : '');

            return match (true) {
                \str_ends_with($seq, 'A') => 'up',
                \str_ends_with($seq, 'B') => 'down',
                \str_ends_with($seq, 'C') => 'right',
                \str_ends_with($seq, 'D') => 'left',
                default => $seq,
            };
        }

        return match ($c) {
            "\n", "\r" => 'enter',
            "\t" => 'tab',
            "\x7f", "\x08" => 'backspace',
            "\x03" => throw new Cancelled('Cancelled.'),
            "\x04" => 'eof',
            ' ' => 'space',
            default => $c,
        };
    }

    private function stty_save(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return '';
        }
        $out = $this->shell('stty -g 2>/dev/null');

        return \trim($out);
    }

    /**
     * @param list<string> $args
     */
    private function stty(array $args): void
    {
        if (PHP_OS_FAMILY === 'Windows' || $args === []) {
            return;
        }
        $this->shell('stty '.\implode(' ', \array_map('escapeshellarg', $args)).' 2>/dev/null');
    }

    private function stty_restore(string $saved): void
    {
        if ($saved === '') {
            $this->stty(['echo', 'icanon']);

            return;
        }
        $this->shell('stty '.\escapeshellarg($saved).' 2>/dev/null');
    }

    private function shell(string $command): string
    {
        $out = [];
        \exec($command, $out);

        return \implode("\n", $out);
    }
}
