<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n\Support;

/**
 * Text direction from ICU script (ext-intl Locale).
 */
final class LocaleDirection
{
    /** @var list<string> ISO 15924 RTL scripts */
    private const RTL_SCRIPTS = ['Arab', 'Hebr', 'Syrc', 'Thaa', 'Nkoo', 'Adlm', 'Rohg', 'Mand'];

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
        $tag = \str_replace('_', '-', \trim($locale));
        if ($tag === '') {
            return false;
        }
        $full = $tag;
        if (\method_exists(\Locale::class, 'addLikelySubtags')) {
            $expanded = \Locale::addLikelySubtags($tag);
            if (\is_string($expanded) && $expanded !== '') {
                $full = $expanded;
            }
        }
        $script = \Locale::getScript($full);
        if (\is_string($script) && $script !== '') {
            return \in_array($script, self::RTL_SCRIPTS, true);
        }
        // PHP 8.4 intl has no Locale::addLikelySubtags (8.5). Native name's first letter.
        $native = \Locale::getDisplayLanguage($tag, $tag);
        if (! \is_string($native) || $native === '' || \preg_match('/./u', $native, $letter) !== 1) {
            return false;
        }
        $cp = \IntlChar::ord($letter[0]);
        if (! \is_int($cp)) {
            return false;
        }
        $dir = \IntlChar::charDirection($cp);

        return $dir === \IntlChar::CHAR_DIRECTION_RIGHT_TO_LEFT
            || $dir === \IntlChar::CHAR_DIRECTION_RIGHT_TO_LEFT_ARABIC;
    }
}
