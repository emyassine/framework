<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n\Support;

use Webkernel\I18n\Catalog;

/**
 * Text direction for a locale code (`ltr` | `rtl`).
 */
final class LocaleDirection
{
    /**
     * @param $locale string
     *
     * @return 'ltr'|'rtl'
     */
    public static function for(string $locale): string
    {
        return self::is_rtl($locale) ? 'rtl' : 'ltr';
    }

    /**
     * @param $locale string
     *
     * @return bool
     */
    public static function is_rtl(string $locale): bool
    {
        $code = \strtolower(\preg_replace('/[^a-z]/', '', \explode('-', \str_replace('_', '-', $locale), 2)[0]) ?? '');

        return $code !== '' && \in_array($code, Catalog::rtl_codes(), true);
    }
}
