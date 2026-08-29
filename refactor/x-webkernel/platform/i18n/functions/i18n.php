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

if (! \function_exists('lang')) {
    /**
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

if (! \function_exists('i18n_path')) {
    /**
     * @param $path string|null
     *
     * @return string
     */
    function i18n_path(?string $path = null): string
    {
        $root = \dirname(__DIR__);

        return $path ? $root.'/'.\ltrim($path, '/') : $root;
    }
}

if (! \function_exists('component_i18n_path')) {
    /**
     * @param $path string|null
     *
     * @return string
     */
    function component_i18n_path(?string $path = null): string
    {
        return i18n_path($path);
    }
}

if (! \function_exists('fast_i18n')) {
    /**
     * Stateless O(1) locale map lookup. Pass $locale on hot paths.
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

if (! \function_exists('translated_value')) {
    /**
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

if (! \function_exists('i18n_catalog_languages')) {
    /**
     * @return list<string>
     */
    function i18n_catalog_languages(): array
    {
        return Catalog::codes();
    }
}

if (! \function_exists('i18n_catalog_language_label')) {
    /**
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

if (! \function_exists('i18n_catalog_options')) {
    /**
     * @param $prefer_native bool
     *
     * @return array<string, string>
     */
    function i18n_catalog_options(bool $prefer_native = true): array
    {
        return Catalog::options($prefer_native);
    }
}

if (! \function_exists('i18n_actual_langs')) {
    /**
     * @param $panel_id string|null
     *
     * @return list<string>
     */
    function i18n_actual_langs(?string $panel_id = null): array
    {
        // ponytail: panel locale allow-list is not built — catalog codes until panel scopes exist
        unset($panel_id);

        return Catalog::codes();
    }
}

if (! \function_exists('i18n_default_lang')) {
    /**
     * @param $panel_id string|null
     *
     * @return string
     */
    function i18n_default_lang(?string $panel_id = null): string
    {
        unset($panel_id);

        return Catalog::default_locale();
    }
}

if (! \function_exists('i18n_current_lang')) {
    /**
     * @param $panel_id string|null
     *
     * @return string
     */
    function i18n_current_lang(?string $panel_id = null): string
    {
        unset($panel_id);
        $locale = I18nContext::get_locale();

        return $locale !== '' ? $locale : Catalog::default_locale();
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

if (! \function_exists('i18n_lang_label')) {
    /**
     * @param $code string
     *
     * @return string
     */
    function i18n_lang_label(string $code): string
    {
        return Catalog::label($code);
    }
}

\class_exists(I18nContext::class, true);
