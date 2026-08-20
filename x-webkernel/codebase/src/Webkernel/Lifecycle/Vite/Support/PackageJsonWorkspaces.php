<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Support;

use JsonException;

/**
 * Sync host package.json "workspaces" to Composer install-layout globs only.
 *
 * Globs come from vite.webapp snapshot packages_json_globs:
 *   - {config.vendor-dir}/asterisk/asterisk  (Composer install path)
 *   - modules/asterisk/asterisk
 *
 * Never path-repo source trees (software/…). Only rewrites the workspaces key.
 */
final class PackageJsonWorkspaces
{
    /**
     * @param  list<string>  $globs
     */
    public static function sync(string $project_root, array $globs): void
    {
        $path = rtrim($project_root, '/').'/package.json';
        if (! is_file($path)) {
            return;
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return;
        }

        if (! is_array($decoded)) {
            return;
        }

        $globs = array_values(array_filter(
            $globs,
            static fn ($g): bool => is_string($g) && $g !== '',
        ));

        if (($decoded['workspaces'] ?? null) === $globs) {
            return;
        }

        $decoded['private'] = true;
        $decoded['workspaces'] = $globs;

        $json = json_encode(
            $decoded,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        )."\n";

        file_put_contents($path, $json);
    }
}
