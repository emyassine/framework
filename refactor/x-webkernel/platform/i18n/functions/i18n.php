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

\class_exists(I18nContext::class, true);
