<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Support;

use Webkernel\Lifecycle\Vite\Data\EntrySet;
use Webkernel\Paths\Path;

/**
 * Tight Tailwind v4 @source globs for a Webkernel host.
 *
 * Goals:
 * - Cover path-repo packages (software/, software-modules/) and real module trees
 * - Avoid duplicate scans (third_party/webkernel/* is usually a symlink into software/)
 * - Avoid scanning all of Filament / entire vendor (slow; theme is prebuilt)
 * - Prefer views/resources/content over full PHP source trees
 */
final class TailwindGlobs
{
    /**
     * Class-bearing text only. CSS/SVG are not class sources (and scanning them is waste).
     */
    public const SCAN_EXTENSIONS = '.{blade.php,php,js,mjs,cjs,ts,tsx,jsx,vue,md,mdx}';

    /**
     * @param  list<string>  $package_roots  relative install paths (may be third_party/… or software/…)
     * @param  list<string>  $module_dirs    path-repo bases (software, software-modules, …)
     * @param  list<string>  $extra
     * @param  list<string>  $module_sources per-module vite.module tailwind_sources
     * @return list<string>
     */
    public static function build(
        string $project_root,
        array $package_roots,
        array $module_dirs,
        string $vendor_dir,
        string $extensions = self::SCAN_EXTENSIONS,
        array $extra = [],
        array $module_sources = [],
    ): array {
        $globs = new EntrySet();
        $vendor_dir = Path::posix(trim($vendor_dir, '/'));
        $ext = $extensions !== '' ? $extensions : self::SCAN_EXTENSIONS;

        // Host app (classic Laravel layout — often empty in pure monorepos).
        self::add_if_dir($project_root, $globs, 'resources', $ext);
        self::add_if_dir($project_root, $globs, 'app', $ext);
        if (is_dir($project_root.'/storage/framework/views')) {
            $globs->add('storage/framework/views/*.php');
        }

        // Path-repo trees — real sources of truth (prefer over vendor symlinks).
        foreach (['software', 'software-modules', 'software-dev', 'software-experimental'] as $dir) {
            if (! is_dir($project_root.'/'.$dir)) {
                continue;
            }
            // Views / Livewire / Blade
            $globs->add($dir.'/**/resources/**/*'.$ext);
            $globs->add($dir.'/**/views/**/*'.$ext);
            // MD site content (class names in ```blade +parse islands)
            $globs->add($dir.'/**/content/**/*'.$ext);
            // Filament resources under src/
            $globs->add($dir.'/**/src/**/*.{blade.php,php}');
        }

        // Composer path packages often resolve to third_party/webkernel/* → software/*.
        // Prefer the realpath under software/ when it exists; otherwise scan install root.
        foreach ($package_roots as $root) {
            $root = Path::posix(trim($root, '/'));
            if ($root === '' || str_starts_with($root, '..')) {
                continue;
            }
            $abs = $project_root.'/'.$root;
            if (! is_dir($abs)) {
                continue;
            }
            $real = realpath($abs) ?: $abs;
            $under = Path::under($project_root, $real) ?? $root;
            // Skip if already covered by software/** targeted globs above.
            if (str_starts_with($under, 'software/')
                || str_starts_with($under, 'software-modules/')
                || str_starts_with($under, 'software-dev/')
                || str_starts_with($under, 'software-experimental/')) {
                continue;
            }
            $globs->add($under.'/resources/**/*'.$ext);
            $globs->add($under.'/src/**/*.{blade.php,php}');
        }

        // modules/ (symlink farm → software-modules) — gitignored, needs explicit @source.
        if (is_dir($project_root.'/modules')) {
            $globs->add('modules/**/resources/**/*'.$ext);
            $globs->add('modules/**/content/**/*'.$ext);
            $globs->add('modules/**/views/**/*'.$ext);
        }

        // Filament: blades only under resources (not whole package PHP/tests).
        // Prebuilt theme CSS still ships separately — we only need class tokens
        // for any host-overridden blades, not a full vendor megascan.
        if ($vendor_dir !== '' && is_dir($project_root.'/'.$vendor_dir.'/filament')) {
            $globs->add($vendor_dir.'/filament/**/resources/**/*.blade.php');
        }

        foreach ([...$extra, ...$module_sources] as $glob) {
            if (is_string($glob) && $glob !== '') {
                $globs->add(Path::posix($glob));
            }
        }

        // Drop dead bases + de-dupe overlapping identical strings.
        $kept = [];
        foreach ($globs->values() as $glob) {
            $base = Path::glob_base($glob);
            if ($base === '' || is_dir($project_root.'/'.$base) || is_file($project_root.'/'.$base)) {
                $kept[] = $glob;
            }
        }

        $kept = array_values(array_unique($kept));
        sort($kept);

        return $kept;
    }

    private static function add_if_dir(string $project_root, EntrySet $globs, string $dir, string $ext): void
    {
        if (is_dir($project_root.'/'.$dir)) {
            $globs->add($dir.'/**/*'.$ext);
        }
    }
}
