<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Platform\Pages;

/**
 * Panel path with no page of its own. Sends the request to home_url (first page).
 */
final class PanelHome
{
    /**
     * @return string
     */
    public function __invoke(): string
    {
        $panel = \webapp()->panel()->matching_path();
        $dest = \is_array($panel) ? (string) ($panel['home_url'] ?? $panel['href'] ?? '/') : '/';
        if ($dest === '') {
            $dest = '/';
        }
        \http_response_code(302);
        \header('Location: '.$dest, true, 302);

        return '';
    }
}
