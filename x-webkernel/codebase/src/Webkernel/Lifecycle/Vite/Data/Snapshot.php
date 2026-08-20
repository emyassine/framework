<?php declare(strict_types=1);

namespace Webkernel\Lifecycle\Vite\Data;

/**
 * Full discovery payload for {@see \Webkernel\Lifecycle\Vite\Generator\Template}.
 *
 * Not the public lifecycle facade — that is {@see \Webkernel\Lifecycle\Vite\ViteWebapp}.
 */
final class Snapshot
{
    /**
     * @param list<string> $module_dirs
     * @param list<string> $extra_globs
     * @param list<string> $entries
     * @param array<string, string> $aliases
     * @param list<string> $tailwind_sources
     * @param list<string> $tailwind_globs
     * @param list<string> $packages_json_globs  npm workspaces globs from vendor-dir + modules
     * @param list<string> $css_imports  package CSS folded into host app.css
     * @param list<Module> $modules
     */
    public function __construct(
        public readonly string $generated_at,
        public readonly string $vendor_dir,
        public readonly array $module_dirs,
        public readonly string $extensions,
        public readonly array $extra_globs,
        public readonly array $entries,
        public readonly array $aliases,
        public readonly array $tailwind_sources,
        public readonly array $tailwind_globs,
        public readonly array $modules,
        public readonly array $css_imports = [],
        public readonly array $packages_json_globs = [],
    ) {
    }

    /**
     * npm workspace globs — Webkernel packages only (not all of vendor-dir).
     *
     * Scanning the whole vendor tree for package.json pulls random Composer deps
     * (blade-heroicons, tiptap-php, …) and still misses path-repo packages that
     * only exist under software until they gain package.json.
     *
     * Reliable set:
     * - {vendor-dir}/webkernel/…  e.g. third_party/webkernel/plugin-mdsite
     * - modules/…/…               business modules
     * - software/…                monorepo path-repo sources (local package.json)
     *
     * Runtime discovery still prefers Composer modules[] from the vite snapshot.
     *
     * @return list<string>
     */
    public static function packages_json_globs_for(string $vendor_dir): array
    {
        $vendor_dir = trim(str_replace('\\', '/', $vendor_dir), '/');
        if ($vendor_dir === '') {
            $vendor_dir = 'vendor';
        }

        // Composer install layout only. Never path-repo sources (software/*):
        // third_party/webkernel/* is already symlinked there → npm EDUPLICATEWORKSPACE.
        return [
            $vendor_dir.'/webkernel/*',
            'modules/*/*',
        ];
    }

    /**
     * JSON-ready payload. Empty aliases must encode as `{}`, never `[]`.
     *
     * @return array<string, mixed>
     */
    public function to_json_payload(): array
    {
        $packages_json_globs = $this->packages_json_globs !== []
            ? $this->packages_json_globs
            : self::packages_json_globs_for($this->vendor_dir);

        return [
            'generated_at' => $this->generated_at,
            'vendor_dir' => $this->vendor_dir,
            'module_dirs' => $this->module_dirs,
            'extensions' => $this->extensions,
            'extra_globs' => $this->extra_globs,
            'entries' => $this->entries,
            'aliases' => $this->aliases === [] ? new \stdClass() : $this->aliases,
            'tailwind_sources' => $this->tailwind_sources,
            'tailwind_globs' => $this->tailwind_globs,
            'packages_json_globs' => $packages_json_globs,
            'css_imports' => $this->css_imports,
            'modules' => array_map(
                static fn (Module $module): array => $module->to_array(),
                $this->modules,
            ),
        ];
    }
}
