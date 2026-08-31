<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Liveview\Extensions;

/**
 * Helper class to register all default HTMX extensions.
 *
 * //> Call this during boot to register all available extensions.
 */
final class DefaultExtensions
{
    /**
     * Register all default extensions.
     *
     * @return void
     */
    public static function register(): void
    {
        // Networking extensions
        ExtensionManager::register(SseExtension::class);
        ExtensionManager::register(WebsocketExtension::class);
        ExtensionManager::register(MultipartExtension::class);
        ExtensionManager::register(DownloadExtension::class);

        // UX extensions
        ExtensionManager::register(PromptExtension::class);
    }

    /**
     * Register all default extensions and enable specific ones.
     *
     * @param list<string> $enabled List of extension names to enable
     * @return void
     */
    public static function register_with(array $enabled): void
    {
        self::register();

        foreach ($enabled as $name) {
            ExtensionManager::enable($name);
        }
    }
}
