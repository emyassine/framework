<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Modules;

use Webkernel\StdQuery\Abstract\QueryCatalog;

/**
 * Query catalog over installed Webkernel packages (Composer-backed).
 *
 * @example QueryModules::make()->where('active')->is(true)->get()
 * @example QueryModules::make()->where('lang_path')->is_not_null()->select(['lang_path'])->get()
 */
final class QueryModules extends QueryCatalog
{
    /** @return list<array<string, mixed>> */
    protected static function load_items(): array
    {
        $cache_path = ModuleCache::path();
        $stamp = ModuleCache::installed_stamp();

        if (is_file($cache_path)) {
            $raw = file_get_contents($cache_path);
            if ($raw !== false) {
                /** @var array{installed_stamp?: int, entries?: list<array<string, mixed>>}|null $payload */
                $payload = json_decode($raw, true);
                if (
                    is_array($payload)
                    && ($payload['installed_stamp'] ?? 0) === $stamp
                    && is_array($payload['entries'] ?? null)
                ) {
                    return array_values($payload['entries']);
                }
            }
        }

        $entries = ComposerModuleScanner::scan();

        file_put_contents($cache_path, (string) json_encode([
            'generated_by' => 'Webkernel\Lifecycle\Modules\QueryModules',
            'generated_at' => gmdate('c'),
            'installed_stamp' => $stamp,
            'entries' => $entries,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

        return $entries;
    }

    public static function refresh(): void
    {
        $path = ModuleCache::path();
        if (is_file($path)) {
            unlink($path);
        }
    }
}
