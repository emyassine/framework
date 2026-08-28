<?php declare(strict_types=1);

namespace Webkernel\Imagery;

/**
 * Brand logos/favicons from res/brands/{brand}/*.brand.php (base64).
 * Served at /__webkernel-app/branding/{brand}/{key} from a decoded disk cache.
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
        $root = Icon::package_root().'/res/brands';
        $files = is_dir($root) ? glob($root.'/*/*.brand.php') : false;
        if (! is_array($files)) {
            return $self;
        }
        foreach ($files as $file) {
            if (! is_string($file) || $file === '') {
                continue;
            }
            $asset = require $file;
            if (! is_array($asset) || ! isset($asset['key'], $asset['format'], $asset['data'])) {
                continue;
            }
            $key = (string) $asset['key'];
            $format = (string) $asset['format'];
            $data = (string) $asset['data'];
            $hash = md5($data);
            $self->assets[$key] = [
                'brand' => basename(dirname($file)),
                'format' => $format,
                'hash' => $hash,
                'source' => $file,
            ];
            $self->warm($hash, $format, $data);
        }

        return $self;
    }

    public function url(string $key): string
    {
        $asset = $this->assets[$key] ?? null;
        if ($asset === null) {
            return '';
        }

        return self::PREFIX.'/branding/'.$asset['brand'].'/'.$key.'?v='.substr($asset['hash'], 0, 8);
    }

    public function show(string $brand, string $key): string
    {
        return self::get()->respond($brand, $key);
    }

    private function respond(string $brand, string $key): string
    {
        $asset = $this->assets[$key] ?? null;
        if ($asset === null || $asset['brand'] !== $brand) {
            http_response_code(404);

            return '';
        }
        $etag = '"'.substr($asset['hash'], 0, 16).'"';
        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);

            return '';
        }
        $binary = $this->binary($asset['hash'], $asset['format'], $asset['source']);
        header('Content-Type: '.$this->mime($asset['format']));
        header('Content-Length: '.(string) strlen($binary));
        header('Cache-Control: public, max-age=31536000, immutable');
        header('ETag: '.$etag);

        return $binary;
    }

    private function binary(string $hash, string $format, string $source): string
    {
        $cache = $this->cache_file($hash, $format);
        if (is_file($cache)) {
            $binary = file_get_contents($cache);

            return is_string($binary) ? $binary : '';
        }
        $asset = require $source;
        $data = is_array($asset) ? (string) ($asset['data'] ?? '') : '';
        $binary = base64_decode($data, true);
        if (! is_string($binary) || $binary === '') {
            return '';
        }
        $this->warm($hash, $format, $data);

        return $binary;
    }

    private function warm(string $hash, string $format, string $base64): void
    {
        $cache = $this->cache_file($hash, $format);
        if (is_file($cache)) {
            return;
        }
        $dir = dirname($cache);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return;
        }
        $binary = base64_decode($base64, true);
        if (! is_string($binary) || $binary === '') {
            return;
        }
        $tmp = $cache.'.'.bin2hex(random_bytes(4)).'.tmp';
        if (file_put_contents($tmp, $binary, LOCK_EX) === false) {
            return;
        }
        if (! rename($tmp, $cache)) {
            @unlink($tmp);
        }
    }

    private function cache_file(string $hash, string $format): string
    {
        return webapp_path('platform/storage/framework/cache/branding/'.$hash.'.'.$format);
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
