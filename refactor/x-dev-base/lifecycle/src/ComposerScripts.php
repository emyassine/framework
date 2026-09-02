<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle;

use Composer\Script\Event;
use Webkernel\Lifecycle\Concerns\LCConcernRunner;
use Webkernel\Lifecycle\Concerns\LCEnvChecker;

/**
 * Static entry-point for Composer script hooks.
 *
 * Declare in extra.webkernel.lifecycle.events of any package:
 *
 *   "post-autoload-dump": "Webkernel\\Lifecycle\\ComposerScripts::post_autoload_dump"
 *
 * This class is intentionally thin: it only wires the Event into
 * the internal runners. All logic lives in LCEnvChecker and LCConcernRunner.
 *
 * Zero dependency on any package outside webkernel/lifecycle.
 */
final class ComposerScripts
{
    /**
     * Triggered after `composer dump-autoload`.
     *
     * 1. Run environment checks (blocking on "danger" level).
     * 2. Run concern-based code generation (one concern = one responsibility).
     */
    public static function post_autoload_dump(Event $event): void
    {
        $event->getIO()->write('<info>[webkernel/lifecycle] Running lifecycle checks…</info>');
        (new LCEnvChecker())->run($event);

        $event->getIO()->write('<info>[webkernel/lifecycle] Running concerns…</info>');
        (new LCConcernRunner())->run($event);
    }

    /**
     * Triggered before `composer install`.
     * Reserved for pre-install environment validation.
     */
    public static function pre_install_cmd(Event $event): void
    {
        (new LCEnvChecker())->run($event);
    }

    /**
     * Triggered before `composer update`.
     * Reserved for pre-update environment validation.
     */
    public static function pre_update_cmd(Event $event): void
    {
        (new LCEnvChecker())->run($event);
    }
}
