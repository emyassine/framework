<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Modules;

use Webkernel\Lifecycle\Installer\LCPackageType;
use Webkernel\Paths\Composer;

/**
 * Discovers installed Webkernel packages via Composer installed.php.
 *
 * Install paths come from Composer (vendor + path repositories + custom
 * installer locations). No hardcoded modules/ or packages/ trees.
 */
final class ComposerModuleScanner
{
    /**
     * Scan installed Webkernel packages.
     *
     * @return list<array{
     *   id: string,
     *   name: string,
     *   slug: string,
     *   version: string,
     *   type: string,
     *   _type: string,
     *   install_path: string,
     *   lang_path: ?string,
     *   prefix: string,
     *   package_repo: string,
     *   config_scope: string,
     *   active: bool
     * }>
     */
    public static function scan(?string $project_root = null): array
    {
        $project_root = $project_root !== null
            ? rtrim($project_root, '/\\')
            : Composer::root();

        $installed_file = self::installed_php_path($project_root);
        if (! is_file($installed_file)) {
            return [];
        }

        /** @var array{versions?: array<string, array<string, mixed>>} $installed */
        $installed = require $installed_file;
        $versions = $installed['versions'] ?? [];
        $installed_dir = dirname($installed_file);

        $entries = [];
        foreach ($versions as $name => $meta) {
            if ($name === 'webkernel/webkernel' || ! is_string($name)) {
                continue;
            }
            if (! is_array($meta)) {
                continue;
            }

            $install_path = self::resolve_install_path(
                (string) ($meta['install_path'] ?? ''),
                $installed_dir,
            );
            if ($install_path === null) {
                continue;
            }

            $type = (string) ($meta['type'] ?? '');
            $extra = self::read_webkernel_extra($install_path);

            if (! self::is_webkernel_package($name, $type, $extra)) {
                continue;
            }

            $entries[] = self::build_entry($name, $meta, $install_path, $type, $extra);
        }

        return $entries;
    }

    /**
     * Base directories from root composer.json path repositories
     * (e.g. software/*, software-modules/* → software, software-modules).
     *
     * @return list<string> Paths relative to project root, posix-ish.
     */
    public static function path_repository_dirs(?string $project_root = null): array
    {
        $project_root = $project_root !== null
            ? rtrim($project_root, '/\\')
            : Composer::root();

        $composer_path = $project_root . '/composer.json';
        if (! is_file($composer_path)) {
            return [];
        }

        try {
            /** @var array{repositories?: list<array<string, mixed>>} $composer */
            $composer = json_decode(
                (string) file_get_contents($composer_path),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException) {
            return [];
        }

        $dirs = [];
        foreach ($composer['repositories'] ?? [] as $repo) {
            if (! is_array($repo) || ($repo['type'] ?? '') !== 'path') {
                continue;
            }
            $url = (string) ($repo['url'] ?? '');
            if ($url === '') {
                continue;
            }

            // software/* → software ; software-experimental → software-experimental
            $base = preg_replace('#/\*$#', '', str_replace('\\', '/', $url)) ?? $url;
            $base = trim($base, '/');
            if ($base === '' || str_contains($base, '..')) {
                continue;
            }
            if (! is_dir($project_root . '/' . $base)) {
                continue;
            }
            $dirs[$base] = true;
        }

        return array_keys($dirs);
    }

    /**
     * Relative parent trees of non-vendor Webkernel install paths, merged with
     * path-repository bases from composer.json. Used for Tailwind globs etc.
     *
     * @param list<array{install_path: string}> $packages
     * @return list<string>
     */
    public static function derive_module_dirs(
        string $project_root,
        array $packages,
        string $vendor_dir = 'vendor',
    ): array {
        $project_root = rtrim(str_replace('\\', '/', $project_root), '/');
        $vendor_dir = trim(str_replace('\\', '/', $vendor_dir), '/');
        $dirs = [];

        foreach (self::path_repository_dirs($project_root) as $dir) {
            $dirs[$dir] = true;
        }

        foreach ($packages as $package) {
            $install_path = (string) ($package['install_path'] ?? '');
            if ($install_path === '') {
                continue;
            }

            $rel = self::relative_to_project($project_root, $install_path);
            if ($rel === null || $rel === '' || str_starts_with($rel, '..')) {
                continue;
            }

            // Covered by vendor_dir/** globs — do not double-list third_party/vendor.
            if ($rel === $vendor_dir || str_starts_with($rel, $vendor_dir . '/')) {
                continue;
            }

            $first = explode('/', $rel, 2)[0];
            if ($first !== '' && $first !== '.') {
                $dirs[$first] = true;
            }
        }

        ksort($dirs);

        return array_keys($dirs);
    }

    private static function installed_php_path(string $project_root): string
    {
        try {
            $vendor = Composer::vendor_dir();
            if (is_file($vendor . '/composer/installed.php')) {
                return $vendor . '/composer/installed.php';
            }
        } catch (\Throwable) {
            // fall through — resolve from project composer.json
        }

        $vendor_rel = 'vendor';
        $composer_path = $project_root . '/composer.json';
        if (is_file($composer_path)) {
            try {
                /** @var array{config?: array{"vendor-dir"?: string}} $composer */
                $composer = json_decode(
                    (string) file_get_contents($composer_path),
                    true,
                    512,
                    JSON_THROW_ON_ERROR,
                );
                $vendor_rel = $composer['config']['vendor-dir'] ?? 'vendor';
            } catch (\JsonException) {
                // keep default
            }
        }

        return $project_root . '/' . trim($vendor_rel, '/') . '/composer/installed.php';
    }

    private static function resolve_install_path(string $install_path, string $installed_dir): ?string
    {
        if ($install_path === '') {
            return null;
        }

        if (! str_starts_with($install_path, '/') && ! preg_match('#^[A-Za-z]:[\\\\/]#', $install_path)) {
            $install_path = $installed_dir . DIRECTORY_SEPARATOR . $install_path;
        }

        // Normalize . and .. without realpath() — keep Composer path-repo symlinks.
        $install_path = self::normalize_path($install_path);

        return is_dir($install_path) ? $install_path : null;
    }

    private static function normalize_path(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $is_absolute = str_starts_with($path, '/');
        $parts = explode('/', $path);
        $out = [];
        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($out);
                continue;
            }
            $out[] = $part;
        }
        $resolved = implode('/', $out);

        return $is_absolute ? '/' . $resolved : $resolved;
    }

    /** @return ?string relative posix path, or null if outside project */
    private static function relative_to_project(string $project_root, string $absolute): ?string
    {
        $project_root = rtrim(str_replace('\\', '/', $project_root), '/');
        $absolute = self::normalize_path($absolute);

        if (str_starts_with($absolute, $project_root . '/')) {
            return substr($absolute, strlen($project_root) + 1);
        }

        // Symlink install under vendor may not share the same prefix string;
        // try realpath only for the relative calculation.
        $root_real = realpath($project_root);
        $abs_real = realpath($absolute);
        if ($root_real !== false && $abs_real !== false) {
            $root_real = rtrim(str_replace('\\', '/', $root_real), '/');
            $abs_real = str_replace('\\', '/', $abs_real);
            if (str_starts_with($abs_real, $root_real . '/')) {
                return substr($abs_real, strlen($root_real) + 1);
            }
        }

        return null;
    }

    /** @param array<string, mixed> $meta @param array<string, mixed> $extra */
    private static function build_entry(
        string $name,
        array $meta,
        string $install_path,
        string $type,
        array $extra,
    ): array {
        $prefix = (string) ($extra['prefix'] ?? self::slug_from_name($name));
        $lang_path = self::resolve_lang_path($install_path, $extra);

        return [
            'id' => $prefix,
            'name' => $name,
            'slug' => $prefix,
            'version' => (string) ($meta['pretty_version'] ?? $meta['version'] ?? ''),
            'type' => $type,
            '_type' => str_starts_with($type, 'webkernel-') ? substr($type, 10) : $type,
            'install_path' => $install_path,
            'lang_path' => $lang_path,
            'prefix' => $prefix,
            'package_repo' => (string) ($extra['package_repo'] ?? ''),
            'config_scope' => (string) ($extra['config_scope'] ?? ''),
            'active' => true,
        ];
    }

    /** @param array<string, mixed> $extra */
    private static function is_webkernel_package(string $name, string $type, array $extra): bool
    {
        if (LCPackageType::tryFrom($type) !== null) {
            return true;
        }

        // Legacy / transitional type labels still seen in the wild.
        if (in_array($type, ['webkernel-module', 'webkernel-stdlib'], true)) {
            return true;
        }

        if (str_starts_with($name, 'webkernel/')) {
            return true;
        }

        return $extra !== [];
    }

    /** @return array<string, mixed> */
    private static function read_webkernel_extra(string $install_path): array
    {
        $composer_json = $install_path . '/composer.json';
        if (! is_file($composer_json)) {
            return [];
        }

        $raw = file_get_contents($composer_json);
        if ($raw === false) {
            return [];
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            return [];
        }

        $extra = $data['extra']['webkernel'] ?? [];

        return is_array($extra) ? $extra : [];
    }

    /** @param array<string, mixed> $extra */
    private static function resolve_lang_path(string $install_path, array $extra): ?string
    {
        if (isset($extra['lang_path']) && is_string($extra['lang_path']) && is_dir($extra['lang_path'])) {
            return $extra['lang_path'];
        }

        foreach (['lang', 'Lang'] as $dir) {
            $path = $install_path . '/' . $dir;
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function slug_from_name(string $name): string
    {
        return str_contains($name, '/') ? explode('/', $name, 2)[1] : $name;
    }
}
