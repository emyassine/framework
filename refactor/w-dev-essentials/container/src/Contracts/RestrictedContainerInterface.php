<?php declare(strict_types=1);

namespace Webkernel\Contracts;

use Closure;

interface RestrictedContainerInterface
{
    public function make(string $abstract): object;

    public function bind(string $abstract, string|Closure $concrete): void;

    public function singleton(string $abstract, string|Closure $concrete): void;

    public function import(string $moduleId, string $abstract): object;
}
