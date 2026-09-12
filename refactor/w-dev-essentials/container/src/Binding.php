<?php declare(strict_types=1);

namespace Webkernel\Container;

use Closure;

final readonly class Binding
{
    public const SCOPE_SINGLETON = 'singleton';
    public const SCOPE_SCOPED = 'scoped';
    public const SCOPE_TRANSIENT = 'transient';

    public function __construct(
        public string $abstract,
        public string|Closure $concrete,
        public string $scope = self::SCOPE_TRANSIENT,
        public string $moduleId = 'kernel',
    ) {}

    public function concreteName(): string
    {
        return \is_string($this->concrete) ? $this->concrete : 'closure';
    }
}
