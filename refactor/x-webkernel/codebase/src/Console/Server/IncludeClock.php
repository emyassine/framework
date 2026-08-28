<?php declare(strict_types=1);

namespace Webkernel\Console\Server;

/**
 * File-stream wrapper that times include cost for `--profile-lifecycle`.
 * Registered only in the PHP built-in server child.
 */
final class IncludeClock
{
    /** @var array<string, array{read_ns: int, run_ns: int, bytes: int}> */
    private static array $stats = [];
    private static bool $active = false;
    private static int $nest = 0;
    private static ?string $pending = null;
    private static float $pending_t = 0.0;

    /** @var resource|null */
    private $handle = null;
    private string $path = '';
    private float $t_open = 0.0;

    public static function start(): void
    {
        self::$stats = [];
        self::$pending = null;
        self::$pending_t = 0;
        self::$nest = 0;
        self::$active = true;
        \stream_wrapper_unregister('file');
        \stream_wrapper_register('file', self::class);
    }

    /**
     * @return array<string, array{ms: float, read_ms: float, run_ms: float, bytes: int}>
     */
    public static function stop(): array
    {
        $now = \hrtime(true);
        if (self::$pending !== null) {
            self::add_run(self::$pending, (int) ($now - self::$pending_t));
            self::$pending = null;
        }
        if (self::$active) {
            \stream_wrapper_restore('file');
            self::$active = false;
        }
        $out = [];
        foreach (self::$stats as $path => $row) {
            $out[$path] = [
                'ms' => ($row['read_ns'] + $row['run_ns']) / 1e6,
                'read_ms' => $row['read_ns'] / 1e6,
                'run_ms' => $row['run_ns'] / 1e6,
                'bytes' => $row['bytes'],
            ];
        }
        \uasort($out, static fn (array $a, array $b): int => $b['ms'] <=> $a['ms']);

        return $out;
    }

    public function stream_open($path, $mode, $options, &$opened_path): bool
    {
        self::flush_pending();
        $this->path = $path;
        $this->t_open = \hrtime(true);
        $use_path = (bool) ((int) $options & STREAM_USE_PATH);
        $handle = self::with_real(static fn () => \fopen($path, $mode, $use_path));
        if ($handle === false || $handle === null) {
            return false;
        }
        /** @var resource $handle */
        $this->handle = $handle;
        $opened_path = $path;
        if (! isset(self::$stats[$path])) {
            self::$stats[$path] = ['read_ns' => 0, 'run_ns' => 0, 'bytes' => 0];
        }

        return true;
    }

    public function stream_read($count): string|false
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \fread($handle, $count);
    }

    public function stream_write($data): int|false
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \fwrite($handle, $data);
    }

    public function stream_eof(): bool
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \feof($handle);
    }

    public function stream_seek($offset, $whence = SEEK_SET): bool
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \fseek($handle, $offset, $whence) === 0;
    }

    public function stream_tell(): int
    {
        $handle = $this->handle;
        if ($handle === null) {
            return 0;
        }
        $pos = \ftell($handle);

        return \is_int($pos) ? $pos : 0;
    }

    public function stream_flush(): bool
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \fflush($handle);
    }

    public function stream_truncate($new_size): bool
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \ftruncate($handle, $new_size);
    }

    public function stream_lock($operation): bool
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }
        $operation = (int) $operation;
        if ($operation === 0) {
            return true;
        }
        $lock = $operation & (LOCK_SH | LOCK_EX | LOCK_UN | LOCK_NB);
        if (($lock & (LOCK_SH | LOCK_EX | LOCK_UN)) === 0) {
            return false;
        }

        return \flock($handle, $lock);
    }

    public function stream_stat(): array|false
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \fstat($handle);
    }

    public function stream_set_option($option, $arg1, $arg2): bool
    {
        // PHP_STREAM_OPTION_LOCKING is 5; not exported as a userland constant.
        if ((int) $option === 5) {
            return $this->stream_lock($arg1);
        }

        return false;
    }

    public function stream_close(): void
    {
        $bytes = 0;
        $handle = $this->handle;
        $this->handle = null;
        if ($handle !== null) {
            $stat = \fstat($handle);
            if (\is_array($stat)) {
                $bytes = (int) ($stat['size'] ?? 0);
            }
            \fclose($handle);
        }
        if (! isset(self::$stats[$this->path])) {
            self::$stats[$this->path] = ['read_ns' => 0, 'run_ns' => 0, 'bytes' => 0];
        }
        self::$stats[$this->path]['read_ns'] += (int) (\hrtime(true) - $this->t_open);
        self::$stats[$this->path]['bytes'] = \max(self::$stats[$this->path]['bytes'], $bytes);
        self::$pending = $this->path;
        self::$pending_t = \hrtime(true);
    }

    public function url_stat($path, $flags)
    {
        return self::with_real(static function () use ($path, $flags) {
            return ($flags & STREAM_URL_STAT_QUIET) ? @\stat($path) : \stat($path);
        });
    }

    public function mkdir($path, $mode, $options): bool
    {
        return (bool) self::with_real(static fn () => \mkdir($path, $mode, (bool) ($options & STREAM_MKDIR_RECURSIVE)));
    }

    public function rmdir($path, $options): bool
    {
        return (bool) self::with_real(static fn () => \rmdir($path));
    }

    public function rename($from, $to): bool
    {
        return (bool) self::with_real(static fn () => \rename($from, $to));
    }

    public function unlink($path): bool
    {
        return (bool) self::with_real(static fn () => \unlink($path));
    }

    public function dir_opendir($path, $options): bool
    {
        $handle = self::with_real(static fn () => \opendir($path));
        if ($handle === false || $handle === null) {
            return false;
        }
        /** @var resource $handle */
        $this->handle = $handle;

        return true;
    }

    public function dir_readdir(): string|false
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }

        return \readdir($handle);
    }

    public function dir_rewinddir(): bool
    {
        $handle = $this->handle;
        if ($handle === null) {
            return false;
        }
        \rewinddir($handle);

        return true;
    }

    public function dir_closedir(): void
    {
        $handle = $this->handle;
        $this->handle = null;
        if ($handle !== null) {
            \closedir($handle);
        }
    }

    private static function flush_pending(): void
    {
        if (self::$pending === null) {
            return;
        }
        self::add_run(self::$pending, (int) (\hrtime(true) - self::$pending_t));
        self::$pending = null;
    }

    private static function add_run(string $path, int $ns): void
    {
        if (! isset(self::$stats[$path])) {
            self::$stats[$path] = ['read_ns' => 0, 'run_ns' => 0, 'bytes' => 0];
        }
        self::$stats[$path]['run_ns'] += \max(0, $ns);
    }

    private static function with_real(callable $fn): mixed
    {
        if (self::$nest === 0) {
            \stream_wrapper_restore('file');
        }
        self::$nest++;
        try {
            return $fn();
        } finally {
            self::$nest--;
            if (self::$nest === 0 && self::$active) {
                \stream_wrapper_unregister('file');
                \stream_wrapper_register('file', self::class);
            }
        }
    }
}
