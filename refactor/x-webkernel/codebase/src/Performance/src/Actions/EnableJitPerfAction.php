<?php declare(strict_types=1);

namespace Webkernel\Performance\Actions;

/**
 * Persist JIT-on for the next engine start. Cannot enable JIT in this process.
 * Panel hook: if (! webapp()->performance()->is_jit()) { (new EnableJitPerfAction())(); }
 */
final readonly class EnableJitPerfAction
{
    public function __invoke(): bool
    {
        return webapp()->performance()->enable_jit();
    }
}
