<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands\DumpAutoloadCommand;

trait CanDumpPanels
{
    use _DumpAutoloadCommand;

    /**
     * @param list<array{class: class-string, prefix: string, path: string}> $providers
     * @param array<string, string> $classmap
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
                $panel = self::PANEL_CLASS;
                $snapshot = $instance->panel(new $panel())->to_array();
                $snapshot['provider'] = $panel_class;
                $snapshot['package_provider'] = $provider;
                $snapshot['prefix'] = $row['prefix'];
                $out[] = $snapshot;
            }
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $panels
     * @param array<string, string> $classmap
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
                $out[] = [['GET', 'HEAD'], $prefix === '' ? '/' : $prefix, $page];
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
     * @param class-string $resource
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
