<?php declare(strict_types=1);

namespace Webkernel\Container;

use Webkernel\Container\Events\ModuleBorderViolationAttempted;
use Webkernel\Container\Exceptions\ModuleBorderViolationException;
use Webkernel\Contracts\ModuleScopeInterface;

final readonly class ModuleScope implements ModuleScopeInterface
{
    /**
     * @param list<class-string> $exports
     */
    public function __construct(
        private string $moduleId,
        private array $exports,
        private WebkernelContainer $root,
    ) {}

    public function moduleId(): string
    {
        return $this->moduleId;
    }

    public function exports(): array
    {
        return $this->exports;
    }

    public function resolve(string $abstract): object
    {
        if (! $this->owns($abstract)) {
            $caller = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown';

            $this->root->inspector()->dispatch(new ModuleBorderViolationAttempted(
                callerModule: $caller,
                targetModule: $this->moduleId,
                requestedAbstract: $abstract,
                attemptedAtMs: WebkernelContainer::nowMs(),
            ));

            throw new ModuleBorderViolationException(
                caller: $caller,
                module: $this->moduleId,
                requested: $abstract,
            );
        }

        return $this->root->make($abstract);
    }

    public function owns(string $abstract): bool
    {
        return \in_array($abstract, $this->exports, true);
    }
}
