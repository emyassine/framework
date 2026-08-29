<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n\Support;

use Webkernel\I18n\Catalog;

/**
 * Per-column JSON envelope: `{ "translations": { "en": …, "fr": … } }`.
 */
final class TranslatedValue
{
    /**
     * @param $map array<string, mixed>
     *
     * @return array{translations: array<string, mixed>}
     */
    public static function make(array $map): array
    {
        return ['translations' => $map];
    }

    /**
     * @param $value mixed
     * @param $locales list<string>|null
     *
     * @return array{translations: array<string, mixed>}
     */
    public static function from_scalar(mixed $value, ?array $locales = null): array
    {
        $locales ??= self::locales();
        $map = [];
        foreach ($locales as $locale) {
            $map[$locale] = $value;
        }

        return self::make($map);
    }

    /**
     * @param $column array<string, mixed>|string|null
     *
     * @return array<string, mixed>
     */
    public static function map(mixed $column): array
    {
        if (\is_string($column) && $column !== '') {
            return self::from_scalar($column)['translations'];
        }
        if (! \is_array($column)) {
            return [];
        }
        if (isset($column['translations']) && \is_array($column['translations'])) {
            return $column['translations'];
        }

        return $column;
    }

    /**
     * @param $column array<string, mixed>|string|null
     *
     * @return array{translations: array<string, mixed>}
     */
    public static function normalize(mixed $column): array
    {
        return self::make(self::map($column));
    }

    /**
     * @return list<string>
     */
    public static function locales(): array
    {
        return Catalog::codes();
    }

    /**
     * @return string
     */
    public static function default_locale(): string
    {
        return Catalog::default_locale();
    }
}
