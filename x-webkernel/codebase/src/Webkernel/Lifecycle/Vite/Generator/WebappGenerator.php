<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Generator;

use Composer\IO\IOInterface;
use JsonException;
use RuntimeException;
use Throwable;
use Webkernel\Lifecycle\Modules\ComposerModuleScanner;
use Webkernel\Lifecycle\Vite\Data\EntrySet;
use Webkernel\Lifecycle\Vite\Data\Module;
use Webkernel\Lifecycle\Vite\Data\Result;
use Webkernel\Lifecycle\Vite\Data\Snapshot;
use Webkernel\Lifecycle\Vite\Support\PackageJsonWorkspaces;
use Webkernel\Lifecycle\Vite\Support\PackageCssImports;
use Webkernel\Lifecycle\Vite\Support\TailwindGlobs;
use Webkernel\Paths\Composer as ProjectComposer;
use Webkernel\Paths\Path;
use Illuminate\Filesystem\Filesystem;

/**
 * Pure PHP discovery -> writes vite.webapp.ts at project root (static snapshot).
 *
 * Purpose: give Vite + Tailwind (@source globs) modules, packages, host app,
 * and vendor Blade/resources (via TailwindGlobs) — not every vendor PHP file.
 *
 * Module roots come from ComposerModuleScanner (installed.php), not hardcoded
 * modules/packages trees. Entry files are optional: only paths that exist
 * (or are declared in vite.module.*) are included.
 *
 * Host vite.config.ts / tailwind.config.ts only import that file.
 * No Node required for generation. Runtime install/uninstall: ViteWebapp::vite_npm_build().
 */
final class WebappGenerator
{
    /** CLI / console tag for JavaScript-Vite-npm asset actions only. */
    public const CLI_PREFIX = 'webkernel:js(actions)';

    /**
     * Tailwind content scan only (class tokens). Not CSS/SVG (noise + slow).
     * Server templates are scan-only — not Vite rollup inputs.
     */
    private const DEFAULT_EXTENSIONS = TailwindGlobs::SCAN_EXTENSIONS;

    /**
     * Files Vite can actually bundle as entries (no PHP/Blade/Twig).
     * Discovered on disk automatically -- vite.module.* is optional.
     *
     * @var list<string>
     */
    private const BUNDLE_EXTENSIONS = [
        'css', 'scss', 'sass', 'less',
        'js', 'mjs', 'cjs', 'ts', 'tsx', 'jsx',
        'vue', 'svelte',
        'html', 'htm',
    ];

    /**
     * Soft Laravel / Inertia convention hints (still optional).
     *
     * @var list<string>
     */
    private const OPTIONAL_ENTRY_CANDIDATES = [
        'resources/js/app.js',
        'resources/js/app.ts',
        'resources/js/app.jsx',
        'resources/js/app.tsx',
        'resources/js/app.vue',
        'resources/css/app.css',
        'resources/css/app.scss',
        'resources/css/app.sass',
    ];

    /** Directories never walked when auto-discovering bundle entries. */
    private const SKIP_DIR_NAMES = [
        'node_modules',
        'vendor',
        'third_party',
        '.git',
        '.svn',
        'dist',
        'build',
        'coverage',
        'storage',
        'bootstrap',
        'tests',
        'test',
        'docs',
        'documentation',
    ];

    /** @var list<string> */
    private const CONFIG_FILENAMES = [
        'vite.module.json',
        'vite.module.php',
        'vite.module.mjs',
        'vite.module.js',
        'vite.module.ts',
    ];

    /** Legacy snapshot locations dropped on every write. */
    private const LEGACY_SNAPSHOT_PATHS = [
        'vite.webapp.mjs',
        'storage/vite.webapp.ts',
        'storage/vite.webapp.mjs',
    ];

    public static function generate(string $project_root, ?IOInterface $io = null, bool $strict = false): Result
    {
        $project_root = rtrim($project_root, '/');

        try {
            self::cli_info('discovering packages for Vite/Tailwind (JS assets)...', $io);
            $webapp = self::discover($project_root, $strict, $io);
            $path = self::write_snapshot($project_root, $webapp);
        } catch (Throwable $exception) {
            self::cli_blank($io);
            self::cli_error('vite.webapp generation failed: ' . $exception->getMessage(), $io);
            self::cli_blank($io);

            return Result::failure($exception->getMessage());
        }

        self::report_success($webapp, $io);

        return Result::success(
            path: 'vite.webapp.ts',
            raw: self::build_success_payload($webapp),
        );
    }

    public static function snapshot_path(string $project_root): string
    {
        return rtrim($project_root, '/') . '/vite.webapp.ts';
    }

    public static function discover(string $project_root, bool $strict = false, ?IOInterface $io = null): Snapshot
    {
        $project_root = rtrim($project_root, '/');
        $vendor_dir = self::resolve_vendor_dir($project_root);
        $extensions = self::DEFAULT_EXTENSIONS;
        $extra_globs = [];

        $packages = ComposerModuleScanner::scan($project_root);
        $module_dirs = ComposerModuleScanner::derive_module_dirs($project_root, $packages, $vendor_dir);

        self::cli_info(sprintf(
            'found %d Webkernel package(s), %d module dir(s)',
            count($packages),
            count($module_dirs),
        ), $io);

        $entries = new EntrySet();
        $aliases = [];
        $tailwind_sources = new EntrySet();
        $modules = [];

        // Host: convention paths + any bundleable files under resources/ (if present).
        // vite.module.* is never required.
        self::collect_existing_entries($project_root, $project_root, $entries);

        $host_resources = $project_root . '/resources';
        if (is_dir($host_resources)) {
            self::collect_bundle_files($project_root, $host_resources, $entries);
        }

        foreach ($packages as $package) {
            $module = self::resolve_module($project_root, $package, $strict, $io, $entries, $aliases, $tailwind_sources);
            if ($module !== null) {
                $modules[] = $module;
            }
        }

        // Final belt: drop any path that vanished between collect and write.
        $entries = self::filter_existing_entries($project_root, $entries, $io);

        $tailwind_sources_list = $tailwind_sources->values();
        $package_roots = array_map(
            static fn (Module $module): string => $module->root,
            $modules,
        );

        $tailwind_globs = TailwindGlobs::build(
            project_root: $project_root,
            package_roots: $package_roots,
            module_dirs: $module_dirs,
            vendor_dir: $vendor_dir,
            extensions: $extensions,
            extra: $extra_globs,
            module_sources: $tailwind_sources_list,
        );

        $css_imports = PackageCssImports::discover($project_root, $package_roots);

        // Real paths of folded CSS — entries may use modules/… vs software-modules/… aliases.
        $css_import_reals = [];
        foreach ($css_imports as $rel) {
            $abs = $project_root.'/'.$rel;
            $real = is_file($abs) ? (realpath($abs) ?: $abs) : $abs;
            $css_import_reals[$real] = true;
        }

        $entry_list = array_values(array_filter(
            $entries->values(),
            static function (string $entry) use ($project_root, $css_import_reals): bool {
                if (! str_ends_with(strtolower($entry), '.css')) {
                    return true;
                }
                if ($entry === 'resources/css/app.css') {
                    return true;
                }
                $abs = $project_root.'/'.$entry;
                $real = is_file($abs) ? (realpath($abs) ?: $abs) : $abs;

                // Drop package CSS already @import'd into app.css (no second Vite CSS entry).
                return ! isset($css_import_reals[$real]);
            },
        ));

        return new Snapshot(
            generated_at: gmdate('c'),
            vendor_dir: $vendor_dir,
            module_dirs: $module_dirs,
            extensions: $extensions,
            extra_globs: $extra_globs,
            entries: $entry_list,
            aliases: $aliases,
            tailwind_sources: $tailwind_sources_list,
            tailwind_globs: $tailwind_globs,
            modules: $modules,
            css_imports: $css_imports,
            packages_json_globs: Snapshot::packages_json_globs_for($vendor_dir),
        );
    }

    /**
     * @param array<string, mixed> $package
     * @param array<string, string> $aliases
     */
    private static function resolve_module(
        string $project_root,
        array $package,
        bool $strict,
        ?IOInterface $io,
        EntrySet $entries,
        array &$aliases,
        EntrySet $tailwind_sources,
    ): ?Module {
        $module_root = (string) ($package['install_path'] ?? '');
        if ($module_root === '' || ! is_dir($module_root)) {
            return null;
        }

        $module_rel = Path::under($project_root, $module_root);
        if ($module_rel === null) {
            return null;
        }

        $config_file = self::find_config_file($module_root);
        $config = [];

        if ($config_file !== null) {
            try {
                $config = self::load_module_config($config_file);
            } catch (Throwable $exception) {
                $notice = 'could not load module config "' . $config_file . '": ' . $exception->getMessage();
                if ($strict) {
                    throw new RuntimeException('webkernel: ' . $notice, 0, $exception);
                }
                self::cli_warn($notice, $io);
                $config = [];
                $config_file = null;
            }
        }

        // Always auto-discover bundleable files (css/js/ts/vue/...). No config required.
        self::collect_existing_entries($project_root, $module_root, $entries);
        self::collect_bundle_files($project_root, $module_root, $entries);

        self::apply_declared_entries($project_root, $module_root, $module_rel, $config, $strict, $io, $entries);
        self::apply_tailwind_sources($module_rel, $config, $tailwind_sources);
        self::apply_aliases($module_rel, $config, $aliases);

        $config_rel = $config_file !== null
            ? Path::under($project_root, $config_file)
            : null;

        return new Module(
            root: $module_rel,
            config_file: $config_rel,
            has_config: $config_file !== null,
            name: (string) ($package['name'] ?? ''),
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function apply_declared_entries(
        string $project_root,
        string $module_root,
        string $module_rel,
        array $config,
        bool $strict,
        ?IOInterface $io,
        EntrySet $entries,
    ): void {
        if (! isset($config['entries']) || ! is_array($config['entries'])) {
            return;
        }

        foreach ($config['entries'] as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }

            $absolute_path = $module_root . '/' . ltrim($entry, '/');
            if (! is_file($absolute_path)) {
                $skipped = Path::posix($module_rel . '/' . ltrim($entry, '/'));
                if ($strict) {
                    throw new RuntimeException('webkernel: declared entry missing: ' . $absolute_path);
                }
                self::cli_warn('skip missing declared entry: ' . $skipped, $io);
                continue;
            }

            $rel = Path::under($project_root, $absolute_path);
            if ($rel !== null) {
                $entries->add($rel);
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function apply_tailwind_sources(string $module_rel, array $config, EntrySet $tailwind_sources): void
    {
        if (! isset($config['tailwind_sources']) || ! is_array($config['tailwind_sources'])) {
            return;
        }

        foreach ($config['tailwind_sources'] as $glob) {
            if (is_string($glob) && $glob !== '') {
                $tailwind_sources->add(Path::posix($module_rel . '/' . ltrim($glob, '/')));
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, string> $aliases
     */
    private static function apply_aliases(string $module_rel, array $config, array &$aliases): void
    {
        if (! isset($config['aliases']) || ! is_array($config['aliases'])) {
            return;
        }

        foreach ($config['aliases'] as $alias => $target) {
            if (! is_string($alias) || ! is_string($target)) {
                continue;
            }

            $relative_target = Path::posix($module_rel . '/' . ltrim($target, '/'));
            if (isset($aliases[$alias]) && $aliases[$alias] !== $relative_target) {
                continue;
            }

            $aliases[$alias] = $relative_target;
        }
    }

    private static function collect_existing_entries(string $project_root, string $base, EntrySet $entries): void
    {
        foreach (self::OPTIONAL_ENTRY_CANDIDATES as $file) {
            $absolute_path = $base . '/' . $file;
            if (is_file($absolute_path)) {
                $rel = Path::under($project_root, $absolute_path);
                if ($rel !== null) {
                    $entries->add($rel);
                }
            }
        }
    }

    /**
     * Walk a tree and take every on-disk file Vite can bundle (css/js/ts/vue/...).
     * Uses Illuminate\Filesystem (ADD-0006) — not a custom walk API.
     */
    private static function collect_bundle_files(string $project_root, string $base, EntrySet $entries): void
    {
        if (! is_dir($base)) {
            return;
        }

        $disk = new Filesystem();
        $skip = self::SKIP_DIR_NAMES;

        foreach ($disk->allFiles($base) as $file) {
            $pathname = $file->getPathname();
            $posix = Path::posix($pathname);

            foreach ($skip as $dir) {
                if (str_contains($posix, '/' . $dir . '/')) {
                    continue 2;
                }
            }

            $ext = strtolower($file->getExtension());
            if (! in_array($ext, self::BUNDLE_EXTENSIONS, true)) {
                continue;
            }

            $basename = $file->getBasename();
            if (str_starts_with($basename, 'vite.module.') || $basename === 'vite.webapp.ts') {
                continue;
            }

            $rel = Path::under($project_root, $pathname);
            if ($rel !== null) {
                $entries->add($rel);
            }
        }
    }

    private static function filter_existing_entries(string $project_root, EntrySet $entries, ?IOInterface $io = null): EntrySet
    {
        $kept = new EntrySet();

        foreach ($entries->values() as $relative_path) {
            $absolute_path = $project_root . '/' . $relative_path;
            if (is_file($absolute_path)) {
                $kept->add($relative_path);
            } else {
                self::cli_warn('drop non-existent entry from snapshot: ' . $relative_path, $io);
            }
        }

        return $kept;
    }

    private static function report_success(Snapshot $webapp, ?IOInterface $io): void
    {
        $entry_count = count($webapp->entries);
        $module_count = count($webapp->modules);
        $glob_count = count($webapp->tailwind_globs);
        $packages_json_globs = $webapp->packages_json_globs !== []
            ? $webapp->packages_json_globs
            : Snapshot::packages_json_globs_for($webapp->vendor_dir);
        $css_import_count = count($webapp->css_imports);

        self::cli_blank($io);
        self::cli_info(sprintf(
            'wrote JS snapshot vite.webapp.ts -- vendor-dir=%s, %d module%s, %d tailwind glob%s, %d css import%s',
            $webapp->vendor_dir,
            $module_count,
            $module_count === 1 ? '' : 's',
            $glob_count,
            $glob_count === 1 ? '' : 's',
            $css_import_count,
            $css_import_count === 1 ? '' : 's',
        ), $io);

        self::cli_info(
            'packages_json_globs (npm workspaces): '.implode(', ', $packages_json_globs),
            $io,
        );

        // Host Vite always resolves LARAVEL_ENTRIES (resources/css/app.css, resources/js/app.js)
        // at runtime via resolve_entries() — even when the snapshot lists 0 discovered entries.
        if ($entry_count === 0) {
            self::cli_info(
                'discovered entries: 0 (host app.css/js always added by resolve_entries; package CSS via css_imports)',
                $io,
            );
        } else {
            foreach ($webapp->entries as $entry) {
                self::cli_info('  entry: '.$entry, $io);
            }
        }

        self::cli_blank($io);
    }

    private static function build_success_payload(Snapshot $webapp): string
    {
        return json_encode([
            'ok' => true,
            'path' => 'vite.webapp.ts',
            'generated_at' => $webapp->generated_at,
            'vendor_dir' => $webapp->vendor_dir,
            'packages_json_globs' => $webapp->packages_json_globs !== []
                ? $webapp->packages_json_globs
                : Snapshot::packages_json_globs_for($webapp->vendor_dir),
            'module_count' => count($webapp->modules),
            'entries' => $webapp->entries,
            'css_imports' => $webapp->css_imports,
            'alias_count' => count($webapp->aliases),
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . "\n";
    }

    private static function cli_blank(?IOInterface $io = null): void
    {
        if ($io !== null) {
            $io->write('');
            return;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDOUT, PHP_EOL);
        }
    }

    private static function cli_info(string $message, ?IOInterface $io = null): void
    {
        $prefix = self::CLI_PREFIX;

        if ($io !== null) {
            $io->write('<info>' . $prefix . '</info> ' . $message);
            return;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDOUT, $prefix . ' ' . $message . PHP_EOL);
        }
    }

    private static function cli_warn(string $message, ?IOInterface $io = null): void
    {
        $prefix = self::CLI_PREFIX;

        if ($io !== null) {
            $io->writeError('<warning>' . $prefix . '</warning> ' . $message);
            return;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDERR, $prefix . ' ' . $message . PHP_EOL);
        }
    }

    private static function cli_error(string $message, ?IOInterface $io = null): void
    {
        $prefix = self::CLI_PREFIX;

        if ($io !== null) {
            $io->writeError('<error>' . $prefix . ' ' . $message . '</error>');
            return;
        }

        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            fwrite(STDERR, $prefix . ' ' . $message . PHP_EOL);
        }
    }

    private static function write_snapshot(string $project_root, Snapshot $webapp): string
    {
        $path = self::snapshot_path($project_root);
        $dir = dirname($path);

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('webkernel: cannot create ' . $dir);
        }

        $payload = $webapp->to_json_payload();
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $body = Template::render(
            json_payload: $json,
            generated_at: $webapp->generated_at,
            cli_prefix: self::CLI_PREFIX,
        );

        if (file_put_contents($path, $body) === false) {
            throw new RuntimeException('webkernel: failed to write ' . $path);
        }

        // Keep host package.json workspaces globs in lockstep with composer vendor-dir
        // (same source as packages_json_globs in the snapshot — not software/* path repos).
        PackageJsonWorkspaces::sync(
            $project_root,
            Snapshot::packages_json_globs_for($webapp->vendor_dir),
        );

        foreach (self::LEGACY_SNAPSHOT_PATHS as $legacy_relative_path) {
            $legacy_path = $project_root . '/' . $legacy_relative_path;
            if (is_file($legacy_path)) {
                @unlink($legacy_path);
            }
        }

        return $path;
    }

    private static function resolve_vendor_dir(string $project_root): string
    {
        try {
            $rel = Path::under($project_root, ProjectComposer::vendor_dir());
            if ($rel !== null) {
                return $rel;
            }
        } catch (Throwable) {
            // fall through
        }

        $composer_path = $project_root . '/composer.json';
        if (! is_file($composer_path)) {
            return 'vendor';
        }

        try {
            /** @var array{config?: array{"vendor-dir"?: string}} $composer */
            $composer = json_decode((string) file_get_contents($composer_path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return 'vendor';
        }

        return $composer['config']['vendor-dir'] ?? 'vendor';
    }

    private static function find_config_file(string $module_root): ?string
    {
        foreach (self::CONFIG_FILENAMES as $filename) {
            $candidate = $module_root . '/' . $filename;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array{entries?: list<string>, tailwind_sources?: list<string>, aliases?: array<string, string>}
     */
    private static function load_module_config(string $config_file): array
    {
        $basename = basename($config_file);

        if ($basename === 'vite.module.json') {
            /** @var mixed $data */
            $data = json_decode((string) file_get_contents($config_file), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($data)) {
                throw new RuntimeException('expected object in ' . $config_file);
            }

            return $data;
        }

        if ($basename === 'vite.module.php') {
            /** @var mixed $data */
            $data = require $config_file;
            if (! is_array($data)) {
                throw new RuntimeException('expected array return from ' . $config_file);
            }

            return $data;
        }

        // vite.module.{mjs,js,ts} -- literals-only subset (no runtime JS).
        return self::parse_literal_module_config((string) file_get_contents($config_file), $config_file);
    }

    /**
     * Parse a small export-default object with string arrays / string maps only.
     *
     * @return array{entries?: list<string>, tailwind_sources?: list<string>, aliases?: array<string, string>}
     */
    private static function parse_literal_module_config(string $source, string $path): array
    {
        $source = preg_replace('/^import\s+type\s+.+;$/m', '', $source) ?? $source;
        $source = preg_replace('/^import\s+.+;$/m', '', $source) ?? $source;
        $source = preg_replace('#//.*$#m', '', $source) ?? $source;
        $source = preg_replace('#/\*.*?\*/#s', '', $source) ?? $source;

        if (! preg_match('/export\s+default\s+(\{.*\})\s*(?:satisfies\s+\w+)?\s*;?/s', $source, $matches)) {
            throw new RuntimeException('no export default {...} object in ' . $path);
        }

        $object = $matches[1];
        $jsonish = preg_replace('/([{\s,])([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $object) ?? $object;
        $jsonish = str_replace("'", '"', $jsonish);
        $jsonish = preg_replace('/,\s*([}\]])/', '$1', $jsonish) ?? $jsonish;

        try {
            /** @var mixed $data */
            $data = json_decode($jsonish, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'literal module config is not JSON-compatible in ' . $path . ': ' . $exception->getMessage(),
                0,
                $exception,
            );
        }

        if (! is_array($data)) {
            throw new RuntimeException('expected object in ' . $path);
        }

        return $data;
    }

}
