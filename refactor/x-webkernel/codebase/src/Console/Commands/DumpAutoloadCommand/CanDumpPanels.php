<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands\DumpAutoloadCommand;

use Webkernel\Platform\PanelProvider;

trait CanDumpPanels
{
    use _DumpAutoloadCommand;

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string, package?: string, type?: string}>
     * @param $classmap array<string, string>
     * @param $root string
     * @return list<array<string, mixed>>
     */
    private function panels_dump(array $providers, array $classmap, string $root): array
    {
        if (class_exists(\Webkernel\Config\Config::class, true)) {
            \Webkernel\Config\Config::boot($root);
        }
        $out = [];
        foreach ($providers as $row) {
            $provider = $row['class'];
            foreach ($provider::declaration('PANELS') as $panel_class) {
                if (! is_string($panel_class) || $panel_class === '') {
                    continue;
                }
                $this->ensure_class($panel_class, $classmap);
                if (
                    ! class_exists($panel_class)
                    || ! class_exists(self::PANEL_CLASS)
                    || ! class_exists(self::PANEL_PROVIDER_CLASS)
                    || ! is_a($panel_class, self::PANEL_PROVIDER_CLASS, true)
                ) {
                    continue;
                }
                $instance = new $panel_class();
                $scope = PanelProvider::scope_for_package(
                    (string) ($row['package'] ?? ''),
                    (string) ($row['type'] ?? ''),
                );
                $snapshot = $instance->register()->scope($scope)->to_array();
                $snapshot['provider'] = $panel_class;
                $snapshot['package_provider'] = $provider;
                $snapshot['prefix'] = $row['prefix'];
                $snapshot['package'] = $row['package'] ?? '';
                $manage = 'Webkernel\\Platform\\Pages\\ManagePanel';
                if (! \in_array($manage, $snapshot['pages'], true)) {
                    $snapshot['pages'][] = $manage;
                }
                $snapshot['navigation'] = $this->panel_navigation($snapshot, $classmap);
                $out[] = $snapshot;
            }
        }

        return $out;
    }

    /**
     * @param $panels list<array<string, mixed>>
     * @param $classmap array<string, string>
     * @return list<array{0: list<string>, 1: string, 2: class-string}>
     */
    private function panel_routes_dump(array $panels, array $classmap): array
    {
        $out = [];
        foreach ($panels as $panel) {
            $base = \trim((string) ($panel['path'] ?? ''), '/');
            $prefix = $base === '' ? '' : '/'.$base;
            foreach ($panel['pages'] ?? [] as $page) {
                if (! \is_string($page) || $page === '') {
                    continue;
                }
                $uri = $prefix === '' ? '/' : $prefix;
                $methods = ['GET', 'HEAD'];
                $this->ensure_class($page, $classmap);
                if (\class_exists($page) && \is_callable([$page, 'route'])) {
                    $def = $page::route();
                    if (\is_array($def)) {
                        $path = \trim((string) ($def['path'] ?? '/'), '/');
                        if ($path !== '') {
                            $uri = ($prefix === '' ? '' : $prefix).'/'.$path;
                        }
                        if (isset($def['methods']) && \is_array($def['methods'])) {
                            $methods = $def['methods'];
                        }
                    }
                }
                /** @var list<string> $methods */
                $out[] = [\array_values(\array_map(\strval(...), $methods)), $uri === '' ? '/' : $uri, $page];
            }
            foreach ($panel['resources'] ?? [] as $resource) {
                if (! \is_string($resource) || $resource === '') {
                    continue;
                }
                $this->ensure_class($resource, $classmap);
                if (! \class_exists($resource) || ! \is_a($resource, self::RESOURCE_CLASS, true)) {
                    continue;
                }
                $slug = $resource::$slug !== '' ? $resource::$slug : $this->resource_slug($resource);
                foreach ($resource::pages() as $def) {
                    $path = '/';
                    $class = $def;
                    $methods = ['GET', 'HEAD'];
                    if (\is_array($def)) {
                        $path = (string) ($def['path'] ?? '/');
                        $class = (string) ($def['class'] ?? '');
                        $methods = \is_array($def['methods'] ?? null) ? $def['methods'] : ['GET', 'HEAD'];
                    }
                    if ($class === '') {
                        continue;
                    }
                    $uri = $prefix.'/'.$slug.$path;
                    $uri = '/'.\trim(\str_replace('//', '/', $uri), '/');
                    if ($uri === '') {
                        $uri = '/';
                    }
                    /** @var list<string> $methods */
                    $out[] = [\array_values(\array_map(\strval(...), $methods)), $uri, $class];
                }
            }
        }

        return $out;
    }

    /**
     * @param $panel array<string, mixed>
     * @param $classmap array<string, string>
     * @return list<array{label: string, items: list<array{label: string, href: string, icon?: string}>}>
     */
    private function panel_navigation(array $panel, array $classmap): array
    {
        $base = '/'.\trim((string) ($panel['path'] ?? $panel['id'] ?? ''), '/');
        $manage = 'Webkernel\\Platform\\Pages\\ManagePanel';
        $items = [];
        foreach ($panel['pages'] ?? [] as $page) {
            if (! \is_string($page) || $page === '' || $page === $manage) {
                continue;
            }
            $this->ensure_class($page, $classmap);
            $href = $base;
            if (\class_exists($page) && \is_callable([$page, 'route'])) {
                $def = $page::route();
                if (\is_array($def)) {
                    $path = \trim((string) ($def['path'] ?? '/'), '/');
                    $href = $path === '' ? $base : $base.'/'.$path;
                }
            }
            $items[] = [
                'label' => $this->class_label($page),
                'href' => $href === '' ? '/' : $href,
                'icon' => 'layout-dashboard',
            ];
        }
        foreach ($panel['resources'] ?? [] as $resource) {
            if (! \is_string($resource) || $resource === '') {
                continue;
            }
            $this->ensure_class($resource, $classmap);
            if (! \class_exists($resource) || ! \is_a($resource, self::RESOURCE_CLASS, true)) {
                continue;
            }
            $slug = $resource::$slug !== '' ? $resource::$slug : $this->resource_slug($resource);
            $label = $slug !== ''
                ? \ucwords(\str_replace(['-', '_'], ' ', $slug))
                : $this->class_label($resource);
            $href = \rtrim($base, '/').'/'.$slug;
            $items[] = [
                'label' => $label,
                'href' => $href === '' ? '/' : $href,
                'icon' => (string) ($panel['icon'] ?? 'package'),
            ];
        }
        $manage_item = [
            'label' => 'panel.manage',
            'href' => \rtrim($base, '/').'/manage',
            'icon' => 'sliders',
        ];
        $groups = [];
        if ($items !== []) {
            $groups[] = [
                'label' => '',
                'items' => $items,
            ];
        }
        $groups[] = [
            'label' => 'panel.settings',
            'icon' => 'folder',
            'items' => [$manage_item],
        ];

        return $groups;
    }

    /**
     * @param $class class-string
     * @return string
     */
    private function class_label(string $class): string
    {
        $short = (new \ReflectionClass($class))->getShortName();
        if (\str_ends_with($short, 'Resource')) {
            $short = \substr($short, 0, -8);
        }
        $spaced = \preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $short);

        return \is_string($spaced) ? $spaced : $short;
    }

    /**
     * @param $resource class-string
     * @return string
     */
    private function resource_slug(string $resource): string
    {
        $short = (new \ReflectionClass($resource))->getShortName();
        if (\str_ends_with($short, 'Resource')) {
            $short = \substr($short, 0, -8);
        }
        $kebab = \preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $short);

        return \strtolower(\is_string($kebab) ? $kebab : $short);
    }
}
