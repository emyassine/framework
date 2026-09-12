<?php declare(strict_types=1);

namespace Webkernel\Container\Exceptions;

final class ModuleBorderViolationException extends ContainerException
{
    public function __construct(
        public readonly string $caller,
        public readonly string $module,
        public readonly string $requested,
    ) {
        parent::__construct(\sprintf(
            '%s attempted to resolve %s::%s which is not in that module\'s EXPORTS contract.',
            $caller,
            $module,
            $requested,
        ));
    }
}
