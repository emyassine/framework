<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Performance;

use Webkernel\Composables\ComposableContract;

/**
 * OPcache / JIT for this engine. JIT is a process-start flag
 * (php.ini or php -d). enable_jit() persists preference; restart to apply.
 *
 * Compiled views are already PHP. With JIT on, the Zend VM compiles hot
 * files to machine code. There is no faster PHP-level compiler to wrap.
 */
final class Performance implements ComposableContract
{
    public const JIT_MODE = 'tracing';

    public const JIT_BUFFER = '64M';

    private ?Status $status = null;

    public static function api_name(): string
    {
        return 'performance';
    }

    public function status(): Status
    {
        return $this->status ??= Status::inspect();
    }

    public function is_opcache(): bool
    {
        return $this->status()->opcache;
    }

    public function is_jit(): bool
    {
        return $this->status()->jit;
    }

    /**
     * Persist JIT-on for the next engine start. Does not enable JIT here.
     *
     * @return bool true when this process already runs with JIT
     */
    public function enable_jit(): bool
    {
        self::write_preference(true);

        return $this->is_jit();
    }

    /**
     * Persist JIT-off for the next engine start. Does not disable JIT here.
     *
     * @return bool true when this process already runs without JIT
     */
    public function disable_jit(): bool
    {
        self::write_preference(false);

        return ! $this->is_jit();
    }

    public function restart_required(): bool
    {
        $file = self::preference_path();
        if (! \is_file($file)) {
            return false;
        }

        return self::wants_jit() !== $this->is_jit();
    }

    /**
     * CLI argv fragments so the next process starts with JIT.
     *
     * @return list<string>
     */
    public static function jit_engine_args(): array
    {
        $args = [
            '-d', 'opcache.enable=1',
            '-d', 'opcache.enable_cli=1',
            '-d', 'opcache.jit='.self::JIT_MODE,
            '-d', 'opcache.jit_buffer_size='.self::JIT_BUFFER,
        ];
        if (! \extension_loaded('Zend OPcache')) {
            \array_unshift($args, '-d', 'zend_extension=opcache');
        }

        return $args;
    }

    /**
     * CLI argv fragments so the next process starts with JIT off.
     *
     * @return list<string>
     */
    public static function jit_disable_args(): array
    {
        return ['-d', 'opcache.jit=disable'];
    }

    public static function preference_path(): string
    {
        return webapp_path('platform/storage/framework/performance.php');
    }

    public static function wants_jit(?string $file = null): bool
    {
        $file ??= self::preference_path();
        if (! \is_file($file)) {
            return false;
        }
        $data = include $file;

        return \is_array($data) && ($data['jit'] ?? false) === true;
    }

    public static function write_preference(bool $jit, ?string $file = null): void
    {
        $file ??= self::preference_path();
        $dir = \dirname($file);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            throw new \RuntimeException('Unable to create '.$dir);
        }
        $ok = \file_put_contents(
            $file,
            "<?php declare(strict_types=1);\n\nreturn ".\var_export(['jit' => $jit], true).";\n",
            LOCK_EX,
        );
        if ($ok === false) {
            throw new \RuntimeException('Unable to write '.$file);
        }
    }
}
