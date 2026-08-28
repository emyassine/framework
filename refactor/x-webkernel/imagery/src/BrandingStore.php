<?php

declare(strict_types=1);

namespace Webkernel\Imagery;

use Webkernel\Paths\Router;

/**
 * Internal branding registry and asset manager.
 *
 * Holds branding assets (logos, icons, etc.) in memory as base64-encoded
 * binary data, and registers HTTP routes via Router to serve
 * them with proper caching headers.
 *
 * Assets are also cached on disk (storage/framework/cache/branding/) as
 * decoded binary files so repeated requests skip base64_decode entirely.
 * Disk filenames are content-addressed (md5 of base64 payload) so the same
 * identity drives ?v=, ETag, and cache path — updating a brand asset never
 * reuses a stale key-named cache file.
 *
 * Response time is reported in the X-Branding-Time header as nanoseconds.
 */
final class BrandingStore
{
    /** @var array<string, array<string, array{format: string, data: string, hash: string}>> */
    private array $store = [];

    private readonly string $cacheDir;

    public function __construct()
    {
        $this->cacheDir = webkernel_cache_path('branding');
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Register a branding asset and return its data URI.
     */
    public function add(string $brand, string $key, string $format, string $base64): string
    {
        $hash = md5($base64);
        $this->store[$brand][$key] = ['format' => $format, 'data' => $base64, 'hash' => $hash];
        $this->write_to_disk_cache($hash, $format, $base64);
        return "data:image/{$format};base64,{$base64}";
    }

    /**
     * Return the data URI for a registered asset, or an empty string if not found.
     */
    public function data_uri(string $key): string
    {
        $asset = $this->find($key);
        return $asset !== null ? "data:image/{$asset['format']};base64,{$asset['data']}" : '';
    }

    /**
     * Return the Router URL for a registered asset, or an empty string if not found.
     *
     * ?v= is the first 8 hex chars of md5(base64 payload) — content, not key/path.
     * Multiple brands may ship the same basename (e.g. favicon.brand.php); only the
     * embedded data (and the brand-prefixed key) distinguish them.
     */
    public function url(string $key): string
    {
        $asset = $this->find($key);
        if ($asset === null) {
            return '';
        }
        $brand = explode('-', $key, 2)[0];
        return Router::url("branding/{$brand}/{$key}") . '?v=' . substr($asset['hash'], 0, 8);
    }

    /**
     * Register HTTP routes for every loaded branding asset.
     */
    public function register_routes(): void
    {
        foreach ($this->store as $brand => $assets) {
            foreach ($assets as $key => $asset) {
                $cacheDir = $this->cacheDir;
                Router::register_closure("branding/{$brand}/{$key}", static function () use ($asset, $cacheDir): never {
                    $start = hrtime(true); // nanoseconds

                    $etag = '"' . substr($asset['hash'], 0, 16) . '"';

                    if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
                        $elapsed = hrtime(true) - $start;
                        header('X-Branding-Time: ' . $elapsed . 'ns');
                        http_response_code(304);
                        exit(0);
                    }

                    // Content-addressed disk cache — same hash as ?v= / ETag
                    $cachePath = $cacheDir . DIRECTORY_SEPARATOR . $asset['hash'] . '.' . $asset['format'];
                    if (is_file($cachePath)) {
                        $binary = file_get_contents($cachePath);
                    } else {
                        $binary = base64_decode($asset['data']);
                        file_put_contents($cachePath, $binary, LOCK_EX);
                    }

                    $elapsed = hrtime(true) - $start;

                    header('Content-Type: image/' . $asset['format']);
                    header('Content-Length: ' . strlen($binary));
                    header('Cache-Control: public, max-age=31536000, immutable');
                    header('ETag: ' . $etag);
                    header('X-Branding-Time: ' . $elapsed . 'ns');
                    echo $binary;
                    exit(0);
                });
            }
        }
    }

    /**
     * Load all asset definition files from a directory.
     *
     * Each file must return an array with 'key', 'format', and 'data' keys.
     * Basename is irrelevant (many brands may use favicon.brand.php); the
     * returned 'key' (e.g. numerimondes-favicon) is the registry identity.
     */
    public function load_from_directory(string $directory, string $brand): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (glob("{$directory}/*.brand.php") as $file) {
            $asset = require $file;
            if (is_array($asset) && isset($asset['key'], $asset['format'], $asset['data'])) {
                $this->add($brand, $asset['key'], $asset['format'], $asset['data']);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * @return array{format: string, data: string, hash: string}|null
     */
    private function find(string $key): ?array
    {
        $brand = explode('-', $key, 2)[0];
        return $this->store[$brand][$key] ?? null;
    }

    /**
     * Write decoded binary to content-addressed disk cache if missing.
     */
    private function write_to_disk_cache(string $hash, string $format, string $base64): void
    {
        $cachePath = $this->cacheDir . DIRECTORY_SEPARATOR . $hash . '.' . $format;
        if (!is_file($cachePath)) {
            file_put_contents($cachePath, base64_decode($base64), LOCK_EX);
        }
    }
}
