<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View;

/**
 * HTMX fragment door. View renders HTML; this class detects a fragment request.
 *
 * //> Network/swap is HTMX. Page and Schema stay the renderer. Local UI is dumped JS.
 */
final class Liveview
{
    /**
     * @return bool
     */
    public static function is_request(): bool
    {
        $flag = $_SERVER['HTTP_HX_REQUEST'] ?? '';

        return $flag === 'true' || $flag === '1';
    }
}
