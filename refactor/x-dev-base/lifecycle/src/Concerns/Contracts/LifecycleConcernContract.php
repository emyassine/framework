<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Concerns\Contracts;

use Composer\Script\Event;

/**
 * A Concern is a self-contained unit of code generation triggered during the lifecycle.
 *
 * Each concern owns exactly ONE responsibility:
 *   - generate a helpers file (path helpers, vendor helpers, env helpers…)
 *   - write a config stub
 *   - emit an asset manifest
 *   - etc.
 *
 * Concerns are declared in extra.webkernel.lifecycle.concerns[] and executed
 * by LCConcernRunner at post-autoload-dump time.
 *
 * Constraints:
 *   - MUST NOT depend on any package outside webkernel/lifecycle.
 *   - MUST be idempotent (safe to run on every composer dump-autoload).
 *   - Receive the Composer Event for config, IO, and package metadata access.
 */
interface LifecycleConcernContract
{
    /**
     * Execute the concern's code generation or side-effect.
     */
    public function handle(Event $event): void;

    /**
     * Human-readable label for Composer output.
     *
     * Example: "Generate path helpers"
     */
    public function name(): string;
}
