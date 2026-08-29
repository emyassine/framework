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

    public const FONTS_CSS = 'fetch-fonts/wts-fonts.css';

    public const RULES_CSS = 'fetch-fonts/wts.css';

    /**
     * @return array<string, array{family: string, google: string}>
     */
    public static function catalog(): array
    {
        return [
            'dm-sans' => [
                'family' => 'DM Sans',
                'google' => 'DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000',
            ],
            'lilex' => [
                'family' => 'Lilex',
                'google' => 'Lilex:ital,wght@0,100..700;1,100..700',
            ],
            'ibm-plex-sans-arabic' => [
                'family' => 'IBM Plex Sans Arabic',
                'google' => 'IBM+Plex+Sans+Arabic:wght@100;200;300;400;500;600;700',
            ],
            'rubik' => [
                'family' => 'Rubik',
                'google' => 'Rubik:ital,wght@0,300..900;1,300..900',
            ],
            'amiri-quran' => [
                'family' => 'Amiri Quran',
                'google' => 'Amiri+Quran',
            ],
            'noto-nastaliq-urdu' => [
                'family' => 'Noto Nastaliq Urdu',
                'google' => 'Noto+Nastaliq+Urdu:wght@400..700',
            ],
            'noto-sans-hebrew' => [
                'family' => 'Noto Sans Hebrew',
                'google' => 'Noto+Sans+Hebrew:wght@100..900',
            ],
            'noto-sans-jp' => [
                'family' => 'Noto Sans JP',
                'google' => 'Noto+Sans+JP:wght@100..900',
            ],
            'noto-sans-sc' => [
                'family' => 'Noto Sans SC',
                'google' => 'Noto+Sans+SC:wght@100..900',
            ],
            'noto-sans-kr' => [
                'family' => 'Noto Sans KR',
                'google' => 'Noto+Sans+KR:wght@100..900',
            ],
            'space-grotesk' => [
                'family' => 'Space Grotesk',
                'google' => 'Space+Grotesk:wght@500;600;700',
            ],
        ];
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
     * @return string
     */
    public static function google_css_url(): string
    {
        $parts = [];
        foreach (self::catalog() as $meta) {
            if ($meta['family'] === 'Space Grotesk') {
                continue;
            }
            $parts[] = 'family='.$meta['google'];
        }

        return 'https://fonts.googleapis.com/css2?'.\implode('&', $parts).'&display=swap';
    }

    /**
     * @return bool
     */
    public static function has_local_fonts(): bool
    {
        $path = self::path(self::FONTS_CSS);

        return \is_file($path) && (\filesize($path) ?: 0) > 32;
    }

    /**
     * Public href for self-hosted font CSS, or the Google CDN URL.
     *
     * @return string
     */
    public static function fonts_href(): string
    {
        if (! self::has_local_fonts()) {
            return self::google_css_url();
        }
        $path = self::path(self::FONTS_CSS);
        $mtime = \filemtime($path) ?: 0;

        return '/'.self::FONTS_CSS.'?v='.$mtime;
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
