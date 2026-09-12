<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview\Extensions;

/**
 * HTMX Prompt extension.
 *
 * //> Prompt before requests with hx-prompt='Reason?'
 * //> See: https://four.htmx.org/extensions/hx-prompt
 */
final class PromptExtension extends Extension
{
    /**
     * The extension name.
     */
    protected static string $name = 'hx-prompt';

    /**
     * The extension script URL (CDN).
     */
    protected static string $script_url = 'https://unpkg.com/htmx.org@4.0.0/dist/ext/hx-prompt.js';

    /**
     * Whether the extension is enabled by default.
     */
    protected static bool $enabled_by_default = false;
}
