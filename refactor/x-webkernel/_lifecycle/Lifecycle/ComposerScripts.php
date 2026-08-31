<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.

namespace Webkernel\Lifecycle;

use Composer\Script\Event;
use Webkernel\Console\Commands\DumpAutoloadCommand;

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
        (new DumpAutoloadCommand())->__invoke();
        if (function_exists('webkernel_boot_flush')) {
            webkernel_boot_flush();
        }
    }
}
