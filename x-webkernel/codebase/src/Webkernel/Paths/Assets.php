<?php declare(strict_types=1);

namespace Webkernel\Paths;

/**
 * Package front-end asset catalog (js / css / image / font).
 *
 * {@see webkernel_load_asset()} registers. {@see webkernel_asset()} returns the URL.
 * Optional $to is a path under public/ (symlink, no duplicate bytes).
 * Without $to, Router serves from /__webkernel-app/asset/{as}.
 */
final class Assets
{
    /** @var array<string, Asset> */
    private static array $catalog = [];

    /**
     * Register an asset and return its public URL.
     *
     * @param  string       $from      Absolute file, "package:subpath", or http(s) URL
     * @param  string       $as        Catalog key, e.g. "codebase/gsap.js"
     * @param  string|null  $to        Optional path under public/, e.g. "js/webkernel/gsap.min.js"
     * @param  bool         $autoload  Inject js/css into the document (images never auto-injected)
     */
    public static function load(string $from, string $as, ?string $to = null, bool $autoload = true): string
    {
        $as = self::assert_key($as, 'as');
        $source = self::resolve_from($from);

        $url = $to !== null && $to !== ''
            ? self::publish($source, $to)
            : Router::url('asset/'.$as);

        $asset = new Asset($as, $source, $url, $to, $autoload);
        self::$catalog[$as] = $asset;

        Router::register_closure('asset/'.$as, static function () use ($as): never {
            self::serve($as);
        });

        return $url;
    }

    public static function url(string $as): string
    {
        $as = self::assert_key($as, 'as');
        $asset = self::$catalog[$as] ?? null;
        if ($asset === null) {
            throw new \RuntimeException('Asset ['.$as.'] is not registered.');
        }

        return $asset->url;
    }

    public static function get(string $as): Asset
    {
        $as = self::assert_key($as, 'as');
        $asset = self::$catalog[$as] ?? null;
        if ($asset === null) {
            throw new \RuntimeException('Asset ['.$as.'] is not registered.');
        }

        return $asset;
    }

    public static function styles_html(): string
    {
        $html = '';
        foreach (self::$catalog as $asset) {
            if (! $asset->autoload || ! self::is_css($asset->source)) {
                continue;
            }
            $html .= '<link rel="stylesheet" href="'.self::esc($asset->url).'">'."\n";
        }

        return $html;
    }

    public static function scripts_html(): string
    {
        $html = '';
        foreach (self::$catalog as $asset) {
            if (! $asset->autoload || ! self::is_js($asset->source)) {
                continue;
            }
            $html .= '<script src="'.self::esc($asset->url).'"></script>'."\n";
        }

        return $html;
    }

    /** @return never */
    public static function serve(string $as): never
    {
        $asset = self::get($as);
        $path = $asset->source;
        if (! is_file($path)) {
            http_response_code(404);
            exit(0);
        }

        $mtime = (string) filemtime($path);
        $size = (string) filesize($path);
        $etag = '"'.$mtime.'-'.$size.'"';

        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            http_response_code(304);
            exit(0);
        }

        header('Content-Type: '.self::mime($path));
        header('Content-Length: '.$size);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('ETag: '.$etag);
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit(0);
    }

    private static function resolve_from(string $from): string
    {
        if (str_starts_with($from, 'https://') || str_starts_with($from, 'http://')) {
            return self::fetch($from);
        }

        if (is_file($from)) {
            $real = realpath($from);

            return $real !== false ? $real : $from;
        }

        if (str_contains($from, ':')) {
            [$package, $subpath] = explode(':', $from, 2);
            $resolved = webkernel_package($package, $subpath);
            $real = realpath($resolved);

            return $real !== false ? $real : $resolved;
        }

        throw new \RuntimeException('Asset source does not exist: '.$from);
    }

    private static function fetch(string $url): string
    {
        $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION);
        $ext = is_string($ext) && $ext !== '' ? '.'.$ext : '';
        $cache = webkernel_platform_dir('assets/'.hash('sha256', $url).$ext);
        $dir = dirname($cache);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        if (is_file($cache)) {
            return $cache;
        }

        $body = file_get_contents($url);
        if ($body === false) {
            throw new \RuntimeException('Unable to fetch asset: '.$url);
        }
        file_put_contents($cache, $body, LOCK_EX);

        return $cache;
    }

    private static function publish(string $source, string $to): string
    {
        $to = self::assert_key($to, 'to');
        $dest = webapp_path('public/'.$to);
        $dir = dirname($dest);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $resolved = is_link($dest) || is_file($dest) ? realpath($dest) : false;
        if ($resolved === $source) {
            $link = is_link($dest) ? (string) readlink($dest) : '';
            if ($link !== '' && str_starts_with($link, '/')) {
                unlink($dest);
                self::link_or_copy($source, $dest);
            }

            return '/'.$to;
        }
        if (is_link($dest) || is_file($dest)) {
            unlink($dest);
        }
        self::link_or_copy($source, $dest);

        return '/'.$to;
    }

    private static function link_or_copy(string $source, string $dest): void
    {
        $relative = Path::relative_link(dirname($dest), $source);
        if (@symlink($relative, $dest)) {
            return;
        }
        if (! copy($source, $dest)) {
            throw new \RuntimeException('Unable to publish asset to '.$dest);
        }
    }

    private static function is_js(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $ext === 'js' || $ext === 'mjs';
    }

    private static function is_css(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'css';
    }

    private static function esc(string $url): string
    {
        return htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function assert_key(string $value, string $field): string
    {
        $value = trim(str_replace('\\', '/', $value), '/');
        if ($value === '' || str_contains($value, '..') || preg_match('#^[A-Za-z0-9._/-]+$#', $value) !== 1) {
            throw new \RuntimeException('Invalid asset '.$field.': '.$value);
        }

        return $value;
    }

    private static function mime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'js', 'mjs' => 'text/javascript; charset=utf-8',
            'css' => 'text/css; charset=utf-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'json' => 'application/json',
            default => 'application/octet-stream',
        };
    }
}

final readonly class Asset
{
    public function __construct(
        public string $as,
        public string $source,
        public string $url,
        public ?string $public_rel,
        public bool $autoload = true,
    ) {
    }
}
