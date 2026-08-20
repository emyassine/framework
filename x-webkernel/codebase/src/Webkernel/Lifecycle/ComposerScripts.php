<?php declare(strict_types=1);

namespace Webkernel\Lifecycle;

use Composer\Script\Event;
use Illuminate\Foundation\ComposerScripts as LaravelCS;
use Webkernel\Lifecycle\Vite\ViteWebapp;
use Webkernel\Paths\Composer;

/**
 * Extends Laravel foundation ComposerScripts.
 *
 * Laravel method names kept (parent::…).
 *
 * Host root composer.json only needs:
 *
 *   "post-autoload-dump": [
 *     "Webkernel\\Lifecycle\\ComposerScripts::postAutoloadDump",
 *     "@php artisan package:discover --ansi"
 *   ]
 *
 * Vite/JS work lives under src/lifecycle/Vite — this class only calls it.
 */
class ComposerScripts extends LaravelCS
{
    /** @param  Event  $event */
    public static function postAutoloadDump($event): void
    {
        parent::postAutoloadDump($event);

        try {
            // Prefer class root (IDE-friendly) over webapp_path() helper.
            (new ViteWebapp(Composer::root()))->vite_npm_build(
                io: $event->getIO(),
            );
        } catch (\Throwable $e) {
            $event->getIO()->writeError(
                '<warning>' . ViteWebapp::CLI_PREFIX . ' ' . $e->getMessage() . '</warning>',
            );
        }
    }

    /** @param  Event  $event */
    public static function postInstall($event): void
    {
        parent::postInstall($event);
    }

    /** @param  Event  $event */
    public static function postUpdate($event): void
    {
        parent::postUpdate($event);
    }
}
