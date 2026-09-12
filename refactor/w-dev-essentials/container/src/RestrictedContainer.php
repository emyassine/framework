<?php declare(strict_types=1);

namespace Webkernel\Container;

use Closure;
use Webkernel\Contracts\RestrictedContainerInterface;

final readonly class RestrictedContainer implements RestrictedContainerInterface
{
    public function __construct(
        private WebkernelContainer $root,
        private string $moduleId,
    ) {}

    public function make(string $abstract): object
    {
        return $this->root->makeForModule($this->moduleId, $abstract);
    }

    public function bind(string $abstract, string|Closure $concrete): void
    {
        $this->root->bindForModule($this->moduleId, $abstract, $concrete);
    }

    public function singleton(string $abstract, string|Closure $concrete): void
    {
        $this->root->singletonForModule($this->moduleId, $abstract, $concrete);
    }

    public function import(string $moduleId, string $abstract): object
    {
        return $this->root->forModule($moduleId)->resolve($abstract);
    }
}
