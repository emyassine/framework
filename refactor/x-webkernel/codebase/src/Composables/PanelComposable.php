<?php declare(strict_types=1);

namespace Webkernel\Composables;

/**
 * Reads dumped panels. Request does not instantiate PanelProvider.
 */
final class PanelComposable implements ComposableContract
{
    /** @var list<array<string, mixed>>|null */
    private static ?array $panels = null;

    private ?string $id = null;

    public static function api_name(): string
    {
        return 'panel';
    }

    public static function flush(): void
    {
        self::$panels = null;
    }

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
     * @return list<array<string, mixed>>
     */
    private static function dumped(): array
    {
        if (self::$panels !== null) {
            return self::$panels;
        }
        $file = vendor_dir('composer/webkernel_panels.php');
        if (! is_file($file)) {
            return self::$panels = [];
        }
        $loaded = require $file;
        if (! is_array($loaded)) {
            return self::$panels = [];
        }
        $out = [];
        foreach ($loaded as $panel) {
            if (is_array($panel)) {
                $out[] = $panel;
            }
        }

        return self::$panels = $out;
    }
}
