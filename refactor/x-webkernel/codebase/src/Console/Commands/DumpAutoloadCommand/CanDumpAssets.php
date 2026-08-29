<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Console\Commands\DumpAutoloadCommand;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Webkernel\DevEnv\IdeHelper;
use Webkernel\Platform\Colors\Color;
use Webkernel\Platform\Wds;

trait CanDumpAssets
{
    use _DumpAutoloadCommand;

    /** @var list<string> */
    private const WDS_CSS_ORDER = ['tokens', 'shell', 'simple', 'btn', 'page', 'field'];

    /**
     * @param list<array{path: string, package: array<string, mixed>}> $packages
     * @return array<string, array{brand: string, format: string, hash: string, source: string}>
     */
    private function branding_dump(array $packages, string $root): array
    {
        $out = [];
        foreach ($packages as $row) {
            $dir = $row['path'].'/res/brands';
            if (! \is_dir($dir)) {
                continue;
            }
            $files = \glob($dir.'/*/*.brand.php');
            if (! \is_array($files)) {
                continue;
            }
            foreach ($files as $file) {
                if (! \is_string($file) || $file === '') {
                    continue;
                }
                $asset = require $file;
                if (! \is_array($asset) || ! isset($asset['key'], $asset['format'], $asset['data'])) {
                    continue;
                }
                $rel = $this->relative($root, $file) ?? $file;
                $out[(string) $asset['key']] = [
                    'brand' => \basename(\dirname($file)),
                    'format' => (string) $asset['format'],
                    'hash' => \md5((string) $asset['data']),
                    'source' => \str_replace('\\', '/', $rel),
                ];
            }
        }

        return $out;
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string, package?: string, type?: string}>
     * @param $panels list<array<string, mixed>>
     * @return array<string, string>
     */
    private function icons_dump(array $providers, array $panels = []): array
    {
        /** @var array<string, true> $names */
        $names = [];
        foreach (['VIEWS', 'COMPONENTS'] as $constant) {
            foreach ($this->collect_provider_paths($providers, $constant) as $dirs) {
                foreach ($dirs as $dir) {
                    $this->collect_icon_names($names, $dir);
                }
            }
        }
        foreach ($panels as $panel) {
            $icon = $panel['icon'] ?? '';
            if (\is_string($icon) && $icon !== '') {
                $names['lucide/'.$icon] = true;
            }
            foreach ($panel['navigation'] ?? [] as $group) {
                if (! \is_array($group)) {
                    continue;
                }
                foreach ($group['items'] ?? [] as $item) {
                    if (! \is_array($item)) {
                        continue;
                    }
                    $item_icon = $item['icon'] ?? '';
                    if (\is_string($item_icon) && $item_icon !== '') {
                        $names['lucide/'.$item_icon] = true;
                    }
                }
            }
        }
        $out = [];
        foreach (\array_keys($names) as $key) {
            $slash = \strpos($key, '/');
            if ($slash === false) {
                continue;
            }
            $set = \substr($key, 0, $slash);
            $name = \substr($key, $slash + 1);
            $file = \class_exists(\Webkernel\Imagery\Icon::class, true)
                ? \Webkernel\Imagery\Icon::path($name, $set)
                : '';
            if ($file === '' || ! \is_file($file)) {
                continue;
            }
            $svg = \file_get_contents($file);
            if (\is_string($svg) && $svg !== '') {
                $out[$key] = $svg;
            }
        }
        \ksort($out);

        return $out;
    }

    /**
     * @param array<string, true> $names
     */
    private function collect_icon_names(array &$names, string $dir): void
    {
        $dir = \rtrim(\str_replace('\\', '/', $dir), '/');
        if (! \is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($it as $file) {
            if (! $file->isFile() || ! \str_ends_with($file->getFilename(), '.view.php')) {
                continue;
            }
            $src = \file_get_contents($file->getPathname());
            if (! \is_string($src) || $src === '') {
                continue;
            }
            if (\preg_match_all('/\bicon\(\s*[\'"]([^\'"]+)[\'"]/', $src, $calls) !== false) {
                foreach ($calls[1] as $call_name) {
                    if (\is_string($call_name) && $call_name !== '') {
                        $names['lucide/'.$call_name] = true;
                    }
                }
            }
            if (\preg_match_all('/<x-webkernel::(icon|button)\b([^>]*)>/', $src, $tags, \PREG_SET_ORDER) === false) {
                continue;
            }
            foreach ($tags as $tag) {
                $kind = $tag[1];
                $attrs = $tag[2];
                $dynamic = $kind === 'icon' ? '/(?:^|\s):name\b/' : '/(?:^|\s):icon\b/';
                if (\preg_match($dynamic, $attrs) === 1) {
                    continue;
                }
                $name = '';
                $set = 'lucide';
                $attr = $kind === 'icon' ? 'name' : 'icon';
                if (\preg_match('/\b'.$attr.'="([^"]+)"/', $attrs, $m) === 1) {
                    $name = $m[1];
                }
                if ($kind === 'icon' && \preg_match('/\bset="([^"]+)"/', $attrs, $m) === 1) {
                    $set = $m[1];
                }
                if ($name !== '') {
                    $names[$set.'/'.$name] = true;
                }
            }
        }
    }

    /**
     * Concatenate WDS `<style>` blocks into public/wds.css. Palettes are baked here.
     *
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return void
     */
    private function dump_wds_css(array $providers): void
    {
        $parts = $this->wds_css_parts($providers);
        if ($parts === []) {
            $this->terminal()->warning('wds css: no style blocks in views/wds');

            return;
        }
        $dest = Wds::css_path();
        $dir = \dirname($dest);
        if (! \is_dir($dir) && ! \mkdir($dir, 0775, true) && ! \is_dir($dir)) {
            $this->terminal()->warning('cannot create '.$dir);

            return;
        }
        $header = "/*\n"
            .IdeHelper::generated_header()."\n"
            ."//>\n"
            ."//> Generated. Do not edit.\n"
            ."//> WDS chrome. Source: resources/views/wds/*.view.php\n"
            ."*/\n\n";
        $body = [];
        foreach ($parts as $part) {
            $min = $this->minify_css($part);
            if ($min !== '') {
                $body[] = $min;
            }
        }
        \file_put_contents($dest, $header.\implode('', $body)."\n", \LOCK_EX);
    }

    /**
     * @param $css string
     *
     * @return string
     */
    private function minify_css(string $css): string
    {
        $css = \preg_replace('/\/\*.*?\*\//s', '', $css) ?? $css;
        $css = \preg_replace('/\s+/', ' ', $css) ?? $css;
        $css = \preg_replace('/\s*([{}:;,])\s*/', '$1', $css) ?? $css;

        return \trim($css);
    }

    /**
     * @param $providers list<array{class: class-string, prefix: string, path: string}>
     *
     * @return list<string>
     */
    private function wds_css_parts(array $providers): array
    {
        /** @var array<string, string> $named */
        $named = [];
        foreach ($this->collect_provider_paths($providers, 'VIEWS') as $dirs) {
            foreach ($dirs as $dir) {
                $wds = \rtrim(\str_replace('\\', '/', $dir), '/').'/wds';
                if (! \is_dir($wds)) {
                    continue;
                }
                $files = \glob($wds.'/*.view.php');
                if (! \is_array($files)) {
                    continue;
                }
                foreach ($files as $file) {
                    if (! \is_string($file) || $file === '') {
                        continue;
                    }
                    $css = $this->wds_style_from_view($file);
                    if ($css === '') {
                        continue;
                    }
                    $named[\basename($file, '.view.php')] = $css;
                }
            }
        }
        $ordered = [];
        foreach (self::WDS_CSS_ORDER as $name) {
            if (isset($named[$name])) {
                $ordered[] = $named[$name];
                unset($named[$name]);
            }
        }
        \ksort($named);

        return [...$ordered, ...\array_values($named)];
    }

    /**
     * @param $file string
     *
     * @return string
     */
    private function wds_style_from_view(string $file): string
    {
        $src = \file_get_contents($file);
        if (! \is_string($src) || $src === '') {
            return '';
        }
        $stripped = \preg_replace('/\{\{--.*?--\}\}/s', '', $src);
        if (\is_string($stripped)) {
            $src = $stripped;
        }
        $src = \str_replace(
            '{!! \\Webkernel\\Platform\\Colors\\Color::root_css() !!}',
            Color::root_css(),
            $src,
        );
        if (\preg_match_all('/<style\b[^>]*>(.*?)<\/style>/s', $src, $matches) < 1) {
            return '';
        }

        return \trim(\implode("\n", $matches[1]));
    }

    /**
     * Copy language/country marks to public/flags.
     *
     * @return void
     */
    private function dump_flags(): void
    {
        if (! \class_exists(\Webkernel\I18n\I18nProvider::class, true)) {
            return;
        }
        $src = \dirname((new \ReflectionClass(\Webkernel\I18n\I18nProvider::class))->getFileName(), 2).'/res/flags';
        $dest = \webapp_path('public/flags');
        if (! \is_dir($src)) {
            return;
        }
        $this->copy_tree($src, $dest);
    }

    /**
     * @param $from string
     * @param $to string
     *
     * @return void
     */
    private function copy_tree(string $from, string $to): void
    {
        if (! \is_dir($to) && ! \mkdir($to, 0775, true) && ! \is_dir($to)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );
        foreach ($it as $file) {
            $rel = \substr($file->getPathname(), \strlen($from) + 1);
            if (! \is_string($rel) || $rel === '') {
                continue;
            }
            $target = $to.DIRECTORY_SEPARATOR.$rel;
            if ($file->isDir()) {
                if (! \is_dir($target)) {
                    \mkdir($target, 0775, true);
                }
                continue;
            }
            $dir = \dirname($target);
            if (! \is_dir($dir)) {
                \mkdir($dir, 0775, true);
            }
            \copy($file->getPathname(), $target);
        }
    }
}
