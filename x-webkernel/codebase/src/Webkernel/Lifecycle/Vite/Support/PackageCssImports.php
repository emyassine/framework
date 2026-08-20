<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Support;

use Webkernel\Lifecycle\Vite\Data\EntrySet;
use Webkernel\Paths\Path;

/**
 * Discover package stylesheets to fold into host app.css (single Vite CSS bundle).
 *
 * Only packages discovered by Composer (path roots) — not every folder under software/.
 * Prefer resources/dist/css over resources/css.
 */
final class PackageCssImports
{
    /**
     * @param  list<string>  $package_roots  relative to project root
     * @return list<string> project-relative css paths
     */
    public static function discover(string $project_root, array $package_roots): array
    {
        $project_root = rtrim($project_root, '/');
        $set = new EntrySet();
        $seen_real = [];

        foreach ($package_roots as $root) {
            $root = Path::posix(trim($root, '/'));
            if ($root === '') {
                continue;
            }
            $abs = $project_root.'/'.$root;
            if (! is_dir($abs)) {
                continue;
            }
            $real = realpath($abs) ?: $abs;
            if (isset($seen_real[$real])) {
                continue;
            }
            $seen_real[$real] = true;

            foreach (self::candidate_css($real) as $file) {
                $rel = Path::under($project_root, $file);
                if ($rel === null || str_ends_with($rel, '/resources/css/app.css')) {
                    continue;
                }
                $set->add($rel);
            }
        }

        $out = $set->values();
        sort($out);

        return $out;
    }

    /**
     * @return list<string> absolute file paths
     */
    private static function candidate_css(string $package_abs): array
    {
        $hits = [];

        foreach ([
            $package_abs.'/resources/dist/css',
            $package_abs.'/resources/css',
        ] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $batch = [];
            foreach (scandir($dir) ?: [] as $name) {
                if ($name === '.' || $name === '..' || ! str_ends_with(strtolower($name), '.css')) {
                    continue;
                }
                // Skip test-only assets.
                if (str_contains($name, '-test.') || str_ends_with($name, '.test.css')) {
                    continue;
                }
                $batch[] = $dir.'/'.$name;
            }
            if ($batch !== []) {
                return $batch;
            }
        }

        return $hits;
    }
}
