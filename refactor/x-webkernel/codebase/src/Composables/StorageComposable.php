<?php declare(strict_types=1);

namespace Webkernel\Composables;

final class StorageComposable implements ComposableContract
{
    public static function api_name(): string
    {
        return 'storage';
    }

    public static function container_lifetime(): string
    {
        return 'singleton';
    }

    public function file_path(string $relative_path): string
    {
        $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');
        if (str_contains($relative_path, '..')) {
            throw new \InvalidArgumentException('Storage path must not contain ..');
        }

        return webapp_path($relative_path);
    }

    public function read(string $file): string
    {
        $path = $this->file_path($file);
        if (! is_file($path)) {
            throw new \RuntimeException('File not found: '.$file);
        }

        return (string) file_get_contents($path);
    }

    public function write(string $file, string $contents): bool
    {
        $path = $this->file_path($file);
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return false;
        }

        return file_put_contents($path, $contents, LOCK_EX) !== false;
    }

    public function exists(string $file): bool
    {
        return is_file($this->file_path($file));
    }

    public function delete(string $file): bool
    {
        $path = $this->file_path($file);

        return is_file($path) ? unlink($path) : false;
    }
}
