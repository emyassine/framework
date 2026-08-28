<?php declare(strict_types=1);

namespace Webkernel\Imagery;

/**
 * Brand logos/favicons. Index is dump-autoload (webkernel_branding.php).
 * Request url() does not load base64. payload() loads one source file on cache miss.
 */
final class Branding
{
    public const PREFIX = '/__webkernel-app';

    private static ?self $instance = null;

    /**
     * @var array<string, array{brand: string, format: string, hash: string, source: string}>
     */
    private array $assets = [];

    public static function get(): self
    {
        return self::$instance ??= self::boot();
    }

    public static function flush(): void
    {
        self::$instance = null;
    }

    public static function boot(): self
    {
        $self = new self();
        $file = \function_exists('vendor_dir') ? \vendor_dir('composer/webkernel_branding.php') : '';
        if ($file !== '' && \is_file($file)) {
            $loaded = \function_exists('webkernel_include') ? \webkernel_include($file) : require $file;
            if (\is_array($loaded)) {
                foreach ($loaded as $key => $row) {
                    if (! \is_string($key) || ! \is_array($row)) {
                        continue;
                    }
                    $self->assets[$key] = [
                        'brand' => (string) ($row['brand'] ?? ''),
                        'format' => (string) ($row['format'] ?? 'png'),
                        'hash' => (string) ($row['hash'] ?? ''),
                        'source' => (string) ($row['source'] ?? ''),
                    ];
                }
            }
        }

        return $self;
    }

    public function url(string $key): string
    {
        $asset = $this->assets[$key] ?? null;
        if ($asset === null || $asset['hash'] === '') {
            return '';
        }

        return self::PREFIX.'/branding/'.$asset['brand'].'/'.$key.'?v='.\substr($asset['hash'], 0, 8);
    }

    public function payload(string $brand, string $key): string
    {
        $asset = $this->assets[$key] ?? null;
        if ($asset === null || $asset['brand'] !== $brand) {
            \http_response_code(404);

            return '';
        }
        $etag = '"'.\substr($asset['hash'], 0, 16).'"';
        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            \http_response_code(304);

            return '';
        }
        $binary = $this->binary($asset);
        \header('Content-Type: '.$this->mime($asset['format']));
        \header('Content-Length: '.(string) \strlen($binary));
        \header('Cache-Control: public, max-age=31536000, immutable');
        \header('ETag: '.$etag);

        return $binary;
    }

    /**
     * @param array{brand: string, format: string, hash: string, source: string} $asset
     */
    private function binary(array $asset): string
    {
        $cache = $this->cache_file($asset['hash'], $asset['format']);
        if (\is_file($cache)) {
            $binary = \file_get_contents($cache);

            return \is_string($binary) ? $binary : '';
        }
        $source = $asset['source'];
        if ($source !== '' && $source[0] !== '/') {
            $source = \webapp_path($source);
        }
        if (! \is_file($source)) {
            return '';
        }
        $loaded = require $source;
        $data = \is_array($loaded) ? (string) ($loaded['data'] ?? '') : '';
        $binary = \base64_decode($data, true);
        if (! \is_string($binary) || $binary === '') {
            return '';
        }
        $this->warm($asset['hash'], $asset['format'], $binary);

        return $binary;
    }

    private function warm(string $hash, string $format, string $binary): void
    {
        $cache = $this->cache_file($hash, $format);
        if (\is_file($cache)) {
            return;
        }
        $dir = \dirname($cache);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            return;
        }
        $tmp = $cache.'.'.\bin2hex(\random_bytes(4)).'.tmp';
        if (\file_put_contents($tmp, $binary, \LOCK_EX) === false) {
            return;
        }
        if (! \rename($tmp, $cache)) {
            @\unlink($tmp);
        }
    }

    private function cache_file(string $hash, string $format): string
    {
        return \webapp_path('platform/storage/framework/cache/branding/'.$hash.'.'.$format);
    }

    private function mime(string $format): string
    {
        return match ($format) {
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/'.$format,
        };
    }
}
