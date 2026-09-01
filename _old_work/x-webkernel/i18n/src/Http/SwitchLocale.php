<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\I18n\Http;

use Webkernel\Csrf;
use Webkernel\I18n\I18nContext;

/**
 * POST locale, then 303 back to the same path. The URL stays clean.
 */
final class SwitchLocale
{
    /**
     * @return never
     */
    public function __invoke(): never
    {
        if (! Csrf::check()) {
            \http_response_code(419);
            echo 'Invalid token.';
            exit;
        }
        I18nContext::persist((string) ($_POST['locale'] ?? ''));
        $path = self::back_path((string) ($_POST['_back'] ?? ''));
        \header('Location: '.$path, true, 303);
        exit;
    }

    /**
     * @param $raw string
     * @return string
     */
    private static function back_path(string $raw): string
    {
        $path = \parse_url($raw, PHP_URL_PATH);
        if (! \is_string($path) || $path === '' || ! \str_starts_with($path, '/') || \str_starts_with($path, '//')) {
            return '/';
        }

        return $path;
    }
}
