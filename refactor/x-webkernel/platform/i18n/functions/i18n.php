<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

use Webkernel\I18n\Catalog;
use Webkernel\I18n\I18nContext;
use Webkernel\I18n\Support\LocaleDirection;
use Webkernel\I18n\Support\TranslatedValue;

if (! \function_exists('fast_i18n')) {
    /**
     * Inline locale map. Pass $locale on hot paths (no context lookup).
     *
     * @param $map array<string, string>
     * @param $locale string|null
     * @param $key string
     * @param $default string
     *
     * @return string
     */
    function fast_i18n(
        array $map,
        ?string $locale = null,
        string $key = '',
        string $default = 'en',
    ): string {
        $active = $locale
            ?? (\class_exists(I18nContext::class, false)
                ? I18nContext::get_locale()
                : null)
            ?? $default;

        if (isset($map[$active]) && $map[$active] !== '') {
            return $map[$active];
        }
        if (isset($map[$default]) && $map[$default] !== '') {
            return $map[$default];
        }

        return $key;
    }
}

if (! \function_exists('lang')) {
    /**
     * File catalog: LANG_PATH / {locale}/translations.php.
     *
     * @param $key string
     * @param $replace array<string, string|int|float>
     * @param $locale string|null
     *
     * @return string
     */
    function lang(string $key, array $replace = [], ?string $locale = null): string
    {
        return Catalog::translate($key, $replace, $locale);
    }
}

if (! \function_exists('translated_value')) {
    /**
     * Model column envelope: { translations: { en: …, fr: … } }.
     *
     * @param $map array<string, mixed>
     *
     * @return array{translations: array<string, mixed>}
     */
    function translated_value(array $map): array
    {
        return TranslatedValue::make($map);
    }
}

if (! \function_exists('fast_i18n_model')) {
    /**
     * Resolve fast_i18n from a translated_value() column (or a bare string).
     *
     * @param $column array<string, mixed>|string|null
     * @param $locale string|null
     * @param $key string
     * @param $default string|null
     *
     * @return string
     */
    function fast_i18n_model(
        mixed $column,
        ?string $locale = null,
        string $key = 'model_column',
        ?string $default = null,
    ): string {
        $default ??= TranslatedValue::default_locale();
        $map = TranslatedValue::map($column);
        $string_map = [];
        foreach ($map as $code => $value) {
            if (\is_string($value) || \is_numeric($value)) {
                $string_map[(string) $code] = (string) $value;
            }
        }
        if ($string_map === []) {
            return \is_string($column) ? $column : '';
        }

        return fast_i18n($string_map, $locale, $key, $default);
    }
}

if (! \function_exists('i18n_current_lang')) {
    /**
     * Request locale (I18nContext), else app.locale.
     *
     * @return string
     */
    function i18n_current_lang(): string
    {
        $locale = I18nContext::get_locale();

        return $locale !== '' ? $locale : Catalog::default_locale();
    }
}

if (! \function_exists('i18n_default_lang')) {
    /**
     * @return string
     */
    function i18n_default_lang(): string
    {
        return Catalog::default_locale();
    }
}

if (! \function_exists('i18n_direction')) {
    /**
     * @param $locale string|null
     *
     * @return 'ltr'|'rtl'
     */
    function i18n_direction(?string $locale = null): string
    {
        $code = $locale !== null && $locale !== '' ? $locale : i18n_current_lang();

        return LocaleDirection::for($code);
    }
}

if (! \function_exists('i18n_is_rtl')) {
    /**
     * @param $locale string|null
     *
     * @return bool
     */
    function i18n_is_rtl(?string $locale = null): bool
    {
        return i18n_direction($locale) === 'rtl';
    }
}

if (! \function_exists('i18n_catalog_languages')) {
    /**
     * ICU primary languages (ext-intl). Any code still works in i18n_catalog_language_label().
     *
     * @return list<string>
     */
    function i18n_catalog_languages(): array
    {
        return Catalog::codes();
    }
}

if (! \function_exists('i18n_catalog_language_label')) {
    /**
     * Locale::getDisplayLanguage / getDisplayName. $prefer_native = name in that language.
     *
     * @param $code string
     * @param $prefer_native bool
     *
     * @return string
     */
    function i18n_catalog_language_label(string $code, bool $prefer_native = true): string
    {
        return Catalog::label($code, $prefer_native);
    }
}

if (! \function_exists('flag_markup')) {
    /**
     * Inline SVG for a language or country mark. Intrinsic 512px size is stripped.
     *
     * @param $code string
     * @param $class string
     *
     * @return string
     */
    function flag_markup(string $code, string $class = ''): string
    {
        $code = \strtolower(\trim($code));
        if ($code === '' || \preg_match('/^[a-z0-9][a-z0-9_-]{0,15}$/', $code) !== 1) {
            return '';
        }
        $candidates = [$code, \explode('-', $code, 2)[0]];
        $svg = '';
        $used = $code;
        foreach (\array_unique($candidates) as $name) {
            foreach (['language', 'countries'] as $set) {
                $path = \function_exists('webapp_path')
                    ? \webapp_path('public/flags/'.$set.'/'.$name.'.svg')
                    : '';
                if ($path === '' || ! \is_file($path)) {
                    continue;
                }
                $raw = \file_get_contents($path);
                if (\is_string($raw) && $raw !== '') {
                    $svg = \trim($raw);
                    $used = $name;
                    break 2;
                }
            }
        }
        if ($svg === '') {
            return '';
        }
        $svg = \preg_replace('/\s(?:width|height)="[^"]*"/i', '', $svg) ?? $svg;
        $uid = 'w-flag-'.\preg_replace('/[^a-z0-9]+/', '-', $used);
        $svg = \preg_replace('/\bid="([^"]+)"/', 'id="'.$uid.'-$1"', $svg) ?? $svg;
        $svg = \preg_replace('/url\(#([^)]+)\)/', 'url(#'.$uid.'-$1)', $svg) ?? $svg;
        $svg = \preg_replace(
            '/<svg\b/i',
            '<svg class="w-lang-flag-svg" width="16" height="16"',
            $svg,
            1,
        ) ?? $svg;
        if ($class === '') {
            return $svg;
        }

        return '<span class="'.\htmlspecialchars($class, \ENT_QUOTES, 'UTF-8').'">'.$svg.'</span>';
    }
}

\class_exists(I18nContext::class, true);
