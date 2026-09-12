<?php declare(strict_types=1);

namespace Webkernel\Container\Exceptions;

final class BindingNotFoundException extends ContainerException
{
    public static function for(string $abstract): self
    {
        return new self(\sprintf('No binding or autowirable class found for "%s".', $abstract));
    }
}
