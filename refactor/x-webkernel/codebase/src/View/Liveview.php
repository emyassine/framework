<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\View;

/**
 * HTMX fragment door. Server-side HTML swap, not a second view engine.
 *
 * //> Network/swap is HTMX. Local UI (drawer, password, tooltip) is dumped JS.
 * //> Yoyo Blade/Twig/Phalcon adapters are not taken. Page and Schema stay the renderer.
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
