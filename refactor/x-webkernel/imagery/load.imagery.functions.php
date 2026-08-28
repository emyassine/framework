<?php

declare(strict_types=1);

// @package webkernel/imagery

use Webkernel\Imagery\BrandingStore;
use Webkernel\Imagery\GetIcons;

if (! function_exists('webkernel_branding_store')) {
    /**
     * Singleton branding store (logos, favicons, …).
     */
    function webkernel_branding_store(): BrandingStore
    {
        static $store = null;

        if ($store !== null) {
            return $store;
        }

        $store = new BrandingStore;

        try {
            $brands_path = webkernel_package('imagery', 'res/brands');
            if (is_string($brands_path) && is_dir($brands_path)) {
                foreach (['webkernel', 'numerimondes', 'thebestrecruit'] as $brand) {
                    $dir = $brands_path.DIRECTORY_SEPARATOR.$brand;
                    if (is_dir($dir)) {
                        $store->load_from_directory($dir, $brand);
                    }
                }
            }
        } catch (\Throwable) {
            // Brand assets are non-critical.
        }

        $store->register_routes();

        return $store;
    }
}

if (! function_exists('webkernel_branding_url')) {
    /**
     * Absolute URL for a registered branding asset.
     */
    function webkernel_branding_url(string $key): string
    {
        return webkernel_branding_store()->url($key);
    }
}

if (! function_exists('webkernel_svg_collection_paths')) {
    /**
     * Ordered list of icon directories for webkernel_grab_icon().
     *
     * @return list<string>
     */
    function webkernel_svg_collection_paths(): array
    {
        static $paths = null;

        if ($paths !== null) {
            return $paths;
        }

        $paths = GetIcons::paths();

        return $paths;
    }
}

if (! function_exists('webkernel_grab_icon')) {
    /**
     * Load an SVG icon by basename and inject class/style attributes.
     */
    function webkernel_grab_icon(string $filename, string $class = '', string $style = ''): ?string
    {
        foreach (webkernel_svg_collection_paths() as $rel) {
            $path = function_exists('webapp_path')
                ? webapp_path($rel)
                : (function_exists('base_path') ? base_path($rel) : $rel);
            $full = \rtrim((string) $path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename.'.svg';

            if (! is_file($full)) {
                continue;
            }

            $svg = file_get_contents($full);
            if ($svg === false) {
                return null;
            }

            $inject = '';
            if ($class !== '') {
                $inject .= ' class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'"';
            }
            if ($style !== '') {
                $inject .= ' style="'.htmlspecialchars($style, ENT_QUOTES, 'UTF-8').'"';
            }

            if ($inject !== '') {
                $svg = substr_replace($svg, $inject, 4, 0);
            }

            return $svg;
        }

        return null;
    }
}

// Eagerly register branding routes for non-CLI HTTP requests.
if (PHP_SAPI !== 'cli') {
    webkernel_branding_store();
}
