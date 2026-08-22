<?php declare(strict_types=1);

namespace Webkernel\Console\Server;

use Webkernel\Console\ExitCode;
use Webkernel\Console\Terminal;
use Webkernel\Performance\Performance;
use Webkernel\Performance\Status;

final class Engine
{
    private const int MAX_PORT_TRIES = 10;

    private string $host = '127.0.0.1';
    private int $port = 8000;
    private string $public_dir = '';
    private string $root = '';
    private string $router = '';
    private bool $profile_lifecycle = false;
    /** true force on, false force off, null inherit php.ini / preference */
    private ?bool $jit = null;
    /** @var resource|null */
    private $process = null;
    /** @var array<int, resource> */
    private array $pipes = [];
    private string $output_buffer = '';
    private bool $banner_shown = false;
    private bool $address_in_use = false;
    private bool $is_first_request = true;

    /** @var array<int, array{started: int|float, request: string, status: int, render_ms: float|null, type: string, times: list<array{ms: float|null, run_ms: float|null, read_ms: float|null, path: string, inferred?: bool}>, classes: list<string>, mem: int|null}> */
    private array $requests_pool = [];

    /** @var array<string, array{render_ms: float|null, request_ms: float, times: array<string, array{ms: float, run_ms: float, read_ms: float}>}> */
    private array $baselines = [];

    public function serve(string $host = '127.0.0.1', int $port = 8000, bool $profile_lifecycle = false, ?bool $jit = null): ExitCode
    {
        $this->host = $host;
        $this->port = $port;
        $this->profile_lifecycle = $profile_lifecycle;
        $this->jit = $jit;
        $this->public_dir = webapp_path('public');
        $this->root = webapp_path();
        $this->router = __DIR__.'/router.php';
        $this->register_signals();
        for ($try = 0; $try < self::MAX_PORT_TRIES; $try++) {
            $exit_code = $this->run_process();
            if ($this->address_in_use) {
                $this->address_in_use = false;
                $this->banner_shown = false;
                $this->port++;
                continue;
            }

            return ExitCode::tryFrom(is_int($exit_code) ? $exit_code : 0) ?? ExitCode::SUCCESS;
        }
        fwrite(STDERR, "Unable to listen on {$this->host} after ".self::MAX_PORT_TRIES." port attempts.\n");

        return ExitCode::ERROR;
    }

    private function run_process(): ?int
    {
        $command = [PHP_BINARY];
        if ($this->jit === true) {
            foreach (Performance::jit_engine_args() as $part) {
                $command[] = $part;
            }
        } elseif ($this->jit === false) {
            foreach (Performance::jit_disable_args() as $part) {
                $command[] = $part;
            }
        }
        $command[] = '-S';
        $command[] = "{$this->host}:{$this->port}";
        $command[] = '-t';
        $command[] = $this->public_dir;
        $command[] = $this->router;
        $env = null;
        if ($this->profile_lifecycle) {
            $env = getenv();
            if (! is_array($env)) {
                $env = [];
            }
            $env['WEBKERNEL_PROFILE_LIFECYCLE'] = '1';
        }
        $pipes = [];
        $process = proc_open($command, [
            0 => ['file', '/dev/null', 'r'],
            1 => ['pipe', 'w'],
            2 => ['redirect', 1],
        ], $pipes, $this->root, $env);
        if ($process === false) {
            fwrite(STDERR, "Failed to start PHP development server.\n");

            return 1;
        }
        $this->process = $process;
        $stdout = $pipes[1] ?? null;
        if ($stdout === null) {
            $this->close_process();
            fwrite(STDERR, "Failed to capture server output.\n");

            return 1;
        }
        /** @var resource $stdout */
        $kept = [];
        foreach ($pipes as $fd => $pipe) {
            if ($pipe === null) {
                continue;
            }
            /** @var resource $pipe */
            $kept[(int) $fd] = $pipe;
        }
        $this->pipes = $kept;
        stream_set_blocking($stdout, false);
        while (true) {
            $chunk = fread($stdout, 8192);
            if (is_string($chunk) && $chunk !== '') {
                $this->ingest($chunk);
            }
            $status = proc_get_status($process);
            if (! $status['running']) {
                $leftover = stream_get_contents($stdout);
                if (is_string($leftover) && $leftover !== '') {
                    $this->ingest($leftover);
                }
                $this->close_process();

                return $status['exitcode'];
            }
            usleep(50_000);
        }
    }

    private function ingest(string $chunk): void
    {
        $this->output_buffer .= $chunk;
        $lines = explode("\n", $this->output_buffer);
        $this->output_buffer = (string) array_pop($lines);
        foreach ($lines as $line) {
            $this->handle_line(trim($line));
        }
    }

    private function handle_line(string $line): void
    {
        if ($line === '') {
            return;
        }
        if (str_contains($line, 'Failed to listen') || str_contains($line, 'Address already in use')) {
            $this->address_in_use = true;

            return;
        }
        if (str_contains($line, 'Development Server (http')) {
            if (! $this->banner_shown) {
                $this->banner_shown = true;
                $this->render_banner();
            }

            return;
        }
        if (str_contains($line, 'Closed without sending a request') || str_contains($line, 'Failed to poll event')) {
            return;
        }

        $port = $this->request_port($line);
        if (str_ends_with($line, ' Accepted') && $port !== null) {
            $this->requests_pool[$port] = [
                'started' => hrtime(true), 'request' => '', 'status' => 200, 'render_ms' => null, 'type' => 'WEB',
                'times' => [], 'classes' => [], 'mem' => null,
            ];

            return;
        }

        if (preg_match('/^webkernel-include (\S+) (\S+) (\S+) (.+)$/', $line, $tm) === 1) {
            $this->trace_time([
                'ms' => $tm[1] === '?' ? null : (float) $tm[1],
                'run_ms' => $tm[2] === '?' ? null : (float) $tm[2],
                'read_ms' => $tm[3] === '?' ? null : (float) $tm[3],
                'path' => $tm[4],
            ]);

            return;
        }
        if (str_starts_with($line, 'webkernel-class ')) {
            $this->trace_push('classes', substr($line, 16));

            return;
        }
        if (str_starts_with($line, 'webkernel-mem ')) {
            $this->trace_push('mem', substr($line, 14));

            return;
        }
        if (str_starts_with($line, 'webkernel-type ')) {
            $this->trace_push('type', substr($line, 15));

            return;
        }

        if (preg_match('/\[(\d+)\]:\s+(\S+)\s+(\S+)/', $line, $m) === 1) {
            $status = (int) $m[1];
            $request = $m[2].' '.$m[3];
            $render_ms = null;
            if (preg_match('/render=([0-9.]+)/', $line, $r) === 1) {
                $render_ms = (float) $r[1];
            }
            if ($port !== null && isset($this->requests_pool[$port])) {
                $this->requests_pool[$port]['request'] = $request;
                $this->requests_pool[$port]['status'] = $status;
                if ($render_ms !== null) {
                    $this->requests_pool[$port]['render_ms'] = $render_ms;
                }

                return;
            }
            $this->log_request($request, $status, hrtime(true), ['render_ms' => $render_ms]);

            return;
        }

        if (str_ends_with($line, ' Closing') && $port !== null) {
            $entry = $this->requests_pool[$port] ?? null;
            unset($this->requests_pool[$port]);
            if ($entry === null || $entry['request'] === '') {
                return;
            }
            $this->log_request($entry['request'], $entry['status'], $entry['started'], [
                'render_ms' => $entry['render_ms'], 'times' => $entry['times'],
                'classes' => $entry['classes'], 'mem' => $entry['mem'], 'type' => $entry['type'],
            ]);

            return;
        }

        if (str_starts_with($line, '[')) {
            $line = trim((string) strstr($line, '] '));
        }
        if ($line !== '') {
            echo '  '.Terminal::muted($line)."\n";
        }
    }

    private function request_port(string $line): ?int
    {
        if (preg_match('/:(\d+)\s+(?:Accepted|Closing|\[\d+\])/', $line, $m) === 1) {
            return (int) $m[1];
        }

        return null;
    }

    private function render_banner(): void
    {
        $banner_start_message = [
            'This feature is part of Webkernel.',
            '(c) 2025 - '.(date('Y') + 1).' Numerimondes, El Moumen Yassine',
            'Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>',
            'For the full copyright and license information, please view the LICENSE',
            'file that was distributed with the source code.',
            'Made in Morocco.',
        ];
        echo "\n";
        foreach ($banner_start_message as $line) {
            echo Terminal::muted('  //> '.$line)."\n";
        }

        $url = "http://{$this->host}:{$this->port}";
        $child = Status::inspect('cli-server');
        $ext = extension_loaded('Zend OPcache');
        $opcache_active = $this->jit === true ? $ext : $child->opcache;
        $jit_active = $this->jit === true ? $ext : ($this->jit === false ? false : $child->jit);
        $opcache_status = $opcache_active ? Terminal::GREEN.'enabled'.Terminal::RESET : Terminal::GRAY.'disabled'.Terminal::RESET;
        $jit_status = $jit_active ? Terminal::GREEN.'enabled'.Terminal::RESET : Terminal::GRAY.'disabled'.Terminal::RESET;
        echo "\n";
        webterminal()->info('Server running on '.Terminal::BOLD.'['.$url.']'.Terminal::RESET);
        echo "\n";
        echo '  '.Terminal::muted('PHP Version: '.PHP_VERSION.' | OPcache: '.$opcache_status.' | JIT: '.$jit_status)."\n\n";
        if ($this->jit === true && ! extension_loaded('Zend OPcache')) {
            webterminal()->warning('Zend OPcache is not loaded; --with-jit has no effect.');
            echo "\n";
        }
        webterminal()->warning('This server is for local development only. Behavior differs from production (Nginx/Apache/FPM).');
        echo "\n";
        if ($this->profile_lifecycle) {
            webterminal()->info(Terminal::muted('--profile-lifecycle').' include cost (run/read); ?? when OPcache hid the file stream.');
            echo "\n";
        }
        echo '  '.Terminal::muted('Press Ctrl+C to stop the server')."\n\n";
    }

    /**
     * @param array{render_ms?: float|null, times?: list<array{ms: float|null, run_ms: float|null, read_ms: float|null, path: string}>, classes?: list<string>, mem?: int|string|null, type?: string} $timings
     */
    private function log_request(string $request, int $status_code, float|int $start_time, array $timings = []): void
    {
        if (! $this->is_first_request && $this->profile_lifecycle) {
            echo "\n";
        }
        $this->is_first_request = false;

        $request_ms = (hrtime(true) - $start_time) / 1e6;
        $timestamp = date('H:i:s');
        $width = $this->line_width();

        $is_api = $timings['type'] ?? 'WEB';
        $type_badge = $is_api === 'API' ? Terminal::badge('API', '45', '97') : Terminal::badge('WEB', '42', '97');
        $reason = $this->status_reason($status_code);

        $left = sprintf('  %s %s %s%s %s %s%s%s', $type_badge, Terminal::muted($timestamp), Terminal::status_color($status_code), $status_code, $reason, Terminal::CYAN, $request, Terminal::RESET);
        $right_plain = $this->metrics_plain($timings['render_ms'] ?? null, $request_ms);
        $right = Terminal::muted($right_plain);

        $left_len = $this->visible_len($left);
        $right_len = strlen($right_plain);
        $dots = $width - $left_len - $right_len - 2;

        if ($dots < 2) {
            $overflow = 2 - $dots;
            $max_req = max(8, strlen($request) - $overflow);
            $request_short = $this->shorten($request, $max_req);
            $left = sprintf('  %s %s %s%s %s %s%s%s', $type_badge, Terminal::muted($timestamp), Terminal::status_color($status_code), $status_code, $reason, Terminal::CYAN, $request_short, Terminal::RESET);
            $dots = max(2, $width - $this->visible_len($left) - $right_len - 2);
        }

        echo $left.' '.Terminal::muted(str_repeat('.', $dots)).' '.$right."\n";
        if (! $this->profile_lifecycle) {
            return;
        }

        $times = $timings['times'] ?? [];
        $classes = $timings['classes'] ?? [];
        $mem = $timings['mem'] ?? null;
        $mem_label = is_numeric($mem) ? number_format(((int) $mem) / 1048576, 1).'MB' : '?';

        $has_real = false;
        foreach ($times as $row) {
            if ($row['ms'] !== null) {
                $has_real = true;
                break;
            }
        }

        $inferred_count = 0;
        if (! $has_real && isset($this->baselines[$request])) {
            $baseline = $this->baselines[$request];
            $base_metric = $baseline['render_ms'] ?? $baseline['request_ms'];
            $curr_metric = $timings['render_ms'] ?? $request_ms;
            $ratio = ($base_metric > 0.0) ? ($curr_metric / $base_metric) : 1.0;

            foreach ($times as &$row) {
                if ($row['ms'] === null && isset($baseline['times'][$row['path']])) {
                    $base_row = $baseline['times'][$row['path']];
                    $row['ms'] = $base_row['ms'] * $ratio;
                    $row['run_ms'] = $base_row['run_ms'] * $ratio;
                    $row['read_ms'] = $base_row['read_ms'] * $ratio;
                    $row['inferred'] = true;
                    $inferred_count++;
                }
            }
            unset($row);
        } elseif ($has_real) {
            $this->baselines[$request] = ['render_ms' => $timings['render_ms'] ?? null, 'request_ms' => $request_ms, 'times' => []];
            foreach ($times as $row) {
                if ($row['ms'] !== null) {
                    $this->baselines[$request]['times'][$row['path']] = [
                        'ms' => (float) $row['ms'], 'run_ms' => (float) $row['run_ms'], 'read_ms' => (float) $row['read_ms'],
                    ];
                }
            }
        }

        $valid_times = [];
        foreach ($times as $row) {
            if ($row['ms'] !== null) {
                $valid_times[] = $row['ms'];
            }
        }
        sort($valid_times);
        $count_valid = count($valid_times);
        $median = 0.0;
        if ($count_valid > 0) {
            $mid = (int) floor(($count_valid - 1) / 2);
            $median = ($count_valid % 2 === 0) ? ($valid_times[$mid] + $valid_times[$mid + 1]) / 2.0 : $valid_times[$mid];
        }

        $grades = ['e' => 0, 'g' => 0, 'f' => 0, 's' => 0];
        foreach ($valid_times as $t) {
            if ($t < 0.1) {
                $grades['e']++;
            } elseif ($t < 0.5) {
                $grades['g']++;
            } elseif ($t < 1.0) {
                $grades['f']++;
            } else {
                $grades['s']++;
            }
        }
        echo "\n";

        $summary1 = sprintf('%s -> %d %s | files=%d timed=%d inferred=%d classes=%d mem=%s', $request, $status_code, $reason, count($times), $count_valid, $inferred_count, count($classes), $mem_label);
        $summary2 = sprintf('grades excellent=%d good=%d fair=%d slow=%d (%de %dg %df %ds) median=%.3fms', $grades['e'], $grades['g'], $grades['f'], $grades['s'], $grades['e'], $grades['g'], $grades['f'], $grades['s'], $median);

        echo '  '.Terminal::muted($summary1)."\n";
        echo '  '.Terminal::muted($summary2)."\n";

        if ($times === [] && $classes === []) {
            return;
        }

        echo "\n";
        echo '  '.Terminal::RESET.'  TOTAL     RUN     READ     FILE'.Terminal::RESET."\n\n";
        echo '  '.Terminal::RESET.' ---------------------------------'.Terminal::RESET."\n\n";
        foreach ($times as $row) {
            $ms = $row['ms'];
            $run = $row['run_ms'];
            $read = $row['read_ms'];
            $path = $row['path'];
            $inferred = $row['inferred'] ?? false;
            $flag = '';
            if ($ms !== null) {
                if ($median > 0.0) {
                    if ($ms > 2.0 * $median) {
                        $flag = ' '.Terminal::RED.'[SLOW] '.Terminal::RESET;
                    } elseif ($ms < 0.5 * $median) {
                        $flag = ' '.Terminal::GREEN.'[FAST] '.Terminal::RESET;
                    } else {
                        $flag = ' '.Terminal::GRAY.' [OK]  '.Terminal::RESET;
                    }
                } else {
                    $flag = ' '.Terminal::GRAY.' [OK]  '.Terminal::RESET;
                }
            }
            $ms_str = $ms === null ? '      ??' : sprintf('%c%7.3f', $inferred ? '~' : ' ', $ms);
            $run_str = $run === null ? '      ??' : sprintf('%c%7.3f', $inferred ? '~' : ' ', $run);
            $read_str = $read === null ? '      ??' : sprintf('%c%7.3f', $inferred ? '~' : ' ', $read);

            echo sprintf("  %s  %s  %s  %s%s\n", Terminal::muted($ms_str), Terminal::muted($run_str), Terminal::muted($read_str), $flag, Terminal::muted(' ->  ').$path);
        }
        echo "\n";
        foreach ($classes as $class) {
            echo '  '.Terminal::muted('class '.$class)."\n";
        }
    }

    private function metrics_plain(?float $render_ms, float $request_ms): string
    {
        $request = 'request '.number_format($request_ms, 2).'ms';

        return $render_ms === null ? $request : 'render '.number_format($render_ms, 2).'ms  '.$request;
    }

    private function line_width(): int
    {
        return max(60, (int) (Terminal::columns() * 0.95));
    }

    private function visible_len(string $text): int
    {
        $plain = preg_replace('/\033\[[0-9;]*m/', '', $text);

        return strlen(is_string($plain) ? $plain : $text);
    }

    private function shorten(string $text, int $max): string
    {
        if ($max < 2 || strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, $max - 1).'...';
    }

    private function status_reason(int $code): string
    {
        return match ($code) {
            200 => 'OK', 201 => 'Created', 204 => 'No Content', 301 => 'Moved Permanently', 302 => 'Found', 304 => 'Not Modified',
            400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed',
            500 => 'Internal Server Error', 502 => 'Bad Gateway', 503 => 'Service Unavailable', default => 'Response',
        };
    }

    /**
     * @param array{ms: float|null, run_ms: float|null, read_ms: float|null, path: string} $row
     */
    private function trace_time(array $row): void
    {
        end($this->requests_pool);
        $port = key($this->requests_pool);
        if (! is_int($port)) {
            return;
        }
        $this->requests_pool[$port]['times'][] = $row;
    }

    private function trace_push(string $kind, string $value): void
    {
        end($this->requests_pool);
        $port = key($this->requests_pool);
        if (! is_int($port)) {
            return;
        }
        if ($kind === 'mem') {
            $this->requests_pool[$port]['mem'] = (int) $value;

            return;
        }
        if ($kind === 'type') {
            $this->requests_pool[$port]['type'] = $value;

            return;
        }
        $this->requests_pool[$port][$kind][] = $value;
    }

    private function register_signals(): void
    {
        if (! function_exists('pcntl_async_signals') || ! function_exists('pcntl_signal')) {
            return;
        }
        pcntl_async_signals(true);
        $stop = function (): void {
            $this->close_process();
            exit(0);
        };
        pcntl_signal(SIGINT, $stop);
        pcntl_signal(SIGTERM, $stop);
        pcntl_signal(SIGHUP, $stop);
        pcntl_signal(SIGQUIT, $stop);
        register_shutdown_function(function (): void {
            $this->close_process();
        });
    }

    private function close_process(): void
    {
        $process = $this->process;
        if ($process === null) {
            $this->pipes = [];

            return;
        }
        $status = proc_get_status($process);
        if ($status['running']) {
            proc_terminate($process, SIGTERM);
            $deadline = microtime(true) + 2;
            while (proc_get_status($process)['running'] && microtime(true) < $deadline) {
                usleep(50_000);
            }
            if (proc_get_status($process)['running']) {
                proc_terminate($process, SIGKILL);
            }
        }
        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        $this->pipes = [];
        proc_close($process);
        $this->process = null;
    }
}
