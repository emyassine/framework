<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Composables;

/**
 * Reads dumped panels. Request does not instantiate PanelProvider.
 */
final class PanelComposable implements ComposableContract
{
    /** @var list<array<string, mixed>>|null */
    private static ?array $panels = null;

    private ?string $id = null;

    /**
     * @return string
     */
    public static function api_name(): string
    {
        return 'panel';
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$panels = null;
    }

    /**
     * @param $id string|null
     * @return self
     */
    public function __invoke(?string $id = null): self
    {
        if ($id === null) {
            return $this;
        }
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return self::dumped();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function current(): ?array
    {
        $id = $this->id;
        foreach (self::dumped() as $panel) {
            if ($id === null && ($panel['default'] ?? false) === true) {
                return $panel;
            }
            if ($id !== null && ($panel['id'] ?? '') === $id) {
                return $panel;
            }
        }

        return self::dumped()[0] ?? null;
    }

    /**
     * Longest panel path prefix of the current request (or $uri).
     *
     * @param $uri string|null
     * @return array<string, mixed>|null
     */
    public function matching_path(?string $uri = null): ?array
    {
        $path = $this->request_path($uri);
        $best = null;
        $best_len = -1;
        foreach (self::dumped() as $panel) {
            $base = '/'.\trim((string) ($panel['path'] ?? $panel['id'] ?? ''), '/');
            if ($base === '/') {
                continue;
            }
            if ($path === $base || \str_starts_with($path, $base.'/')) {
                $len = \strlen($base);
                if ($len > $best_len) {
                    $best = $panel;
                    $best_len = $len;
                }
            }
        }

        return $best ?? $this->current();
    }

    /**
     * @param $uri string|null
     * @return string
     */
    public function request_path(?string $uri = null): string
    {
        $raw = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path = \parse_url($raw, PHP_URL_PATH);

        return \is_string($path) && $path !== '' ? $path : '/';
    }

    /**
     * @param $href string
     * @param $path string|null
     * @return bool
     */
    public function href_is_active(string $href, ?string $path = null): bool
    {
        $path ??= $this->request_path();
        $href = '/'.\trim($href, '/');
        if ($href === '/') {
            return $path === '/';
        }

        return $path === $href || \str_starts_with($path, $href.'/');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function dumped(): array
    {
        if (self::$panels !== null) {
            return self::$panels;
        }
        $file = vendor_dir('composer/webkernel_panels.php');
        if (! \is_file($file)) {
            return self::$panels = [];
        }
        $loaded = require $file;
        if (! \is_array($loaded)) {
            return self::$panels = [];
        }
        $out = [];
        foreach ($loaded as $panel) {
            if (\is_array($panel)) {
                $out[] = $panel;
            }
        }

        return self::$panels = $out;
    }
}
