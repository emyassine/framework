<?php

declare(strict_types=1);

namespace Webkernel\Imagery;

use BladeUI\Icons\Factory as IconFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use ReflectionClass;

/**
 * Discovers blade-icons sets and lists SVG icons for the picker.
 */
final class IconSetManager
{
    protected IconFactory $icon_factory;

    protected ?Collection $cached_icons = null;

    public function __construct()
    {
        $this->icon_factory = app(IconFactory::class);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function get_sets(): array
    {
        $sets = [];

        $reflection = new ReflectionClass($this->icon_factory);
        if ($reflection->hasProperty('sets')) {
            $sets_property = $reflection->getProperty('sets');
            $factory_sets = $sets_property->getValue($this->icon_factory);

            foreach ($factory_sets as $set_name => $set_config) {
                $sets[$set_name] = [
                    'name' => $set_name,
                    'prefix' => $set_config['prefix'] ?? $set_name,
                    'path' => $set_config['path'] ?? $set_config['paths'][0] ?? null,
                    'paths' => $set_config['paths'] ?? [$set_config['path'] ?? null],
                ];
            }
        }

        $blade_icons_config = config('blade-icons.sets', []);
        foreach ($blade_icons_config as $set_name => $set_config) {
            if (! isset($sets[$set_name])) {
                $sets[$set_name] = [
                    'name' => $set_name,
                    'prefix' => $set_config['prefix'] ?? $set_name,
                    'path' => $set_config['path'] ?? null,
                    'paths' => [$set_config['path'] ?? null],
                ];
            }
        }

        return $sets;
    }

    /**
     * @return list<string>
     */
    public function get_set_names(): array
    {
        return array_keys($this->get_sets());
    }

    /**
     * @param  array<string>|null  $allowed_sets
     * @return Collection<int, array{name: string, set: string, prefix: string, label?: string}>
     */
    public function get_icons(?array $allowed_sets = null): Collection
    {
        $cache_key = 'imagery:icons:'.md5(serialize($allowed_sets));

        if (config('imagery.cache_icons', true)) {
            return Cache::remember(
                $cache_key,
                config('imagery.cache_duration', 86400),
                fn (): Collection => $this->load_icons($allowed_sets),
            );
        }

        return $this->load_icons($allowed_sets);
    }

    /**
     * @param  array<string>|null  $allowed_sets
     * @return Collection<int, array{name: string, set: string, prefix: string, label?: string}>
     */
    protected function load_icons(?array $allowed_sets = null): Collection
    {
        $icons = collect();
        $sets = $this->get_sets();

        $config_allowed = config('imagery.allowed_sets', []);
        if (! empty($config_allowed)) {
            $allowed_sets = $allowed_sets
                ? array_intersect($allowed_sets, $config_allowed)
                : $config_allowed;
        }

        foreach ($sets as $set_name => $set_config) {
            if ($allowed_sets && ! in_array($set_name, $allowed_sets, true)) {
                continue;
            }

            $icons = $icons->merge($this->get_icons_from_set($set_name, $set_config));
        }

        return $icons->sortBy('name')->values();
    }

    /**
     * @param  array<string, mixed>  $set_config
     * @return Collection<int, array{name: string, set: string, prefix: string, label: string}>
     */
    protected function get_icons_from_set(string $set_name, array $set_config): Collection
    {
        $icons = collect();
        $prefix = $set_config['prefix'] ?? $set_name;
        $paths = $set_config['paths'] ?? [$set_config['path'] ?? null];

        foreach ($paths as $path) {
            if (! $path || ! File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() !== 'svg') {
                    continue;
                }

                $relative_path = str_replace($path.DIRECTORY_SEPARATOR, '', $file->getPathname());
                $icon_name = str_replace([DIRECTORY_SEPARATOR, '/', '.svg'], ['-', '-', ''], $relative_path);
                $full_name = $prefix.'-'.$icon_name;

                $icons->push([
                    'name' => $full_name,
                    'set' => $set_name,
                    'prefix' => $prefix,
                    'label' => $this->format_icon_label($icon_name),
                ]);
            }
        }

        return $icons;
    }

    protected function format_icon_label(string $name): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $name));
    }

    /**
     * @param  array<string>|null  $allowed_sets
     * @return Collection<int, array{name: string, set: string, prefix: string, label?: string}>
     */
    public function search_icons(string $query, ?array $allowed_sets = null, ?string $set_filter = null): Collection
    {
        $icons = $this->get_icons($allowed_sets);

        if ($set_filter) {
            $icons = $icons->filter(fn (array $icon): bool => $icon['set'] === $set_filter);
        }

        if ($query === '') {
            return $icons;
        }

        $query = strtolower($query);

        return $icons->filter(function (array $icon) use ($query): bool {
            return str_contains(strtolower($icon['name']), $query)
                || str_contains(strtolower((string) ($icon['label'] ?? '')), $query);
        })->values();
    }

    /**
     * @param  array<string>|null  $allowed_sets
     * @return array{icons: Collection, has_more: bool, total: int}
     */
    public function get_icons_paginated(
        int $page = 1,
        int $per_page = 100,
        ?string $search = null,
        ?string $set_filter = null,
        ?array $allowed_sets = null,
    ): array {
        $icons = $search
            ? $this->search_icons($search, $allowed_sets, $set_filter)
            : $this->get_icons($allowed_sets);

        if ($set_filter && ! $search) {
            $icons = $icons->filter(fn (array $icon): bool => $icon['set'] === $set_filter);
        }

        $total = $icons->count();
        $offset = ($page - 1) * $per_page;
        $page_icons = $icons->slice($offset, $per_page)->values();

        return [
            'icons' => $page_icons,
            'has_more' => ($offset + $per_page) < $total,
            'total' => $total,
        ];
    }

    public function clear_cache(): void
    {
        Cache::forget('imagery:icons:'.md5(serialize(null)));

        foreach ($this->get_set_names() as $set) {
            Cache::forget('imagery:icons:'.md5(serialize([$set])));
        }
    }

    /**
     * @return list<string>
     */
    public function get_icons_for_set(string $set_name): array
    {
        $sets = $this->get_sets();
        if (! isset($sets[$set_name])) {
            return [];
        }

        return $this->get_icons_from_set($set_name, $sets[$set_name])->pluck('name')->all();
    }
}
