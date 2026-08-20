<?php declare(strict_types=1);

namespace Webkernel\Lifecycle;

use Composer\Script\Event;
use Webkernel\Lifecycle\Boot\BootGenerator;

/**
 * Optional host composer.json hook. The plugin already runs on post-autoload-dump.
 *
 *   "post-autoload-dump": [
 *     "Webkernel\\Lifecycle\\ComposerScripts::post_autoload_dump"
 *   ]
 */
final class ComposerScripts
{
    public static function post_autoload_dump(Event $event): void
    {
        BootGenerator::write($event->getComposer(), $event->getIO());
        if (function_exists('webkernel_boot_flush')) {
            webkernel_boot_flush();
        }
    }
}
