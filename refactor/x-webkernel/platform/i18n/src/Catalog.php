<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n;

use Webkernel\Config\Config;

/**
 * File catalog from dumped LANG_PATH dirs, plus the known-language list.
 */
final class Catalog
{
    /** @var list<string> */
    public const RTL = ['ar', 'fa', 'he', 'ur', 'ps', 'ckb', 'ku'];

    /** @var array<string, array<string, mixed>> locale => tree */
    private static array $files = [];

    /** @var list<string>|null */
    private static ?array $paths = null;

    /**
     * @param $key string
     * @param $replace array<string, string|int|float>
     * @param $locale string|null
     *
     * @return string
     */
    public static function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $resolved = $locale ?? I18nContext::get_locale();
        $resolved = $resolved !== '' ? $resolved : self::default_locale();
        $value = self::stringify(self::pick(self::file($resolved), $key));
        if ($value === '') {
            $fallback = self::default_locale();
            if ($fallback !== $resolved) {
                $value = self::stringify(self::pick(self::file($fallback), $key));
            }
        }
        if ($value === '') {
            return $key;
        }
        foreach ($replace as $name => $replacement) {
            $value = \str_replace(':'.$name, (string) $replacement, $value);
        }

        return $value;
    }

    /**
     * @return string
     */
    public static function default_locale(): string
    {
        if (\class_exists(Config::class, true)) {
            $locale = Config::get('app.locale', 'en');
            if (\is_string($locale) && $locale !== '') {
                $normalized = I18nContext::normalize($locale);

                return $normalized !== '' ? $normalized : 'en';
            }
        }

        return 'en';
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return \array_keys(self::names());
    }

    /**
     * @return list<string>
     */
    public static function rtl_codes(): array
    {
        return self::RTL;
    }

    /**
     * @param $code string
     * @param $prefer_native bool
     *
     * @return string
     */
    public static function label(string $code, bool $prefer_native = true): string
    {
        $code = \str_replace('-', '_', \strtolower(\trim($code)));
        if ($code === '') {
            return '';
        }
        $english = self::names()[$code] ?? null;
        $native = self::native_names()[$code] ?? null;
        if ($prefer_native && $native !== null) {
            $text = $native;
        } elseif ($english !== null) {
            $text = $english;
        } elseif ($native !== null) {
            $text = $native;
        } else {
            $text = $code;
        }

        return $text.' ('.$code.')';
    }

    /**
     * @param $prefer_native bool
     *
     * @return array<string, string>
     */
    public static function options(bool $prefer_native = true): array
    {
        $out = [];
        foreach (self::codes() as $code) {
            $out[$code] = self::label($code, $prefer_native);
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public static function names(): array
    {
        return [
            'en' => 'English',
            'ar' => 'Arabic',
            'fr' => 'French',
            'es' => 'Spanish',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'hi' => 'Hindi',
            'az' => 'Azerbaijani',
            'bg' => 'Bulgarian',
            'bn' => 'Bengali',
            'ha' => 'Hausa',
            'ca' => 'Catalan',
            'ckb' => 'Kurdish (Sorani)',
            'cs' => 'Czech',
            'da' => 'Danish',
            'el' => 'Greek',
            'fa' => 'Persian',
            'fi' => 'Finnish',
            'he' => 'Hebrew',
            'hr' => 'Croatian',
            'hu' => 'Hungarian',
            'hy' => 'Armenian',
            'id' => 'Indonesian',
            'ka' => 'Georgian',
            'km' => 'Khmer',
            'ku' => 'Kurdish',
            'lt' => 'Lithuanian',
            'lv' => 'Latvian',
            'mk' => 'Macedonian',
            'ml' => 'Malayalam',
            'mn' => 'Mongolian',
            'ms' => 'Malay',
            'my' => 'Myanmar',
            'ne' => 'Nepali',
            'nl' => 'Dutch',
            'no' => 'Norwegian',
            'pa' => 'Punjabi',
            'pl' => 'Polish',
            'ps' => 'Pashto',
            'ro' => 'Romanian',
            'si' => 'Sinhala',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'so' => 'Somali',
            'sq' => 'Albanian',
            'sr' => 'Serbian',
            'sv' => 'Swedish',
            'sw' => 'Swahili',
            'ta' => 'Tamil',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'zh_CN' => 'Chinese (Simplified)',
            'zh_TW' => 'Chinese (Traditional)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function native_names(): array
    {
        return [
            'en' => 'English',
            'ar' => 'العربية',
            'fr' => 'Français',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'ko' => '한국어',
            'hi' => 'हिन्दी',
            'az' => 'Azərbaycan dili',
            'bg' => 'Български',
            'bn' => 'বাংলা',
            'ca' => 'Català',
            'cs' => 'Čeština',
            'da' => 'Dansk',
            'el' => 'Ελληνικά',
            'fa' => 'فارسی',
            'fi' => 'Suomi',
            'he' => 'עברית',
            'hr' => 'Hrvatski',
            'hu' => 'Magyar',
            'hy' => 'Հայերեն',
            'id' => 'Bahasa Indonesia',
            'ka' => 'ქართული',
            'km' => 'ខ្មែរ',
            'ku' => 'کوردی',
            'lt' => 'Lietuvių',
            'lv' => 'Latviešu',
            'mk' => 'Македонски',
            'ml' => 'മലയാളം',
            'mn' => 'Монгол',
            'ms' => 'Bahasa Melayu',
            'my' => 'မြန်မာ',
            'ne' => 'नेपाली',
            'nl' => 'Nederlands',
            'no' => 'Norsk',
            'pa' => 'ਪੰਜਾਬੀ',
            'pl' => 'Polski',
            'ps' => 'پښتو',
            'ro' => 'Română',
            'si' => 'සිංහල',
            'sk' => 'Slovenčina',
            'sl' => 'Slovenščina',
            'so' => 'Soomaali',
            'sq' => 'Shqip',
            'sr' => 'Српски',
            'sv' => 'Svenska',
            'sw' => 'Kiswahili',
            'ta' => 'தமிழ்',
            'th' => 'ไทย',
            'tr' => 'Türkçe',
            'uk' => 'Українська',
            'ur' => 'اردو',
            'uz' => 'Oʻzbekcha',
            'vi' => 'Tiếng Việt',
            'zh_CN' => '简体中文',
            'zh_TW' => '繁體中文',
        ];
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$files = [];
        self::$paths = null;
    }

    /**
     * @param $locale string
     *
     * @return array<string, mixed>
     */
    private static function file(string $locale): array
    {
        $locale = \str_replace('-', '_', \strtolower($locale));
        if (isset(self::$files[$locale])) {
            return self::$files[$locale];
        }
        $tree = [];
        foreach (self::paths() as $dir) {
            $file = $dir.'/'.$locale.'/translations.php';
            if (! \is_file($file)) {
                continue;
            }
            $loaded = \function_exists('webkernel_include') ? \webkernel_include($file) : require $file;
            if (\is_array($loaded)) {
                $tree = \array_replace_recursive($tree, $loaded);
            }
        }

        return self::$files[$locale] = $tree;
    }

    /**
     * @return list<string>
     */
    private static function paths(): array
    {
        if (self::$paths !== null) {
            return self::$paths;
        }
        $out = [];
        if (\function_exists('vendor_dir')) {
            $file = vendor_dir('composer/webkernel_lang.php');
            if (\is_file($file)) {
                $loaded = \function_exists('webkernel_include') ? \webkernel_include($file) : require $file;
                if (\is_array($loaded)) {
                    foreach ($loaded as $dir) {
                        if (\is_string($dir) && $dir !== '' && \is_dir($dir)) {
                            $out[] = \rtrim(\str_replace('\\', '/', $dir), '/');
                        }
                    }
                }
            }
        }

        return self::$paths = $out;
    }

    /**
     * @param $tree array<string, mixed>
     * @param $key string
     *
     * @return mixed
     */
    private static function pick(array $tree, string $key): mixed
    {
        if ($key !== '' && \array_key_exists($key, $tree)) {
            return $tree[$key];
        }
        $walk = $tree;
        foreach (\explode('.', $key) as $part) {
            if (! \is_array($walk) || ! \array_key_exists($part, $walk)) {
                return null;
            }
            $walk = $walk[$part];
        }

        return $walk;
    }

    /**
     * @param $value mixed
     *
     * @return string
     */
    private static function stringify(mixed $value): string
    {
        if (\is_string($value)) {
            return $value;
        }
        if (\is_array($value) && isset($value['label']) && \is_string($value['label'])) {
            return $value['label'];
        }

        return '';
    }
}
