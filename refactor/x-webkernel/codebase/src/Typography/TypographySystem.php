<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Typography;

/**
 * Webkernel Typography System. Files live at webapp_path("public/$typo_path").
 */
final class TypographySystem
{
    public const DIR = 'fetch-fonts';

    public const RULES_CSS = 'fetch-fonts/wts.css';

    /**
     * @return array<string, array{family: string, google: string, pack: string}>
     */
    public static function catalog(): array
    {
        return [
            'dm-sans' => [
                'family' => 'DM Sans',
                'google' => 'DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000',
                'pack' => 'latin',
            ],
            'lilex' => [
                'family' => 'Lilex',
                'google' => 'Lilex:ital,wght@0,100..700;1,100..700',
                'pack' => 'latin',
            ],
            'ibm-plex-sans-arabic' => [
                'family' => 'IBM Plex Sans Arabic',
                'google' => 'IBM+Plex+Sans+Arabic:wght@100;200;300;400;500;600;700',
                'pack' => 'arabic',
            ],
            'rubik' => [
                'family' => 'Rubik',
                'google' => 'Rubik:ital,wght@0,300..900;1,300..900',
                'pack' => 'arabic',
            ],
            'amiri-quran' => [
                'family' => 'Amiri Quran',
                'google' => 'Amiri+Quran',
                'pack' => 'arabic',
            ],
            'noto-nastaliq-urdu' => [
                'family' => 'Noto Nastaliq Urdu',
                'google' => 'Noto+Nastaliq+Urdu:wght@400..700',
                'pack' => 'arabic',
            ],
            'noto-sans-hebrew' => [
                'family' => 'Noto Sans Hebrew',
                'google' => 'Noto+Sans+Hebrew:wght@100..900',
                'pack' => 'hebrew',
            ],
            'noto-sans-jp' => [
                'family' => 'Noto Sans JP',
                'google' => 'Noto+Sans+JP:wght@100..900',
                'pack' => 'cjk',
            ],
            'noto-sans-sc' => [
                'family' => 'Noto Sans SC',
                'google' => 'Noto+Sans+SC:wght@100..900',
                'pack' => 'cjk',
            ],
            'noto-sans-kr' => [
                'family' => 'Noto Sans KR',
                'google' => 'Noto+Sans+KR:wght@100..900',
                'pack' => 'cjk',
            ],
            'space-grotesk' => [
                'family' => 'Space Grotesk',
                'google' => 'Space+Grotesk:wght@500;600;700',
                'pack' => 'latin',
            ],
        ];
    }

    /**
     * @param $locale string|null
     *
     * @return string
     */
    public static function pack(?string $locale = null): string
    {
        $tag = \strtolower(\str_replace('_', '-', \trim((string) $locale)));
        $lang = \explode('-', $tag === '' ? 'en' : $tag, 2)[0];

        return match ($lang) {
            'ar', 'fa', 'ur', 'ps', 'ckb', 'ku' => 'arabic',
            'he', 'yi' => 'hebrew',
            'ja', 'zh', 'ko' => 'cjk',
            default => 'latin',
        };
    }

    /**
     * @return list<string>
     */
    public static function packs(): array
    {
        return ['latin', 'arabic', 'hebrew', 'cjk'];
    }

    /**
     * @param $pack string
     *
     * @return string
     */
    public static function fonts_css(string $pack): string
    {
        return self::DIR.'/wts-fonts-'.$pack.'.css';
    }

    /**
     * @param $typo_path string
     *
     * @return string
     */
    public static function path(string $typo_path): string
    {
        return webapp_path('public/'.$typo_path);
    }

    /**
     * Remote Google woff2 urls in $css, mapped to /fetch-fonts/{slug}/{basename}.
     *
     * @param $css string
     * @param $slug string
     *
     * @return array<string, string>
     */
    public static function woff2_targets(string $css, string $slug): array
    {
        if (\preg_match_all('#url\([\'"]?(https://[^\'")\s]+\.woff2)[\'"]?\)#', $css, $matches) < 1) {
            return [];
        }
        $map = [];
        foreach (\array_unique($matches[1]) as $remote) {
            $path = \parse_url($remote, PHP_URL_PATH);
            $name = \basename(\is_string($path) && $path !== '' ? $path : $remote);
            $map[$remote] = '/'.self::DIR.'/'.$slug.'/'.$name;
        }

        return $map;
    }

    /**
     * @param $pack string
     *
     * @return string
     */
    public static function google_css_url_for_pack(string $pack): string
    {
        $parts = [];
        foreach (self::catalog() as $meta) {
            if ($meta['family'] === 'Space Grotesk' || $meta['pack'] !== $pack) {
                continue;
            }
            $parts[] = 'family='.$meta['google'];
        }

        return 'https://fonts.googleapis.com/css2?'.\implode('&', $parts).'&display=optional';
    }

    /**
     * @param $locale string|null
     *
     * @return string
     */
    public static function google_css_url(?string $locale = null): string
    {
        return self::google_css_url_for_pack(self::pack($locale));
    }

    /**
     * @param $locale string|null
     *
     * @return bool
     */
    public static function has_local_fonts(?string $locale = null): bool
    {
        $path = self::path(self::fonts_css(self::pack($locale)));
        if (! \is_file($path)) {
            return false;
        }
        $css = \file_get_contents($path);
        if (! \is_string($css) || $css === '') {
            return false;
        }

        return \str_contains($css, '/'.self::DIR.'/')
            && ! \str_contains($css, 'fonts.googleapis.com');
    }

    /**
     * Public href for the script pack CSS, or the Google CDN URL for that pack.
     *
     * @param $locale string|null
     *
     * @return string
     */
    public static function fonts_href(?string $locale = null): string
    {
        if (! self::has_local_fonts($locale)) {
            return self::google_css_url($locale);
        }
        $rel = self::fonts_css(self::pack($locale));
        $mtime = \filemtime(self::path($rel)) ?: 0;

        return '/'.$rel.'?v='.$mtime;
    }

    /**
     * Critical woff2 for preload (latin/arabic/… regular, no late swap).
     *
     * @param $locale string|null
     *
     * @return string
     */
    public static function preload_href(?string $locale = null): string
    {
        $rel = self::fonts_css(self::pack($locale));
        $css_path = self::path($rel);
        if (! \is_file($css_path)) {
            return '';
        }
        $css = \file_get_contents($css_path);
        if (! \is_string($css) || $css === '') {
            return '';
        }
        if (\preg_match(
            '/font-style:\s*normal;[^}]*src:\s*url\(([^)]+)\)[^}]*unicode-range:\s*U\+0000-00FF/s',
            $css,
            $match,
        ) !== 1 && \preg_match(
            '/font-style:\s*normal;[^}]*src:\s*url\(([^)]+)\)/s',
            $css,
            $match,
        ) !== 1) {
            return '';
        }

        return \trim($match[1]);
    }

    /**
     * @return string
     */
    public static function rules_href(): string
    {
        $path = self::path(self::RULES_CSS);
        if (! \is_file($path)) {
            return '';
        }
        $mtime = \filemtime($path) ?: 0;

        return '/'.self::RULES_CSS.'?v='.$mtime;
    }
}
