<?php declare(strict_types=1);

namespace Webkernel\Performance;

/**
 * Snapshot of this process. JIT cannot change after the engine starts.
 */
final readonly class Status
{
    public function __construct(
        public string $php_version,
        public bool $opcache,
        public bool $jit,
        public string $jit_mode,
        public string $jit_buffer,
    ) {
    }

    /**
     * @param string|null $sapi SAPI to evaluate. Default: this process. Pass cli-server for php -S.
     */
    public static function inspect(?string $sapi = null): self
    {
        $sapi ??= PHP_SAPI;
        $ext = extension_loaded('Zend OPcache');
        $cli = $sapi === 'cli' || $sapi === 'phpdbg';
        $opcache = $ext && ($cli ? self::ini_on('opcache.enable_cli') : self::ini_on('opcache.enable'));

        $mode = (string) ini_get('opcache.jit');
        $buffer = (string) ini_get('opcache.jit_buffer_size');
        $jit = false;

        if ($sapi === PHP_SAPI && $opcache && function_exists('opcache_get_status')) {
            $raw = opcache_get_status(false);
            if (is_array($raw)) {
                $opcache = (bool) ($raw['opcache_enabled'] ?? $opcache);
                $jit_raw = $raw['jit'] ?? null;
                if (is_array($jit_raw)) {
                    $jit = (bool) ($jit_raw['on'] ?? false);
                    if (isset($jit_raw['buffer_size']) && is_numeric($jit_raw['buffer_size'])) {
                        $buffer = (string) (int) $jit_raw['buffer_size'];
                    }
                }
            }
        } elseif ($opcache) {
            $jit = self::jit_ini_on($mode) && self::buffer_on($buffer);
        }

        return new self(
            php_version: PHP_VERSION,
            opcache: $opcache,
            jit: $jit,
            jit_mode: $mode !== '' ? $mode : 'disable',
            jit_buffer: $buffer,
        );
    }

    private static function ini_on(string $key): bool
    {
        $value = ini_get($key);
        if ($value === false) {
            return false;
        }
        $value = strtolower(trim($value));

        return $value !== '' && $value !== '0' && $value !== 'off' && $value !== 'false';
    }

    private static function jit_ini_on(string $mode): bool
    {
        $mode = strtolower(trim($mode));

        return $mode !== '' && $mode !== '0' && $mode !== 'disable' && $mode !== 'off' && $mode !== 'no';
    }

    private static function buffer_on(string $buffer): bool
    {
        $buffer = strtolower(trim($buffer));

        return $buffer !== '' && $buffer !== '0' && $buffer !== '0b' && $buffer !== '0k' && $buffer !== '0m' && $buffer !== '0g';
    }
}
