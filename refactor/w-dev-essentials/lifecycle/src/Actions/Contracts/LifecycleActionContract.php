<?php declare(strict_types=1);
//> This file is part of Webkernel.
//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine
//> Yassine El Moumen <yassine@numerimondes.com> | <platform@webkernelphp.com>
//> For the full copyright and license information, please view the LICENSE
//> file that was distributed with this source code.
namespace Webkernel\Lifecycle\Actions\Contracts;

use Composer\Script\Event;

/**
 * One action = one code-generation responsibility.
 *
 * Rules:
 *   - Idempotent: safe to re-run on every `composer dump-autoload`.
 *   - No dependency outside webkernel/lifecycle.
 *   - Use webkernel_package('lifecycle', 'generated/{file}') for output paths.
 */
interface LifecycleActionContract
{
    /**
     * Unique key identifying what this action generates.
     * Used for logging and future diffing.
     * Example: "tailwind-config", "env-stub"
     */
    public function key(): string;

    /**
     * Human-readable label shown in Composer output.
     * Example: "Generate Tailwind config stub"
     */
    public function name(): string;

    /**
     * Execute the action.
     */
    public function handle(Event $event): void;
}
