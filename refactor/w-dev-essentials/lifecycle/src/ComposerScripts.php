<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle;

use Composer\Script\Event;
use Webkernel\Lifecycle\Actions\LCActionRunner;
use Webkernel\Lifecycle\Actions\LCEnvChecker;

/**
 * Static entry-point for Composer lifecycle script hooks.
 *
 * Declared in extra.webkernel.lifecycle.events of any package:
 *   "post-autoload-dump": "Webkernel\\Lifecycle\\ComposerScripts::post_autoload_dump"
 *
 * Thin orchestrator only — all logic lives in LCEnvChecker and LCActionRunner.
 * Zero dependency outside webkernel/lifecycle.
 */
final class ComposerScripts
{
    /**
     * After `composer dump-autoload`:
     *   1. Environment checks (blocking on "danger").
     *   2. Action-based code generation.
     */
    public static function post_autoload_dump(Event $event): void
    {
        $io = $event->getIO();

        $io->write("\n<comment>[webkernel/lifecycle]</comment><info>  Checking environment…</info>\n");
        (new LCEnvChecker())->run($event);

        $io->write("<comment>[webkernel/lifecycle]</comment><info>  Running actions…</info>\n");
        (new LCActionRunner())->run($event);
    }

    /** Before `composer install` — checks only, no generation. */
    public static function pre_install_cmd(Event $event): void
    {
        $event->getIO()->write("\n<comment>[webkernel/lifecycle]</comment><info>  Pre-install checks…</info>\n");
        (new LCEnvChecker())->run($event);
    }

    /** Before `composer update` — checks only, no generation. */
    public static function pre_update_cmd(Event $event): void
    {
        $event->getIO()->write("\n<comment>[webkernel/lifecycle]</comment><info>  Pre-update checks…</info>\n");
        (new LCEnvChecker())->run($event);
    }
}
