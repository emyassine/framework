<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview;

/**
 * HTMX fragment door. View renders HTML; this class detects a fragment request.
 *
 * //> Network/swap is HTMX. Page and Schema stay the renderer. Local UI is dumped JS.
 * //> This is the main entry point for Liveview (HTMX-based reactive components).
 */
final class Liveview
{
    /**
     * Check if the current request is an HTMX request.
     *
     * @return bool
     */
    public static function is_request(): bool
    {
        $flag = $_SERVER['HTTP_HX_REQUEST'] ?? '';

        return $flag === 'true' || $flag === '1';
    }

    /**
     * Check if the current request is a boosted request (HTMX boost).
     *
     * @return bool
     */
    public static function is_boosted(): bool
    {
        return ($_SERVER['HTTP_HX_BOOSTED'] ?? '') === 'true';
    }

    /**
     * Get the current HTMX target element identifier.
     *
     * @return string|null
     */
    public static function hx_target(): ?string
    {
        return $_SERVER['HTTP_HX_TARGET'] ?? null;
    }

    /**
     * Get the current HTMX trigger element identifier.
     *
     * @return string|null
     */
    public static function hx_trigger(): ?string
    {
        return $_SERVER['HTTP_HX_TRIGGER'] ?? null;
    }

    /**
     * Get the current HTMX trigger name.
     *
     * @return string|null
     */
    public static function hx_trigger_name(): ?string
    {
        return $_SERVER['HTTP_HX_TRIGGER_NAME'] ?? null;
    }

    /**
     * Check if this is a history restore request (browser back/forward).
     *
     * @return bool
     */
    public static function is_history_restore(): bool
    {
        return ($_SERVER['HTTP_HX_HISTORY_RESTORE_REQUEST'] ?? '') === 'true';
    }

    /**
     * Get the HX-Current-URL header value.
     *
     * @return string|null
     */
    public static function hx_current_url(): ?string
    {
        return $_SERVER['HTTP_HX_CURRENT_URL'] ?? null;
    }

    /**
     * Response helpers for Liveview components.
     *
     * @return ResponseHelper
     */
    public static function response(): ResponseHelper
    {
        return new ResponseHelper();
    }
}
