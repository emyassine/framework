<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n;

use Webkernel\Config\Config;

/**
 * File catalog from dumped LANG_PATH dirs. Names and RTL come from ext-intl Locale.
 */
final class Catalog
{
    /** @var array<string, array<string, mixed>> locale => tree */
    private static array $files = [];

    /** @var list<string>|null */
    private static ?array $paths = null;

    /** @var list<string>|null */
    private static ?array $codes = null;

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
     * ISO 639 primary languages known to ICU. Regional tags still work in label().
     *
     * @return list<string>
     */
    public static function codes(): array
    {
        if (self::$codes !== null) {
            return self::$codes;
        }
        $out = [];
        foreach (\ResourceBundle::getLocales('') as $locale) {
            if (! \is_string($locale) || $locale === '') {
                continue;
            }
            $lang = \Locale::getPrimaryLanguage($locale);
            if (! \is_string($lang) || $lang === '' || $lang === 'und') {
                continue;
            }
            $out[$lang] = true;
        }
        $codes = \array_keys($out);
        \sort($codes);

        return self::$codes = $codes;
    }

    /**
     * @param $code string
     * @param $prefer_native bool
     *
     * @return string
     */
    public static function label(string $code, bool $prefer_native = true): string
    {
        $code = \trim($code);
        if ($code === '') {
            return '';
        }
        $tag = \str_replace('_', '-', $code);
        $in = $prefer_native ? $tag : 'en';
        $region = \Locale::getRegion($tag);
        $script = \Locale::getScript($tag);
        $has_variant = (\is_string($region) && $region !== '') || (\is_string($script) && $script !== '');
        $name = $has_variant
            ? \Locale::getDisplayName($tag, $in)
            : \Locale::getDisplayLanguage($tag, $in);
        if (! \is_string($name) || $name === '' || $name === $tag) {
            $name = $code;
        }

        return $name.' ('.$code.')';
    }

    /**
     * @return void
     */
    public static function flush(): void
    {
        self::$files = [];
        self::$paths = null;
        self::$codes = null;
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
